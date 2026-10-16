<?php

namespace App\Listeners;

use App\Events\Bookings\BookingCreated;
use App\Notifications\BookingConfirmedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class BookingCreatedListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(BookingCreated $event): void
    {
        $booking = $event->booking->load('accommodation.host', 'user');

        // Notify the host about the new booking request
        if ($booking->accommodation && $booking->accommodation->host) {
            $booking->accommodation->host->notify(new BookingConfirmedNotification($booking));
        }

        // TODO: Notify guest about pending confirmation
        // $booking->user->notify(new BookingPendingNotification($booking));
    }
}