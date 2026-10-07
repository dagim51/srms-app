<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class FacilityBooking extends Model
{
    use HasUuids;

    protected $fillable = [
        'shared_facility_id',
        'user_id',
        'start_time',
        'end_time',
        'status'
    ];

    public function facility()
    {
        return $this->belongsTo(SharedFacility::class, 'shared_facility_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
