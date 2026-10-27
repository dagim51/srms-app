<?php

namespace App\Domains\CRM\Services;

use App\Domains\CRM\Models\Lead;
use App\Models\User;

class LeadAssignmentService
{
    /**
     * Auto-assign a lead to an appropriate active real estate agent.
     */
    public function autoAssign(Lead $lead): ?User
    {
        $agent = null;

        if ($lead->organization_id) {
            // Find active member in the organization with least number of assigned leads
            $agent = User::whereHas('organizationMemberships', function ($q) use ($lead) {
                $q->where('organization_id', $lead->organization_id)
                    ->where(function ($sq) {
                        $sq->where('status', 'active')
                            ->orWhereNull('status');
                    });
            })
                ->withCount(['assignedLeads' => function ($q) {
                    $q->whereNotIn('status', ['won', 'lost']);
                }])
                ->orderBy('assigned_leads_count')
                ->first();
        }

        if (! $agent) {
            // Fallback: system-wide least-loaded user
            $agent = User::withCount(['assignedLeads' => function ($q) {
                $q->whereNotIn('status', ['won', 'lost']);
            }])
                ->orderBy('assigned_leads_count')
                ->first();
        }

        if ($agent) {
            $lead->update([
                'assigned_to' => $agent->id,
            ]);
        }

        return $agent;
    }
}
