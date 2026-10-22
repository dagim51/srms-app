<?php

namespace App\Domains\Booking\Http\Controllers\Api\V1;

use App\Domains\Booking\Http\Controllers\Controller;
use App\Domains\Booking\Models\AccommodationBooking;
use App\Http\Requests\Accommodation\StoreAccommodationBookingRequest;
use App\Http\Requests\Accommodation\UpdateAccommodationBookingRequest;
use App\Http\Resources\AccommodationBookingResource;
use App\Services\Bookings\BookingManager;

class AccommodationBookingController extends Controller
{
    protected BookingManager $bookingManager;

    public function __construct(BookingManager $bookingManager)
    {
        $this->bookingManager = $bookingManager;
    }

    public function index()
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

        return response()->json(['data' => AccommodationBookingResource::collection($bookings)]);
    }

    public function store(StoreAccommodationBookingRequest $request)
    {
        $validated = $request->validated();

        try {
            $booking = $this->bookingManager->create($validated, auth()->id());
            $booking->load('accommodation.property', 'user:id,name');

            return response()->json(['data' => new AccommodationBookingResource($booking)], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }
    }

    public function update(UpdateAccommodationBookingRequest $request, AccommodationBooking $accommodationBooking)
    {
        $validated = $request->validated();
        $user = auth()->user();

        try {
            if ($validated['status'] === 'cancelled' && $accommodationBooking->user_id === $user->id) {
                // Guest cancelling
                $booking = $this->bookingManager->cancel($accommodationBooking, $user->id, 'guest_cancelled');
            } elseif (in_array($validated['status'], ['confirmed', 'cancelled']) && $accommodationBooking->accommodation->host_id === $user->id) {
                // Host confirming/cancelling
                if ($validated['status'] === 'confirmed') {
                    $booking = $this->bookingManager->confirm($accommodationBooking, $user->id);
                } else {
                    $booking = $this->bookingManager->cancel($accommodationBooking, $user->id, 'host_cancelled');
                }
            } else {
                abort(403, 'Unauthorized.');
            }

            $booking->load('accommodation.property', 'user:id,name');

            return response()->json(['data' => new AccommodationBookingResource($booking)]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
