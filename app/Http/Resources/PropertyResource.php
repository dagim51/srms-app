<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
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
            'property_code' => $this->property_code,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'location' => $this->whenLoaded('location', fn () => new LocationResource($this->location)),
            'category' => $this->whenLoaded('category', fn () => new PropertyCategoryResource($this->category)),
            'type' => $this->whenLoaded('type', fn () => new PropertyTypeResource($this->type)),
            'physical_attributes' => [
                'land_area' => $this->land_area,
                'building_area' => $this->building_area,
                'bedrooms' => $this->bedrooms,
                'bathrooms' => $this->bathrooms,
                'floors' => $this->floors,
                'condition' => $this->condition,
                'construction_year' => $this->construction_year,
                'completion_pct' => $this->completion_pct,
            ],
            'latitude' => $this->latitude ? (float) $this->latitude : null,
            'longitude' => $this->longitude ? (float) $this->longitude : null,
            'coordinates' => $this->when(
                $this->latitude && $this->longitude,
                fn () => [
                    'latitude' => (float) $this->latitude,
                    'longitude' => (float) $this->longitude,
                ]
            ),
            'cover_image' => $this->whenLoaded('media', fn () => $this->media->first()?->url),
            'gallery' => $this->whenLoaded('media', fn () => PropertyMediaResource::collection($this->media)),
            'amenities' => $this->whenLoaded('amenities', fn () => AmenityResource::collection($this->amenities)),
            'features' => $this->whenLoaded('features', fn () => PropertyFeatureResource::collection($this->features)),
            'listings' => $this->whenLoaded('listings', fn () => ListingResource::collection($this->listings)),
            'owner_user_id' => $this->owner_user_id,
            'owner' => $this->whenLoaded('ownerUser', fn () => new UserResource($this->ownerUser)),
            'owner_user' => $this->when($this->owner_user_id, fn () => new UserResource(\App\Models\User::find($this->owner_user_id))),
            'organization' => $this->whenLoaded('organization', fn () => new OrganizationResource($this->organization)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}