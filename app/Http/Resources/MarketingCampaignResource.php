<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MarketingCampaignResource extends JsonResource
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
            'name' => $this->name,
            'channel' => $this->channel,
            'budget' => $this->budget ? (float) $this->budget : null,
            'start_date' => $formatDate($this->start_date),
            'end_date' => $formatDate($this->end_date),
            'status' => $this->status,
            'leads' => $this->whenLoaded('leads', fn () => LeadResource::collection($this->leads)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
