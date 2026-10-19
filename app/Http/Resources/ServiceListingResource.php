<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceListingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'service_provider_profile_id' => $this->service_provider_profile_id,
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price ? (float) $this->price : null,
            'price_unit' => $this->price_unit,
            'is_active' => $this->is_active,
            'profile' => $this->whenLoaded('profile', fn () => new ServiceProviderProfileResource($this->profile)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
