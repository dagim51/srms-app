<?php

namespace App\Domains\Organization\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'trade_name' => $this->trade_name,
            'tin' => $this->when($request->user()?->can('view-financials', $this), $this->tin),
            'logo' => $this->logo,
            'status' => $this->status,
            'members' => $this->whenLoaded('members', fn () => OrganizationMemberResource::collection($this->members)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
