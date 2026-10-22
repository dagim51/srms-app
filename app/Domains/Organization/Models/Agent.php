<?php

namespace App\Domains\Organization\Models;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agent extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'organization_id',
        'profile_photo',
        'verification_status',
        'service_areas',
        'languages',
        'properties_count',
        'reviews_count',
        'response_rate',
        'response_time',
    ];

    protected $casts = [
        'service_areas' => 'array',
        'languages' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function properties()
    {
        return $this->hasMany(Property::class);
    }
}
