<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceTicketResource extends JsonResource
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
            'property_id' => $this->property_id,
            'reported_by' => $this->reported_by,
            'assigned_to' => $this->assigned_to,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'status' => $this->status,
            'property' => $this->whenLoaded('property', fn () => new PropertyResource($this->property)),
            'reporter' => $this->whenLoaded('reportedBy', fn () => new UserResource($this->reportedBy)),
            'assignee' => $this->whenLoaded('assignedTo', fn () => new UserResource($this->assignedTo)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
