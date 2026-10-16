<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AccommodationBooking;
use App\Services\Bookings\BookingManager;
use Illuminate\Http\Request;

class AccommodationBookingController extends Controller
{
    protected BookingManager $bookingManager;

    public function __construct(BookingManager $bookingManager)
    {
        $this->bookingManager = $bookingManager;
    }

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
        
        try {
            $booking = $this->bookingManager->create($validated, auth()->id());
            return response()->json(['data' => $booking], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }
    }

    public function update(Request $request, string $id)
    {
        $booking = AccommodationBooking::with('accommodation')->findOrFail($id);
        
        $validated = $request->validate([
            'status' => 'required|string|in:confirmed,cancelled',
        ]);
        
        $user = auth()->user();
        
        try {
            if ($validated['status'] === 'cancelled' && $booking->user_id === $user->id) {
                // Guest cancelling
                $booking = $this->bookingManager->cancel($booking, $user->id, 'guest_cancelled');
            } elseif (in_array($validated['status'], ['confirmed', 'cancelled']) && $booking->accommodation->host_id === $user->id) {
                // Host confirming/cancelling
                if ($validated['status'] === 'confirmed') {
                    $booking = $this->bookingManager->confirm($booking, $user->id);
                } else {
                    $booking = $this->bookingManager->cancel($booking, $user->id, 'host_cancelled');
                }
            } else {
                abort(403, 'Unauthorized.');
            }
            
            return response()->json(['data' => $booking]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}