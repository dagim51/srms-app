<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payment::with('payable')
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(15);
            
        return response()->json(['data' => $payments]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'payable_type' => 'required|string', // e.g. App\Models\AccommodationBooking
            'payable_id' => 'required|uuid',
            'payment_method' => 'required|string|in:chapa,telebirr,card,bank_transfer',
        ]);
        
        // Find payable
        if (!class_exists($validated['payable_type'])) {
            return response()->json(['message' => 'Invalid payable type.'], 400);
        }
        
        $payable = $validated['payable_type']::findOrFail($validated['payable_id']);
        
        if ($payable->user_id !== auth()->id()) {
            abort(403, 'Unauthorized.');
        }
        
        // Ensure payable has a price/amount property
        $amount = 0;
        if (isset($payable->total_price)) {
            $amount = $payable->total_price;
        } elseif (isset($payable->price)) {
            $amount = $payable->price;
        } else {
            return response()->json(['message' => 'Payable entity does not have a defined amount.'], 400);
        }
        
        // Check if there is an existing successful payment
        $existingPayment = Payment::where('payable_type', $validated['payable_type'])
            ->where('payable_id', $validated['payable_id'])
            ->where('status', 'completed')
            ->exists();
            
        if ($existingPayment) {
            return response()->json(['message' => 'This item has already been paid for.'], 409);
        }
        
        $payment = Payment::create([
            'user_id' => auth()->id(),
            'payable_type' => $validated['payable_type'],
            'payable_id' => $validated['payable_id'],
            'amount' => $amount,
            'currency' => 'ETB',
            'payment_method' => $validated['payment_method'],
            'status' => 'pending',
        ]);
        
        // Normally here we would initialize the payment with a gateway like Chapa or Telebirr
        // For the scope of this implementation, we just return the pending payment wrapper.
        
        return response()->json(['data' => $payment], 201);
    }

    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:completed,failed,refunded',
            'transaction_reference' => 'nullable|string',
            'metadata' => 'nullable|array',
        ]);
        
        // In a real app, this endpoint is often a Webhook from Chapa/Telebirr,
        // protected by IP whitelisting or signature verification instead of typical auth.
        // For this test scope, we simulate it via normal auth logic.
        
        $payment->update([
            'status' => $validated['status'],
            'transaction_reference' => $validated['transaction_reference'] ?? $payment->transaction_reference,
            'metadata' => $validated['metadata'] ?? $payment->metadata,
        ]);
        
        // Cascade the success to the parent entity
        if ($validated['status'] === 'completed' && $payment->payable) {
            if (method_exists($payment->payable, 'update')) {
                // If the entity supports a status column, mark it as paid or confirmed
                if (in_array('status', $payment->payable->getFillable())) {
                    $payment->payable->update(['status' => 'confirmed']); // Example flow
                }
            }
        }
        
        return response()->json(['data' => $payment]);
    }
}
