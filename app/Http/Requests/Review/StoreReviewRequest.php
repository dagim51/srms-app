<?php

namespace App\Http\Requests\Review;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Any authenticated user can create a review
    }

    public function rules(): array
    {
        return [
            'reviewable_type' => 'required|string|in:App\Models\Listing,App\Models\User',
            'reviewable_id' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ];
    }
}
