<?php

namespace App\Http\Requests\Review;

use Illuminate\Foundation\Http\FormRequest;

class ReportReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Any authenticated user can report a review
    }

    public function rules(): array
    {
        return [];
    }
}
