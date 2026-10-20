<?php

namespace App\Services\Operations;

use App\Events\Operations\TicketCreated;
use App\Events\Operations\TicketDispatched;
use App\Events\Operations\TicketResolved;
use App\Models\CleaningTask;
use App\Models\MaintenanceTicket;
use Illuminate\Support\Facades\DB;

class MaintenanceService
{
    /**
     * Create a maintenance ticket.
     */
    public function createTicket(string $reportedBy, array $data): MaintenanceTicket
    {
        return DB::transaction(function () use ($reportedBy, $data) {
            $ticket = MaintenanceTicket::create([
                'property_id' => $data['property_id'],
                'reported_by' => $reportedBy,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'priority' => $data['priority'] ?? 'medium',
                'status' => 'open',
            ]);

            event(new TicketCreated($ticket));

            return $ticket;
        });
    }

    /**
     * Dispatch / assign ticket to a technician.
     */
    public function assignTicket(MaintenanceTicket $ticket, string $assigneeId): MaintenanceTicket
    {
        return DB::transaction(function () use ($ticket, $assigneeId) {
            $ticket->assigned_to = $assigneeId;
            $ticket->status = 'assigned';
            $ticket->save();

            event(new TicketDispatched($ticket, $assigneeId));

            return $ticket;
        });
    }

    /**
     * Update maintenance ticket status.
     */
    public function updateTicketStatus(MaintenanceTicket $ticket, string $status): MaintenanceTicket
    {
        return DB::transaction(function () use ($ticket, $status) {
            $ticket->status = $status;
            $ticket->save();

            if (in_array($status, ['completed', 'closed'])) {
                event(new TicketResolved($ticket));
            }

            return $ticket;
        });
    }

    /**
     * Create cleaning task.
     */
    public function createCleaningTask(array $data): CleaningTask
    {
        return DB::transaction(function () use ($data) {
            return CleaningTask::create([
                'property_id' => $data['property_id'],
                'status' => 'pending',
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }

    /**
     * Update cleaning task status.
     */
    public function updateCleaningStatus(CleaningTask $task, string $status): CleaningTask
    {
        return DB::transaction(function () use ($task, $status) {
            $task->status = $status;
            $task->save();

            return $task;
        });
    }
}
