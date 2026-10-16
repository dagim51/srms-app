<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\AccommodationPricingRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccommodationPricingWebController extends Controller
{
    protected function getActiveUser()
    {
        return Auth::user();
    }

    public function edit(Accommodation $accommodation)
    {
        $user = $this->getActiveUser();
        
        if ($accommodation->host_id !== $user->id) {
            abort(403);
        }

        $accommodation->load('property');
        $rules = AccommodationPricingRule::where('accommodation_id', $accommodation->id)
            ->orderBy('priority', 'desc')
            ->get();

        return view('dashboard.accommodations.pricing', compact('accommodation', 'user', 'rules'));
    }

    public function update(Request $request, Accommodation $accommodation)
    {
        $user = $this->getActiveUser();
        
        if ($accommodation->host_id !== $user->id) {
            abort(403);
        }

        $request->validate([
            'minimum_stay' => 'required|integer|min:1',
            'maximum_stay' => 'required|integer|gte:minimum_stay',
            'cancellation_policy' => 'required|in:flexible,moderate,strict',
        ]);

        $accommodation->update([
            'minimum_stay' => $request->minimum_stay,
            'maximum_stay' => $request->maximum_stay,
            'cancellation_policy' => $request->cancellation_policy,
        ]);

        // Note: The base price itself belongs to the Property ($accommodation->property->price)
        // Here we can update the property price if the form submits it
        if ($request->has('base_price')) {
            $request->validate([
                'base_price' => 'required|numeric|min:0'
            ]);
            $accommodation->property->update([
                'price' => $request->base_price
            ]);
        }

        return back()->with('success', 'Pricing and policies updated successfully.');
    }

    public function storeRule(Request $request, Accommodation $accommodation)
    {
        $user = $this->getActiveUser();
        
        if ($accommodation->host_id !== $user->id) {
            abort(403);
        }

        $request->validate([
            'type' => 'required|in:seasonal,weekend,special',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'price_modifier_type' => 'required|in:fixed,percentage',
            'price_modifier_value' => 'required|numeric',
        ]);

        AccommodationPricingRule::create([
            'accommodation_id' => $accommodation->id,
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'price_modifier_type' => $request->price_modifier_type,
            'price_modifier_value' => $request->price_modifier_value,
            'priority' => $request->type === 'special' ? 10 : ($request->type === 'seasonal' ? 5 : 1),
        ]);

        return back()->with('success', 'Pricing rule added successfully.');
    }

    public function destroyRule(Accommodation $accommodation, AccommodationPricingRule $rule)
    {
        $user = $this->getActiveUser();
        
        if ($accommodation->host_id !== $user->id || $rule->accommodation_id !== $accommodation->id) {
            abort(403);
        }

        $rule->delete();

        return back()->with('success', 'Pricing rule deleted.');
    }
}
