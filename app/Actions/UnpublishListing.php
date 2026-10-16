<?php

namespace App\Actions;

use App\Models\Listing;

class UnpublishListing extends Action
{
    /**
     * Execute the unpublish action.
     */
    public function execute(Listing $listing): Listing
    {
        return $this->transactional(function () use ($listing) {
            $listing->status = 'paused';
            $listing->published_at = null;
            $listing->save();

            return $listing->fresh();
        });
    }
}