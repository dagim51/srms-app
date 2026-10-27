<?php

namespace Tests\Feature;

use Tests\TestCase;

class LocalizationAfaanOromoTest extends TestCase
{
    public function test_afaan_oromo_locale_switches_and_loads_translations(): void
    {
        app()->setLocale('om');

        // Check authentication translations in Afaan Oromo
        $authFailed = __('auth.failed');
        $this->assertNotEmpty($authFailed);
        $this->assertNotEquals('auth.failed', $authFailed);

        // Check validation translations
        $validationRequired = __('validation.required', ['attribute' => 'maqaa']);
        $this->assertNotEmpty($validationRequired);
        $this->assertStringContainsString('maqaa', $validationRequired);

        // Check location names in Afaan Oromo
        $boleZone = __('locations.zones.bole');
        $this->assertEquals('Boolee', $boleZone);

        $oromiaRegion = __('locations.regions.oromia');
        $this->assertEquals('Oromiyaa', $oromiaRegion);
    }
}
