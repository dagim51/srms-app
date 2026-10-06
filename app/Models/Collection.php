<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Collection extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'is_private' => 'boolean'
    ];

    public function properties()
    {
        return $this->hasMany(CollectionProperty::class)->orderBy('sort_order', 'asc');
    }
}
