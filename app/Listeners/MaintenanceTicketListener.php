<?php

namespace App\Listeners;

use App\Events\Operations\TicketCreated;
use App\Events\Operations\TicketDispatched;
use App\Events\Operations\TicketResolved;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class MaintenanceTicketListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        if ($event instanceof TicketCreated) {
            Log::info("Maintenance ticket [{$event->ticket->id}] created for property [{$event->ticket->property_id}].");
        } elseif ($event instanceof TicketDispatched) {
            Log::info("Maintenance ticket [{$event->ticket->id}] dispatched to technician [{$event->assigneeId}].");
        } elseif ($event instanceof TicketResolved) {
            Log::info("Maintenance ticket [{$event->ticket->id}] resolved.");
        }
    }
}
