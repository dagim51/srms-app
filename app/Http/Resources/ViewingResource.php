<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ViewingResource extends JsonResource
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
            'requester_id' => $this->requester_id,
            'agent_id' => $this->agent_id,
            'status' => $this->status,
            'scheduled_at' => $this->scheduled_at ? (is_string($this->scheduled_at) ? $this->scheduled_at : $this->scheduled_at->toISOString()) : null,
            'notes' => $this->notes,
            'listing' => $this->whenLoaded('listing', fn () => new ListingResource($this->listing)),
            'requester' => $this->whenLoaded('requester', fn () => new UserResource($this->requester)),
            'agent' => $this->whenLoaded('agent', fn () => new UserResource($this->agent)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
