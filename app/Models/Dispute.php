<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dispute extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'complainant_id',
        'respondent_id',
        'assigned_moderator_id',
        'disputable_type',
        'disputable_id',
        'type',
        'subject',
        'description',
        'amount_in_dispute',
        'currency',
        'status',
        'priority',
        'resolution',
        'resolution_notes',
        'opened_at',
        'assigned_at',
        'first_response_at',
        'resolved_at',
        'closed_at',
        'sla_hours',
        'sla_breached',
    ];

    protected $casts = [
        'amount_in_dispute' => 'decimal:2',
        'sla_breached' => 'boolean',
        'opened_at' => 'datetime',
        'assigned_at' => 'datetime',
        'first_response_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function complainant()
    {
        return $this->belongsTo(User::class, 'complainant_id');
    }

    public function respondent()
    {
        return $this->belongsTo(User::class, 'respondent_id');
    }

    public function assignedModerator()
    {
        return $this->belongsTo(User::class, 'assigned_moderator_id');
    }

    public function disputable()
    {
        return $this->morphTo();
    }

    public function evidence()
    {
        return $this->hasMany(DisputeEvidence::class);
    }

    public function isOpen(): bool
    {
        return in_array($this->status, ['open', 'under_review', 'awaiting_evidence', 'awaiting_response']);
    }

    public function isClosed(): bool
    {
        return in_array($this->status, ['resolved', 'closed']);
    }

    public function canTransitionTo(string $newStatus): bool
    {
        $transitions = [
            'open' => ['under_review', 'awaiting_evidence', 'closed'],
            'under_review' => ['awaiting_evidence', 'awaiting_response', 'mediated', 'resolved', 'closed'],
            'awaiting_evidence' => ['under_review', 'awaiting_response', 'closed'],
            'awaiting_response' => ['under_review', 'mediated', 'resolved', 'closed'],
            'mediated' => ['resolved', 'closed'],
            'resolved' => ['closed'],
            'closed' => [],
            'escalated' => ['under_review', 'awaiting_evidence', 'closed'],
        ];

        return in_array($newStatus, $transitions[$this->status] ?? []);
    }
}