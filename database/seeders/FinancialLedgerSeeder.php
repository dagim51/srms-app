<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\LedgerAccount;
use App\Models\LedgerEntry;
use App\Models\LedgerEntryLine;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;

class FinancialLedgerSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        if (!$user) {
            return;
        }

        // 1. Chart of Accounts
        $accounts = [
            ['code' => '1010', 'name' => 'Cash - Commercial Bank of Ethiopia', 'type' => 'asset'],
            ['code' => '1020', 'name' => 'Cash - Telebirr Merchant Account', 'type' => 'asset'],
            ['code' => '1200', 'name' => 'Accounts Receivable', 'type' => 'asset'],
            ['code' => '2010', 'name' => 'Client Security Deposits Held', 'type' => 'liability'],
            ['code' => '2050', 'name' => 'Refunds & Payouts Payable', 'type' => 'liability'],
            ['code' => '4010', 'name' => 'Rental Lease Revenue', 'type' => 'revenue'],
            ['code' => '4020', 'name' => 'Development Sales Commission', 'type' => 'revenue'],
            ['code' => '5010', 'name' => 'Property Maintenance Expense', 'type' => 'expense'],
        ];

        $accountModels = [];
        foreach ($accounts as $acc) {
            $accountModels[$acc['code']] = LedgerAccount::firstOrCreate(
                ['code' => $acc['code']],
                ['name' => $acc['name'], 'type' => $acc['type']]
            );
        }

        // 2. Ledger Journal Entries with balanced debit/credit lines
        $journalEntries = [
            [
                'ref' => 'TRX-9001',
                'description' => 'Development Sale Installment Collection #1',
                'lines' => [
                    ['code' => '1010', 'type' => 'debit', 'amount' => 3600000],
                    ['code' => '1200', 'type' => 'credit', 'amount' => 3600000],
                ]
            ],
            [
                'ref' => 'TRX-9002',
                'description' => 'Monthly Rent Collection via Telebirr - Unit 405',
                'lines' => [
                    ['code' => '1020', 'type' => 'debit', 'amount' => 35000],
                    ['code' => '4010', 'type' => 'credit', 'amount' => 35000],
                ]
            ],
            [
                'ref' => 'TRX-9003',
                'description' => 'Refund Processing for Security Deposit',
                'lines' => [
                    ['code' => '2050', 'type' => 'debit', 'amount' => 100000],
                    ['code' => '1010', 'type' => 'credit', 'amount' => 100000],
                ]
            ],
        ];

        foreach ($journalEntries as $entryData) {
            $entry = LedgerEntry::firstOrCreate(
                ['reference_id' => $entryData['ref']],
                [
                    'description' => $entryData['description'],
                    'entry_date' => Carbon::now()->subDays(rand(1, 10))->toDateString(),
                ]
            );

            foreach ($entryData['lines'] as $line) {
                $acc = $accountModels[$line['code']] ?? null;
                if ($acc) {
                    LedgerEntryLine::firstOrCreate(
                        [
                            'ledger_entry_id' => $entry->id,
                            'ledger_account_id' => $acc->id,
                            'type' => $line['type'],
                        ],
                        [
                            'amount' => $line['amount'],
                        ]
                    );
                }
            }
        }

        // 3. Create Invoices
        $invoicesData = [
            ['amount' => 4140000, 'due_days' => 7, 'status' => 'paid', 'notes' => 'Skyline Towers Unit A-101 Payment'],
            ['amount' => 35000, 'due_days' => 5, 'status' => 'pending', 'notes' => 'Rent Oct - Bole Apt 405'],
            ['amount' => 25000, 'due_days' => -3, 'status' => 'overdue', 'notes' => 'Rent Oct - CMC 201'],
            ['amount' => 100000, 'due_days' => 14, 'status' => 'draft', 'notes' => 'Annual Facilities Service Fee'],
        ];

        $createdInvoices = [];
        foreach ($invoicesData as $inv) {
            $createdInvoices[] = Invoice::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'notes' => $inv['notes'],
                ],
                [
                    'amount' => $inv['amount'],
                    'due_date' => Carbon::now()->addDays($inv['due_days'])->toDateString(),
                    'status' => $inv['status'],
                ]
            );
        }

        // 4. Create Payments
        $paymentsData = [
            [
                'amount' => 3600000,
                'currency' => 'ETB',
                'method' => 'bank_transfer',
                'ref' => 'TRX-9001',
                'status' => 'completed',
            ],
            [
                'amount' => 35000,
                'currency' => 'ETB',
                'method' => 'telebirr',
                'ref' => 'TRX-9002',
                'status' => 'processing',
            ],
            [
                'amount' => 100000,
                'currency' => 'ETB',
                'method' => 'bank_transfer',
                'ref' => 'TRX-9003',
                'status' => 'refunded',
            ],
            [
                'amount' => 4500000,
                'currency' => 'ETB',
                'method' => 'card',
                'ref' => 'TRX-9004',
                'status' => 'failed',
            ],
        ];

        foreach ($paymentsData as $idx => $p) {
            $inv = $createdInvoices[$idx % count($createdInvoices)] ?? null;
            Payment::firstOrCreate(
                ['transaction_reference' => $p['ref']],
                [
                    'user_id' => $user->id,
                    'payable_type' => $inv ? Invoice::class : User::class,
                    'payable_id' => $inv ? $inv->id : $user->id,
                    'amount' => $p['amount'],
                    'currency' => $p['currency'],
                    'payment_method' => $p['method'],
                    'status' => $p['status'],
                    'metadata' => ['gateway_ref' => 'CBE-' . rand(100000, 999999)],
                ]
            );
        }
    }
}
