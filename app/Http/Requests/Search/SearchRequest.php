<?php

namespace App\Http\Requests\Search;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Public search endpoint
    }

    public function rules(): array
    {
        return [
            'q' => 'nullable|string|max:255',
            'purpose' => 'nullable|string',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'bedrooms' => 'nullable|integer|min:0',
            'property_type' => 'nullable|string',
            'amenities' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:0',
            'sort' => 'nullable|in:price_asc,price_desc,newest',
        ];
    }
}
