<?php

namespace App\Domains\Booking\Models;

use App\Models\AccommodationBooking;
use App\Models\CancellationPolicy;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CancellationRecord extends Model
{
    use HasUuids;

    protected $table = 'cancellation_records';

    protected $fillable = [
        'booking_id',
        'cancelled_by',
        'policy_id',
        'original_amount',
        'refund_amount',
        'fee_amount',
        'reason',
        'status',
    ];

    protected $casts = [
        'original_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(AccommodationBooking::class, 'booking_id');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(CancellationPolicy::class, 'policy_id');
    }
}
