<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListingFlagResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'listing' => $this->whenLoaded('listing', fn () => new ListingResource($this->listing)),
            'reporter' => $this->whenLoaded('reporter', fn () => new UserResource($this->reporter)),
            'assigned_moderator' => $this->whenLoaded('assignedModerator', fn () => new UserResource($this->assignedModerator)),
            'reason' => $this->reason,
            'description' => $this->description,
            'severity' => $this->severity,
            'status' => $this->status,
            'moderator_notes' => $this->moderator_notes,
            'reported_at' => $this->reported_at?->toISOString(),
            'reviewed_at' => $this->reviewed_at?->toISOString(),
            'resolved_at' => $this->resolved_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
