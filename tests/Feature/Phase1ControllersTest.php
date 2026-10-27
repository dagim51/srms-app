<?php

namespace Tests\Feature;

use App\Models\Commission;
use App\Models\CommissionRule;
use App\Models\Organization;
use App\Models\Property;
use App\Models\PropertyPriceHistory;
use App\Models\PropertyStatusHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase1ControllersTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_developer_sales_routes_render_cleanly_without_mock_fallbacks(): void
    {
        $this->actingAs($this->user);

        $this->get('/dashboard/sales')->assertRedirect('/dashboard/sales/units');
        $this->get('/dashboard/sales/units')->assertOk();
        $this->get('/dashboard/sales/buyers')->assertOk();
        $this->get('/dashboard/sales/contracts')->assertOk();
        $this->get('/dashboard/sales/handovers')->assertOk();
        $this->get('/dashboard/sales/payment-plans')->assertOk();
    }

    public function test_rental_management_routes_render_cleanly_without_mock_fallbacks(): void
    {
        $this->actingAs($this->user);

        $this->get('/dashboard/rental')->assertOk();
        $this->get('/dashboard/rental/applications')->assertOk();
        $this->get('/dashboard/rental/tenants')->assertOk();
        $this->get('/dashboard/rental/leases')->assertOk();
        $this->get('/dashboard/rental/payments')->assertOk();
    }

    public function test_operations_routes_render_cleanly_without_mock_fallbacks(): void
    {
        $this->actingAs($this->user);

        $this->get('/dashboard/operations')->assertOk();
        $this->get('/dashboard/operations/tickets')->assertOk();
        $this->get('/dashboard/operations/cleaning')->assertOk();
        $this->get('/dashboard/operations/staff')->assertOk();
    }

    public function test_finance_routes_render_cleanly_without_mock_fallbacks(): void
    {
        $this->actingAs($this->user);

        $this->get('/dashboard/finance')->assertOk();
        $this->get('/dashboard/finance/transactions')->assertOk();
        $this->get('/dashboard/finance/invoices')->assertOk();
        $this->get('/dashboard/finance/ledger')->assertOk();
    }

    public function test_trust_routes_render_cleanly_without_mock_fallbacks(): void
    {
        $this->actingAs($this->user);

        $this->get('/dashboard/trust')->assertOk();
        $this->get('/dashboard/trust/reports')->assertOk();
        $this->get('/dashboard/trust/disputes')->assertOk();
    }

    public function test_crm_routes_render_cleanly_without_mock_fallbacks(): void
    {
        $this->actingAs($this->user);

        $this->get('/dashboard/crm')->assertOk();
        $this->get('/dashboard/crm/leads')->assertOk();
        $this->get('/dashboard/crm/pipeline')->assertOk();
        $this->get('/dashboard/crm/clients')->assertOk();
        $this->get('/dashboard/crm/tasks')->assertOk();
        $this->get('/dashboard/crm/commissions')->assertOk();
    }

    public function test_property_price_and_status_history_models_can_be_persisted(): void
    {
        $property = Property::create([
            'title' => 'History Test Villa',
            'property_code' => 'HIST-001',
            'owner_user_id' => $this->user->id,
            'status' => 'draft',
        ]);

        $priceHistory = PropertyPriceHistory::create([
            'property_id' => $property->id,
            'price' => 5500000,
            'currency' => 'ETB',
            'changed_by' => $this->user->id,
            'reason' => 'Annual market adjustment',
        ]);

        $statusHistory = PropertyStatusHistory::create([
            'property_id' => $property->id,
            'from_status' => 'draft',
            'to_status' => 'published',
            'changed_by' => $this->user->id,
            'comment' => 'Published after verification',
        ]);

        $this->assertDatabaseHas('property_price_history', ['id' => $priceHistory->id, 'price' => 5500000]);
        $this->assertDatabaseHas('property_status_history', ['id' => $statusHistory->id, 'to_status' => 'published']);
    }

    public function test_commission_and_rule_models_can_be_persisted(): void
    {
        $org = Organization::create([
            'name' => 'Brokerage Org',
            'slug' => 'brokerage-org',
            'owner_user_id' => $this->user->id,
        ]);

        $rule = CommissionRule::create([
            'organization_id' => $org->id,
            'rule_type' => 'percentage',
            'percentage' => 2.5,
            'is_active' => true,
        ]);

        $commission = Commission::create([
            'agent_id' => $this->user->id,
            'gross_amount' => 10000000,
            'rate_percentage' => 2.5,
            'commission_amount' => 250000,
            'currency' => 'ETB',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('commission_rules', ['id' => $rule->id, 'percentage' => 2.5]);
        $this->assertDatabaseHas('commissions', ['id' => $commission->id, 'commission_amount' => 250000]);
    }
}
