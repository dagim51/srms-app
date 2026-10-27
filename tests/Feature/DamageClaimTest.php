<?php

namespace Tests\Feature;

use App\Domains\Trust\Models\DamageClaim;
use App\Models\Accommodation;
use App\Models\AccommodationBooking;
use App\Models\Property;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DamageClaimTest extends TestCase
{
    use RefreshDatabase;

    public function test_host_can_file_damage_claim_against_booking(): void
    {
        $host = User::factory()->create();
        $guest = User::factory()->create();

        $property = Property::create([
            'title' => 'Bole Penthouse',
            'property_code' => 'PENT-001',
            'owner_user_id' => $host->id,
            'status' => 'verified',
        ]);

        $accommodation = Accommodation::create([
            'property_id' => $property->id,
            'host_id' => $host->id,
            'max_guests' => 6,
        ]);

        $booking = AccommodationBooking::create([
            'accommodation_id' => $accommodation->id,
            'user_id' => $guest->id,
            'check_in_date' => Carbon::now()->subDays(5)->toDateString(),
            'check_out_date' => Carbon::now()->subDays(2)->toDateString(),
            'total_price' => 3000.00,
            'status' => 'confirmed',
        ]);

        $claim = DamageClaim::create([
            'booking_id' => $booking->id,
            'host_id' => $host->id,
            'guest_id' => $guest->id,
            'amount_claimed' => 7500.00,
            'currency' => 'ETB',
            'description' => 'Broken glass balcony coffee table during stay',
            'evidence_photos' => ['https://storage.srms.et/claims/photo1.jpg'],
            'status' => 'pending',
        ]);

        $this->assertInstanceOf(DamageClaim::class, $claim);
        $this->assertEquals(7500.00, $claim->amount_claimed);
        $this->assertEquals('pending', $claim->status);

        // Host reviews and claim is settled
        $claim->update([
            'status' => 'settled',
            'resolution_notes' => 'Guest agreed to reimburse 7500 ETB via security deposit deduction',
            'resolved_at' => now(),
        ]);

        $this->assertDatabaseHas('damage_claims', [
            'id' => $claim->id,
            'status' => 'settled',
        ]);
        $this->assertNotNull($claim->fresh()->resolved_at);
    }
}
