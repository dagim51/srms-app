<?php

namespace App\Domains\PropertyManagement\Http\Controllers\Api\V1;

use App\Domains\PropertyManagement\Http\Controllers\Controller;
use App\Http\Resources\CleaningTaskResource;
use App\Http\Resources\MaintenanceTicketResource;
use App\Models\CleaningTask;
use App\Models\MaintenanceTicket;
use App\Services\Operations\MaintenanceService;
use Illuminate\Http\Request;

class PropertyManagementController extends Controller
{
    public function __construct(
        protected MaintenanceService $maintenanceService
    ) {}

    public function storeTicket(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|uuid|exists:properties,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|string|in:low,medium,high,urgent',
        ]);

        $ticket = $this->maintenanceService->createTicket(auth()->id(), $validated);
        $ticket->load('property', 'reporter', 'assignee');

        return response()->json(['data' => new MaintenanceTicketResource($ticket)], 201);
    }

    public function updateTicketStatus(Request $request, MaintenanceTicket $ticket)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:open,assigned,in_progress,waiting,completed,closed',
        ]);

        $ticket = $this->maintenanceService->updateTicketStatus($ticket, $validated['status']);
        $ticket->load('property', 'reporter', 'assignee');

        return response()->json(['data' => new MaintenanceTicketResource($ticket)]);
    }

    public function storeCleaningTask(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|uuid|exists:properties,id',
            'notes' => 'nullable|string',
        ]);

        $task = $this->maintenanceService->createCleaningTask($validated);
        $task->load('property', 'cleaner');

        return response()->json(['data' => new CleaningTaskResource($task)], 201);
    }

    public function updateCleaningStatus(Request $request, CleaningTask $task)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,assigned,in_progress,inspection,ready',
        ]);

        $task = $this->maintenanceService->updateCleaningStatus($task, $validated['status']);
        $task->load('property', 'cleaner');

        return response()->json(['data' => new CleaningTaskResource($task)]);
    }
}
