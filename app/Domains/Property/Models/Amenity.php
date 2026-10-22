<?php

namespace App\Domains\Property\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Amenity extends Model
{
    use HasUuids;

    protected $guarded = [];

    public function properties()
    {
        return $this->belongsToMany(Property::class, 'property_amenities')
            ->withPivot('value');
    }
}
