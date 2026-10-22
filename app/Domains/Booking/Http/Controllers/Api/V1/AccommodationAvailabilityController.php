<?php

namespace App\Domains\Booking\Http\Controllers\Api\V1;

use App\Domains\Booking\Http\Controllers\Controller;
use App\Http\Requests\Accommodation\IndexAccommodationAvailabilityRequest;
use App\Http\Requests\Accommodation\StoreAccommodationAvailabilityRequest;
use App\Http\Resources\AccommodationAvailabilityResource;
use App\Models\Accommodation;
use App\Models\AccommodationAvailability;

class AccommodationAvailabilityController extends Controller
{
    public function index(IndexAccommodationAvailabilityRequest $request)
    {
        $validated = $request->validated();

        $query = AccommodationAvailability::where('accommodation_id', $validated['accommodation_id']);

        if (! empty($validated['start_date'])) {
            $query->where('end_date', '>=', $validated['start_date']);
        }

        if (! empty($validated['end_date'])) {
            $query->where('start_date', '<=', $validated['end_date']);
        }

        return response()->json(['data' => AccommodationAvailabilityResource::collection($query->get())]);
    }

    public function store(StoreAccommodationAvailabilityRequest $request)
    {
        $validated = $request->validated();
        $accommodation = Accommodation::findOrFail($validated['accommodation_id']);

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

        return response()->json(['data' => new AccommodationAvailabilityResource($availability)], 201);
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
