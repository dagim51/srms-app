<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check for bearer token first if present in request headers
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

            return response()->json(['error' => ['code' => 'UNAUTHENTICATED', 'message' => 'Unauthenticated.']], 401);
        }

        // Otherwise check if already authenticated via web session
        if (Auth::check()) {
            return $next($request);
        }

        return response()->json(['error' => ['code' => 'UNAUTHENTICATED', 'message' => 'Unauthenticated.']], 401);
    }
}
