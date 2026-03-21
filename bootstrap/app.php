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
        $middleware->validateCsrfTokens(except: ['webhooks/*', 'api/chat']);

        $middleware->redirectGuestsTo('/login');
        $middleware->redirectUsersTo('/client');

        $middleware->web(append: [
            \App\Http\Middleware\TrackReferral::class,
        ]);

        $middleware->alias([
            'auth' => \App\Http\Middleware\EnsureAuthenticated::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
