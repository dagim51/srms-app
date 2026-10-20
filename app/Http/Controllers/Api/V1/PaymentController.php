<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Payments\PaymentService;
use App\Http\Resources\PaymentResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index(Request $request)
    {
        $payments = Payment::with('payable')
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(15);

        return PaymentResource::collection($payments);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'payable_type' => 'required|string', // e.g. App\Models\AccommodationBooking
            'payable_id' => 'required|uuid',
            'payment_method' => 'required|string|in:telebirr,chapa,card,bank_transfer',
        ]);

        // Find payable
        if (!class_exists($validated['payable_type'])) {
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

    public function update(Request $request, Payment $payment)
    {
        // In a real app, this endpoint is often a Webhook from Chapa/Telebirr,
        // protected by IP whitelisting or signature verification instead of typical auth.
        // For this test scope, we simulate it via normal auth logic.
        Gate::authorize('update', $payment);

        $validated = $request->validate([
            'status' => 'required|string|in:completed,failed,refunded',
            'transaction_reference' => 'nullable|string',
            'metadata' => 'nullable|array',
        ]);

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

    public function verify(Request $request, Payment $payment)
    {
        Gate::authorize('view', $payment);

        try {
            $result = $this->paymentService->verifyStatus($payment);
            return response()->json(['data' => $result]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function refund(Request $request, Payment $payment)
    {
        Gate::authorize('update', $payment);

        if ($payment->status !== 'completed') {
            return response()->json(['message' => 'Can only refund completed payments'], 422);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $payment->amount,
            'reason' => 'required|string',
        ]);

        try {
            $result = $this->paymentService->refund($payment, $validated['amount'], $validated['reason']);
            return response()->json(['data' => $result]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}