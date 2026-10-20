<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaseResource extends JsonResource
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
            'start_date' => $this->start_date ? (is_string($this->start_date) ? $this->start_date : $this->start_date->toDateString()) : null,
            'end_date' => $this->end_date ? (is_string($this->end_date) ? $this->end_date : $this->end_date->toDateString()) : null,
            'rent_amount' => (float) $this->rent_amount,
            'deposit_amount' => (float) $this->deposit_amount,
            'status' => $this->status,
            'property' => $this->whenLoaded('property', fn () => new PropertyResource($this->property)),
            'tenant_profile' => $this->whenLoaded('tenantProfile', fn () => new TenantProfileResource($this->tenantProfile)),
            'schedules' => $this->whenLoaded('schedules', fn () => RentScheduleResource::collection($this->schedules)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
