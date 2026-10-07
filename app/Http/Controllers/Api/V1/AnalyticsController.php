<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReportTemplate;
use App\Models\GeneratedReport;

class AnalyticsController extends Controller
{
    public function dashboard(Request $request)
    {
        // Placeholder for aggregated data
        return response()->json([
            'data' => [
                'total_properties' => 120,
                'occupancy_rate' => 0.85,
                'total_revenue' => 150000.00
            ]
        ]);
    }

    public function createTemplate(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:financial,occupancy,maintenance',
            'configuration' => 'nullable|array'
        ]);

        $template = ReportTemplate::create($validated);
        return response()->json(['data' => $template], 201);
    }

    public function generateReport(Request $request)
    {
        $validated = $request->validate([
            'report_template_id' => 'required|uuid|exists:report_templates,id',
            'format' => 'required|string|in:csv,pdf'
        ]);

        $report = GeneratedReport::create([
            'report_template_id' => $validated['report_template_id'],
            'user_id' => auth()->id(),
            'status' => 'processing',
            'format' => $validated['format']
        ]);

        // Simulating async dispatch
        $report->update([
            'status' => 'completed',
            'file_path' => '/storage/reports/' . $report->id . '.' . $report->format
        ]);

        return response()->json(['data' => $report], 201);
    }
}
