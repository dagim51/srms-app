<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'reviewable_type' => 'required|string',
            'reviewable_id' => 'required|string',
        ]);

        $reviews = Review::where('reviewable_type', $validated['reviewable_type'])
            ->where('reviewable_id', $validated['reviewable_id'])
            ->where('status', 'published')
            ->with('reviewer:id,name')
            ->paginate(15);

        return response()->json(['data' => ReviewResource::collection($reviews)]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reviewable_type' => 'required|string|in:App\Models\Listing,App\Models\User',
            'reviewable_id' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $user = auth()->user();

        // Prevent duplicate reviews from the same user for the same target
        $exists = Review::where('reviewer_id', $user->id)
            ->where('reviewable_type', $validated['reviewable_type'])
            ->where('reviewable_id', $validated['reviewable_id'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'You have already reviewed this item.'], 409);
        }

        // Ensure user is not reviewing themselves (if target is User)
        if ($validated['reviewable_type'] === 'App\Models\User' && $validated['reviewable_id'] === $user->id) {
            return response()->json(['message' => 'You cannot review yourself.'], 422);
        }

        $review = Review::create([
            'reviewer_id' => $user->id,
            'reviewable_type' => $validated['reviewable_type'],
            'reviewable_id' => $validated['reviewable_id'],
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'status' => 'published', // Could default to moderation if needed
        ]);

        $review->load('reviewer');

        return response()->json(['data' => new ReviewResource($review)], 201);
    }

    public function report(Request $request, Review $review)
    {
        $review->update(['status' => 'flagged']);

        return response()->json(['message' => 'Review reported successfully.']);
    }

    public function destroy(Review $review)
    {
        $user = auth()->user();

        if ($review->reviewer_id !== $user->id) {
            abort(403, 'Only the reviewer can delete the review.');
        }

        $review->delete();

        return response()->noContent();
    }
}
