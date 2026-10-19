<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'service_listing_id' => $this->service_listing_id,
            'customer_id' => $this->customer_id,
            'property_id' => $this->property_id,
            'status' => $this->status,
            'requirements' => $this->requirements,
            'requested_date' => $this->requested_date?->toISOString(),
            'agreed_price' => $this->agreed_price ? (float) $this->agreed_price : null,
            'listing' => $this->whenLoaded('listing', fn () => new ServiceListingResource($this->listing)),
            'customer' => $this->whenLoaded('customer', fn () => new UserResource($this->customer)),
            'property' => $this->whenLoaded('property', fn () => new PropertyResource($this->property)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
