<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kebele extends Model
{
    use HasUuids, SoftDeletes;
    protected $fillable = ['woreda_id', 'name_en', 'name_am', 'name_om', 'latitude', 'longitude', 'status'];

    public function woreda()
    {
        return $this->belongsTo(Woreda::class);
    }
    public function neighborhoods()
    {
        return $this->hasMany(Neighborhood::class);
    }
}
