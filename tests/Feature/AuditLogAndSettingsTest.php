<?php

namespace Tests\Feature;

use App\Domains\Administration\Models\AuditLog;
use App\Domains\Administration\Models\FeatureFlag;
use App\Domains\Administration\Models\SystemSetting;
use App\Domains\Identity\Models\Role;
use App\Http\Middleware\CheckFeatureFlag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class AuditLogAndSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_settings_stores_and_casts_types_correctly(): void
    {
        $admin = User::factory()->create();

        // 1. String setting
        SystemSetting::set('platform_name', 'SRMS Real Estate Platform', 'string', 'Platform title', $admin->id);
        $this->assertEquals('SRMS Real Estate Platform', SystemSetting::get('platform_name'));

        // 2. Boolean setting
        SystemSetting::set('maintenance_mode', false, 'boolean', 'Maintenance flag', $admin->id);
        $this->assertFalse(SystemSetting::get('maintenance_mode'));

        SystemSetting::set('enable_telebirr', true, 'boolean', 'Telebirr active', $admin->id);
        $this->assertTrue(SystemSetting::get('enable_telebirr'));

        // 3. Integer setting
        SystemSetting::set('max_upload_size_mb', 25, 'integer', 'Upload limit', $admin->id);
        $this->assertSame(25, SystemSetting::get('max_upload_size_mb'));

        // 4. JSON setting
        $allowedCurrencies = ['ETB', 'USD', 'EUR'];
        SystemSetting::set('supported_currencies', $allowedCurrencies, 'json', 'Currencies', $admin->id);
        $this->assertEquals($allowedCurrencies, SystemSetting::get('supported_currencies'));

        // 5. Default fallback
        $this->assertEquals('fallback_val', SystemSetting::get('non_existent_key', 'fallback_val'));
    }

    public function test_feature_flags_evaluates_role_based_permissions(): void
    {
        // 1. Globally disabled flag
        FeatureFlag::create([
            'name' => 'beta_ai_assistant',
            'is_enabled' => false,
            'description' => 'AI agent for properties',
        ]);
        $this->assertFalse(FeatureFlag::isEnabled('beta_ai_assistant', 'admin'));
        $this->assertFalse(FeatureFlag::isEnabled('beta_ai_assistant', 'agent'));

        // 2. Globally enabled flag
        FeatureFlag::create([
            'name' => 'direct_chat',
            'is_enabled' => true,
            'allowed_roles' => [],
            'description' => 'Real-time messaging',
        ]);
        $this->assertTrue(FeatureFlag::isEnabled('direct_chat', 'buyer'));
        $this->assertTrue(FeatureFlag::isEnabled('direct_chat', 'host'));

        // 3. Role-restricted flag
        FeatureFlag::create([
            'name' => 'developer_analytics_portal',
            'is_enabled' => true,
            'allowed_roles' => ['developer', 'admin'],
            'description' => 'Advanced sales projection chart',
        ]);
        $this->assertTrue(FeatureFlag::isEnabled('developer_analytics_portal', 'developer'));
        $this->assertTrue(FeatureFlag::isEnabled('developer_analytics_portal', 'admin'));
        $this->assertFalse(FeatureFlag::isEnabled('developer_analytics_portal', 'buyer'));
    }

    public function test_audit_log_records_events_properly(): void
    {
        $actor = User::factory()->create();

        $log = AuditLog::log(
            event: 'security_override',
            auditable: $actor,
            newValues: ['role' => 'admin'],
            oldValues: ['role' => 'user'],
            notes: 'Elevated privileges for support escalation',
            actorId: $actor->id
        );

        $this->assertInstanceOf(AuditLog::class, $log);
        $this->assertEquals('security_override', $log->event);
        $this->assertEquals($actor->id, $log->actor_id);

        $this->assertDatabaseHas('audit_logs', [
            'id' => $log->id,
            'event' => 'security_override',
            'actor_id' => $actor->id,
        ]);
    }

    public function test_auditable_trait_automatically_logs_model_lifecycle(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        $setting = SystemSetting::set('audit_test_key', 'initial_val', 'string', 'Test setting', $admin->id);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_id' => $setting->id,
            'event' => 'created',
            'actor_id' => $admin->id,
        ]);

        $setting->update(['value' => 'updated_val']);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_id' => $setting->id,
            'event' => 'updated',
        ]);
    }

    public function test_feature_flag_middleware_blocks_access_when_disabled(): void
    {
        $user = User::factory()->create();

        FeatureFlag::create([
            'name' => 'secret_experimental_tool',
            'is_enabled' => false,
            'description' => 'Experimental tool',
        ]);

        $request = Request::create('/api/v1/experimental', 'GET');
        $request->setUserResolver(fn () => $user);

        $middleware = new CheckFeatureFlag;
        $response = $middleware->handle($request, function () {
            return response()->json(['success' => true]);
        }, 'secret_experimental_tool');

        $this->assertEquals(403, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('FEATURE_DISABLED', $data['error']);

        // Now enable flag
        FeatureFlag::where('name', 'secret_experimental_tool')->update(['is_enabled' => true]);

        $responseAllowed = $middleware->handle($request, function () {
            return response()->json(['success' => true]);
        }, 'secret_experimental_tool');

        $this->assertEquals(200, $responseAllowed->getStatusCode());
    }

    public function test_admin_web_audit_and_settings_endpoints(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Administrator']);
        $admin = User::factory()->create();
        $admin->assignRole('Administrator');

        // Create some audit logs
        AuditLog::log('system_startup', null, ['version' => '1.0'], [], 'System initiated', $admin->id);

        $responseAudit = $this->actingAs($admin)->get('/dashboard/admin/audit');
        $responseAudit->assertStatus(200)
            ->assertSee('Audit Logs')
            ->assertSee('System_startup');

        $responseSettings = $this->actingAs($admin)->get('/dashboard/admin/settings');
        $responseSettings->assertStatus(200)
            ->assertSee('Global System Settings');

        $responseUpdate = $this->actingAs($admin)->post('/dashboard/admin/settings', [
            'settings' => [
                'platform_fee_percent' => '7.5',
                'payout_frequency' => 'weekly',
            ],
        ]);
        $responseUpdate->assertRedirect()
            ->assertSessionHas('success');

        $this->assertEquals('7.5', SystemSetting::get('platform_fee_percent'));
        $this->assertEquals('weekly', SystemSetting::get('payout_frequency'));
    }
}
