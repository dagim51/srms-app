<?php

namespace App\Console\Commands;

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
        
        \App\Models\ListingSearchIndex::truncate();

        $listings = \App\Models\Listing::where('status', 'published')->get();
        $count = $listings->count();

        foreach ($listings as $listing) {
            \App\Jobs\IndexListingJob::dispatch($listing->id);
        }

        $this->info("Queued {$count} listings for indexing.");
    }
}
