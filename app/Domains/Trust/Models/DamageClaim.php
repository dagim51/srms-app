<?php

namespace App\Domains\Trust\Models;

use App\Models\AccommodationBooking;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DamageClaim extends Model
{
    use HasUuids;

    protected $table = 'damage_claims';

    protected $fillable = [
        'booking_id',
        'host_id',
        'guest_id',
        'amount_claimed',
        'currency',
        'description',
        'evidence_photos',
        'status',
        'resolution_notes',
        'resolved_at',
    ];

    protected $casts = [
        'amount_claimed' => 'float',
        'evidence_photos' => 'array',
        'resolved_at' => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(AccommodationBooking::class, 'booking_id');
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_id');
    }
}
