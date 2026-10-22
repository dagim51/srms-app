<?php

namespace App\Domains\Stay\Http\Controllers\Api\V1;

use App\Domains\Stay\Http\Controllers\Controller;
use App\Domains\Stay\Models\Reservation;
use App\Http\Requests\Reservation\StoreReservationRequest;
use App\Http\Requests\Reservation\UpdateReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Models\Listing;

class ReservationController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $reservations = Reservation::with(['listing.property', 'user:id,name'])
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhereHas('listing', function ($q) use ($user) {
                        $q->where('owner_actor_id', $user->id);
                    });
            })
            ->paginate(15);

        return response()->json(['data' => ReservationResource::collection($reservations)]);
    }

    public function store(StoreReservationRequest $request)
    {
        $validated = $request->validated();

        $listing = Listing::findOrFail($validated['listing_id']);

        if ($listing->owner_actor_id === auth()->id()) {
            return response()->json(['message' => 'You cannot reserve your own listing.'], 422);
        }

        // Prevent if listing already has a pending or confirmed reservation
        $hasActiveReservation = Reservation::where('listing_id', $listing->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($hasActiveReservation) {
            return response()->json(['message' => 'This listing is already reserved or has a pending request.'], 409);
        }

        $reservation = Reservation::create([
            'listing_id' => $validated['listing_id'],
            'user_id' => auth()->id(),
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
            'expires_at' => now()->addDays(3),
        ]);

        $reservation->load('listing.property', 'user:id,name');

        return response()->json(['data' => new ReservationResource($reservation)], 201);
    }

    public function update(UpdateReservationRequest $request, Reservation $reservation)
    {
        $validated = $request->validated();
        $user = auth()->user();

        // Requester can cancel
        if ($validated['status'] === 'cancelled' && $reservation->user_id === $user->id) {
            // allowed
        }
        // Owner can confirm or cancel
        elseif (in_array($validated['status'], ['confirmed', 'cancelled']) && $reservation->listing->owner_actor_id === $user->id) {
            // allowed
        } else {
            abort(403, 'Unauthorized.');
        }

        if ($reservation->status !== 'pending') {
            return response()->json(['message' => 'Only pending reservations can be updated.'], 422);
        }

        $reservation->update(['status' => $validated['status']]);
        $reservation->load('listing.property', 'user:id,name');

        return response()->json(['data' => new ReservationResource($reservation)]);
    }
}
