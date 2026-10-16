<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AccommodationResource;
use App\Models\Accommodation;
use Illuminate\Http\Request;

class AccommodationController extends Controller
{
    public function index()
    {
        $accommodations = Accommodation::with('property', 'host:id,name')
            ->where('is_active', true)
            ->paginate(15);
            
        return AccommodationResource::collection($accommodations);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id|unique:accommodations,property_id',
            'max_guests' => 'nullable|integer|min:1',
            'bedrooms' => 'nullable|integer|min:0',
            'beds' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|numeric|min:0',
            'check_in_time' => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i',
            'rules' => 'nullable|string',
        ]);
        
        $property = \App\Models\Property::findOrFail($validated['property_id']);
        $user = auth()->user();
        
        // Only the property owner can configure it as an accommodation
        if ($property->owner_user_id !== $user->id) {
            abort(403, 'Unauthorized. Only the property owner can configure accommodation.');
        }
        
        $validated['host_id'] = $user->id;
        
        $accommodation = Accommodation::create($validated);
        
        return response()->json(['data' => new AccommodationResource($accommodation)], 201);
    }

    public function show(Accommodation $accommodation)
    {
        $accommodation->load(['property', 'host', 'amenities', 'availabilities', 'pricingRules', 'media', 'bookings']);
        return response()->json(['data' => new AccommodationResource($accommodation)]);
    }

    public function update(Request $request, Accommodation $accommodation)
    {
        if ($accommodation->host_id !== auth()->id()) {
            abort(403, 'Unauthorized.');
        }
        
        $validated = $request->validate([
            'max_guests' => 'nullable|integer|min:1',
            'bedrooms' => 'nullable|integer|min:0',
            'beds' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|numeric|min:0',
            'check_in_time' => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i',
            'rules' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);
        
        $accommodation->update($validated);
        
        return response()->json(['data' => new AccommodationResource($accommodation)]);
    }

    public function destroy(Accommodation $accommodation)
    {
        if ($accommodation->host_id !== auth()->id()) {
            abort(403, 'Unauthorized.');
        }
        
        $accommodation->delete();
        
        return response()->noContent();
    }
}