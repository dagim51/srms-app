<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'listing_id' => $this->listing_id,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'expires_at' => $this->expires_at?->toISOString(),
            'notes' => $this->notes,
            'listing' => $this->whenLoaded('listing', fn () => new ListingResource($this->listing)),
            'user' => $this->whenLoaded('user', fn () => new UserResource($this->user)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
