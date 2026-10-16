<?php

namespace App\Events\Bookings;

use App\Models\AccommodationBooking;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingConfirmed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly AccommodationBooking $booking,
        public readonly ?string $confirmedBy = null,
    ) {}
}