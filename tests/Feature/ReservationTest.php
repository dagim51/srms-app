<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Property;
use App\Models\Listing;
use App\Models\Reservation;

class ReservationTest extends TestCase
{
    use RefreshDatabase;

    protected $owner;
    protected $requester;
    protected $listing;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->owner = User::factory()->create();
        $this->requester = User::factory()->create();
        
        $property = Property::create([
            'title' => 'Test Property for Reservation',
            'property_code' => 'TEST-RES-001',
            'owner_user_id' => $this->owner->id,
            'status' => 'verified',
        ]);
        
        $this->listing = Listing::create([
            'property_id' => $property->id,
            'purpose' => 'For Rent',
            'price' => 15000,
            'owner_actor_id' => $this->owner->id,
            'status' => 'published',
            'visibility' => 'public',
        ]);
    }

    public function test_user_can_submit_reservation_request()
    {
        $response = $this->actingAs($this->requester)->postJson('/api/v1/reservations', [
            'listing_id' => $this->listing->id,
            'notes' => 'I want to reserve this.',
        ]);
        
        $response->assertStatus(201)
                 ->assertJsonPath('data.status', 'pending');
                 
        $this->assertDatabaseHas('reservations', [
            'listing_id' => $this->listing->id,
            'user_id' => $this->requester->id,
            'status' => 'pending',
        ]);
    }

    public function test_conflicting_reservations_are_prevented()
    {
        Reservation::create([
            'listing_id' => $this->listing->id,
            'user_id' => $this->requester->id,
            'status' => 'pending',
            'expires_at' => now()->addDays(3),
        ]);
        
        $otherUser = User::factory()->create();
        
        $response = $this->actingAs($otherUser)->postJson('/api/v1/reservations', [
            'listing_id' => $this->listing->id,
        ]);
        
        $response->assertStatus(409); // Conflict
    }

    public function test_owner_can_confirm_reservation()
    {
        $reservation = Reservation::create([
            'listing_id' => $this->listing->id,
            'user_id' => $this->requester->id,
            'status' => 'pending',
            'expires_at' => now()->addDays(3),
        ]);
        
        $response = $this->actingAs($this->owner)->patchJson("/api/v1/reservations/{$reservation->id}", [
            'status' => 'confirmed',
        ]);
        
        $response->assertStatus(200);
        $this->assertEquals('confirmed', $reservation->fresh()->status);
    }

    public function test_owner_cannot_reserve_own_listing()
    {
        $response = $this->actingAs($this->owner)->postJson('/api/v1/reservations', [
            'listing_id' => $this->listing->id,
        ]);
        
        $response->assertStatus(422);
    }
}
