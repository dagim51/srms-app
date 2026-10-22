<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AuthorizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class OromoLocalizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AuthorizationSeeder::class);
        $this->user = User::factory()->create();
        $this->user->assignRole('Administrator');
    }

    public function test_locale_switch_endpoint_switches_to_oromo_and_persists(): void
    {
        $response = $this->post('/locale/om');
        $response->assertRedirect();
        $response->assertSessionHas('locale', 'om');
        $response->assertCookie('srms_locale', 'om');
    }

    public function test_locale_switch_via_get_request_switches_to_oromo(): void
    {
        $response = $this->get('/locale/om');
        $response->assertRedirect();
        $response->assertSessionHas('locale', 'om');
    }

    public function test_authenticated_user_locale_is_saved_to_profile_on_switch_to_oromo(): void
    {
        $user = User::factory()->create(['locale' => 'en']);

        $response = $this->actingAs($user)->post('/locale/om');
        $response->assertRedirect();

        $user->refresh();
        $this->assertEquals('om', $user->locale);
    }

    public function test_authenticated_user_saved_oromo_locale_is_applied_automatically(): void
    {
        $user = User::factory()->create(['locale' => 'om']);
        $user->assignRole('Administrator');

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('lang="om"', false);
        $response->assertSee('Daashboordii', false);
    }

    public function test_guest_cookie_preference_for_oromo_is_honored(): void
    {
        $response = $this->withCookie('srms_locale', 'om')->get('/');
        $response->assertStatus(200);
        $response->assertSee('lang="om"', false);
    }

    public function test_pages_render_in_oromo_when_locale_session_is_active(): void
    {
        // 1. Public home page in Afaan Oromoo
        $publicRes = $this->withSession(['locale' => 'om'])->get('/');
        $publicRes->assertStatus(200);
        $publicRes->assertSee('lang="om"', false);
        $publicRes->assertSee('Afaan Oromoo', false);

        // 2. Auth Login page in Afaan Oromoo
        $authRes = $this->withSession(['locale' => 'om'])->get('/login');
        $authRes->assertStatus(200);
        $authRes->assertSee('lang="om"', false);
        $authRes->assertSee('Seeni', false);

        // 3. Dashboard in Afaan Oromoo
        $dashRes = $this->actingAs($this->user)->withSession(['locale' => 'om'])->get('/dashboard');
        $dashRes->assertStatus(200);
        $dashRes->assertSee('lang="om"', false);
        $dashRes->assertSee('Daashboordii', false);
    }

    public function test_form_validation_messages_are_localized_in_oromo(): void
    {
        $response = $this->withSession(['locale' => 'om'])
            ->from('/login')
            ->post('/login', [
                'email' => '',
                'password' => '',
            ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['email', 'password']);

        $errors = session('errors');
        $this->assertNotNull($errors);
        $this->assertStringContainsString('barbaachisaa dha', $errors->first('email'));
        $this->assertStringContainsString('Imeeyilii', $errors->first('email'));
    }

    public function test_price_component_formats_currency_for_oromo(): void
    {
        App::setLocale('om');
        $renderedOm = Blade::render('<x-price :amount="50000" />');
        $this->assertStringContainsString('Qarshii', $renderedOm);
        $this->assertStringContainsString('50,000', $renderedOm);

        // Reset back to English default
        App::setLocale('en');
    }

    public function test_marketplace_and_locations_pages_render_in_oromo(): void
    {
        // Marketplace in Afaan Oromoo
        $marketRes = $this->withSession(['locale' => 'om'])->get('/properties');
        $marketRes->assertStatus(200);
        $marketRes->assertSee('lang="om"', false);

        // Locations Management in Afaan Oromoo
        $locRes = $this->actingAs($this->user)->withSession(['locale' => 'om'])->get('/dashboard/locations');
        $locRes->assertStatus(200);
        $locRes->assertSee('lang="om"', false);
        $locRes->assertSee('Kutaalee Magaalaa', false);
        $locRes->assertSee('Zoonota Naannoo', false);
    }
}
