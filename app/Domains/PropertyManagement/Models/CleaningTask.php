<?php

namespace App\Domains\PropertyManagement\Models;

use App\Models\AccommodationBooking;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CleaningTask extends Model
{
    use HasUuids;

    protected $fillable = [
        'property_id',
        'accommodation_booking_id',
        'assigned_cleaner_id',
        'inspector_id',
        'status',
        'notes',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function booking()
    {
        return $this->belongsTo(AccommodationBooking::class, 'accommodation_booking_id');
    }

    public function cleaner()
    {
        return $this->belongsTo(User::class, 'assigned_cleaner_id');
    }

    public function inspector()
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }
}
