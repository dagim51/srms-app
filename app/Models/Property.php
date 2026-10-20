<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use HasUuids, SoftDeletes;
    protected $guarded = [];

    protected $casts = [
        'bathrooms' => 'float',
    ];

    public function listings()
    {
        return $this->hasMany(Listing::class);
    }

    public function category()
    {
        return $this->belongsTo(PropertyCategory::class);
    }

    public function type()
    {
        return $this->belongsTo(PropertyType::class);
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'property_amenities')
            ->withPivot('value');
    }

    public function features()
    {
        return $this->belongsToMany(PropertyFeature::class, 'property_feature_links', 'property_id', 'feature_id')
            ->withPivot('value');
    }

    public function media()
    {
        return $this->hasMany(PropertyMedia::class)->orderBy('sort_order');
    }

    public function primaryMedia()
    {
        return $this->hasOne(PropertyMedia::class)->where('kind', 'image')->orderBy('sort_order');
    }

    public function getCoverImageAttribute(): ?string
    {
        $media = $this->media()->first();
        return $media ? $media->url : null;
    }
}
