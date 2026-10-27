<?php

namespace Tests\Feature;

use App\Events\Offers\OfferAccepted;
use App\Listeners\OfferNegotiationListener;
use App\Models\Invoice;
use App\Models\Listing;
use App\Models\Offer;
use App\Models\Property;
use App\Models\User;
use App\Services\Offers\NegotiationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Module8OfferNegotiationsTest extends TestCase
{
    use RefreshDatabase;

    protected User $seller;

    protected User $buyer;

    protected Property $property;

    protected Listing $listing;

    protected NegotiationService $negotiationService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seller = User::factory()->create(['name' => 'Tirunesh Dibaba']);
        $this->buyer = User::factory()->create(['name' => 'Sileshi Sihine']);

        $this->property = Property::create([
            'title' => 'Kazanchis Executive Villa',
            'property_code' => 'KAZ-VIL-009',
            'owner_user_id' => $this->seller->id,
            'status' => 'verified',
        ]);

        $this->listing = Listing::create([
            'property_id' => $this->property->id,
            'owner_actor_id' => $this->seller->id,
            'purpose' => 'sale',
            'price' => 15000000.0,
            'status' => 'active',
        ]);

        $this->negotiationService = app(NegotiationService::class);
    }

    public function test_counter_offer_creates_audit_trail_in_offer_events(): void
    {
        // Initial offer submitted by buyer
        $offer = $this->negotiationService->submitOffer($this->buyer->id, [
            'listing_id' => $this->listing->id,
            'amount' => 14000000.0,
            'notes' => 'Initial cash offer',
        ]);

        $this->assertEquals('pending', $offer->status);

        // Seller counters with 14,500,000 ETB
        $counterOffer = $this->negotiationService->counterOffer(
            offer: $offer,
            actorId: $this->seller->id,
            counterAmount: 14500000.0,
            notes: 'Countering with minimal concession'
        );

        // Assert original offer updated to countered
        $this->assertEquals('countered', $offer->fresh()->status);
        $this->assertDatabaseHas('offer_events', [
            'offer_id' => $offer->id,
            'actor_id' => $this->seller->id,
            'event_type' => 'countered',
            'amount' => 14500000.0,
        ]);

        // Assert new counter offer created with parent linkage and audit record
        $this->assertEquals('pending', $counterOffer->status);
        $this->assertEquals($offer->id, $counterOffer->parent_offer_id);
        $this->assertEquals(14500000.0, (float) $counterOffer->amount);

        $this->assertDatabaseHas('offer_events', [
            'offer_id' => $counterOffer->id,
            'actor_id' => $this->seller->id,
            'event_type' => 'created',
            'amount' => 14500000.0,
        ]);
    }

    public function test_offer_acceptance_triggers_earnest_deposit_hold_invoice(): void
    {
        $offer = $this->negotiationService->submitOffer($this->buyer->id, [
            'listing_id' => $this->listing->id,
            'amount' => 15000000.0,
            'notes' => 'Full asking price offer',
        ]);

        // Seller accepts offer
        $acceptedOffer = $this->negotiationService->acceptOffer($offer, $this->seller->id);

        $this->assertEquals('accepted', $acceptedOffer->status);

        // Assert audit trail event logged
        $this->assertDatabaseHas('offer_events', [
            'offer_id' => $offer->id,
            'actor_id' => $this->seller->id,
            'event_type' => 'accepted',
            'amount' => 15000000.0,
        ]);

        // Trigger listener directly to verify earnest deposit hold invoice generation
        $listener = new OfferNegotiationListener;
        $listener->handle(new OfferAccepted($acceptedOffer, $this->seller->id));

        // 5% earnest deposit of 15,000,000 = 750,000 ETB
        $this->assertDatabaseHas('invoices', [
            'user_id' => $this->buyer->id,
            'amount' => 750000.0,
            'status' => 'pending',
        ]);
    }

    public function test_expire_offers_command_transitions_stale_offers(): void
    {
        // Active offer not yet expired
        $activeOffer = Offer::create([
            'listing_id' => $this->listing->id,
            'buyer_id' => $this->buyer->id,
            'amount' => 12000000.0,
            'status' => 'pending',
            'expires_at' => Carbon::now()->addDays(2),
        ]);

        // Stale offer past its expiration
        $staleOffer = Offer::create([
            'listing_id' => $this->listing->id,
            'buyer_id' => $this->buyer->id,
            'amount' => 11000000.0,
            'status' => 'pending',
            'expires_at' => Carbon::now()->subHour(),
        ]);

        $this->artisan('srms:expire-offers')
            ->expectsOutputToContain('Scanning for expired offers...')
            ->expectsOutputToContain('Successfully expired 1 offers.')
            ->assertExitCode(0);

        $this->assertEquals('expired', $staleOffer->fresh()->status);
        $this->assertEquals('pending', $activeOffer->fresh()->status);

        $this->assertDatabaseHas('offer_events', [
            'offer_id' => $staleOffer->id,
            'event_type' => 'expired',
        ]);
    }
}
