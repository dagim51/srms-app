<?php

namespace Tests\Feature\DomainServices;

use App\Events\Listings\ListingApproved;
use App\Events\Listings\ListingRejected;
use App\Events\Listings\ListingSuspended;
use App\Events\Trust\DisputeOpened;
use App\Events\Trust\DisputeResolved;
use App\Models\Dispute;
use App\Models\Listing;
use App\Models\Property;
use App\Models\User;
use App\Services\Listings\ListingPublisher;
use App\Services\Trust\DisputeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ModerationAndDisputeDomainServicesTest extends TestCase
{
    use RefreshDatabase;

    protected ListingPublisher $listingPublisher;
    protected DisputeService $disputeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->listingPublisher = app(ListingPublisher::class);
        $this->disputeService = app(DisputeService::class);
    }

    public function test_listing_publisher_moderation_decisions_and_events(): void
    {
        Event::fake([
            ListingApproved::class,
            ListingRejected::class,
            ListingSuspended::class,
        ]);

        $owner = User::factory()->create();
        $moderator = User::factory()->create();

        $property = Property::create([
            'property_code' => 'PROP-MOD-01',
            'title' => 'Moderation Test Apartment',
            'owner_user_id' => $owner->id,
            'status' => 'verified',
        ]);

        $listing = Listing::create([
            'property_id' => $property->id,
            'purpose' => 'For Sale',
            'price' => 7500000,
            'owner_actor_id' => $owner->id,
            'status' => 'pending_review',
            'visibility' => 'public',
        ]);

        // 1. Approve
        $approvedListing = $this->listingPublisher->approve($listing, $moderator->id, 'Documentation verified.');
        $this->assertEquals('approved', $approvedListing->status);
        $this->assertDatabaseHas('listing_moderations', [
            'listing_id' => $listing->id,
            'moderator_id' => $moderator->id,
            'action' => 'approve',
        ]);

        Event::assertDispatched(ListingApproved::class, function ($e) use ($listing, $moderator) {
            return $e->listing->id === $listing->id && $e->moderatorId === $moderator->id;
        });

        // 2. Suspend
        $suspendedListing = $this->listingPublisher->suspend($listing, $moderator->id, 'Disputed ownership title.');
        $this->assertEquals('suspended', $suspendedListing->status);

        Event::assertDispatched(ListingSuspended::class, function ($e) use ($listing) {
            return $e->listing->id === $listing->id;
        });

        // 3. Reject
        $rejectedListing = $this->listingPublisher->reject($listing, $moderator->id, 'Fails KYC.');
        $this->assertEquals('rejected', $rejectedListing->status);

        Event::assertDispatched(ListingRejected::class, function ($e) use ($listing) {
            return $e->listing->id === $listing->id;
        });
    }

    public function test_dispute_service_lifecycle_and_events(): void
    {
        Event::fake([
            DisputeOpened::class,
            DisputeResolved::class,
        ]);

        $complainant = User::factory()->create();
        $respondent = User::factory()->create();
        $moderator = User::factory()->create();

        // 1. File Dispute
        $dispute = $this->disputeService->fileDispute($complainant->id, [
            'respondent_id' => $respondent->id,
            'subject' => 'Unauthorized Security Deposit Withholding',
            'description' => 'Landlord withheld 15,000 ETB for repainting walls that were in good state.',
            'amount_in_dispute' => 15000,
            'currency' => 'ETB',
            'type' => 'security_deposit',
            'priority' => 'high',
        ]);

        $this->assertInstanceOf(Dispute::class, $dispute);
        $this->assertEquals('open', $dispute->status);

        Event::assertDispatched(DisputeOpened::class, function ($e) use ($dispute) {
            return $e->dispute->id === $dispute->id;
        });

        // 2. Assign Moderator
        $assignedDispute = $this->disputeService->assignModerator($dispute, $moderator->id);
        $this->assertEquals('under_review', $assignedDispute->status);
        $this->assertEquals($moderator->id, $assignedDispute->assigned_moderator_id);

        // 3. Resolve Dispute
        $resolvedDispute = $this->disputeService->resolveDispute(
            $dispute,
            $moderator->id,
            'refund_full',
            'Inspection report confirms wear and tear. Full deposit refund ordered.'
        );

        $this->assertEquals('resolved', $resolvedDispute->status);
        $this->assertEquals('refund_full', $resolvedDispute->resolution);

        Event::assertDispatched(DisputeResolved::class, function ($e) use ($dispute, $moderator) {
            return $e->dispute->id === $dispute->id && $e->resolverId === $moderator->id;
        });
    }
}
