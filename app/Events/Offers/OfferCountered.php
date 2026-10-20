<?php

namespace App\Events\Offers;

use App\Models\Offer;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OfferCountered
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Offer $counterOffer,
        public readonly Offer $originalOffer,
        public readonly ?string $actorId = null
    ) {}
}
