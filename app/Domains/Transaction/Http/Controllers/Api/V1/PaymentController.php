<?php

namespace App\Domains\Transaction\Http\Controllers\Api\V1;

use App\Domains\Transaction\Http\Controllers\Controller;
use App\Domains\Transaction\Models\Payment;
use App\Http\Requests\Payment\RefundRequest;
use App\Http\Requests\Payment\StorePaymentRequest;
use App\Http\Requests\Payment\UpdatePaymentRequest;
use App\Http\Requests\Payment\VerifyPaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Services\Payments\PaymentService;
use Illuminate\Support\Facades\Gate;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index()
    {
        $payments = Payment::with('payable')
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(15);

        return PaymentResource::collection($payments);
    }

    public function store(StorePaymentRequest $request)
    {
        $validated = $request->validated();

        // Find payable
        if (! class_exists($validated['payable_type'])) {
            return response()->json(['message' => 'Invalid payable type.'], 400);
        }

        $payable = $validated['payable_type']::findOrFail($validated['payable_id']);

        if ($payable->user_id !== auth()->id()) {
            abort(403, 'Unauthorized.');
        }

        try {
            $result = $this->paymentService->initiate([
                'payable_type' => $validated['payable_type'],
                'payable_id' => $validated['payable_id'],
                'user_id' => auth()->id(),
                'payment_method' => $validated['payment_method'],
            ]);

            return response()->json([
                'data' => (new PaymentResource($result['payment']))->resolve(),
                'gateway' => $result['gateway_data'],
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(Payment $payment)
    {
        Gate::authorize('view', $payment);

        return new PaymentResource($payment);
    }

    public function update(UpdatePaymentRequest $request, Payment $payment)
    {
        Gate::authorize('update', $payment);

        $validated = $request->validated();

        if ($validated['status'] === 'completed' && $payment->status !== 'completed') {
            $payment = $this->paymentService->handleSuccess(
                $validated['transaction_reference'] ?? $payment->transaction_reference,
                $validated['metadata'] ?? []
            );
        } elseif ($validated['status'] === 'failed') {
            $payment = $this->paymentService->handleFailure(
                $validated['transaction_reference'] ?? $payment->transaction_reference,
                $request->input('failure_reason', 'Payment failed'),
                $validated['metadata'] ?? []
            );
        } else {
            $payment->update([
                'status' => $validated['status'],
                'transaction_reference' => $validated['transaction_reference'] ?? $payment->transaction_reference,
                'metadata' => $validated['metadata'] ?? $payment->metadata,
            ]);
        }

        return new PaymentResource($payment);
    }

    public function verify(VerifyPaymentRequest $request, Payment $payment)
    {
        Gate::authorize('view', $payment);

        try {
            $result = $this->paymentService->verifyStatus($payment);

            return response()->json(['data' => $result]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function refund(RefundRequest $request, Payment $payment)
    {
        Gate::authorize('update', $payment);

        if ($payment->status !== 'completed') {
            return response()->json(['message' => 'Can only refund completed payments'], 422);
        }

        $validated = $request->validated();

        try {
            $result = $this->paymentService->refund($payment, $validated['amount'], $validated['reason']);

            return response()->json(['data' => $result]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
