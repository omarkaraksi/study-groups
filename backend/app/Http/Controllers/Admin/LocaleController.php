<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocaleController extends Controller
{
    /**
     * Supported locales for the application.
     */
    protected array $supportedLocales = ['en', 'ar'];

    /**
     * Switch the application locale.
     */
    public function switch(Request $request, string $locale): RedirectResponse
    {
        // Validate that the locale is supported
        if (! in_array($locale, $this->supportedLocales)) {
            abort(400, 'Unsupported locale');
        }

        // For authenticated users, persist the locale in their profile
        if (Auth::check()) {
            $user = Auth::user();
            
            // Ensure the user has a profile
            if (! $user->profile) {
                $user->profile()->create(['locale' => $locale]);
            } else {
                $user->profile()->update(['locale' => $locale]);
            }
            
            // Refresh the user to ensure the profile change is reflected
            $user->refresh();
        }

        // For guests, use session-based locale (existing middleware will handle this)
        // The SetLocale middleware already checks session, so we just need to store it
        $request->session()->put('locale', $locale);
        
        // Also set the locale for the current request so the redirect shows the correct locale
        app()->setLocale($locale);

        // Redirect back to the previous page
        return back();
    }

    /**
     * Get the current locale information for the view.
     */
    public function getCurrentLocale(): array
    {
        $currentLocale = app()->getLocale();
        
        return [
            'current' => $currentLocale,
            'supported' => $this->supportedLocales,
            'is_rtl' => $currentLocale === 'ar',
            'is_ltr' => $currentLocale === 'en',
        ];
    }
}