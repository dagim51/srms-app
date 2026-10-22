<?php

namespace App\Http\Requests\Accommodation;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAccommodationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->id === $this->route('accommodation')->host_id;
    }

    public function rules(): array
    {
        return [
            'max_guests' => 'nullable|integer|min:1',
            'bedrooms' => 'nullable|integer|min:0',
            'beds' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|numeric|min:0',
            'check_in_time' => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i',
            'rules' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ];
    }
}
