<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DevelopmentUnitResource extends JsonResource
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
            'development_building_id' => $this->development_building_id,
            'floor_name' => $this->floor_name,
            'unit_number' => $this->unit_number,
            'status' => $this->status,
            'price' => $this->price !== null ? (float) $this->price : null,
            'building' => $this->whenLoaded('building', fn () => new DevelopmentBuildingResource($this->building)),
            'payment_plan' => $this->whenLoaded('paymentPlan', fn () => new PaymentPlanResource($this->paymentPlan)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
