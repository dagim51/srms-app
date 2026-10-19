<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacilityBookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $formatDate = function ($date) {
            if ($date === null) {
                return null;
            }
            if (is_string($date)) {
                return $date;
            }

            return $date->toISOString();
        };

        return [
            'id' => $this->id,
            'shared_facility_id' => $this->shared_facility_id,
            'user_id' => $this->user_id,
            'start_time' => $formatDate($this->start_time),
            'end_time' => $formatDate($this->end_time),
            'status' => $this->status,
            'facility' => $this->whenLoaded('facility', fn () => new SharedFacilityResource($this->facility)),
            'user' => $this->whenLoaded('user', fn () => new UserResource($this->user)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
