<?php

namespace App\Domains\Booking\Models;

use App\Models\AccommodationBooking;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingGuest extends Model
{
    use HasUuids;

    protected $table = 'booking_guests';

    protected $fillable = [
        'booking_id',
        'full_name',
        'id_passport_number',
        'is_primary',
        'nationality',
        'phone',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(AccommodationBooking::class, 'booking_id');
    }
}
