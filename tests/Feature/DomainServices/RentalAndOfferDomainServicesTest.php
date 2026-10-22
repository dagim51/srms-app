<?php

namespace Tests\Feature\DomainServices;

use App\Events\Offers\OfferAccepted;
use App\Events\Offers\OfferCountered;
use App\Events\Offers\OfferRejected;
use App\Events\Offers\OfferSubmitted;
use App\Events\Offers\OfferWithdrawn;
use App\Events\Rentals\LeaseActivated;
use App\Events\Rentals\RentalApplicationApproved;
use App\Events\Rentals\RentalApplicationSubmitted;
use App\Models\Listing;
use App\Models\Offer;
use App\Models\Property;
use App\Models\RentalApplication;
use App\Models\TenantProfile;
use App\Models\User;
use App\Services\Offers\NegotiationService;
use App\Services\Rentals\LeaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class RentalAndOfferDomainServicesTest extends TestCase
{
    use RefreshDatabase;

    protected LeaseService $leaseService;

    protected NegotiationService $negotiationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->leaseService = app(LeaseService::class);
        $this->negotiationService = app(NegotiationService::class);
    }

    public function test_lease_service_lifecycle_and_domain_events(): void
    {
        Event::fake([
            RentalApplicationSubmitted::class,
            RentalApplicationApproved::class,
            LeaseActivated::class,
        ]);

        $tenant = User::factory()->create();
        $property = Property::create([
            'property_code' => 'PROP-SERV-01',
            'title' => 'Service Lease Test Apartment',
            'status' => 'active',
        ]);

        // 1. Profile creation
        $profile = $this->leaseService->storeTenantProfile($tenant->id, [
            'employment_status' => 'Software Engineer',
            'annual_income' => 1200000,
        ]);
        $this->assertInstanceOf(TenantProfile::class, $profile);
        $this->assertEquals($tenant->id, $profile->user_id);

        // 2. Application submission
        $application = $this->leaseService->submitApplication($tenant->id, [
            'property_id' => $property->id,
            'proposed_rent' => 35000,
            'message' => 'Looking forward to moving in.',
        ]);
        $this->assertInstanceOf(RentalApplication::class, $application);
        $this->assertEquals('pending', $application->status);

        Event::assertDispatched(RentalApplicationSubmitted::class, function ($event) use ($application) {
            return $event->application->id === $application->id;
        });

        // 3. Application approval & lease activation
        $lease = $this->leaseService->approveApplication($application, [
            'start_date' => '2026-11-01',
            'end_date' => '2027-11-01',
            'rent_amount' => 35000,
            'deposit_amount' => 70000,
        ]);

        $this->assertEquals('active', $lease->status);
        $this->assertEquals('approved', $application->fresh()->status);
        $this->assertCount(12, $lease->schedules);

        Event::assertDispatched(RentalApplicationApproved::class, function ($event) use ($application, $lease) {
            return $event->application->id === $application->id && $event->lease->id === $lease->id;
        });

        Event::assertDispatched(LeaseActivated::class, function ($event) use ($lease) {
            return $event->lease->id === $lease->id;
        });
    }

    public function test_negotiation_service_lifecycle_and_domain_events(): void
    {
        Event::fake([
            OfferSubmitted::class,
            OfferAccepted::class,
            OfferRejected::class,
            OfferWithdrawn::class,
            OfferCountered::class,
        ]);

        $owner = User::factory()->create();
        $buyer = User::factory()->create();

        $property = Property::create([
            'property_code' => 'PROP-NEG-01',
            'title' => 'Negotiation Test Villa',
            'owner_user_id' => $owner->id,
            'status' => 'verified',
        ]);

        $listing = Listing::create([
            'property_id' => $property->id,
            'purpose' => 'For Sale',
            'price' => 10000000,
            'owner_actor_id' => $owner->id,
            'status' => 'published',
            'visibility' => 'public',
        ]);

        // 1. Submit Offer
        $offer = $this->negotiationService->submitOffer($buyer->id, [
            'listing_id' => $listing->id,
            'amount' => 9500000,
            'notes' => 'Cash offer, quick close.',
        ]);

        $this->assertInstanceOf(Offer::class, $offer);
        $this->assertEquals('pending', $offer->status);
        $this->assertDatabaseHas('offer_events', [
            'offer_id' => $offer->id,
            'actor_id' => $buyer->id,
            'event_type' => 'created',
        ]);

        Event::assertDispatched(OfferSubmitted::class, function ($event) use ($offer) {
            return $event->offer->id === $offer->id;
        });

        // 2. Counter Offer
        $counterOffer = $this->negotiationService->counterOffer($offer, $owner->id, 9800000, 'Lowest acceptable price.');
        $this->assertEquals('countered', $offer->fresh()->status);
        $this->assertEquals('pending', $counterOffer->status);
        $this->assertEquals($offer->id, $counterOffer->parent_offer_id);

        Event::assertDispatched(OfferCountered::class, function ($event) use ($counterOffer, $offer) {
            return $event->counterOffer->id === $counterOffer->id && $event->originalOffer->id === $offer->id;
        });

        // 3. Accept Offer
        $acceptedOffer = $this->negotiationService->acceptOffer($counterOffer, $owner->id);
        $this->assertEquals('accepted', $acceptedOffer->status);

        Event::assertDispatched(OfferAccepted::class, function ($event) use ($counterOffer) {
            return $event->offer->id === $counterOffer->id;
        });
    }
}
