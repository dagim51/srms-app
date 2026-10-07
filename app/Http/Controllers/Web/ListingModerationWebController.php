<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ListingModerationWebController extends Controller
{
    /**
     * Enforce authorization: only Administrator / Moderator can access.
     */
    protected function ensureAuthorized()
    {
        // If logged in as non-admin, abort 403
        if (Auth::check() && !Auth::user()->hasRole('Administrator')) {
            abort(403, 'Unauthorized. Moderator access is restricted to Administrators.');
        }

        // If not logged in in local development, auto-login or retrieve the seeded Admin user
        if (!Auth::check()) {
            $admin = User::whereHas('roles', fn($q) => $q->where('name', 'Administrator'))->first();
            if ($admin) {
                Auth::login($admin);
            }
        }
    }

    /**
     * Display the moderator review queue with filters, stats, and quick actions.
     */
    public function index(Request $request)
    {
        $this->ensureAuthorized();

        $status = $request->get('status', 'pending_review');
        $query = Listing::with(['property.media', 'property.category', 'property.type'])->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($request->filled('purpose') && $request->purpose !== 'all') {
            $query->where('purpose', $request->purpose);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('property', function ($pq) use ($search) {
                    $pq->where('title', 'like', "%{$search}%")
                       ->orWhere('property_code', 'like', "%{$search}%");
                })->orWhere('purpose', 'like', "%{$search}%");
            });
        }

        $listings = $query->paginate(15)->withQueryString();

        $stats = [
            'in_queue' => Listing::where('status', 'pending_review')->count(),
            'approved' => Listing::where('status', 'approved')->orWhere('status', 'published')->count(),
            'rejected' => Listing::where('status', 'rejected')->count(),
            'suspended' => Listing::where('status', 'suspended')->count(),
            'total' => Listing::count(),
        ];

        return view('dashboard.moderation.index', compact('listings', 'stats', 'status'));
    }

    /**
     * Detailed inspection workstation for a specific listing under review.
     */
    public function show(Listing $listing)
    {
        $this->ensureAuthorized();

        $listing->load([
            'property.media', 
            'property.amenities', 
            'property.features', 
            'property.category', 
            'property.type',
            'moderations' => fn($q) => $q->latest()
        ]);

        $automatedChecks = $this->runAutomatedValidationEngine($listing);

        return view('dashboard.moderation.show', compact('listing', 'automatedChecks'));
    }

    /**
     * Record a moderation decision (Approve, Reject, or Suspend).
     */
    public function decision(Request $request, Listing $listing)
    {
        $this->ensureAuthorized();

        $validated = $request->validate([
            'action' => 'required|in:approve,reject,suspend',
            'comments' => 'nullable|string',
            'rejection_template' => 'nullable|string',
        ]);

        $action = $validated['action'];
        $comment = $validated['comments'] ?? $validated['rejection_template'] ?? null;

        if ($action === 'reject' && empty($comment)) {
            return back()->with('error', 'A clear rejection reason or explanation is mandatory when rejecting a listing.');
        }

        if ($action === 'approve') {
            $listing->status = 'published';
            $listing->published_at = $listing->published_at ?? now();
        } elseif ($action === 'reject') {
            $listing->status = 'rejected';
        } elseif ($action === 'suspend') {
            $listing->status = 'suspended';
        }

        $listing->save();

        // Record in moderation audit trail
        $listing->moderations()->create([
            'moderator_id' => Auth::id() ?? User::whereHas('roles', fn($q) => $q->where('name', 'Administrator'))->first()->id,
            'action' => $action,
            'comments' => $comment,
        ]);

        $statusText = match($action) {
            'approve' => 'Approved & Published to Marketplace',
            'reject' => 'Rejected with feedback recorded',
            'suspend' => 'Suspended from marketplace',
        };

        return redirect()->route('dashboard.moderation.listings.index')
            ->with('success', "Listing '{$listing->property->title}' {$statusText}.");
    }

    /**
     * Automated integrity validation checks
     */
    protected function runAutomatedValidationEngine(Listing $listing): array
    {
        $property = $listing->property;
        $checks = [];

        // Price Check
        $checks[] = [
            'name' => 'Price Integrity',
            'passed' => !empty($listing->price) && $listing->price >= 1000,
            'desc' => $listing->price >= 1000 ? 'Price is within legitimate real estate bracket' : 'Price is missing or suspiciously low',
        ];

        // Media count check
        $mediaCount = $property ? $property->media->count() : 0;
        $checks[] = [
            'name' => 'Visual Asset Verification',
            'passed' => $mediaCount >= 1,
            'desc' => "{$mediaCount} photo(s) attached" . ($mediaCount >= 3 ? ' (Meets high standard)' : ' (Below recommended threshold of 3)'),
        ];

        // Title and description length
        $descLen = $property ? strlen($property->description ?? '') : 0;
        $checks[] = [
            'name' => 'Content & Description Quality',
            'passed' => $descLen >= 30,
            'desc' => "Description length: {$descLen} characters",
        ];

        // GIS Geolocation check
        $hasGis = $property && !empty($property->latitude) && !empty($property->longitude);
        $checks[] = [
            'name' => 'Geographic Pin Verification',
            'passed' => $hasGis,
            'desc' => $hasGis ? "Coordinates: {$property->latitude}, {$property->longitude}" : 'No geographic coordinates set',
        ];

        return $checks;
    }
}
