<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListingModerationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'moderator' => $this->whenLoaded('moderator', fn () => new UserResource($this->moderator)),
            'action' => $this->action,
            'comments' => $this->comments,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}