<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Supported application locales.
     *
     * @var array<string>
     */
    protected array $supportedLocales = ['en', 'am'];

    /**
     * Switch application language and redirect back preserving current page and state.
     */
    public function switch(Request $request, ?string $locale = null): RedirectResponse
    {
        $targetLocale = $locale ?? $request->input('locale', 'en');

        if (!in_array($targetLocale, $this->supportedLocales, true)) {
            $targetLocale = 'en';
        }

        // Store in session
        $request->session()->put('locale', $targetLocale);

        // If authenticated, update user profile preference
        if ($request->user()) {
            $request->user()->update(['locale' => $targetLocale]);
        }

        return redirect()->back(fallback: '/')->withCookie(cookie()->forever('srms_locale', $targetLocale));
    }
}
