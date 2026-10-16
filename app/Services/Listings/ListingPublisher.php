<?php

namespace App\Services\Listings;

use App\Actions\PublishListing;
use App\Actions\UnpublishListing;
use App\Events\Listings\ListingPublished;
use App\Events\Listings\ListingUnpublished;
use App\Models\Listing;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ListingPublisher
{
    /**
     * Publish a listing (transition to published status and fire event).
     */
    public function publish(Listing $listing, ?string $triggeredBy = null): Listing
    {
        return DB::transaction(function () use ($listing, $triggeredBy) {
            // Validate before publishing
            if (empty($listing->price) || empty($listing->purpose)) {
                throw new \InvalidArgumentException('Mandatory fields missing for publishing');
            }

            // Use the PublishListing action for the core logic
            $action = new PublishListing();
            $publishedListing = $action->execute($listing);

            // Fire domain event
            event(new ListingPublished($publishedListing, $triggeredBy));

            return $publishedListing;
        });
    }

    /**
     * Unpublish a listing (remove from index and fire event).
     */
    public function unpublish(Listing $listing, ?string $triggeredBy = null, string $reason = 'manual'): Listing
    {
        return DB::transaction(function () use ($listing, $triggeredBy, $reason) {
            // Use the UnpublishListing action for the core logic
            $action = new UnpublishListing();
            $unpublishedListing = $action->execute($listing);

            // Fire domain event
            event(new ListingUnpublished($unpublishedListing, $triggeredBy, $reason));

            return $unpublishedListing;
        });
    }

    /**
     * Submit a listing for review.
     */
    public function submitForReview(Listing $listing): Listing
    {
        if (!in_array($listing->status, ['draft', 'rejected'])) {
            throw new \InvalidArgumentException('Only draft or rejected listings can be submitted');
        }

        if (empty($listing->price) || empty($listing->purpose)) {
            throw new \InvalidArgumentException('Mandatory fields missing for submission');
        }

        $listing->status = 'pending_review';
        $listing->save();

        return $listing;
    }

    /**
     * Bulk publish listings.
     */
    public function bulkPublish(Collection $listings, ?string $triggeredBy = null): Collection
    {
        return $listings->map(function ($listing) use ($triggeredBy) {
            return $this->publish($listing, $triggeredBy);
        });
    }

    /**
     * Bulk unpublish listings.
     */
    public function bulkUnpublish(Collection $listings, ?string $triggeredBy = null, string $reason = 'manual'): Collection
    {
        return $listings->map(function ($listing) use ($triggeredBy, $reason) {
            return $this->unpublish($listing, $triggeredBy, $reason);
        });
    }
}