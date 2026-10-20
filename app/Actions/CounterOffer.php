<?php

namespace App\Actions;

use App\Models\Offer;
use App\Models\OfferEvent;

class CounterOffer extends Action
{
    /**
     * Counter an offer by marking original as countered and creating a new offer in the chain.
     */
    public function execute(Offer $offer, string $actorId, float $counterAmount, ?string $notes = null): Offer
    {
        return $this->transactional(function () use ($offer, $actorId, $counterAmount, $notes) {
            $offer->update(['status' => 'countered']);

            OfferEvent::create([
                'offer_id' => $offer->id,
                'actor_id' => $actorId,
                'event_type' => 'countered',
                'amount' => $counterAmount,
                'currency' => 'ETB',
                'notes' => $notes ?? 'Counteroffer proposed.',
            ]);

            $newOffer = Offer::create([
                'listing_id' => $offer->listing_id,
                'buyer_id' => $offer->buyer_id,
                'parent_offer_id' => $offer->id,
                'amount' => $counterAmount,
                'status' => 'pending',
                'notes' => $notes,
                'expires_at' => now()->addDays(7),
            ]);

            OfferEvent::create([
                'offer_id' => $newOffer->id,
                'actor_id' => $actorId,
                'event_type' => 'created',
                'amount' => $newOffer->amount,
                'currency' => 'ETB',
                'notes' => $notes ?? 'Counteroffer created.',
            ]);

            return $newOffer;
        });
    }
}
