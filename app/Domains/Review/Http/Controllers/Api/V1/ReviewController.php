<?php

namespace App\Domains\Review\Http\Controllers\Api\V1;

use App\Domains\Review\Http\Controllers\Controller;
use App\Domains\Review\Models\Review;
use App\Http\Requests\Review\IndexReviewRequest;
use App\Http\Requests\Review\ReportReviewRequest;
use App\Http\Requests\Review\StoreReviewRequest;
use App\Http\Resources\ReviewResource;

class ReviewController extends Controller
{
    public function index(IndexReviewRequest $request)
    {
        $validated = $request->validated();

        $reviews = Review::where('reviewable_type', $validated['reviewable_type'])
            ->where('reviewable_id', $validated['reviewable_id'])
            ->where('status', 'published')
            ->with('reviewer:id,name')
            ->paginate(15);

        return response()->json(['data' => ReviewResource::collection($reviews)]);
    }

    public function store(StoreReviewRequest $request)
    {
        $validated = $request->validated();
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
            'status' => 'published',
        ]);

        $review->load('reviewer');

        return response()->json(['data' => new ReviewResource($review)], 201);
    }

    public function report(ReportReviewRequest $request, Review $review)
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
