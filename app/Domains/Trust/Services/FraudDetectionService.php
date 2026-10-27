<?php

namespace App\Domains\Trust\Services;

use App\Domains\Identity\Models\LoginEvent;
use App\Domains\Listing\Models\Listing;
use App\Domains\Listing\Models\ListingFlag;
use App\Domains\Property\Models\PropertyMedia;
use App\Domains\Trust\Models\UserFlag;
use App\Models\User;

class FraudDetectionService
{
    /**
     * Check if a listing price is severely anomalous (>70% below neighborhood average).
     *
     * @param  float  $threshold  Fraction below average to trigger flag (0.70 = 70% below average)
     */
    public function checkPriceAnomaly(Listing $listing, float $threshold = 0.70, ?string $reporterId = null): ?ListingFlag
    {
        $listingPrice = (float) $listing->price;
        if ($listingPrice <= 0) {
            return null;
        }

        $property = $listing->property;
        $subCityId = $property?->sub_city_id;
        $purpose = $listing->purpose ?? $listing->listing_type ?? 'For Rent';

        // Query comparable active listings
        $compQuery = Listing::where('id', '!=', $listing->id)
            ->where(function ($q) use ($purpose) {
                $q->where('purpose', $purpose)
                    ->orWhere('purpose', strtolower($purpose));
            })
            ->whereIn('status', ['active', 'published']);

        if ($subCityId) {
            $compQuery->whereHas('property', function ($q) use ($subCityId) {
                $q->where('sub_city_id', $subCityId);
            });
        }

        $avgPrice = (float) $compQuery->avg('price');

        // Fallback to all active listings of same purpose if no neighborhood comps
        if ($avgPrice <= 0) {
            $avgPrice = (float) Listing::where('id', '!=', $listing->id)
                ->where(function ($q) use ($purpose) {
                    $q->where('purpose', $purpose)
                        ->orWhere('purpose', strtolower($purpose));
                })
                ->whereIn('status', ['active', 'published'])
                ->avg('price');
        }

        if ($avgPrice > 0) {
            $discountFraction = ($avgPrice - $listingPrice) / $avgPrice;

            if ($discountFraction >= $threshold) {
                $discountPercent = round($discountFraction * 100);
                $effectiveReporterId = $reporterId ?? auth()->id() ?? $listing->owner_actor_id ?? User::first()?->id;

                if (! $effectiveReporterId) {
                    return null;
                }

                return ListingFlag::create([
                    'listing_id' => $listing->id,
                    'reporter_id' => $effectiveReporterId,
                    'reason' => 'Severe price anomaly detected (>70% below average)',
                    'description' => "Listing price of {$listingPrice} is {$discountPercent}% below the market average ({$avgPrice}). Possible rental scam or fraudulent listing.",
                    'severity' => 'high',
                    'status' => 'open',
                    'reported_at' => now(),
                ]);
            }
        }

        return null;
    }

    /**
     * Check if multiple user accounts were registered from the same IP address within a short window.
     */
    public function checkRegistrationVelocity(
        string $ipAddress,
        int $windowMinutes = 10,
        int $thresholdCount = 3,
        ?string $targetUserId = null,
        ?string $reporterId = null
    ): ?UserFlag {
        $recentRegistrationsCount = LoginEvent::where('ip', $ipAddress)
            ->where('event_type', 'register')
            ->where('created_at', '>=', now()->subMinutes($windowMinutes))
            ->count();

        // Also check User records created within window if LoginEvent not yet populated
        if ($recentRegistrationsCount < $thresholdCount) {
            $userCount = User::where('created_at', '>=', now()->subMinutes($windowMinutes))->count();
            if ($userCount >= $thresholdCount && $recentRegistrationsCount > 0) {
                $recentRegistrationsCount = $userCount;
            }
        }

        if ($recentRegistrationsCount >= $thresholdCount) {
            $userId = $targetUserId;

            if (! $userId) {
                $latestEvent = LoginEvent::where('ip', $ipAddress)
                    ->where('event_type', 'register')
                    ->latest()
                    ->first();

                $userId = $latestEvent?->user_id;
            }

            if ($userId) {
                $effectiveReporterId = $reporterId ?? auth()->id() ?? $userId;

                return UserFlag::create([
                    'user_id' => $userId,
                    'reporter_id' => $effectiveReporterId,
                    'reason' => "High registration velocity from IP {$ipAddress}",
                    'description' => "Detected {$recentRegistrationsCount} accounts registered from identical IP within {$windowMinutes} minutes.",
                    'severity' => 'high',
                    'status' => 'pending',
                    'reported_at' => now(),
                ]);
            }
        }

        return null;
    }

    /**
     * Check if a property media file duplicate exists on a different property listing.
     */
    public function checkDuplicateMedia(PropertyMedia $media, ?string $reporterId = null): ?ListingFlag
    {
        if (empty($media->checksum)) {
            return null;
        }

        $duplicate = PropertyMedia::where('property_id', '!=', $media->property_id)
            ->where('checksum', $media->checksum)
            ->first();

        if ($duplicate) {
            $listing = Listing::where('property_id', $media->property_id)->first();

            if ($listing) {
                $effectiveReporterId = $reporterId ?? auth()->id() ?? $listing->owner_actor_id ?? User::first()?->id;

                if (! $effectiveReporterId) {
                    return null;
                }

                return ListingFlag::create([
                    'listing_id' => $listing->id,
                    'reporter_id' => $effectiveReporterId,
                    'reason' => 'Duplicate property image detected across multiple listings',
                    'description' => "Media checksum {$media->checksum} matches existing photo on property {$duplicate->property_id}.",
                    'severity' => 'high',
                    'status' => 'open',
                    'reported_at' => now(),
                ]);
            }
        }

        return null;
    }
}
