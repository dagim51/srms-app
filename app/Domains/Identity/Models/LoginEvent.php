<?php

namespace App\Domains\Identity\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginEvent extends Model
{
    use HasUuids;

    protected $table = 'login_events';

    protected $fillable = [
        'user_id',
        'event_type',
        'ip',
        'device',
        'success',
        'occurred_at',
    ];

    protected $casts = [
        'success' => 'boolean',
        'occurred_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
