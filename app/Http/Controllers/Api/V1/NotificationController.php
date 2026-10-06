<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        return response()->json([
            'data' => $user->notifications()->paginate(20)
        ]);
    }

    public function markAsRead(Request $request)
    {
        $validated = $request->validate([
            'notification_ids' => 'required|array',
            'notification_ids.*' => 'string' // UUIDs
        ]);
        
        $user = auth()->user();
        
        $user->notifications()
            ->whereIn('id', $validated['notification_ids'])
            ->get()
            ->markAsRead();
            
        return response()->json(['message' => 'Notifications marked as read.']);
    }

    public function destroy(string $id)
    {
        $user = auth()->user();
        
        $notification = $user->notifications()->findOrFail($id);
        $notification->delete();
        
        return response()->noContent();
    }
}
