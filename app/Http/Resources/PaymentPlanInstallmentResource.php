<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentPlanInstallmentResource extends JsonResource
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
            'payment_plan_id' => $this->payment_plan_id,
            'name' => $this->name,
            'percentage' => (float) $this->percentage,
            'amount' => $this->amount !== null ? (float) $this->amount : null,
            'due_days_after_reservation' => (int) $this->due_days_after_reservation,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
