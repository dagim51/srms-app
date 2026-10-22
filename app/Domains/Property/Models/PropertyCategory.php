<?php

namespace App\Domains\Property\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PropertyCategory extends Model
{
    use HasUuids;

    protected $guarded = [];

    public function types()
    {
        return $this->hasMany(PropertyType::class, 'category_id');
    }
}
