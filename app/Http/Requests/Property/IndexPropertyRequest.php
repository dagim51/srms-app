<?php

namespace App\Http\Requests\Property;

use App\Models\Property;
use Illuminate\Foundation\Http\FormRequest;

class IndexPropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Property::class);
    }

    public function rules(): array
    {
        return [
            'category_id' => 'nullable|exists:property_categories,id',
            'type_id' => 'nullable|exists:property_types,id',
            'amenity_id' => 'nullable|exists:amenities,id',
            'feature_id' => 'nullable|exists:property_features,id',
            'bedrooms' => 'nullable|integer|min:0',
        ];
    }
}
