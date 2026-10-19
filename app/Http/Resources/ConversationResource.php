<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
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
            'subject' => $this->subject,
            'listing_id' => $this->listing_id,
            'property_id' => $this->property_id,
            'inquiry_id' => $this->inquiry_id,
            'participants' => $this->whenLoaded('participants', fn () => ConversationParticipantResource::collection($this->participants)),
            'messages' => $this->whenLoaded('messages', fn () => MessageResource::collection($this->messages)),
            'listing' => $this->whenLoaded('listing', fn () => new ListingResource($this->listing)),
            'property' => $this->whenLoaded('property', fn () => new PropertyResource($this->property)),
            'inquiry' => $this->whenLoaded('inquiry', fn () => new InquiryResource($this->inquiry)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
