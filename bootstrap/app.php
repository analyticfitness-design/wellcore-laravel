<?php

use App\Http\Middleware\ApiBearerAuth;
use App\Http\Middleware\CheckPlanLock;
use App\Http\Middleware\ContentSecurityPolicy;
use App\Http\Middleware\EnsureAuthenticated;
use App\Http\Middleware\EnsureCoachContractAccepted;
use App\Http\Middleware\EnsureCompleteBrandProfile;
use App\Http\Middleware\EnsurePlan;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\SetAssetCacheHeaders;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\TrackReferral;
use App\Http\Middleware\TrackUtmParameters;
use App\Http\Middleware\UpdateLastSeen;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Sentry\Laravel\Integration;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(
            at: ['10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16', '127.0.0.1'],
            headers: Request::HEADER_X_FORWARDED_FOR,
        );

        $middleware->validateCsrfTokens(except: ['webhooks/*', 'api/chat', 'api/newsletter']);
        $middleware->encryptCookies(except: ['wc_locale', 'wc_country', 'cookieConsent', 'wc_pwa_dismissed', 'wc_visitor_id']);

        $middleware->redirectGuestsTo('/login');
        $middleware->redirectUsersTo('/client');

        $middleware->web(append: [
            SetLocale::class,
            TrackReferral::class,
            TrackUtmParameters::class,
            ContentSecurityPolicy::class,
        ]);

        // Global (all routes) — cache headers for static/hashed assets.
        // Safe no-op for HTML responses; only sets Cache-Control on asset paths.
        $middleware->append(SetAssetCacheHeaders::class);

        $middleware->alias([
            'auth' => EnsureAuthenticated::class,
            'guest' => RedirectIfAuthenticated::class,
            'role' => EnsureRole::class,
            'ensure.plan' => EnsurePlan::class,
            'plan.lock' => CheckPlanLock::class,
            'api.bearer' => ApiBearerAuth::class,
            'coach.contract' => EnsureCoachContractAccepted::class,
            'complete-brand-profile' => EnsureCompleteBrandProfile::class,
        ]);

        // Track real client activity — runs after response, skips impersonation sessions.
        // Self-gates: only acts on /api/v/client/* routes.
        $middleware->append(UpdateLastSeen::class);
    })
    ->withBroadcasting(
        channels: __DIR__.'/../routes/channels.php',
        attributes: ['middleware' => ['auth:wellcore']],
    )
    ->withExceptions(function (Exceptions $exceptions): void {
        // Report to Sentry when available (install sentry/sentry-laravel to activate)
        if (class_exists(Integration::class)) {
            $exceptions->reportable(function (Throwable $e) {
                Integration::captureUnhandledException($e);
            });
        }

        // Rutas /api/v/*: cualquier 500 sin manejar devuelve mensaje claro al usuario
        // y loggea el detalle real con contexto. En local/testing sí exponemos detalle.
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/v/*') && ! $request->is('api/ejercicios/*')) {
                return null;
            }

            // Excepciones HTTP conocidas (403/404/422/419 etc.) — dejamos que Laravel las maneje.
            if ($e instanceof HttpExceptionInterface) {
                return null;
            }

            // ThrottleRequests lanza HttpResponseException (no HttpExceptionInterface) — pasar directo.
            if ($e instanceof HttpResponseException) {
                return null;
            }

            if ($e instanceof ValidationException) {
                return null;
            }

            if ($e instanceof AuthenticationException) {
                return null;
            }

            Log::error('API unhandled exception', [
                'path' => $request->path(),
                'method' => $request->method(),
                'user_id' => optional($request->user())->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $payload = [
                'message' => 'Tuvimos un problema. Intenta de nuevo en unos segundos.',
            ];

            if (! app()->environment('production')) {
                $payload['debug'] = [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ];
            }

            return response()->json($payload, 500);
        });
    })->create();
