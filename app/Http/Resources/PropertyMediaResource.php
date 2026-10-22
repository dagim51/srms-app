<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyMediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kind' => $this->kind,
            'mime_type' => $this->mime_type,
            'url' => $this->url,
            'width' => $this->width,
            'height' => $this->height,
            'sort_order' => $this->sort_order,
            'visibility' => $this->visibility,
            'status' => $this->status,
            'is_cover' => $this->sort_order === 0,
        ];
    }
}
