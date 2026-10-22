<?php

namespace App\Http\Requests\Property;

use Illuminate\Foundation\Http\FormRequest;

class DetachAmenityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('property'));
    }

    public function rules(): array
    {
        return [];
    }
}
