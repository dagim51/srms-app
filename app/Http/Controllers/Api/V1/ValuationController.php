<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Property;
use App\Models\PropertyValuation;

class ValuationController extends Controller
{
    public function calculate(Request $request, Property $property)
    {
        $validated = $request->validate([
            'purchase_price' => 'required|numeric|min:0',
            'monthly_rent' => 'required|numeric|min:0',
            'operating_expenses_yearly' => 'required|numeric|min:0',
            'down_payment' => 'nullable|numeric|min:0',
        ]);

        $purchasePrice = $validated['purchase_price'];
        $monthlyRent = $validated['monthly_rent'];
        $annualRent = $monthlyRent * 12;
        $operatingExpenses = $validated['operating_expenses_yearly'];
        $downPayment = $validated['down_payment'] ?? $purchasePrice; // Assume full cash if no down payment given

        // Net Operating Income
        $noi = $annualRent - $operatingExpenses;
        
        // Cap Rate = NOI / Purchase Price
        $capRate = $purchasePrice > 0 ? ($noi / $purchasePrice) : 0;

        // Cash on Cash Return = Pre-Tax Cash Flow / Total Cash Invested (down payment)
        // Ignoring debt service for simplified calculation, so cash flow = NOI
        $cashOnCash = $downPayment > 0 ? ($noi / $downPayment) : 0;

        // Save valuation
        $valuation = PropertyValuation::create([
            'property_id' => $property->id,
            'user_id' => $request->user()->id ?? null,
            'estimated_value' => $purchasePrice, // Using purchase price as estimate base
            'projected_annual_rent' => $annualRent,
            'cap_rate' => $capRate,
            'cash_on_cash_return' => $cashOnCash,
            'inputs' => $validated,
            'is_authoritative' => false,
        ]);

        return response()->json(['data' => $valuation], 201);
    }
    
    public function index(Property $property)
    {
        return response()->json([
            'data' => $property->valuations()->orderBy('created_at', 'desc')->get()
        ]);
    }
}
