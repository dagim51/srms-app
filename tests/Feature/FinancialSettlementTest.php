<?php

namespace Tests\Feature;

use App\Models\Accommodation;
use App\Models\AccommodationBooking;
use App\Models\LedgerAccount;
use App\Models\LedgerEntry;
use App\Models\LedgerEntryLine;
use App\Models\Payment;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Role;
use App\Models\User;
use App\Services\Payments\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialSettlementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'client', 'guard_name' => 'web']);
    }

    public function test_payment_completion_automatically_records_balanced_double_entry_ledger_lines()
    {
        $host = User::factory()->create();
        $guest = User::factory()->create();

        $propertyType = PropertyType::firstOrCreate(['code' => 'APT'], ['name_en' => 'Apartment']);
        $property = Property::create([
            'title' => 'Bole Suite',
            'property_code' => 'BOL-'.uniqid(),
            'type_id' => $propertyType->id,
            'status' => 'active',
        ]);

        $accommodation = Accommodation::create([
            'property_id' => $property->id,
            'host_id' => $host->id,
            'max_guests' => 2,
            'bedrooms' => 1,
            'beds' => 1,
            'bathrooms' => 1,
            'check_in_time' => '14:00',
            'check_out_time' => '11:00',
            'is_active' => true,
        ]);

        $booking = AccommodationBooking::create([
            'accommodation_id' => $accommodation->id,
            'user_id' => $guest->id,
            'check_in_date' => '2026-11-01',
            'check_out_date' => '2026-11-04',
            'guests' => 2,
            'total_price' => 450.00,
            'status' => 'pending',
        ]);

        $paymentService = app(PaymentService::class);

        $initData = $paymentService->initiate([
            'payable_type' => AccommodationBooking::class,
            'payable_id' => $booking->id,
            'user_id' => $guest->id,
            'payment_method' => 'telebirr',
        ]);

        $payment = $initData['payment'];
        $orderRef = $payment->metadata['gateway_order_ref'] ?? $payment->id;

        // Complete the payment
        $completedPayment = $paymentService->handleSuccess($orderRef, [
            'gateway' => 'telebirr',
            'transaction_id' => 'TLB-'.uniqid(),
        ]);

        $this->assertEquals('completed', $completedPayment->status);
        $this->assertEquals('confirmed', $booking->fresh()->status);

        // Verify Double-Entry Ledger Entry was recorded
        $entry = LedgerEntry::where('reference_id', $payment->id)->first();
        $this->assertNotNull($entry, 'Ledger entry should be automatically created upon settlement.');

        $lines = LedgerEntryLine::where('ledger_entry_id', $entry->id)->get();
        $this->assertCount(2, $lines, 'Ledger entry must contain exactly two balancing lines.');

        $debitLine = $lines->where('type', 'debit')->first();
        $creditLine = $lines->where('type', 'credit')->first();

        $this->assertNotNull($debitLine);
        $this->assertNotNull($creditLine);

        // Assert debit account is an Asset account
        $debitAccount = LedgerAccount::find($debitLine->ledger_account_id);
        $this->assertEquals('Asset', $debitAccount->type);
        $this->assertEquals(450.00, (float) $debitLine->amount);

        // Assert credit account is Revenue account
        $creditAccount = LedgerAccount::find($creditLine->ledger_account_id);
        $this->assertEquals('Revenue', $creditAccount->type);
        $this->assertEquals(450.00, (float) $creditLine->amount);

        // Assert debits equal credits (mathematical zero imbalance)
        $this->assertEquals($debitLine->amount, $creditLine->amount);
    }

    public function test_duplicate_payment_completion_does_not_duplicate_ledger_lines()
    {
        $user = User::factory()->create();
        $propertyType = PropertyType::firstOrCreate(['code' => 'APT'], ['name_en' => 'Apartment']);
        $property = Property::create([
            'title' => 'Sample',
            'property_code' => 'SMP-'.uniqid(),
            'type_id' => $propertyType->id,
            'status' => 'active',
        ]);
        $accommodation = Accommodation::create([
            'property_id' => $property->id,
            'host_id' => $user->id,
            'max_guests' => 2,
            'bedrooms' => 1,
            'beds' => 1,
            'bathrooms' => 1,
            'check_in_time' => '14:00',
            'check_out_time' => '11:00',
            'is_active' => true,
        ]);
        $booking = AccommodationBooking::create([
            'accommodation_id' => $accommodation->id,
            'user_id' => $user->id,
            'check_in_date' => '2026-11-10',
            'check_out_date' => '2026-11-12',
            'guests' => 1,
            'total_price' => 200.00,
            'status' => 'pending',
        ]);

        $paymentService = app(PaymentService::class);
        $initData = $paymentService->initiate([
            'payable_type' => AccommodationBooking::class,
            'payable_id' => $booking->id,
            'user_id' => $user->id,
            'payment_method' => 'telebirr',
        ]);

        $payment = $initData['payment'];
        $orderRef = $payment->metadata['gateway_order_ref'] ?? $payment->id;

        // First completion
        $paymentService->handleSuccess($orderRef);
        $this->assertEquals(1, LedgerEntry::where('reference_id', $payment->id)->count());

        // Second completion (replay)
        $paymentService->handleSuccess($orderRef);
        $this->assertEquals(1, LedgerEntry::where('reference_id', $payment->id)->count());
    }

    public function test_telebirr_webhook_processes_settlement_and_balances_ledger()
    {
        $user = User::factory()->create();
        $orderRef = 'SRMS-ORD-'.uniqid();

        $payment = Payment::create([
            'user_id' => $user->id,
            'payable_type' => User::class,
            'payable_id' => $user->id,
            'amount' => 1250.00,
            'currency' => 'ETB',
            'payment_method' => 'telebirr',
            'status' => 'processing',
            'transaction_reference' => $orderRef,
            'metadata' => [
                'gateway_order_ref' => $orderRef,
            ],
        ]);

        $response = $this->postJson('/api/webhooks/payments/telebirr', [
            'out_trade_no' => $orderRef,
            'trade_status' => 'SUCCESS',
            'total_amount' => 1250.00,
            'currency' => 'ETB',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'code' => 'SUCCESS',
            ]);

        $this->assertEquals('completed', $payment->fresh()->status);

        // Verify double-entry
        $entry = LedgerEntry::where('reference_id', $payment->id)->first();
        $this->assertNotNull($entry);
        $lines = LedgerEntryLine::where('ledger_entry_id', $entry->id)->get();
        $this->assertCount(2, $lines);
        $this->assertEquals(1250.00, (float) $lines->where('type', 'debit')->first()->amount);
        $this->assertEquals(1250.00, (float) $lines->where('type', 'credit')->first()->amount);
    }

    public function test_payment_refund_records_balanced_reversal_double_entry()
    {
        $user = User::factory()->create();
        $orderRef = 'SRMS-REF-'.uniqid();

        $payment = Payment::create([
            'user_id' => $user->id,
            'payable_type' => User::class,
            'payable_id' => $user->id,
            'amount' => 500.00,
            'currency' => 'ETB',
            'payment_method' => 'telebirr',
            'status' => 'completed',
            'transaction_reference' => $orderRef,
            'metadata' => ['gateway_order_ref' => $orderRef],
        ]);

        $paymentService = app(PaymentService::class);
        $refundResult = $paymentService->refund($payment, 200.00, 'Partial customer refund');

        $this->assertNotNull($refundResult);

        // Check refund ledger entry
        $refundPayment = Payment::where('user_id', $user->id)
            ->where('amount', -200.00)
            ->first();
        $this->assertNotNull($refundPayment);

        $refundEntry = LedgerEntry::where('reference_id', $refundPayment->id)->first();
        $this->assertNotNull($refundEntry);

        $lines = LedgerEntryLine::where('ledger_entry_id', $refundEntry->id)->get();
        $this->assertCount(2, $lines);

        $debitLine = $lines->where('type', 'debit')->first();
        $creditLine = $lines->where('type', 'credit')->first();

        $this->assertEquals(200.00, (float) $debitLine->amount);
        $this->assertEquals(200.00, (float) $creditLine->amount);
        $this->assertEquals('Liability', LedgerAccount::find($debitLine->ledger_account_id)->type);
        $this->assertEquals('Asset', LedgerAccount::find($creditLine->ledger_account_id)->type);
    }
}
