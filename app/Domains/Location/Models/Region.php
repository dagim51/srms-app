<?php

namespace App\Domains\Location\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string $country_id
 * @property string $name_en
 * @property string|null $name_am
 * @property string|null $name_om
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string $status
 */
class Region extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = ['country_id', 'name_en', 'name_am', 'name_om', 'latitude', 'longitude', 'status'];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function zones(): HasMany
    {
        return $this->hasMany(Zone::class);
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }
}
