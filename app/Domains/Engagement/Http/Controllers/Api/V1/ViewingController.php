<?php

namespace App\Domains\Engagement\Http\Controllers\Api\V1;

use App\Domains\Engagement\Http\Controllers\Controller;
use App\Domains\Engagement\Models\Viewing;
use App\Http\Resources\ViewingResource;
use App\Models\Listing;
use Illuminate\Http\Request;

class ViewingController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $viewings = Viewing::where('requester_id', $user->id)
            ->orWhere('agent_id', $user->id)
            ->with(['listing.property', 'requester', 'agent'])
            ->get();

        return ViewingResource::collection($viewings);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'listing_id' => 'required|exists:listings,id',
            'scheduled_at' => 'required|date|after:now',
            'notes' => 'nullable|string',
        ]);

        $listing = Listing::findOrFail($validated['listing_id']);

        // Handle scheduling conflict
        $conflict = Viewing::where('agent_id', $listing->owner_actor_id)
            ->where('scheduled_at', $validated['scheduled_at'])
            ->whereIn('status', ['requested', 'confirmed'])
            ->exists();

        if ($conflict) {
            return response()->json(['message' => 'Agent is not available at this time.'], 409);
        }

        $viewing = Viewing::create([
            'listing_id' => $listing->id,
            'requester_id' => auth()->id(),
            'agent_id' => $listing->owner_actor_id,
            'status' => 'requested',
            'scheduled_at' => $validated['scheduled_at'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return (new ViewingResource($viewing))->response()->setStatusCode(201);
    }

    public function show(Viewing $viewing)
    {
        $user = auth()->user();

        if ($viewing->requester_id !== $user->id && $viewing->agent_id !== $user->id) {
            abort(403, 'Unauthorized access to viewing.');
        }

        $viewing->load(['listing.property', 'requester', 'agent']);

        return new ViewingResource($viewing);
    }

    public function update(Request $request, Viewing $viewing)
    {
        $user = auth()->user();

        if ($viewing->agent_id !== $user->id && $viewing->requester_id !== $user->id) {
            abort(403, 'Unauthorized modification blocked.');
        }

        $validated = $request->validate([
            'status' => 'required|in:requested,confirmed,rescheduled,completed,cancelled',
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        // Basic transition validation (e.g., only agents can confirm)
        if (in_array($validated['status'], ['confirmed', 'completed']) && $viewing->agent_id !== $user->id) {
            abort(403, 'Only agents can confirm or complete viewings.');
        }

        $viewing->update($validated);

        return new ViewingResource($viewing);
    }

    public function destroy(Viewing $viewing)
    {
        $user = auth()->user();

        if ($viewing->requester_id !== $user->id) {
            abort(403, 'Only the requester can cancel viewing.');
        }

        // Instead of hard delete, we mark it cancelled if the user "deletes" it
        $viewing->update(['status' => 'cancelled']);

        return response()->noContent();
    }
}
