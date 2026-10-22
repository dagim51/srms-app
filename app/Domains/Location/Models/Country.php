<?php

namespace App\Domains\Location\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Country extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = ['code', 'name_en', 'name_am', 'name_om', 'latitude', 'longitude', 'status'];

    public function regions()
    {
        return $this->hasMany(Region::class);
    }
}
