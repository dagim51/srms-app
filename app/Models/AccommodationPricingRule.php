<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AccommodationPricingRule extends Model
{
    use HasUuids;

    protected $fillable = [
        'accommodation_id',
        'type',
        'start_date',
        'end_date',
        'price_modifier_type',
        'price_modifier_value',
        'priority',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'price_modifier_value' => 'float',
    ];

    public function accommodation()
    {
        return $this->belongsTo(Accommodation::class);
    }
}
