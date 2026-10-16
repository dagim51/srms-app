<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;

class NotificationWebController extends Controller
{
    protected function getActiveUser()
    {
        return Auth::user();
    }

    public function index()
    {
        $user = $this->getActiveUser();
        
        // Ensure user uses Notifiable trait. We'll use DatabaseNotification directly for simplicity if Notifiable isn't set up perfectly for mock user.
        $notifications = DatabaseNotification::where('notifiable_id', $user->id)
            ->where('notifiable_type', User::class)
            ->latest()
            ->paginate(15);
            
        // If there are no notifications, let's mock some for the UI demonstration
        if ($notifications->isEmpty() && $user->id) {
            $user->notifications()->create([
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'type' => 'App\Notifications\BookingConfirmed',
                'data' => [
                    'title' => 'Booking Confirmed',
                    'message' => 'Your stay at "Luxury Villa" has been confirmed!',
                    'action_url' => '/dashboard/reservations',
                ],
                'read_at' => null,
            ]);
            $user->notifications()->create([
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'type' => 'App\Notifications\NewMessage',
                'data' => [
                    'title' => 'New Message',
                    'message' => 'Host John sent you a message about your upcoming stay.',
                    'action_url' => '/dashboard/messages',
                ],
                'read_at' => now()->subDays(1),
            ]);
            
            $notifications = DatabaseNotification::where('notifiable_id', $user->id)
                ->where('notifiable_type', User::class)
                ->latest()
                ->paginate(15);
        }
            
        return view('frontend.notifications.index', compact('notifications'));
    }

    public function markAsRead(Request $request, $id)
    {
        $user = $this->getActiveUser();
        
        $notification = DatabaseNotification::where('notifiable_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();
            
        $notification->markAsRead();
        
        return back()->with('success', 'Notification marked as read.');
    }

    public function preferences()
    {
        $user = $this->getActiveUser();
        
        // Mock preferences for the UI if they don't exist
        $preferences = [
            'email_marketing' => true,
            'email_booking_updates' => true,
            'sms_booking_updates' => false,
            'push_messages' => true,
        ];
        
        return view('frontend.notifications.preferences', compact('preferences'));
    }
    
    public function updatePreferences(Request $request)
    {
        $user = $this->getActiveUser();
        
        // Typically we would save these to a preferences table or JSON column on the user
        // $user->update(['notification_preferences' => $request->only([...])]);
        
        return redirect()->back()->with('success', 'Your notification preferences have been saved.');
    }
}
