<?php

namespace App\Services\Recommendation;

use App\Models\Listing;
use App\Models\ListingSearchIndex;
use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RecommendationEngine
{
    /**
     * Get personalized recommendations for a user
     */
    public function getForUser(User $user, int $limit = 10): Collection
    {
        // Get user's interaction history
        $interactions = $this->getUserInteractions($user);

        if ($interactions->isEmpty()) {
            // Cold start: return trending/popular listings
            return $this->getTrending($limit);
        }

        // Build user profile from interactions
        $profile = $this->buildUserProfile($interactions);

        // Score all candidate listings
        $candidates = $this->getCandidateListings($user, $limit * 3);

        $scored = $candidates->map(function ($listing) use ($profile) {
            $score = $this->calculateScore($listing, $profile);

            return ['listing' => $listing, 'score' => $score, 'reasons' => $this->getReasons($listing, $profile)];
        })->sortByDesc('score')
            ->take($limit)
            ->values();

        return $scored;
    }

    /**
     * Get similar listings (content-based)
     */
    public function getSimilar(Listing $listing, int $limit = 5): Collection
    {
        $cacheKey = "similar_listings_{$listing->id}_$limit";

        return Cache::remember($cacheKey, 3600, function () use ($listing, $limit) {
            return ListingSearchIndex::query()
                ->where('id', '!=', $listing->id)
                ->where('status', 'published')
                ->where(function ($q) use ($listing) {
                    // Same purpose
                    $q->where('purpose', $listing->purpose);

                    // Similar property type
                    if ($listing->property_type_code) {
                        $q->orWhere('property_type_code', $listing->property_type_code);
                    }

                    // Similar price range (±30%)
                    if ($listing->price) {
                        $min = $listing->price * 0.7;
                        $max = $listing->price * 1.3;
                        $q->orWhereBetween('price', [$min, $max]);
                    }

                    // Similar bedrooms
                    if ($listing->bedrooms) {
                        $q->orWhereBetween('bedrooms', [
                            max(0, $listing->bedrooms - 1),
                            $listing->bedrooms + 1,
                        ]);
                    }

                    // Similar location (same city/region)
                    if ($listing->city) {
                        $q->orWhere('city', $listing->city);
                    }
                })
                ->orderBy('published_at', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get trending listings (popularity-based)
     */
    public function getTrending(int $limit = 10): Collection
    {
        return Cache::remember("trending_listings_$limit", 1800, function () use ($limit) {
            // Use view count, favorites, inquiries as signals
            return ListingSearchIndex::query()
                ->where('status', 'published')
                ->orderByRaw('(COALESCE(view_count, 0) * 0.3 + COALESCE(favorite_count, 0) * 0.5 + COALESCE(inquiry_count, 0) * 0.2) DESC')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get "Because you viewed X" recommendations
     */
    public function getBecauseYouViewed(User $user, int $limit = 5): Collection
    {
        $recentViews = $this->getRecentViews($user, 5);

        if ($recentViews->isEmpty()) {
            return collect();
        }

        $recommendations = collect();

        foreach ($recentViews as $viewedListing) {
            $similar = $this->getSimilar($viewedListing, 3);
            $recommendations = $recommendations->merge($similar);
        }

        return $recommendations->unique('id')->take($limit)->values();
    }

    /**
     * Get "Users also liked" collaborative filtering
     */
    public function getCollaborative(User $user, int $limit = 5): Collection
    {
        // Find users with similar preferences
        $userFavorites = $user->favorites()->pluck('property_id');

        if ($userFavorites->isEmpty()) {
            return collect();
        }

        // Find other users who favorited same properties
        $similarUserIds = DB::table('favorites')
            ->whereIn('property_id', $userFavorites)
            ->where('user_id', '!=', $user->id)
            ->select('user_id')
            ->groupBy('user_id')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(20)
            ->pluck('user_id');

        if ($similarUserIds->isEmpty()) {
            return collect();
        }

        // Get their favorites that current user hasn't seen
        $recommendations = DB::table('favorites')
            ->whereIn('user_id', $similarUserIds)
            ->whereNotIn('property_id', $userFavorites)
            ->select('property_id', DB::raw('COUNT(*) as score'))
            ->groupBy('property_id')
            ->orderByDesc('score')
            ->limit($limit)
            ->pluck('property_id');

        return ListingSearchIndex::whereIn('id', $recommendations)
            ->where('status', 'published')
            ->get();
    }

    /**
     * Build user preference profile from interactions
     */
    private function buildUserProfile(Collection $interactions): array
    {
        $profile = [
            'preferred_purposes' => [],
            'price_range' => ['min' => 0, 'max' => PHP_INT_MAX],
            'preferred_bedrooms' => [],
            'preferred_property_types' => [],
            'preferred_locations' => [],
            'preferred_amenities' => [],
        ];

        foreach ($interactions as $interaction) {
            $listing = $interaction['listing'];
            $weight = $this->getInteractionWeight($interaction['type']);

            // Purposes
            if ($listing->purpose) {
                $profile['preferred_purposes'][$listing->purpose] =
                    ($profile['preferred_purposes'][$listing->purpose] ?? 0) + $weight;
            }

            // Price range
            if ($listing->price) {
                $profile['price_range']['min'] = min($profile['price_range']['min'] ?: $listing->price, $listing->price);
                $profile['price_range']['max'] = max($profile['price_range']['max'], $listing->price);
            }

            // Bedrooms
            if ($listing->bedrooms) {
                $profile['preferred_bedrooms'][$listing->bedrooms] =
                    ($profile['preferred_bedrooms'][$listing->bedrooms] ?? 0) + $weight;
            }

            // Property types
            if ($listing->property_type_code) {
                $profile['preferred_property_types'][$listing->property_type_code] =
                    ($profile['preferred_property_types'][$listing->property_type_code] ?? 0) + $weight;
            }

            // Locations
            if ($listing->city) {
                $profile['preferred_locations'][$listing->city] =
                    ($profile['preferred_locations'][$listing->city] ?? 0) + $weight;
            }
        }

        // Normalize preferences to top 3 each
        foreach (['preferred_purposes', 'preferred_bedrooms', 'preferred_property_types', 'preferred_locations'] as $key) {
            arsort($profile[$key]);
            $profile[$key] = array_keys(array_slice($profile[$key], 0, 3, true));
        }

        return $profile;
    }

    /**
     * Calculate recommendation score for a listing against user profile
     */
    private function calculateScore(ListingSearchIndex $listing, array $profile): float
    {
        $score = 0;
        $maxScore = 0;

        // Purpose match (weight: 30)
        if (in_array($listing->purpose, $profile['preferred_purposes'])) {
            $score += 30;
        }
        $maxScore += 30;

        // Price range (weight: 25)
        if ($listing->price && $listing->price >= $profile['price_range']['min'] * 0.8
            && $listing->price <= $profile['price_range']['max'] * 1.2) {
            $score += 25;
        }
        $maxScore += 25;

        // Bedrooms (weight: 15)
        if (in_array($listing->bedrooms, $profile['preferred_bedrooms'])) {
            $score += 15;
        }
        $maxScore += 15;

        // Property type (weight: 15)
        if (in_array($listing->property_type_code, $profile['preferred_property_types'])) {
            $score += 15;
        }
        $maxScore += 15;

        // Location (weight: 15)
        if (in_array($listing->city, $profile['preferred_locations'])) {
            $score += 15;
        }
        $maxScore += 15;

        return $maxScore > 0 ? ($score / $maxScore) * 100 : 0;
    }

    /**
     * Get human-readable reasons for recommendation
     */
    private function getReasons(ListingSearchIndex $listing, array $profile): array
    {
        $reasons = [];

        if (in_array($listing->purpose, $profile['preferred_purposes'])) {
            $reasons[] = "Matches your preferred purpose: {$listing->purpose}";
        }

        if ($listing->price && $listing->price >= $profile['price_range']['min'] * 0.8
            && $listing->price <= $profile['price_range']['max'] * 1.2) {
            $reasons[] = 'Within your price range';
        }

        if (in_array($listing->bedrooms, $profile['preferred_bedrooms'])) {
            $reasons[] = "{$listing->bedrooms} bedrooms - matches your preference";
        }

        if (in_array($listing->property_type_code, $profile['preferred_property_types'])) {
            $reasons[] = "Property type: {$listing->property_type_code}";
        }

        if (in_array($listing->city, $profile['preferred_locations'])) {
            $reasons[] = "Located in {$listing->city} - area you've shown interest in";
        }

        return $reasons;
    }

    /**
     * Get interaction weight by type
     */
    private function getInteractionWeight(string $type): float
    {
        return match ($type) {
            'purchase' => 1.0,
            'inquiry' => 0.8,
            'favorite' => 0.7,
            'view' => 0.3,
            'viewing_booked' => 0.9,
            'offer_made' => 0.95,
            default => 0.2,
        };
    }

    /**
     * Get user's recent property interactions
     */
    private function getUserInteractions(User $user): Collection
    {
        // This would typically come from an analytics/events table
        // For now, combine favorites, inquiries, viewings
        $interactions = collect();

        // Favorites
        $user->favorites()->with('property.listings')->each(function ($fav) use ($interactions) {
            if ($listing = $fav->property->listings->first()) {
                $interactions->push(['listing' => $listing, 'type' => 'favorite']);
            }
        });

        // Inquiries
        $user->inquiries()->with('listing')->each(function ($inquiry) use ($interactions) {
            $interactions->push(['listing' => $inquiry->listing, 'type' => 'inquiry']);
        });

        // Viewings
        $user->viewings()->with('listing')->each(function ($viewing) use ($interactions) {
            $interactions->push(['listing' => $viewing->listing, 'type' => 'viewing_booked']);
        });

        // Offers
        $user->offers()->with('listing')->each(function ($offer) use ($interactions) {
            $interactions->push(['listing' => $offer->listing, 'type' => 'offer_made']);
        });

        return $interactions;
    }

    /**
     * Get recent views from tracking (placeholder)
     */
    private function getRecentViews(User $user, int $limit): Collection
    {
        // In production, this would query a view tracking table
        // For now, return recent inquiries/favorites as proxy
        return $user->inquiries()
            ->with('listing')
            ->latest()
            ->limit($limit)
            ->get()
            ->pluck('listing')
            ->filter();
    }

    /**
     * Get candidate listings for scoring
     */
    private function getCandidateListings(User $user, int $limit): Collection
    {
        $excludedIds = $user->favorites()->pluck('property_id')
            ->merge($user->inquiries()->pluck('listing_id'))
            ->merge($user->offers()->pluck('listing_id'))
            ->unique();

        return ListingSearchIndex::query()
            ->where('status', 'published')
            ->whereNotIn('id', $excludedIds)
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }
}
