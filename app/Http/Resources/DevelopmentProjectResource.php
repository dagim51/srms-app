<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DevelopmentProjectResource extends JsonResource
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
            'organization_id' => $this->organization_id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'handover_date' => $this->handover_date ? (is_string($this->handover_date) ? $this->handover_date : $this->handover_date->toDateString()) : null,
            'progress_percentage' => $this->progress_percentage !== null ? (int) $this->progress_percentage : 0,
            'buildings' => $this->whenLoaded('buildings', fn () => DevelopmentBuildingResource::collection($this->buildings)),
            'payment_plans' => $this->whenLoaded('paymentPlans', fn () => PaymentPlanResource::collection($this->paymentPlans)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
