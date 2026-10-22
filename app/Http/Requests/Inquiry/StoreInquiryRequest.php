<?php

namespace App\Http\Requests\Inquiry;

use Illuminate\Foundation\Http\FormRequest;

class StoreInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Any authenticated user can create an inquiry
    }

    public function rules(): array
    {
        return [
            'listing_id' => 'required|exists:listings,id',
            'message' => 'nullable|string',
        ];
    }
}
