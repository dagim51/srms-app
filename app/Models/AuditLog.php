<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AuditLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'actor_id',
        'auditable_type',
        'auditable_id',
        'event',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'notes',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function auditable()
    {
        return $this->morphTo();
    }

    /**
     * Record a system or user audit event.
     */
    public static function log(
        string $event,
        $auditable = null,
        array $newValues = [],
        array $oldValues = [],
        ?string $notes = null,
        ?string $actorId = null
    ): self {
        $request = request();

        return static::create([
            'actor_id' => $actorId ?? auth()->id(),
            'auditable_type' => $auditable ? get_class($auditable) : null,
            'auditable_id' => $auditable ? $auditable->getKey() : null,
            'event' => $event,
            'old_values' => !empty($oldValues) ? $oldValues : null,
            'new_values' => !empty($newValues) ? $newValues : null,
            'ip_address' => $request ? $request->ip() : '127.0.0.1',
            'user_agent' => $request ? substr($request->userAgent() ?? '', 0, 255) : 'CLI',
            'notes' => $notes,
        ]);
    }
}
