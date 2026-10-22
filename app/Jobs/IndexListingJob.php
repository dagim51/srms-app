<?php

namespace App\Jobs;

use App\Models\Listing;
use App\Models\ListingSearchIndex;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class IndexListingJob implements ShouldQueue
{
    use Queueable;

    public $listingId;

    public function __construct(string $listingId)
    {
        $this->listingId = $listingId;
    }

    public function handle(): void
    {
        $listing = Listing::with(['property.category', 'property.type', 'property.amenities'])->find($this->listingId);

        if (! $listing || $listing->status !== 'published') {
            ListingSearchIndex::where('listing_id', $this->listingId)->delete();

            return;
        }

        $amenities = $listing->property->amenities->pluck('code')->toArray();

        ListingSearchIndex::updateOrCreate(
            ['listing_id' => $listing->id],
            [
                'title' => $listing->property->title,
                'description' => $listing->property->description,
                'purpose' => $listing->purpose,
                'price' => $listing->price,
                'currency' => $listing->currency,
                'property_category_code' => $listing->property->category->code ?? null,
                'property_type_code' => $listing->property->type->code ?? null,
                'bedrooms' => $listing->property->bedrooms,
                'bathrooms' => $listing->property->bathrooms,
                'latitude' => $listing->property->latitude,
                'longitude' => $listing->property->longitude,
                'amenities' => count($amenities) > 0 ? $amenities : null,
                'published_at' => $listing->published_at ?? now(),
            ]
        );
    }
}
