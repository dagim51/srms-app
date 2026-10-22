<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DisputeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'complainant' => $this->whenLoaded('complainant', fn () => new UserResource($this->complainant)),
            'respondent' => $this->whenLoaded('respondent', fn () => new UserResource($this->respondent)),
            'assigned_moderator' => $this->whenLoaded('assignedModerator', fn () => new UserResource($this->assignedModerator)),
            'disputable' => $this->whenLoaded('disputable'),
            'type' => $this->type,
            'subject' => $this->subject,
            'description' => $this->description,
            'amount_in_dispute' => $this->amount_in_dispute ? (float) $this->amount_in_dispute : null,
            'currency' => $this->currency,
            'status' => $this->status,
            'priority' => $this->priority,
            'resolution' => $this->resolution,
            'resolution_notes' => $this->resolution_notes,
            'opened_at' => $this->opened_at?->toISOString(),
            'assigned_at' => $this->assigned_at?->toISOString(),
            'first_response_at' => $this->first_response_at?->toISOString(),
            'resolved_at' => $this->resolved_at?->toISOString(),
            'closed_at' => $this->closed_at?->toISOString(),
            'sla_hours' => $this->sla_hours,
            'sla_breached' => $this->sla_breached,
            'evidence' => $this->whenLoaded('evidence', fn () => DisputeEvidenceResource::collection($this->evidence)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
