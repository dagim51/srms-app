<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CbeBirrGateway implements PaymentGatewayInterface
{
    protected array $config;

    public function __construct()
    {
        $this->config = config('payments.cbe_birr') ?? [
            'api_url' => env('CBE_BIRR_API_URL', 'https://api.cbebirr.et'),
            'merchant_id' => env('CBE_BIRR_MERCHANT_ID', 'CBE_MOCK_MERCHANT'),
            'secret_key' => env('CBE_BIRR_SECRET_KEY', 'cbe_birr_test_secret_key_12345'),
            'notify_url' => env('CBE_BIRR_NOTIFY_URL', 'https://srms.et/api/v1/payments/webhook/cbe-birr'),
            'currency' => 'ETB',
        ];
    }

    protected function isTesting(): bool
    {
        return app()->environment('testing', 'testing-pgsql') || app()->runningUnitTests() || empty($this->config['merchant_id']);
    }

    /**
     * Initialize payment with CBE Birr gateway.
     */
    public function initialize(array $data): array
    {
        if ($this->isTesting()) {
            Log::debug('CbeBirrGateway: Using mock initialize', ['out_trade_no' => $data['out_trade_no']]);

            return [
                'code' => 'SUCCESS',
                'payment_url' => 'https://checkout.cbebirr.et/pay/'.$data['out_trade_no'],
                'transaction_ref' => 'CBE-'.strtoupper(uniqid()),
                'out_trade_no' => $data['out_trade_no'],
                'amount' => (float) $data['amount'],
            ];
        }

        $payload = [
            'merchant_id' => $this->config['merchant_id'],
            'out_trade_no' => $data['out_trade_no'],
            'amount' => (float) $data['amount'],
            'currency' => $this->config['currency'] ?? 'ETB',
            'notify_url' => $this->config['notify_url'],
            'description' => $data['description'] ?? 'SRMS Property Service',
        ];

        try {
            $response = Http::asJson()
                ->timeout(30)
                ->post($this->config['api_url'].'/checkout/initiate', $payload);

            if ($response->successful()) {
                return $response->json();
            }

            throw new \RuntimeException('CBE Birr API error: '.$response->body());
        } catch (Exception $e) {
            Log::error('CbeBirrGateway: Initialization failed', [
                'error' => $e->getMessage(),
                'out_trade_no' => $data['out_trade_no'],
            ]);
            throw new \RuntimeException('Payment gateway unavailable: '.$e->getMessage());
        }
    }

    /**
     * Verify webhook signature using HMAC-SHA256.
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $secret = $this->config['secret_key'] ?? 'cbe_birr_test_secret_key_12345';
        $computed = hash_hmac('sha256', $payload, $secret);

        return hash_equals($computed, $signature);
    }

    /**
     * Process incoming webhook event.
     */
    public function processEvent(array $payload): void
    {
        Log::info('CbeBirrGateway: Processing webhook event', [
            'out_trade_no' => $payload['out_trade_no'] ?? null,
            'status' => $payload['status'] ?? null,
        ]);
    }
}
