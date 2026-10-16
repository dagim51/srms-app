<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DisputeEvidenceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'dispute_id' => $this->dispute_id,
            'submitted_by' => $this->whenLoaded('submittedBy', fn () => new UserResource($this->submittedBy)),
            'type' => $this->type,
            'title' => $this->title,
            'description' => $this->description,
            'file_path' => $this->file_path,
            'file_url' => $this->file_url,
            'mime_type' => $this->mime_type,
            'file_size' => $this->file_size,
            'metadata' => $this->metadata,
            'is_verified' => $this->is_verified,
            'verified_by' => $this->whenLoaded('verifiedBy', fn () => new UserResource($this->verifiedBy)),
            'verified_at' => $this->verified_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}