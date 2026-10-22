<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\Offer;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfferTest extends TestCase
{
    use RefreshDatabase;

    protected $owner;

    protected $buyer;

    protected $listing;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create();
        $this->buyer = User::factory()->create();

        $property = Property::create([
            'title' => 'Test Property for Offer',
            'property_code' => 'TEST-OFFER-001',
            'owner_user_id' => $this->owner->id,
            'status' => 'verified',
        ]);

        $this->listing = Listing::create([
            'property_id' => $property->id,
            'purpose' => 'For Sale',
            'price' => 5000000,
            'owner_actor_id' => $this->owner->id,
            'status' => 'published',
            'visibility' => 'public',
        ]);
    }

    public function test_buyer_can_submit_offer()
    {
        $response = $this->actingAs($this->buyer)->postJson('/api/v1/offers', [
            'listing_id' => $this->listing->id,
            'amount' => 4500000,
            'notes' => 'Valid for 24 hours',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.amount', 4500000)
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('offers', [
            'listing_id' => $this->listing->id,
            'buyer_id' => $this->buyer->id,
            'amount' => 4500000,
        ]);
    }

    public function test_owner_cannot_submit_offer_on_own_listing()
    {
        $response = $this->actingAs($this->owner)->postJson('/api/v1/offers', [
            'listing_id' => $this->listing->id,
            'amount' => 4500000,
        ]);

        $response->assertStatus(422);
    }

    public function test_owner_can_accept_offer()
    {
        $offer = Offer::create([
            'listing_id' => $this->listing->id,
            'buyer_id' => $this->buyer->id,
            'amount' => 4500000,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->owner)->patchJson("/api/v1/offers/{$offer->id}", [
            'status' => 'accepted',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('accepted', $offer->fresh()->status);
    }

    public function test_buyer_can_withdraw_offer()
    {
        $offer = Offer::create([
            'listing_id' => $this->listing->id,
            'buyer_id' => $this->buyer->id,
            'amount' => 4500000,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->buyer)->patchJson("/api/v1/offers/{$offer->id}", [
            'status' => 'withdrawn',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('withdrawn', $offer->fresh()->status);
    }

    public function test_buyer_cannot_accept_own_offer()
    {
        $offer = Offer::create([
            'listing_id' => $this->listing->id,
            'buyer_id' => $this->buyer->id,
            'amount' => 4500000,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->buyer)->patchJson("/api/v1/offers/{$offer->id}", [
            'status' => 'accepted',
        ]);

        $response->assertStatus(403);
    }
}
