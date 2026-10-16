<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListingSearchIndexResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'purpose' => $this->purpose,
            'price' => $this->price ? (float) $this->price : null,
            'currency' => $this->currency,
            'property_category_code' => $this->property_category_code,
            'property_type_code' => $this->property_type_code,
            'bedrooms' => $this->bedrooms,
            'bathrooms' => $this->bathrooms,
            'latitude' => $this->latitude ? (float) $this->latitude : null,
            'longitude' => $this->longitude ? (float) $this->longitude : null,
            'amenities' => $this->amenities,
            'published_at' => $this->published_at?->toISOString(),
        ];
    }
}