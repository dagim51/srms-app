<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Dispute;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuditLoggingTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'Administrator'], ['id' => (string) Str::uuid()]);
        $userRole = Role::firstOrCreate(['name' => 'User'], ['id' => (string) Str::uuid()]);

        $this->admin = User::factory()->create([
            'email' => 'admin@srms.et',
            'status' => 'active',
        ]);
        $this->admin->roles()->attach($adminRole->id);

        $this->user = User::factory()->create([
            'email' => 'tenant@srms.et',
            'status' => 'active',
        ]);
        $this->user->roles()->attach($userRole->id);
    }

    public function test_audit_log_helper_records_security_events(): void
    {
        $log = AuditLog::log(
            event: 'fee_tier.updated',
            auditable: null,
            newValues: ['platform_fee' => '5.5%'],
            oldValues: ['platform_fee' => '5.0%'],
            notes: 'Elevated platform brokerage fee',
            actorId: $this->admin->id
        );

        $this->assertDatabaseHas('audit_logs', [
            'id' => $log->id,
            'actor_id' => $this->admin->id,
            'event' => 'fee_tier.updated',
            'notes' => 'Elevated platform brokerage fee',
        ]);

        $this->assertEquals($this->admin->id, $log->actor->id);
        $this->assertEquals('5.5%', $log->new_values['platform_fee']);
    }

    public function test_auditable_trait_automatically_logs_dispute_lifecycle(): void
    {
        $this->actingAs($this->admin);

        // 1. Creation event
        $dispute = Dispute::create([
            'complainant_id' => $this->user->id,
            'respondent_id' => $this->admin->id,
            'disputable_type' => 'App\Models\Listing',
            'disputable_id' => (string) Str::uuid(),
            'type' => 'security_deposit',
            'subject' => 'Escrow release contestation',
            'description' => 'Disputed paint damage fees',
            'amount_in_dispute' => 7500.00,
            'currency' => 'ETB',
            'status' => 'open',
            'priority' => 'high',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => Dispute::class,
            'auditable_id' => $dispute->id,
            'event' => 'created',
        ]);

        // 2. Update event
        $dispute->update([
            'status' => 'resolved',
            'resolution_notes' => 'Mutually agreed on 2,500 ETB compensation',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => Dispute::class,
            'auditable_id' => $dispute->id,
            'event' => 'updated',
        ]);

        $updateLog = AuditLog::where('auditable_id', $dispute->id)
            ->where('event', 'updated')
            ->first();

        $this->assertNotNull($updateLog);
        $this->assertEquals('resolved', $updateLog->new_values['status']);

        // 3. Deletion event
        $dispute->delete();

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => Dispute::class,
            'auditable_id' => $dispute->id,
            'event' => 'deleted',
        ]);
    }

    public function test_admin_audit_page_renders_database_logs(): void
    {
        AuditLog::log(
            event: 'system.backup',
            auditable: null,
            notes: 'Database snapshots rotated to warm cold storage',
            actorId: $this->admin->id
        );

        $response = $this->actingAs($this->admin)->get(route('dashboard.admin.audit'));

        $response->assertStatus(200);
        $response->assertSee('Audit Logs');
        $response->assertSee('Database snapshots rotated');
    }

    public function test_admin_audit_page_filters_by_search_query(): void
    {
        AuditLog::log(
            event: 'payment.flagged',
            auditable: null,
            notes: 'Unusual Telebirr velocity detected',
            actorId: $this->admin->id
        );

        AuditLog::log(
            event: 'user.login',
            auditable: null,
            notes: 'Standard OAuth session opened',
            actorId: $this->user->id
        );

        $response = $this->actingAs($this->admin)->get(route('dashboard.admin.audit', ['search' => 'Telebirr']));

        $response->assertStatus(200);
        $response->assertSee('Unusual Telebirr velocity detected');
        $response->assertDontSee('Standard OAuth session opened');
    }
}
