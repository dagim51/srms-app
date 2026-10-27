<?php

namespace Tests\Feature;

use App\Domains\Transaction\Models\Invoice;
use App\Domains\Transaction\Models\LedgerAccount;
use App\Domains\Transaction\Models\LedgerEntry;
use App\Domains\Transaction\Models\LedgerEntryLine;
use App\Domains\Transaction\Models\Payment;
use App\Domains\Transaction\Models\Payout;
use App\Domains\Transaction\Models\PayoutAccount;
use App\Jobs\PayoutProcessingJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class Module13PaymentLedgerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Finance Director',
            'status' => 'active',
        ]);

        $this->user = User::factory()->create([
            'name' => 'Dawit Tadesse',
            'status' => 'active',
        ]);
    }

    public function test_telebirr_webhook_rejects_invalid_hmac_signature_with_401(): void
    {
        $payload = json_encode([
            'out_trade_no' => 'TEL-TX-999',
            'trade_status' => 'SUCCESS',
            'amount' => 5000.00,
        ]);

        $response = $this->call(
            'POST',
            '/api/webhooks/payments/telebirr',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-Telebirr-Signature' => 'invalid-signature',
            ],
            $payload
        );

        $response->assertStatus(401)
            ->assertJsonPath('code', 'SIGNATURE_INVALID');
    }

    public function test_cbe_birr_webhook_rejects_invalid_hmac_signature_with_401(): void
    {
        $payload = json_encode([
            'out_trade_no' => 'CBE-TX-888',
            'status' => 'SUCCESS',
            'amount' => 7500.00,
        ]);

        $response = $this->call(
            'POST',
            '/api/webhooks/payments/cbe-birr',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-Cbe-Birr-Signature' => 'tampered-hmac-signature-xyz',
            ],
            $payload
        );

        $response->assertStatus(401)
            ->assertJsonPath('code', 'SIGNATURE_INVALID');
    }

    public function test_cbe_birr_webhook_accepts_valid_hmac_signature(): void
    {
        $payment = Payment::create([
            'user_id' => $this->user->id,
            'payable_type' => 'App\Models\AccommodationBooking',
            'payable_id' => (string) Str::uuid(),
            'amount' => 12000.00,
            'currency' => 'ETB',
            'status' => 'pending',
            'payment_method' => 'cbe_birr',
            'transaction_reference' => 'CBE-VALID-001',
        ]);

        $rawPayload = json_encode([
            'out_trade_no' => 'CBE-VALID-001',
            'status' => 'SUCCESS',
            'amount' => 12000.00,
        ]);

        $secret = 'cbe_birr_test_secret_key_12345';
        $signature = hash_hmac('sha256', $rawPayload, $secret);

        $response = $this->call(
            'POST',
            '/api/webhooks/payments/cbe-birr',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-Cbe-Birr-Signature' => $signature,
            ],
            $rawPayload
        );

        $response->assertStatus(200)
            ->assertJsonPath('code', 'SUCCESS');

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'completed',
        ]);
    }

    public function test_idempotency_middleware_prevents_duplicate_mutations(): void
    {
        $idempotencyKey = 'IDEM-KEY-'.Str::random(16);

        // First request to an idempotent endpoint (e.g. webhook with idempotency header)
        $payment = Payment::create([
            'user_id' => $this->user->id,
            'payable_type' => 'App\Models\AccommodationBooking',
            'payable_id' => (string) Str::uuid(),
            'amount' => 8500.00,
            'currency' => 'ETB',
            'status' => 'pending',
            'payment_method' => 'telebirr',
            'transaction_reference' => 'TEL-IDEM-001',
        ]);

        $payload = [
            'out_trade_no' => 'TEL-IDEM-001',
            'trade_status' => 'SUCCESS',
            'amount' => 8500.00,
        ];

        $firstResponse = $this->postJson(
            '/api/webhooks/payments/telebirr',
            $payload,
            ['Idempotency-Key' => $idempotencyKey]
        );

        $firstResponse->assertStatus(200);

        // Immediate replay of identical request with same Idempotency-Key
        $secondResponse = $this->postJson(
            '/api/webhooks/payments/telebirr',
            $payload,
            ['Idempotency-Key' => $idempotencyKey]
        );

        $secondResponse->assertStatus(200);
        $this->assertDatabaseCount('payments', 1);
        $this->assertSame('completed', $payment->fresh()->status);
    }

    public function test_financial_ledger_balances_and_renders_live_database_records(): void
    {
        // 1. Setup Chart of Accounts
        $bankAccount = LedgerAccount::create([
            'code' => '1010',
            'name' => 'Commercial Bank of Ethiopia - Operating',
            'type' => 'asset',
            'currency' => 'ETB',
            'current_balance' => 150000.00,
            'is_active' => true,
        ]);

        $revenueAccount = LedgerAccount::create([
            'code' => '4010',
            'name' => 'Property Rental Revenue',
            'type' => 'revenue',
            'currency' => 'ETB',
            'current_balance' => 150000.00,
            'is_active' => true,
        ]);

        // 2. Create balanced double-entry journal entry
        $entry = LedgerEntry::create([
            'entry_number' => 'JRN-2026-0001',
            'entry_date' => now()->toDateString(),
            'description' => 'Monthly rental payment deposit settlement',
            'posted_by' => $this->admin->id,
            'status' => 'posted',
        ]);

        // Debit: Asset increases by 150,000 ETB
        LedgerEntryLine::create([
            'ledger_entry_id' => $entry->id,
            'ledger_account_id' => $bankAccount->id,
            'type' => 'debit',
            'amount' => 150000.00,
            'description' => 'Cash received into bank',
        ]);

        // Credit: Revenue increases by 150,000 ETB
        LedgerEntryLine::create([
            'ledger_entry_id' => $entry->id,
            'ledger_account_id' => $revenueAccount->id,
            'type' => 'credit',
            'amount' => 150000.00,
            'description' => 'Earned rental income recognized',
        ]);

        // Verify debits equal credits
        $totalDebit = LedgerEntryLine::where('ledger_entry_id', $entry->id)->where('type', 'debit')->sum('amount');
        $totalCredit = LedgerEntryLine::where('ledger_entry_id', $entry->id)->where('type', 'credit')->sum('amount');
        $this->assertEquals($totalDebit, $totalCredit);
        $this->assertEquals(150000.00, $totalDebit);

        // 3. Test Dashboard Ledger UI renders dynamically
        $response = $this->actingAs($this->admin)->get(route('dashboard.finance.ledger'));

        $response->assertStatus(200);
        $response->assertSee('Commercial Bank of Ethiopia - Operating');
        $response->assertSee('Property Rental Revenue');
        $response->assertSee('150,000');
    }

    public function test_financial_invoices_and_transactions_render_live_records(): void
    {
        $invoice = Invoice::create([
            'user_id' => $this->user->id,
            'amount' => 45000.00,
            'status' => 'pending',
            'due_date' => now()->addDays(10)->toDateString(),
        ]);

        $payment = Payment::create([
            'user_id' => $this->user->id,
            'payable_type' => 'App\Models\Invoice',
            'payable_id' => $invoice->id,
            'amount' => 45000.00,
            'currency' => 'ETB',
            'status' => 'completed',
            'payment_method' => 'telebirr',
            'transaction_reference' => 'TRX-REAL-45000',
        ]);

        $expectedInvId = 'INV-'.strtoupper(substr($invoice->id, 0, 8));

        $invoiceResponse = $this->actingAs($this->admin)->get(route('dashboard.finance.invoices'));
        $invoiceResponse->assertStatus(200);
        $invoiceResponse->assertSee($expectedInvId);
        $invoiceResponse->assertSee('Dawit Tadesse');

        $trxResponse = $this->actingAs($this->admin)->get(route('dashboard.finance.transactions'));
        $trxResponse->assertStatus(200);
        $trxResponse->assertSee('TRX-REAL-45000');
    }

    public function test_payout_batch_processing_settles_pending_records(): void
    {
        $account = PayoutAccount::create([
            'user_id' => $this->user->id,
            'bank_name' => 'Awash International Bank',
            'account_number' => '01420999887700',
            'account_name' => 'Dawit Tadesse',
            'is_verified' => true,
        ]);

        $payout = Payout::create([
            'payout_account_id' => $account->id,
            'amount' => 38000.00,
            'fee' => 120.00,
            'currency' => 'ETB',
            'status' => 'pending',
        ]);

        $job = new PayoutProcessingJob;
        $processed = $job->handle();

        $this->assertGreaterThanOrEqual(1, $processed);
        $this->assertSame('completed', $payout->fresh()->status);
        $this->assertNotNull($payout->fresh()->processed_at);
    }
}
