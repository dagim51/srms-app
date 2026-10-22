<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\Property;
use App\Models\User;
use App\Support\EthiopianCalendar;
use App\Support\EthiopicTextNormalizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class AmharicIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_ethiopic_text_normalizer_homophones()
    {
        // H family
        $this->assertEquals('ሀ', EthiopicTextNormalizer::normalizeFidel('ሐ'));
        $this->assertEquals('ሀ', EthiopicTextNormalizer::normalizeFidel('ኀ'));
        $this->assertEquals('ሁ', EthiopicTextNormalizer::normalizeFidel('ሑ'));

        // S family
        $this->assertEquals('ሰላም', EthiopicTextNormalizer::normalizeFidel('ሠላም'));

        // A family
        $this->assertEquals('አዲስ', EthiopicTextNormalizer::normalizeFidel('ዐዲስ'));

        // Ts family
        $this->assertEquals('ጸ', EthiopicTextNormalizer::normalizeFidel('ፀ'));
        $this->assertEquals('ጸሀይ', EthiopicTextNormalizer::normalizeFidel('ፀሐይ'));
    }

    public function test_ethiopic_text_normalizer_search_tokens()
    {
        // English query Bole expands to Amharic ቦሌ
        $boleTokens = EthiopicTextNormalizer::getSearchTokens('Bole');
        $this->assertContains('Bole', $boleTokens);
        $this->assertContains('ቦሌ', $boleTokens);

        // Amharic query ቦሌ expands to English bole
        $amTokens = EthiopicTextNormalizer::getSearchTokens('ቦሌ');
        $this->assertContains('ቦሌ', $amTokens);
        $this->assertContains('bole', $amTokens);

        // Villa expands to ቪላ
        $villaTokens = EthiopicTextNormalizer::getSearchTokens('villa');
        $this->assertContains('villa', $villaTokens);
        $this->assertContains('ቪላ', $villaTokens);
    }

    public function test_public_property_search_matches_amharic_and_english_queries()
    {
        $owner = User::factory()->create();

        $property = Property::create([
            'owner_user_id' => $owner->id,
            'title' => 'Luxury Bole Apartment',
            'description' => 'Beautiful villa in Kazanchis with garden',
            'property_code' => 'PROP-BOLE-001',
        ]);

        $listing = Listing::create([
            'property_id' => $property->id,
            'status' => 'published',
            'visibility' => 'public',
            'purpose' => 'Rent',
            'price' => 50000,
            'published_at' => now(),
        ]);

        // 1. Search using Amharic "ቦሌ" should find the property titled "Luxury Bole Apartment"
        $responseAm = $this->get('/properties?search=ቦሌ');
        $responseAm->assertStatus(200);
        $responseAm->assertSee('Luxury Bole Apartment');

        // 2. Search using Amharic "ካዛንቺስ" should find it via description
        $responseKaz = $this->get('/properties?search=ካዛንቺስ');
        $responseKaz->assertStatus(200);
        $responseKaz->assertSee('Luxury Bole Apartment');

        // 3. Location filter using Amharic "ቦሌ"
        $responseLoc = $this->get('/properties?location=ቦሌ');
        $responseLoc->assertStatus(200);
        $responseLoc->assertSee('Luxury Bole Apartment');
    }

    public function test_ethiopian_calendar_date_conversion_accuracy()
    {
        // 2026-10-11 -> Tikimt 1, 2019 E.C.
        $eth = EthiopianCalendar::fromGregorian(2026, 10, 11);
        $this->assertEquals(2019, $eth['year']);
        $this->assertEquals(2, $eth['month']);
        $this->assertEquals(1, $eth['day']);

        // Reverse conversion: Tikimt 1, 2019 -> 2026-10-11
        $greg = EthiopianCalendar::toGregorian(2019, 2, 1);
        $this->assertEquals(2026, $greg['year']);
        $this->assertEquals(10, $greg['month']);
        $this->assertEquals(11, $greg['day']);

        // Enkutatash 2017: Sep 11, 2024 -> Meskerem 1, 2017 E.C.
        $ethNewYear = EthiopianCalendar::fromGregorian(2024, 9, 11);
        $this->assertEquals(2017, $ethNewYear['year']);
        $this->assertEquals(1, $ethNewYear['month']);
        $this->assertEquals(1, $ethNewYear['day']);
    }

    public function test_ethiopian_calendar_formatting()
    {
        // Amharic locale formatting
        $formattedAm = EthiopianCalendar::format('2026-10-11', 'am');
        $this->assertEquals('ጥቅምት 1፣ 2019 ዓ.ም.', $formattedAm);

        // Amharic dual formatting
        $formattedDualAm = EthiopianCalendar::format('2026-10-11', 'am', true);
        $this->assertStringContainsString('ጥቅምት 1፣ 2019 ዓ.ም.', $formattedDualAm);
        $this->assertStringContainsString('እ.ኤ.አ.', $formattedDualAm);

        // English formatting
        $formattedEn = EthiopianCalendar::format('2026-10-11', 'en');
        $this->assertEquals('Oct 11, 2026', $formattedEn);

        // English dual formatting
        $formattedDualEn = EthiopianCalendar::format('2026-10-11', 'en', true);
        $this->assertStringContainsString('Oct 11, 2026', $formattedDualEn);
        $this->assertStringContainsString('Tikimt 1, 2019 E.C.', $formattedDualEn);
    }

    public function test_blade_ethiopian_date_component_renders_correctly()
    {
        App::setLocale('am');
        $rendered = Blade::render('<x-ethiopian-date :date="\'2026-10-11\'" />');
        $this->assertStringContainsString('ጥቅምት 1፣ 2019 ዓ.ም.', $rendered);
        $this->assertStringContainsString('<time', $rendered);

        App::setLocale('en');
        $renderedEn = Blade::render('<x-ethiopian-date :date="\'2026-10-11\'" />');
        $this->assertStringContainsString('Oct 11, 2026', $renderedEn);
    }

    public function test_seo_alternates_and_client_js_translation_bridge()
    {
        // Home page layout
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('hreflang="en"', false);
        $response->assertSee('hreflang="am"', false);
        $response->assertSee('window.__ = function', false);

        // Dashboard layout
        $user = User::factory()->create();
        $dashRes = $this->actingAs($user)->get('/dashboard');
        $dashRes->assertStatus(200);
        $dashRes->assertSee('hreflang="en"', false);
        $dashRes->assertSee('hreflang="am"', false);
        $dashRes->assertSee('window.__ = function', false);
    }

    public function test_amharic_validation_attributes()
    {
        App::setLocale('am');
        $titleTrans = __('validation.attributes.title');
        $this->assertEquals('ርዕስ', $titleTrans);

        $priceTrans = __('validation.attributes.price');
        $this->assertEquals('ዋጋ', $priceTrans);

        $descTrans = __('validation.attributes.description');
        $this->assertEquals('መግለጫ', $descTrans);
    }
}
