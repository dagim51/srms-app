<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SearchAlert;

class SearchAlertController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'criteria' => 'required|array',
            'frequency' => 'nullable|string|in:instant,daily,weekly'
        ]);

        $alert = SearchAlert::create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'] ?? null,
            'criteria' => $validated['criteria'],
            'frequency' => $validated['frequency'] ?? 'daily',
            'is_active' => true
        ]);

        return response()->json(['data' => $alert], 201);
    }
}
