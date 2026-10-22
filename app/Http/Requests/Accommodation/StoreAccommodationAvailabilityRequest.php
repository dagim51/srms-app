<?php

namespace App\Http\Requests\Accommodation;

use App\Models\Accommodation;
use Illuminate\Foundation\Http\FormRequest;

class StoreAccommodationAvailabilityRequest extends FormRequest
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
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'type' => 'nullable|string|in:blocked,booked',
            'reason' => 'nullable|string',
        ];
    }
}
