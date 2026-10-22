<?php

namespace App\Domains\Property\Models;

use App\Models\Listing;
use App\Models\Region;
use App\Models\SubCity;
use App\Models\Woreda;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * @property string $id
 * @property string|null $address
 * @property-read SubCity|null $subCity
 * @property-read Zone|null $zone
 * @property-read Woreda|null $woreda
 * @property-read Region|null $region
 */
class Property extends Model
{
    use HasUuids, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'bathrooms' => 'float',
        'property_point' => 'geometry:point',
        'property_boundary' => 'geometry:polygon',
    ];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function subCity(): BelongsTo
    {
        return $this->belongsTo(SubCity::class);
    }

    public function woreda(): BelongsTo
    {
        return $this->belongsTo(Woreda::class);
    }

    public function getLocationSummaryAttribute(): string
    {
        $parts = [];
        if ($this->subCity) {
            $parts[] = $this->subCity->name_en.' Sub-City';
        } elseif ($this->zone) {
            $parts[] = $this->zone->name_en.' Zone';
        }

        if ($this->woreda) {
            $parts[] = $this->woreda->name_en;
        }

        if (! empty($parts)) {
            return implode(', ', $parts);
        }

        return $this->address ?? __('Location not set');
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PropertyCategory::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(PropertyType::class);
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'property_amenities')
            ->withPivot('value');
    }

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(PropertyFeature::class, 'property_feature_links', 'property_id', 'feature_id')
            ->withPivot('value');
    }

    public function media(): HasMany
    {
        return $this->hasMany(PropertyMedia::class)->orderBy('sort_order');
    }

    public function primaryMedia(): HasOne
    {
        return $this->hasOne(PropertyMedia::class)->where('kind', 'image')->orderBy('sort_order');
    }

    public function getCoverImageAttribute(): ?string
    {
        $media = $this->media()->first();

        return $media ? $media->url : null;
    }

    /**
     * Scope to filter properties within a radius using PostGIS (ST_DWithin) when available,
     * falling back to Haversine in PHP for SQLite.
     */
    public function scopeWithinRadius($query, float $latitude, float $longitude, float $radiusKm)
    {
        $connection = DB::connection()->getDriverName();

        if ($connection === 'pgsql' || $connection === 'testing-pgsql') {
            $point = "ST_SetSRID(ST_MakePoint($longitude, $latitude), 4326)::geography";
            $distanceMeters = $radiusKm * 1000;

            return $query->where(function ($q) use ($point, $distanceMeters) {
                $q->where(function ($sub) use ($point, $distanceMeters) {
                    $sub->whereNotNull('property_point')
                        ->whereRaw("ST_DWithin(property_point::geography, $point, ?)", [$distanceMeters]);
                })->orWhere(function ($sub) use ($point, $distanceMeters) {
                    $sub->whereNull('property_point')
                        ->whereNotNull('latitude')
                        ->whereNotNull('longitude')
                        ->whereRaw("ST_DWithin(ST_SetSRID(ST_MakePoint(longitude, latitude), 4326)::geography, $point, ?)", [$distanceMeters]);
                });
            });
        }

        $latRange = $radiusKm / 111.32;
        $lonRange = $radiusKm / (111.32 * cos(deg2rad($latitude)));

        return $query->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereBetween('latitude', [$latitude - $latRange, $latitude + $latRange])
            ->whereBetween('longitude', [$longitude - $lonRange, $longitude + $lonRange]);
    }

    /**
     * Add distance attribute to properties using PostGIS (ST_Distance) when available,
     * falling back to Haversine in PHP.
     */
    public function scopeWithDistance($query, float $latitude, float $longitude)
    {
        $connection = DB::connection()->getDriverName();

        if ($connection === 'pgsql' || $connection === 'testing-pgsql') {
            $point = "ST_SetSRID(ST_MakePoint($longitude, $latitude), 4326)::geography";

            return $query->selectRaw("*, ST_Distance(COALESCE(property_point::geography, ST_SetSRID(ST_MakePoint(longitude, latitude), 4326)::geography), $point) as distance_meters");
        }

        return $query;
    }
}
