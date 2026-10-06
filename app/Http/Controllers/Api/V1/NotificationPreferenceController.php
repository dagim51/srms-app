<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationPreferenceController extends Controller
{
    public function show()
    {
        $user = auth()->user();
        
        $preferences = \App\Models\NotificationPreference::firstOrCreate(
            ['user_id' => $user->id],
            [
                'email_notifications' => true,
                'in_app_notifications' => true,
                'push_notifications' => false,
            ]
        );
        
        return response()->json(['data' => $preferences]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'email_notifications' => 'boolean',
            'in_app_notifications' => 'boolean',
            'push_notifications' => 'boolean',
        ]);
        
        $user = auth()->user();
        
        $preferences = \App\Models\NotificationPreference::firstOrCreate(
            ['user_id' => $user->id],
            [
                'email_notifications' => true,
                'in_app_notifications' => true,
                'push_notifications' => false,
            ]
        );
        
        $preferences->update($validated);
        
        return response()->json(['data' => $preferences]);
    }
}
