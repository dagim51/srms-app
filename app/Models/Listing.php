<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Listing extends Model
{
    use HasUuids, SoftDeletes;
    protected $guarded = [];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function moderations()
    {
        return $this->hasMany(ListingModeration::class);
    }

    protected static function booted()
    {
        static::saved(function ($listing) {
            if ($listing->status === 'published') {
                \App\Jobs\IndexListingJob::dispatch($listing->id);
            } else {
                \App\Jobs\RemoveListingIndexJob::dispatch($listing->id);
            }
        });

        static::deleted(function ($listing) {
            \App\Jobs\RemoveListingIndexJob::dispatch($listing->id);
        });
    }
}
