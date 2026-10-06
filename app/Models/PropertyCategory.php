<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PropertyCategory extends Model
{
    use HasUuids;
    protected $guarded = [];

    public function types()
    {
        return $this->hasMany(PropertyType::class, 'category_id');
    }
}
