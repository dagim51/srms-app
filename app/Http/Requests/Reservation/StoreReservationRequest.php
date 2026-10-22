<?php

namespace App\Http\Requests\Reservation;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Any authenticated user can create a reservation
    }

    public function rules(): array
    {
        return [
            'listing_id' => 'required|exists:listings,id',
            'notes' => 'nullable|string',
        ];
    }
}
