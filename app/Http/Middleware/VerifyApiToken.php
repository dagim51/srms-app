<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ApiToken;
use Illuminate\Support\Facades\Auth;

class VerifyApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // First, check if already authenticated via session
        if (Auth::check()) {
            return $next($request);
        }

        // Check for bearer token
        $token = $request->bearerToken();
        if ($token) {
            $apiToken = ApiToken::where('token', hash('sha256', $token))
                ->where(function ($query) {
                    $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })->first();

            if ($apiToken && $apiToken->user) {
                Auth::login($apiToken->user);
                $apiToken->update(['last_used_at' => now()]);
                return $next($request);
            }
        }

        return response()->json(['error' => ['code' => 'UNAUTHENTICATED', 'message' => 'Unauthenticated.']], 401);
    }
}
