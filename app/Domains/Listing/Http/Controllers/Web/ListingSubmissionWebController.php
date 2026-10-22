<?php

namespace App\Domains\Listing\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ListingSubmissionWebController extends Controller
{
    /**
     * Show the pre-submission automated validation check and submission form.
     */
    public function showSubmitForm(Listing $listing)
    {
        $listing->load(['property.media', 'property.amenities', 'property.features', 'moderations.moderator']);
        $validationChecks = $this->evaluateValidationRules($listing);

        return view('dashboard.listings.submit', compact('listing', 'validationChecks'));
    }

    /**
     * Process listing submission to the moderation queue.
     */
    public function processSubmit(Request $request, Listing $listing)
    {
        $listing->load('property');
        $validationChecks = $this->evaluateValidationRules($listing);

        if (! $validationChecks['all_passed']) {
            return back()->with('error', 'Cannot submit listing: Mandatory validation requirements are missing.');
        }

        // Transition status to pending_review
        $listing->status = 'pending_review';
        $listing->save();

        // Optional submission notes recorded in moderations log
        if ($request->filled('comments')) {
            $listing->moderations()->create([
                'moderator_id' => Auth::id() ?? $listing->owner_actor_id,
                'action' => 'submitted',
                'comments' => $request->input('comments'),
            ]);
        }

        return redirect()->route('dashboard.listings.submission-status', $listing)
            ->with('success', 'Listing submitted successfully to the moderation queue.');
    }

    /**
     * Display current submission status, lifecycle stepper, rejection explanation, and history.
     */
    public function status(Listing $listing)
    {
        $listing->load([
            'property.media',
            'property.amenities',
            'moderations' => fn ($q) => $q->latest(),
        ]);

        $latestModeration = $listing->moderations->first();
        $validationChecks = $this->evaluateValidationRules($listing);

        return view('dashboard.listings.submission-status', compact('listing', 'latestModeration', 'validationChecks'));
    }

    /**
     * Automated pre-submission validation rules
     */
    protected function evaluateValidationRules(Listing $listing): array
    {
        $property = $listing->property;
        $rules = [];

        // 1. Price is set & positive
        $rules[] = [
            'key' => 'price',
            'label' => 'Valid Commercial Price & Currency',
            'required' => true,
            'passed' => ! empty($listing->price) && $listing->price > 0,
            'message' => 'Listing must have an explicit price greater than 0.',
        ];

        // 2. Purpose is defined
        $rules[] = [
            'key' => 'purpose',
            'label' => 'Transaction Purpose Specified',
            'required' => true,
            'passed' => ! empty($listing->purpose),
            'message' => 'Specify transaction type (For Sale, For Rent, or Short Stay).',
        ];

        // 3. Property title & description
        $rules[] = [
            'key' => 'description',
            'label' => 'Comprehensive Title & Description',
            'required' => true,
            'passed' => $property && ! empty($property->title) && ! empty($property->description) && strlen($property->description) >= 30,
            'message' => 'Property description must contain at least 30 characters.',
        ];

        // 4. Photos uploaded
        $mediaCount = $property ? $property->media->count() : 0;
        $rules[] = [
            'key' => 'media',
            'label' => 'Property Photos Uploaded (≥ 1 photo)',
            'required' => true,
            'passed' => $mediaCount >= 1,
            'message' => 'At least 1 photo is required. (3+ photos recommended for high search ranking).',
        ];

        // 5. Geographic location coordinates
        $hasCoords = $property && ! empty($property->latitude) && ! empty($property->longitude);
        $rules[] = [
            'key' => 'gis',
            'label' => 'GIS Map Coordinates Set',
            'required' => false,
            'passed' => $hasCoords,
            'message' => 'Pin the property on the map for accurate neighborhood discovery.',
        ];

        // 6. Property area or bedrooms
        $hasSpecs = $property && ($property->bedrooms > 0 || $property->building_area > 0 || $property->land_area > 0);
        $rules[] = [
            'key' => 'specs',
            'label' => 'Physical Specifications (Area / Bedrooms)',
            'required' => false,
            'passed' => (bool) $hasSpecs,
            'message' => 'Provide bedroom count and area in m².',
        ];

        $requiredRules = array_filter($rules, fn ($r) => $r['required']);
        $allPassed = ! in_array(false, array_column($requiredRules, 'passed'), true);

        return [
            'rules' => $rules,
            'all_passed' => $allPassed,
        ];
    }
}
