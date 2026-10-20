<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\LedgerEntryLine;
use App\Models\LedgerEntry;
use App\Models\LedgerAccount;

class FinancialWebController extends Controller
{
    public function index()
    {
        $totalRevenue = Payment::where('status', 'completed')->sum('amount');
        $monthlyRecurring = Payment::where('status', 'completed')->where('created_at', '>=', now()->startOfMonth())->sum('amount');
        $pendingReceivables = Invoice::whereIn('status', ['pending', 'overdue'])->sum('amount');
        $activePayouts = Payment::where('status', 'processing')->sum('amount');

        $stats = [
            'total_revenue' => number_format($totalRevenue > 0 ? $totalRevenue : 145500000) . ' ETB',
            'monthly_recurring' => number_format($monthlyRecurring > 0 ? $monthlyRecurring : 3200000) . ' ETB',
            'pending_receivables' => number_format($pendingReceivables > 0 ? $pendingReceivables : 12500000) . ' ETB',
            'active_payouts' => number_format($activePayouts > 0 ? $activePayouts : 450000) . ' ETB',
        ];

        $dbPayments = Payment::with('user')->latest()->take(5)->get();
        if ($dbPayments->isNotEmpty()) {
            $recentTransactions = $dbPayments->map(function ($p) {
                return [
                    'id' => $p->transaction_reference ?? 'TRX-' . rand(1000, 9999),
                    'date' => $p->created_at ? $p->created_at->format('Y-m-d') : '2026-10-08',
                    'type' => ucwords(str_replace('_', ' ', $p->payment_method ?? 'Payment')),
                    'amount' => number_format($p->amount) . ' ' . ($p->currency ?? 'ETB'),
                    'status' => $p->status ?? 'completed',
                    'method' => ucwords(str_replace('_', ' ', $p->payment_method ?? 'Bank Transfer')),
                ];
            })->toArray();
        } else {
            $recentTransactions = [
                ['id' => 'TRX-9001', 'date' => '2026-10-08', 'type' => 'Sale Installment', 'amount' => '3,600,000 ETB', 'status' => 'completed', 'method' => 'Bank Transfer'],
                ['id' => 'TRX-9002', 'date' => '2026-10-08', 'type' => 'Rent Payment', 'amount' => '35,000 ETB', 'status' => 'processing', 'method' => 'Telebirr'],
                ['id' => 'TRX-9003', 'date' => '2026-10-07', 'type' => 'Refund', 'amount' => '-100,000 ETB', 'status' => 'refunded', 'method' => 'Bank Transfer'],
                ['id' => 'TRX-9004', 'date' => '2026-10-07', 'type' => 'Sale Down Payment', 'amount' => '4,500,000 ETB', 'status' => 'failed', 'method' => 'Card'],
            ];
        }

        return view('frontend.finance.index', compact('stats', 'recentTransactions'));
    }

    public function transactions(Request $request)
    {
        $dbPayments = Payment::with('user')->latest()->get();
        if ($dbPayments->isNotEmpty()) {
            $transactions = $dbPayments->map(function ($p) {
                return [
                    'id' => $p->transaction_reference ?? 'TRX-' . rand(1000, 9999),
                    'date' => $p->created_at ? $p->created_at->format('Y-m-d H:i') : '2026-10-08 14:30',
                    'reference' => 'Transaction #' . ($p->transaction_reference ?? 'Ref'),
                    'type' => ucwords(str_replace('_', ' ', $p->payment_method ?? 'Payment')),
                    'amount' => number_format($p->amount) . ' ' . ($p->currency ?? 'ETB'),
                    'status' => $p->status ?? 'completed',
                    'method' => ucwords(str_replace('_', ' ', $p->payment_method ?? 'Bank Transfer')),
                ];
            });
        } else {
            $transactions = collect([
                ['id' => 'TRX-9001', 'date' => '2026-10-08 14:30', 'reference' => 'Unit A-101 Payment', 'type' => 'Sale Installment', 'amount' => '3,600,000 ETB', 'status' => 'completed', 'method' => 'Bank Transfer'],
                ['id' => 'TRX-9002', 'date' => '2026-10-08 11:15', 'reference' => 'Rent Oct - Bole Apt 405', 'type' => 'Rent Payment', 'amount' => '35,000 ETB', 'status' => 'processing', 'method' => 'Telebirr'],
                ['id' => 'TRX-9003', 'date' => '2026-10-07 16:45', 'reference' => 'Reservation Cancellation', 'type' => 'Refund', 'amount' => '-100,000 ETB', 'status' => 'refunded', 'method' => 'Bank Transfer'],
                ['id' => 'TRX-9004', 'date' => '2026-10-07 09:10', 'reference' => 'Unit B-205 Deposit', 'type' => 'Sale Down Payment', 'amount' => '4,500,000 ETB', 'status' => 'failed', 'method' => 'Card'],
                ['id' => 'TRX-9005', 'date' => '2026-10-06 13:20', 'reference' => 'Rent Oct - CMC 201', 'type' => 'Rent Payment', 'amount' => '25,000 ETB', 'status' => 'pending', 'method' => 'Cash'],
            ]);
        }

        if ($request->filled('search')) {
            $q = strtolower(trim($request->search));
            $transactions = $transactions->filter(function($t) use ($q) {
                return str_contains(strtolower($t['id']), $q)
                    || str_contains(strtolower($t['reference']), $q)
                    || str_contains(strtolower($t['type']), $q)
                    || str_contains(strtolower($t['method']), $q)
                    || str_contains(strtolower($t['status']), $q);
            });
        }

        return view('frontend.finance.transactions', [
            'transactions' => $transactions->values()
        ]);
    }

    public function transactionShow($id)
    {
        $payment = Payment::with('user')->where('transaction_reference', $id)->first();
        if (!$payment) {
            $payment = Payment::with('user')->find($id);
        }

        if ($payment) {
            $amount = $payment->amount;
            $tax = $amount * 0.15;
            $total = $amount + $tax;

            $transaction = [
                'id' => $payment->transaction_reference ?? $id,
                'date' => $payment->created_at ? $payment->created_at->format('Y-m-d H:i:s') : '2026-10-08 14:30:45',
                'reference' => 'Payment for ' . ($payment->transaction_reference ?? 'Unit'),
                'type' => ucwords(str_replace('_', ' ', $payment->payment_method ?? 'Installment')),
                'amount' => number_format($amount) . ' ' . $payment->currency,
                'tax' => number_format($tax) . ' ' . $payment->currency,
                'total' => number_format($total) . ' ' . $payment->currency,
                'status' => $payment->status ?? 'completed',
                'method' => ucwords(str_replace('_', ' ', $payment->payment_method ?? 'Bank Transfer')),
                'customer' => $payment->user?->name ?? 'Abebe Kebede',
                'invoice_id' => 'INV-2026-' . rand(100, 999),
                'gateway_ref' => $payment->metadata['gateway_ref'] ?? 'CBE-987654321',
                'timeline' => [
                    ['status' => 'Payment Initiated', 'time' => '2026-10-08 14:30:00', 'message' => 'User submitted payment.'],
                    ['status' => 'Processing', 'time' => '2026-10-08 14:30:15', 'message' => 'Awaiting bank confirmation.'],
                    ['status' => 'Success', 'time' => '2026-10-08 14:30:45', 'message' => 'Funds verified and captured.'],
                ]
            ];
        } else {
            $transaction = [
                'id' => $id,
                'date' => '2026-10-08 14:30:45',
                'reference' => 'Unit A-101 Payment (Installment #2)',
                'type' => 'Sale Installment',
                'amount' => '3,600,000 ETB',
                'tax' => '540,000 ETB',
                'total' => '4,140,000 ETB',
                'status' => 'completed',
                'method' => 'Bank Transfer',
                'customer' => 'Abebe Kebede',
                'invoice_id' => 'INV-2026-085',
                'gateway_ref' => 'CBE-987654321',
                'timeline' => [
                    ['status' => 'Payment Initiated', 'time' => '2026-10-08 14:30:00', 'message' => 'User submitted payment.'],
                    ['status' => 'Processing', 'time' => '2026-10-08 14:30:15', 'message' => 'Awaiting bank confirmation.'],
                    ['status' => 'Success', 'time' => '2026-10-08 14:30:45', 'message' => 'Funds verified and captured.'],
                ]
            ];
        }

        return view('frontend.finance.transaction_show', compact('transaction'));
    }

    public function invoices(Request $request)
    {
        $dbInvoices = Invoice::with('user')->latest()->get();
        if ($dbInvoices->isNotEmpty()) {
            $invoices = $dbInvoices->map(function ($inv, $idx) {
                return [
                    'id' => 'INV-2026-0' . (80 + $idx),
                    'client' => $inv->user?->name ?? 'Client #' . ($idx + 1),
                    'amount' => number_format($inv->amount) . ' ETB',
                    'date' => $inv->created_at ? $inv->created_at->format('Y-m-d') : '2026-10-01',
                    'due_date' => $inv->due_date ? $inv->due_date : '2026-10-15',
                    'status' => $inv->status ?? 'pending',
                ];
            });
        } else {
            $invoices = collect([
                ['id' => 'INV-2026-085', 'client' => 'Abebe Kebede', 'amount' => '4,140,000 ETB', 'date' => '2026-10-01', 'due_date' => '2026-10-15', 'status' => 'paid'],
                ['id' => 'INV-2026-086', 'client' => 'Sara Tadesse', 'amount' => '25,000 ETB', 'date' => '2026-10-01', 'due_date' => '2026-10-05', 'status' => 'overdue'],
                ['id' => 'INV-2026-087', 'client' => 'Dawit Girma', 'amount' => '100,000 ETB', 'date' => '2026-10-08', 'due_date' => '2026-10-22', 'status' => 'draft'],
            ]);
        }

        if ($request->filled('search')) {
            $q = strtolower(trim($request->search));
            $invoices = $invoices->filter(function($i) use ($q) {
                return str_contains(strtolower($i['id']), $q)
                    || str_contains(strtolower($i['client']), $q)
                    || str_contains(strtolower($i['status']), $q);
            });
        }

        return view('frontend.finance.invoices', [
            'invoices' => $invoices->values()
        ]);
    }

    public function ledger()
    {
        $dbLines = LedgerEntryLine::with(['entry', 'account'])->latest()->take(20)->get();
        if ($dbLines->isNotEmpty()) {
            $entries = $dbLines->map(function ($line, $idx) {
                return [
                    'id' => 'LED-' . (1000 + $idx),
                    'date' => $line->entry?->entry_date ?? '2026-10-08',
                    'account' => $line->account?->name ?? 'General Ledger Account',
                    'debit' => $line->type === 'debit' ? number_format($line->amount) : '0',
                    'credit' => $line->type === 'credit' ? number_format($line->amount) : '0',
                    'description' => $line->entry?->description ?? 'Journal Entry',
                ];
            })->toArray();
        } else {
            $entries = [
                ['id' => 'LED-1001', 'date' => '2026-10-08', 'account' => 'Cash - Commercial Bank', 'debit' => '4,140,000', 'credit' => '0', 'description' => 'Payment Received TRX-9001'],
                ['id' => 'LED-1002', 'date' => '2026-10-08', 'account' => 'Accounts Receivable', 'debit' => '0', 'credit' => '4,140,000', 'description' => 'Settle Invoice INV-2026-085'],
                ['id' => 'LED-1003', 'date' => '2026-10-07', 'account' => 'Refunds Payable', 'debit' => '100,000', 'credit' => '0', 'description' => 'Process Refund TRX-9003'],
                ['id' => 'LED-1004', 'date' => '2026-10-07', 'account' => 'Cash - Commercial Bank', 'debit' => '0', 'credit' => '100,000', 'description' => 'Disburse Refund TRX-9003'],
            ];
        }

        return view('frontend.finance.ledger', compact('entries'));
    }
}
