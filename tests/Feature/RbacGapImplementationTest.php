<?php

namespace Tests\Feature;

use App\Domains\Administration\Models\AuditLog;
use App\Domains\Engagement\Models\Offer;
use App\Domains\Identity\Models\User;
use App\Domains\Organization\Models\Branch;
use App\Domains\Organization\Models\Organization;
use App\Domains\Organization\Models\Team;
use App\Domains\Property\Models\Property;
use App\Domains\Transaction\Models\Document;
use App\Models\Listing;
use App\Models\PropertyType;
use Database\Seeders\AuthorizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Tests\TestCase;

class RbacGapImplementationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AuthorizationSeeder::class);
    }

    /**
     * Gap 1 Test: Audit Log Entity Diffing (SRS Section 142)
     */
    public function test_audit_log_captures_property_price_diffing(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);

        $propType = PropertyType::firstOrCreate(['code' => 'TEST-VILLA-GAP'], [
            'id' => (string) Str::uuid(),
            'name_en' => 'Villa',
        ]);

        $property = Property::create([
            'id' => (string) Str::uuid(),
            'property_code' => 'PROP-GAP-001',
            'type_id' => $propType->id,
            'owner_user_id' => $owner->id,
            'title' => 'Bole Medhanealem Penthouse',
            'price' => 20000000.00,
            'currency' => 'ETB',
            'status' => 'draft',
        ]);

        // Modify price (e.g. 20,000,000 -> 18,500,000 as in SRS Section 142 example)
        $property->update([
            'price' => 18500000.00,
        ]);

        $auditLog = AuditLog::where('event', 'price_changed')
            ->where('auditable_id', $property->id)
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertEquals(20000000, $auditLog->old_values['price']);
        $this->assertEquals(18500000, $auditLog->new_values['price']);
    }

    public function test_audit_log_captures_role_and_permission_mutations(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        $targetUser = User::factory()->create();

        // 1. Assign role
        $targetUser->assignRole('Agent');

        $roleLog = AuditLog::where('event', 'role_assigned')
            ->where('auditable_id', $targetUser->id)
            ->first();

        $this->assertNotNull($roleLog);
        $this->assertEquals('Agent', $roleLog->new_values['assigned_role']);
        $this->assertContains('Agent', $roleLog->new_values['roles']);

        // 2. Grant direct permission
        $targetUser->givePermissionTo('properties.create');

        $permLog = AuditLog::where('event', 'permission_granted')
            ->where('auditable_id', $targetUser->id)
            ->first();

        $this->assertNotNull($permLog);
        $this->assertEquals('properties.create', $permLog->new_values['granted_permission']);
        $this->assertContains('properties.create', $permLog->new_values['permissions']);
    }

    /**
     * Gap 2 Test: Transaction Participants Detection in DocumentPolicy (SRS Section 28 & 144)
     */
    public function test_document_policy_transaction_participants_access(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $unrelatedUser = User::factory()->create();
        $financeOfficer = User::factory()->create();
        $financeOfficer->assignRole('Finance Officer');

        $propType = PropertyType::firstOrCreate(['code' => 'TEST-COMM-GAP'], [
            'id' => (string) Str::uuid(),
            'name_en' => 'Commercial',
        ]);

        $property = Property::create([
            'id' => (string) Str::uuid(),
            'property_code' => 'PROP-COMM-GAP-01',
            'type_id' => $propType->id,
            'owner_user_id' => $seller->id,
            'title' => 'Kazanchis Commercial Suite',
            'price' => 12000000,
            'currency' => 'ETB',
            'status' => 'published',
        ]);

        $listing = Listing::create([
            'id' => (string) Str::uuid(),
            'property_id' => $property->id,
            'owner_actor_id' => $seller->id,
            'purpose' => 'For Sale',
            'price' => 12000000,
            'status' => 'published',
        ]);

        // Create transaction participant document
        $document = Document::create([
            'id' => (string) Str::uuid(),
            'user_id' => $seller->id,
            'name' => 'Title Deed Scan',
            'file_path' => 'documents/deed.pdf',
            'access_level' => 'transaction_participants',
            'documentable_type' => Property::class,
            'documentable_id' => $property->id,
        ]);

        // Unrelated user cannot view
        $this->assertFalse(Gate::forUser($unrelatedUser)->allows('view', $document));

        // Finance officer can view as overseer
        $this->assertTrue(Gate::forUser($financeOfficer)->allows('view', $document));

        // Create active offer by buyer
        $offer = Offer::create([
            'id' => (string) Str::uuid(),
            'listing_id' => $listing->id,
            'buyer_id' => $buyer->id,
            'amount' => 11500000,
            'status' => 'pending',
        ]);

        // Now buyer IS a transaction participant on this property!
        $this->assertTrue(Gate::forUser($buyer)->allows('view', $document));
    }

    /**
     * Gap 3 Test: Organization Role Inheritance (SRS Section 197: Company -> Branch -> Team)
     */
    public function test_organization_role_hierarchical_inheritance(): void
    {
        $org = Organization::create([
            'id' => (string) Str::uuid(),
            'name' => 'Abyssinia Property Holdings',
            'slug' => 'abyssinia-holdings',
        ]);

        $branch = Branch::create([
            'id' => (string) Str::uuid(),
            'organization_id' => $org->id,
            'name' => 'Bole Subcity Branch',
            'status' => 'active',
        ]);

        $team = Team::create([
            'id' => (string) Str::uuid(),
            'branch_id' => $branch->id,
            'name' => 'Luxury Sales Team Alpha',
            'status' => 'active',
        ]);

        $companyManager = User::factory()->create();
        $branchAgent = User::factory()->create();

        // Assign Company Manager at the root Organization level
        $companyManager->assignRole('Property Manager', $org->id);

        // 1. Inherited access: Company Manager inherits role on Branch and Team
        $this->assertTrue($companyManager->hasRole('Property Manager', $org->id));
        $this->assertTrue($companyManager->hasRole('Property Manager', $branch->id));
        $this->assertTrue($companyManager->hasRole('Property Manager', $team->id));

        // 2. Assign Branch Agent at Branch level
        $branchAgent->assignRole('Agent', $branch->id);

        // Branch Agent cascades down to Team, but does NOT leak upward to Organization
        $this->assertTrue($branchAgent->hasRole('Agent', $branch->id));
        $this->assertTrue($branchAgent->hasRole('Agent', $team->id));
        $this->assertFalse($branchAgent->hasExactRole('Agent', $org->id));
    }
}
