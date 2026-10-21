<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

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
            'status' => 'active',
        ]);

        $res = $this->actingAs($user)->postJson("/api/v1/properties/{$property->id}/valuation", [
            'purchase_price' => 500000,
            'monthly_rent' => 4000,
            'operating_expenses_yearly' => 8000,
            'down_payment' => 100000,
        ]);

        $res->assertStatus(201);

        // The new valuation service returns ensemble with methods
        // User inputs method contains cap_rate and cash_on_cash as percentages
        $res->assertJsonPath('data.methods.user_inputs.details.cap_rate', '8%');
        $res->assertJsonPath('data.methods.user_inputs.details.cash_on_cash', '40%');

        $this->assertDatabaseHas('property_valuations', [
            'property_id' => $property->id,
            'estimated_value' => '500000.00',
            'is_authoritative' => false,
        ]);
    }
}
