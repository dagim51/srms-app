<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Database\Seeders\AuthorizationSeeder;
use Database\Seeders\LocationSeeder;
use Database\Seeders\AmenityAndFeatureSeeder;
use Database\Seeders\PublicDiscoverySeeder;
use Database\Seeders\ModerationUserSeeder;
use Database\Seeders\RentalSeeder;
use Database\Seeders\DeveloperSalesSeeder;
use Database\Seeders\OperationsSeeder;
use Database\Seeders\FinancialLedgerSeeder;
use Database\Seeders\TrustSafetySeeder;

class OperationalWebControllersTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AuthorizationSeeder::class);
        $this->seed(LocationSeeder::class);
        $this->seed(AmenityAndFeatureSeeder::class);
        $this->seed(PublicDiscoverySeeder::class);
        $this->seed(ModerationUserSeeder::class);
        $this->seed(RentalSeeder::class);
        $this->seed(DeveloperSalesSeeder::class);
        $this->seed(OperationsSeeder::class);
        $this->seed(FinancialLedgerSeeder::class);
        $this->seed(TrustSafetySeeder::class);

        $this->user = User::first();
    }

    public function test_rental_dashboard_and_subpages_render_successfully(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard.rental.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)->get(route('dashboard.rental.applications'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)->get(route('dashboard.rental.tenants'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)->get(route('dashboard.rental.leases'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)->get(route('dashboard.rental.payments'));
        $response->assertStatus(200);
    }

    public function test_developer_sales_pages_render_successfully(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard.sales.units'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)->get(route('dashboard.sales.buyers'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)->get(route('dashboard.sales.payment_plans'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)->get(route('dashboard.sales.contracts'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)->get(route('dashboard.sales.handovers'));
        $response->assertStatus(200);
    }

    public function test_operations_pages_render_successfully(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard.operations.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)->get(route('dashboard.operations.tickets'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)->get(route('dashboard.operations.cleaning'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)->get(route('dashboard.operations.staff'));
        $response->assertStatus(200);
    }

    public function test_finance_pages_render_successfully(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard.finance.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)->get(route('dashboard.finance.transactions'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)->get(route('dashboard.finance.invoices'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)->get(route('dashboard.finance.ledger'));
        $response->assertStatus(200);
    }

    public function test_trust_and_safety_pages_render_successfully(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard.trust.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)->get(route('dashboard.trust.reports'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)->get(route('dashboard.trust.disputes'));
        $response->assertStatus(200);
    }
}
