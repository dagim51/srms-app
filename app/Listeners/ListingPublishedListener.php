<?php

namespace App\Listeners;

use App\Events\Listings\ListingPublished;
use App\Jobs\IndexListingJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ListingPublishedListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(ListingPublished $event): void
    {
        IndexListingJob::dispatch($event->listing->id);
    }
}
