<?php

namespace Tests\Feature;

use App\Domains\CRM\Models\Commission;
use App\Domains\CRM\Models\CommissionRule;
use App\Domains\CRM\Services\CommissionCalculatorService;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CommissionEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculates_commission_with_default_rate_when_no_rules_match(): void
    {
        $agent = User::factory()->create();
        $service = app(CommissionCalculatorService::class);
        $dealId = (string) Str::uuid();

        // 1,000,000 ETB deal with no rule -> 2.5% default rate = 25,000 ETB
        $commission = $service->calculateAndRecord(
            dealId: $dealId,
            dealType: 'sale',
            grossAmount: 1000000.0,
            agentId: $agent->id,
            notes: 'Default brokerage rate deal'
        );

        $this->assertInstanceOf(Commission::class, $commission);
        $this->assertEquals(2.5, $commission->rate_percentage);
        $this->assertEquals(25000.0, $commission->commission_amount);
        $this->assertEquals('pending', $commission->status);

        $this->assertDatabaseHas('commissions', [
            'id' => $commission->id,
            'deal_id' => $dealId,
            'agent_id' => $agent->id,
            'commission_amount' => 25000.0,
        ]);
    }

    public function test_calculates_commission_with_custom_tiered_rule(): void
    {
        $org = Organization::create([
            'name' => 'Addis Premium Realty',
            'slug' => 'addis-premium-realty',
        ]);

        $agent = User::factory()->create();

        // Tier 1 rule: Up to 5M ETB -> 3%
        CommissionRule::create([
            'organization_id' => $org->id,
            'rule_type' => 'sale',
            'percentage' => 3.0,
            'min_deal_amount' => 0,
            'max_deal_amount' => 5000000,
            'is_active' => true,
        ]);

        // Tier 2 rule: Over 5M ETB -> 4% + 50,000 ETB bonus
        CommissionRule::create([
            'organization_id' => $org->id,
            'rule_type' => 'sale',
            'percentage' => 4.0,
            'fixed_amount' => 50000.0,
            'min_deal_amount' => 5000001,
            'max_deal_amount' => null,
            'is_active' => true,
        ]);

        $service = app(CommissionCalculatorService::class);

        $dealId = (string) Str::uuid();

        // Test Tier 2 deal: 8,000,000 ETB -> 4% (320,000) + 50,000 = 370,000 ETB
        $commission = $service->calculateAndRecord(
            dealId: $dealId,
            dealType: 'sale',
            grossAmount: 8000000.0,
            agentId: $agent->id,
            organizationId: $org->id,
            notes: 'High tier luxury sale'
        );

        $this->assertEquals(4.0, $commission->rate_percentage);
        $this->assertEquals(370000.0, $commission->commission_amount);
        $this->assertEquals('pending', $commission->status);
    }
}
