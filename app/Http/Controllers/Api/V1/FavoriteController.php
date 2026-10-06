<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Listing;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $favorites = Favorite::with(['property.listings' => function($q) {
            $q->where('status', 'published');
        }])
        ->where('user_id', $request->user()->id)
        ->get();

        return response()->json(['data' => $favorites]);
    }

    public function store(Request $request)
    {
        $request->validate(['property_id' => 'required|exists:properties,id']);

        $favorite = Favorite::firstOrCreate([
            'user_id' => $request->user()->id,
            'property_id' => $request->property_id
        ]);

        return response()->json(['data' => $favorite], 201);
    }

    public function destroy(Request $request, $propertyId)
    {
        Favorite::where('user_id', $request->user()->id)
            ->where('property_id', $propertyId)
            ->delete();

        return response()->json(null, 204);
    }
}
