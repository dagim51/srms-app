<?php

namespace Tests\Feature;

use App\Domains\Transaction\Models\Payment;
use App\Models\User;
use App\Services\Payments\CbeBirrGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CbeBirrGatewayTest extends TestCase
{
    use RefreshDatabase;

    public function test_cbe_birr_gateway_initialize_returns_mock_in_test_environment(): void
    {
        $gateway = app(CbeBirrGateway::class);

        $result = $gateway->initialize([
            'out_trade_no' => 'ORDER-CBE-1001',
            'amount' => 1500.0,
            'description' => 'Test Rental Deposit',
        ]);

        $this->assertEquals('SUCCESS', $result['code']);
        $this->assertStringContainsString('ORDER-CBE-1001', $result['payment_url']);
        $this->assertEquals(1500.0, $result['amount']);
        $this->assertNotEmpty($result['transaction_ref']);
    }

    public function test_cbe_birr_signature_verification_succeeds_with_valid_hmac(): void
    {
        $gateway = app(CbeBirrGateway::class);
        $payload = json_encode(['out_trade_no' => 'ORDER-123', 'status' => 'SUCCESS']);
        $secret = 'cbe_birr_test_secret_key_12345';
        $validSignature = hash_hmac('sha256', $payload, $secret);

        $this->assertTrue($gateway->verifyWebhookSignature($payload, $validSignature));
        $this->assertFalse($gateway->verifyWebhookSignature($payload, 'invalid_tampered_signature'));
    }

    public function test_cbe_birr_webhook_processes_payment_successfully(): void
    {
        $user = User::factory()->create();

        $payment = Payment::create([
            'user_id' => $user->id,
            'payable_type' => 'App\Models\AccommodationBooking',
            'payable_id' => (string) Str::uuid(),
            'amount' => 2500.00,
            'currency' => 'ETB',
            'status' => 'pending',
            'payment_method' => 'cbe_birr',
            'transaction_reference' => 'CBE-TX-5500',
        ]);

        $payload = [
            'out_trade_no' => 'CBE-TX-5500',
            'status' => 'SUCCESS',
            'amount' => 2500.00,
        ];
        $rawPayload = json_encode($payload);
        $signature = hash_hmac('sha256', $rawPayload, 'cbe_birr_test_secret_key_12345');

        $response = $this->call(
            'POST',
            '/api/webhooks/payments/cbe-birr',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-Cbe-Birr-Signature' => $signature,
            ],
            $rawPayload
        );

        $response->assertStatus(200)
            ->assertJsonPath('code', 'SUCCESS');

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'completed',
        ]);

        // Replay webhook should return already processed
        $replayResponse = $this->call(
            'POST',
            '/api/webhooks/payments/cbe-birr',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-Cbe-Birr-Signature' => $signature,
            ],
            $rawPayload
        );

        $replayResponse->assertStatus(200)
            ->assertJsonPath('msg', 'Already processed');
    }
}
