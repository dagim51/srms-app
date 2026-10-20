<?php

namespace Tests\Feature;

use App\Models\Accommodation;
use App\Models\AccommodationPricingRule;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Listing;
use App\Models\Offer;
use App\Models\OfferEvent;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\RentSchedule;
use App\Models\TenantProfile;
use App\Models\User;
use App\Services\Bookings\BookingManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class BusinessEnginesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed core lookups
        \App\Models\Role::firstOrCreate(['name' => 'client', 'guard_name' => 'web']);
    }

    public function test_dynamic_pricing_calculation_itemizes_properly()
    {
        $user = User::factory()->create();
        $propertyType = PropertyType::firstOrCreate(['code' => 'APT'], ['name_en' => 'Apartment']);
        $property = Property::create([
            'title' => 'Luxury Condo',
            'property_code' => 'LUX-' . uniqid(),
            'type_id' => $propertyType->id,
            'status' => 'active',
        ]);

        $listing = Listing::create([
            'property_id' => $property->id,
            'owner_actor_id' => $user->id,
            'purpose' => 'rent',
            'price' => 150.00,
            'status' => 'published',
        ]);

        $accommodation = Accommodation::create([
            'property_id' => $property->id,
            'listing_id' => $listing->id,
            'host_id' => $user->id,
            'max_guests' => 4,
            'bedrooms' => 2,
            'beds' => 2,
            'bathrooms' => 1,
            'check_in_time' => '14:00',
            'check_out_time' => '11:00',
            'is_active' => true,
        ]);

        // Add a 10% seasonal rule
        AccommodationPricingRule::create([
            'accommodation_id' => $accommodation->id,
            'type' => 'seasonal',
            'price_modifier_type' => 'percentage',
            'price_modifier_value' => 10.0,
            'priority' => 1,
        ]);

        $bookingManager = new BookingManager();

        // 3 nights encompassing Friday and Saturday
        $checkIn = '2026-10-23'; // Friday
        $checkOut = '2026-10-26'; // Monday (3 nights: Fri, Sat, Sun)

        $breakdown = $bookingManager->calculateDetailedPrice($accommodation, $checkIn, $checkOut);

        $this->assertEquals(3, $breakdown['nights']);
        $this->assertEquals(150.00, $breakdown['base_nightly_rate']);
        $this->assertEquals(450.00, $breakdown['base_subtotal']);
        $this->assertEquals(2, $breakdown['weekend_nights']); // Fri, Sat
        $this->assertGreaterThan(0, $breakdown['weekend_surcharge']);
        $this->assertNotEmpty($breakdown['pricing_rules']);
        $this->assertEquals(50.00, $breakdown['cleaning_fee']);
        $this->assertGreaterThan(0, $breakdown['service_fee']);
        $this->assertGreaterThan(0, $breakdown['tax_amount']);
        $this->assertGreaterThan($breakdown['stay_subtotal'], $breakdown['total_price']);
    }

    public function test_expire_offers_command_expires_past_offers_and_creates_event()
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $propertyType = PropertyType::firstOrCreate(['code' => 'VIL'], ['name_en' => 'Villa']);
        $property = Property::create([
            'title' => 'Cozy Villa',
            'property_code' => 'VIL-' . uniqid(),
            'type_id' => $propertyType->id,
            'status' => 'active',
        ]);
        $listing = Listing::create([
            'property_id' => $property->id,
            'owner_actor_id' => $seller->id,
            'purpose' => 'sale',
            'price' => 500000.00,
            'status' => 'published',
        ]);

        $pastOffer = Offer::create([
            'listing_id' => $listing->id,
            'buyer_id' => $buyer->id,
            'amount' => 480000.00,
            'status' => 'pending',
            'expires_at' => now()->subDay(),
        ]);

        $futureOffer = Offer::create([
            'listing_id' => $listing->id,
            'buyer_id' => $buyer->id,
            'amount' => 490000.00,
            'status' => 'pending',
            'expires_at' => now()->addDays(2),
        ]);

        $exitCode = Artisan::call('srms:expire-offers');
        $this->assertEquals(0, $exitCode);

        $this->assertEquals('expired', $pastOffer->fresh()->status);
        $this->assertEquals('pending', $futureOffer->fresh()->status);

        $this->assertDatabaseHas('offer_events', [
            'offer_id' => $pastOffer->id,
            'event_type' => 'expired',
        ]);
    }

    public function test_generate_rent_invoices_command_creates_invoices_for_upcoming_rent()
    {
        $tenantUser = User::factory()->create();
        $tenantProfile = TenantProfile::create([
            'user_id' => $tenantUser->id,
            'employment_status' => 'employed',
            'annual_income' => 120000,
            'background_check_status' => 'passed',
        ]);

        $propertyType = PropertyType::firstOrCreate(['code' => 'STU'], ['name_en' => 'Studio']);
        $property = Property::create([
            'title' => 'Studio 4B',
            'property_code' => 'STU-' . uniqid(),
            'type_id' => $propertyType->id,
            'status' => 'active',
        ]);

        $lease = Lease::create([
            'property_id' => $property->id,
            'tenant_profile_id' => $tenantProfile->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'rent_amount' => 1200.00,
            'deposit_amount' => 2400.00,
            'status' => 'active',
        ]);

        $schedule = RentSchedule::create([
            'lease_id' => $lease->id,
            'due_date' => now()->addDays(3)->toDateString(),
            'amount' => 1200.00,
            'status' => 'unpaid',
        ]);

        $exitCode = Artisan::call('srms:generate-rent-invoices', ['--days' => 7]);
        $this->assertEquals(0, $exitCode);

        $this->assertDatabaseHas('invoices', [
            'user_id' => $tenantUser->id,
            'amount' => 1200.00,
            'status' => 'sent',
        ]);

        // Running again must not duplicate invoices
        Artisan::call('srms:generate-rent-invoices', ['--days' => 7]);
        $this->assertEquals(1, Invoice::where('user_id', $tenantUser->id)->count());
    }

    public function test_check_milestones_command_audits_projects()
    {
        $exitCode = Artisan::call('srms:check-milestones');
        $this->assertEquals(0, $exitCode);
    }
}
