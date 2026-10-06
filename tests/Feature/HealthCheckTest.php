<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    /**
     * Test the API health check endpoint.
     */
    public function test_api_health_endpoint_returns_successful_response(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'environment',
                'timestamp',
            ])
            ->assertJson([
                'status' => 'success',
                'message' => 'API is running cleanly.',
            ]);
    }
}
