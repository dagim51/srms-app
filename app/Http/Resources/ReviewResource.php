<?php

namespace App\Http\Resources;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reviewer_id' => $this->reviewer_id,
            'reviewable_type' => $this->reviewable_type,
            'reviewable_id' => $this->reviewable_id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'status' => $this->status,
            'reviewer' => $this->whenLoaded('reviewer', fn () => new UserResource($this->reviewer)),
            'reviewable' => $this->whenLoaded('reviewable', function () {
                if ($this->reviewable instanceof Listing) {
                    return new ListingResource($this->reviewable);
                }
                if ($this->reviewable instanceof User) {
                    return new UserResource($this->reviewable);
                }

                return $this->reviewable;
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
