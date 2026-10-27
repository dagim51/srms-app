<?php

namespace App\Domains\CRM\Services;

use App\Domains\CRM\Models\Commission;
use App\Domains\CRM\Models\CommissionRule;

class CommissionCalculatorService
{
    /**
     * Calculate and record a commission for a closed deal.
     */
    public function calculateAndRecord(
        string $dealId,
        string $dealType,
        float $grossAmount,
        string $agentId,
        ?string $organizationId = null,
        ?string $notes = null
    ): Commission {
        $rule = $this->resolveApplicableRule($dealType, $grossAmount, $organizationId);

        if ($rule) {
            $ratePercentage = (float) ($rule->percentage ?? 0.0);
            $fixedAmount = (float) ($rule->fixed_amount ?? 0.0);
            $commissionAmount = round(($grossAmount * ($ratePercentage / 100)) + $fixedAmount, 2);
        } else {
            // Default 2.5% standard brokerage commission
            $ratePercentage = 2.5;
            $commissionAmount = round($grossAmount * 0.025, 2);
        }

        return Commission::create([
            'agent_id' => $agentId,
            'deal_id' => $dealId,
            'deal_type' => $dealType,
            'gross_amount' => $grossAmount,
            'rate_percentage' => $ratePercentage,
            'commission_amount' => $commissionAmount,
            'currency' => 'ETB',
            'status' => 'pending',
            'notes' => $notes,
        ]);
    }

    /**
     * Resolve the most applicable commission rule.
     */
    public function resolveApplicableRule(string $dealType, float $grossAmount, ?string $organizationId = null): ?CommissionRule
    {
        $query = CommissionRule::where('is_active', true)
            ->where(function ($q) use ($dealType) {
                $q->where('rule_type', $dealType)
                    ->orWhere('rule_type', 'standard');
            })
            ->where(function ($q) use ($grossAmount) {
                $q->whereNull('min_deal_amount')
                    ->orWhere('min_deal_amount', '<=', $grossAmount);
            })
            ->where(function ($q) use ($grossAmount) {
                $q->whereNull('max_deal_amount')
                    ->orWhere('max_deal_amount', '>=', $grossAmount);
            });

        if ($organizationId) {
            $query->where(function ($q) use ($organizationId) {
                $q->where('organization_id', $organizationId)
                    ->orWhereNull('organization_id');
            });
        }

        return $query->orderByRaw('CASE WHEN organization_id IS NOT NULL THEN 0 ELSE 1 END')
            ->orderByDesc('percentage')
            ->first();
    }
}
