<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Accommodation extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'property_id',
        'listing_id',
        'host_id',
        'max_guests',
        'bedrooms',
        'beds',
        'bathrooms',
        'check_in_time',
        'check_out_time',
        'rules',
        'is_active',
        'minimum_stay',
        'maximum_stay',
        'cancellation_policy',
    ];

    protected $casts = [
        'bathrooms' => 'float',
        'is_active' => 'boolean',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'accommodation_amenities')
            ->withPivot('value');
    }

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(PropertyFeature::class, 'accommodation_feature_links', 'accommodation_id', 'feature_id')
            ->withPivot('value');
    }

    public function availabilities(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AccommodationAvailability::class);
    }

    public function pricingRules(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AccommodationPricingRule::class);
    }

    public function bookings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AccommodationBooking::class);
    }

    public function media(): HasMany
    {
        // Delegate to property's media
        return $this->property->media();
    }

    /**
     * Scope to filter by price range via listing
     */
    public function scopePriceRange(Builder $query, ?float $minPrice = null, ?float $maxPrice = null): Builder
    {
        if ($minPrice !== null) {
            $query->whereHas('listing', function ($q) use ($minPrice) {
                $q->where('price', '>=', $minPrice);
            });
        }
        if ($maxPrice !== null) {
            $query->whereHas('listing', function ($q) use ($maxPrice) {
                $q->where('price', '<=', $maxPrice);
            });
        }
        return $query;
    }
}
