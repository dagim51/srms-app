<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FinancialWebController extends Controller
{
    public function index()
    {
        $stats = [
            'total_revenue' => '145,500,000 ETB',
            'monthly_recurring' => '3,200,000 ETB',
            'pending_receivables' => '12,500,000 ETB',
            'active_payouts' => '450,000 ETB'
        ];

        $recentTransactions = [
            ['id' => 'TRX-9001', 'date' => '2026-10-08', 'type' => 'Sale Installment', 'amount' => '3,600,000 ETB', 'status' => 'completed', 'method' => 'Bank Transfer'],
            ['id' => 'TRX-9002', 'date' => '2026-10-08', 'type' => 'Rent Payment', 'amount' => '35,000 ETB', 'status' => 'processing', 'method' => 'Telebirr'],
            ['id' => 'TRX-9003', 'date' => '2026-10-07', 'type' => 'Refund', 'amount' => '-100,000 ETB', 'status' => 'refunded', 'method' => 'Bank Transfer'],
            ['id' => 'TRX-9004', 'date' => '2026-10-07', 'type' => 'Sale Down Payment', 'amount' => '4,500,000 ETB', 'status' => 'failed', 'method' => 'Card'],
        ];

        return view('frontend.finance.index', compact('stats', 'recentTransactions'));
    }

    public function transactions()
    {
        $transactions = [
            ['id' => 'TRX-9001', 'date' => '2026-10-08 14:30', 'reference' => 'Unit A-101 Payment', 'type' => 'Sale Installment', 'amount' => '3,600,000 ETB', 'status' => 'completed', 'method' => 'Bank Transfer'],
            ['id' => 'TRX-9002', 'date' => '2026-10-08 11:15', 'reference' => 'Rent Oct - Bole Apt 405', 'type' => 'Rent Payment', 'amount' => '35,000 ETB', 'status' => 'processing', 'method' => 'Telebirr'],
            ['id' => 'TRX-9003', 'date' => '2026-10-07 16:45', 'reference' => 'Reservation Cancellation', 'type' => 'Refund', 'amount' => '-100,000 ETB', 'status' => 'refunded', 'method' => 'Bank Transfer'],
            ['id' => 'TRX-9004', 'date' => '2026-10-07 09:10', 'reference' => 'Unit B-205 Deposit', 'type' => 'Sale Down Payment', 'amount' => '4,500,000 ETB', 'status' => 'failed', 'method' => 'Card'],
            ['id' => 'TRX-9005', 'date' => '2026-10-06 13:20', 'reference' => 'Rent Oct - CMC 201', 'type' => 'Rent Payment', 'amount' => '25,000 ETB', 'status' => 'pending', 'method' => 'Cash'],
        ];

        return view('frontend.finance.transactions', compact('transactions'));
    }

    public function transactionShow($id)
    {
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
                ['status' => 'Success', 'time' => '2026-10-08 14:30:45', 'message' => 'Funds verified and captured.']
            ]
        ];

        return view('frontend.finance.transaction_show', compact('transaction'));
    }

    public function invoices()
    {
        $invoices = [
            ['id' => 'INV-2026-085', 'client' => 'Abebe Kebede', 'amount' => '4,140,000 ETB', 'date' => '2026-10-01', 'due_date' => '2026-10-15', 'status' => 'paid'],
            ['id' => 'INV-2026-086', 'client' => 'Sara Tadesse', 'amount' => '25,000 ETB', 'date' => '2026-10-01', 'due_date' => '2026-10-05', 'status' => 'overdue'],
            ['id' => 'INV-2026-087', 'client' => 'Dawit Girma', 'amount' => '100,000 ETB', 'date' => '2026-10-08', 'due_date' => '2026-10-22', 'status' => 'draft'],
        ];

        return view('frontend.finance.invoices', compact('invoices'));
    }

    public function ledger()
    {
        $entries = [
            ['id' => 'LED-1001', 'date' => '2026-10-08', 'account' => 'Cash - Commercial Bank', 'debit' => '4,140,000', 'credit' => '0', 'description' => 'Payment Received TRX-9001'],
            ['id' => 'LED-1002', 'date' => '2026-10-08', 'account' => 'Accounts Receivable', 'debit' => '0', 'credit' => '4,140,000', 'description' => 'Settle Invoice INV-2026-085'],
            ['id' => 'LED-1003', 'date' => '2026-10-07', 'account' => 'Refunds Payable', 'debit' => '100,000', 'credit' => '0', 'description' => 'Process Refund TRX-9003'],
            ['id' => 'LED-1004', 'date' => '2026-10-07', 'account' => 'Cash - Commercial Bank', 'debit' => '0', 'credit' => '100,000', 'description' => 'Disburse Refund TRX-9003'],
        ];

        return view('frontend.finance.ledger', compact('entries'));
    }
}
