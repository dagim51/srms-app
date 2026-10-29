<?php

namespace Tests\Feature;

use App\Domains\Booking\Models\AccommodationBooking;
use App\Domains\Stay\Models\Accommodation;
use App\Domains\Transaction\Models\LedgerAccount;
use App\Domains\Transaction\Models\LedgerEntry;
use App\Domains\Transaction\Models\LedgerEntryLine;
use App\Domains\Transaction\Models\Payment;
use App\Domains\Transaction\Services\LedgerPostingService;
use App\Jobs\ExpireHostBookingRequestsJob;
use App\Jobs\ExpireTemporaryBookingHoldsJob;
use App\Models\AccommodationAvailability;
use App\Models\City;
use App\Models\Country;
use App\Models\Listing;
use App\Models\Property;
use App\Models\Region;
use App\Models\User;
use App\Services\Bookings\BookingManager;
use App\Services\Payments\TelebirrGateway;
use Carbon\Carbon;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase4Holes1And2Test extends TestCase
{
    use RefreshDatabase;

    protected User $host;

    protected User $guest1;

    protected User $guest2;

    protected Accommodation $accommodation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->host = User::factory()->create(['name' => 'Abebe Host']);
        $this->guest1 = User::factory()->create(['name' => 'Chala Guest']);
        $this->guest2 = User::factory()->create(['name' => 'Almaz Guest']);

        $country = Country::create(['name_en' => 'Ethiopia', 'code' => 'ET', 'phone_code' => '251', 'is_active' => true]);
        $region = Region::create(['country_id' => $country->id, 'name_en' => 'Addis Ababa', 'code' => 'AA', 'is_active' => true]);
        $city = City::create(['region_id' => $region->id, 'name_en' => 'Addis Ababa', 'code' => 'ADD', 'is_active' => true]);

        $property = Property::create([
            'property_code' => 'PROP-'.uniqid(),
            'owner_user_id' => $this->host->id,
            'title' => 'Bole Luxury Penthouse',
            'description' => 'Five star stay in central Bole',
            'status' => 'verified',
            'bedrooms' => 2,
            'bathrooms' => 2,
            'building_area' => 120,
        ]);

        Listing::create([
            'property_id' => $property->id,
            'purpose' => 'Rent',
            'price' => 3500.0,
            'currency' => 'ETB',
            'status' => 'published',
        ]);

        $this->accommodation = Accommodation::create([
            'property_id' => $property->id,
            'host_id' => $this->host->id,
            'type' => 'entire_place',
            'status' => 'active',
            'max_guests' => 4,
            'bedrooms' => 2,
            'bathrooms' => 2,
            'base_price' => 3500.0,
            'currency' => 'ETB',
            'is_instant_book' => true,
        ]);
    }

    /**
     * HOLE 2: Concurrency & Pessimistic lock prevents double booking under concurrent requests.
     */
    public function test_booking_hold_concurrency_lock_for_update_prevents_overlapping_reservation(): void
    {
        $manager = app(BookingManager::class);

        $checkIn = Carbon::now()->addDays(5)->toDateString();
        $checkOut = Carbon::now()->addDays(8)->toDateString();

        // Guest 1 creates temporary hold (15 minutes)
        $hold = $manager->createHold([
            'accommodation_id' => $this->accommodation->id,
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'guests' => 2,
        ], $this->guest1->id);

        $this->assertEquals('holding', $hold->status);
        $this->assertNotNull($hold->expires_at);

        // Guest 2 attempts to reserve overlapping dates simultaneously
        $this->expectException(DomainException::class);
        $manager->createHold([
            'accommodation_id' => $this->accommodation->id,
            'check_in_date' => Carbon::now()->addDays(6)->toDateString(),
            'check_out_date' => Carbon::now()->addDays(9)->toDateString(),
            'guests' => 2,
        ], $this->guest2->id);
    }

    /**
     * HOLE 2: ExpireTemporaryBookingHoldsJob cancels expired holds and cleans up calendar availability.
     */
    public function test_expire_temporary_booking_holds_job_releases_calendar_inventory(): void
    {
        $booking = AccommodationBooking::create([
            'accommodation_id' => $this->accommodation->id,
            'user_id' => $this->guest1->id,
            'check_in_date' => '2026-11-01',
            'check_out_date' => '2026-11-04',
            'guests' => 2,
            'total_price' => 10500.0,
            'status' => 'holding',
            'expires_at' => Carbon::now()->subMinutes(5), // Expired 5 mins ago
        ]);

        AccommodationAvailability::create([
            'accommodation_id' => $this->accommodation->id,
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-04',
            'type' => 'held',
            'reason' => 'Hold ID: '.$booking->id,
        ]);

        $this->assertDatabaseHas('accommodation_availabilities', ['reason' => 'Hold ID: '.$booking->id]);

        $job = new ExpireTemporaryBookingHoldsJob;
        $count = $job->handle();

        $this->assertEquals(1, $count);
        $this->assertDatabaseHas('accommodation_bookings', [
            'id' => $booking->id,
            'status' => 'expired',
        ]);
        $this->assertDatabaseMissing('accommodation_availabilities', [
            'reason' => 'Hold ID: '.$booking->id,
        ]);
    }

    /**
     * HOLE 2: ExpireHostBookingRequestsJob cancels 24-hr unresponded host requests.
     */
    /**
     * HOLE 2: ExpireHostBookingRequestsJob cancels 24-hr unresponded host requests.
     */
    public function test_expire_host_booking_requests_job_cancels_stale_submissions(): void
    {
        $staleBooking = AccommodationBooking::create([
            'accommodation_id' => $this->accommodation->id,
            'user_id' => $this->guest1->id,
            'check_in_date' => '2026-12-01',
            'check_out_date' => '2026-12-05',
            'guests' => 2,
            'total_price' => 14000.0,
            'status' => 'requested',
        ]);
        $staleBooking->created_at = Carbon::now()->subHours(25);
        $staleBooking->saveQuietly();

        AccommodationAvailability::create([
            'accommodation_id' => $this->accommodation->id,
            'start_date' => '2026-12-01',
            'end_date' => '2026-12-05',
            'type' => 'held',
            'reason' => 'Booking ID: '.$staleBooking->id,
        ]);

        $freshBooking = AccommodationBooking::create([
            'accommodation_id' => $this->accommodation->id,
            'user_id' => $this->guest2->id,
            'check_in_date' => '2026-12-10',
            'check_out_date' => '2026-12-12',
            'guests' => 1,
            'total_price' => 7000.0,
            'status' => 'requested',
        ]);

        $job = new ExpireHostBookingRequestsJob;
        $count = $job->handle();

        $this->assertEquals(1, $count);
        $this->assertDatabaseHas('accommodation_bookings', [
            'id' => $staleBooking->id,
            'status' => 'expired',
        ]);
        $this->assertDatabaseHas('accommodation_bookings', [
            'id' => $freshBooking->id,
            'status' => 'requested',
        ]);
        $this->assertDatabaseMissing('accommodation_availabilities', [
            'reason' => 'Booking ID: '.$staleBooking->id,
        ]);
    }

    /**
     * HOLE 1: Telebirr real RSA-SHA256 asymmetric signature verification.
     */
    public function test_telebirr_real_rsa_sha256_public_key_signature_verification(): void
    {
        $privateKeyPem = "-----BEGIN PRIVATE KEY-----\n".
            "MIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQC4EHdPog1SvdZs\n".
            "RsGQ5Kdnwf8av+QPFh6XonTKoPt2iYKGAnik090EXT9Ctsu/hTDgPiC9CtM+Ckk6\n".
            "fn57rYXqkWccrcmLKNC/12QvRjnu6s1HEMELdbb3aqCxv7u2feIW/zoR/T/XyH3v\n".
            "GQHwODpcCjjFzUkIlVf5PoWyFkW2EQCsBYYd6zyY9YEDCEbUHgo6AR/pRbP8ts1j\n".
            "JxYkAEA613AhtVlGMAsiodyB8aI9T5YCliYbZ8NIgDulMCE2+vH19W5aMy4jqQzQ\n".
            "8YFL7qICV7xQsiW6Mfu5P6HNse+Zb5leg/4V8L9lyjhOEikCcBeg3pQRXPlb82nh\n".
            "wrtB89aRAgMBAAECggEAAiYxPMhnY+FAXqz+6PtuK+PqmoggWwT9iX9EYk053HD8\n".
            "+ZC6h7BV261/+JONn9xiX9xChHnlhytGc+jK1Cm9tzRDDi/xCydTCdS+HaocsRoz\n".
            "+F3E/EZ/yDt/byM44f0jVv8xoSjEvmG1MeXSoAiptUi1BUm+feDlCThFEOlpygHm\n".
            "Gymtos6KNMJDlqSnvTd1JDawb92gkCaAaVkZs6328nDFMa/qmTF84XNS94XJegDU\n".
            "Lasv/enHWu7wcj/ttZKy3Iq4QPEwfAlDuasvtYl6s2YeZdj5lMBdzzq/1JZd7HYk\n".
            "+J9owYuONTIgE8uceXDh46oanWd+yB/sr45ynChIAQKBgQDrCuksIVQttpe7i4vg\n".
            "5VhNgXeLILmf2i60uIAC0sIHH+wleAClZlKsBBh9jfqvCY5kvehZlvMGVx/b4d3h\n".
            "eks26NVW76G5eVi8EjcW8fquPgjQguMdFhmu56/twoDl7UgvP8SZYrIceuQcqg9l\n".
            "VRCdHCNDWlpTGHTvD9YxW3iOkQKBgQDIeexV6+NLTugggbZh+ZtnECVkSNfDqzDL\n".
            "o+UmnPQp5r+sOTi3NgdfmZQ1WNdX24ISIs2DOCq+I/CdemtfxpYQCqbgBECz5QTW\n".
            "f/Y047wEs60u4GlLbnexgQkys3DuaLpwjBzVWdpgu+HQXiW0CnqVl/aKPv++X4bE\n".
            "bNyyZnrIAQKBgEdJfuEQlrxqpeiO8XTbeoW/X6eF3GbcQNA1KTYyAAEzMGdI10FT\n".
            "IH3lnBvwhPizuElbw+3wN3NT12jUP3yUn91ejt4YPf/ICx63k5fU+ogAbqH3ILUA\n".
            "r/d5c8Vz+a5C4mLaP2GaKpUyvJ7pAVRlkW1lTFiwPbb4MvcS4Y8ExDLRAoGAZNO1\n".
            "Qm12Do0sABVc5h/EwewD8lEUe2uhCfpvKKFtx5kvM25AER0STkmOmdoJzD0xSO/K\n".
            "DsHz9VdcD8ffMvdMx9qjnS1mYVPAwCTxbh5PlMnNQzNIodB4UIUGXEKVfMKdajd9\n".
            "1OvxAg/cfZCsQ8W1a4FXVKZkt5mRzT63LN6PyAECgYBvWe6w3LlgXo2eegM9DAzs\n".
            "9m3SURr+ifHxCFH8ifaJE2aBo89UynI4nYZ0lOMAS/6uUkD30ZZUNG8XlP/BaiDo\n".
            "RBEaemcTM9mrpYqAFHauyugJdIsYlIf19X/Il+2Q18LuxWFfNr7jQjijDJ7GJYrv\n".
            "gBsSGBOjmWxmD4UznTJoKA==\n".
            '-----END PRIVATE KEY-----';

        $publicKeyPem = "-----BEGIN PUBLIC KEY-----\n".
            "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuBB3T6INUr3WbEbBkOSn\n".
            "Z8H/Gr/kDxYel6J0yqD7domChgJ4pNPdBF0/QrbLv4Uw4D4gvQrTPgpJOn5+e62F\n".
            "6pFnHK3JiyjQv9dkL0Y57urNRxDBC3W292qgsb+7tn3iFv86Ef0/18h97xkB8Dg6\n".
            "XAo4xc1JCJVX+T6FshZFthEArAWGHes8mPWBAwhG1B4KOgEf6UWz/LbNYycWJABA\n".
            "OtdwIbVZRjALIqHcgfGiPU+WApYmG2fDSIA7pTAhNvrx9fVuWjMuI6kM0PGBS+6i\n".
            "Ale8ULIlujH7uT+hzbHvmW+ZXoP+FfC/Zco4ThIpAnAXoN6UEVz5W/Np4cK7QfPW\n".
            "kQIDAQAB\n".
            '-----END PUBLIC KEY-----';

        config(['payments.telebirr.public_key' => $publicKeyPem]);
        $gateway = app(TelebirrGateway::class);

        $payload = json_encode([
            'out_trade_no' => 'SRMS-TB-RSA-8899',
            'total_amount' => 45000.0,
            'trade_status' => 'SUCCESS',
            'timestamp' => time(),
        ]);

        $rawSignature = '';
        openssl_sign($payload, $rawSignature, $privateKeyPem, OPENSSL_ALGO_SHA256);
        $base64Signature = base64_encode($rawSignature);

        // Verification assertions
        $this->assertTrue($gateway->verifyWebhookSignature($payload, $base64Signature));

        // Forged signature or tampered payload fails
        $tamperedPayload = json_encode([
            'out_trade_no' => 'SRMS-TB-RSA-8899',
            'total_amount' => 99999.0, // Tampered amount
            'trade_status' => 'SUCCESS',
        ]);
        $this->assertFalse($gateway->verifyWebhookSignature($tamperedPayload, $base64Signature));
        $this->assertFalse($gateway->verifyWebhookSignature($payload, 'invalid-signature'));
    }

    /**
     * HOLE 1: LedgerPostingService creates zero-sum balanced double-entry journals.
     */
    public function test_ledger_posting_service_enforces_zero_sum_balanced_double_entry(): void
    {
        $service = app(LedgerPostingService::class);

        $payment = Payment::create([
            'user_id' => $this->guest1->id,
            'payable_type' => AccommodationBooking::class,
            'payable_id' => '00000000-0000-0000-0000-000000000001',
            'amount' => 10000.0,
            'currency' => 'ETB',
            'payment_method' => 'telebirr',
            'status' => 'completed',
            'transaction_reference' => 'SRMS-TX-1001',
            'metadata' => [
                'split_distribution' => [
                    'host_escrow' => 8500.0,
                    'platform_fee' => 1200.0,
                    'tax' => 300.0,
                ],
            ],
        ]);

        $entry = $service->postPaymentSettlement($payment);

        $this->assertInstanceOf(LedgerEntry::class, $entry);

        $debits = round((float) LedgerEntryLine::where('ledger_entry_id', $entry->id)->where('type', 'debit')->sum('amount'), 2);
        $credits = round((float) LedgerEntryLine::where('ledger_entry_id', $entry->id)->where('type', 'credit')->sum('amount'), 2);

        $this->assertEquals(10000.0, $debits);
        $this->assertEquals(10000.0, $credits);
        $this->assertEquals($debits, $credits);

        // Test refund journal balancing
        $refund = Payment::create([
            'user_id' => $this->guest1->id,
            'payable_type' => AccommodationBooking::class,
            'payable_id' => '00000000-0000-0000-0000-000000000001',
            'amount' => -5000.0,
            'currency' => 'ETB',
            'payment_method' => 'telebirr',
            'status' => 'completed',
            'transaction_reference' => 'SRMS-REF-2002',
        ]);

        $refundEntry = $service->postRefund($refund, $payment, 5000.0);
        $refundDebits = round((float) LedgerEntryLine::where('ledger_entry_id', $refundEntry->id)->where('type', 'debit')->sum('amount'), 2);
        $refundCredits = round((float) LedgerEntryLine::where('ledger_entry_id', $refundEntry->id)->where('type', 'credit')->sum('amount'), 2);

        $this->assertEquals(5000.0, $refundDebits);
        $this->assertEquals(5000.0, $refundCredits);
    }

    /**
     * HOLE 1: Assert unbalanced manual ledger entry is rejected with DomainException.
     */
    public function test_ledger_posting_service_rejects_unbalanced_manual_entry(): void
    {
        $service = app(LedgerPostingService::class);

        $entry = LedgerEntry::create([
            'description' => 'Unbalanced Test Entry',
            'reference_id' => 'TEST-UNBALANCED',
            'entry_date' => now()->toDateString(),
        ]);

        $account = LedgerAccount::create([
            'code' => '9999',
            'name' => 'Suspense Account',
            'type' => 'Asset',
        ]);

        // Create unequal lines: Debit 1000, Credit 800
        LedgerEntryLine::create([
            'ledger_entry_id' => $entry->id,
            'ledger_account_id' => $account->id,
            'type' => 'debit',
            'amount' => 1000.0,
        ]);
        LedgerEntryLine::create([
            'ledger_entry_id' => $entry->id,
            'ledger_account_id' => $account->id,
            'type' => 'credit',
            'amount' => 800.0,
        ]);

        $this->expectException(DomainException::class);
        $service->assertBalanced($entry);
    }
}
