<?php

namespace App\Domains\Listing\Models;

use Illuminate\Database\Eloquent\Model;

class ListingSearchIndex extends Model
{
    protected $primaryKey = 'listing_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'amenities' => 'array',
        'published_at' => 'datetime',
    ];
}
