<?php

namespace App\Domains\Stay\Http\Controllers\Api\V1;

use App\Domains\Stay\Http\Controllers\Controller;
use App\Domains\Stay\Models\Accommodation;
use App\Http\Requests\Accommodation\StoreAccommodationRequest;
use App\Http\Requests\Accommodation\UpdateAccommodationRequest;
use App\Http\Resources\AccommodationResource;
use App\Models\Property;

class AccommodationController extends Controller
{
    public function index()
    {
        $accommodations = Accommodation::with('property', 'host:id,name')
            ->where('is_active', true)
            ->paginate(15);

        return AccommodationResource::collection($accommodations);
    }

    public function store(StoreAccommodationRequest $request)
    {
        $validated = $request->validated();
        $property = Property::findOrFail($validated['property_id']);
        $validated['host_id'] = $request->user()->id;

        $accommodation = Accommodation::create($validated);

        return response()->json(['data' => new AccommodationResource($accommodation)], 201);
    }

    public function show(Accommodation $accommodation)
    {
        $accommodation->load(['property', 'host', 'amenities', 'availabilities', 'pricingRules', 'media', 'bookings']);

        return response()->json(['data' => new AccommodationResource($accommodation)]);
    }

    public function update(UpdateAccommodationRequest $request, Accommodation $accommodation)
    {
        $validated = $request->validated();
        $accommodation->update($validated);

        return response()->json(['data' => new AccommodationResource($accommodation)]);
    }

    public function destroy(Accommodation $accommodation)
    {
        $accommodation->delete();

        return response()->noContent();
    }
}
