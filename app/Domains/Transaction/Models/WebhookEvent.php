<?php

namespace App\Domains\Transaction\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class WebhookEvent extends Model
{
    use HasUuids;

    protected $fillable = ['provider', 'event_type', 'idempotency_key', 'payload', 'status', 'error_message', 'retry_count'];

    protected $casts = ['payload' => 'array'];
}
