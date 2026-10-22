<?php

namespace App\Actions;

use App\Models\Inquiry;
use App\Models\Listing;

class CreateInquiry extends Action
{
    /**
     * Execute the create inquiry action.
     */
    public function execute(Listing $listing, string $requesterId, ?string $message = null): Inquiry
    {
        return $this->transactional(function () use ($listing, $requesterId, $message) {
            $inquiry = Inquiry::create([
                'listing_id' => $listing->id,
                'property_id' => $listing->property_id,
                'requester_id' => $requesterId,
                'seller_agent_id' => $listing->owner_actor_id,
                'status' => 'pending',
                'message' => $message,
            ]);

            return $inquiry;
        });
    }
}
