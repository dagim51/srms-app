<?php

namespace App\Actions;

use App\Models\Listing;

class PublishListing extends Action
{
    /**
     * Execute the publish action.
     */
    public function execute(Listing $listing): Listing
    {
        return $this->transactional(function () use ($listing) {
            $listing->status = 'published';
            $listing->published_at = now();
            $listing->save();

            return $listing->fresh();
        });
    }
}
