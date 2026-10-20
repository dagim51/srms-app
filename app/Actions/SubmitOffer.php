<?php

namespace App\Actions;

use App\Models\Offer;
use App\Models\OfferEvent;

class SubmitOffer extends Action
{
    /**
     * Submit an offer and create audit event.
     */
    public function execute(string $buyerId, array $data): Offer
    {
        return $this->transactional(function () use ($buyerId, $data) {
            $offer = Offer::create([
                'listing_id' => $data['listing_id'],
                'buyer_id' => $buyerId,
                'amount' => $data['amount'],
                'notes' => $data['notes'] ?? null,
                'parent_offer_id' => $data['parent_offer_id'] ?? null,
                'status' => 'pending',
                'expires_at' => $data['expires_at'] ?? now()->addDays(7),
            ]);

            OfferEvent::create([
                'offer_id' => $offer->id,
                'actor_id' => $buyerId,
                'event_type' => 'created',
                'amount' => $offer->amount,
                'currency' => 'ETB',
                'notes' => $offer->notes,
            ]);

            return $offer;
        });
    }
}
