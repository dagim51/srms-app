<?php

namespace App\Domains\Location\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Zone extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = ['region_id', 'name_en', 'name_am', 'name_om', 'latitude', 'longitude', 'status'];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function woredas()
    {
        return $this->hasMany(Woreda::class);
    }
}
