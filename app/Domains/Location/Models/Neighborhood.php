<?php

namespace App\Domains\Location\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Neighborhood extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = ['kebele_id', 'name_en', 'name_am', 'name_om', 'latitude', 'longitude', 'status'];

    public function kebele()
    {
        return $this->belongsTo(Kebele::class);
    }
}
