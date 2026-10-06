<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ApiToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $plainTextToken = Str::random(40);
        ApiToken::create([
            'user_id' => $user->id,
            'name' => 'auth_token',
            'token' => hash('sha256', $plainTextToken),
        ]);

        Auth::login($user);

        return response()->json([
            'data' => [
                'user' => $user,
                'token' => $plainTextToken
            ]
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($validated)) {
            return response()->json([
                'error' => [
                    'code' => 'INVALID_CREDENTIALS',
                    'message' => 'Invalid credentials provided.'
                ]
            ], 401);
        }

        $user = Auth::user();
        
        $plainTextToken = Str::random(40);
        ApiToken::create([
            'user_id' => $user->id,
            'name' => 'auth_token',
            'token' => hash('sha256', $plainTextToken),
        ]);

        return response()->json([
            'data' => [
                'user' => $user,
                'token' => $plainTextToken
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $token = $request->bearerToken();
        if ($token) {
            ApiToken::where('token', hash('sha256', $token))->delete();
        }

        Auth::guard('web')->logout();
        
        // Return 204 No Content for successful logout
        return response()->json(null, 204);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            // Return 200 to prevent email enumeration
            return response()->json(['message' => 'If an account exists, a reset link has been sent.']);
        }

        $token = \Illuminate\Support\Facades\Password::createToken($user);
        
        // In a real app, send email here. We just return it for testing purposes.
        return response()->json(['message' => 'Reset token generated.', 'token' => $token]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = \Illuminate\Support\Facades\Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));
                
                $user->save();
                
                // Invalidate all API tokens upon password reset
                ApiToken::where('user_id', $user->id)->delete();
            }
        );

        if ($status == \Illuminate\Support\Facades\Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Password reset successfully.']);
        }

        return response()->json(['error' => ['message' => 'Invalid token or email.']], 400);
    }
}
