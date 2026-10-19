<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceProviderProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'business_name' => $this->business_name,
            'bio' => $this->bio,
            'category' => $this->category,
            'is_verified' => $this->is_verified,
            'user' => $this->whenLoaded('user', fn () => new UserResource($this->user)),
            'listings' => $this->whenLoaded('listings', fn () => ServiceListingResource::collection($this->listings)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
