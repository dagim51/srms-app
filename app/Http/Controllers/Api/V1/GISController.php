<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Services\GIS\AdvancedGIS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class GISController extends Controller
{
    public function __construct(
        protected AdvancedGIS $gis
    ) {}

    /**
     * Search properties within radius of a point
     */
    public function radiusSearch(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius_km' => 'required|numeric|min:0.1|max:100',
            'purpose' => 'nullable|string',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'bedrooms' => 'nullable|integer|min:0',
            'property_type' => 'nullable|string',
            'city' => 'nullable|string',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $results = $this->gis->searchWithinRadius(
            $validated['latitude'],
            $validated['longitude'],
            $validated['radius_km'],
            $request->only(['purpose', 'min_price', 'max_price', 'bedrooms', 'property_type', 'city']),
            $validated['limit'] ?? 50
        );

        return response()->json([
            'data' => $results,
            'meta' => [
                'center' => [
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                ],
                'radius_km' => $validated['radius_km'],
                'count' => $results->count(),
            ],
        ]);
    }

    /**
     * Search within a custom polygon boundary
     */
    public function polygonSearch(Request $request)
    {
        $validated = $request->validate([
            'coordinates' => 'required|array|min:4',
            'coordinates.*' => 'required|array|size:2',
            'coordinates.*.*' => 'numeric',
            'purpose' => 'nullable|string',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'bedrooms' => 'nullable|integer|min:0',
            'property_type' => 'nullable|string',
            'city' => 'nullable|string',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        // Validate polygon (first and last points should match or be close)
        $coords = $validated['coordinates'];
        if (count($coords) < 4) {
            return response()->json([
                'message' => 'Polygon must have at least 4 points (including closing point)',
            ], 422);
        }

        $results = $this->gis->searchWithinPolygon(
            $coords,
            $request->only(['purpose', 'min_price', 'max_price', 'bedrooms', 'property_type', 'city']),
            $request->integer('limit', 50)
        );

        return response()->json([
            'data' => $results,
            'meta' => [
                'polygon_points' => count($coords),
                'count' => $results->count(),
            ],
        ]);
    }

    /**
     * K-nearest neighbors search
     */
    public function nearest(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'k' => 'nullable|integer|min:1|max:50',
            'purpose' => 'nullable|string',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'bedrooms' => 'nullable|integer|min:0',
            'property_type' => 'nullable|string',
            'city' => 'nullable|string',
        ]);

        $results = $this->gis->kNearestNeighbors(
            $validated['latitude'],
            $validated['longitude'],
            $validated['k'] ?? 10,
            $request->only(['purpose', 'min_price', 'max_price', 'bedrooms', 'property_type', 'city'])
        );

        return response()->json([
            'data' => $results,
            'meta' => [
                'center' => [
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                ],
                'k' => $validated['k'] ?? 10,
            ],
        ]);
    }

    /**
     * Get density heatmap for an area
     */
    public function heatmap(Request $request)
    {
        $validated = $request->validate([
            'min_lat' => 'required|numeric|between:-90,90',
            'max_lat' => 'required|numeric|between:-90,90',
            'min_lon' => 'required|numeric|between:-180,180',
            'max_lon' => 'required|numeric|between:-180,180',
            'grid_size' => 'nullable|integer|min:10|max:200',
        ]);

        if ($validated['min_lat'] >= $validated['max_lat']) {
            return response()->json([
                'message' => 'min_lat must be less than max_lat',
            ], 422);
        }

        if ($validated['min_lon'] >= $validated['max_lon']) {
            return response()->json([
                'message' => 'min_lon must be less than max_lon',
            ], 422);
        }

        $heatmap = $this->gis->getDensityHeatmap(
            $validated['min_lat'],
            $validated['min_lon'],
            $validated['max_lat'],
            $validated['max_lon'],
            $validated['grid_size'] ?? 100
        );

        return response()->json([
            'data' => $heatmap,
        ]);
    }

    /**
     * Find property clusters (DBSCAN)
     */
    public function clusters(Request $request)
    {
        $validated = $request->validate([
            'min_lat' => 'required|numeric|between:-90,90',
            'max_lat' => 'required|numeric|between:-90,90',
            'min_lon' => 'required|numeric|between:-180,180',
            'max_lon' => 'required|numeric|between:-180,180',
            'eps_km' => 'nullable|numeric|min:0.1|max:10',
            'min_points' => 'nullable|integer|min:2|max:20',
        ]);

        if ($validated['min_lat'] >= $validated['max_lat'] || $validated['min_lon'] >= $validated['max_lon']) {
            return response()->json([
                'message' => 'Invalid bounds',
            ], 422);
        }

        $clusters = $this->gis->findClusters(
            $validated['min_lat'],
            $validated['min_lon'],
            $validated['max_lat'],
            $validated['max_lon'],
            $validated['eps_km'] ?? 1.0,
            $validated['min_points'] ?? 3
        );

        return response()->json([
            'data' => $clusters,
        ]);
    }

    /**
     * Calculate commute distance/time
     */
    public function commute(Request $request)
    {
        $validated = $request->validate([
            'from_lat' => 'required|numeric|between:-90,90',
            'from_lon' => 'required|numeric|between:-180,180',
            'to_lat' => 'required|numeric|between:-90,90',
            'to_lon' => 'required|numeric|between:-180,180',
            'mode' => 'nullable|string|in:walking,cycling,driving,transit',
        ]);

        $commute = $this->gis->calculateCommute(
            $validated['from_lat'],
            $validated['from_lon'],
            $validated['to_lat'],
            $validated['to_lon'],
            $validated['mode'] ?? 'driving'
        );

        return response()->json([
            'data' => $commute,
        ]);
    }

    /**
     * Get nearby amenities
     */
    public function amenities(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius_km' => 'nullable|numeric|min:0.5|max:10',
        ]);

        $amenities = $this->gis->getNearbyAmenities(
            $validated['latitude'],
            $validated['longitude'],
            $validated['radius_km'] ?? 2
        );

        return response()->json([
            'data' => $amenities,
        ]);
    }

    /**
     * Set property boundary (for owners/agents)
     */
    public function setBoundary(Request $request, string $propertyId)
    {
        $property = Property::findOrFail($propertyId);

        // Authorization check
        Gate::authorize('update', $property);

        $validated = $request->validate([
            'coordinates' => 'required|array|min:4',
            'coordinates.*' => 'required|array|size:2',
            'coordinates.*.*' => 'numeric',
        ]);

        $success = $this->gis->setPropertyBoundary($property, $validated['coordinates']);

        if (! $success) {
            return response()->json([
                'message' => 'Invalid boundary polygon (too small or invalid geometry)',
            ], 422);
        }

        return response()->json([
            'message' => 'Property boundary updated successfully',
            'data' => $property->fresh(['property_boundary', 'property_point']),
        ]);
    }

    /**
     * Get properties intersecting a boundary
     */
    public function intersecting(Request $request)
    {
        $validated = $request->validate([
            'boundary_wkt' => 'required|string',
            'purpose' => 'nullable|string',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'bedrooms' => 'nullable|integer|min:0',
            'property_type' => 'nullable|string',
            'city' => 'nullable|string',
        ]);

        $results = $this->gis->getPropertiesIntersecting(
            $validated['boundary_wkt'],
            $request->only(['purpose', 'min_price', 'max_price', 'bedrooms', 'property_type', 'city'])
        );

        return response()->json([
            'data' => $results,
        ]);
    }

    /**
     * Get area statistics for a boundary
     */
    public function areaStatistics(Request $request)
    {
        $validated = $request->validate([
            'boundary_wkt' => 'required|string',
        ]);

        $stats = $this->gis->getAreaStatistics($validated['boundary_wkt']);

        return response()->json([
            'data' => $stats,
        ]);
    }

    /**
     * Get isochrone (reachable area within time)
     */
    public function isochrone(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'minutes' => 'nullable|integer|min:1|max:120',
            'mode' => 'nullable|string|in:walking,cycling,driving,transit',
        ]);

        $isochrone = $this->gis->getIsochrone(
            $validated['latitude'],
            $validated['longitude'],
            $validated['minutes'] ?? 15,
            $validated['mode'] ?? 'driving'
        );

        return response()->json([
            'data' => $isochrone,
        ]);
    }

    /**
     * Calculate commute between two points
     */
    public function commuteBetween(Request $request)
    {
        $validated = $request->validate([
            'from_lat' => 'required|numeric|between:-90,90',
            'from_lon' => 'required|numeric|between:-180,180',
            'to_lat' => 'required|numeric|between:-90,90',
            'to_lon' => 'required|numeric|between:-180,180',
            'mode' => 'nullable|string|in:walking,cycling,driving,transit',
        ]);

        $commute = $this->gis->calculateCommute(
            $validated['from_lat'],
            $validated['from_lon'],
            $validated['to_lat'],
            $validated['to_lon'],
            $validated['mode'] ?? 'driving'
        );

        return response()->json([
            'data' => $commute,
        ]);
    }
}
