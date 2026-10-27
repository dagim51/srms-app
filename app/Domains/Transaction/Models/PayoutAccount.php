<?php

namespace App\Domains\Transaction\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayoutAccount extends Model
{
    use HasUuids;

    protected $table = 'payout_accounts';

    protected $fillable = [
        'user_id',
        'bank_name',
        'account_number',
        'account_name',
        'is_verified',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(Payout::class, 'payout_account_id');
    }
}
