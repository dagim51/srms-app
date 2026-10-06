<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

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
