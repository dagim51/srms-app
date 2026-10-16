<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccommodationPricingRuleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'priority' => $this->priority,
            'price_modifier_type' => $this->price_modifier_type,
            'price_modifier_value' => $this->price_modifier_value ? (float) $this->price_modifier_value : null,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'min_stay_nights' => $this->min_stay_nights,
            'max_stay_nights' => $this->max_stay_nights,
            'day_of_week' => $this->day_of_week,
            'status' => $this->status,
        ];
    }
}