<?php

namespace App\Domains\CheckInOut\Models;

use App\Models\AccommodationBooking;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckInOutRecord extends Model
{
    use HasUuids;

    protected $table = 'check_in_outs';

    protected $fillable = [
        'booking_id',
        'direction',
        'action_time',
        'staff_id',
        'room_condition_notes',
        'key_handed_over',
    ];

    protected $casts = [
        'action_time' => 'datetime',
        'key_handed_over' => 'boolean',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(AccommodationBooking::class, 'booking_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
