<?php

namespace App\Listeners;

use App\Events\Inquiries\InquiryResponded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class InquiryRespondedListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(InquiryResponded $event): void
    {
        $inquiry = $event->inquiry->load('requester');

        // Notify the requester about the response
        if ($inquiry->requester) {
            // TODO: Create and send notification
            // $inquiry->requester->notify(new InquiryRespondedNotification($inquiry, $event->response));
        }
    }
}
