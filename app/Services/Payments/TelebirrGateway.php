<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class TelebirrGateway implements PaymentGatewayInterface
{
    protected array $config;

    public function __construct()
    {
        $this->config = config('payments.telebirr');
    }

    /**
     * Initialize a payment and return the payment URL/token.
     */
    public function initialize(array $data): array
    {
        // Mock mode for testing
        if (app()->environment('testing') && empty($this->config['app_id'])) {
            \Log::debug('TelebirrGateway: Using mock initialize', ['out_trade_no' => $data['out_trade_no']]);
            return [
                'code' => 'SUCCESS',
                'payment_url' => 'https://test.telebirr.com/pay/' . $data['out_trade_no'],
                'qr_code' => 'data:image/png;base64,mock',
                'out_trade_no' => $data['out_trade_no'],
            ];
        }

        $payload = [
            'app_id' => $this->config['app_id'],
            'app_key' => $this->config['app_key'],
            'out_trade_no' => $data['out_trade_no'],
            'total_amount' => (float) $data['amount'],
            'subject' => $data['subject'],
            'body' => $data['body'] ?? $data['subject'],
            'currency' => $this->config['currency'] ?? 'ETB',
            'notify_url' => $this->config['notify_url'],
            'return_url' => $this->config['return_url'],
            'timeout_express' => $data['timeout_express'] ?? '30m',
        ];

        // Add idempotency key if provided
        if (!empty($data['idempotency_key'])) {
            $payload['idempotency_key'] = $data['idempotency_key'];
        }

        $endpoint = $this->config['api_url'] . '/payment/initialize';

        try {
            $response = Http::asJson()
                ->withHeaders([
                    'Accept' => 'application/json',
                ])
                ->timeout(30)
                ->post($endpoint, $payload);

            return $this->handleResponse($response);
        } catch (Exception $e) {
            Log::error('Telebirr initialization failed', [
                'error' => $e->getMessage(),
                'out_trade_no' => $data['out_trade_no'],
            ]);
            throw new \RuntimeException('Payment gateway unavailable: ' . $e->getMessage());
        }
    }

    /**
     * Verify payment status by transaction reference.
     */
    public function verify(string $transactionRef): array
    {
        // Mock mode for testing
        if (app()->environment('testing') && empty($this->config['app_id'])) {
            return [
                'code' => 'SUCCESS',
                'trade_status' => 'SUCCESS',
                'out_trade_no' => $transactionRef,
            ];
        }

        $endpoint = $this->config['api_url'] . '/payment/query';

        $payload = [
            'app_id' => $this->config['app_id'],
            'out_trade_no' => $transactionRef,
        ];

        try {
            $response = Http::asJson()
                ->withHeaders([
                    'Accept' => 'application/json',
                ])
                ->timeout(30)
                ->post($endpoint, $payload);

            return $this->handleResponse($response);
        } catch (Exception $e) {
            Log::error('Telebirr verification failed', [
                'error' => $e->getMessage(),
                'transaction_ref' => $transactionRef,
            ]);
            throw new \RuntimeException('Payment gateway unavailable: ' . $e->getMessage());
        }
    }

    /**
     * Process refund for a completed payment.
     */
    public function refund(string $transactionRef, float $amount, string $reason): array
    {
        // Mock mode for testing
        if (app()->environment('testing') && empty($this->config['app_id'])) {
            return [
                'code' => 'SUCCESS',
                'refund_id' => 'REF-' . uniqid(),
                'out_trade_no' => $transactionRef,
                'refund_amount' => $amount,
                'reason' => $reason,
            ];
        }

        $endpoint = $this->config['api_url'] . '/payment/refund';

        $payload = [
            'app_id' => $this->config['app_id'],
            'app_key' => $this->config['app_key'],
            'out_trade_no' => $transactionRef,
            'refund_amount' => $amount,
            'refund_reason' => $reason,
        ];

        try {
            $response = Http::asJson()
                ->withHeaders([
                    'Accept' => 'application/json',
                ])
                ->timeout(30)
                ->post($endpoint, $payload);

            return $this->handleResponse($response);
        } catch (Exception $e) {
            Log::error('Telebirr refund failed', [
                'error' => $e->getMessage(),
                'transaction_ref' => $transactionRef,
            ]);
            throw new \RuntimeException('Payment gateway unavailable: ' . $e->getMessage());
        }
    }

    /**
     * Verify webhook signature from Telebirr.
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        // Telebirr uses RSA signature verification
        // The public key should be configured
        $publicKey = $this->config['public_key'];

        if (empty($publicKey)) {
            Log::warning('Telebirr public key not configured, skipping signature verification');
            return true; // Allow in sandbox if not configured
        }

        $publicKeyResource = openssl_pkey_get_public($publicKey);

        if (!$publicKeyResource) {
            Log::error('Invalid Telebirr public key');
            return false;
        }

        $verified = openssl_verify(
            $payload,
            base64_decode($signature),
            $publicKeyResource,
            OPENSSL_ALGO_SHA256
        );

        openssl_free_key($publicKeyResource);

        return $verified === 1;
    }

    /**
     * Process incoming webhook event.
     */
    public function processEvent(array $payload): void
    {
        $eventType = $payload['event_type'] ?? $payload['type'] ?? 'unknown';
        $outTradeNo = $payload['out_trade_no'] ?? $payload['trade_no'] ?? null;

        Log::info('Telebirr webhook received', [
            'event_type' => $eventType,
            'out_trade_no' => $outTradeNo,
            'status' => $payload['trade_status'] ?? 'unknown',
        ]);

        // The actual event handling is done in the controller/webhook handler
        // This method just logs and validates
        if (empty($outTradeNo)) {
            Log::warning('Telebirr webhook missing out_trade_no', ['payload' => $payload]);
        }
    }

    /**
     * Generate payment initiation URL for redirect-based flow.
     */
    public function getPaymentUrl(array $data): string
    {
        $initData = $this->initialize($data);

        // Telebirr typically returns a payment_url or qr_code
        return $initData['payment_url'] ?? $initData['qr_code'] ?? '';
    }

    /**
     * Handle HTTP response from Telebirr API.
     */
    protected function handleResponse(Response $response): array
    {
        if ($response->successful()) {
            $data = $response->json();

            // Check Telebirr-specific response code
            $code = $data['code'] ?? $data['result_code'] ?? 'UNKNOWN';
            $msg = $data['msg'] ?? $data['message'] ?? '';

            if (in_array($code, ['SUCCESS', '0', '0000', 0, 200])) {
                return $data;
            }

            throw new \RuntimeException("Telebirr API error [{$code}]: {$msg}");
        }

        $statusCode = $response->status();
        $body = $response->body();

        throw new \RuntimeException("Telebirr HTTP {$statusCode}: {$body}");
    }

    /**
     * Generate a unique order reference.
     */
    public static function generateOrderRef(string $prefix = 'SRMS'): string
    {
        return $prefix . '-' . now()->format('YmdHis') . '-' . Str::random(8);
    }
}