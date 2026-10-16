<?php

namespace App\Listeners;

use App\Events\Inquiries\InquiryCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class InquiryCreatedListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(InquiryCreated $event): void
    {
        $inquiry = $event->inquiry->load('listing.property', 'sellerAgent');

        // Notify the seller/agent about the new inquiry
        if ($inquiry->sellerAgent) {
            // TODO: Create and send notification
            // $inquiry->sellerAgent->notify(new InquiryReceivedNotification($inquiry));
        }
    }
}