<?php

namespace App\Http\Requests\Rental;

use Illuminate\Foundation\Http\FormRequest;

class StoreTenantProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Any authenticated user can create a tenant profile
    }

    public function rules(): array
    {
        return [
            'employment_status' => 'nullable|string',
            'annual_income' => 'nullable|numeric',
        ];
    }
}
