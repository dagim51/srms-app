<?php

namespace App\Http\Requests\Reservation;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $reservation = $this->route('reservation');
        $user = $this->user();

        // Requester can cancel
        if ($this->input('status') === 'cancelled' && $reservation->user_id === $user->id) {
            return true;
        }

        // Owner can confirm or cancel
        if (in_array($this->input('status'), ['confirmed', 'cancelled']) && $reservation->listing->owner_actor_id === $user->id) {
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
