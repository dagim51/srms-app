<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\AccommodationAvailability;
use App\Models\AccommodationBooking;
use App\Services\Bookings\BookingManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BookingEngineWebController extends Controller
{
    protected BookingManager $bookingManager;

    public function __construct(BookingManager $bookingManager)
    {
        $this->bookingManager = $bookingManager;
    }

    protected function getActiveUser()
    {
        return Auth::user();
    }

    public function create(Request $request, Accommodation $accommodation)
    {
        $accommodation->load(['listing', 'property']);
        
        // Defaults for form if none provided
        $checkIn = $request->input('check_in', Carbon::now()->addDays(1)->format('Y-m-d'));
        $checkOut = $request->input('check_out', Carbon::now()->addDays(3)->format('Y-m-d'));
        $guests = $request->input('guests', 1);

        // Precompute some info for the form
        $nights = Carbon::parse($checkIn)->diffInDays(Carbon::parse($checkOut));
        $basePrice = $accommodation->listing->price ?? 100;
        
        return view('frontend.booking.create', compact('accommodation', 'checkIn', 'checkOut', 'guests', 'nights', 'basePrice'));
    }

    public function store(Request $request, Accommodation $accommodation)
    {
        $user = $this->getActiveUser();

        $request->validate([
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'guests' => 'required|integer|min:1|max:' . $accommodation->max_guests,
        ]);

        $checkIn = Carbon::parse($request->check_in_date);
        $checkOut = Carbon::parse($request->check_out_date);
        $nights = $checkIn->diffInDays($checkOut);

        // Validate Stay length rules
        if ($nights < $accommodation->minimum_stay) {
            return back()->withErrors(['check_in_date' => "Minimum stay is {$accommodation->minimum_stay} nights."]);
        }
        if ($nights > $accommodation->maximum_stay) {
            return back()->withErrors(['check_out_date' => "Maximum stay is {$accommodation->maximum_stay} nights."]);
        }

        // Use BookingManager for creation (includes availability and booking conflict validation)
        try {
            $booking = $this->bookingManager->create([
                'accommodation_id' => $accommodation->id,
                'check_in_date' => $checkIn->format('Y-m-d'),
                'check_out_date' => $checkOut->format('Y-m-d'),
                'guests' => $request->guests,
            ], $user->id);

            return redirect()->route('booking.show', $booking)->with('success', 'Booking reserved! Please complete payment.');
        } catch (\DomainException $e) {
            return back()->withErrors(['dates' => $e->getMessage()]);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'An error occurred while securing your booking. Please try again.']);
        }
    }

    public function show(AccommodationBooking $booking)
    {
        $user = $this->getActiveUser();
        
        if ($booking->user_id !== $user->id) {
            abort(403);
        }

        $booking->load(['accommodation.property']);

        return view('frontend.booking.show', compact('booking'));
    }

    public function confirm(Request $request, AccommodationBooking $booking)
    {
        $user = $this->getActiveUser();
        
        if ($booking->user_id !== $user->id) {
            abort(403);
        }

        // Mock payment processing...
        $booking->update(['status' => 'confirmed']);

        return redirect()->route('booking.show', $booking)->with('success', 'Payment successful! Your booking is confirmed.');
    }

    public function cancel(Request $request, AccommodationBooking $booking)
    {
        $user = $this->getActiveUser();
        
        if ($booking->user_id !== $user->id) {
            abort(403);
        }

        // Cancel booking
        $booking->update(['status' => 'cancelled']);

        // Free up availability calendar
        AccommodationAvailability::where('reason', 'Booking ID: ' . $booking->id)->delete();

        return redirect()->route('booking.show', $booking)->with('success', 'Booking cancelled successfully.');
    }

    public function checkIn(Request $request, AccommodationBooking $booking)
    {
        $user = $this->getActiveUser();
        
        // Host or Guest could initiate check-in depending on business logic. Here we assume Guest initiates.
        if ($booking->user_id !== $user->id && $booking->accommodation->host_id !== $user->id) {
            abort(403);
        }

        if ($booking->status !== 'confirmed') {
            return back()->withErrors(['error' => 'You can only check-in to confirmed bookings.']);
        }

        $booking->update(['status' => 'checked_in']);

        return redirect()->route('booking.show', $booking)->with('success', 'You have successfully checked in! Enjoy your stay.');
    }

    public function checkOut(Request $request, AccommodationBooking $booking)
    {
        $user = $this->getActiveUser();
        
        if ($booking->user_id !== $user->id && $booking->accommodation->host_id !== $user->id) {
            abort(403);
        }

        if ($booking->status !== 'checked_in') {
            return back()->withErrors(['error' => 'You must be checked in to check out.']);
        }

        $booking->update(['status' => 'completed']);

        // Check-out complete, free up availability? (Normally kept as history).
        return redirect()->route('booking.show', $booking)->with('success', 'You have successfully checked out. We hope you enjoyed your stay!');
    }
}