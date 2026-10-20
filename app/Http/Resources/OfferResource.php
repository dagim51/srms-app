<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'listing_id' => $this->listing_id,
            'buyer_id' => $this->buyer_id,
            'parent_offer_id' => $this->parent_offer_id,
            'amount' => (float) $this->amount,
            'status' => $this->status,
            'expires_at' => $this->expires_at ? (is_string($this->expires_at) ? $this->expires_at : $this->expires_at->toISOString()) : null,
            'notes' => $this->notes,
            'listing' => $this->whenLoaded('listing', fn () => new ListingResource($this->listing)),
            'buyer' => $this->whenLoaded('buyer', fn () => new UserResource($this->buyer)),
            'events' => $this->whenLoaded('events', fn () => OfferEventResource::collection($this->events)),
            'counter_offers' => $this->whenLoaded('counterOffers', fn () => OfferResource::collection($this->counterOffers)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
