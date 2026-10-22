<?php

namespace App\Http\Requests\Offer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        $offer = $this->route('offer');
        $user = $this->user();

        // Buyer can withdraw their own offer
        if ($this->input('status') === 'withdrawn' && $offer->buyer_id === $user->id) {
            return true;
        }

        // Seller/agent can accept or reject
        if (in_array($this->input('status'), ['accepted', 'rejected']) && $offer->listing->owner_actor_id === $user->id) {
            return true;
        }

        return false;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|string|in:accepted,rejected,withdrawn',
        ];
    }
}
