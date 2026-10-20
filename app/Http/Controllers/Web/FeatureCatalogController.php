<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PropertyFeature;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FeatureCatalogController extends Controller
{
    public function index(Request $request)
    {
        $query = PropertyFeature::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name_en', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $features = $query->orderBy('name_en')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => PropertyFeature::count(),
            'active' => PropertyFeature::where('status', 'active')->count(),
            'inactive' => PropertyFeature::where('status', 'inactive')->count(),
        ];

        return view('dashboard.features.index', compact('features', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'code' => 'nullable|string|max:100|unique:property_features,code',
            'status' => 'required|in:active,inactive',
        ]);

        if (empty($validated['code'])) {
            $baseCode = Str::slug($validated['name_en'], '_') ?: 'feature';
            $code = $baseCode;
            $counter = 1;
            while (PropertyFeature::where('code', $code)->exists()) {
                $code = $baseCode . '_' . $counter++;
            }
            $validated['code'] = $code;
        }

        PropertyFeature::create($validated);

        return redirect()->route('dashboard.features.index')
            ->with('success', 'Feature created successfully.');
    }

    public function update(Request $request, PropertyFeature $feature)
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:property_features,code,' . $feature->id,
            'status' => 'required|in:active,inactive',
        ]);

        $feature->update($validated);

        return redirect()->route('dashboard.features.index')
            ->with('success', 'Feature updated successfully.');
    }

    public function toggleStatus(PropertyFeature $feature)
    {
        $feature->update([
            'status' => $feature->status === 'active' ? 'inactive' : 'active',
        ]);

        if (request()->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'new_status' => $feature->status,
                'message' => 'Status updated successfully.',
            ]);
        }

        return redirect()->back()->with('success', "Feature status changed to {$feature->status}.");
    }

    public function destroy(PropertyFeature $feature)
    {
        $feature->properties()->detach();
        $feature->delete();

        return redirect()->route('dashboard.features.index')
            ->with('success', 'Feature deleted successfully.');
    }
}
