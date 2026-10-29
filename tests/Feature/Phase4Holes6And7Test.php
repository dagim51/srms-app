<?php

namespace Tests\Feature;

use App\Support\EthiopianCalendar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class Phase4Holes6And7Test extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Hole 6: Ethiopian Calendar conversion algorithms and bidirectional date mapping.
     */
    public function test_ethiopian_calendar_support_and_conversion(): void
    {
        // 1. Gregorian -> Ethiopian conversion
        // Oct 27, 2024 -> Tikimt 17, 2017 E.C.
        $eth = EthiopianCalendar::fromGregorian(2024, 10, 27);
        $this->assertEquals(2017, $eth['year']);
        $this->assertEquals(2, $eth['month']); // Tikimt
        $this->assertEquals(17, $eth['day']);

        // 2. Ethiopian -> Gregorian round-trip
        $greg = EthiopianCalendar::toGregorian(2017, 2, 17);
        $this->assertEquals(2024, $greg['year']);
        $this->assertEquals(10, $greg['month']);
        $this->assertEquals(27, $greg['day']);

        // 3. Formatting with Dual Display
        $formattedAmDual = EthiopianCalendar::format('2024-10-27', 'am', true);
        $this->assertStringContainsString('ጥቅምት 17፣ 2017 ዓ.ም.', $formattedAmDual);
        $this->assertStringContainsString('Oct 27, 2024 እ.ኤ.አ.', $formattedAmDual);

        $formattedEnDual = EthiopianCalendar::format('2024-10-27', 'en', true);
        $this->assertStringContainsString('Oct 27, 2024', $formattedEnDual);
        $this->assertStringContainsString('Tikimt 17, 2017 E.C.', $formattedEnDual);
    }

    /**
     * Test Hole 6: ethiopian-datepicker component renders hidden input, toggle button, and alpine initialization.
     */
    public function test_ethiopian_datepicker_blade_component_renders(): void
    {
        $rendered = Blade::render(
            '<x-ethiopian-datepicker name="handover_date" value="2026-11-01" label="Official Handover Date" required />'
        );

        $this->assertStringContainsString('name="handover_date"', $rendered);
        $this->assertStringContainsString('type="hidden"', $rendered);
        $this->assertStringContainsString('ethiopianDatePicker', $rendered);
        $this->assertStringContainsString('Official Handover Date', $rendered);
        $this->assertStringContainsString('ዓ.ም (Eth)', $rendered);
        $this->assertStringContainsString('2026-11-01', $rendered);
    }

    /**
     * Test Hole 7: mortgage-yield-calculator component renders financial tabs, CBE mortgage presets, and yield metrics.
     */
    public function test_mortgage_yield_calculator_blade_component_renders(): void
    {
        $rendered = Blade::render(
            '<x-mortgage-yield-calculator :price="7500000" :estimatedRent="55000" currency="ETB" />'
        );

        $this->assertStringContainsString('mortgageYieldCalculator', $rendered);
        $this->assertStringContainsString('Ethiopian Real Estate Financial Estimator', $rendered);
        $this->assertStringContainsString('Mortgage Loan', $rendered);
        $this->assertStringContainsString('Rental Yield & ROI', $rendered);
        $this->assertStringContainsString('7500000', $rendered);
        $this->assertStringContainsString('Down Payment', $rendered);
        $this->assertStringContainsString('Gross Rental Yield', $rendered);
        $this->assertStringContainsString('Net Cap Rate', $rendered);
        $this->assertStringContainsString('CBE', $rendered);
    }

    /**
     * Test Hole 7: listing-quality-assistant component renders live gauge and checklist items including cadastral parcel.
     */
    public function test_listing_quality_assistant_blade_component_renders(): void
    {
        $rendered = Blade::render(
            '<x-listing-quality-assistant initialTitle="Bole Penthouse" :initialPrice="12000000" :initialPhotosCount="6" :hasCadastral="true" />'
        );

        $this->assertStringContainsString('listingQualityAssistant', $rendered);
        $this->assertStringContainsString('Live Listing Quality Score', $rendered);
        $this->assertStringContainsString('Informative Title', $rendered);
        $this->assertStringContainsString('HD Photos', $rendered);
        $this->assertStringContainsString('Cadastral Parcel / Carta', $rendered);
        $this->assertStringContainsString('Bole Penthouse', $rendered);
    }
}
