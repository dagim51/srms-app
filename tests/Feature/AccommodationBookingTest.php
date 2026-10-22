<?php

namespace Tests\Feature;

use App\Models\Accommodation;
use App\Models\AccommodationBooking;
use App\Models\AccommodationPricingRule;
use App\Models\Property;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccommodationBookingTest extends TestCase
{
    use RefreshDatabase;

    protected $host;

    protected $guest;

    protected $property;

    protected $accommodation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->host = User::factory()->create();
        $this->guest = User::factory()->create();

        $this->property = Property::create([
            'title' => 'Test Booking Property',
            'property_code' => 'TEST-BOOK-001',
            'owner_user_id' => $this->host->id,
            'status' => 'verified',
        ]);

        $this->accommodation = Accommodation::create([
            'property_id' => $this->property->id,
            'host_id' => $this->host->id,
            'max_guests' => 4,
        ]);
    }

    public function test_guest_can_book_accommodation()
    {
        $checkIn = Carbon::now()->addDays(5)->format('Y-m-d');
        $checkOut = Carbon::now()->addDays(7)->format('Y-m-d'); // 2 nights

        $response = $this->actingAs($this->guest)->postJson('/api/v1/accommodation-bookings', [
            'accommodation_id' => $this->accommodation->id,
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'guests' => 2,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.total_price', 200); // 2 nights * 100

        $this->assertDatabaseHas('accommodation_bookings', [
            'accommodation_id' => $this->accommodation->id,
            'user_id' => $this->guest->id,
        ]);
    }

    public function test_booking_calculates_price_with_rules()
    {
        AccommodationPricingRule::create([
            'accommodation_id' => $this->accommodation->id,
            'type' => 'special',
            'price_modifier_type' => 'percentage',
            'price_modifier_value' => 20, // 20% surcharge
            'priority' => 10,
        ]);

        $checkIn = Carbon::now()->addDays(5)->format('Y-m-d');
        $checkOut = Carbon::now()->addDays(7)->format('Y-m-d'); // 2 nights

        $response = $this->actingAs($this->guest)->postJson('/api/v1/accommodation-bookings', [
            'accommodation_id' => $this->accommodation->id,
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'guests' => 2,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.total_price', 240); // 200 * 1.2
    }

    public function test_host_cannot_book_own_accommodation()
    {
        $response = $this->actingAs($this->host)->postJson('/api/v1/accommodation-bookings', [
            'accommodation_id' => $this->accommodation->id,
            'check_in_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
            'check_out_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'guests' => 2,
        ]);

        $response->assertStatus(422);
    }

    public function test_cannot_exceed_max_guests()
    {
        $response = $this->actingAs($this->guest)->postJson('/api/v1/accommodation-bookings', [
            'accommodation_id' => $this->accommodation->id,
            'check_in_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
            'check_out_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'guests' => 5, // max is 4
        ]);

        $response->assertStatus(422);
    }

    public function test_overlapping_bookings_are_prevented()
    {
        AccommodationBooking::create([
            'accommodation_id' => $this->accommodation->id,
            'user_id' => $this->guest->id,
            'check_in_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
            'check_out_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
            'guests' => 2,
            'total_price' => 500,
            'status' => 'confirmed',
        ]);

        $otherGuest = User::factory()->create();

        $response = $this->actingAs($otherGuest)->postJson('/api/v1/accommodation-bookings', [
            'accommodation_id' => $this->accommodation->id,
            'check_in_date' => Carbon::now()->addDays(8)->format('Y-m-d'),
            'check_out_date' => Carbon::now()->addDays(12)->format('Y-m-d'),
            'guests' => 2,
        ]);

        $response->assertStatus(409); // Conflict
    }

    public function test_host_can_confirm_booking()
    {
        $booking = AccommodationBooking::create([
            'accommodation_id' => $this->accommodation->id,
            'user_id' => $this->guest->id,
            'check_in_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
            'check_out_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
            'guests' => 2,
            'total_price' => 500,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->host)->patchJson("/api/v1/accommodation-bookings/{$booking->id}", [
            'status' => 'confirmed',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('confirmed', $booking->fresh()->status);
    }
}
