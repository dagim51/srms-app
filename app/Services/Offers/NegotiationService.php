<?php

namespace App\Services\Offers;

use App\Actions\CounterOffer;
use App\Actions\SubmitOffer;
use App\Events\Offers\OfferAccepted;
use App\Events\Offers\OfferCountered;
use App\Events\Offers\OfferRejected;
use App\Events\Offers\OfferSubmitted;
use App\Events\Offers\OfferWithdrawn;
use App\Models\Listing;
use App\Models\Offer;
use App\Models\OfferEvent;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class NegotiationService
{
    /**
     * Submit an initial offer for a listing.
     */
    public function submitOffer(string $buyerId, array $data): Offer
    {
        $listing = Listing::findOrFail($data['listing_id']);

        if ($listing->owner_actor_id === $buyerId) {
            throw new InvalidArgumentException('You cannot make an offer on your own listing.');
        }

        return DB::transaction(function () use ($buyerId, $data) {
            $action = new SubmitOffer();
            $offer = $action->execute($buyerId, $data);

            event(new OfferSubmitted($offer));

            return $offer;
        });
    }

    /**
     * Accept a pending offer.
     */
    public function acceptOffer(Offer $offer, string $actorId): Offer
    {
        if ($offer->status !== 'pending') {
            throw new InvalidArgumentException('Can only update pending offers.');
        }

        $listing = $offer->listing;
        if ($listing->owner_actor_id !== $actorId) {
            throw new AuthorizationException('Unauthorized.');
        }

        return DB::transaction(function () use ($offer, $actorId) {
            $offer->update(['status' => 'accepted']);

            OfferEvent::create([
                'offer_id' => $offer->id,
                'actor_id' => $actorId,
                'event_type' => 'accepted',
                'amount' => $offer->amount,
                'currency' => 'ETB',
                'notes' => 'Offer status changed to accepted.',
            ]);

            event(new OfferAccepted($offer, $actorId));

            return $offer;
        });
    }

    /**
     * Reject a pending offer.
     */
    public function rejectOffer(Offer $offer, string $actorId): Offer
    {
        if ($offer->status !== 'pending') {
            throw new InvalidArgumentException('Can only update pending offers.');
        }

        $listing = $offer->listing;
        if ($listing->owner_actor_id !== $actorId) {
            throw new AuthorizationException('Unauthorized.');
        }

        return DB::transaction(function () use ($offer, $actorId) {
            $offer->update(['status' => 'rejected']);

            OfferEvent::create([
                'offer_id' => $offer->id,
                'actor_id' => $actorId,
                'event_type' => 'rejected',
                'amount' => $offer->amount,
                'currency' => 'ETB',
                'notes' => 'Offer status changed to rejected.',
            ]);

            event(new OfferRejected($offer, $actorId));

            return $offer;
        });
    }

    /**
     * Withdraw an offer (buyer action).
     */
    public function withdrawOffer(Offer $offer, string $actorId): Offer
    {
        if ($offer->status !== 'pending') {
            throw new InvalidArgumentException('Can only update pending offers.');
        }

        if ($offer->buyer_id !== $actorId) {
            throw new AuthorizationException('Unauthorized.');
        }

        return DB::transaction(function () use ($offer, $actorId) {
            $offer->update(['status' => 'withdrawn']);

            OfferEvent::create([
                'offer_id' => $offer->id,
                'actor_id' => $actorId,
                'event_type' => 'withdrawn',
                'amount' => $offer->amount,
                'currency' => 'ETB',
                'notes' => 'Offer status changed to withdrawn.',
            ]);

            event(new OfferWithdrawn($offer, $actorId));

            return $offer;
        });
    }

    /**
     * Counter an offer in the negotiation chain.
     */
    public function counterOffer(Offer $offer, string $actorId, float $counterAmount, ?string $notes = null): Offer
    {
        $isOwner = $offer->listing->owner_actor_id === $actorId;
        $isBuyer = $offer->buyer_id === $actorId;

        if (!$isOwner && !$isBuyer) {
            throw new AuthorizationException('Unauthorized to counter this offer.');
        }

        return DB::transaction(function () use ($offer, $actorId, $counterAmount, $notes) {
            $action = new CounterOffer();
            $counterOffer = $action->execute($offer, $actorId, $counterAmount, $notes);

            event(new OfferCountered($counterOffer, $offer, $actorId));

            return $counterOffer;
        });
    }
}
