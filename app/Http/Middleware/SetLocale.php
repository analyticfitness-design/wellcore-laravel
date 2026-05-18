<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    private const SUPPORTED = ['es', 'en'];

    public function handle(Request $request, Closure $next)
    {
        $locale = $this->resolveLocale($request);

        if (in_array($locale, self::SUPPORTED, true)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }

    /**
     * Prioridad:
     * 1. Usuario autenticado (Client o Admin) con columna locale → fuente de verdad.
     * 2. Cookie wc_locale (fallback para anónimos y compatibilidad legacy).
     * 3. Default 'es'.
     */
    private function resolveLocale(Request $request): string
    {
        $user = $request->user('wellcore') ?? $request->user();

        if ($user && isset($user->locale) && in_array($user->locale, self::SUPPORTED, true)) {
            return $user->locale;
        }

        $cookie = $request->cookie('wc_locale', 'es');

        return in_array($cookie, self::SUPPORTED, true) ? $cookie : 'es';
    }
}
