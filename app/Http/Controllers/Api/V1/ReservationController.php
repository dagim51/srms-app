<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function index(Request $request)
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
            
        return response()->json(['data' => $reservations]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'listing_id' => 'required|exists:listings,id',
            'notes' => 'nullable|string',
        ]);
        
        $listing = \App\Models\Listing::findOrFail($validated['listing_id']);
        
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
        
        return response()->json(['data' => $reservation], 201);
    }

    public function update(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:confirmed,cancelled',
        ]);
        
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
        
        return response()->json(['data' => $reservation]);
    }
}
