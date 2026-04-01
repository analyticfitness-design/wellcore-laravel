<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CoachImpersonateController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\ImpersonateController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\WebhookController;
use App\Livewire\Checkout;
use App\Livewire\Public\ClientIntakeForm;
use App\Livewire\TestDashboard;
use App\Models\AuthToken;
use App\Models\Client;
use App\Services\FeatureFlagService;
use Illuminate\Support\Facades\Route;

// Health check endpoint (public, no auth — used by uptime monitors and load balancers)
Route::get('/health', function () {
    $checks = [
        'app' => true,
        'database' => false,
        'cache' => false,
    ];

    try {
        DB::connection()->getPdo();
        $checks['database'] = true;
    } catch (Exception $e) {
    }

    try {
        Cache::store()->put('health_check', true, 10);
        $checks['cache'] = Cache::store()->get('health_check') === true;
    } catch (Exception $e) {
    }

    $allHealthy = ! in_array(false, $checks);

    return response()->json([
        'status' => $allHealthy ? 'healthy' : 'degraded',
        'checks' => $checks,
        'timestamp' => now()->toISOString(),
        'version' => config('wellcore.version', '2.0.0'),
    ], $allHealthy ? 200 : 503);
})->name('health');

// Sitemap (public, no auth required)
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// Chatbot API (public, no auth required)
Route::post('/api/chat', [ChatController::class, 'send'])->name('api.chat')->middleware('throttle:chat');

// Public marketing pages
Route::get('/', function () {
    return view('public.home');
})->name('home');
Route::get('/planes', function () {
    return view('public.planes');
})->name('planes');
Route::get('/nosotros', function () {
    return view('public.nosotros');
})->name('nosotros');
Route::get('/faq', function () {
    return view('public.faq');
})->name('faq');
Route::get('/metodo', function () {
    return view('public.metodo');
})->name('metodo');
Route::get('/proceso', function () {
    return view('public.proceso');
})->name('proceso');
Route::get('/reto-rise', function () {
    return view('public.rise');
})->name('reto-rise');
Route::get('/coaches', function () {
    return view('public.coaches');
})->name('coaches');
Route::get('/coaches/apply', fn () => view('vue'))->name('coaches.apply');

// Legal pages
Route::get('/terminos', fn () => view('public.legal.terminos'))->name('terminos');
Route::get('/privacidad', fn () => view('public.legal.privacidad'))->name('privacidad');
Route::get('/politica-cookies', fn () => view('public.legal.cookies'))->name('cookies');
Route::get('/reembolsos', fn () => view('public.legal.reembolso'))->name('reembolsos');

// April 2026 Launch event landing page
Route::get('/lanzamiento', fn () => view('public.lanzamiento'))->name('lanzamiento');

// Coach Silvia landing
Route::get('/fit', fn () => view('public.fit'))->name('fit');

// Presencial
Route::get('/presencial', fn () => view('public.presencial'))->name('presencial');
Route::get('/presencial/inscripcion', fn () => view('vue'))->name('presencial.form');

// RISE Enrollment
Route::get('/rise-enroll', fn () => view('vue'))->name('rise.enroll');

// Invitation intake form — public, guest-only
Route::get('/unirse/{code}', ClientIntakeForm::class)
    ->name('invite.intake');

Route::get('/inscripcion', fn () => view('vue'))->name('inscripcion')->middleware('throttle:inscription');
Route::get('/pagar', Checkout::class)->name('pagar');
Route::get('/pago-exitoso', [PaymentController::class, 'result'])->name('pago-exitoso');
Route::get('/pago-confirmado', [PaymentController::class, 'result'])->name('pago-confirmado');

// Blog
Route::get('/blog', function () {
    return view('public.blog.index');
})->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

// Shop routes (public, no auth required) — hidden until shop is production-ready
if (FeatureFlagService::isEnabled('shop')) {
    Route::get('/tienda/{any?}', fn () => view('vue'))->where('any', '.*');
}

// Newsletter API (public, no auth required)
Route::post('/api/newsletter', [NewsletterController::class, 'subscribe'])->name('api.newsletter')->middleware('throttle:newsletter');

// Webhook routes (excluded from CSRF via bootstrap/app.php)
Route::post('/webhooks/wompi', [WebhookController::class, 'wompi'])->name('webhooks.wompi')->middleware('throttle:webhook');

// Auth routes (guest only — redirect if already logged in)
Route::middleware('guest:wellcore')->group(function () {
    Route::get('/login', fn () => view('vue'))->name('login')->middleware('throttle:login');
    Route::get('/forgot-password', fn () => view('vue'))->name('password.request');
    Route::get('/reset-password/{token}', fn () => view('vue'))->name('password.reset');
    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
});

// Vue SPA portal routes — auth enforced by Vue Router + API middleware, not Laravel
Route::get('/client/{any?}', fn () => view('vue'))->where('any', '.*');
Route::get('/rise/{any?}', fn () => view('vue'))->where('any', '.*');
Route::get('/coach/{any?}', fn () => view('vue'))->where('any', '.*');
Route::get('/admin/{any?}', fn () => view('vue'))->where('any', '.*');

// Session logout + impersonation (POST routes — still need auth)
Route::middleware('auth:wellcore')->group(function () {
    Route::post('/logout', function () {
        $token = session('wc_token');
        if ($token) {
            AuthToken::where('token', $token)->delete();
        }
        session()->flush();

        return redirect('/login');
    })->name('logout');

    Route::post('/admin/impersonate/{clientId}', [ImpersonateController::class, 'start'])
        ->name('admin.impersonate.start')
        ->middleware('role:superadmin,admin')
        ->whereNumber('clientId');

    Route::post('/admin/coach-impersonate/{adminId}', [CoachImpersonateController::class, 'start'])
        ->name('coach.impersonate.start')
        ->middleware('role:superadmin')
        ->whereNumber('adminId');
});

// Impersonation stop — outside auth group (session is client session when stopping)
Route::post('/admin/impersonate/stop', [ImpersonateController::class, 'stop'])->name('admin.impersonate.stop');
Route::post('/admin/coach-impersonate/stop', [CoachImpersonateController::class, 'stop'])->name('coach.impersonate.stop');

// Backwards-compat: /v/* redirects to /* without the /v prefix (301 permanent)
Route::get('/v/{any}', fn ($any) => redirect('/'.$any, 301))->where('any', '.*');

// DEV ONLY routes — disabled in production
if (app()->environment('local', 'testing')) {
    Route::get('/test', TestDashboard::class);
    Route::get('/dev-login/{clientId}', function ($clientId) {
        $client = Client::find($clientId);
        if (! $client) {
            return redirect('/login');
        }
        $token = AuthToken::where('user_id', $clientId)->where('user_type', 'client')->where('expires_at', '>', now())->first();
        if (! $token) {
            return redirect('/login');
        }
        session(['wc_token' => $token->token, 'wc_user_type' => 'client', 'wc_user_id' => $clientId]);

        return redirect('/client');
    });
}
