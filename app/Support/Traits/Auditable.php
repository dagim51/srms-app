<?php

namespace App\Support\Traits;

use App\Domains\Administration\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    /**
     * Boot the auditable trait for a model.
     */
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            $hidden = array_flip($model->getHidden());
            $newValues = array_diff_key($model->getAttributes(), $hidden);

            AuditLog::log(
                event: 'created',
                auditable: $model,
                newValues: $newValues,
                oldValues: [],
                notes: class_basename($model).' created',
                actorId: Auth::id()
            );
        });

        static::updated(function ($model) {
            $hidden = array_flip($model->getHidden());
            $changes = array_diff_key($model->getChanges(), $hidden);
            $old = array_intersect_key($model->getOriginal(), $changes);

            AuditLog::log(
                event: 'updated',
                auditable: $model,
                newValues: $changes,
                oldValues: $old,
                notes: class_basename($model).' updated',
                actorId: Auth::id()
            );
        });

        static::deleted(function ($model) {
            $hidden = array_flip($model->getHidden());
            $oldValues = array_diff_key($model->getOriginal(), $hidden);

            AuditLog::log(
                event: 'deleted',
                auditable: $model,
                newValues: [],
                oldValues: $oldValues,
                notes: class_basename($model).' deleted',
                actorId: Auth::id()
            );
        });
    }
}
