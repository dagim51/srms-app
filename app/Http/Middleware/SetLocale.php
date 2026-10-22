<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Supported application locales.
     *
     * @var array<string>
     */
    protected array $supportedLocales = ['en', 'am', 'om'];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        // Apply locale to Laravel and Carbon
        App::setLocale($locale);
        if (class_exists(Carbon::class)) {
            Carbon::setLocale($locale);
        }

        // Store in session if available
        if ($request->hasSession() && $request->session()->get('locale') !== $locale) {
            $request->session()->put('locale', $locale);
        }

        $response = $next($request);

        // Attach persistent cookie to response if not already set by response
        if ($response instanceof Response && method_exists($response, 'withCookie')) {
            $hasCookie = false;
            foreach ($response->headers->getCookies() as $c) {
                if (in_array($c->getName(), ['srms_locale', 'locale'], true)) {
                    $hasCookie = true;
                    break;
                }
            }

            if (! $hasCookie) {
                $currentCookie = $request->cookie('srms_locale') ?? $request->cookie('locale');
                if ($currentCookie !== $locale) {
                    $response->withCookie(cookie()->forever('srms_locale', $locale));
                }
            }
        }

        return $response;
    }

    /**
     * Resolve the active locale based on request, session, user, cookie, and defaults.
     */
    protected function resolveLocale(Request $request): string
    {
        // 1. Explicit route parameter (/locale/{locale}) or query parameter (?locale=am or ?lang=am)
        $routeLocale = $request->route('locale');
        if ($routeLocale && in_array($routeLocale, $this->supportedLocales, true)) {
            return $routeLocale;
        }

        $queryLocale = $request->query('locale') ?? $request->query('lang');
        if ($queryLocale && in_array($queryLocale, $this->supportedLocales, true)) {
            return $queryLocale;
        }

        // 2. Session preference
        if ($request->hasSession() && $request->session()->has('locale')) {
            $sessionLocale = $request->session()->get('locale');
            if (in_array($sessionLocale, $this->supportedLocales, true)) {
                return $sessionLocale;
            }
        }

        // 3. Authenticated user saved preference
        if ($request->user() && ! empty($request->user()->locale)) {
            $userLocale = $request->user()->locale;
            if (in_array($userLocale, $this->supportedLocales, true)) {
                return $userLocale;
            }
        }

        // 4. Persistent cookie (support both srms_locale and locale)
        $cookieLocale = $request->cookie('srms_locale') ?? $request->cookie('locale');
        if ($cookieLocale && in_array($cookieLocale, $this->supportedLocales, true)) {
            return $cookieLocale;
        }

        // 5. Fallback to application default
        return Config::get('app.locale', 'en');
    }
}
