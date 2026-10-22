<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccommodationBookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isOwner = $request->user() && (
            $request->user()->id === $this->user_id ||
            ($this->relationLoaded('accommodation') && $this->accommodation && $this->accommodation->host_id === $request->user()->id)
        );

        return [
            'id' => $this->id,
            'accommodation' => $this->whenLoaded('accommodation', fn () => new AccommodationResource($this->accommodation)),
            'guest' => $isOwner ? new UserResource($this->whenLoaded('user')) : null,
            'check_in_date' => $this->check_in_date?->toDateString(),
            'check_out_date' => $this->check_out_date?->toDateString(),
            'nights' => $this->check_in_date && $this->check_out_date
                ? $this->check_in_date->diffInDays($this->check_out_date)
                : null,
            'guests' => $this->guests,
            'total_price' => $this->total_price ? (float) $this->total_price : null,
            'currency' => $this->currency ?? 'ETB',
            'status' => $this->status,
            'payment_status' => $this->payment_status ?? 'pending',
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
