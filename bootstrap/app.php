<?php

use App\Http\Middleware\CheckFeatureFlag;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\EnforceSubscriptionLimits;
use App\Http\Middleware\IdempotencyMiddleware;
use App\Http\Middleware\OrganizationScope;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\VerifyApiToken;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SetLocale::class,
        ]);
        // Attach the named "api" rate limiter (defined in AppServiceProvider)
        // to every route in routes/api.php.
        $middleware->throttleApi();
        $middleware->alias([
            'api.token' => VerifyApiToken::class,
            'org.scope' => OrganizationScope::class,
            'idempotency' => IdempotencyMiddleware::class,
            'role' => CheckRole::class,
            'permission' => CheckPermission::class,
            'subscription.limits' => EnforceSubscriptionLimits::class,
            'feature' => CheckFeatureFlag::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
