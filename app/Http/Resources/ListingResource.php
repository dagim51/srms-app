<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListingResource extends JsonResource
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
            'purpose' => $this->purpose,
            'price' => $this->price ? (float) $this->price : null,
            'currency' => $this->currency,
            'status' => $this->status,
            'visibility' => $this->visibility,
            'published_at' => $this->published_at
                ? (is_string($this->published_at) ? $this->published_at : $this->published_at->toISOString())
                : null,
            'expires_at' => $this->expires_at
                ? (is_string($this->expires_at) ? $this->expires_at : $this->expires_at->toISOString())
                : null,
            'property' => $this->whenLoaded('property', fn () => new PropertyResource($this->property)),
            'owner_actor' => $this->whenLoaded('ownerActor', fn () => new UserResource($this->ownerActor)),
            'moderations' => $this->whenLoaded('moderations', fn () => ListingModerationResource::collection($this->moderations)),
            'search_index' => $this->whenLoaded('searchIndex', fn () => new ListingSearchIndexResource($this->searchIndex)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
