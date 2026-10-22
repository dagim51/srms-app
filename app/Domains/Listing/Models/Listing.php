<?php

namespace App\Domains\Listing\Models;

use App\Jobs\IndexListingJob;
use App\Jobs\RemoveListingIndexJob;
use App\Models\Property;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string $owner_actor_id
 * @property string $title
 * @property string $status
 * @property-read Property|null $property
 */
class Listing extends Model
{
    use HasUuids, SoftDeletes;

    protected $guarded = [];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function moderations(): HasMany
    {
        return $this->hasMany(ListingModeration::class);
    }

    /**
     * Compute Listing Quality Score (0 to 100)
     */
    public function getQualityScoreAttribute(): int
    {
        $breakdown = $this->quality_breakdown;
        $score = 0;
        foreach ($breakdown as $item) {
            if ($item['passed']) {
                $score += $item['points'];
            }
        }

        return min(100, $score);
    }

    /**
     * Compute quality checklist with recommendations
     */
    public function getQualityBreakdownAttribute(): array
    {
        $items = [];
        $property = $this->relationLoaded('property') ? $this->property : $this->property()->first();

        // 1. Price is set (20 pts)
        $hasPrice = ! empty($this->price) && $this->price > 0;
        $items[] = [
            'key' => 'price',
            'label' => 'Pricing & Currency Set',
            'passed' => $hasPrice,
            'points' => 20,
            'tip' => 'Specify a realistic pricing structure to attract verified buyers or tenants.',
        ];

        // 2. Clear purpose / mode (15 pts)
        $hasPurpose = ! empty($this->purpose);
        $items[] = [
            'key' => 'purpose',
            'label' => 'Listing Mode & Transaction Type',
            'passed' => $hasPurpose,
            'points' => 15,
            'tip' => 'Choose between For Sale, For Rent, or Short Stay.',
        ];

        // 3. Property title & description (20 pts)
        $hasTitle = $property && ! empty($property->title) && strlen($property->title) >= 10;
        $hasDesc = $property && ! empty($property->description) && strlen($property->description) >= 40;
        $items[] = [
            'key' => 'description',
            'label' => 'Detailed Title & Description',
            'passed' => $hasTitle && $hasDesc,
            'points' => 20,
            'tip' => 'Provide a compelling title and at least 40 characters describing unique highlights.',
        ];

        // 4. Photos and Media (20 pts)
        $mediaCount = 0;
        if ($property) {
            $mediaCount = $property->relationLoaded('media') ? $property->media->count() : $property->media()->count();
        }
        $items[] = [
            'key' => 'media',
            'label' => 'High Quality Media (3+ Photos)',
            'passed' => $mediaCount >= 3,
            'points' => 20,
            'tip' => $mediaCount === 0 ? 'Upload at least 3 photos to increase buyer engagement by 5x.' : "Currently has {$mediaCount} photo(s). Adding 3+ unlocks top search ranking.",
        ];

        // 5. Geographic Coordinates (15 pts)
        $hasGeo = $property && ! empty($property->latitude) && ! empty($property->longitude);
        $items[] = [
            'key' => 'gis',
            'label' => 'GIS Map Coordinates Set',
            'passed' => $hasGeo,
            'points' => 15,
            'tip' => 'Pin the property on the map so discovery searches locate it.',
        ];

        // 6. Property Specifications (10 pts)
        $hasSpecs = $property && ($property->bedrooms > 0 || $property->building_area > 0 || $property->land_area > 0);
        $items[] = [
            'key' => 'specs',
            'label' => 'Bedrooms / Area Dimensions',
            'passed' => (bool) $hasSpecs,
            'points' => 10,
            'tip' => 'Provide bedroom count and area in m² to match search filters.',
        ];

        return $items;
    }

    /**
     * Get quality status badge info
     */
    public function getQualityTierAttribute(): array
    {
        $score = $this->quality_score;
        if ($score >= 80) {
            return ['label' => 'Excellent', 'class' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400', 'color' => '#10B981'];
        }
        if ($score >= 50) {
            return ['label' => 'Good', 'class' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400', 'color' => '#F59E0B'];
        }

        return ['label' => 'Needs Work', 'class' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400', 'color' => '#EF4444'];
    }

    public function getFormattedPriceAttribute(): string
    {
        $curr = $this->currency ?? 'ETB';
        $formatted = number_format((float) ($this->price ?? 0));

        return "{$curr} {$formatted}";
    }

    protected static function booted()
    {
        static::saved(function ($listing) {
            if ($listing->status === 'published') {
                IndexListingJob::dispatch($listing->id);
            } else {
                RemoveListingIndexJob::dispatch($listing->id);
            }
        });

        static::deleted(function ($listing) {
            RemoveListingIndexJob::dispatch($listing->id);
        });
    }
}
