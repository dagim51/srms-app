<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RentalApplicationResource extends JsonResource
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
            'tenant_profile_id' => $this->tenant_profile_id,
            'status' => $this->status,
            'proposed_rent' => $this->proposed_rent !== null ? (float) $this->proposed_rent : null,
            'message' => $this->message,
            'property' => $this->whenLoaded('property', fn () => new PropertyResource($this->property)),
            'tenant_profile' => $this->whenLoaded('tenantProfile', fn () => new TenantProfileResource($this->tenantProfile)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
