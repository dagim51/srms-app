<?php

namespace App\Events\Bookings;

use App\Models\AccommodationBooking;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingCancelled
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly AccommodationBooking $booking,
        public readonly ?string $cancelledBy = null,
        public readonly string $reason = 'guest_cancelled', // guest_cancelled, host_cancelled, admin_cancelled
    ) {}
}
