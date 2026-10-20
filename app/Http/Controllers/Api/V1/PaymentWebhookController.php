<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Payments\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Handle Telebirr payment notification (webhook).
     */
    public function telebirr(Request $request)
    {
        // Webhook endpoint - no auth required, signature verification handles auth
        $payload = $request->all();
        $signature = $request->header('X-Telebirr-Signature') ?? $request->header('signature');

        Log::info('Telebirr webhook received', [
            'payload' => $payload,
            'has_signature' => !empty($signature),
        ]);

        // Verify webhook signature if provided
        if ($signature) {
            $gateway = app(\App\Services\Payments\TelebirrGateway::class);
            $rawPayload = $request->getContent();

            if (!$gateway->verifyWebhookSignature($rawPayload, $signature)) {
                Log::warning('Telebirr webhook signature verification failed');
                return response()->json(['code' => 'SIGNATURE_INVALID', 'msg' => 'Invalid signature'], 400);
            }
        }

        // Process the webhook event
        $gateway = app(\App\Services\Payments\TelebirrGateway::class);
        $gateway->processEvent($payload);

        // Handle based on trade status
        $tradeStatus = $payload['trade_status'] ?? $payload['status'] ?? 'unknown';
        $outTradeNo = $payload['out_trade_no'] ?? $payload['trade_no'] ?? null;

        if (!$outTradeNo) {
            Log::warning('Telebirr webhook missing out_trade_no');
            return response()->json(['code' => 'INVALID_PAYLOAD', 'msg' => 'Missing out_trade_no'], 400);
        }

        // Idempotency / Replay protection: if payment already completed, return OK immediately
        $existing = Payment::where(function ($q) use ($outTradeNo) {
            $q->where('transaction_reference', $outTradeNo)
              ->orWhere('id', $outTradeNo)
              ->orWhere('metadata->gateway_order_ref', $outTradeNo);
        })->first();

        if ($existing && $existing->status === 'completed' && in_array($tradeStatus, ['SUCCESS', 'TRADE_SUCCESS', 'completed', 'paid'])) {
            Log::info('Telebirr webhook duplicate ignored', ['out_trade_no' => $outTradeNo]);
            return response()->json(['code' => 'SUCCESS', 'msg' => 'Already processed']);
        }

        try {
            match ($tradeStatus) {
                'SUCCESS', 'TRADE_SUCCESS', 'completed', 'paid' => $this->handleSuccess($outTradeNo, $payload),
                'FAILED', 'TRADE_FAILED', 'failed', 'expired' => $this->handleFailure($outTradeNo, $payload),
                'REFUND_SUCCESS', 'refunded' => $this->handleRefund($outTradeNo, $payload),
                default => $this->handleUnknown($outTradeNo, $tradeStatus, $payload),
            };
        } catch (\Exception $e) {
            Log::error('Telebirr webhook processing failed', [
                'error' => $e->getMessage(),
                'out_trade_no' => $outTradeNo,
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['code' => 'PROCESSING_ERROR', 'msg' => 'Internal error'], 500);
        }

        // Telebirr expects a specific response format
        return response()->json(['code' => 'SUCCESS', 'msg' => 'OK']);
    }

    protected function handleSuccess(string $outTradeNo, array $payload): void
    {
        $this->paymentService->handleSuccess($outTradeNo, [
            'gateway_payload' => $payload,
            'verified_at' => now()->toISOString(),
        ]);

        Log::info('Payment completed via webhook', ['out_trade_no' => $outTradeNo]);
    }

    protected function handleFailure(string $outTradeNo, array $payload): void
    {
        $reason = $payload['fail_reason'] ?? $payload['message'] ?? 'Payment failed';
        $this->paymentService->handleFailure($outTradeNo, $reason, [
            'gateway_payload' => $payload,
            'failed_at' => now()->toISOString(),
        ]);

        Log::info('Payment failed via webhook', ['out_trade_no' => $outTradeNo, 'reason' => $reason]);
    }

    protected function handleRefund(string $outTradeNo, array $payload): void
    {
        // Find original payment
        $payment = Payment::where('transaction_reference', $outTradeNo)
            ->orWhere('metadata->gateway_order_ref', $outTradeNo)
            ->first();

        if (!$payment) {
            Log::warning('Refund webhook for unknown payment', ['out_trade_no' => $outTradeNo]);
            return;
        }

        $refundAmount = (float) ($payload['refund_amount'] ?? $payload['amount'] ?? 0);

        $this->paymentService->refund($payment, $refundAmount, 'Refund via webhook');

        Log::info('Payment refunded via webhook', ['out_trade_no' => $outTradeNo, 'amount' => $refundAmount]);
    }

    protected function handleUnknown(string $outTradeNo, string $status, array $payload): void
    {
        Log::warning('Unknown payment status', [
            'out_trade_no' => $outTradeNo,
            'status' => $status,
        ]);
    }
}