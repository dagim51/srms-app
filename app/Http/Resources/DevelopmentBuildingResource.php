<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DevelopmentBuildingResource extends JsonResource
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
            'development_project_id' => $this->development_project_id,
            'name' => $this->name,
            'total_floors' => $this->total_floors,
            'status' => $this->status,
            'units' => $this->whenLoaded('units', fn () => DevelopmentUnitResource::collection($this->units)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
