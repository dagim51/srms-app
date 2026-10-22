<?php

namespace App\Domains\Analytics\Models;

use App\Domains\Identity\Models\User;
use App\Domains\Property\Models\Property;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PropertyValuation extends Model
{
    use HasUuids;

    protected $fillable = [
        'property_id',
        'user_id',
        'estimated_value',
        'confidence_score',
        'projected_annual_rent',
        'cap_rate',
        'cash_on_cash_return',
        'inputs',
        'is_authoritative',
    ];

    protected $casts = [
        'estimated_value' => 'decimal:2',
        'confidence_score' => 'decimal:2',
        'projected_annual_rent' => 'decimal:2',
        'cap_rate' => 'decimal:4',
        'cash_on_cash_return' => 'decimal:4',
        'inputs' => 'array',
        'is_authoritative' => 'boolean',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
