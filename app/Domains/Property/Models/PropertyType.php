<?php

namespace App\Domains\Property\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PropertyType extends Model
{
    use HasUuids;

    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(PropertyCategory::class);
    }
}
