<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Search\SearchRequest;
use App\Models\ListingSearchIndex;

class SearchController extends Controller
{
    public function search(SearchRequest $request)
    {
        $validated = $request->validated();
        $query = ListingSearchIndex::query();

        if (! empty($validated['q'])) {
            $query->where('title', 'like', '%'.$validated['q'].'%');
        }

        if (! empty($validated['purpose'])) {
            $query->where('purpose', $validated['purpose']);
        }

        if (! empty($validated['min_price'])) {
            $query->where('price', '>=', $validated['min_price']);
        }

        if (! empty($validated['max_price'])) {
            $query->where('price', '<=', $validated['max_price']);
        }

        if (! empty($validated['bedrooms'])) {
            $query->where('bedrooms', '>=', $validated['bedrooms']);
        }

        if (! empty($validated['property_type'])) {
            $query->where('property_type_code', $validated['property_type']);
        }

        if (! empty($validated['amenities'])) {
            // Assume amenities is a comma-separated list of codes
            $amenities = explode(',', $validated['amenities']);
            foreach ($amenities as $amenity) {
                // SQLite JSON querying is limited without extension, so we fallback to LIKE for testing
                // In PostgreSQL this would be: ->whereJsonContains('amenities', $amenity);
                $query->where('amenities', 'like', '%"'.$amenity.'"%');
            }
        }

        if (! empty($validated['latitude']) && ! empty($validated['longitude']) && ! empty($validated['radius'])) {
            $lat = $validated['latitude'];
            $lon = $validated['longitude'];
            $radius = $validated['radius'];
            $latRange = $radius / 111.32;
            $lonRange = $radius / (111.32 * cos(deg2rad($lat)));
            $query->whereBetween('latitude', [$lat - $latRange, $lat + $latRange])
                ->whereBetween('longitude', [$lon - $lonRange, $lon + $lonRange]);
        }

        // Prioritize active promotions (sponsored boost)
        $query->orderByRaw('CASE WHEN EXISTS (
            SELECT 1 FROM promotions 
            WHERE promotions.listing_id = listing_search_indices.listing_id 
            AND promotions.status = "active" 
            AND promotions.end_date >= CURRENT_TIMESTAMP
        ) THEN 1 ELSE 0 END DESC');

        if (! empty($validated['sort'])) {
            $sort = $validated['sort'];
            if ($sort === 'price_asc') {
                $query->orderBy('price', 'asc');
            } elseif ($sort === 'price_desc') {
                $query->orderBy('price', 'desc');
            } elseif ($sort === 'newest') {
                $query->orderBy('published_at', 'desc');
            }
        } else {
            // Default to newest (can add relevance later if using full-text)
            $query->orderBy('published_at', 'desc');
        }

        $results = $query->paginate(20);

        // Filter exact radius in PHP (if location provided)
        if (! empty($validated['latitude']) && ! empty($validated['longitude']) && ! empty($validated['radius'])) {
            $lat = $validated['latitude'];
            $lon = $validated['longitude'];
            $radius = $validated['radius'];

            $filteredItems = collect($results->items())->filter(function ($item) use ($lat, $lon, $radius) {
                if (! $item->latitude || ! $item->longitude) {
                    return false;
                }
                $earthRadius = 6371;
                $latDelta = deg2rad($item->latitude - $lat);
                $lonDelta = deg2rad($item->longitude - $lon);
                $a = sin($latDelta / 2) * sin($latDelta / 2) +
                     cos(deg2rad($lat)) * cos(deg2rad($item->latitude)) *
                     sin($lonDelta / 2) * sin($lonDelta / 2);
                $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

                return ($earthRadius * $c) <= $radius;
            })->values();

            $results->setCollection($filteredItems);
        }

        return response()->json($results);
    }
}
