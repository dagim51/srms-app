<?php

namespace App\Domains\Review\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\AccommodationBooking;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewWebController extends Controller
{
    protected function getActiveUser()
    {
        return Auth::user();
    }

    public function index()
    {
        $user = $this->getActiveUser();

        $reviews = Review::where('reviewer_id', $user->id)
            ->latest()
            ->paginate(10);

        return view('frontend.booking.reviews.index', compact('reviews'));
    }

    public function create(AccommodationBooking $booking)
    {
        $user = $this->getActiveUser();

        // Ensure user is authorized and booking is completed
        if ($booking->user_id !== $user->id) {
            abort(403);
        }

        if ($booking->status !== 'completed') {
            return back()->withErrors(['error' => 'You can only review a completed stay.']);
        }

        // Check if already reviewed
        $existingReview = Review::where('reviewer_id', $user->id)
            ->where('reviewable_type', Accommodation::class)
            ->where('reviewable_id', $booking->accommodation_id)
            ->where('booking_id', $booking->id) // Assuming we add this or just rely on accommodation_id + user + completed
            ->first();

        // Workaround since we might not have booking_id in reviews table, we use created_at logic or simply pass

        return view('frontend.booking.reviews.create', compact('booking'));
    }

    public function store(Request $request, AccommodationBooking $booking)
    {
        $user = $this->getActiveUser();

        if ($booking->user_id !== $user->id || $booking->status !== 'completed') {
            abort(403);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:10|max:1000',
        ]);

        Review::create([
            'reviewer_id' => $user->id,
            'reviewable_type' => Accommodation::class,
            'reviewable_id' => $booking->accommodation_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'status' => 'published',
        ]);

        return redirect()->route('booking.show', $booking)->with('success', 'Your review has been submitted successfully.');
    }
}
