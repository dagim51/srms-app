<?php

namespace Tests\Feature;

use App\Domains\Booking\Models\AccommodationBooking;
use App\Domains\Transaction\Models\LedgerAccount;
use App\Domains\Transaction\Models\LedgerEntryLine;
use App\Domains\Transaction\Models\Payment;
use App\Jobs\ExpireTemporaryBookingHoldsJob;
use App\Models\Accommodation;
use App\Models\AccommodationAvailability;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\User;
use App\Services\Bookings\BookingManager;
use App\Services\Payments\PaymentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class Phase1FinancialAndConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected User $host;

    protected User $guest;

    protected Accommodation $accommodation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->host = User::factory()->create(['name' => 'Host User']);
        $this->guest = User::factory()->create(['name' => 'Guest User']);

        $propertyType = PropertyType::firstOrCreate(['code' => 'APT'], ['name_en' => 'Apartment']);
        $property = Property::create([
            'title' => 'Bole Luxury Penthouse',
            'property_code' => 'BOL-'.uniqid(),
            'type_id' => $propertyType->id,
            'status' => 'active',
            'price' => 1500.00,
        ]);

        $this->accommodation = Accommodation::create([
            'property_id' => $property->id,
            'host_id' => $this->host->id,
            'max_guests' => 4,
            'bedrooms' => 2,
            'beds' => 2,
            'bathrooms' => 2,
            'check_in_time' => '14:00',
            'check_out_time' => '11:00',
            'is_active' => true,
        ]);
    }

    public function test_create_hold_creates_holding_status_with_expiration_and_blocks_availability(): void
    {
        $manager = app(BookingManager::class);

        $checkIn = Carbon::now()->addDays(5)->toDateString();
        $checkOut = Carbon::now()->addDays(8)->toDateString();

        $hold = $manager->createHold([
            'accommodation_id' => $this->accommodation->id,
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'guests' => 2,
        ], $this->guest->id, 15);

        $this->assertEquals('holding', $hold->status);
        $this->assertNotNull($hold->expires_at);
        $this->assertTrue($hold->expires_at->isFuture());

        // Check availability table has held block
        $availability = AccommodationAvailability::where('accommodation_id', $this->accommodation->id)
            ->where('reason', 'Hold ID: '.$hold->id)
            ->first();

        $this->assertNotNull($availability);
        $this->assertEquals('held', $availability->type);

        // A second concurrent booking attempt for overlapping dates must be rejected
        $this->expectException(\DomainException::class);
        $manager->create([
            'accommodation_id' => $this->accommodation->id,
            'check_in_date' => Carbon::now()->addDays(6)->toDateString(),
            'check_out_date' => Carbon::now()->addDays(7)->toDateString(),
            'guests' => 1,
        ], $this->guest->id);
    }

    public function test_expiration_job_cancels_expired_holds_and_releases_availability(): void
    {
        $manager = app(BookingManager::class);

        $checkIn = Carbon::now()->addDays(10)->toDateString();
        $checkOut = Carbon::now()->addDays(12)->toDateString();

        $hold = $manager->createHold([
            'accommodation_id' => $this->accommodation->id,
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'guests' => 2,
        ], $this->guest->id, 15);

        // Artificially age the hold to simulate 15-minute expiration
        $hold->update([
            'expires_at' => Carbon::now()->subMinutes(5),
        ]);

        $this->assertTrue($hold->fresh()->isExpired());

        // Run the automated expiration background job
        $job = new ExpireTemporaryBookingHoldsJob;
        $expiredCount = $job->handle();

        $this->assertGreaterThanOrEqual(1, $expiredCount);
        $this->assertEquals('expired', $hold->fresh()->status);

        // Availability block must be removed
        $availability = AccommodationAvailability::where('accommodation_id', $this->accommodation->id)
            ->where('reason', 'Hold ID: '.$hold->id)
            ->first();
        $this->assertNull($availability);

        // Dates are now released and available for booking
        $newBooking = $manager->create([
            'accommodation_id' => $this->accommodation->id,
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'guests' => 2,
        ], $this->guest->id);

        $this->assertEquals('pending', $newBooking->status);
    }

    public function test_confirm_hold_transitions_status_to_confirmed_and_converts_availability(): void
    {
        $manager = app(BookingManager::class);

        $checkIn = Carbon::now()->addDays(15)->toDateString();
        $checkOut = Carbon::now()->addDays(18)->toDateString();

        $hold = $manager->createHold([
            'accommodation_id' => $this->accommodation->id,
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'guests' => 2,
        ], $this->guest->id, 15);

        $confirmed = $manager->confirm($hold, $this->host->id);

        $this->assertEquals('confirmed', $confirmed->status);

        // Availability converted from held to booked
        $availability = AccommodationAvailability::where('accommodation_id', $this->accommodation->id)
            ->where('reason', 'Booking ID: '.$hold->id)
            ->first();

        $this->assertNotNull($availability);
        $this->assertEquals('booked', $availability->type);
    }

    public function test_multi_party_ledger_settlement_enforces_exact_zero_mathematical_imbalance(): void
    {
        $payment = Payment::create([
            'user_id' => $this->guest->id,
            'payable_type' => AccommodationBooking::class,
            'payable_id' => (string) Str::uuid(),
            'amount' => 10000.00,
            'currency' => 'ETB',
            'status' => 'completed',
            'payment_method' => 'telebirr',
            'transaction_reference' => 'TLB-SPLIT-'.uniqid(),
        ]);

        $paymentService = app(PaymentService::class);

        // Multi-party itemized split: 8,500 host escrow, 1,000 platform fee, 500 VAT tax
        $entry = $paymentService->recordSplitSettlement($payment, [
            'host_escrow' => 8500.00,
            'platform_fee' => 1000.00,
            'tax' => 500.00,
        ]);

        $lines = LedgerEntryLine::where('ledger_entry_id', $entry->id)->get();
        $this->assertCount(4, $lines, 'Expected 1 Debit line and 3 Credit lines');

        $debits = $lines->where('type', 'debit');
        $credits = $lines->where('type', 'credit');

        $this->assertCount(1, $debits);
        $this->assertCount(3, $credits);

        $totalDebit = $debits->sum('amount');
        $totalCredit = $credits->sum('amount');

        $this->assertEquals(10000.00, (float) $totalDebit);
        $this->assertEquals(10000.00, (float) $totalCredit);
        $this->assertEquals($totalDebit, $totalCredit, 'Debits must mathematically equal credits.');

        // Verify account categories
        $debitAccount = LedgerAccount::find($debits->first()->ledger_account_id);
        $this->assertEquals('Asset', $debitAccount->type);

        $creditAccountIds = $credits->pluck('ledger_account_id');
        $creditAccounts = LedgerAccount::whereIn('id', $creditAccountIds)->get();

        $types = $creditAccounts->pluck('type')->unique()->sort()->values()->all();
        $this->assertContains('Liability', $types);
        $this->assertContains('Revenue', $types);
    }

    public function test_webhook_rejects_replayed_stale_timestamp(): void
    {
        $payload = [
            'out_trade_no' => 'TLB-STALE-001',
            'trade_status' => 'SUCCESS',
            'amount' => 500.00,
            'timestamp' => Carbon::now()->subMinutes(10)->timestamp, // 10 minutes old (> 300s)
        ];

        $response = $this->postJson('/api/webhooks/payments/telebirr', $payload);

        $response->assertStatus(401)
            ->assertJsonPath('code', 'REQUEST_EXPIRED');
    }

    public function test_webhook_rejects_missing_signature_in_production(): void
    {
        $this->app->detectEnvironment(function () {
            return 'production';
        });

        $payload = [
            'out_trade_no' => 'TLB-PROD-001',
            'trade_status' => 'SUCCESS',
            'amount' => 500.00,
        ];

        $response = $this->postJson('/api/webhooks/payments/telebirr', $payload);

        $response->assertStatus(401)
            ->assertJsonPath('code', 'SIGNATURE_REQUIRED');
    }
}
