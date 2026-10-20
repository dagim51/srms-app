<?php

namespace App\Services\Trust;

use App\Events\Trust\DisputeOpened;
use App\Events\Trust\DisputeResolved;
use App\Models\Dispute;
use Illuminate\Support\Facades\DB;

class DisputeService
{
    /**
     * File a new dispute.
     */
    public function fileDispute(string $complainantId, array $data): Dispute
    {
        return DB::transaction(function () use ($complainantId, $data) {
            $dispute = Dispute::create([
                'complainant_id' => $complainantId,
                'respondent_id' => $data['respondent_id'] ?? null,
                'disputable_type' => $data['disputable_type'] ?? \App\Models\User::class,
                'disputable_id' => $data['disputable_id'] ?? ($data['respondent_id'] ?? $complainantId),
                'type' => $data['type'] ?? 'general',
                'subject' => $data['subject'],
                'description' => $data['description'] ?? null,
                'amount_in_dispute' => $data['amount_in_dispute'] ?? 0,
                'currency' => $data['currency'] ?? 'ETB',
                'status' => 'open',
                'priority' => $data['priority'] ?? 'medium',
                'opened_at' => now(),
            ]);

            event(new DisputeOpened($dispute));

            return $dispute;
        });
    }

    /**
     * Assign a moderator to the dispute.
     */
    public function assignModerator(Dispute $dispute, string $moderatorId): Dispute
    {
        return DB::transaction(function () use ($dispute, $moderatorId) {
            $dispute->assigned_moderator_id = $moderatorId;
            $dispute->assigned_at = now();
            if ($dispute->status === 'open') {
                $dispute->status = 'under_review';
            }
            $dispute->save();

            return $dispute;
        });
    }

    /**
     * Resolve a dispute.
     */
    public function resolveDispute(Dispute $dispute, string $resolverId, string $resolution, ?string $notes = null): Dispute
    {
        return DB::transaction(function () use ($dispute, $resolverId, $resolution, $notes) {
            $dispute->status = 'resolved';
            $dispute->resolution = $resolution;
            $dispute->resolution_notes = $notes;
            $dispute->resolved_at = now();
            $dispute->save();

            event(new DisputeResolved($dispute, $resolverId));

            return $dispute;
        });
    }

    /**
     * Close a dispute.
     */
    public function closeDispute(Dispute $dispute, string $closerId, ?string $notes = null): Dispute
    {
        return DB::transaction(function () use ($dispute, $closerId, $notes) {
            $dispute->status = 'closed';
            if ($notes) {
                $dispute->resolution_notes = $notes;
            }
            $dispute->closed_at = now();
            $dispute->save();

            return $dispute;
        });
    }
}
