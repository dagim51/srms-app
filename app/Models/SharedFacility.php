<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SharedFacility extends Model
{
    use HasUuids;

    protected $fillable = [
        'community_id',
        'name',
        'description',
        'capacity',
        'booking_rules'
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
