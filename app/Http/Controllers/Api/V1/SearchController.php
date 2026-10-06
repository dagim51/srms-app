<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ListingSearchIndex;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = ListingSearchIndex::query();

        if ($request->filled('q')) {
            $query->where('title', 'like', '%' . $request->q . '%');
        }

        if ($request->filled('purpose')) {
            $query->where('purpose', $request->purpose);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->filled('bedrooms')) {
            $query->where('bedrooms', '>=', $request->bedrooms);
        }

        if ($request->filled('property_type')) {
            $query->where('property_type_code', $request->property_type);
        }

        if ($request->filled('amenities')) {
            // Assume amenities is a comma-separated list of codes
            $amenities = explode(',', $request->amenities);
            foreach ($amenities as $amenity) {
                // SQLite JSON querying is limited without extension, so we fallback to LIKE for testing
                // In PostgreSQL this would be: ->whereJsonContains('amenities', $amenity);
                $query->where('amenities', 'like', '%"' . $amenity . '"%');
            }
        }

        if ($request->filled('latitude') && $request->filled('longitude') && $request->filled('radius')) {
            $lat = $request->latitude;
            $lon = $request->longitude;
            $radius = $request->radius;
            $latRange = $radius / 111.32;
            $lonRange = $radius / (111.32 * cos(deg2rad($lat)));
            $query->whereBetween('latitude', [$lat - $latRange, $lat + $latRange])
                  ->whereBetween('longitude', [$lon - $lonRange, $lon + $lonRange]);
        }

        if ($request->filled('sort')) {
            $sort = $request->sort;
            if ($sort === 'price_asc') $query->orderBy('price', 'asc');
            elseif ($sort === 'price_desc') $query->orderBy('price', 'desc');
            elseif ($sort === 'newest') $query->orderBy('published_at', 'desc');
        } else {
            // Default to newest (can add relevance later if using full-text)
            $query->orderBy('published_at', 'desc');
        }

        $results = $query->paginate(20);

        // Filter exact radius in PHP (if location provided)
        if ($request->filled('latitude') && $request->filled('longitude') && $request->filled('radius')) {
            $lat = $request->latitude;
            $lon = $request->longitude;
            $radius = $request->radius;
            
            $filteredItems = collect($results->items())->filter(function ($item) use ($lat, $lon, $radius) {
                if (!$item->latitude || !$item->longitude) return false;
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
