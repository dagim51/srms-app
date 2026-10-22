<?php

namespace App\Traits;

use App\Models\AuditLog;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            AuditLog::log('created', $model, $model->getAttributes(), [], 'Record created');
        });

        static::updated(function ($model) {
            $changes = $model->getChanges();
            $original = array_intersect_key($model->getOriginal(), $changes);
            AuditLog::log('updated', $model, $changes, $original, 'Record updated');
        });

        static::deleted(function ($model) {
            AuditLog::log('deleted', $model, [], $model->getOriginal(), 'Record deleted');
        });
    }

    public function recordAudit(string $event, ?string $notes = null, array $extra = []): AuditLog
    {
        return AuditLog::log($event, $this, $extra, [], $notes);
    }
}
