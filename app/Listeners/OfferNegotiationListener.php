<?php

namespace App\Listeners;

use App\Events\Offers\OfferAccepted;
use App\Events\Offers\OfferCountered;
use App\Events\Offers\OfferRejected;
use App\Events\Offers\OfferSubmitted;
use App\Events\Offers\OfferWithdrawn;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class OfferNegotiationListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        if ($event instanceof OfferSubmitted) {
            Log::info("Offer submitted [{$event->offer->id}] on listing [{$event->offer->listing_id}] by buyer [{$event->offer->buyer_id}].");
        } elseif ($event instanceof OfferAccepted) {
            Log::info("Offer accepted [{$event->offer->id}] by actor [{$event->actorId}].");
        } elseif ($event instanceof OfferRejected) {
            Log::info("Offer rejected [{$event->offer->id}] by actor [{$event->actorId}].");
        } elseif ($event instanceof OfferWithdrawn) {
            Log::info("Offer withdrawn [{$event->offer->id}] by actor [{$event->actorId}].");
        } elseif ($event instanceof OfferCountered) {
            Log::info("Offer [{$event->originalOffer->id}] countered by actor [{$event->actorId}] with new offer [{$event->counterOffer->id}].");
        }
    }
}
