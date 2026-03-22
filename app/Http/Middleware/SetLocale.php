<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        // Read raw cookie (JS sets it unencrypted, so bypass Laravel's cookie decryption)
        // Default to Spanish — English only if the user explicitly sets the cookie
        $locale = $_COOKIE['wc_locale'] ?? 'es';

        if (in_array($locale, ['es', 'en'])) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
