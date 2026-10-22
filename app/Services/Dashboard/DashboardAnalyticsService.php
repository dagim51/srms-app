<?php

namespace App\Services\Dashboard;

use App\Models\Inquiry;
use App\Models\Listing;
use App\Models\Offer;
use App\Models\Payment;
use App\Models\User;
use App\Models\Viewing;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardAnalyticsService
{
    /**
     * Compute summary KPI metrics with period-over-period comparison.
     */
    public function getMetrics(User $user, array $filters = []): array
    {
        $period = $filters['period'] ?? 'all';
        $purpose = $filters['purpose'] ?? 'all';

        [$startDate, $prevStartDate, $prevEndDate] = $this->resolveDateRanges($period);

        // 1. Active Listings (Published)
        $listingQuery = Listing::where('status', 'published')->where('visibility', 'public');
        if ($purpose !== 'all') {
            $listingQuery->where('purpose', $this->normalizePurpose($purpose));
        }

        $activeListings = (clone $listingQuery)->count();
        $prevListings = $prevStartDate
            ? (clone $listingQuery)->whereBetween('created_at', [$prevStartDate, $prevEndDate])->count()
            : 0;
        $currentPeriodListings = $startDate
            ? (clone $listingQuery)->where('created_at', '>=', $startDate)->count()
            : $activeListings;

        $listingChange = $this->calculatePercentageChange($currentPeriodListings, $prevListings);

        // 2. Portfolio Market Value (ETB)
        $portfolioValue = (float) (clone $listingQuery)->sum('price');
        $prevPortfolioValue = $prevStartDate
            ? (float) (clone $listingQuery)->whereBetween('created_at', [$prevStartDate, $prevEndDate])->sum('price')
            : 0;
        $valueChange = $this->calculatePercentageChange($portfolioValue, $prevPortfolioValue);

        // 3. Inquiries
        $inquiryQuery = Inquiry::query();
        if ($startDate) {
            $inquiryQuery->where('created_at', '>=', $startDate);
        }
        $totalInquiries = (clone $inquiryQuery)->count();
        $pendingInquiries = (clone $inquiryQuery)->where('status', 'pending')->count();
        $respondedInquiries = (clone $inquiryQuery)->whereIn('status', ['responded', 'closed'])->count();
        $responseRate = $totalInquiries > 0 ? round(($respondedInquiries / $totalInquiries) * 100) : 100;

        // 4. Viewings Scheduled
        $viewingQuery = Viewing::query();
        if ($startDate) {
            $viewingQuery->where('created_at', '>=', $startDate);
        }
        $scheduledViewings = (clone $viewingQuery)->whereIn('status', ['scheduled', 'requested'])->count();
        $completedViewings = (clone $viewingQuery)->where('status', 'completed')->count();

        // 5. Active Offers & Pipeline Value
        $offerQuery = Offer::query();
        if ($startDate) {
            $offerQuery->where('created_at', '>=', $startDate);
        }
        $activeOffers = (clone $offerQuery)->whereIn('status', ['pending', 'countered'])->count();
        $offerVolume = (float) (clone $offerQuery)->whereIn('status', ['pending', 'countered', 'accepted'])->sum('amount');

        // 6. Verified Completed Payments
        $paymentQuery = Payment::where('status', 'completed');
        if ($startDate) {
            $paymentQuery->where('created_at', '>=', $startDate);
        }
        $collectedRevenue = (float) (clone $paymentQuery)->sum('amount');

        return [
            'active_listings' => [
                'value' => $activeListings,
                'change' => $listingChange,
                'caption' => __('Live in marketplace'),
                'badge' => $activeListings > 0 ? __('Healthy') : __('Needs Inventory'),
                'badgeVariant' => $activeListings > 0 ? 'success' : 'neutral',
            ],
            'portfolio_value' => [
                'value' => $portfolioValue,
                'formatted' => $this->formatCurrencyCompact($portfolioValue),
                'raw_formatted' => number_format($portfolioValue).' ETB',
                'change' => $valueChange,
                'caption' => __('Active catalog valuation'),
                'badge' => 'ETB',
                'badgeVariant' => 'brand',
            ],
            'inquiries' => [
                'total' => $totalInquiries,
                'pending' => $pendingInquiries,
                'response_rate' => $responseRate,
                'caption' => $pendingInquiries > 0 ? __(':count awaiting response', ['count' => $pendingInquiries]) : __('All caught up'),
                'badge' => $pendingInquiries > 0 ? __('Active') : __('Clear'),
                'badgeVariant' => $pendingInquiries > 0 ? 'warning' : 'success',
            ],
            'viewings' => [
                'scheduled' => $scheduledViewings,
                'completed' => $completedViewings,
                'caption' => __('Scheduled & requested'),
                'badge' => __('Tours'),
                'badgeVariant' => 'neutral',
            ],
            'offers' => [
                'count' => $activeOffers,
                'volume' => $offerVolume,
                'formatted_volume' => $this->formatCurrencyCompact($offerVolume),
                'caption' => __('Negotiations in pipeline'),
                'badge' => $activeOffers > 0 ? __('Negotiating') : __('No Offers'),
                'badgeVariant' => $activeOffers > 0 ? 'info' : 'neutral',
            ],
            'revenue' => [
                'collected' => $collectedRevenue,
                'formatted' => $this->formatCurrencyCompact($collectedRevenue),
                'caption' => __('Settled platform revenue'),
                'badge' => __('Verified'),
                'badgeVariant' => 'success',
            ],
        ];
    }

    /**
     * Compute multi-series monthly / periodic trend datasets.
     */
    public function getTrends(User $user, array $filters = []): array
    {
        $period = $filters['period'] ?? 'all';
        $monthsToDisplay = match ($period) {
            'today', 'week' => 7,
            'month' => 30,
            'quarter' => 12,
            default => 6,
        };

        $labels = [];
        $listingCounts = [];
        $inquiryCounts = [];
        $offerCounts = [];

        if ($period === 'today' || $period === 'week') {
            // Daily breakdown for the past 7 days
            for ($i = 6; $i >= 0; $i--) {
                $day = Carbon::now()->subDays($i);
                $dayLabel = $day->format('D, M j');
                $start = $day->copy()->startOfDay();
                $end = $day->copy()->endOfDay();

                $labels[] = $dayLabel;
                $listingCounts[] = Listing::whereBetween('created_at', [$start, $end])->count();
                $inquiryCounts[] = Inquiry::whereBetween('created_at', [$start, $end])->count();
                $offerCounts[] = Offer::whereBetween('created_at', [$start, $end])->count();
            }
        } else {
            // Monthly breakdown for last 6 months
            for ($i = 5; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                $monthLabel = $month->format('M Y');
                $start = $month->copy()->startOfMonth();
                $end = $month->copy()->endOfMonth();

                $labels[] = $monthLabel;
                $listingCounts[] = Listing::whereBetween('created_at', [$start, $end])->count();
                $inquiryCounts[] = Inquiry::whereBetween('created_at', [$start, $end])->count();
                $offerCounts[] = Offer::whereBetween('created_at', [$start, $end])->count();
            }
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => __('New Listings'),
                    'data' => $listingCounts,
                    'borderColor' => '#F59E0B',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.12)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
                [
                    'label' => __('Client Inquiries'),
                    'data' => $inquiryCounts,
                    'borderColor' => '#3B82F6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.08)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
                [
                    'label' => __('Purchase/Rent Offers'),
                    'data' => $offerCounts,
                    'borderColor' => '#10B981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.08)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
        ];
    }

    /**
     * Breakdown of listings by Purpose (For Sale, For Rent, Short Stay, Development)
     */
    public function getPurposeDistribution(User $user, array $filters = []): array
    {
        $purposes = ['For Sale', 'For Rent', 'Short Stay', 'Development'];
        $counts = [];

        foreach ($purposes as $purpose) {
            $counts[] = Listing::where('purpose', $purpose)->count();
        }

        return [
            'labels' => [__('For Sale'), __('For Rent'), __('Short Stay'), __('Development')],
            'data' => $counts,
            'colors' => ['#F59E0B', '#3B82F6', '#8B5CF6', '#10B981'],
        ];
    }

    /**
     * Breakdown of Offer negotiation outcomes.
     */
    public function getOfferOutcomes(User $user, array $filters = []): array
    {
        $statuses = ['accepted', 'countered', 'pending', 'rejected'];
        $counts = [];

        foreach ($statuses as $status) {
            $counts[] = Offer::where('status', $status)->count();
        }

        return [
            'labels' => [__('Accepted'), __('Countered'), __('Pending'), __('Rejected')],
            'data' => $counts,
            'colors' => ['#10B981', '#F59E0B', '#3B82F6', '#EF4444'],
        ];
    }

    /**
     * Real Estate Transaction & Moderation Lifecycle Stages (Flowchart data).
     */
    public function getLifecycleStages(User $user): array
    {
        $draftCount = Listing::where('status', 'draft')->count();
        $pendingReviewCount = Listing::where('status', 'pending_review')->count();
        $publishedCount = Listing::where('status', 'published')->count();
        $inquiryCount = Inquiry::where('status', 'pending')->count();
        $activeOfferCount = Offer::whereIn('status', ['pending', 'countered'])->count();
        $closedOfferCount = Offer::where('status', 'accepted')->count();

        return [
            [
                'step' => 1,
                'name' => __('Draft & Staging'),
                'code' => 'draft',
                'count' => $draftCount,
                'status' => 'active',
                'description' => __('Property specs, photos, GIS coordinates, and pricing drafted by owner/agent.'),
                'icon' => 'document-pencil',
                'link' => '/dashboard/listings?status=draft',
            ],
            [
                'step' => 2,
                'name' => __('Compliance Review'),
                'code' => 'moderation',
                'count' => $pendingReviewCount,
                'status' => $pendingReviewCount > 0 ? 'warning' : 'complete',
                'description' => __('SRMS compliance verifies ownership documents, parcel coordinates, and pricing authenticity.'),
                'icon' => 'shield-check',
                'link' => '/dashboard/moderation',
            ],
            [
                'step' => 3,
                'name' => __('Live Marketplace'),
                'code' => 'published',
                'count' => $publishedCount,
                'status' => 'success',
                'description' => __('Published to public directory, search index, and interactive GIS map explorer.'),
                'icon' => 'globe',
                'link' => '/properties',
            ],
            [
                'step' => 4,
                'name' => __('Inquiries & Viewings'),
                'code' => 'engagement',
                'count' => $inquiryCount,
                'status' => $inquiryCount > 0 ? 'active' : 'idle',
                'description' => __('Buyer engagement, chat messages, and on-site verified physical viewings.'),
                'icon' => 'chat',
                'link' => '/dashboard/messages',
            ],
            [
                'step' => 5,
                'name' => __('Offer Negotiation'),
                'code' => 'negotiation',
                'count' => $activeOfferCount,
                'status' => $activeOfferCount > 0 ? 'active' : 'idle',
                'description' => __('Formal binding offers submitted, counter-offers exchanged, and terms agreed.'),
                'icon' => 'currency-dollar',
                'link' => '/dashboard/offers',
            ],
            [
                'step' => 6,
                'name' => __('Contract & Closing'),
                'code' => 'closed',
                'count' => $closedOfferCount,
                'status' => 'complete',
                'description' => __('Formal lease/sale contract signed, payment settled, and keys transferred.'),
                'icon' => 'check-badge',
                'link' => '/dashboard/finance/ledger',
            ],
        ];
    }

    /**
     * Recent system activities.
     */
    public function getRecentActivities(User $user, int $limit = 6): array
    {
        $activities = [];

        // Recent listings
        $recentListings = Listing::with('property')
            ->latest('created_at')
            ->take(3)
            ->get();

        foreach ($recentListings as $item) {
            $title = $item->property ? $item->property->title : 'Property #'.substr($item->id, 0, 8);
            $activities[] = [
                'type' => 'listing',
                'title' => $title,
                'caption' => __('Listing :purpose created (:price ETB)', [
                    'purpose' => $item->purpose,
                    'price' => number_format((float) $item->price),
                ]),
                'badge' => ucfirst($item->status),
                'badgeVariant' => match ($item->status) {
                    'published' => 'success',
                    'pending_review' => 'warning',
                    default => 'neutral',
                },
                'timestamp' => $item->created_at ? $item->created_at->diffForHumans() : __('Recently'),
                'raw_time' => $item->created_at ? $item->created_at->timestamp : 0,
                'link' => '/dashboard/listings/'.$item->id,
            ];
        }

        // Recent inquiries
        $recentInquiries = Inquiry::with('property')
            ->latest('created_at')
            ->take(3)
            ->get();

        foreach ($recentInquiries as $inq) {
            $propTitle = $inq->property ? $inq->property->title : __('Inquiry');
            $activities[] = [
                'type' => 'inquiry',
                'title' => __('New Client Inquiry'),
                'caption' => __('For :property', ['property' => $propTitle]),
                'badge' => ucfirst($inq->status),
                'badgeVariant' => $inq->status === 'pending' ? 'warning' : 'info',
                'timestamp' => $inq->created_at ? $inq->created_at->diffForHumans() : __('Recently'),
                'raw_time' => $inq->created_at ? $inq->created_at->timestamp : 0,
                'link' => '/dashboard/messages',
            ];
        }

        // Recent offers
        $recentOffers = Offer::with('listing.property')
            ->latest('created_at')
            ->take(2)
            ->get();

        foreach ($recentOffers as $offer) {
            $propTitle = $offer->listing && $offer->listing->property ? $offer->listing->property->title : __('Property');
            $activities[] = [
                'type' => 'offer',
                'title' => __('Offer Submitted'),
                'caption' => __(':amount ETB on :property', [
                    'amount' => number_format((float) $offer->amount),
                    'property' => $propTitle,
                ]),
                'badge' => ucfirst($offer->status),
                'badgeVariant' => match ($offer->status) {
                    'accepted' => 'success',
                    'countered' => 'brand',
                    'rejected' => 'error',
                    default => 'warning',
                },
                'timestamp' => $offer->created_at ? $offer->created_at->diffForHumans() : __('Recently'),
                'raw_time' => $offer->created_at ? $offer->created_at->timestamp : 0,
                'link' => '/dashboard/offers/'.$offer->id,
            ];
        }

        // Sort descending by raw_time
        usort($activities, fn ($a, $b) => $b['raw_time'] <=> $a['raw_time']);

        return array_slice($activities, 0, $limit);
    }

    /**
     * Date range resolution helper.
     */
    protected function resolveDateRanges(string $period): array
    {
        $now = Carbon::now();

        return match ($period) {
            'today' => [
                $now->copy()->startOfDay(),
                $now->copy()->subDay()->startOfDay(),
                $now->copy()->subDay()->endOfDay(),
            ],
            'week' => [
                $now->copy()->subDays(7)->startOfDay(),
                $now->copy()->subDays(14)->startOfDay(),
                $now->copy()->subDays(7)->endOfDay(),
            ],
            'month' => [
                $now->copy()->subDays(30)->startOfDay(),
                $now->copy()->subDays(60)->startOfDay(),
                $now->copy()->subDays(30)->endOfDay(),
            ],
            'quarter' => [
                $now->copy()->subDays(90)->startOfDay(),
                $now->copy()->subDays(180)->startOfDay(),
                $now->copy()->subDays(90)->endOfDay(),
            ],
            'year' => [
                $now->copy()->subYear()->startOfDay(),
                $now->copy()->subYears(2)->startOfDay(),
                $now->copy()->subYear()->endOfDay(),
            ],
            default => [null, null, null],
        };
    }

    /**
     * Compute percentage change between periods.
     */
    protected function calculatePercentageChange(float|int $current, float|int $previous): array
    {
        if ($previous == 0) {
            return [
                'direction' => $current > 0 ? 'up' : 'neutral',
                'percentage' => $current > 0 ? 100 : 0,
                'text' => $current > 0 ? '+100%' : '0%',
            ];
        }

        $change = (($current - $previous) / $previous) * 100;
        $rounded = round(abs($change), 1);

        if ($change > 0) {
            return [
                'direction' => 'up',
                'percentage' => $rounded,
                'text' => "+{$rounded}%",
            ];
        } elseif ($change < 0) {
            return [
                'direction' => 'down',
                'percentage' => $rounded,
                'text' => "-{$rounded}%",
            ];
        }

        return [
            'direction' => 'neutral',
            'percentage' => 0,
            'text' => '0%',
        ];
    }

    /**
     * Compact format for ETB currency.
     */
    protected function formatCurrencyCompact(float $amount): string
    {
        if ($amount >= 1_000_000_000) {
            return round($amount / 1_000_000_000, 2).'B ETB';
        }
        if ($amount >= 1_000_000) {
            return round($amount / 1_000_000, 2).'M ETB';
        }
        if ($amount >= 1_000) {
            return round($amount / 1_000, 1).'K ETB';
        }

        return number_format($amount).' ETB';
    }

    /**
     * Normalize URL purpose parameter to DB purpose column.
     */
    protected function normalizePurpose(string $purpose): string
    {
        return match (strtolower($purpose)) {
            'sale', 'buy', 'for sale' => 'For Sale',
            'rent', 'for rent' => 'For Rent',
            'stay', 'short stay' => 'Short Stay',
            'development', 'projects' => 'Development',
            default => $purpose,
        };
    }
}
