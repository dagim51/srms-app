<?php

namespace App\Listeners;

use App\Events\Listings\ListingApproved;
use App\Events\Listings\ListingRejected;
use App\Events\Listings\ListingSuspended;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class ListingModerationListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        if ($event instanceof ListingApproved) {
            Log::info("Listing [{$event->listing->id}] approved by moderator [{$event->moderatorId}].");
        } elseif ($event instanceof ListingRejected) {
            Log::info("Listing [{$event->listing->id}] rejected by moderator [{$event->moderatorId}]. Reason: {$event->comments}");
        } elseif ($event instanceof ListingSuspended) {
            Log::info("Listing [{$event->listing->id}] suspended by moderator [{$event->moderatorId}]. Reason: {$event->comments}");
        }
    }
}
