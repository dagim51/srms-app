<?php

namespace App\Http\Requests\Accommodation;

use App\Models\Property;
use Illuminate\Foundation\Http\FormRequest;

class StoreAccommodationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $property = Property::findOrFail($this->input('property_id'));

        return $this->user()->id === $property->owner_user_id;
    }

    public function rules(): array
    {
        return [
            'property_id' => 'required|exists:properties,id|unique:accommodations,property_id',
            'max_guests' => 'nullable|integer|min:1',
            'bedrooms' => 'nullable|integer|min:0',
            'beds' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|numeric|min:0',
            'check_in_time' => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i',
            'rules' => 'nullable|string',
        ];
    }
}
