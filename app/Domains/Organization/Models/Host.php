<?php

namespace App\Domains\Organization\Models;

use App\Models\Accommodation;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Host extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'organization_id',
        'profile_photo',
        'verification_status',
        'properties_count',
        'reviews_count',
        'response_rate',
        'response_time',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function accommodations()
    {
        return $this->hasMany(Accommodation::class);
    }
}
