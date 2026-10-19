<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommunityResource;
use App\Http\Resources\FacilityBookingResource;
use App\Http\Resources\SharedFacilityResource;
use App\Models\Community;
use App\Models\SharedFacility;
use Illuminate\Http\Request;

class CommunityController extends Controller
{
    public function storeCommunity(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'hoa_rules' => 'nullable|string',
        ]);

        $community = Community::create($validated);

        return response()->json(['data' => new CommunityResource($community)], 201);
    }

    public function storeFacility(Request $request, Community $community)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'nullable|integer',
            'booking_rules' => 'nullable|string',
        ]);

        $facility = $community->facilities()->create($validated);

        return response()->json(['data' => new SharedFacilityResource($facility)], 201);
    }

    public function storeBooking(Request $request, SharedFacility $facility)
    {
        $validated = $request->validate([
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        $booking = $facility->bookings()->create([
            'user_id' => auth()->id(),
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'status' => 'pending',
        ]);

        $booking->load('facility', 'user');

        return response()->json(['data' => new FacilityBookingResource($booking)], 201);
    }
}
