<?php

namespace App\Domains\Identity\Http\Controllers\Api\V1;

use App\Domains\Identity\Http\Controllers\Controller;
use App\Domains\Identity\Http\Requests\Auth\ForgotPasswordRequest;
use App\Domains\Identity\Http\Requests\Auth\LoginRequest;
use App\Domains\Identity\Http\Requests\Auth\RegisterRequest;
use App\Domains\Identity\Http\Requests\Auth\ResetPasswordRequest;
use App\Domains\Identity\Http\Resources\UserResource;
use App\Domains\Identity\Models\ApiToken;
use App\Domains\Identity\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

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
                'user' => new UserResource($user),
                'token' => $plainTextToken,
            ],
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        if (! Auth::attempt($validated)) {
            return response()->json([
                'error' => [
                    'code' => 'INVALID_CREDENTIALS',
                    'message' => 'Invalid credentials provided.',
                ],
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
                'user' => new UserResource($user),
                'token' => $plainTextToken,
            ],
        ]);
    }

    public function logout()
    {
        $token = request()->bearerToken();
        if ($token) {
            ApiToken::where('token', hash('sha256', $token))->delete();
        }

        Auth::guard('web')->logout();

        // Return 204 No Content for successful logout
        return response()->json(null, 204);
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $user = User::where('email', $request->validated('email'))->first();
        if (! $user) {
            // Return 200 to prevent email enumeration
            return response()->json(['message' => 'If an account exists, a reset link has been sent.']);
        }

        $token = Password::createToken($user);

        // In a real app, send email here. We just return it for testing purposes.
        return response()->json(['message' => 'Reset token generated.', 'token' => $token]);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $validated = $request->validated();

        $status = Password::reset(
            $validated,
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                // Invalidate all API tokens upon password reset
                ApiToken::where('user_id', $user->id)->delete();
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Password reset successfully.']);
        }

        return response()->json(['error' => ['message' => 'Invalid token or email.']], 400);
    }
}
