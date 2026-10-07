<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AmenityCatalogController extends Controller
{
    public function index(Request $request)
    {
        $query = Amenity::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name_en', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $amenities = $query->orderBy('category')
            ->orderBy('name_en')
            ->paginate(15)
            ->withQueryString();

        $categories = Amenity::select('category')
            ->distinct()
            ->whereNotNull('category')
            ->orderBy('category')
            ->pluck('category');

        $stats = [
            'total' => Amenity::count(),
            'active' => Amenity::where('status', 'active')->count(),
            'inactive' => Amenity::where('status', 'inactive')->count(),
            'categories_count' => $categories->count(),
        ];

        return view('dashboard.amenities.index', compact('amenities', 'categories', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'code' => 'nullable|string|max:100|unique:amenities,code',
            'status' => 'required|in:active,inactive',
        ]);

        if (empty($validated['code'])) {
            $validated['code'] = Str::slug($validated['name_en'], '_');
        }

        Amenity::create($validated);

        return redirect()->route('dashboard.amenities.index')
            ->with('success', 'Amenity created successfully.');
    }

    public function update(Request $request, Amenity $amenity)
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'code' => 'required|string|max:100|unique:amenities,code,' . $amenity->id,
            'status' => 'required|in:active,inactive',
        ]);

        $amenity->update($validated);

        return redirect()->route('dashboard.amenities.index')
            ->with('success', 'Amenity updated successfully.');
    }

    public function toggleStatus(Amenity $amenity)
    {
        $amenity->update([
            'status' => $amenity->status === 'active' ? 'inactive' : 'active',
        ]);

        if (request()->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'new_status' => $amenity->status,
                'message' => 'Status updated successfully.',
            ]);
        }

        return redirect()->back()->with('success', "Amenity status changed to {$amenity->status}.");
    }

    public function destroy(Amenity $amenity)
    {
        $amenity->properties()->detach();
        $amenity->delete();

        return redirect()->route('dashboard.amenities.index')
            ->with('success', 'Amenity deleted successfully.');
    }
}
