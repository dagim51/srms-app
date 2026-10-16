<?php

namespace App\Listeners;

use App\Events\Payments\PaymentCompleted;
use App\Models\AccommodationBooking;
use App\Models\Listing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class PaymentCompletedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(PaymentCompleted $event): void
    {
        $payment = $event->payment->load('payable');

        if (!$payment->payable) {
            return;
        }

        // Handle specific payable types
        match (class_basename($payment->payable)) {
            'AccommodationBooking' => $this->handleBookingConfirmed($payment->payable),
            'Listing' => $this->handleListingPublished($payment->payable),
            default => null,
        };
    }

    protected function handleBookingConfirmed(AccommodationBooking $booking): void
    {
        $booking->update(['status' => 'confirmed']);

        // Create availability block
        \App\Models\AccommodationAvailability::firstOrCreate(
            [
                'accommodation_id' => $booking->accommodation_id,
                'start_date' => $booking->check_in_date,
                'end_date' => $booking->check_out_date,
                'type' => 'booked',
                'reason' => 'Booking ID: ' . $booking->id,
            ],
            [
                'type' => 'booked',
                'reason' => 'Booking ID: ' . $booking->id,
            ]
        );
    }

    protected function handleListingPublished(Listing $listing): void
    {
        if ($listing->status !== 'published') {
            $listing->update([
                'status' => 'published',
                'published_at' => now(),
            ]);
        }
    }
}