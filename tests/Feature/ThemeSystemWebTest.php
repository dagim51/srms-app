<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeSystemWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_render_fouc_prevention_script_and_theme_toggle()
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        // FOUC prevention script in head
        $response->assertSee("localStorage.getItem('theme')", false);
        $response->assertSee('prefers-color-scheme: dark', false);
        $response->assertSee("document.documentElement.classList.add('dark')", false);

        // Reusable Theme toggle button
        $response->assertSee('id="dark-mode-toggle"', false);
        $response->assertSee('aria-label="Toggle dark mode"', false);
        $response->assertSee('x-show="darkMode"', false);
        $response->assertSee('x-show="!darkMode"', false);
    }

    public function test_auth_pages_render_fouc_prevention_script_and_theme_toggle()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);

        // FOUC prevention script in head
        $response->assertSee("localStorage.getItem('theme')", false);
        $response->assertSee('prefers-color-scheme: dark', false);
        $response->assertSee("document.documentElement.classList.add('dark')", false);

        // Auth Theme toggle button
        $response->assertSee('id="auth-dark-toggle"', false);
        $response->assertSee('aria-label="Toggle dark mode"', false);
    }

    public function test_dashboard_pages_render_fouc_prevention_script_and_theme_toggle()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);

        // FOUC prevention script in head
        $response->assertSee("localStorage.getItem('theme')", false);
        $response->assertSee('prefers-color-scheme: dark', false);
        $response->assertSee("document.documentElement.classList.add('dark')", false);

        // Dashboard Topbar Theme toggle button
        $response->assertSee('id="topbar-dark-toggle"', false);
        $response->assertSee('aria-label="Toggle dark mode"', false);
    }

    public function test_compiled_css_bundle_contains_dark_mode_selectors()
    {
        $manifestPath = public_path('build/manifest.json');
        $this->assertFileExists($manifestPath, 'Vite manifest must exist. Run npm run build.');

        $manifest = json_decode(file_get_contents($manifestPath), true);
        $cssFile = $manifest['resources/css/app.css']['file'] ?? null;
        $this->assertNotNull($cssFile, 'resources/css/app.css must be registered in manifest.');

        $cssContent = file_get_contents(public_path('build/'.$cssFile));
        $this->assertStringContainsString('.dark', $cssContent, 'Built CSS must contain .dark selectors for dark mode.');
    }
}
