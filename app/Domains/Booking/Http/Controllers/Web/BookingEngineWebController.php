<?php

namespace App\Domains\Booking\Http\Controllers\Web;

use App\Domains\Stay\Services\CalendarSyncService;
use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\AccommodationBooking;
use App\Services\Bookings\BookingManager;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $guests = (int) $request->input('guests', 1);

        // Precompute detailed itemized pricing breakdown using dynamic rules and weekend multipliers
        $nights = max(1, (int) Carbon::parse($checkIn)->diffInDays(Carbon::parse($checkOut)));
        $pricing = $this->bookingManager->calculateDetailedPrice($accommodation, $checkIn, $checkOut);
        $basePrice = $pricing['base_subtotal'];

        return view('frontend.booking.create', compact('accommodation', 'checkIn', 'checkOut', 'guests', 'nights', 'basePrice', 'pricing'));
    }

    public function store(Request $request, Accommodation $accommodation)
    {
        $user = $this->getActiveUser();

        $request->validate([
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'guests' => 'required|integer|min:1|max:'.$accommodation->max_guests,
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

        // Use BookingManager for reservation hold creation (15-minute hold)
        try {
            $booking = $this->bookingManager->createHold([
                'accommodation_id' => $accommodation->id,
                'check_in_date' => $checkIn->format('Y-m-d'),
                'check_out_date' => $checkOut->format('Y-m-d'),
                'guests' => $request->guests,
            ], $user->id, 15);

            return redirect()->route('booking.show', $booking)->with('success', 'Temporary reservation held for 15 minutes! Please complete payment.');
        } catch (\DomainException $e) {
            return back()->withErrors(['dates' => $e->getMessage()]);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'An error occurred while securing your booking hold: '.$e->getMessage()]);
        }
    }

    public function show(AccommodationBooking $booking)
    {
        $user = $this->getActiveUser();

        if ($booking->user_id !== $user->id && $booking->accommodation->host_id !== $user->id) {
            abort(403);
        }

        $booking->load(['accommodation.property']);

        return view('frontend.booking.show', compact('booking'));
    }

    public function confirm(Request $request, AccommodationBooking $booking)
    {
        $user = $this->getActiveUser();

        if ($booking->user_id !== $user->id && $booking->accommodation->host_id !== $user->id) {
            abort(403);
        }

        try {
            $this->bookingManager->confirm($booking, $user->id);

            return redirect()->route('booking.show', $booking)->with('success', 'Booking confirmed successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function cancel(Request $request, AccommodationBooking $booking)
    {
        $user = $this->getActiveUser();

        if ($booking->user_id !== $user->id && $booking->accommodation->host_id !== $user->id) {
            abort(403);
        }

        try {
            $this->bookingManager->cancel($booking, $user->id, 'guest_cancelled');

            return redirect()->route('booking.show', $booking)->with('success', 'Booking cancelled and calendar availability released.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Export standard RFC 5545 iCalendar feed for external synchronization.
     */
    public function exportCalendar(Accommodation $accommodation, CalendarSyncService $calendarService)
    {
        $ics = $calendarService->generateIcsFeed($accommodation);

        return response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'inline; filename="accommodation-'.$accommodation->id.'.ics"',
        ]);
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
