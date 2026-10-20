<?php

namespace App\Listeners;

use App\Events\Trust\DisputeOpened;
use App\Events\Trust\DisputeResolved;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class DisputeListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        if ($event instanceof DisputeOpened) {
            Log::info("Dispute opened [{$event->dispute->id}] by complainant [{$event->dispute->complainant_id}].");
        } elseif ($event instanceof DisputeResolved) {
            Log::info("Dispute resolved [{$event->dispute->id}] by resolver [{$event->resolverId}].");
        }
    }
}
