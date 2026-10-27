<?php

namespace App\Domains\Booking\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CancellationPolicy extends Model
{
    use HasUuids;

    protected $table = 'cancellation_policies';

    protected $fillable = [
        'name',
        'flexible_days',
        'refund_percentage',
        'fee_percentage',
        'description',
    ];

    protected $casts = [
        'flexible_days' => 'integer',
        'refund_percentage' => 'decimal:2',
        'fee_percentage' => 'decimal:2',
    ];

    public function cancellationRecords(): HasMany
    {
        return $this->hasMany(CancellationRecord::class, 'policy_id');
    }
}
