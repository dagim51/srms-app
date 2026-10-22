<?php

namespace App\Domains\Listing\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'is_private' => 'boolean',
    ];

    public function properties()
    {
        return $this->hasMany(CollectionProperty::class)->orderBy('sort_order', 'asc');
    }
}
