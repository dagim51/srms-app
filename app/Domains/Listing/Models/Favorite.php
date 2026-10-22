<?php

namespace App\Domains\Listing\Models;

use App\Models\Property;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    use HasUuids;

    protected $guarded = [];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
