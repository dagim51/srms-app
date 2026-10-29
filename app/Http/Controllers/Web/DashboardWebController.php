<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AccommodationBooking;
use App\Models\Favorite;
use App\Models\Inquiry;
use App\Models\Lease;
use App\Models\Listing;
use App\Models\Offer;
use App\Models\Property;
use App\Models\Viewing;
use App\Services\Dashboard\DashboardAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardWebController extends Controller
{
    public function __construct(
        protected DashboardAnalyticsService $analyticsService
    ) {}

    /**
     * Display the upgraded interactive analytics dashboard or personalized client portal.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $isManagement = $user->hasAnyRole([
            'Administrator',
            'Property Manager',
            'Agent',
            'Developer',
            'Host',
            'Seller',
            'Landlord',
            'Moderator',
            'Verification Officer',
            'Finance Officer',
            'Professional Service Provider',
            'Real Estate Company',
        ]) || $user->can('properties.create') || $user->can('listings.submit') || Property::where('owner_user_id', $user->id)->exists();

        // Regular users (Buyers, Tenants, standard Users) see the personalized Client Portal
        if (! $isManagement) {
            $savedFavoritesCount = Favorite::where('user_id', $user->id)->count();
            $activeInquiriesCount = Inquiry::where('requester_id', $user->id)->count();
            $scheduledViewingsCount = Viewing::where('requester_id', $user->id)
                ->whereIn('status', ['requested', 'scheduled', 'confirmed'])
                ->count();
            $submittedOffersCount = Offer::where('buyer_id', $user->id)->count();
            $activeLeasesCount = Lease::whereHas('tenantProfile', fn ($tq) => $tq->where('user_id', $user->id))->count();
            $activeBookingsCount = AccommodationBooking::where('guest_id', $user->id)->count();

            $recentFavorites = Favorite::where('user_id', $user->id)
                ->with([
                    'property.media' => fn ($q) => $q->where('visibility', 'public')->orderBy('sort_order'),
                    'property.listings' => fn ($q) => $q->where('status', 'published')->latest(),
                    'property.category',
                ])
                ->latest()
                ->take(4)
                ->get();

            $recentInquiries = Inquiry::where('requester_id', $user->id)
                ->with(['listing.property'])
                ->latest()
                ->take(4)
                ->get();

            $recommendedListings = Listing::where('status', 'published')
                ->where('visibility', 'public')
                ->with([
                    'property.media' => fn ($q) => $q->where('visibility', 'public')->orderBy('sort_order'),
                    'property.category',
                ])
                ->latest()
                ->take(3)
                ->get();

            return view('dashboard.user', compact(
                'savedFavoritesCount',
                'activeInquiriesCount',
                'scheduledViewingsCount',
                'submittedOffersCount',
                'activeLeasesCount',
                'activeBookingsCount',
                'recentFavorites',
                'recentInquiries',
                'recommendedListings'
            ));
        }

        $filters = [
            'period' => $request->get('period', 'all'),
            'purpose' => $request->get('purpose', 'all'),
        ];

        $metrics = $this->analyticsService->getMetrics($user, $filters);
        $trends = $this->analyticsService->getTrends($user, $filters);
        $purposeDist = $this->analyticsService->getPurposeDistribution($user, $filters);
        $offerOutcomes = $this->analyticsService->getOfferOutcomes($user, $filters);
        $lifecycleStages = $this->analyticsService->getLifecycleStages($user);
        $recentActivities = $this->analyticsService->getRecentActivities($user, 6);

        return view('dashboard.index', compact(
            'metrics',
            'trends',
            'purposeDist',
            'offerOutcomes',
            'lifecycleStages',
            'recentActivities',
            'filters'
        ));
    }

    /**
     * Provide JSON payload for real-time / AJAX filter updates.
     */
    public function data(Request $request): JsonResponse
    {
        $user = $request->user();
        $filters = [
            'period' => $request->get('period', 'all'),
            'purpose' => $request->get('purpose', 'all'),
        ];

        $metrics = $this->analyticsService->getMetrics($user, $filters);
        $trends = $this->analyticsService->getTrends($user, $filters);
        $purposeDist = $this->analyticsService->getPurposeDistribution($user, $filters);
        $offerOutcomes = $this->analyticsService->getOfferOutcomes($user, $filters);

        return response()->json([
            'status' => 'success',
            'filters' => $filters,
            'metrics' => $metrics,
            'trends' => $trends,
            'purposeDist' => $purposeDist,
            'offerOutcomes' => $offerOutcomes,
        ]);
    }
}
