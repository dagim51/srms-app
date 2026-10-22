<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FloatingUiEffectWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_compiled_css_contains_floating_tokens_and_hover_motion_rules()
    {
        $cssPath = public_path('build/assets');
        $this->assertDirectoryExists($cssPath);

        $cssFiles = glob($cssPath.'/app-*.css');
        $this->assertNotEmpty($cssFiles, 'Compiled app-*.css bundle should exist in public/build/assets');

        $cssContent = file_get_contents($cssFiles[0]);

        // Floating Motion Tokens / Easings
        $this->assertStringContainsString('cubic-bezier(.23,1,.32,1)', str_replace(' ', '', $cssContent), 'CSS should contain custom ease-float curve');

        // Button motion rules
        $this->assertStringContainsString('translateY(-2px)', $cssContent, 'Buttons should elevate by -2px on hover');
        $this->assertStringContainsString('scale(.98)', str_replace(' ', '', $cssContent), 'Buttons should have tactile active feedback scale');

        // Card motion rules
        $this->assertStringContainsString('translateY(-4px)', $cssContent, 'Cards should float by -4px on hover');

        // Accessibility & Touch Protection
        $this->assertStringContainsString('prefers-reduced-motion:reduce', str_replace(' ', '', $cssContent), 'Reduced motion media query should be present');
        $this->assertStringContainsString('hover:hover', str_replace(' ', '', $cssContent), 'Hover media query should be present to protect touch devices');
    }

    public function test_public_pages_render_cards_and_buttons_with_floating_classes()
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        // Cards and buttons on landing page
        $response->assertSee('card', false);
        $response->assertSee('btn', false);
        $response->assertSee('btn-primary', false);

        // Assert Short Stay is completely removed from landing page
        $response->assertDontSee('Curated Short Stays');
        $response->assertDontSee('id="nav-short-stay"', false);
        $response->assertDontSee('id="mobile-nav-short-stay"', false);
    }

    public function test_auth_pages_render_card_and_floating_button_classes()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);

        $response->assertSee('card', false);
        $response->assertSee('btn', false);
        $response->assertSee('btn-primary', false);
    }

    public function test_dashboard_renders_cards_and_floating_button_classes()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);

        $response->assertSee('card', false);
        $response->assertSee('btn', false);
        $response->assertSee('btn-primary', false);
    }

    public function test_compiled_css_contains_shadow_tokens()
    {
        $cssPath = public_path('build/assets');
        $cssFiles = glob($cssPath.'/app-*.css');
        $this->assertNotEmpty($cssFiles, 'Compiled app-*.css bundle should exist in public/build/assets');

        $cssContent = file_get_contents($cssFiles[0]);

        // Assert shadow tokens are present in compiled CSS (these are the source-of-truth identifiers)
        // Base Card shadow token
        $this->assertStringContainsString('var(--shadow-card)', $cssContent, 'CSS should contain --shadow-card token for base card elevation');

        // Card Hover shadow token
        $this->assertStringContainsString('var(--shadow-card-hover)', $cssContent, 'CSS should contain --shadow-card-hover token for card hover elevation');

        // Card Interactive shadow token
        $this->assertStringContainsString('var(--shadow-card-interactive)', $cssContent, 'CSS should contain --shadow-card-interactive token for interactive card elevation');

        // Modal shadow token
        $this->assertStringContainsString('var(--shadow-modal)', $cssContent, 'CSS should contain --shadow-modal token for modal elevation');

        // Dark mode card shadow token
        $this->assertStringContainsString('var(--shadow-card-dark)', $cssContent, 'CSS should contain --shadow-card-dark token for dark mode card elevation');
    }

    public function test_shadow_tokens_defined_in_theme_source()
    {
        $cssSourcePath = base_path('resources/css/app.css');
        $this->assertFileExists($cssSourcePath, 'Theme source file should exist');

        $sourceContent = file_get_contents($cssSourcePath);

        // Assert shadow tokens are defined in the @theme block (source of truth)
        $this->assertStringContainsString('--shadow-card:', $sourceContent, 'Theme should define --shadow-card token');
        $this->assertStringContainsString('--shadow-card-hover:', $sourceContent, 'Theme should define --shadow-card-hover token');
        $this->assertStringContainsString('--shadow-card-interactive:', $sourceContent, 'Theme should define --shadow-card-interactive token');
        $this->assertStringContainsString('--shadow-modal:', $sourceContent, 'Theme should define --shadow-modal token');
        $this->assertStringContainsString('--shadow-card-dark:', $sourceContent, 'Theme should define --shadow-card-dark token');
    }

    public function test_no_arbitrary_px_in_blade_views()
    {
        // Safety net: scan Blade views for arbitrary px utilities in elevation properties
        // The "no arbitrary px" anti-AI-design rule targets shadow, blur, and elevation tokens at source
        $viewPaths = glob(base_path('resources/views/**/*.blade.php'));
        $this->assertNotEmpty($viewPaths);

        // Pattern matches arbitrary px values for elevation-related utilities only
        // e.g., shadow-[27px], blur-[10px], drop-shadow-[5px], ring-[3px]
        // but allows legitimate arbitrary values like text-[13px], w-[100px], h-[200px]
        $arbitraryElevationPxPattern = '/(shadow|drop-shadow|ring|outline|inset)\[.*?\d+px.*?\]/i';
        $violations = [];

        foreach ($viewPaths as $viewPath) {
            $content = file_get_contents($viewPath);
            if (preg_match($arbitraryElevationPxPattern, $content)) {
                $relativePath = str_replace(base_path().'/', '', $viewPath);
                $violations[] = $relativePath;
            }
        }

        $this->assertEmpty($violations, 'Blade views should not contain arbitrary px utilities for elevation properties (shadow, blur, drop-shadow, ring). Violations in: '.implode(', ', $violations));
    }
}
