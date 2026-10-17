<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
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
     * Display the upgraded interactive analytics dashboard.
     */
    public function index(Request $request): View
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
