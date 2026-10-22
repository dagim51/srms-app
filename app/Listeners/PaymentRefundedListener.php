<?php

namespace App\Listeners;

use App\Events\Payments\PaymentRefunded;
use App\Models\AccommodationAvailability;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class PaymentRefundedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(PaymentRefunded $event): void
    {
        $refund = $event->refundPayment->load('payable');
        $original = $event->originalPayment->load('payable');

        if (! $original->payable) {
            return;
        }

        // Handle specific payable types
        match (class_basename($original->payable)) {
            'AccommodationBooking' => $this->handleBookingRefunded($original->payable),
            default => null,
        };
    }

    protected function handleBookingRefunded($booking): void
    {
        // Update booking status
        if (in_array('status', $booking->getFillable())) {
            $booking->update(['status' => 'refunded']);
        }

        // Remove availability blocks
        AccommodationAvailability::where('accommodation_id', $booking->accommodation_id)
            ->where('type', 'booked')
            ->where('reason', 'like', '%'.$booking->id.'%')
            ->delete();
    }
}
