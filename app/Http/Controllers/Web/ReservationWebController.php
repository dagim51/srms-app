<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReservationWebController extends Controller
{
    protected function getActiveUser()
    {
        return Auth::user();
    }

    public function index()
    {
        $user = $this->getActiveUser();
        
        $reservations = Reservation::with(['listing.property'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Expire pending reservations that have passed their expiration time
        foreach ($reservations as $res) {
            if ($res->status === 'pending' && $res->expires_at && Carbon::now()->greaterThan($res->expires_at)) {
                $res->update(['status' => 'expired']);
            }
        }

        return view('dashboard.reservations.index', compact('reservations'));
    }

    public function create(Request $request)
    {
        $listingId = $request->query('listing_id');
        $listing = $listingId ? Listing::with('property')->findOrFail($listingId) : null;

        // Check if user already has an active reservation for this
        $user = $this->getActiveUser();
        if ($listing) {
            $existing = Reservation::where('user_id', $user->id)
                ->where('listing_id', $listing->id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->first();
                
            if ($existing) {
                return redirect()->route('dashboard.reservations.show', $existing)
                    ->with('info', 'You already have an active reservation for this property.');
            }
            
            // Check if property is already reserved by someone else
            $conflict = Reservation::where('listing_id', $listing->id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->where(function($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', Carbon::now());
                })
                ->exists();
                
            if ($conflict) {
                return back()->with('error', 'This property is currently reserved by another user.');
            }
        }

        return view('dashboard.reservations.create', compact('listing'));
    }

    public function store(Request $request)
    {
        $user = $this->getActiveUser();

        $request->validate([
            'listing_id' => 'required|exists:listings,id',
            'notes' => 'nullable|string',
        ]);

        $listing = Listing::findOrFail($request->listing_id);

        // Double check conflict
        $conflict = Reservation::where('listing_id', $listing->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where(function($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', Carbon::now());
            })
            ->exists();
            
        if ($conflict) {
            return back()->with('error', 'This property was just reserved by another user.');
        }

        $reservation = Reservation::create([
            'listing_id' => $listing->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'expires_at' => Carbon::now()->addHours(48), // 48 hours to complete payment/confirmation
            'notes' => $request->notes,
        ]);

        return redirect()->route('dashboard.reservations.show', $reservation)->with('success', 'Reservation requested successfully! Complete payment to secure it.');
    }

    public function show(Reservation $reservation)
    {
        $user = $this->getActiveUser();
        
        if ($reservation->user_id !== $user->id) {
            abort(403);
        }

        if ($reservation->status === 'pending' && $reservation->expires_at && Carbon::now()->greaterThan($reservation->expires_at)) {
            $reservation->update(['status' => 'expired']);
        }

        $reservation->load('listing.property');

        return view('dashboard.reservations.show', compact('reservation'));
    }

    public function update(Request $request, Reservation $reservation)
    {
        $user = $this->getActiveUser();
        
        if ($reservation->user_id !== $user->id) {
            abort(403);
        }

        $request->validate([
            'action' => 'required|in:cancel,pay',
        ]);

        if ($request->action === 'cancel') {
            $reservation->update(['status' => 'cancelled']);
            return back()->with('success', 'Reservation cancelled.');
        } 
        
        if ($request->action === 'pay') {
            // Mock payment success
            $reservation->update(['status' => 'confirmed']);
            return back()->with('success', 'Payment successful! Reservation confirmed.');
        }
    }
}
