<?php

namespace App\Jobs;

use App\Models\AccommodationBooking;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessUpcomingBookingsJob implements ShouldQueue
{
    use Queueable;

    public $tries = 3;

    public $backoff = 60;

    public function __construct()
    {
        //
    }

    public function handle(): void
    {
        $upcomingBookings = AccommodationBooking::where('status', 'confirmed')
            ->where('check_in_date', now()->addDay()->toDateString())
            ->get();

        foreach ($upcomingBookings as $booking) {
            // Ideally we'd trigger an UpcomingBookingReminderNotification
            // Here we just simulate an automation step checking status
            Log::info("Processing upcoming booking: {$booking->id}");
        }
    }
}
