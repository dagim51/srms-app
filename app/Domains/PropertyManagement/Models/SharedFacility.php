<?php

namespace App\Domains\PropertyManagement\Models;

use App\Models\Community;
use App\Models\FacilityBooking;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SharedFacility extends Model
{
    use HasUuids;

    protected $fillable = [
        'community_id',
        'name',
        'description',
        'capacity',
        'booking_rules',
    ];

    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    public function bookings()
    {
        return $this->hasMany(FacilityBooking::class);
    }
}
