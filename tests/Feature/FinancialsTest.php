<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialsTest extends TestCase
{
    use RefreshDatabase;

    public function test_double_entry_accounting()
    {
        $user = User::factory()->create();

        // 1. Create Accounts
        $cashAcc = $this->actingAs($user)->postJson('/api/v1/financials/accounts', [
            'code' => '1001',
            'name' => 'Cash',
            'type' => 'Asset',
        ])->json('data.id');

        $revenueAcc = $this->actingAs($user)->postJson('/api/v1/financials/accounts', [
            'code' => '4001',
            'name' => 'Rent Revenue',
            'type' => 'Revenue',
        ])->json('data.id');

        // 2. Record balanced entry
        $entryRes = $this->actingAs($user)->postJson('/api/v1/financials/ledger-entries', [
            'description' => 'Monthly Rent Payment',
            'entry_date' => '2026-11-01',
            'lines' => [
                [
                    'ledger_account_id' => $cashAcc,
                    'type' => 'debit',
                    'amount' => 1500.00,
                ],
                [
                    'ledger_account_id' => $revenueAcc,
                    'type' => 'credit',
                    'amount' => 1500.00,
                ],
            ],
        ]);
        $entryRes->assertStatus(201);

        $entryId = $entryRes->json('data.id');
        $this->assertDatabaseHas('ledger_entries', ['id' => $entryId]);
        $this->assertDatabaseCount('ledger_entry_lines', 2);

        // 3. Reject unbalanced entry
        $failRes = $this->actingAs($user)->postJson('/api/v1/financials/ledger-entries', [
            'description' => 'Unbalanced Payment',
            'entry_date' => '2026-11-02',
            'lines' => [
                [
                    'ledger_account_id' => $cashAcc,
                    'type' => 'debit',
                    'amount' => 1500.00,
                ],
                [
                    'ledger_account_id' => $revenueAcc,
                    'type' => 'credit',
                    'amount' => 1400.00, // Unbalanced!
                ],
            ],
        ]);
        $failRes->assertStatus(400);

        // 4. Create Invoice
        $invRes = $this->actingAs($user)->postJson('/api/v1/financials/invoices', [
            'user_id' => $user->id,
            'amount' => 1500.00,
            'due_date' => '2026-11-05',
        ]);
        $invRes->assertStatus(201);
        $this->assertDatabaseHas('invoices', ['amount' => 1500.00]);
    }
}
