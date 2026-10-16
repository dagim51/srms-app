<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InquiryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'listing' => $this->whenLoaded('listing', fn () => new ListingResource($this->listing)),
            'property' => $this->whenLoaded('property', fn () => new PropertyResource($this->property)),
            'requester' => $this->whenLoaded('requester', fn () => new UserResource($this->requester)),
            'seller_agent' => $this->whenLoaded('sellerAgent', fn () => new UserResource($this->sellerAgent)),
            'status' => $this->status,
            'message' => $this->message,
            'response' => $this->response,
            'responded_at' => $this->responded_at?->toISOString(),
            'closed_at' => $this->closed_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}