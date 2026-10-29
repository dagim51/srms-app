<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Clear stale intended admin URL if user is not an administrator
            $intended = session()->get('url.intended');
            if ($intended && str_contains($intended, '/dashboard/admin') && ! Auth::user()->hasRole('Administrator')) {
                session()->forget('url.intended');
            }

            return redirect()->intended('/dashboard');
        }

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'intent' => [
                'required', 
                'string', 
                'in:buyer,tenant,traveler,seller,landlord,host,agent,developer,property_manager,service_provider,buyer_tenant,owner_host,professional'
            ],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'intent' => $validated['intent'],
            'password' => Hash::make($validated['password']),
        ]);

        // Direct Canonical Role Binding across all SRS user types
        $intentRoleMap = [
            // Granular Specific User Types
            'buyer' => 'Buyer',
            'tenant' => 'Tenant',
            'traveler' => 'Guest / Traveler',
            'seller' => 'Property Owner',
            'landlord' => 'Landlord',
            'host' => 'Host',
            'agent' => 'Agent',
            'developer' => 'Developer',
            'property_manager' => 'Property Manager',
            'service_provider' => 'Professional Service Provider',

            // Backward-compatibility aliases
            'buyer_tenant' => 'Buyer',
            'owner_host' => 'Property Owner',
            'professional' => 'Agent',
        ];

        $targetRole = $intentRoleMap[$validated['intent']] ?? 'Buyer';
        if (Role::where('name', $targetRole)->exists()) {
            $user->assignRole($targetRole);
        }

        Auth::login($user);

        session()->flash('registered_intent', $validated['intent']);
        session()->flash('welcome_toast', true);

        return redirect('/dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
