<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model
{
    use HasUuids, SoftDeletes;
    protected $fillable = ['region_id', 'name_en', 'name_am', 'name_om', 'latitude', 'longitude', 'status'];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }
    public function subCities()
    {
        return $this->hasMany(SubCity::class);
    }
}
