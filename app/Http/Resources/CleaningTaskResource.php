<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CleaningTaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'property_id' => $this->property_id,
            'accommodation_booking_id' => $this->accommodation_booking_id,
            'assigned_cleaner_id' => $this->assigned_cleaner_id,
            'inspector_id' => $this->inspector_id,
            'scheduled_date' => $this->scheduled_date ? (is_string($this->scheduled_date) ? $this->scheduled_date : $this->scheduled_date->toDateString()) : null,
            'status' => $this->status,
            'notes' => $this->notes,
            'property' => $this->whenLoaded('property', fn () => new PropertyResource($this->property)),
            'cleaner' => $this->whenLoaded('cleaner', fn () => new UserResource($this->cleaner)),
            'inspector' => $this->whenLoaded('inspector', fn () => new UserResource($this->inspector)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
