<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use Illuminate\Http\Request;

class AmenityController extends Controller
{
    public function index(Request $request)
    {
        $query = Amenity::where('status', 'active');

        if ($request->has('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name_en', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $amenities = $query->orderBy('category')->orderBy('name_en')->get();

        if ($request->boolean('grouped')) {
            $amenities = $amenities->groupBy('category');
        }

        return response()->json([
            'status' => 'success',
            'data' => $amenities,
        ]);
    }

    public function categories()
    {
        $categories = Amenity::where('status', 'active')
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return response()->json([
            'status' => 'success',
            'data' => $categories,
        ]);
    }
}
