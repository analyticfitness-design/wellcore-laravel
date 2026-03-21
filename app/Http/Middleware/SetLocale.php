<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->cookie('wc_locale')
            ?? $request->header('Accept-Language', 'es');

        // Extract primary language
        $locale = substr($locale, 0, 2);

        if (in_array($locale, ['es', 'en'])) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
