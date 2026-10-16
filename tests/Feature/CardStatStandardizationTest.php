<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class CardStatStandardizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_card_stat_renders_value_label_and_fallback_icon(): void
    {
        $rendered = Blade::render('<x-card-stat label="Active Users" value="1,250" iconColor="blue" />');

        $this->assertStringContainsString('Active Users', $rendered);
        $this->assertStringContainsString('1,250', $rendered);
        $this->assertStringContainsString('<svg', $rendered);
        $this->assertStringContainsString('card-stat', $rendered);
    }

    public function test_card_stat_renders_named_string_icon(): void
    {
        $rendered = Blade::render('<x-card-stat label="Cashflow" value="ETB 50,000" icon="heroicon-o-banknotes" iconColor="emerald" />');

        $this->assertStringContainsString('Cashflow', $rendered);
        $this->assertStringContainsString('ETB 50,000', $rendered);
        $this->assertStringContainsString('<svg', $rendered);
    }

    public function test_card_stat_renders_slot_icon(): void
    {
        $template = <<<'BLADE'
<x-card-stat label="Custom Metric" value="99">
    <x-slot:icon>
        <span class="custom-test-icon">ICON</span>
    </x-slot:icon>
</x-card-stat>
BLADE;
        $rendered = Blade::render($template);

        $this->assertStringContainsString('Custom Metric', $rendered);
        $this->assertStringContainsString('custom-test-icon', $rendered);
    }

    public function test_card_stat_renders_badge_and_caption(): void
    {
        $rendered = Blade::render('<x-card-stat label="System Status" value="Online" badge="Healthy" badgeVariant="success" caption="All services operational" />');

        $this->assertStringContainsString('Healthy', $rendered);
        $this->assertStringContainsString('badge-success', $rendered);
        $this->assertStringContainsString('All services operational', $rendered);
    }

    public function test_homepage_renders_standardized_metric_and_city_cards(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Verified Listings');
        $response->assertSee('Deed Authenticated');
        $response->assertSee('Institutional Safeguards');
    }
}
