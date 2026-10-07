<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Property;

class ValuationTest extends TestCase
{
    use RefreshDatabase;

    public function test_valuation_calculation()
    {
        $user = User::factory()->create();
        $property = Property::create([
            'title' => 'Investment Property',
            'property_code' => 'INV-001',
            'owner_user_id' => $user->id,
            'status' => 'active'
        ]);

        $res = $this->actingAs($user)->postJson("/api/v1/properties/{$property->id}/valuation", [
            'purchase_price' => 500000,
            'monthly_rent' => 4000,
            'operating_expenses_yearly' => 8000,
            'down_payment' => 100000
        ]);
        
        $res->assertStatus(201);
        
        // Annual Rent = 4000 * 12 = 48000
        // NOI = 48000 - 8000 = 40000
        // Cap Rate = 40000 / 500000 = 0.08
        // Cash on Cash = 40000 / 100000 = 0.40
        $res->assertJsonPath('data.cap_rate', '0.0800');
        $res->assertJsonPath('data.cash_on_cash_return', '0.4000');
        $res->assertJsonPath('data.is_authoritative', false);
        
        $this->assertDatabaseHas('property_valuations', [
            'property_id' => $property->id,
            'estimated_value' => '500000.00'
        ]);
    }
}
