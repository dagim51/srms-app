<?php

namespace Tests\Feature;

use App\Models\Accommodation;
use App\Models\AccommodationAvailability;
use App\Models\AccommodationBooking;
use App\Models\AccommodationPricingRule;
use App\Models\BookingGuest;
use App\Models\CancellationPolicy;
use App\Models\CancellationRecord;
use App\Models\CheckInOutRecord;
use App\Models\Property;
use App\Models\User;
use App\Services\Bookings\BookingManager;
use App\Services\Bookings\CancellationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Module5StayOperationsTest extends TestCase
{
    use RefreshDatabase;

    protected User $host;

    protected User $guest;

    protected User $staff;

    protected Property $property;

    protected Accommodation $accommodation;

    protected BookingManager $bookingManager;

    protected CancellationService $cancellationService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->host = User::factory()->create();
        $this->guest = User::factory()->create();
        $this->staff = User::factory()->create();

        $this->property = Property::create([
            'title' => 'Bole Boutique Stay',
            'property_code' => 'BOLE-STAY-001',
            'owner_user_id' => $this->host->id,
            'status' => 'verified',
        ]);

        $this->accommodation = Accommodation::create([
            'property_id' => $this->property->id,
            'host_id' => $this->host->id,
            'max_guests' => 4,
        ]);

        $this->bookingManager = app(BookingManager::class);
        $this->cancellationService = app(CancellationService::class);
    }

    public function test_dynamic_pricing_with_weekend_and_seasonal_rules(): void
    {
        // Default base rate is 100 ETB
        // Add seasonal peak rule: +50 ETB from days +2 to +4
        $startDate = Carbon::now()->addDays(2)->toDateString();
        $endDate = Carbon::now()->addDays(4)->toDateString();

        AccommodationPricingRule::create([
            'accommodation_id' => $this->accommodation->id,
            'type' => 'seasonal',
            'price_modifier_type' => 'fixed',
            'price_modifier_value' => 150.00,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'priority' => 10,
        ]);

        // 2 nights within seasonal range (startDate to endDate)
        $totalPrice = $this->bookingManager->calculatePrice(
            $this->accommodation,
            $startDate,
            $endDate
        );

        // 2 nights * (100 base + 50 surge) = 300 ETB
        $this->assertEquals(300.00, $totalPrice);
    }

    public function test_cancellation_within_flexible_window_grants_full_refund(): void
    {
        $policy = CancellationPolicy::create([
            'name' => 'Flexible 48h',
            'flexible_days' => 2,
            'refund_percentage' => 100.00,
            'fee_percentage' => 0.00,
            'description' => 'Full refund up to 2 days prior to check-in.',
        ]);

        $checkIn = Carbon::now()->addDays(5)->toDateString();
        $checkOut = Carbon::now()->addDays(8)->toDateString();

        $booking = AccommodationBooking::create([
            'accommodation_id' => $this->accommodation->id,
            'user_id' => $this->guest->id,
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'guests' => 2,
            'total_price' => 450.00,
            'status' => 'confirmed',
        ]);

        // Availability block created during booking
        AccommodationAvailability::create([
            'accommodation_id' => $this->accommodation->id,
            'start_date' => $checkIn,
            'end_date' => $checkOut,
            'status' => 'booked',
        ]);

        $record = $this->cancellationService->cancel(
            $booking,
            $this->guest->id,
            'Flight cancelled',
            $policy
        );

        $this->assertInstanceOf(CancellationRecord::class, $record);
        $this->assertEquals('processed', $record->status);
        $this->assertEquals(450.00, (float) $record->refund_amount);
        $this->assertEquals(0.00, (float) $record->fee_amount);
        $this->assertEquals('cancelled', $booking->fresh()->status);

        // Verify availability block released
        $remainingBlocks = AccommodationAvailability::where('accommodation_id', $this->accommodation->id)
            ->where('start_date', $checkIn)
            ->where('end_date', $checkOut)
            ->count();
        $this->assertEquals(0, $remainingBlocks);

        // Verify relationship on booking
        $this->assertNotNull($booking->fresh()->cancellationRecord);
        $this->assertEquals($record->id, $booking->fresh()->cancellationRecord->id);
    }

    public function test_cancellation_outside_window_applies_cancellation_fee(): void
    {
        $policy = CancellationPolicy::create([
            'name' => 'Strict 7-Day',
            'flexible_days' => 7,
            'refund_percentage' => 100.00,
            'fee_percentage' => 50.00,
            'description' => '50% fee if cancelled within 7 days.',
        ]);

        // Check-in in 3 days (outside the 7-day flexible window)
        $checkIn = Carbon::now()->addDays(3)->toDateString();
        $checkOut = Carbon::now()->addDays(5)->toDateString();

        $booking = AccommodationBooking::create([
            'accommodation_id' => $this->accommodation->id,
            'user_id' => $this->guest->id,
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'guests' => 2,
            'total_price' => 500.00,
            'status' => 'confirmed',
        ]);

        $record = $this->cancellationService->cancel(
            $booking,
            $this->guest->id,
            'Emergency change of plan',
            $policy
        );

        $this->assertEquals(250.00, (float) $record->fee_amount);
        $this->assertEquals(250.00, (float) $record->refund_amount);
        $this->assertEquals('cancelled', $booking->fresh()->status);
    }

    public function test_booking_guest_roster_tracking(): void
    {
        $booking = AccommodationBooking::create([
            'accommodation_id' => $this->accommodation->id,
            'user_id' => $this->guest->id,
            'check_in_date' => Carbon::now()->addDays(1)->toDateString(),
            'check_out_date' => Carbon::now()->addDays(3)->toDateString(),
            'guests' => 2,
            'total_price' => 200.00,
            'status' => 'confirmed',
        ]);

        $guest1 = BookingGuest::create([
            'booking_id' => $booking->id,
            'full_name' => 'Abebe Bikila',
            'id_passport_number' => 'ETH-PASS-98765',
            'is_primary' => true,
            'nationality' => 'Ethiopian',
            'phone' => '+251911223344',
        ]);

        $guest2 = BookingGuest::create([
            'booking_id' => $booking->id,
            'full_name' => 'Derartu Tulu',
            'id_passport_number' => 'ETH-PASS-54321',
            'is_primary' => false,
            'nationality' => 'Ethiopian',
            'phone' => '+251922334455',
        ]);

        $this->assertCount(2, $booking->fresh()->bookingGuests);
        $this->assertCount(2, $booking->fresh()->guestsRoster);
        $this->assertTrue($guest1->booking->is($booking));
        $this->assertTrue($booking->bookingGuests->firstWhere('is_primary', true)->is($guest1));
    }

    public function test_check_in_and_check_out_key_handover_operations(): void
    {
        $booking = AccommodationBooking::create([
            'accommodation_id' => $this->accommodation->id,
            'user_id' => $this->guest->id,
            'check_in_date' => Carbon::now()->toDateString(),
            'check_out_date' => Carbon::now()->addDays(2)->toDateString(),
            'guests' => 1,
            'total_price' => 200.00,
            'status' => 'confirmed',
        ]);

        // Check-in operation
        $checkInRecord = CheckInOutRecord::create([
            'booking_id' => $booking->id,
            'direction' => 'check_in',
            'action_time' => Carbon::now(),
            'staff_id' => $this->staff->id,
            'room_condition_notes' => 'Room inspected, pristine condition. Keycard #12 handed over.',
            'key_handed_over' => true,
        ]);

        // Check-out operation
        $checkOutRecord = CheckInOutRecord::create([
            'booking_id' => $booking->id,
            'direction' => 'check_out',
            'action_time' => Carbon::now()->addDays(2),
            'staff_id' => $this->staff->id,
            'room_condition_notes' => 'Keycard returned. No damages.',
            'key_handed_over' => false,
        ]);

        $records = $booking->fresh()->checkInOutRecords;
        $this->assertCount(2, $records);
        $this->assertEquals('check_in', $records->first()->direction);
        $this->assertTrue($records->first()->key_handed_over);
        $this->assertEquals('check_out', $records->last()->direction);
        $this->assertFalse($records->last()->key_handed_over);
        $this->assertTrue($checkInRecord->staff->is($this->staff));
    }
}
