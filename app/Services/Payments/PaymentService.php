<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Payment;
use App\Models\LedgerAccount;
use App\Models\LedgerEntry;
use App\Models\LedgerEntryLine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class PaymentService
{
    protected PaymentGatewayInterface $gateway;

    public function __construct(PaymentGatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * Initiate a payment for a payable entity.
     */
    public function initiate(array $data): array
    {
        $payableType = $data['payable_type'];
        $payableId = $data['payable_id'];
        $userId = $data['user_id'];
        $paymentMethod = $data['payment_method'] ?? 'telebirr';

        // Validate payable exists and belongs to user
        if (!class_exists($payableType)) {
            throw new \InvalidArgumentException('Invalid payable type');
        }

        $payable = $payableType::findOrFail($payableId);

        if ($payable->user_id !== $userId) {
            throw new \RuntimeException('Unauthorized');
        }

        // Determine amount
        $amount = $this->getPayableAmount($payable);

        // Check for existing completed payment
        $existing = Payment::where('payable_type', $payableType)
            ->where('payable_id', $payableId)
            ->where('status', 'completed')
            ->exists();

        if ($existing) {
            throw new \RuntimeException('Already paid');
        }

        // Generate idempotency key
        $idempotencyKey = $this->generateIdempotencyKey($payableType, $payableId, $userId);

        // Check for pending payment with same idempotency key
        $pendingPayment = Payment::where('idempotency_key', $idempotencyKey)
            ->where('status', 'pending')
            ->first();

        if ($pendingPayment) {
            return [
                'payment' => $pendingPayment,
                'gateway_data' => $this->getGatewayPaymentData($pendingPayment),
            ];
        }

        // Create payment record
        $payment = DB::transaction(function () use ($payableType, $payableId, $userId, $amount, $paymentMethod, $idempotencyKey) {
            return Payment::create([
                'user_id' => $userId,
                'payable_type' => $payableType,
                'payable_id' => $payableId,
                'amount' => $amount,
                'currency' => 'ETB',
                'payment_method' => $paymentMethod,
                'status' => 'pending',
                'idempotency_key' => $idempotencyKey,
                'metadata' => [
                    'gateway' => 'telebirr',
                ],
            ]);
        });

        // Initialize with gateway
        $gatewayData = $this->initializeGatewayPayment($payment, $payable);

        // Update payment with gateway response
        $payment->update([
            'metadata' => array_merge($payment->metadata ?? [], [
                'gateway' => 'telebirr',
                'gateway_response' => $gatewayData,
                'gateway_order_ref' => $gatewayData['out_trade_no'] ?? null,
            ]),
        ]);

        $processedGatewayData = $this->getGatewayPaymentData($payment);

        return [
            'payment' => $payment,
            'gateway_data' => $processedGatewayData,
        ];
    }

    /**
     * Handle successful payment callback/webhook.
     */
    public function handleSuccess(string $transactionRef, array $metadata = []): Payment
    {
        return DB::transaction(function () use ($transactionRef, $metadata) {
            $isUuid = Str::isUuid($transactionRef);

            $payment = Payment::where(function ($query) use ($transactionRef, $metadata, $isUuid) {
                $query->where('transaction_reference', $transactionRef)
                    ->orWhere('metadata->gateway_order_ref', $transactionRef);

                if ($isUuid) {
                    $query->orWhere('id', $transactionRef)
                        ->orWhere(function ($q) use ($transactionRef, $metadata) {
                            $q->where('payable_id', $transactionRef)
                              ->where('payable_type', $metadata['payable_type'] ?? 'App\Models\AccommodationBooking');
                        });
                }
            })->firstOrFail();

            if ($payment->status === 'completed') {
                Log::info('Payment already completed', ['payment_id' => $payment->id]);
                return $payment;
            }

            $payment->update([
                'status' => 'completed',
                'transaction_reference' => $transactionRef,
                'metadata' => array_merge($payment->metadata ?? [], $metadata, [
                    'completed_at' => now()->toISOString(),
                ]),
            ]);

            // Update payable entity and record balanced double-entry
            $this->onPaymentCompleted($payment);

            // Fire domain event
            event(new \App\Events\Payments\PaymentCompleted($payment));

            return $payment;
        });
    }

    /**
     * Handle failed payment.
     */
    public function handleFailure(string $transactionRef, string $reason = 'Payment failed', array $metadata = []): Payment
    {
        return DB::transaction(function () use ($transactionRef, $reason, $metadata) {
            $payment = Payment::where('transaction_reference', $transactionRef)
                ->orWhere('metadata->gateway_order_ref', $transactionRef)
                ->firstOrFail();

            $payment->update([
                'status' => 'failed',
                'transaction_reference' => $transactionRef,
                'metadata' => array_merge($payment->metadata ?? [], $metadata, [
                    'failure_reason' => $reason,
                    'failed_at' => now()->toISOString(),
                ]),
            ]);

            // Fire domain event
            event(new \App\Events\Payments\PaymentFailed($payment));

            return $payment;
        });
    }

    /**
     * Verify payment status with gateway.
     */
    public function verifyStatus(Payment $payment): array
    {
        $orderRef = $payment->metadata['gateway_order_ref'] ?? $payment->transaction_reference;

        if (!$orderRef) {
            throw new \RuntimeException('No gateway reference found');
        }

        return $this->gateway->verify($orderRef);
    }

    /**
     * Process refund.
     */
    public function refund(Payment $payment, float $amount, string $reason): array
    {
        if ($payment->status !== 'completed') {
            throw new \RuntimeException('Can only refund completed payments');
        }

        if ($amount > $payment->amount) {
            throw new \RuntimeException('Refund amount exceeds payment amount');
        }

        $orderRef = $payment->transaction_reference;

        $result = $this->gateway->refund($orderRef, $amount, $reason);

        // Record refund
        $refund = Payment::create([
            'user_id' => $payment->user_id,
            'payable_type' => $payment->payable_type,
            'payable_id' => $payment->payable_id,
            'amount' => -$amount,
            'currency' => $payment->currency,
            'payment_method' => $payment->payment_method,
            'status' => 'completed',
            'transaction_reference' => $result['refund_id'] ?? Str::uuid(),
            'metadata' => [
                'type' => 'refund',
                'original_payment_id' => $payment->id,
                'reason' => $reason,
                'gateway_response' => $result,
            ],
        ]);

        event(new \App\Events\Payments\PaymentRefunded($refund, $payment));

        $this->recordLedgerRefundEntry($refund, $payment, $amount);

        return $result;
    }

    /**
     * Get amount from payable entity.
     */
    protected function getPayableAmount($payable): float
    {
        if (isset($payable->total_price)) {
            return (float) $payable->total_price;
        }

        if (isset($payable->price)) {
            return (float) $payable->price;
        }

        if (isset($payable->amount)) {
            return (float) $payable->amount;
        }

        throw new \RuntimeException('Payable entity does not have a defined amount');
    }

    /**
     * Generate idempotency key.
     */
    protected function generateIdempotencyKey(string $payableType, string $payableId, string $userId): string
    {
        $prefix = config('payments.idempotency.prefix', 'payment:idempotency:');
        $key = $prefix . hash('sha256', "{$payableType}:{$payableId}:{$userId}");

        // Store in cache with TTL
        $ttl = config('payments.idempotency.ttl', 3600);
        Cache::put($key, true, $ttl);

        return $key;
    }

    /**
     * Initialize gateway payment and return gateway response.
     */
    protected function initializeGatewayPayment(Payment $payment, $payable): array
    {
        $orderRef = $payment->metadata['gateway_order_ref'] ?? TelebirrGateway::generateOrderRef('SRMS');

        // Update payment with order ref
        $payment->update([
            'metadata' => array_merge($payment->metadata ?? [], [
                'gateway_order_ref' => $orderRef,
            ]),
        ]);

        // In test environment, return mock data if gateway not configured
        $isTesting = app()->environment('testing');
        $appId = config('payments.telebirr.app_id');
        \Log::debug('Telebirr gateway check', [
            'is_testing' => $isTesting,
            'app_id' => $appId,
            'app_id_empty' => empty($appId),
        ]);
        
        if ($isTesting && empty($appId)) {
            \Log::debug('Using mock Telebirr gateway');
            return [
                'code' => 'SUCCESS',
                'payment_url' => 'https://test.telebirr.com/pay/' . $orderRef,
                'qr_code' => 'data:image/png;base64,mock',
                'out_trade_no' => $orderRef,
            ];
        }

        $gatewayData = $this->gateway->initialize([
            'out_trade_no' => $orderRef,
            'amount' => $payment->amount,
            'subject' => $this->getSubject($payable),
            'body' => $this->getBody($payable),
            'idempotency_key' => $payment->idempotency_key,
        ]);

        return $gatewayData;
    }

    /**
     * Get gateway payment data for frontend.
     */
    protected function getGatewayPaymentData(Payment $payment): array
    {
        $orderRef = $payment->metadata['gateway_order_ref'] ?? $payment->transaction_reference;

        return [
            'payment_url' => $this->gateway->getPaymentUrl([
                'out_trade_no' => $orderRef,
                'amount' => $payment->amount,
                'subject' => 'Payment for ' . class_basename($payment->payable_type) . ' ' . $payment->payable_id,
            ]),
            'qr_code' => $payment->metadata['gateway_response']['qr_code'] ?? null,
            'order_ref' => $orderRef,
        ];
    }

    /**
     * Update payable entity after successful payment.
     */
    protected function onPaymentCompleted(Payment $payment): void
    {
        // 1. Record balanced double-entry ledger lines for settlement
        $this->recordLedgerJournalEntry($payment);

        if (!$payment->payable) {
            return;
        }

        $payable = $payment->payable;

        // Update status if entity supports it
        if (method_exists($payable, 'update') && in_array('status', $payable->getFillable())) {
            $newStatus = $this->determineNewStatus($payable);
            if ($newStatus) {
                $payable->update(['status' => $newStatus]);
            }
        }

        // Trigger payable-specific logic
        if (method_exists($payable, 'onPaymentCompleted')) {
            $payable->onPaymentCompleted($payment);
        }
    }

    /**
     * Record balanced double-entry ledger lines for completed payment settlement.
     */
    protected function recordLedgerJournalEntry(Payment $payment): void
    {
        // Prevent duplicate journal entries
        $existingEntry = LedgerEntry::where('reference_id', $payment->id)
            ->orWhere('reference_id', $payment->transaction_reference)
            ->first();

        if ($existingEntry) {
            return;
        }

        // 1. Asset Account (Debit: Cash / Gateway Clearing)
        $assetCode = match ($payment->payment_method) {
            'telebirr' => '1020',
            default => '1010',
        };
        $assetName = ($assetCode === '1020')
            ? 'Cash - Telebirr Merchant Account'
            : 'Cash - Commercial Bank of Ethiopia';

        $assetAccount = LedgerAccount::firstOrCreate(
            ['code' => $assetCode],
            ['name' => $assetName, 'type' => 'Asset']
        );

        // 2. Revenue Account (Credit: Operating Revenue)
        $payableClass = class_basename($payment->payable_type ?? '');
        $revenueCode = match ($payableClass) {
            'AccommodationBooking' => '4030',
            'Lease', 'RentSchedule' => '4010',
            'DevelopmentUnit', 'PaymentPlanInstallment' => '4020',
            default => '4000',
        };
        $revenueName = match ($revenueCode) {
            '4030' => 'Short-Stay Accommodation Revenue',
            '4010' => 'Rental Lease Revenue',
            '4020' => 'Development Sales Commission',
            default => 'General Operating Revenue',
        };

        $revenueAccount = LedgerAccount::firstOrCreate(
            ['code' => $revenueCode],
            ['name' => $revenueName, 'type' => 'Revenue']
        );

        // 3. Create Ledger Entry
        $entry = LedgerEntry::create([
            'description' => "Automated settlement for Payment #{$payment->id} ({$payableClass} #{$payment->payable_id})",
            'reference_id' => $payment->id,
            'entry_date' => now()->toDateString(),
        ]);

        $amount = abs((float) $payment->amount);

        // 4. Debit Asset Account (Cash increases)
        LedgerEntryLine::create([
            'ledger_entry_id' => $entry->id,
            'ledger_account_id' => $assetAccount->id,
            'type' => 'debit',
            'amount' => $amount,
        ]);

        // 5. Credit Revenue Account (Revenue increases)
        LedgerEntryLine::create([
            'ledger_entry_id' => $entry->id,
            'ledger_account_id' => $revenueAccount->id,
            'type' => 'credit',
            'amount' => $amount,
        ]);
    }

    /**
     * Record balanced double-entry ledger lines for payment refund.
     */
    protected function recordLedgerRefundEntry(Payment $refund, Payment $payment, float $amount): void
    {
        $existingEntry = LedgerEntry::where('reference_id', $refund->id)
            ->orWhere('reference_id', $refund->transaction_reference)
            ->first();

        if ($existingEntry) {
            return;
        }

        $liabilityAccount = LedgerAccount::firstOrCreate(
            ['code' => '2050'],
            ['name' => 'Refunds & Payouts Payable', 'type' => 'Liability']
        );

        $assetCode = match ($payment->payment_method) {
            'telebirr' => '1020',
            default => '1010',
        };
        $assetName = ($assetCode === '1020')
            ? 'Cash - Telebirr Merchant Account'
            : 'Cash - Commercial Bank of Ethiopia';

        $assetAccount = LedgerAccount::firstOrCreate(
            ['code' => $assetCode],
            ['name' => $assetName, 'type' => 'Asset']
        );

        $entry = LedgerEntry::create([
            'description' => "Automated reversal for Refund #{$refund->id} (Original Payment #{$payment->id})",
            'reference_id' => $refund->id,
            'entry_date' => now()->toDateString(),
        ]);

        // Debit Liability
        LedgerEntryLine::create([
            'ledger_entry_id' => $entry->id,
            'ledger_account_id' => $liabilityAccount->id,
            'type' => 'debit',
            'amount' => $amount,
        ]);

        // Credit Asset (Cash decreases)
        LedgerEntryLine::create([
            'ledger_entry_id' => $entry->id,
            'ledger_account_id' => $assetAccount->id,
            'type' => 'credit',
            'amount' => $amount,
        ]);
    }

    /**
     * Determine new status based on payable type.
     */
    protected function determineNewStatus($payable): ?string
    {
        return match (class_basename($payable)) {
            'AccommodationBooking' => 'confirmed',
            'Listing' => 'published',
            default => 'paid',
        };
    }

    /**
     * Get subject for gateway.
     */
    protected function getSubject($payable): string
    {
        $type = class_basename($payable);
        $id = $payable->getKey();

        return match ($type) {
            'AccommodationBooking' => "Short-stay booking #{$id}",
            'Listing' => "Listing publication #{$id}",
            default => "Payment for {$type} #{$id}",
        };
    }

    /**
     * Get body for gateway.
     */
    protected function getBody($payable): string
    {
        return $this->getSubject($payable);
    }
}