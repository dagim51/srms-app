<?php

namespace App\Http\Requests\Accommodation;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAccommodationBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $booking = $this->route('accommodationBooking');

        if (! $booking) {
            return false;
        }

        $user = $this->user();

        // Guest can cancel their own booking
        if ($this->input('status') === 'cancelled' && $booking->user_id === $user->id) {
            return true;
        }

        // Host can confirm or cancel
        if (in_array($this->input('status'), ['confirmed', 'cancelled']) && $booking->accommodation->host_id === $user->id) {
            return true;
        }

        return false;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|string|in:confirmed,cancelled',
        ];
    }
}
