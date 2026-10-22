<?php

namespace App\Domains\Property\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PropertyFeature extends Model
{
    use HasUuids;

    protected $guarded = [];

    public function properties()
    {
        return $this->belongsToMany(Property::class, 'property_feature_links', 'feature_id', 'property_id')
            ->withPivot('value');
    }
}
