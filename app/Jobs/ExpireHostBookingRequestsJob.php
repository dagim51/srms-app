<?php

namespace App\Jobs;

use App\Models\AccommodationAvailability;
use App\Models\AccommodationBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpireHostBookingRequestsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job: Cancel Request-to-Book submissions unresponded after 24 hours.
     */
    public function handle(): int
    {
        $cutoff = now()->subHours(24);

        $staleRequests = AccommodationBooking::whereIn('status', ['requested', 'pending'])
            ->where('created_at', '<=', $cutoff)
            ->get();

        $count = 0;

        foreach ($staleRequests as $booking) {
            DB::transaction(function () use ($booking) {
                $booking->update([
                    'status' => 'expired',
                ]);

                // Release any calendar availability blocks tied to this booking
                AccommodationAvailability::where('accommodation_id', $booking->accommodation_id)
                    ->where(function ($q) use ($booking) {
                        $q->where('reason', 'Hold ID: '.$booking->id)
                            ->orWhere('reason', 'Booking ID: '.$booking->id);
                    })
                    ->delete();
            });

            $count++;
            Log::info("Expired 24-hr unresponded booking request [{$booking->id}] for accommodation [{$booking->accommodation_id}].");
        }

        return $count;
    }
}
