<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceProviderProfile;
use App\Models\ServiceListing;
use App\Models\ServiceRequest;

class ProfessionalServiceController extends Controller
{
    public function storeProfile(Request $request)
    {
        $validated = $request->validate([
            'business_name' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'category' => 'required|string|max:100',
        ]);

        $profile = ServiceProviderProfile::firstOrCreate(
            ['user_id' => $request->user()->id],
            $validated
        );

        return response()->json(['data' => $profile], 201);
    }

    public function storeListing(Request $request, ServiceProviderProfile $profile)
    {
        if ($profile->user_id !== $request->user()->id) abort(403, 'Unauthorized action.');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'price_unit' => 'required|string|in:hourly,fixed,per_sqm',
        ]);

        $listing = $profile->listings()->create($validated);
        return response()->json(['data' => $listing], 201);
    }

    public function storeRequest(Request $request, ServiceListing $listing)
    {
        $validated = $request->validate([
            'property_id' => 'nullable|exists:properties,id',
            'requirements' => 'required|string',
            'requested_date' => 'nullable|date',
        ]);

        $serviceReq = $listing->requests()->create([
            'customer_id' => $request->user()->id,
            'property_id' => $validated['property_id'] ?? null,
            'requirements' => $validated['requirements'],
            'requested_date' => $validated['requested_date'] ?? null,
            'status' => 'pending',
            'agreed_price' => $listing->price,
        ]);

        return response()->json(['data' => $serviceReq], 201);
    }

    public function updateRequestStatus(Request $request, ServiceRequest $serviceRequest)
    {
        // Only provider can accept/reject/complete, customer can cancel
        $isProvider = $serviceRequest->listing->profile->user_id === $request->user()->id;
        $isCustomer = $serviceRequest->customer_id === $request->user()->id;
        
        if (!$isProvider && !$isCustomer) abort(403, 'Unauthorized action.');

        $validated = $request->validate([
            'status' => 'required|string|in:accepted,rejected,completed,cancelled'
        ]);

        if ($validated['status'] === 'cancelled' && !$isCustomer) abort(403, 'Only customers can cancel.');
        if (in_array($validated['status'], ['accepted', 'rejected', 'completed']) && !$isProvider) {
            abort(403, 'Only providers can update to this status.');
        }

        $serviceRequest->update(['status' => $validated['status']]);
        return response()->json(['data' => $serviceRequest]);
    }
}
