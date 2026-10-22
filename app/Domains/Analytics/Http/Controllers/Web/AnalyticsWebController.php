<?php

namespace App\Domains\Analytics\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class AnalyticsWebController extends Controller
{
    public function index()
    {
        $metrics = [
            'active_listings' => 4520,
            'occupancy_rate' => '84%',
            'avg_daily_rate' => '2,500 ETB',
            'total_revenue' => '12.4M ETB',
        ];

        // Market Intelligence Mock Data
        $marketIntelligence = [
            'price_per_sqm' => '35,000 ETB',
            'demand_trend' => '+12% YoY',
            'popular_neighborhood' => 'Bole',
            'avg_time_on_market' => '18 days',
        ];

        return view('frontend.analytics.index', compact('metrics', 'marketIntelligence'));
    }

    public function reports()
    {
        $savedReports = [
            ['id' => 'RPT-001', 'name' => 'Q3 Rental Yields', 'type' => 'Financial', 'frequency' => 'Quarterly', 'last_run' => '2026-10-01', 'status' => 'ready'],
            ['id' => 'RPT-002', 'name' => 'Monthly Occupancy - Bole', 'type' => 'Performance', 'frequency' => 'Monthly', 'last_run' => '2026-10-05', 'status' => 'ready'],
            ['id' => 'RPT-003', 'name' => 'Platform Gross Volume YTD', 'type' => 'Financial', 'frequency' => 'On-Demand', 'last_run' => '2026-10-08', 'status' => 'processing'],
        ];

        return view('frontend.analytics.reports', compact('savedReports'));
    }

    public function exports()
    {
        $exports = [
            ['id' => 'EXP-901', 'report' => 'Platform Gross Volume YTD', 'format' => 'CSV', 'status' => 'processing', 'progress' => '45%', 'requested_at' => '2026-10-08 10:15'],
            ['id' => 'EXP-902', 'report' => 'All Active Leases (Bole)', 'format' => 'Excel', 'status' => 'completed', 'progress' => '100%', 'requested_at' => '2026-10-07 14:20'],
            ['id' => 'EXP-903', 'report' => 'User Growth Q3', 'format' => 'PDF', 'status' => 'failed', 'progress' => 'Error', 'requested_at' => '2026-10-06 09:00'],
        ];

        return view('frontend.analytics.exports', compact('exports'));
    }
}
