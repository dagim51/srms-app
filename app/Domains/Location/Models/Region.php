<?php

namespace App\Domains\Location\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Region extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = ['country_id', 'name_en', 'name_am', 'name_om', 'latitude', 'longitude', 'status'];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function zones()
    {
        return $this->hasMany(Zone::class);
    }

    public function cities()
    {
        return $this->hasMany(City::class);
    }
}
