<?php

namespace App\Console\Commands;

use App\Jobs\IndexListingJob;
use App\Models\Listing;
use App\Models\ListingSearchIndex;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('search:reindex')]
#[Description('Rebuild the search index from authoritative DB data')]
class ReindexListingsCommand extends Command
{
    public function handle()
    {
        $this->info('Starting full re-index of published listings...');

        ListingSearchIndex::truncate();

        $listings = Listing::where('status', 'published')->get();
        $count = $listings->count();

        foreach ($listings as $listing) {
            IndexListingJob::dispatch($listing->id);
        }

        $this->info("Queued {$count} listings for indexing.");
    }
}
