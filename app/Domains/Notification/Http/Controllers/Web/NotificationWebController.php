<?php

namespace App\Domains\Notification\Http\Controllers\Web;

use App\Domains\Notification\Models\NotificationPreference;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class NotificationWebController extends Controller
{
    protected function getActiveUser()
    {
        return Auth::user();
    }

    public function index(Request $request)
    {
        $user = $this->getActiveUser();
        $currentFilter = $request->query('filter', 'all');

        // Seed initial notifications if empty for rich interactive demonstration
        if ($user && $user->notifications()->count() === 0 && $user->id) {
            $user->notifications()->create([
                'id' => Str::uuid()->toString(),
                'type' => 'App\Notifications\EscrowMilestoneFunded',
                'data' => [
                    'title' => 'Escrow Milestone Funded',
                    'message' => 'Buyer deposited 13,000,000 ETB earnest for Contemporary Luxury Villa in Bole Atlas.',
                    'action_url' => '/dashboard/transactions/1',
                    'category' => 'deals',
                ],
                'read_at' => null,
                'created_at' => now()->subMinutes(12),
            ]);
            $user->notifications()->create([
                'id' => Str::uuid()->toString(),
                'type' => 'App\Notifications\InquiryReceived',
                'data' => [
                    'title' => 'New Diaspora Inquiry',
                    'message' => 'Dr. Meron Tadesse requested a verified site tour for Bole Atlas Luxury Villa.',
                    'action_url' => '/dashboard/crm',
                    'category' => 'inquiries',
                ],
                'read_at' => null,
                'created_at' => now()->subHours(2),
            ]);
            $user->notifications()->create([
                'id' => Str::uuid()->toString(),
                'type' => 'App\Notifications\ListingAuditPassed',
                'data' => [
                    'title' => 'Cadastral Deed Verified',
                    'message' => 'Title Deed Cert #AA-BOL-2026/8941 passed cadastral compliance review.',
                    'action_url' => '/dashboard/properties',
                    'category' => 'deals',
                ],
                'read_at' => now()->subDay(),
                'created_at' => now()->subDays(1),
            ]);
            $user->notifications()->create([
                'id' => Str::uuid()->toString(),
                'type' => 'App\Notifications\SystemSecurityAudit',
                'data' => [
                    'title' => 'Security Audit Completed',
                    'message' => 'Monthly two-factor security posture and RBAC permissions validated.',
                    'action_url' => '/dashboard/settings',
                    'category' => 'system',
                ],
                'read_at' => now()->subDays(2),
                'created_at' => now()->subDays(2),
            ]);
        }

        $query = $user->notifications()->latest();

        if ($currentFilter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($currentFilter === 'deals') {
            $query->where(function ($q) {
                $q->where('type', 'like', '%Milestone%')
                  ->orWhere('type', 'like', '%Escrow%')
                  ->orWhere('type', 'like', '%Deal%')
                  ->orWhere('type', 'like', '%Listing%');
            });
        } elseif ($currentFilter === 'inquiries') {
            $query->where(function ($q) {
                $q->where('type', 'like', '%Inquiry%')
                  ->orWhere('type', 'like', '%Message%')
                  ->orWhere('type', 'like', '%Lead%');
            });
        } elseif ($currentFilter === 'system') {
            $query->where(function ($q) {
                $q->where('type', 'like', '%Security%')
                  ->orWhere('type', 'like', '%Login%')
                  ->orWhere('type', 'like', '%System%')
                  ->orWhere('type', 'like', '%Password%');
            });
        }

        $notifications = $query->paginate(12)->withQueryString();

        $counts = [
            'all' => $user->notifications()->count(),
            'unread' => $user->unreadNotifications()->count(),
            'deals' => $user->notifications()->where(function ($q) {
                $q->where('type', 'like', '%Milestone%')
                  ->orWhere('type', 'like', '%Escrow%')
                  ->orWhere('type', 'like', '%Deal%')
                  ->orWhere('type', 'like', '%Listing%');
            })->count(),
            'inquiries' => $user->notifications()->where(function ($q) {
                $q->where('type', 'like', '%Inquiry%')
                  ->orWhere('type', 'like', '%Message%')
                  ->orWhere('type', 'like', '%Lead%');
            })->count(),
            'system' => $user->notifications()->where(function ($q) {
                $q->where('type', 'like', '%Security%')
                  ->orWhere('type', 'like', '%Login%')
                  ->orWhere('type', 'like', '%System%')
                  ->orWhere('type', 'like', '%Password%');
            })->count(),
        ];

        return view('dashboard.notifications.index', compact('notifications', 'currentFilter', 'counts'));
    }

    public function markAsRead(Request $request, $id)
    {
        $user = $this->getActiveUser();

        $notification = DatabaseNotification::where('notifiable_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'unread_count' => $user->unreadNotifications()->count(),
            ]);
        }

        if ($request->has('redirect')) {
            return redirect($request->input('redirect'));
        }

        return back()->with('success', __('Notification marked as read.'));
    }

    public function markAllAsRead(Request $request)
    {
        $user = $this->getActiveUser();
        $user->unreadNotifications->markAsRead();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'unread_count' => 0,
            ]);
        }

        return back()->with('success', __('All notifications marked as read.'));
    }

    public function destroy(Request $request, $id)
    {
        $user = $this->getActiveUser();

        $notification = DatabaseNotification::where('notifiable_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        $notification->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', __('Notification removed.'));
    }

    public function preferences()
    {
        $user = $this->getActiveUser();

        $preference = NotificationPreference::firstOrCreate(
            ['user_id' => $user->id],
            [
                'email_notifications' => true,
                'in_app_notifications' => true,
                'push_notifications' => true,
            ]
        );

        return view('dashboard.notifications.preferences', compact('preference'));
    }

    public function updatePreferences(Request $request)
    {
        $user = $this->getActiveUser();

        $preference = NotificationPreference::firstOrCreate(['user_id' => $user->id]);
        $preference->update([
            'email_notifications' => $request->boolean('email_notifications'),
            'in_app_notifications' => $request->boolean('in_app_notifications'),
            'push_notifications' => $request->boolean('push_notifications'),
        ]);

        return redirect()->route('dashboard.preferences')->with('success', __('Notification preferences saved.'));
    }
}

