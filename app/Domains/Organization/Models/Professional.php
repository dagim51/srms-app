<?php

namespace App\Domains\Organization\Models;

use App\Models\ServiceListing;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Professional extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'organization_id',
        'profession_type',
        'license_number',
        'verification_status',
        'service_areas',
        'languages',
        'rating',
        'reviews_count',
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

    public function serviceListings()
    {
        return $this->hasMany(ServiceListing::class);
    }
}
