<?php

namespace Tests\Feature;

use App\Models\DevelopmentProject;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\LedgerAccount;
use App\Models\Listing;
use App\Models\Offer;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Role;
use App\Models\TenantProfile;
use App\Models\User;
use App\Models\Viewing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiResourceTransformersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'client', 'guard_name' => 'web']);
    }

    public function test_development_project_endpoints_return_resource_format()
    {
        $user = User::factory()->create();
        $org = Organization::create([
            'name' => 'Emaar Addis',
            'slug' => 'emaar-addis-' . uniqid(),
            'legal_name' => 'Emaar Addis Real Estate SC',
            'tax_id' => 'TID-' . uniqid(),
            'status' => 'active',
        ]);
        OrganizationMember::create([
            'organization_id' => $org->id,
            'user_id' => $user->id,
            'role' => 'admin',
        ]);

        $this->actingAs($user);

        $response = $this->postJson('/api/v1/development/projects', [
            'organization_id' => $org->id,
            'name' => 'Bole Horizon Towers',
            'description' => '30-story luxury development',
            'status' => 'planning',
            'handover_date' => '2028-12-31',
            'progress_percentage' => 15,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'organization_id',
                    'name',
                    'description',
                    'status',
                    'handover_date',
                    'progress_percentage',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertEquals('Bole Horizon Towers', $response->json('data.name'));
        $this->assertEquals(15, $response->json('data.progress_percentage'));

        // Test GET index
        $indexResponse = $this->getJson('/api/v1/development/projects');
        $indexResponse->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'status', 'progress_percentage'],
                ],
            ]);
    }

    public function test_rental_endpoints_return_resource_format()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/api/v1/rental/profile', [
            'employment_status' => 'Full-time Engineer',
            'annual_income' => 600000.00,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'employment_status',
                    'annual_income',
                    'background_check_status',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertEquals('Full-time Engineer', $response->json('data.employment_status'));
        $this->assertEquals(600000.00, $response->json('data.annual_income'));
    }

    public function test_financials_endpoints_return_resource_format()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Store account
        $accResponse = $this->postJson('/api/v1/financials/accounts', [
            'code' => '1010-CASH',
            'name' => 'Primary Operating Cash',
            'type' => 'Asset',
        ]);

        $accResponse->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'code', 'name', 'type', 'created_at'],
            ]);

        // Store invoice
        $targetUser = User::factory()->create();
        $invResponse = $this->postJson('/api/v1/financials/invoices', [
            'user_id' => $targetUser->id,
            'amount' => 15000.00,
            'due_date' => '2026-11-01',
        ]);

        $invResponse->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'user_id', 'amount', 'due_date', 'status', 'created_at'],
            ]);
        $this->assertEquals(15000.00, $invResponse->json('data.amount'));
    }

    public function test_offers_endpoints_return_resource_format()
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();

        $propertyType = PropertyType::firstOrCreate(['code' => 'PENT'], ['name_en' => 'Penthouse']);
        $property = Property::create([
            'title' => 'Sky Penthouse',
            'property_code' => 'SKY-' . uniqid(),
            'type_id' => $propertyType->id,
            'status' => 'active',
        ]);
        $listing = Listing::create([
            'property_id' => $property->id,
            'owner_actor_id' => $seller->id,
            'purpose' => 'sale',
            'price' => 12000000.00,
            'status' => 'published',
        ]);

        $this->actingAs($buyer);

        $response = $this->postJson('/api/v1/offers', [
            'listing_id' => $listing->id,
            'amount' => 11500000.00,
            'notes' => 'Cash buyer ready to close within 14 days.',
            'expires_at' => now()->addDays(5)->toDateTimeString(),
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'listing_id',
                    'buyer_id',
                    'amount',
                    'status',
                    'expires_at',
                    'notes',
                    'created_at',
                ],
            ]);

        $this->assertEquals(11500000.00, $response->json('data.amount'));
        $this->assertEquals('pending', $response->json('data.status'));
        $this->assertNotNull($response->json('data.expires_at'));
    }

    public function test_viewings_endpoints_return_resource_format()
    {
        $seller = User::factory()->create();
        $requester = User::factory()->create();

        $propertyType = PropertyType::firstOrCreate(['code' => 'TOWN'], ['name_en' => 'Townhouse']);
        $property = Property::create([
            'title' => 'Modern Townhouse',
            'property_code' => 'TWN-' . uniqid(),
            'type_id' => $propertyType->id,
            'status' => 'active',
        ]);
        $listing = Listing::create([
            'property_id' => $property->id,
            'owner_actor_id' => $seller->id,
            'purpose' => 'sale',
            'price' => 8500000.00,
            'status' => 'published',
        ]);

        $this->actingAs($requester);

        $response = $this->postJson('/api/v1/viewings', [
            'listing_id' => $listing->id,
            'scheduled_at' => now()->addDays(2)->setHour(14)->setMinute(0)->toDateTimeString(),
            'notes' => 'Looking forward to weekend tour.',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'listing_id',
                    'requester_id',
                    'agent_id',
                    'status',
                    'scheduled_at',
                    'notes',
                ],
            ]);

        $this->assertEquals('requested', $response->json('data.status'));
    }
}
