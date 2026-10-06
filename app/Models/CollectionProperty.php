<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CollectionProperty extends Model
{
    use HasUuids;

    protected $guarded = [];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
