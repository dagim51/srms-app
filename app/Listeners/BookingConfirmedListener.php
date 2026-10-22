<?php

namespace App\Listeners;

use App\Events\Bookings\BookingConfirmed;
use App\Models\AccommodationAvailability;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class BookingConfirmedListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(BookingConfirmed $event): void
    {
        $booking = $event->booking;

        // Create availability block for confirmed booking (if not already exists)
        AccommodationAvailability::firstOrCreate(
            [
                'accommodation_id' => $booking->accommodation_id,
                'start_date' => $booking->check_in_date,
                'end_date' => $booking->check_out_date,
                'type' => 'booked',
                'reason' => 'Booking ID: '.$booking->id,
            ],
            [
                'type' => 'booked',
                'reason' => 'Booking ID: '.$booking->id,
            ]
        );

        // TODO: Trigger payment intent when WS-07 is implemented
        // event(new PaymentIntentCreated($booking));
    }
}
