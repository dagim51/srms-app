<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AccommodationBooking;
use Illuminate\Http\Request;

class AccommodationBookingController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $bookings = AccommodationBooking::with(['accommodation.property', 'user:id,name'])
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereHas('accommodation', function ($q) use ($user) {
                          $q->where('host_id', $user->id);
                      });
            })
            ->paginate(15);
            
        return response()->json(['data' => $bookings]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'accommodation_id' => 'required|exists:accommodations,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'guests' => 'required|integer|min:1',
        ]);
        
        $accommodation = \App\Models\Accommodation::findOrFail($validated['accommodation_id']);
        
        if ($accommodation->host_id === auth()->id()) {
            return response()->json(['message' => 'You cannot book your own accommodation.'], 422);
        }
        
        if ($validated['guests'] > $accommodation->max_guests) {
            return response()->json(['message' => 'Number of guests exceeds maximum allowed.'], 422);
        }
        
        // Check availability (conflicting bookings or blocks)
        $conflict = \App\Models\AccommodationAvailability::where('accommodation_id', $accommodation->id)
            ->where('start_date', '<', $validated['check_out_date'])
            ->where('end_date', '>', $validated['check_in_date'])
            ->exists();
            
        if ($conflict) {
            return response()->json(['message' => 'The selected dates are not available.'], 409);
        }
        
        // Also check existing confirmed/pending bookings just in case they aren't in availabilities table yet
        $bookingConflict = AccommodationBooking::where('accommodation_id', $accommodation->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('check_in_date', '<', $validated['check_out_date'])
            ->where('check_out_date', '>', $validated['check_in_date'])
            ->exists();
            
        if ($bookingConflict) {
            return response()->json(['message' => 'The selected dates are already booked.'], 409);
        }
        
        // Calculate price based on base price and rules
        $basePrice = $accommodation->property->price ?? 100; // Fallback
        
        $days = \Carbon\Carbon::parse($validated['check_in_date'])->diffInDays(\Carbon\Carbon::parse($validated['check_out_date']));
        $totalPrice = $basePrice * $days;
        
        $rules = \App\Models\AccommodationPricingRule::where('accommodation_id', $accommodation->id)
            ->where(function($q) use ($validated) {
                $q->where(function($q2) use ($validated) {
                    $q2->whereNull('start_date')
                       ->orWhere(function($q3) use ($validated) {
                           $q3->where('start_date', '<', $validated['check_out_date'])
                              ->where('end_date', '>', $validated['check_in_date']);
                       });
                });
            })
            ->orderByDesc('priority')
            ->get();
            
        if ($rules->isNotEmpty()) {
            // Apply first applicable rule for simplicity
            $rule = $rules->first();
            if ($rule->price_modifier_type === 'fixed') {
                $totalPrice = $rule->price_modifier_value * $days;
            } else {
                $totalPrice = $totalPrice * (1 + ($rule->price_modifier_value / 100));
            }
        }
        
        $booking = AccommodationBooking::create([
            'accommodation_id' => $accommodation->id,
            'user_id' => auth()->id(),
            'check_in_date' => $validated['check_in_date'],
            'check_out_date' => $validated['check_out_date'],
            'guests' => $validated['guests'],
            'total_price' => $totalPrice,
            'status' => 'pending',
        ]);
        
        // Optionally create availability block immediately
        \App\Models\AccommodationAvailability::create([
            'accommodation_id' => $accommodation->id,
            'start_date' => $validated['check_in_date'],
            'end_date' => $validated['check_out_date'],
            'type' => 'booked',
            'reason' => 'Booking ID: ' . $booking->id,
        ]);
        
        return response()->json(['data' => $booking], 201);
    }

    public function update(Request $request, AccommodationBooking $accommodationBooking)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:confirmed,cancelled',
        ]);
        
        $user = auth()->user();
        
        if ($validated['status'] === 'cancelled' && $accommodationBooking->user_id === $user->id) {
            // Guest cancelling
        } elseif (in_array($validated['status'], ['confirmed', 'cancelled']) && $accommodationBooking->accommodation->host_id === $user->id) {
            // Host confirming/cancelling
        } else {
            abort(403, 'Unauthorized.');
        }
        
        if ($accommodationBooking->status !== 'pending') {
            return response()->json(['message' => 'Only pending bookings can be updated.'], 422);
        }
        
        $accommodationBooking->update(['status' => $validated['status']]);
        
        // If cancelled, remove the availability block
        if ($validated['status'] === 'cancelled') {
            \App\Models\AccommodationAvailability::where('accommodation_id', $accommodationBooking->accommodation_id)
                ->where('type', 'booked')
                ->where('reason', 'like', '%' . $accommodationBooking->id . '%')
                ->delete();
        }
        
        return response()->json(['data' => $accommodationBooking]);
    }
}
