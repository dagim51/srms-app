<?php

namespace App\Domains\GIS\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MapFeature extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = ['name', 'category', 'geometry', 'properties', 'status'];

    protected $casts = [
        'properties' => 'array',
    ];
}
