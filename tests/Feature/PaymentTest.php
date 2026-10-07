<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\AccommodationBooking;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Accommodation;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $booking;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        
        $property = Property::create([
            'title' => 'Payable Property',
            'property_code' => 'PAY-001',
            'owner_user_id' => User::factory()->create()->id,
            'status' => 'verified',
        ]);
        
        $accommodation = Accommodation::create([
            'property_id' => $property->id,
            'host_id' => $property->owner_user_id,
            'max_guests' => 2,
        ]);
        
        $this->booking = AccommodationBooking::create([
            'accommodation_id' => $accommodation->id,
            'user_id' => $this->user->id,
            'check_in_date' => now()->addDays(5)->format('Y-m-d'),
            'check_out_date' => now()->addDays(7)->format('Y-m-d'),
            'guests' => 1,
            'total_price' => 500,
            'status' => 'pending',
        ]);
    }

    public function test_user_can_initialize_payment_for_booking()
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/payments', [
            'payable_type' => 'App\Models\AccommodationBooking',
            'payable_id' => $this->booking->id,
            'payment_method' => 'chapa',
        ]);
        
        $response->assertStatus(201)
                 ->assertJsonPath('data.status', 'pending')
                 ->assertJsonPath('data.amount', 500);
                 
        $this->assertDatabaseHas('payments', [
            'payable_type' => 'App\Models\AccommodationBooking',
            'payable_id' => $this->booking->id,
            'payment_method' => 'chapa',
            'amount' => 500,
        ]);
    }

    public function test_cannot_pay_for_another_users_booking()
    {
        $otherUser = User::factory()->create();
        
        $response = $this->actingAs($otherUser)->postJson('/api/v1/payments', [
            'payable_type' => 'App\Models\AccommodationBooking',
            'payable_id' => $this->booking->id,
            'payment_method' => 'telebirr',
        ]);
        
        $response->assertStatus(403);
    }

    public function test_cannot_create_duplicate_payment_for_completed_booking()
    {
        Payment::create([
            'user_id' => $this->user->id,
            'payable_type' => 'App\Models\AccommodationBooking',
            'payable_id' => $this->booking->id,
            'amount' => 500,
            'currency' => 'ETB',
            'payment_method' => 'card',
            'status' => 'completed',
        ]);
        
        $response = $this->actingAs($this->user)->postJson('/api/v1/payments', [
            'payable_type' => 'App\Models\AccommodationBooking',
            'payable_id' => $this->booking->id,
            'payment_method' => 'chapa',
        ]);
        
        $response->assertStatus(409); // Conflict: already paid
    }

    public function test_webhook_can_mark_payment_completed_and_cascade_status()
    {
        $payment = Payment::create([
            'user_id' => $this->user->id,
            'payable_type' => 'App\Models\AccommodationBooking',
            'payable_id' => $this->booking->id,
            'amount' => 500,
            'currency' => 'ETB',
            'payment_method' => 'chapa',
            'status' => 'pending',
        ]);
        
        // Simulating webhook hit
        $response = $this->actingAs($this->user)->patchJson("/api/v1/payments/{$payment->id}", [
            'status' => 'completed',
            'transaction_reference' => 'CHAPA-REF-12345',
            'metadata' => ['gateway_response' => 'success'],
        ]);
        
        $response->assertStatus(200);
        $this->assertEquals('completed', $payment->fresh()->status);
        $this->assertEquals('CHAPA-REF-12345', $payment->fresh()->transaction_reference);
        
        // Check cascade to booking
        $this->assertEquals('confirmed', $this->booking->fresh()->status);
    }
}
