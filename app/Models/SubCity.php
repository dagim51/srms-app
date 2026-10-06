<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubCity extends Model
{
    use HasUuids, SoftDeletes;
    protected $fillable = ['city_id', 'name_en', 'name_am', 'name_om', 'latitude', 'longitude', 'status'];

    public function city()
    {
        return $this->belongsTo(City::class);
    }
    public function woredas()
    {
        return $this->hasMany(Woreda::class);
    }
}
