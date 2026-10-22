<?php

namespace App\Domains\Location\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string|null $zone_id
 * @property string|null $sub_city_id
 * @property string $name_en
 * @property string|null $name_am
 * @property string|null $name_om
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string $status
 * @property-read Zone|null $zone
 * @property-read SubCity|null $subCity
 */
class Woreda extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = ['zone_id', 'sub_city_id', 'name_en', 'name_am', 'name_om', 'latitude', 'longitude', 'status'];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function subCity(): BelongsTo
    {
        return $this->belongsTo(SubCity::class);
    }

    public function kebeles(): HasMany
    {
        return $this->hasMany(Kebele::class);
    }
}
