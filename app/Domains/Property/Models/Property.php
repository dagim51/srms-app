<?php

namespace App\Domains\Property\Models;

use App\Domains\Identity\Models\User;
use App\Domains\Transaction\Models\Document;
use App\Models\Listing;
use App\Models\Organization;
use App\Models\PropertyPriceHistory;
use App\Models\PropertyStatusHistory;
use App\Models\Region;
use App\Models\SubCity;
use App\Models\Woreda;
use App\Models\Zone;
use App\Support\Traits\Auditable;
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
 * @property string|null $cadastral_parcel_id
 * @property string|null $block_number
 * @property string|null $woreda_cadastre_sheet
 * @property string|null $land_tenure_type
 * @property int|null $lease_years_remaining
 * @property string|null $carta_number
 * @property string|null $deed_document_id
 * @property-read Document|null $deedDocument
 * @property-read SubCity|null $subCity
 * @property-read Zone|null $zone
 * @property-read Woreda|null $woreda
 * @property-read Region|null $region
 */
class Property extends Model
{
    use Auditable, HasUuids, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'bathrooms' => 'float',
        'lease_years_remaining' => 'integer',
    ];

    public function deedDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'deed_document_id');
    }

    public function isLeasehold(): bool
    {
        return str_starts_with((string) $this->land_tenure_type, 'leasehold');
    }

    public function getCadastralReference(): string
    {
        return $this->cadastral_parcel_id ?? 'UNREGISTERED';
    }

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

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
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

    public function priceHistory(): HasMany
    {
        return $this->hasMany(PropertyPriceHistory::class)->orderByDesc('created_at')->orderByDesc('id');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(PropertyStatusHistory::class)->orderByDesc('created_at')->orderByDesc('id');
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
