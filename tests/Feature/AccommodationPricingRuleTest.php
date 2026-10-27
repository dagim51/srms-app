<?php

namespace Tests\Feature;

use App\Models\Accommodation;
use App\Models\AccommodationPricingRule;
use App\Models\Property;
use App\Models\User;
use App\Services\Bookings\BookingManager;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccommodationPricingRuleTest extends TestCase
{
    use RefreshDatabase;

    protected $host;

    protected $property;

    protected $accommodation;

    protected $bookingManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->host = User::factory()->create();

        $this->property = Property::create([
            'title' => 'Luxury Bole Apartment',
            'property_code' => 'BOLE-LUX-001',
            'owner_user_id' => $this->host->id,
            'status' => 'verified',
        ]);

        $this->accommodation = Accommodation::create([
            'property_id' => $this->property->id,
            'host_id' => $this->host->id,
            'max_guests' => 4,
        ]);

        $this->bookingManager = app(BookingManager::class);
    }

    public function test_evaluates_base_price_when_no_rules_exist(): void
    {
        // 3 nights at default 100 ETB/night
        $checkIn = Carbon::now()->addDays(2)->toDateString();
        $checkOut = Carbon::now()->addDays(5)->toDateString();

        $price = $this->bookingManager->calculatePrice($this->accommodation, $checkIn, $checkOut);

        $this->assertEquals(300.0, $price);
    }

    public function test_evaluates_seasonal_date_range_modifier(): void
    {
        // Create a seasonal peak rule between Day 4 and Day 6 (fixed 250 ETB/night)
        $seasonStart = Carbon::now()->addDays(4)->toDateString();
        $seasonEnd = Carbon::now()->addDays(6)->toDateString();

        AccommodationPricingRule::create([
            'accommodation_id' => $this->accommodation->id,
            'type' => 'seasonal',
            'start_date' => $seasonStart,
            'end_date' => $seasonEnd,
            'price_modifier_type' => 'fixed',
            'price_modifier_value' => 250.00,
            'priority' => 20,
        ]);

        // Book 4 nights: Day 3, Day 4, Day 5, Day 6 (leaving on Day 7)
        // Day 3: 100 ETB (outside season)
        // Day 4: 250 ETB (inside season)
        // Day 5: 250 ETB (inside season)
        // Day 6: 100 ETB (season ended on Day 6 check)
        $checkIn = Carbon::now()->addDays(3)->toDateString();
        $checkOut = Carbon::now()->addDays(7)->toDateString();

        $price = $this->bookingManager->calculatePrice($this->accommodation, $checkIn, $checkOut);

        // 100 (Day 3) + 250 (Day 4) + 250 (Day 5) + 100 (Day 6) = 700 ETB
        $this->assertEquals(700.0, $price);
    }

    public function test_evaluates_weekend_surcharge_rule(): void
    {
        // Find next Friday
        $nextFriday = Carbon::now()->next(Carbon::FRIDAY);
        $followingMonday = $nextFriday->copy()->addDays(3); // Fri, Sat, Sun nights

        // Rule: 50% surcharge on weekend
        AccommodationPricingRule::create([
            'accommodation_id' => $this->accommodation->id,
            'type' => 'weekend',
            'price_modifier_type' => 'percentage',
            'price_modifier_value' => 50.00,
            'priority' => 10,
        ]);

        $price = $this->bookingManager->calculatePrice(
            $this->accommodation,
            $nextFriday->toDateString(),
            $followingMonday->toDateString()
        );

        // Friday night: 150 ETB
        // Saturday night: 150 ETB
        // Sunday night: 100 ETB
        // Total = 400 ETB
        $this->assertEquals(400.0, $price);
    }

    public function test_detailed_price_breakdown_includes_fees_and_taxes(): void
    {
        // Weekday stay: Monday to Wednesday (2 nights)
        $nextMonday = Carbon::now()->next(Carbon::MONDAY);
        $checkIn = $nextMonday->toDateString();
        $checkOut = $nextMonday->copy()->addDays(2)->toDateString(); // 2 nights

        $breakdown = $this->bookingManager->calculateDetailedPrice(
            $this->accommodation,
            $checkIn,
            $checkOut,
            [
                'cleaning_fee' => 75.00,
                'service_fee_rate' => 0.10, // 10%
                'tax_rate' => 0.15, // 15% VAT
            ]
        );

        $this->assertEquals(2, $breakdown['nights']);
        $this->assertEquals(100.0, $breakdown['base_nightly_rate']);
        $this->assertEquals(200.0, $breakdown['stay_subtotal']);
        $this->assertEquals(75.0, $breakdown['cleaning_fee']);
        $this->assertEquals(20.0, $breakdown['service_fee']); // 10% of 200
        $this->assertEquals(33.0, $breakdown['tax_amount']); // 15% of (200 + 20)
        $this->assertEquals(328.0, $breakdown['total_price']);
        $this->assertCount(2, $breakdown['daily_breakdown']);
    }
}
