<?php

namespace App\Domains\Stay\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\Property;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccommodationWebController extends Controller
{
    protected function getActiveUser()
    {
        return Auth::user();
    }

    /**
     * Public listing of accommodations for search
     */
    public function search(Request $request)
    {
        $query = Accommodation::with(['property.media', 'property.category', 'property.type', 'amenities', 'listing'])
            ->where('is_active', true)
            ->whereHas('property', function ($q) {
                $q->where('status', 'verified');
            });

        // Location filter
        if ($request->filled('location')) {
            $location = $request->input('location');
            $query->whereHas('property', function ($q) use ($location) {
                $q->where(function ($q2) use ($location) {
                    $q2->where('title', 'like', "%{$location}%")
                        ->orWhere('address', 'like', "%{$location}%")
                        ->orWhere('neighborhood', 'like', "%{$location}%")
                        ->orWhereHas('neighborhood', function ($q3) use ($location) {
                            $q3->where('name', 'like', "%{$location}%");
                        });
                });
            });
        }

        // Date filters
        $checkIn = $request->input('check_in');
        $checkOut = $request->input('check_out');

        if ($checkIn && $checkOut) {
            $query->whereDoesntHave('availabilities', function ($q) use ($checkIn, $checkOut) {
                $q->where('start_date', '<', $checkOut)
                    ->where('end_date', '>', $checkIn)
                    ->where('type', 'booked');
            })->whereDoesntHave('bookings', function ($q) use ($checkIn, $checkOut) {
                $q->whereIn('status', ['pending', 'confirmed'])
                    ->where('check_in_date', '<', $checkOut)
                    ->where('check_out_date', '>', $checkIn);
            });
        }

        // Guests filter
        if ($request->filled('guests')) {
            $query->where('max_guests', '>=', $request->input('guests'));
        }

        // Price range
        if ($request->filled('min_price') || $request->filled('max_price')) {
            $query->join('listings', 'accommodations.listing_id', '=', 'listings.id')
                ->where('listings.price', '<=', $request->input('max_price'))
                ->distinct();
            if ($request->filled('min_price')) {
                $query->where('listings.price', '>=', $request->input('min_price'));
            }
        }

        // Amenities filter
        if ($request->filled('amenities')) {
            $amenities = $request->input('amenities');
            $query->whereHas('amenities', function ($q) use ($amenities) {
                $q->whereIn('amenities.id', $amenities);
            });
        }

        // Sorting
        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'price_asc':
                $query->join('listings', 'accommodations.listing_id', '=', 'listings.id')
                    ->orderBy('listings.price', 'asc');
                break;
            case 'price_desc':
                $query->join('listings', 'accommodations.listing_id', '=', 'listings.id')
                    ->orderBy('listings.price', 'desc');
                break;
            case 'rating':
                $query->orderByDesc('rating');
                break;
            default:
                $query->latest();
        }

        $accommodations = $query->paginate(12)->withQueryString();

        return view('accommodations.index', compact('accommodations', 'checkIn', 'checkOut'));
    }

    /**
     * Public show page for accommodation
     */
    public function showPublic(Request $request, Accommodation $accommodation)
    {
        $accommodation->load([
            'property.media',
            'property.category',
            'property.type',
            'amenities',
            'features',
            'availabilities',
            'pricingRules',
            'host:id,name,avatar',
            'listing',
        ]);

        // Default dates
        $checkIn = $request->input('check_in', Carbon::now()->addDay()->format('Y-m-d'));
        $checkOut = $request->input('check_out', Carbon::now()->addDays(3)->format('Y-m-d'));
        $guests = $request->input('guests', 1);

        $nights = Carbon::parse($checkIn)->diffInDays(Carbon::parse($checkOut));
        $basePrice = $accommodation->listing->price * $nights;

        return view('accommodations.show', compact(
            'accommodation',
            'checkIn',
            'checkOut',
            'guests',
            'nights',
            'basePrice'
        ));
    }

    /**
     * Host dashboard - list own accommodations
     */
    public function index(Request $request)
    {
        $user = $this->getActiveUser();

        $query = Accommodation::with(['property.media', 'property.category', 'property.type'])
            ->where('host_id', $user->id);

        if ($search = $request->input('search')) {
            $query->whereHas('property', function ($pq) use ($search) {
                $pq->where('title', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('property_code', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $accommodations = $query->latest()->paginate(9)->withQueryString();

        return view('dashboard.accommodations.index', compact('accommodations', 'user'));
    }

    public function create()
    {
        $user = $this->getActiveUser();

        $properties = Property::where('owner_user_id', $user->id)
            ->where('status', 'verified')
            ->get();

        return view('dashboard.accommodations.create', compact('properties', 'user'));
    }

    public function store(Request $request)
    {
        $user = $this->getActiveUser();

        $request->validate([
            'property_id' => 'required|exists:properties,id|unique:accommodations,property_id',
            'max_guests' => 'required|integer|min:1',
            'bedrooms' => 'required|integer|min:0',
            'beds' => 'required|integer|min:1',
            'bathrooms' => 'required|numeric|min:0',
            'check_in_time' => 'required',
            'check_out_time' => 'required',
            'rules' => 'nullable|string',
        ]);

        $accommodation = Accommodation::create([
            'property_id' => $request->property_id,
            'host_id' => $user->id,
            'max_guests' => $request->max_guests,
            'bedrooms' => $request->bedrooms,
            'beds' => $request->beds,
            'bathrooms' => $request->bathrooms,
            'check_in_time' => $request->check_in_time,
            'check_out_time' => $request->check_out_time,
            'rules' => $request->rules,
            'is_active' => true,
        ]);

        return redirect()->route('dashboard.accommodations.show', $accommodation)->with('success', 'Accommodation setup successfully.');
    }

    /**
     * Host dashboard - show own accommodation
     */
    public function show(Accommodation $accommodation)
    {
        $user = $this->getActiveUser();

        if ($accommodation->host_id !== $user->id) {
            abort(403);
        }

        $accommodation->load('property');

        return view('dashboard.accommodations.show', compact('accommodation', 'user'));
    }

    public function edit(Accommodation $accommodation)
    {
        $user = $this->getActiveUser();

        if ($accommodation->host_id !== $user->id) {
            abort(403);
        }

        return view('dashboard.accommodations.edit', compact('accommodation'));
    }

    public function update(Request $request, Accommodation $accommodation)
    {
        $user = $this->getActiveUser();

        if ($accommodation->host_id !== $user->id) {
            abort(403);
        }

        $request->validate([
            'max_guests' => 'required|integer|min:1',
            'bedrooms' => 'required|integer|min:0',
            'beds' => 'required|integer|min:1',
            'bathrooms' => 'required|numeric|min:0',
            'check_in_time' => 'required',
            'check_out_time' => 'required',
            'rules' => 'nullable|string',
        ]);

        $accommodation->update($request->only([
            'max_guests', 'bedrooms', 'beds', 'bathrooms', 'check_in_time', 'check_out_time', 'rules',
        ]));

        return redirect()->route('dashboard.accommodations.show', $accommodation)->with('success', 'Accommodation updated successfully.');
    }
}
