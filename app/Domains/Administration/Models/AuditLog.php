<?php

namespace App\Domains\Administration\Models;

use App\Domains\Identity\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

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

        $auditableType = null;
        if ($auditable) {
            $class = get_class($auditable);
            $legacy = 'App\\Models\\'.class_basename($class);
            $auditableType = class_exists($legacy) ? $legacy : $class;
        }

        return static::create([
            'actor_id' => $actorId ?? auth()->id(),
            'auditable_type' => $auditableType,
            'auditable_id' => $auditable ? $auditable->getKey() : null,
            'event' => $event,
            'old_values' => ! empty($oldValues) ? $oldValues : null,
            'new_values' => ! empty($newValues) ? $newValues : null,
            'ip_address' => $request ? $request->ip() : '127.0.0.1',
            'user_agent' => $request ? substr($request->userAgent() ?? '', 0, 255) : 'CLI',
            'notes' => $notes,
        ]);
    }
}
