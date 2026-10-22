<?php

namespace App\Http\Requests\Review;

use Illuminate\Foundation\Http\FormRequest;

class IndexReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Public endpoint for viewing reviews
    }

    public function rules(): array
    {
        return [
            'reviewable_type' => 'required|string',
            'reviewable_id' => 'required|string',
        ];
    }
}
