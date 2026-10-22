<?php

namespace App\Http\Requests\Dispute;

use Illuminate\Foundation\Http\FormRequest;

class FlagListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Any authenticated user can flag a listing
    }

    public function rules(): array
    {
        return [
            'listing_id' => 'required|uuid|exists:listings,id',
            'reason' => 'required|in:scam_fraudulent_price,misleading_photos,duplicate_listing,prohibited_content,spam,personal_info,off_topic,other',
            'description' => 'required|string|min:10|max:2000',
            'severity' => 'nullable|in:low,medium,high,critical',
        ];
    }
}
