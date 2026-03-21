<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        // Read raw cookie (JS sets it unencrypted, so bypass Laravel's cookie decryption)
        $locale = $_COOKIE['wc_locale'] ?? null;

        // Fallback to Accept-Language header
        if (!$locale) {
            $locale = substr($request->header('Accept-Language', 'es'), 0, 2);
        }

        if (in_array($locale, ['es', 'en'])) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
