<?php

namespace App\Domains\Transaction\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payout extends Model
{
    use HasUuids;

    protected $table = 'payouts';

    protected $fillable = [
        'payout_account_id',
        'amount',
        'fee',
        'currency',
        'status',
        'reference_number',
        'processed_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'float',
        'fee' => 'float',
        'processed_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(PayoutAccount::class, 'payout_account_id');
    }
}
