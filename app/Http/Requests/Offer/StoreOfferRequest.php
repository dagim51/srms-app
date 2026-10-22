<?php

namespace App\Http\Requests\Offer;

use Illuminate\Foundation\Http\FormRequest;

class StoreOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Any authenticated user can make an offer
    }

    public function rules(): array
    {
        return [
            'listing_id' => 'required|exists:listings,id',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'parent_offer_id' => 'nullable|exists:offers,id',
            'expires_at' => 'nullable|date',
        ];
    }
}
