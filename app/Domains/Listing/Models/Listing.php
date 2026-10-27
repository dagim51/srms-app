<?php

namespace App\Domains\Listing\Models;

use App\Jobs\IndexListingJob;
use App\Jobs\RemoveListingIndexJob;
use App\Models\Promotion;
use App\Models\Property;
use App\Services\Listing\ListingQualityScorer;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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

    public function promotions(): HasMany
    {
        return $this->hasMany(Promotion::class);
    }

    public function activePromotion(): HasOne
    {
        return $this->hasOne(Promotion::class)
            ->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->latestOfMany('amount_paid');
    }

    /**
     * Compute Listing Quality Score (0 to 100)
     */
    public function getQualityScoreAttribute(): int
    {
        if (isset($this->attributes['quality_score']) && $this->attributes['quality_score'] > 0) {
            return (int) $this->attributes['quality_score'];
        }

        return app(ListingQualityScorer::class)->calculate($this);
    }

    /**
     * Compute quality checklist with recommendations
     */
    public function getQualityBreakdownAttribute(): array
    {
        return app(ListingQualityScorer::class)->getBreakdown($this);
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
        static::saving(function ($listing) {
            $listing->quality_score = app(ListingQualityScorer::class)->calculate($listing);
        });

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
