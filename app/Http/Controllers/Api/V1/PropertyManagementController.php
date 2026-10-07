<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MaintenanceTicket;
use App\Models\CleaningTask;

class PropertyManagementController extends Controller
{
    public function storeTicket(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|uuid|exists:properties,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|string|in:low,medium,high,urgent'
        ]);

        $ticket = MaintenanceTicket::create([
            'property_id' => $validated['property_id'],
            'reported_by' => auth()->id(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'priority' => $validated['priority'] ?? 'medium',
            'status' => 'open'
        ]);

        return response()->json(['data' => $ticket], 201);
    }

    public function updateTicketStatus(Request $request, MaintenanceTicket $ticket)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:open,assigned,in_progress,waiting,completed,closed'
        ]);

        $ticket->update(['status' => $validated['status']]);

        return response()->json(['data' => $ticket]);
    }

    public function storeCleaningTask(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|uuid|exists:properties,id',
            'notes' => 'nullable|string'
        ]);

        $task = CleaningTask::create([
            'property_id' => $validated['property_id'],
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null
        ]);

        return response()->json(['data' => $task], 201);
    }

    public function updateCleaningStatus(Request $request, CleaningTask $task)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,assigned,in_progress,inspection,ready'
        ]);

        $task->update(['status' => $validated['status']]);

        return response()->json(['data' => $task]);
    }
}
