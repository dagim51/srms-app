<?php

namespace App\Events\Listings;

use App\Models\Listing;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ListingApproved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Listing $listing,
        public readonly string $moderatorId,
        public readonly ?string $comments = null
    ) {}
}
