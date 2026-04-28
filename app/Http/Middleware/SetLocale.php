<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        // wc_locale is in encryptCookies except list — safe to read directly via request
        $locale = $request->cookie('wc_locale', 'es');

        if (in_array($locale, ['es', 'en'])) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
