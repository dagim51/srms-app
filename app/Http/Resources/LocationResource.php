<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocationResource extends JsonResource
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
            'name_en' => $this->name_en,
            'name_am' => $this->name_am,
            'name_om' => $this->name_om,
            'type' => $this->type, // country, region, zone, city, sub_city, woreda, kebele, neighborhood
            'latitude' => $this->latitude ? (float) $this->latitude : null,
            'longitude' => $this->longitude ? (float) $this->longitude : null,
            'status' => $this->status,
            'parent' => $this->whenLoaded('parent', fn () => new LocationResource($this->parent)),
            'children' => $this->whenLoaded('children', fn () => LocationResource::collection($this->children)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
