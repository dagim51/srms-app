<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AccommodationPricingRule;
use Illuminate\Http\Request;

class AccommodationPricingRuleController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'accommodation_id' => 'required|exists:accommodations,id',
        ]);
        
        $rules = AccommodationPricingRule::where('accommodation_id', $validated['accommodation_id'])
            ->orderByDesc('priority')
            ->get();
            
        return response()->json(['data' => $rules]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'accommodation_id' => 'required|exists:accommodations,id',
            'type' => 'required|string|in:seasonal,weekend,special',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'price_modifier_type' => 'required|string|in:fixed,percentage',
            'price_modifier_value' => 'required|numeric',
            'priority' => 'nullable|integer',
        ]);
        
        $accommodation = \App\Models\Accommodation::findOrFail($validated['accommodation_id']);
        
        if ($accommodation->host_id !== auth()->id()) {
            abort(403, 'Unauthorized.');
        }
        
        $rule = AccommodationPricingRule::create([
            'accommodation_id' => $accommodation->id,
            'type' => $validated['type'],
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'price_modifier_type' => $validated['price_modifier_type'],
            'price_modifier_value' => $validated['price_modifier_value'],
            'priority' => $validated['priority'] ?? 0,
        ]);
        
        return response()->json(['data' => $rule], 201);
    }

    public function destroy(AccommodationPricingRule $accommodationPricingRule)
    {
        $accommodation = $accommodationPricingRule->accommodation;
        
        if ($accommodation->host_id !== auth()->id()) {
            abort(403, 'Unauthorized.');
        }
        
        $accommodationPricingRule->delete();
        
        return response()->noContent();
    }
}
