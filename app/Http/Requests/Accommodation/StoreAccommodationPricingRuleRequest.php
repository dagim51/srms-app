<?php

namespace App\Http\Requests\Accommodation;

use App\Models\Accommodation;
use Illuminate\Foundation\Http\FormRequest;

class StoreAccommodationPricingRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $accommodation = Accommodation::findOrFail($this->input('accommodation_id'));

        return $this->user()->id === $accommodation->host_id;
    }

    public function rules(): array
    {
        return [
            'accommodation_id' => 'required|exists:accommodations,id',
            'type' => 'required|string|in:seasonal,weekend,special',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'price_modifier_type' => 'required|string|in:fixed,percentage',
            'price_modifier_value' => 'required|numeric',
            'priority' => 'nullable|integer',
        ];
    }
}
