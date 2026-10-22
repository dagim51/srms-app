<?php

namespace App\Domains\Location\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LocationTranslation extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = ['location_id', 'language_code', 'name', 'description'];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
