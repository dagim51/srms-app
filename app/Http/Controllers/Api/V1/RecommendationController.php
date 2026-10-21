<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ListingSearchIndex;
use App\Services\Recommendation\RecommendationEngine;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    public function __construct(
        protected RecommendationEngine $engine
    ) {}

    /**
     * Get personalized recommendations for authenticated user
     */
    public function forUser(Request $request)
    {
        $limit = $request->integer('limit', 10);
        $recommendations = $this->engine->getForUser($request->user(), $limit);

        return response()->json([
            'data' => $recommendations->map(function ($item) {
                return [
                    'listing' => $item['listing'],
                    'score' => round($item['score'], 1),
                    'reasons' => $item['reasons'],
                ];
            }),
        ]);
    }

    /**
     * Get similar listings to a specific listing
     */
    public function similar(Request $request, string $listingId)
    {
        $listing = ListingSearchIndex::findOrFail($listingId);
        $limit = $request->integer('limit', 5);

        $similar = $this->engine->getSimilar($listing, $limit);

        return response()->json([
            'data' => $similar,
        ]);
    }

    /**
     * Get trending/popular listings
     */
    public function trending(Request $request)
    {
        $limit = $request->integer('limit', 10);
        $trending = $this->engine->getTrending($limit);

        return response()->json([
            'data' => $trending,
        ]);
    }

    /**
     * Get "because you viewed" recommendations
     */
    public function becauseYouViewed(Request $request)
    {
        $limit = $request->integer('limit', 5);
        $recommendations = $this->engine->getBecauseYouViewed($request->user(), $limit);

        return response()->json([
            'data' => $recommendations,
        ]);
    }

    /**
     * Get collaborative filtering recommendations
     */
    public function collaborative(Request $request)
    {
        $limit = $request->integer('limit', 5);
        $recommendations = $this->engine->getCollaborative($request->user(), $limit);

        return response()->json([
            'data' => $recommendations,
        ]);
    }
}
