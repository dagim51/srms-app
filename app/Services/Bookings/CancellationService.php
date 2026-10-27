<?php

namespace App\Services\Bookings;

use App\Events\Bookings\BookingCancelled;
use App\Models\AccommodationAvailability;
use App\Models\AccommodationBooking;
use App\Models\CancellationPolicy;
use App\Models\CancellationRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CancellationService
{
    /**
     * Cancel an accommodation booking and process refund calculation according to policy.
     */
    public function cancel(
        AccommodationBooking $booking,
        ?string $cancelledBy = null,
        string $reason = 'guest_cancelled',
        ?CancellationPolicy $policy = null
    ): CancellationRecord {
        return DB::transaction(function () use ($booking, $cancelledBy, $reason, $policy) {
            if (in_array($booking->status, ['cancelled', 'completed'])) {
                throw new InvalidArgumentException("Booking cannot be cancelled because it is already {$booking->status}.");
            }

            $originalAmount = (float) $booking->total_price;
            $checkIn = Carbon::parse($booking->check_in_date);
            $daysUntilCheckIn = (int) now()->diffInDays($checkIn, false);

            if ($policy) {
                if ($daysUntilCheckIn >= $policy->flexible_days) {
                    $refundPercentage = (float) $policy->refund_percentage;
                    $refundAmount = round($originalAmount * ($refundPercentage / 100), 2);
                    $feeAmount = round($originalAmount - $refundAmount, 2);
                } else {
                    $feePercentage = (float) ($policy->fee_percentage > 0 ? $policy->fee_percentage : 100.0);
                    $feeAmount = round($originalAmount * ($feePercentage / 100), 2);
                    $refundAmount = max(0.0, round($originalAmount - $feeAmount, 2));
                }
            } else {
                // Standard default policy
                if ($daysUntilCheckIn >= 5) {
                    $refundAmount = $originalAmount;
                    $feeAmount = 0.0;
                } elseif ($daysUntilCheckIn >= 1) {
                    $refundAmount = round($originalAmount * 0.50, 2);
                    $feeAmount = round($originalAmount - $refundAmount, 2);
                } else {
                    $refundAmount = 0.0;
                    $feeAmount = $originalAmount;
                }
            }

            // Create immutable cancellation record
            $record = CancellationRecord::create([
                'booking_id' => $booking->id,
                'cancelled_by' => $cancelledBy ?? auth()->id(),
                'policy_id' => $policy?->id,
                'original_amount' => $originalAmount,
                'refund_amount' => $refundAmount,
                'fee_amount' => $feeAmount,
                'reason' => $reason,
                'status' => 'processed',
            ]);

            // Update booking status
            $booking->update([
                'status' => 'cancelled',
            ]);

            // Release availability block
            AccommodationAvailability::where('accommodation_id', $booking->accommodation_id)
                ->where('start_date', $booking->check_in_date)
                ->where('end_date', $booking->check_out_date)
                ->delete();

            // Fire domain event
            event(new BookingCancelled($booking, $cancelledBy, $reason));

            return $record;
        });
    }
}
