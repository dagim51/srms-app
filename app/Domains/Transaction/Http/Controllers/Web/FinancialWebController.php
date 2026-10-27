<?php

namespace App\Domains\Transaction\Http\Controllers\Web;

use App\Domains\Transaction\Models\Invoice;
use App\Domains\Transaction\Models\LedgerAccount;
use App\Domains\Transaction\Models\LedgerEntry;
use App\Domains\Transaction\Models\LedgerEntryLine;
use App\Domains\Transaction\Models\Payment;
use App\Domains\Transaction\Models\Payout;
use App\Domains\Transaction\Models\PayoutAccount;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class FinancialWebController extends Controller
{
    public function index()
    {
        $totalRevenue = Payment::where('status', 'completed')->sum('amount');
        $monthlyRecurring = Payment::where('status', 'completed')->where('created_at', '>=', now()->startOfMonth())->sum('amount');
        $pendingReceivables = Invoice::whereIn('status', ['pending', 'overdue'])->sum('amount');
        $activePayouts = Payment::where('status', 'processing')->sum('amount');

        $stats = [
            'total_revenue' => number_format($totalRevenue).' ETB',
            'monthly_recurring' => number_format($monthlyRecurring).' ETB',
            'pending_receivables' => number_format($pendingReceivables).' ETB',
            'active_payouts' => number_format($activePayouts).' ETB',
        ];

        $recentTransactions = Payment::with('user')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->transaction_reference ?? 'TRX-'.strtoupper(substr($p->id, 0, 8)),
                    'date' => $p->created_at ? $p->created_at->format('Y-m-d') : now()->format('Y-m-d'),
                    'type' => ucwords(str_replace('_', ' ', $p->payment_method ?? 'Payment')),
                    'amount' => number_format($p->amount).' '.($p->currency ?? 'ETB'),
                    'status' => $p->status ?? 'completed',
                    'method' => ucwords(str_replace('_', ' ', $p->payment_method ?? 'Bank Transfer')),
                ];
            })->toArray();

        return view('frontend.finance.index', compact('stats', 'recentTransactions'));
    }

    public function transactions(Request $request)
    {
        $query = Payment::with('user')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $q = trim($request->search);
            $query->where(function ($sub) use ($q) {
                $sub->where('transaction_reference', 'like', "%{$q}%")
                    ->orWhere('payment_method', 'like', "%{$q}%")
                    ->orWhereHas('user', function ($u) use ($q) {
                        $u->where('name', 'like', "%{$q}%");
                    });
            });
        }

        $transactions = $query->paginate(20)->withQueryString()->through(function ($p) {
            return [
                'raw_id' => $p->id,
                'id' => $p->transaction_reference ?? 'TRX-'.strtoupper(substr($p->id, 0, 8)),
                'date' => $p->created_at ? $p->created_at->format('Y-m-d H:i') : now()->format('Y-m-d H:i'),
                'reference' => 'Transaction #'.($p->transaction_reference ?? strtoupper(substr($p->id, 0, 8))),
                'type' => ucwords(str_replace('_', ' ', $p->payment_method ?? 'Payment')),
                'amount' => number_format($p->amount).' '.($p->currency ?? 'ETB'),
                'raw_amount' => $p->amount,
                'status' => $p->status ?? 'completed',
                'method' => ucwords(str_replace('_', ' ', $p->payment_method ?? 'Bank Transfer')),
            ];
        });

        $refundablePayments = Payment::with('user')
            ->where('status', 'completed')
            ->where('amount', '>', 0)
            ->latest()
            ->take(30)
            ->get();

        return view('frontend.finance.transactions', compact('transactions', 'refundablePayments'));
    }

    public function transactionShow($id)
    {
        $payment = Payment::with('user')->where('transaction_reference', $id)->first();
        if (! $payment && Str::isUuid($id)) {
            $payment = Payment::with('user')->where('id', $id)->first();
        }
        if (! $payment && str_starts_with($id, 'TRX-')) {
            $clean = str_replace('TRX-', '', $id);
            $payment = Payment::with('user')->whereRaw('CAST(id AS TEXT) LIKE ?', [substr($clean, 0, 8).'%'])->first();
        }

        if ($payment) {
            $amount = $payment->amount;
            $tax = $amount * 0.15;
            $total = $amount + $tax;

            $transaction = [
                'raw_id' => $payment->id,
                'id' => $payment->transaction_reference ?? $id,
                'date' => $payment->created_at ? $payment->created_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
                'reference' => 'Payment for '.($payment->transaction_reference ?? 'Transaction'),
                'type' => ucwords(str_replace('_', ' ', $payment->payment_method ?? 'Payment')),
                'amount' => number_format($amount).' '.$payment->currency,
                'raw_amount' => $amount,
                'tax' => number_format($tax).' '.$payment->currency,
                'total' => number_format($total).' '.$payment->currency,
                'status' => $payment->status ?? 'completed',
                'method' => ucwords(str_replace('_', ' ', $payment->payment_method ?? 'Bank Transfer')),
                'customer' => $payment->user?->name ?? 'Valued Customer',
                'invoice_id' => 'INV-'.strtoupper(substr($payment->id, 0, 8)),
                'gateway_ref' => $payment->metadata['gateway_ref'] ?? ($payment->metadata['gateway_reference'] ?? ($payment->transaction_reference ?? 'N/A')),
                'timeline' => [
                    ['status' => 'Payment Initiated', 'time' => $payment->created_at ? $payment->created_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'), 'message' => 'Payment record initiated.'],
                    ['status' => 'Payment Verified & Cleared', 'time' => $payment->updated_at ? $payment->updated_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'), 'message' => 'Cleared via '.ucwords(str_replace('_', ' ', $payment->payment_method ?? 'Bank Transfer')).'.'],
                ],
            ];

            return view('frontend.finance.transaction_show', compact('transaction'));
        }

        // Fallback: Check if $id corresponds to a pipeline deal from Deals Pipeline (Bank Receipt / Financial Receipt)
        $pipelineDeals = TransactionWebController::getPipelineDeals();
        $deal = $pipelineDeals->firstWhere('id', $id);

        if (! $deal && str_starts_with($id, 'TRX-')) {
            $deal = $pipelineDeals->first();
            if ($deal) {
                $deal['id'] = $id;
            }
        }

        if ($deal) {
            $amount = $deal['escrow_amount'] ?? ($deal['total_amount'] ?? 13000000);
            $tax = 0; // Principal escrow deposit
            $total = $amount;

            $milestones = collect($deal['milestones'] ?? [])->map(function ($m) {
                return [
                    'status' => $m['title'],
                    'time' => $m['date'].' (Milestone Stage)',
                    'message' => $m['note'] ?? 'Escrow verification milestone.',
                ];
            })->toArray();

            if (empty($milestones)) {
                $milestones = [
                    ['status' => 'Bank Escrow Deposit Confirmed', 'time' => ($deal['initiated_at'] ?? now()->format('Y-m-d')).' 10:00:00', 'message' => 'Certified by '.($deal['bank_partner'] ?? 'Commercial Bank of Ethiopia')],
                ];
            }

            $transaction = [
                'raw_id' => $deal['id'],
                'id' => $deal['id'],
                'date' => ($deal['initiated_at'] ?? now()->format('Y-m-d')).' 10:00:00',
                'reference' => 'Bank & Escrow Receipt for '.($deal['property_title'] ?? 'Real Estate Property'),
                'type' => $deal['type'] ?? 'Purchase Escrow',
                'amount' => number_format($amount).' ETB',
                'raw_amount' => $amount,
                'tax' => '0.00 ETB (Exempt / Principal Escrow)',
                'total' => number_format($total).' ETB',
                'status' => 'completed',
                'method' => $deal['bank_partner'] ?? 'Commercial Bank of Ethiopia (Wire Transfer)',
                'customer' => $deal['buyer_name'] ?? 'Buyer / Escrow Depositor',
                'invoice_id' => 'INV-'.strtoupper(substr(md5($id), 0, 8)),
                'gateway_ref' => $deal['escrow_account'] ?? 'CBE-ESCROW-'.strtoupper(substr(md5($id), 0, 10)),
                'timeline' => $milestones,
            ];

            return view('frontend.finance.transaction_show', compact('transaction'));
        }

        abort(404, 'Transaction or financial receipt not found.');
    }

    public function processRefund(Request $request, $id, \App\Services\Payments\PaymentService $paymentService)
    {
        $payment = Payment::where('transaction_reference', $id)->first();
        if (! $payment && Str::isUuid($id)) {
            $payment = Payment::where('id', $id)->first();
        }
        if (! $payment && str_starts_with($id, 'TRX-')) {
            $clean = str_replace('TRX-', '', $id);
            $payment = Payment::whereRaw('CAST(id AS TEXT) LIKE ?', [substr($clean, 0, 8).'%'])->first();
        }
        if (! $payment) {
            $deal = TransactionWebController::getPipelineDeals()->firstWhere('id', $id);
            if ($deal) {
                $payment = Payment::create([
                    'user_id' => auth()->id() ?? User::first()->id,
                    'payable_type' => Invoice::class,
                    'payable_id' => Str::uuid(),
                    'amount' => $deal['escrow_amount'] ?? ($deal['total_amount'] ?? 1000000),
                    'currency' => 'ETB',
                    'payment_method' => 'bank_transfer',
                    'status' => 'completed',
                    'transaction_reference' => $deal['id'],
                ]);
            } elseif (Str::isUuid($id)) {
                $payment = Payment::findOrFail($id);
            } else {
                abort(404, 'Transaction or financial receipt not found.');
            }
        }

        Gate::authorize('update', $payment);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:'.$payment->amount,
            'reason' => 'required|string|max:500',
        ]);

        try {
            $paymentService->refund($payment, (float) $validated['amount'], $validated['reason']);

            return redirect()->back()->with('success', 'Refund of '.number_format($validated['amount']).' '.($payment->currency ?? 'ETB').' processed and ledger reversal recorded.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Unable to process refund: '.$e->getMessage());
        }
    }

    public function invoices(Request $request)
    {
        $query = Invoice::with('user')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $q = trim($request->search);
            $query->where(function ($sub) use ($q) {
                $sub->where('notes', 'like', "%{$q}%")
                    ->orWhere('id', 'like', "%{$q}%")
                    ->orWhereHas('user', function ($u) use ($q) {
                        $u->where('name', 'like', "%{$q}%");
                    });
            });
        }

        $invoices = $query->paginate(20)->withQueryString()->through(function ($inv, $idx) {
            return [
                'raw_id' => $inv->id,
                'id' => 'INV-'.strtoupper(substr($inv->id, 0, 8)),
                'client' => $inv->user?->name ?? 'Client #'.($idx + 1),
                'amount' => number_format($inv->amount).' ETB',
                'raw_amount' => $inv->amount,
                'date' => $inv->created_at ? $inv->created_at->format('Y-m-d') : now()->format('Y-m-d'),
                'due_date' => $inv->due_date ? $inv->due_date : now()->addDays(14)->format('Y-m-d'),
                'status' => $inv->status ?? 'pending',
                'notes' => $inv->notes,
            ];
        });

        $users = User::select('id', 'name', 'email')->orderBy('name')->take(50)->get();

        return view('frontend.finance.invoices', compact('invoices', 'users'));
    }

    public function storeInvoice(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:1',
            'due_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        Invoice::create([
            'user_id' => $validated['user_id'],
            'amount' => $validated['amount'],
            'due_date' => $validated['due_date'],
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('dashboard.finance.invoices')->with('success', 'Tax invoice generated successfully.');
    }

    public function markInvoicePaid(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->update(['status' => 'paid']);

        Payment::create([
            'user_id' => $invoice->user_id,
            'payable_type' => Invoice::class,
            'payable_id' => $invoice->id,
            'amount' => $invoice->amount,
            'currency' => 'ETB',
            'payment_method' => $request->input('payment_method', 'bank_transfer'),
            'status' => 'completed',
            'transaction_reference' => 'PAY-'.strtoupper(Str::random(10)),
            'metadata' => [
                'invoice_id' => $invoice->id,
                'notes' => $request->input('notes', 'Marked paid from financial dashboard'),
            ],
        ]);

        return redirect()->route('dashboard.finance.invoices')->with('success', 'Invoice marked as paid and settlement payment recorded.');
    }

    public function invoiceShow($id)
    {
        $invoice = null;
        if (Str::isUuid($id)) {
            $invoice = Invoice::with('user')->where('id', $id)->first();
        }
        if (! $invoice) {
            $cleanId = str_replace('INV-', '', strtoupper($id));
            $invoice = Invoice::with('user')
                ->whereRaw('UPPER(SUBSTR(CAST(id AS TEXT), 1, 8)) = ?', [$cleanId])
                ->orWhereRaw('CAST(id AS TEXT) LIKE ?', ["%{$id}%"])
                ->firstOrFail();
        }

        $payments = Payment::where(function ($query) use ($invoice) {
            $query->where('payable_type', Invoice::class)
                ->where('payable_id', $invoice->id);
        })->orWhere(function ($query) use ($invoice) {
            $query->whereJsonContains('metadata->invoice_id', $invoice->id);
        })->latest()->get();

        $subtotal = round($invoice->amount / 1.15, 2);
        $tax = round($invoice->amount - $subtotal, 2);

        $invoiceData = [
            'raw_id' => $invoice->id,
            'id' => 'INV-'.strtoupper(substr($invoice->id, 0, 8)),
            'client_name' => $invoice->user?->name ?? 'Direct Client',
            'client_email' => $invoice->user?->email ?? 'N/A',
            'client_id' => $invoice->user_id,
            'amount' => number_format($invoice->amount, 2).' ETB',
            'raw_amount' => $invoice->amount,
            'subtotal' => number_format($subtotal, 2).' ETB',
            'tax' => number_format($tax, 2).' ETB',
            'date' => $invoice->created_at ? $invoice->created_at->format('Y-m-d') : now()->format('Y-m-d'),
            'due_date' => $invoice->due_date ? date('Y-m-d', strtotime($invoice->due_date)) : now()->addDays(14)->format('Y-m-d'),
            'status' => $invoice->status ?? 'pending',
            'notes' => $invoice->notes ?? 'Tax invoice generated in accordance with Ethiopian Revenue regulations.',
        ];

        return view('frontend.finance.invoice_show', compact('invoice', 'invoiceData', 'payments'));
    }

    public function updateInvoiceDecision(Request $request, $id)
    {
        $invoice = null;
        if (Str::isUuid($id)) {
            $invoice = Invoice::where('id', $id)->first();
        }
        if (! $invoice) {
            $cleanId = str_replace('INV-', '', strtoupper($id));
            $invoice = Invoice::whereRaw('UPPER(SUBSTR(CAST(id AS TEXT), 1, 8)) = ?', [$cleanId])
                ->orWhereRaw('CAST(id AS TEXT) LIKE ?', ["%{$id}%"])
                ->firstOrFail();
        }

        $validated = $request->validate([
            'decision' => 'required|in:approve,pay,cancel,dispute',
            'decision_notes' => 'nullable|string|max:1000',
            'payment_method' => 'nullable|string|in:telebirr,cbe_birr,bank_transfer,cash',
        ]);

        $decision = $validated['decision'];
        $notes = $validated['decision_notes'] ?? null;
        $existingNotes = $invoice->notes ? $invoice->notes."\n" : '';

        if ($decision === 'approve') {
            $appendNote = $notes ? "[Approved: {$notes}]" : '[Invoice Approved and Issued]';
            $invoice->update([
                'status' => 'pending',
                'notes' => trim($existingNotes.$appendNote),
            ]);

            return redirect()->back()->with('success', 'Invoice decision recorded: approved and ready for settlement.');
        }

        if ($decision === 'pay') {
            $appendNote = $notes ? "[Paid: {$notes}]" : '[Settled via Financial Decision Panel]';
            $invoice->update([
                'status' => 'paid',
                'notes' => trim($existingNotes.$appendNote),
            ]);

            Payment::create([
                'user_id' => $invoice->user_id,
                'payable_type' => Invoice::class,
                'payable_id' => $invoice->id,
                'amount' => $invoice->amount,
                'currency' => 'ETB',
                'payment_method' => $request->input('payment_method', 'bank_transfer'),
                'status' => 'completed',
                'transaction_reference' => 'PAY-'.strtoupper(Str::random(10)),
                'metadata' => [
                    'invoice_id' => $invoice->id,
                    'notes' => $notes ?? 'Settled via Financial Decision Panel',
                ],
            ]);

            return redirect()->back()->with('success', 'Invoice decision recorded: marked as PAID and settlement payment created.');
        }

        if ($decision === 'cancel') {
            $appendNote = $notes ? "[Cancelled: {$notes}]" : '[Invoice Voided/Cancelled]';
            $invoice->update([
                'status' => 'cancelled',
                'notes' => trim($existingNotes.$appendNote),
            ]);

            return redirect()->back()->with('success', 'Invoice has been voided/cancelled.');
        }

        if ($decision === 'dispute') {
            $appendNote = $notes ? "[Disputed: {$notes}]" : '[Flagged as Disputed]';
            $invoice->update([
                'status' => 'disputed',
                'notes' => trim($existingNotes.$appendNote),
            ]);

            return redirect()->back()->with('success', 'Invoice marked as disputed and flagged for financial investigation.');
        }

        return redirect()->back()->with('error', 'Unknown decision type.');
    }

    public function payouts(Request $request)
    {
        $query = Payout::with('account.user')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $q = trim($request->search);
            $query->where(function ($sub) use ($q) {
                $sub->where('reference_number', 'like', "%{$q}%")
                    ->orWhereHas('account', function ($acc) use ($q) {
                        $acc->where('bank_name', 'like', "%{$q}%")
                            ->orWhere('account_number', 'like', "%{$q}%")
                            ->orWhere('account_name', 'like', "%{$q}%")
                            ->orWhereHas('user', function ($u) use ($q) {
                                $u->where('name', 'like', "%{$q}%");
                            });
                    });
            });
        }

        $payouts = $query->paginate(20)->withQueryString()->through(function ($p) {
            return [
                'id' => $p->id,
                'reference' => $p->reference_number ?? 'PO-'.strtoupper(substr($p->id, 0, 8)),
                'recipient' => $p->account?->user?->name ?? ($p->account?->account_name ?? 'Recipient'),
                'bank_name' => $p->account?->bank_name ?? 'Commercial Bank of Ethiopia',
                'account_number' => $p->account?->account_number ?? 'N/A',
                'amount' => number_format($p->amount).' '.($p->currency ?? 'ETB'),
                'raw_amount' => $p->amount,
                'fee' => number_format($p->fee).' '.($p->currency ?? 'ETB'),
                'status' => $p->status,
                'date' => $p->created_at ? $p->created_at->format('M d, Y H:i') : now()->format('M d, Y H:i'),
                'processed_at' => $p->processed_at ? $p->processed_at->format('M d, Y H:i') : null,
                'notes' => $p->notes,
            ];
        });

        $payoutAccounts = PayoutAccount::with('user')->where('is_verified', true)->get();
        $users = User::select('id', 'name', 'email')->orderBy('name')->take(50)->get();

        return view('frontend.finance.payouts', compact('payouts', 'payoutAccounts', 'users'));
    }

    public function requestPayout(Request $request)
    {
        $validated = $request->validate([
            'payout_account_id' => 'required|exists:payout_accounts,id',
            'amount' => 'required|numeric|min:10',
            'notes' => 'nullable|string|max:500',
        ]);

        $fee = round($validated['amount'] * 0.01, 2);
        if ($fee < 10) {
            $fee = 10;
        }

        Payout::create([
            'payout_account_id' => $validated['payout_account_id'],
            'amount' => $validated['amount'],
            'fee' => $fee,
            'currency' => 'ETB',
            'status' => 'pending',
            'reference_number' => 'PO-'.strtoupper(Str::random(10)),
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('dashboard.finance.payouts')->with('success', 'Disbursement settlement requested successfully.');
    }

    public function processPayout(Request $request, $id)
    {
        $payout = Payout::findOrFail($id);
        $status = $request->input('status', 'completed');
        if (! in_array($status, ['completed', 'failed', 'processing'])) {
            $status = 'completed';
        }

        $payout->update([
            'status' => $status,
            'processed_at' => now(),
            'notes' => $request->filled('notes') ? $request->notes : $payout->notes,
        ]);

        return redirect()->route('dashboard.finance.payouts')->with('success', 'Payout settlement updated to '.ucfirst($status).'.');
    }

    public function ledger(Request $request)
    {
        $query = LedgerEntryLine::with(['entry', 'account'])->latest();

        if ($request->filled('account_id')) {
            $query->where('ledger_account_id', $request->account_id);
        }

        $entries = $query->paginate(20)
            ->withQueryString()
            ->through(function ($line, $idx) {
                return [
                    'id' => 'LED-'.strtoupper(substr($line->id, 0, 8)),
                    'date' => $line->entry?->entry_date ?? ($line->created_at ? $line->created_at->format('Y-m-d') : now()->format('Y-m-d')),
                    'account' => $line->account?->name ?? 'General Ledger Account',
                    'debit' => $line->type === 'debit' ? number_format($line->amount) : '0',
                    'credit' => $line->type === 'credit' ? number_format($line->amount) : '0',
                    'description' => $line->entry?->description ?? 'Journal Entry',
                ];
            });

        $totalDebit = (float) LedgerEntryLine::where('type', 'debit')->sum('amount');
        $totalCredit = (float) LedgerEntryLine::where('type', 'credit')->sum('amount');
        $accounts = LedgerAccount::orderBy('name')->get();

        return view('frontend.finance.ledger', compact('entries', 'totalDebit', 'totalCredit', 'accounts'));
    }

    public function storeLedgerEntry(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'debit_account_id' => 'required|exists:ledger_accounts,id',
            'credit_account_id' => 'required|exists:ledger_accounts,id|different:debit_account_id',
            'amount' => 'required|numeric|min:1',
            'entry_date' => 'nullable|date',
        ]);

        $entry = LedgerEntry::create([
            'description' => $validated['description'],
            'reference_id' => 'MAN-'.strtoupper(Str::random(8)),
            'entry_date' => $validated['entry_date'] ?? now()->toDateString(),
        ]);

        LedgerEntryLine::create([
            'ledger_entry_id' => $entry->id,
            'ledger_account_id' => $validated['debit_account_id'],
            'type' => 'debit',
            'amount' => $validated['amount'],
        ]);

        LedgerEntryLine::create([
            'ledger_entry_id' => $entry->id,
            'ledger_account_id' => $validated['credit_account_id'],
            'type' => 'credit',
            'amount' => $validated['amount'],
        ]);

        return redirect()->route('dashboard.finance.ledger')->with('success', 'Manual double-entry ledger adjustment posted.');
    }
}
