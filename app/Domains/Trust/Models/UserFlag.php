<?php

namespace App\Domains\Trust\Models;

use App\Domains\Identity\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserFlag extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'reporter_id',
        'assigned_moderator_id',
        'reason',
        'description',
        'severity',
        'status',
        'moderator_notes',
        'context_id',
        'context_type',
        'reported_at',
        'reviewed_at',
        'resolved_at',
    ];

    protected $casts = [
        'reported_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function assignedModerator()
    {
        return $this->belongsTo(User::class, 'assigned_moderator_id');
    }

    public function context()
    {
        return $this->morphTo('context');
    }

    public function isPending(): bool
    {
        return in_array($this->status, ['pending', 'under_review']);
    }

    public function isResolved(): bool
    {
        return in_array($this->status, ['dismissed', 'validated', 'escalated_to_dispute', 'user_suspended']);
    }
}
