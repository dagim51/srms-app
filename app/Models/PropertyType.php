<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PropertyType extends Model
{
    use HasUuids;
    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(PropertyCategory::class);
    }
}
