<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Woreda extends Model
{
    use HasUuids, SoftDeletes;
    protected $fillable = ['zone_id', 'sub_city_id', 'name_en', 'name_am', 'name_om', 'latitude', 'longitude', 'status'];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }
    public function subCity()
    {
        return $this->belongsTo(SubCity::class);
    }
    public function kebeles()
    {
        return $this->hasMany(Kebele::class);
    }
}
