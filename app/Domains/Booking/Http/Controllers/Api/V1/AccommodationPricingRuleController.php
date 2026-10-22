<?php

namespace App\Domains\Booking\Http\Controllers\Api\V1;

use App\Domains\Booking\Http\Controllers\Controller;
use App\Http\Requests\Accommodation\IndexAccommodationPricingRuleRequest;
use App\Http\Requests\Accommodation\StoreAccommodationPricingRuleRequest;
use App\Models\Accommodation;
use App\Models\AccommodationPricingRule;

class AccommodationPricingRuleController extends Controller
{
    public function index(IndexAccommodationPricingRuleRequest $request)
    {
        $validated = $request->validated();

        $rules = AccommodationPricingRule::where('accommodation_id', $validated['accommodation_id'])
            ->orderByDesc('priority')
            ->get();

        return response()->json(['data' => $rules]);
    }

    public function store(StoreAccommodationPricingRuleRequest $request)
    {
        $validated = $request->validated();
        $accommodation = Accommodation::findOrFail($validated['accommodation_id']);

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
