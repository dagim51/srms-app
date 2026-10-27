<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class IdempotencyMiddleware
{
    /**
     * Handle an incoming request with idempotency protection.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only enforce on mutating requests
        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            return $next($request);
        }

        $idempotencyKey = $request->header('Idempotency-Key') ?? $request->header('X-Idempotency-Key') ?? $request->input('idempotency_key');
        if (! $idempotencyKey) {
            return $next($request);
        }

        $userId = $request->user()?->id ?? $request->ip();
        $cacheKey = "idempotency:{$userId}:{$idempotencyKey}";

        $cached = Cache::get($cacheKey);
        if ($cached) {
            if (is_array($cached) && ($cached['status'] ?? null) === 'processing') {
                return response()->json([
                    'error' => [
                        'code' => 'CONCURRENT_REQUEST',
                        'message' => 'A request with this idempotency key is currently processing.',
                    ],
                ], 409);
            }

            if (is_array($cached) && isset($cached['content'], $cached['status_code'])) {
                return response($cached['content'], $cached['status_code'])
                    ->withHeaders(array_merge($cached['headers'] ?? [], ['X-Cache-Lookup' => 'HIT-IDEMPOTENT']));
            }
        }

        // Lock processing state for 60 seconds
        Cache::put($cacheKey, ['status' => 'processing'], 60);

        try {
            $response = $next($request);

            // If response is successful, cache the result for 24 hours
            if ($response->isSuccessful()) {
                Cache::put($cacheKey, [
                    'status' => 'completed',
                    'status_code' => $response->getStatusCode(),
                    'content' => $response->getContent(),
                    'headers' => ['Content-Type' => $response->headers->get('Content-Type') ?? 'application/json'],
                ], 86400);
            } else {
                // If failed, release lock so the client can retry
                Cache::forget($cacheKey);
            }

            return $response;
        } catch (\Throwable $e) {
            Cache::forget($cacheKey);
            throw $e;
        }
    }
}
