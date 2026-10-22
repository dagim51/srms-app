<?php

namespace App\Actions;

use App\Models\AccommodationBooking;

class ConfirmBooking extends Action
{
    /**
     * Execute the confirm action.
     */
    public function execute(AccommodationBooking $booking): AccommodationBooking
    {
        return $this->transactional(function () use ($booking) {
            $booking->status = 'confirmed';
            $booking->save();

            return $booking->fresh();
        });
    }
}
