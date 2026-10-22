<?php

namespace App\Domains\Stay\Models;

use App\Models\Accommodation;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AccommodationAvailability extends Model
{
    use HasUuids;

    protected $fillable = [
        'accommodation_id',
        'start_date',
        'end_date',
        'type',
        'reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function accommodation()
    {
        return $this->belongsTo(Accommodation::class);
    }
}
