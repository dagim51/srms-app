<?php

namespace App\Jobs;

use App\Models\ListingSearchIndex;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RemoveListingIndexJob implements ShouldQueue
{
    use Queueable;

    public $listingId;

    public function __construct(string $listingId)
    {
        $this->listingId = $listingId;
    }

    public function handle(): void
    {
        ListingSearchIndex::where('listing_id', $this->listingId)->delete();
    }
}
