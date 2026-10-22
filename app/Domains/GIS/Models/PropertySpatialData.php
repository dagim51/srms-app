<?php

namespace App\Domains\GIS\Models;

use App\Models\Property;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertySpatialData extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = ['property_id', 'coordinates', 'crs', 'accuracy', 'last_verified'];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
