<?php

namespace App\Domains\Location\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string $city_id
 * @property string $name_en
 * @property string|null $name_am
 * @property string|null $name_om
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string $status
 * @property-read City|null $city
 */
class SubCity extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = ['city_id', 'name_en', 'name_am', 'name_om', 'latitude', 'longitude', 'status'];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function woredas(): HasMany
    {
        return $this->hasMany(Woreda::class);
    }
}
