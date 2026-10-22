<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccommodationResource extends JsonResource
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
            'host_id' => $this->host_id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'max_guests' => $this->max_guests,
            'host' => $this->whenLoaded('host', fn () => new UserResource($this->host)),
            'property' => $this->whenLoaded('property', fn () => new PropertyResource($this->property)),
            'pricing' => [
                'base_price' => $this->base_price ? (float) $this->base_price : null,
                'currency' => $this->currency ?? 'ETB',
                'cleaning_fee' => $this->cleaning_fee ? (float) $this->cleaning_fee : null,
                'service_fee' => $this->service_fee ? (float) $this->service_fee : null,
                'security_deposit' => $this->security_deposit ? (float) $this->security_deposit : null,
            ],
            'capacity' => [
                'max_guests' => $this->max_guests,
                'bedrooms' => $this->bedrooms,
                'beds' => $this->beds,
                'bathrooms' => $this->bathrooms,
            ],
            'amenities' => $this->whenLoaded('amenities', fn () => AmenityResource::collection($this->amenities)),
            'availabilities' => $this->whenLoaded('availabilities', fn () => AccommodationAvailabilityResource::collection($this->availabilities)),
            'pricing_rules' => $this->whenLoaded('pricingRules', fn () => AccommodationPricingRuleResource::collection($this->pricingRules)),
            'media' => $this->whenLoaded('media', fn () => PropertyMediaResource::collection($this->media)),
            'bookings' => $this->whenLoaded('bookings', fn () => AccommodationBookingResource::collection($this->bookings)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
