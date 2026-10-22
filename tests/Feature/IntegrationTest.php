<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class IntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_idempotency_and_success()
    {
        Config::set('services.dummy.secret', 'secret');

        $payload = ['id' => 'evt_123', 'type' => 'charge.succeeded'];

        $res = $this->withHeaders(['X-Signature' => 'valid-signature-for-test'])
            ->postJson('/api/webhooks/dummy', $payload);

        $res->assertStatus(200);

        $this->assertDatabaseHas('webhook_events', [
            'idempotency_key' => 'evt_123',
            'status' => 'processed',
        ]);

        // Duplicate request
        $res2 = $this->withHeaders(['X-Signature' => 'valid-signature-for-test'])
            ->postJson('/api/webhooks/dummy', $payload);

        $res2->assertStatus(200);
        $res2->assertJsonPath('message', 'Event already processed.');
    }

    public function test_webhook_failure_isolation()
    {
        Config::set('services.dummy.secret', 'secret');

        $payload = ['id' => 'evt_456', 'type' => 'charge.failed'];

        $res = $this->withHeaders(['X-Signature' => 'valid-signature-for-test'])
            ->postJson('/api/webhooks/dummy', $payload);

        $res->assertStatus(200);

        $this->assertDatabaseHas('webhook_events', [
            'idempotency_key' => 'evt_456',
            'status' => 'failed',
            'error_message' => 'Simulated integration failure',
            'retry_count' => 1,
        ]);
    }

    public function test_webhook_invalid_signature()
    {
        Config::set('services.dummy.secret', 'secret');

        $res = $this->withHeaders(['X-Signature' => 'invalid'])
            ->postJson('/api/webhooks/dummy', ['id' => 'evt_789']);

        $res->assertStatus(401);
    }
}
