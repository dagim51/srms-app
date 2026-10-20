<?php

namespace App\Events\Operations;

use App\Models\MaintenanceTicket;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketDispatched
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly MaintenanceTicket $ticket,
        public readonly string $assigneeId
    ) {}
}
