<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class SearchAlertTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_alert_creation()
    {
        $user = User::factory()->create();

        $res = $this->actingAs($user)->postJson('/api/v1/search/alerts', [
            'name' => 'Bole Villas',
            'criteria' => [
                'location' => 'Bole',
                'type' => 'villa',
                'price_max' => 50000
            ],
            'frequency' => 'daily'
        ]);
        
        $res->assertStatus(201);
        $res->assertJsonPath('data.name', 'Bole Villas');
        
        $this->assertDatabaseHas('search_alerts', [
            'user_id' => $user->id,
            'name' => 'Bole Villas',
            'frequency' => 'daily'
        ]);
    }
}
