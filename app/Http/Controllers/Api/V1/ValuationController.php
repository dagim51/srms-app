<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyValuation;
use App\Services\Valuation\ValuationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ValuationController extends Controller
{
    public function __construct(
        protected ValuationService $service
    ) {}

    /**
     * Calculate comprehensive property valuation
     */
    public function calculate(Request $request, Property $property)
    {
        // Authorization: owner or agent can request valuation
        Gate::authorize('view', $property);

        $inputs = $request->only([
            'purchase_price',
            'monthly_rent',
            'operating_expenses_yearly',
            'down_payment',
        ]);

        $valuation = $this->service->calculate($property, $inputs);

        // Save valuation record
        $record = PropertyValuation::create([
            'property_id' => $property->id,
            'user_id' => $request->user()->id ?? null,
            'estimated_value' => $valuation['ensemble_value'],
            'confidence_score' => $valuation['confidence'],
            'methodology' => 'ensemble',
            'comparables_used' => $valuation['comparables_used'],
            'inputs' => $inputs,
            'details' => $valuation,
            'is_authoritative' => false,
        ]);

        return response()->json([
            'data' => array_merge($valuation, ['id' => $record->id]),
        ], 201);
    }

    /**
     * Get market trends for an area
     */
    public function marketTrends(Request $request)
    {
        $validated = $request->validate([
            'city' => 'required|string',
            'subcity' => 'nullable|string',
        ]);

        $trends = $this->service->getMarketTrends($validated['city'], $validated['subcity'] ?? null);

        return response()->json([
            'data' => $trends,
        ]);
    }

    /**
     * Get valuation history for a property
     */
    public function history(Property $property)
    {
        Gate::authorize('view', $property);

        $valuations = $property->valuations()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $valuations,
        ]);
    }

    /**
     * Quick estimate (lightweight)
     */
    public function estimate(Request $request)
    {
        $validated = $request->validate([
            'city' => 'required|string',
            'property_type' => 'required|string',
            'bedrooms' => 'required|integer|min:0|max:10',
            'bathrooms' => 'required|numeric|min:0|max:10',
            'building_area' => 'required|numeric|min:10|max:10000',
            'condition' => 'nullable|string|in:excellent,good,fair,poor',
        ]);

        // Create a temporary property object for hedonic calculation
        $tempProperty = new Property($validated);

        $hedonic = $this->service->calculateHedonic($tempProperty);

        return response()->json([
            'data' => [
                'estimated_value' => $hedonic['value'],
                'confidence' => $hedonic['confidence'],
                'method' => 'hedonic_regression',
                'details' => $hedonic['details'],
            ],
        ]);
    }
}
