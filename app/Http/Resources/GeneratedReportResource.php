<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GeneratedReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'report_template_id' => $this->report_template_id,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'file_path' => $this->file_path,
            'format' => $this->format,
            'template' => $this->whenLoaded('template', fn () => new ReportTemplateResource($this->template)),
            'requester' => $this->whenLoaded('requester', fn () => new UserResource($this->requester)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
