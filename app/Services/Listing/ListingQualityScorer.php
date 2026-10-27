<?php

namespace App\Services\Listing;

use App\Models\Listing;

class ListingQualityScorer
{
    /**
     * Calculate quality score (0 to 100) based on SRS Doc 04 §4.3 criteria.
     */
    public function calculate(Listing $listing): int
    {
        $breakdown = $this->getBreakdown($listing);
        $total = 0;

        foreach ($breakdown as $item) {
            $total += $item['awarded'];
        }

        return min(100, max(0, $total));
    }

    /**
     * Compute detailed quality score breakdown.
     */
    public function getBreakdown(Listing $listing): array
    {
        $property = $listing->relationLoaded('property') ? $listing->property : $listing->property()->first();
        $items = [];

        // 1. High-Res Media Count (Max 30 pts)
        $mediaCount = 0;
        if ($property) {
            $mediaCount = $property->relationLoaded('media') ? $property->media->count() : $property->media()->count();
        }

        $mediaPoints = 0;
        if ($mediaCount >= 5) {
            $mediaPoints = 30;
        } elseif ($mediaCount >= 3) {
            $mediaPoints = 20;
        } elseif ($mediaCount >= 1) {
            $mediaPoints = 10;
        }

        $items[] = [
            'criterion' => 'media',
            'label' => 'High-Resolution Photos & Media',
            'max_points' => 30,
            'awarded' => $mediaPoints,
            'points' => $mediaPoints,
            'passed' => $mediaCount >= 3,
            'details' => "{$mediaCount} photo(s) attached.",
            'tip' => 'Upload at least 5 photos to receive the maximum 30 points.',
        ];

        // 2. Detailed Description (Max 20 pts)
        $description = $property->description ?? $listing->description ?? '';
        $wordCount = str_word_count(strip_tags((string) $description));
        $descPoints = 0;

        if ($wordCount >= 200 || strlen((string) $description) >= 500) {
            $descPoints = 20;
        } elseif ($wordCount >= 50 || strlen((string) $description) >= 150) {
            $descPoints = 10;
        }

        $items[] = [
            'criterion' => 'description',
            'label' => 'Detailed Description',
            'max_points' => 20,
            'awarded' => $descPoints,
            'points' => $descPoints,
            'passed' => $descPoints >= 10,
            'details' => "{$wordCount} words provided.",
            'tip' => 'Provide a comprehensive description of at least 200 words detailing property highlights.',
        ];

        // 3. Geographic Coordinates Set (Max 15 pts)
        $hasCoords = false;
        if ($property && ! empty($property->latitude) && ! empty($property->longitude)) {
            $hasCoords = abs((float) $property->latitude) <= 90 && abs((float) $property->longitude) <= 180;
        }

        $items[] = [
            'criterion' => 'coordinates',
            'label' => 'GIS Map Coordinates',
            'max_points' => 15,
            'awarded' => $hasCoords ? 15 : 0,
            'points' => $hasCoords ? 15 : 0,
            'passed' => $hasCoords,
            'details' => $hasCoords ? 'Valid latitude and longitude pinned.' : 'Location coordinates missing.',
            'tip' => 'Pin the exact property location on the map to enable spatial radius discovery.',
        ];

        // 4. Amenities Tagged (Max 15 pts)
        $amenityCount = 0;
        if ($property) {
            $amenityCount = $property->relationLoaded('amenities') ? $property->amenities->count() : $property->amenities()->count();
        }

        $amenityPoints = 0;
        if ($amenityCount >= 3) {
            $amenityPoints = 15;
        } elseif ($amenityCount >= 1) {
            $amenityPoints = 5;
        }

        $items[] = [
            'criterion' => 'amenities',
            'label' => 'Tagged Amenities & Features',
            'max_points' => 15,
            'awarded' => $amenityPoints,
            'points' => $amenityPoints,
            'passed' => $amenityCount >= 3,
            'details' => "{$amenityCount} amenity tag(s) associated.",
            'tip' => 'Tag 3 or more amenities (e.g. Backup Generator, Water Tank, Security).',
        ];

        // 5. Verified Title Deed / Documents Attached (Max 20 pts)
        $hasDocument = false;
        if ($property) {
            $docCount = $property->relationLoaded('media')
                ? $property->media->where('kind', 'document')->count()
                : $property->media()->where('kind', 'document')->count();

            $hasDocument = $docCount > 0 || in_array($property->status, ['verified', 'approved']);
        }

        $items[] = [
            'criterion' => 'verified_documents',
            'label' => 'Verified Title Deed / Documentation',
            'max_points' => 20,
            'awarded' => $hasDocument ? 20 : 0,
            'points' => $hasDocument ? 20 : 0,
            'passed' => $hasDocument,
            'details' => $hasDocument ? 'Title deed / verification document attached.' : 'No verification document attached.',
            'tip' => 'Upload land holding certificate or deed to achieve verified badge and maximum trust score.',
        ];

        return $items;
    }
}
