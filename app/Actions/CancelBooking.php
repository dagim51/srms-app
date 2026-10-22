<?php

namespace App\Actions;

use App\Models\AccommodationBooking;

class CancelBooking extends Action
{
    /**
     * Execute the cancel action.
     */
    public function execute(AccommodationBooking $booking): AccommodationBooking
    {
        return $this->transactional(function () use ($booking) {
            $booking->status = 'cancelled';
            $booking->save();

            return $booking->fresh();
        });
    }
}
