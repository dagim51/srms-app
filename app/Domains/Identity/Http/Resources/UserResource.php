<?php

namespace App\Domains\Identity\Http\Resources;

use App\Http\Resources\OrganizationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->when($this->id === $request->user()?->id || $request->user()?->hasRole('Administrator'), $this->email),
            'phone' => $this->when($this->id === $request->user()?->id || $request->user()?->hasRole('Administrator'), $this->phone),
            'avatar' => $this->avatar,
            'locale' => $this->locale,
            'is_verified' => $this->is_verified,
            'roles' => $this->whenLoaded('roles', fn () => $this->roles->pluck('name')),
            'permissions' => $this->whenLoaded('permissions', fn () => $this->permissions->pluck('name')),
            'organizations' => $this->whenLoaded('organizations', fn () => OrganizationResource::collection($this->organizations)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
