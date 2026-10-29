<?php

namespace App\Domains\Booking\Models;

use App\Domains\Identity\Models\User;
use App\Domains\Stay\Models\Accommodation;
use App\Models\CancellationRecord;
use App\Models\CheckInOutRecord;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccommodationBooking extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'accommodation_id',
        'user_id',
        'check_in_date',
        'check_out_date',
        'guests',
        'total_price',
        'status',
        'expires_at',
        'confirmed_at',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'total_price' => 'float',
        'expires_at' => 'datetime',
    ];

    public function isExpired(): bool
    {
        return $this->status === 'holding' && $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * @return BelongsTo<Accommodation, $this>
     */
    public function accommodation(): BelongsTo
    {
        return $this->belongsTo(Accommodation::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<BookingGuest, $this>
     */
    public function bookingGuests(): HasMany
    {
        return $this->hasMany(BookingGuest::class, 'booking_id');
    }

    public function guestsRoster()
    {
        return $this->hasMany(BookingGuest::class, 'booking_id');
    }

    public function checkInOutRecords()
    {
        return $this->hasMany(CheckInOutRecord::class, 'booking_id');
    }

    public function cancellationRecord()
    {
        return $this->hasOne(CancellationRecord::class, 'booking_id');
    }
}
