<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->validateCsrfTokens(except: ['webhooks/*', 'api/chat', 'api/newsletter']);
        $middleware->encryptCookies(except: ['wc_locale', 'wc_country', 'cookieConsent', 'wc_pwa_dismissed', 'wc_visitor_id']);

        $middleware->redirectGuestsTo('/login');
        $middleware->redirectUsersTo('/client');

        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\TrackReferral::class,
            \App\Http\Middleware\TrackUtmParameters::class,
            \App\Http\Middleware\ContentSecurityPolicy::class,
        ]);

        $middleware->alias([
            'auth' => \App\Http\Middleware\EnsureAuthenticated::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'role' => \App\Http\Middleware\EnsureRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Report to Sentry when available (install sentry/sentry-laravel to activate)
        if (class_exists(\Sentry\Laravel\Integration::class)) {
            $exceptions->reportable(function (\Throwable $e) {
                \Sentry\Laravel\Integration::captureUnhandledException($e);
            });
        }
    })->create();
