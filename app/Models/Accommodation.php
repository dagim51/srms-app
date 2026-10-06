<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Accommodation extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'property_id',
        'host_id',
        'max_guests',
        'bedrooms',
        'beds',
        'bathrooms',
        'check_in_time',
        'check_out_time',
        'rules',
        'is_active',
    ];

    protected $casts = [
        'bathrooms' => 'float',
        'is_active' => 'boolean',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function host()
    {
        return $this->belongsTo(User::class, 'host_id');
    }
}
