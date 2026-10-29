<?php

namespace Tests\Feature;

use App\Domains\Identity\Models\User;
use App\Domains\Property\Models\Property;
use App\Domains\Rental\Models\Lease;
use App\Domains\Rental\Models\TenantProfile;
use App\Domains\Transaction\Models\Document;
use App\Models\City;
use App\Models\Country;
use App\Models\Region;
use App\Services\Documents\DocumentWatermarkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase4Hole5Test extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected User $tenant;

    protected User $stranger;

    protected Property $property;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['name' => 'Kassahun Landholder']);
        $this->tenant = User::factory()->create(['name' => 'Birtukan Tenant']);
        $this->stranger = User::factory()->create(['name' => 'Unrelated User']);

        $country = Country::create(['name_en' => 'Ethiopia', 'code' => 'ET', 'phone_code' => '251', 'is_active' => true]);
        $region = Region::create(['country_id' => $country->id, 'name_en' => 'Addis Ababa', 'code' => 'AA', 'is_active' => true]);
        $city = City::create(['region_id' => $region->id, 'name_en' => 'Addis Ababa', 'code' => 'ADD', 'is_active' => true]);

        $this->property = Property::create([
            'property_code' => 'PROP-AA-'.uniqid(),
            'owner_user_id' => $this->owner->id,
            'title' => 'Old Airport Villa',
            'description' => 'Prime freehold residential compound',
            'status' => 'verified',
            'bedrooms' => 4,
            'bathrooms' => 3,
            'building_area' => 320,
            'cadastral_parcel_id' => 'AA-KK-03-9844',
            'block_number' => 'B-09',
            'woreda_cadastre_sheet' => 'W03-S11',
            'land_tenure_type' => 'leasehold_99yr',
            'lease_years_remaining' => 82,
            'carta_number' => 'CRTA-AA-2024-9844',
        ]);
    }

    /**
     * Test Hole 5: Ethiopian cadastral parcel attributes, tenure classifications, and helper methods.
     */
    public function test_cadastral_attributes_and_tenure_classification(): void
    {
        $this->assertEquals('AA-KK-03-9844', $this->property->cadastral_parcel_id);
        $this->assertEquals('B-09', $this->property->block_number);
        $this->assertEquals('W03-S11', $this->property->woreda_cadastre_sheet);
        $this->assertEquals('leasehold_99yr', $this->property->land_tenure_type);
        $this->assertEquals(82, $this->property->lease_years_remaining);
        $this->assertEquals('CRTA-AA-2024-9844', $this->property->carta_number);

        $this->assertTrue($this->property->isLeasehold());
        $this->assertEquals('AA-KK-03-9844', $this->property->getCadastralReference());

        // Test non-leasehold (private freehold / municipal permit)
        $freeholdProp = Property::create([
            'property_code' => 'PROP-FH-'.uniqid(),
            'owner_user_id' => $this->owner->id,
            'title' => 'Piazza Heritage Building',
            'status' => 'verified',
            'land_tenure_type' => 'private_freehold_historical',
        ]);

        $this->assertFalse($freeholdProp->isLeasehold());
    }

    /**
     * Test Hole 5: DocumentWatermarkService generates forensic metadata, watermarked content, and audit logs.
     */
    public function test_document_watermark_service_generates_forensic_metadata_and_logs(): void
    {
        $service = new DocumentWatermarkService;
        $ip = '197.156.104.55';

        // 1. Metadata Generation
        $meta = $service->generateWatermarkMetadata($this->owner, $ip);
        $this->assertEquals($this->owner->id, $meta['viewer_id']);
        $this->assertEquals('Kassahun Landholder', $meta['viewer_name']);
        $this->assertEquals($ip, $meta['ip_address']);
        $this->assertEquals(64, strlen($meta['security_token']));
        $this->assertEquals(16, strlen($meta['short_token']));
        $this->assertStringContainsString('Kassahun Landholder', $meta['legal_notice']);

        // 2. Binary PDF Forensic Watermarking
        $doc = Document::create([
            'user_id' => $this->owner->id,
            'name' => 'Official Carta Scan',
            'file_path' => 'documents/test_carta.pdf',
            'mime_type' => 'application/pdf',
        ]);

        $pdfSample = "%PDF-1.4\n1 0 obj\n<< /Title (Carta) >>\nendobj\n%%EOF";
        $watermarkedPdf = $service->watermarkContent($pdfSample, $this->owner, $ip, $doc);

        $this->assertStringContainsString('%SRMS_FORENSIC_WATERMARK_START%', $watermarkedPdf);
        $this->assertStringContainsString('% TOKEN: ', $watermarkedPdf);
        $this->assertStringContainsString('% VIEWER: Kassahun Landholder', $watermarkedPdf);
        $this->assertStringContainsString('% IP: '.$ip, $watermarkedPdf);
        $this->assertStringContainsString('%SRMS_FORENSIC_WATERMARK_END%', $watermarkedPdf);

        // Verify audit log was recorded
        $this->assertDatabaseHas('document_access_logs', [
            'document_id' => $doc->id,
            'user_id' => $this->owner->id,
            'ip_address' => $ip,
            'action' => 'download_watermarked',
        ]);

        // 3. HTML/Canonical Title Deed Watermarking
        $canonicalHtml = $service->generateCanonicalCartaDeed($this->property);
        $this->assertStringContainsString('Official Real Property Title Deed (ካርታ)', $canonicalHtml);
        $this->assertStringContainsString('AA-KK-03-9844', $canonicalHtml);
        $this->assertStringContainsString('CRTA-AA-2024-9844', $canonicalHtml);

        $watermarkedHtml = $service->watermarkContent($canonicalHtml, $this->tenant, '196.188.240.12');
        $this->assertStringContainsString('id="srms-forensic-watermark"', $watermarkedHtml);
        $this->assertStringContainsString('Birtukan Tenant', $watermarkedHtml);
        $this->assertStringContainsString('id="srms-diagonal-watermark"', $watermarkedHtml);
    }

    /**
     * Test Hole 5: Unauthorized user receives 403 when trying to download title deed.
     */
    public function test_unauthorized_user_cannot_download_title_deed(): void
    {
        $response = $this->actingAs($this->stranger)
            ->get(route('dashboard.properties.deed.download', $this->property));

        $response->assertStatus(403);
    }

    /**
     * Test Hole 5: Registered landholder (owner) can download watermarked title deed.
     */
    public function test_owner_can_download_watermarked_title_deed(): void
    {
        $response = $this->actingAs($this->owner)
            ->get(route('dashboard.properties.deed.download', $this->property));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
        $response->assertHeader('Cache-Control', 'must-revalidate, no-cache, no-store, private');
        $this->assertStringContainsString('CRTA-AA-2024-9844', $response->getContent());
        $this->assertStringContainsString('Official Real Property Title Deed (ካርታ)', $response->getContent());
        $this->assertStringContainsString('id="srms-forensic-watermark"', $response->getContent());
        $this->assertStringContainsString('Kassahun Landholder', $response->getContent());
    }

    /**
     * Test Hole 5: Active tenant with signed lease on property can download watermarked deed.
     */
    public function test_active_tenant_can_download_watermarked_title_deed(): void
    {
        $tenantProfile = TenantProfile::create([
            'user_id' => $this->tenant->id,
            'employment_status' => 'Employed',
        ]);

        Lease::create([
            'property_id' => $this->property->id,
            'tenant_profile_id' => $tenantProfile->id,
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addMonths(11)->toDateString(),
            'rent_amount' => 45000.0,
            'deposit_amount' => 90000.0,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->tenant)
            ->get(route('dashboard.properties.deed.download', $this->property));

        $response->assertStatus(200);
        $this->assertStringContainsString('id="srms-forensic-watermark"', $response->getContent());
        $this->assertStringContainsString('Birtukan Tenant', $response->getContent());
    }
}
