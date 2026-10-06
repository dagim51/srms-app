<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AccommodationAvailability;
use Illuminate\Http\Request;

class AccommodationAvailabilityController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'accommodation_id' => 'required|exists:accommodations,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);
        
        $query = AccommodationAvailability::where('accommodation_id', $validated['accommodation_id']);
        
        if (!empty($validated['start_date'])) {
            $query->where('end_date', '>=', $validated['start_date']);
        }
        
        if (!empty($validated['end_date'])) {
            $query->where('start_date', '<=', $validated['end_date']);
        }
        
        return response()->json(['data' => $query->get()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'accommodation_id' => 'required|exists:accommodations,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'type' => 'nullable|string|in:blocked,booked',
            'reason' => 'nullable|string',
        ]);
        
        $accommodation = \App\Models\Accommodation::findOrFail($validated['accommodation_id']);
        
        // Only host can block dates
        if ($accommodation->host_id !== auth()->id()) {
            abort(403, 'Unauthorized.');
        }
        
        // Check for overlap
        $overlap = AccommodationAvailability::where('accommodation_id', $accommodation->id)
            ->where('start_date', '<=', $validated['end_date'])
            ->where('end_date', '>=', $validated['start_date'])
            ->exists();
            
        if ($overlap) {
            return response()->json(['message' => 'The selected dates overlap with an existing block or booking.'], 409);
        }
        
        $availability = AccommodationAvailability::create([
            'accommodation_id' => $accommodation->id,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'type' => $validated['type'] ?? 'blocked',
            'reason' => $validated['reason'] ?? null,
        ]);
        
        return response()->json(['data' => $availability], 201);
    }

    public function destroy(AccommodationAvailability $accommodationAvailability)
    {
        $accommodation = $accommodationAvailability->accommodation;
        
        if ($accommodation->host_id !== auth()->id()) {
            abort(403, 'Unauthorized.');
        }
        
        $accommodationAvailability->delete();
        
        return response()->noContent();
    }
}
