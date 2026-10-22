<?php

namespace App\Listeners;

use App\Events\Payments\PaymentFailed;
use App\Models\AccommodationAvailability;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class PaymentFailedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(PaymentFailed $event): void
    {
        $payment = $event->payment->load('payable');

        if (! $payment->payable) {
            return;
        }

        // Handle specific payable types
        match (class_basename($payment->payable)) {
            'AccommodationBooking' => $this->handleBookingFailed($payment->payable),
            default => null,
        };
    }

    protected function handleBookingFailed($booking): void
    {
        // Optionally update booking status
        if (in_array('status', $booking->getFillable())) {
            $booking->update(['status' => 'payment_failed']);
        }

        // Remove any availability blocks created for this booking
        AccommodationAvailability::where('accommodation_id', $booking->accommodation_id)
            ->where('type', 'booked')
            ->where('reason', 'like', '%'.$booking->id.'%')
            ->delete();
    }
}
