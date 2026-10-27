<?php

namespace App\Services\Listing;

use App\Models\Listing;
use App\Models\Promotion;
use InvalidArgumentException;

class ListingPromotionService
{
    public const TIERS = [
        'basic' => ['daily_rate' => 50.00, 'boost_multiplier' => 1.2],
        'featured' => ['daily_rate' => 120.00, 'boost_multiplier' => 1.5],
        'premium' => ['daily_rate' => 250.00, 'boost_multiplier' => 2.0],
        'urgent' => ['daily_rate' => 180.00, 'boost_multiplier' => 1.8],
    ];

    /**
     * Promote a marketplace listing with a sponsored boost tier.
     */
    public function promote(
        Listing $listing,
        string $tier,
        int $durationDays = 7,
        ?float $customAmount = null,
        ?string $userId = null
    ): Promotion {
        $tier = strtolower($tier);
        if (! array_key_exists($tier, self::TIERS)) {
            throw new InvalidArgumentException("Invalid promotion tier: [{$tier}]. Supported: ".implode(', ', array_keys(self::TIERS)));
        }

        $durationDays = max(1, $durationDays);
        $amountPaid = $customAmount ?? (self::TIERS[$tier]['daily_rate'] * $durationDays);

        return Promotion::create([
            'listing_id' => $listing->id,
            'tier' => $tier,
            'start_date' => now(),
            'end_date' => now()->addDays($durationDays),
            'amount_paid' => $amountPaid,
            'status' => 'active',
            'created_by' => $userId ?? auth()->id(),
        ]);
    }

    /**
     * Increment impression metrics on search view.
     */
    public function recordImpression(Promotion $promotion): void
    {
        $promotion->increment('impressions_count');
    }

    /**
     * Increment click engagement metrics.
     */
    public function recordClick(Promotion $promotion): void
    {
        $promotion->increment('clicks_count');
    }
}
