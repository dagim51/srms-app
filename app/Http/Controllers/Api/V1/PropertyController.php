<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Property;
use Illuminate\Support\Str;

class PropertyController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:property_categories,id',
            'type_id' => 'nullable|exists:property_types,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $validated['property_code'] = 'PRP-' . strtoupper(Str::random(8));
        $validated['owner_user_id'] = $request->user()->id;
        $validated['status'] = 'draft';

        $property = Property::create($validated);
        return response()->json($property, 201);
    }

    public function update(Request $request, Property $property)
    {
        \Illuminate\Support\Facades\Gate::authorize('update', $property);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:draft,pending_verification,verified,rejected',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $property->update($validated);
        return response()->json($property);
    }

    public function attachAmenity(Request $request, Property $property)
    {
        \Illuminate\Support\Facades\Gate::authorize('update', $property);
        $validated = $request->validate([
            'amenity_id' => 'required|exists:amenities,id',
            'value' => 'nullable|string'
        ]);

        $property->amenities()->syncWithoutDetaching([
            $validated['amenity_id'] => ['value' => $validated['value'] ?? null]
        ]);

        return response()->json(['message' => 'Amenity attached']);
    }

    public function detachAmenity(Request $request, Property $property, string $amenityId)
    {
        \Illuminate\Support\Facades\Gate::authorize('update', $property);
        $property->amenities()->detach($amenityId);
        return response()->json(['message' => 'Amenity detached']);
    }

    public function attachFeature(Request $request, Property $property)
    {
        \Illuminate\Support\Facades\Gate::authorize('update', $property);
        $validated = $request->validate([
            'feature_id' => 'required|exists:property_features,id',
            'value' => 'nullable|string'
        ]);

        $property->features()->syncWithoutDetaching([
            $validated['feature_id'] => ['value' => $validated['value'] ?? null]
        ]);

        return response()->json(['message' => 'Feature attached']);
    }

    public function index(Request $request)
    {
        $query = Property::query();

        if ($request->has('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->has('type_id')) {
            $query->where('type_id', $request->input('type_id'));
        }

        if ($request->has('amenity_id')) {
            $query->whereHas('amenities', function ($q) use ($request) {
                $q->where('amenities.id', $request->input('amenity_id'));
            });
        }

        if ($request->has('feature_id')) {
            $query->whereHas('features', function ($q) use ($request) {
                $q->where('property_features.id', $request->input('feature_id'));
            });
        }

        if ($request->has('bedrooms')) {
            $query->where('bedrooms', $request->input('bedrooms'));
        }

        return response()->json($query->paginate(15));
    }

    public function radiusSearch(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|numeric|min:0.1', // radius in km
        ]);

        $lat = $request->input('latitude');
        $lon = $request->input('longitude');
        $radius = $request->input('radius');

        // Bounding box calculation (Approximate, safe for SQLite testing without math extensions)
        // 1 degree of latitude is roughly 111.32 km
        $latRange = $radius / 111.32;
        $lonRange = $radius / (111.32 * cos(deg2rad($lat)));

        $properties = Property::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereBetween('latitude', [$lat - $latRange, $lat + $latRange])
            ->whereBetween('longitude', [$lon - $lonRange, $lon + $lonRange])
            ->get()
            ->filter(function ($property) use ($lat, $lon, $radius) {
                // Precise Haversine in PHP to filter out corners of the bounding box
                $earthRadius = 6371;
                $latDelta = deg2rad($property->latitude - $lat);
                $lonDelta = deg2rad($property->longitude - $lon);
                $a = sin($latDelta / 2) * sin($latDelta / 2) +
                     cos(deg2rad($lat)) * cos(deg2rad($property->latitude)) *
                     sin($lonDelta / 2) * sin($lonDelta / 2);
                $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
                $distance = $earthRadius * $c;
                
                $property->distance = $distance;
                return $distance <= $radius;
            })
            ->sortBy('distance')
            ->values();

        return response()->json(['data' => $properties]);
    }
}
