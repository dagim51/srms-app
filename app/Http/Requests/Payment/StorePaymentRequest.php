<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Any authenticated user can initiate payment
    }

    public function rules(): array
    {
        return [
            'payable_type' => 'required|string', // e.g. App\Models\AccommodationBooking
            'payable_id' => 'required|uuid',
            'payment_method' => 'required|string|in:telebirr,chapa,card,bank_transfer',
        ];
    }
}
