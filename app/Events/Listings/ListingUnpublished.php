<?php

namespace App\Events\Listings;

use App\Models\Listing;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ListingUnpublished
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly Listing $listing,
        public readonly ?string $triggeredBy = null,
        public readonly string $reason = 'manual', // manual, expired, rejected
    ) {}
}
