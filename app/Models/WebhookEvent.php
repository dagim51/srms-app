<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class WebhookEvent extends Model
{
    use HasUuids;

    protected $fillable = ['provider', 'event_type', 'idempotency_key', 'payload', 'status', 'error_message', 'retry_count'];
    protected $casts = ['payload' => 'array'];
}
