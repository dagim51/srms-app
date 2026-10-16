<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class LocalizationWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_english_is_default_language()
    {
        $this->assertEquals('en', config('app.locale'));

        $response = $this->get('/');
        $response->assertStatus(200);

        // Language tag in html root
        $response->assertSee('lang="en"', false);

        // Default navigation in English
        $response->assertSee('Sign In', false);
        $response->assertSee('Properties', false);
    }

    public function test_language_switcher_is_rendered_across_public_auth_and_dashboard_layouts()
    {
        // 1. Public home page
        $publicRes = $this->get('/');
        $publicRes->assertStatus(200);
        $publicRes->assertSee('English', false);
        $publicRes->assertSee('አማርኛ', false);
        $publicRes->assertSee('id="nav-language-switcher"', false);

        // 2. Auth login page
        $authRes = $this->get('/login');
        $authRes->assertStatus(200);
        $authRes->assertSee('English', false);
        $authRes->assertSee('አማርኛ', false);
        $authRes->assertSee('id="auth-language-switcher"', false);

        // 3. Authenticated dashboard page
        $user = User::factory()->create();
        $dashRes = $this->actingAs($user)->get('/dashboard');
        $dashRes->assertStatus(200);
        $dashRes->assertSee('English', false);
        $dashRes->assertSee('አማርኛ', false);
        $dashRes->assertSee('id="topbar-language-switcher"', false);
    }

    public function test_locale_switch_endpoint_switches_to_amharic_and_persists()
    {
        $response = $this->post('/locale/am');
        $response->assertRedirect();
        $response->assertSessionHas('locale', 'am');
        $response->assertCookie('srms_locale', 'am');
    }

    public function test_locale_switch_via_get_request_also_works()
    {
        $response = $this->get('/locale/am');
        $response->assertRedirect();
        $response->assertSessionHas('locale', 'am');
    }

    public function test_invalid_locale_safely_falls_back_to_english()
    {
        $response = $this->post('/locale/invalid_locale');
        $response->assertRedirect();
        $response->assertSessionHas('locale', 'en');
    }

    public function test_authenticated_user_locale_is_saved_to_profile_on_switch()
    {
        $user = User::factory()->create(['locale' => 'en']);

        $response = $this->actingAs($user)->post('/locale/am');
        $response->assertRedirect();

        $user->refresh();
        $this->assertEquals('am', $user->locale);
    }

    public function test_pages_render_in_amharic_when_locale_session_is_active()
    {
        // Public home page in Amharic
        $publicRes = $this->withSession(['locale' => 'am'])->get('/');
        $publicRes->assertStatus(200);
        $publicRes->assertSee('lang="am"', false);
        $publicRes->assertSee('Noto+Sans+Ethiopic', false);
        $publicRes->assertSee('ይግቡ', false); // Sign In
        $publicRes->assertSee('ንብረቶች', false); // Properties

        // Auth Login page in Amharic
        $authRes = $this->withSession(['locale' => 'am'])->get('/login');
        $authRes->assertStatus(200);
        $authRes->assertSee('lang="am"', false);
        $authRes->assertSee('ይግቡ', false);
        $authRes->assertSee('የኢሜይል አድራሻ', false);
        $authRes->assertSee('የይለፍ ቃል', false);

        // Dashboard in Amharic
        $user = User::factory()->create();
        $dashRes = $this->actingAs($user)->withSession(['locale' => 'am'])->get('/dashboard');
        $dashRes->assertStatus(200);
        $dashRes->assertSee('lang="am"', false);
        $dashRes->assertSee('ዳሽቦርድ', false);
        $dashRes->assertSee('ንብረቶች', false);
        $dashRes->assertSee('ካርታ', false);
    }

    public function test_guest_cookie_preference_is_honored()
    {
        $response = $this->withCookie('srms_locale', 'am')->get('/');
        $response->assertStatus(200);
        $response->assertSee('lang="am"', false);
        $response->assertSee('ይግቡ', false);
    }

    public function test_authenticated_user_saved_locale_is_applied_automatically()
    {
        $user = User::factory()->create(['locale' => 'am']);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('lang="am"', false);
        $response->assertSee('ዳሽቦርድ', false);
    }

    public function test_form_validation_messages_are_localized_in_amharic()
    {
        $response = $this->withSession(['locale' => 'am'])
            ->from('/login')
            ->post('/login', [
                'email' => '',
                'password' => '',
            ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['email', 'password']);

        $errors = session('errors');
        $this->assertNotNull($errors);
        $this->assertStringContainsString('ግዴታ ነው።', $errors->first('email'));
        $this->assertStringContainsString('ኢሜይል', $errors->first('email'));
    }

    public function test_price_component_formats_currency_for_english_and_amharic()
    {
        // English locale -> ETB
        App::setLocale('en');
        $renderedEn = Blade::render('<x-price :amount="50000" />');
        $this->assertStringContainsString('ETB', $renderedEn);
        $this->assertStringContainsString('50,000', $renderedEn);

        // Amharic locale -> ብር
        App::setLocale('am');
        $renderedAm = Blade::render('<x-price :amount="50000" />');
        $this->assertStringContainsString('ብር', $renderedAm);
        $this->assertStringContainsString('50,000', $renderedAm);

        // Reset back to English default
        App::setLocale('en');
    }

    public function test_marketplace_and_analytics_pages_render_in_amharic()
    {
        // Marketplace in Amharic
        $marketRes = $this->withSession(['locale' => 'am'])->get('/properties');
        $marketRes->assertStatus(200);
        $marketRes->assertSee('lang="am"', false);
        $marketRes->assertSee('የኢትዮጵያ ንብረት ፍለጋ', false);
        $marketRes->assertSee('ሁሉም ዝርዝሮች', false);

        // Analytics in Amharic
        $user = User::factory()->create();
        $analyticsRes = $this->actingAs($user)->withSession(['locale' => 'am'])->get('/dashboard/analytics');
        $analyticsRes->assertStatus(200);
        $analyticsRes->assertSee('lang="am"', false);
        $analyticsRes->assertSee('የገበያ መረጃና ትንተና', false);
        $analyticsRes->assertSee('ሪፖርት እና ትንታኔ', false);
    }
}
