<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * Sets the application locale based on:
     * 1. Explicitly requested valid locale (?locale=ar)
     * 2. Authenticated user's preferred locale (user_profiles.locale)
     * 3. Session-based locale
     * 4. Accept-Language HTTP header
     * 5. Default locale (en)
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);
        
        if ($locale !== null && in_array($locale, config('app.supported_locales', ['en']))) {
            App::setLocale($locale);
        }

        return $next($request);
    }

    /**
     * Resolve the locale for the request.
     */
    protected function resolveLocale(Request $request): ?string
    {
        // 1. Check for explicit locale override via query parameter
        if ($request->has('locale')) {
            $explicitLocale = $request->query('locale');
            if (is_string($explicitLocale) && $this->isSupportedLocale($explicitLocale)) {
                return $explicitLocale;
            }
        }

        // 2. Check authenticated user's preferred locale
        if (Auth::check()) {
            $userLocale = Auth::user()->profile?->locale;
            if ($userLocale !== null && $this->isSupportedLocale($userLocale)) {
                return $userLocale;
            }
        }

        // 3. Check session-based locale (for guests and persisted user preference)
        if ($request->hasSession()) {
            $sessionLocale = $request->session()->get('locale');
            if ($sessionLocale !== null && $this->isSupportedLocale($sessionLocale)) {
                return $sessionLocale;
            }
        }

        // 4. Check Accept-Language header
        $acceptLanguage = $request->header('Accept-Language');
        if ($acceptLanguage !== null) {
            $parsedLocale = $this->parseAcceptLanguage($acceptLanguage);
            if ($parsedLocale !== null && $this->isSupportedLocale($parsedLocale)) {
                return $parsedLocale;
            }
        }

        // 5. Default to application locale
        return config('app.locale', 'en');
    }

    /**
     * Check if a locale is supported.
     */
    protected function isSupportedLocale(string $locale): bool
    {
        return in_array($locale, config('app.supported_locales', ['en']));
    }

    /**
     * Parse Accept-Language header to extract the first locale.
     *
     * @example: "en-US,en;q=0.9,ar;q=0.8" -> "en"
     */
    protected function parseAcceptLanguage(string $acceptLanguage): ?string
    {
        if (empty($acceptLanguage)) {
            return null;
        }

        $parts = explode(',', $acceptLanguage);
        $firstPart = trim($parts[0] ?? '');

        if (empty($firstPart)) {
            return null;
        }

        // Extract locale code (before '-', ';', or end of string)
        // e.g., "en-US" -> "en", "ar" -> "ar", "en;q=0.9" -> "en"
        $locale = preg_match('/^([a-z]{2})/', $firstPart, $matches) ? $matches[1] : null;

        return $locale ?: null;
    }
}
