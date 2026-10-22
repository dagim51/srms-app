<?php

namespace App\Listeners;

use App\Events\Listings\ListingUnpublished;
use App\Models\ListingSearchIndex;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ListingUnpublishedListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(ListingUnpublished $event): void
    {
        ListingSearchIndex::where('listing_id', $event->listing->id)->delete();
    }
}
