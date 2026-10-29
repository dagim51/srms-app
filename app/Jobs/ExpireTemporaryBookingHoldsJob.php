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

class ExpireTemporaryBookingHoldsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): int
    {
        $expiredHolds = AccommodationBooking::where('status', 'holding')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        $count = 0;

        foreach ($expiredHolds as $booking) {
            DB::transaction(function () use ($booking) {
                $booking->update([
                    'status' => 'expired',
                ]);

                // Release calendar availability block
                AccommodationAvailability::where('accommodation_id', $booking->accommodation_id)
                    ->where('reason', 'Hold ID: '.$booking->id)
                    ->delete();
            });

            $count++;
            Log::info("Expired temporary hold on booking [{$booking->id}] for accommodation [{$booking->accommodation_id}]. Availability released.");
        }

        return $count;
    }
}
