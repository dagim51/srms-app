<?php

namespace App\Listeners;

use App\Events\Bookings\BookingCancelled;
use App\Models\AccommodationAvailability;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class BookingCancelledListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(BookingCancelled $event): void
    {
        $booking = $event->booking;

        // Remove the availability block for the cancelled booking
        AccommodationAvailability::where('accommodation_id', $booking->accommodation_id)
            ->where('type', 'booked')
            ->where('reason', 'like', '%'.$booking->id.'%')
            ->delete();

        // TODO: Trigger refund when WS-07 is implemented
        // event(new RefundInitiated($booking));
    }
}
