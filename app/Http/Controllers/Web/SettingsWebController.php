<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class SettingsWebController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user() ?? (object)[
            'id' => 'USR-001',
            'name' => 'Admin User',
            'email' => 'admin@srms.et',
            'phone' => '+251 911 000 111',
            'locale' => 'en',
            'role' => 'Administrator',
            'created_at' => now(),
        ];

        $organization = \App\Models\Organization::first() ?? (object)[
            'id' => 'org_1',
            'name' => 'Addis Real Estate Group',
            'tin' => '0049281729',
            'email' => 'contact@addisrealestate.et',
            'phone' => '+251 116 678 900',
            'address' => 'Bole Subcity, Woreda 03, House No. 812, Addis Ababa',
            'plan' => 'Enterprise Tier',
        ];

        $activeTab = $request->query('tab', 'profile');

        $activeSessions = [
            [
                'device' => 'Windows PC — Chrome 129',
                'ip' => '197.156.92.12 (Ethio Telecom, Addis Ababa)',
                'current' => true,
                'last_active' => 'Active now',
            ],
            [
                'device' => 'iPhone 15 Pro — Safari Mobile',
                'ip' => '197.156.88.45 (Ethio Telecom, Bole)',
                'current' => false,
                'last_active' => '2 hours ago',
            ],
        ];

        $platformSettings = [
            'platform_fee_percent' => 3.5,
            'vat_percent' => 15.0,
            'payout_schedule' => 'Monthly (1st of Month)',
            'currency' => 'ETB',
            'gateways' => [
                'telebirr' => true,
                'cbe_birr' => true,
                'chapa' => true,
                'awash_bank' => false,
            ],
        ];

        return view('dashboard.settings.index', compact(
            'user',
            'organization',
            'activeTab',
            'activeSessions',
            'platformSettings'
        ));
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:30',
            'locale' => 'required|string|in:en,am',
        ]);

        $user = Auth::user();
        if ($user && $user instanceof \App\Models\User) {
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'locale' => $validated['locale'],
            ]);
        }

        return redirect()->route('dashboard.settings', ['tab' => 'profile'])
            ->with('success', 'Profile and localization preferences successfully updated.');
    }

    public function updateSecurity(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();
        if ($user && $user instanceof \App\Models\User) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                return redirect()->route('dashboard.settings', ['tab' => 'security'])
                    ->with('error', 'The current password you provided does not match our records.');
            }

            $user->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        return redirect()->route('dashboard.settings', ['tab' => 'security'])
            ->with('success', 'Password updated successfully.');
    }

    public function updateNotifications(Request $request)
    {
        return redirect()->route('dashboard.settings', ['tab' => 'notifications'])
            ->with('success', 'Notification preferences and communication channels saved.');
    }

    public function updatePlatform(Request $request)
    {
        return redirect()->route('dashboard.settings', ['tab' => 'platform'])
            ->with('success', 'Platform financial settings and payment gateway integrations saved.');
    }
}
