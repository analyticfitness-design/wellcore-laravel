<?php

use App\Http\Controllers\Admin\AdminPaymentProofViewController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CoachImpersonateController;
use App\Http\Controllers\CoachInvitationPublicController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\ImpersonateController;
use App\Http\Controllers\Media\GifController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Public\CoachesController;
use App\Http\Controllers\Public\FitController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\MetodoController;
use App\Http\Controllers\Public\NosotrosController;
use App\Http\Controllers\Public\PlanesController;
use App\Http\Controllers\Public\PresencialController;
use App\Http\Controllers\Public\ProcesoController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\WebhookController;
use App\Livewire\Checkout;
use App\Livewire\TestDashboard;
use App\Models\AuthToken;
use App\Models\Client;
use App\Services\FeatureFlagService;
use Illuminate\Support\Facades\Route;

// Serve progress photos — local first, then proxy from PHP vanilla service via Docker network
Route::get('/uploads/photos/{filename}', function (string $filename) {
    // Security: only allow safe filenames (no path traversal)
    if (! preg_match('/^[\w\-\.]+\.(jpg|jpeg|png|webp|gif)$/i', $filename)) {
        abort(404);
    }

    $localPath = public_path('uploads/photos/'.$filename);
    if (file_exists($localPath)) {
        return response()->file($localPath);
    }

    // Proxy from PHP vanilla service on Docker internal network
    $vanillaHosts = ['wellcorefitness', 'wellcorefitness.wellcorefitness'];
    foreach ($vanillaHosts as $host) {
        try {
            $url = "http://{$host}/uploads/photos/{$filename}";
            $ctx = stream_context_create(['http' => ['timeout' => 3, 'ignore_errors' => true]]);
            $content = @file_get_contents($url, false, $ctx);
            if ($content !== false && strlen($content) > 0) {
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                $mime = match ($ext) {
                    'jpg', 'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'webp' => 'image/webp',
                    default => 'image/jpeg',
                };

                return response($content, 200)->header('Content-Type', $mime);
            }
        } catch (Throwable) {
        }
    }

    abort(404);
})->where('filename', '[^/]+');


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

// robots.txt served as a route so tests can hit it (nginx serves the static file in production)
Route::get('/robots.txt', function () {
    return response(file_get_contents(public_path('robots.txt')), 200, ['Content-Type' => 'text/plain']);
})->name('robots');

// Chatbot API (public, no auth required)
Route::post('/api/chat', [ChatController::class, 'send'])->name('api.chat')->middleware('throttle:chat');

// Public marketing pages
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/planes', [PlanesController::class, 'index'])->name('planes');
Route::get('/nosotros', [NosotrosController::class, 'index'])->name('nosotros');
Route::get('/faq', function () {
    return view('public.faq');
})->name('faq');
Route::get('/metodo', [MetodoController::class, 'index'])->name('metodo');
Route::get('/proceso', [ProcesoController::class, 'index'])->name('proceso');
// RISE publico cerrado: redirige al home (clientes RISE existentes siguen con acceso en /rise)
Route::get('/reto-rise', fn () => redirect('/', 301))->name('reto-rise');
Route::get('/coaches', [CoachesController::class, 'index'])->name('coaches');
Route::get('/coaches/apply', fn () => view('vue'))->name('coaches.apply');

// Legal pages
Route::get('/terminos', fn () => view('public.legal.terminos'))->name('terminos');
Route::get('/privacidad', fn () => view('public.legal.privacidad'))->name('privacidad');
Route::get('/politica-cookies', fn () => view('public.legal.cookies'))->name('cookies');
Route::get('/reembolsos', fn () => view('public.legal.reembolso'))->name('reembolsos');

// April 2026 Launch event landing page
Route::get('/lanzamiento', fn () => view('public.lanzamiento'))->name('lanzamiento');

// Coach Silvia landing
Route::get('/fit', [FitController::class, 'index'])->name('fit');

// Presencial
Route::get('/presencial', [PresencialController::class, 'index'])->name('presencial');
Route::get('/presencial/inscripcion', fn () => view('vue'))->name('presencial.form');

// RISE Enrollment cerrado — inscripciones no abiertas al publico
Route::get('/rise-enroll', fn () => redirect('/planes', 301))->name('rise.enroll');

// Invitation intake form — Vue SPA
Route::get('/unirse/{code}', fn () => view('vue'))
    ->name('invite.intake')
    ->where('code', '[A-Za-z0-9]{12}');

Route::get('/inscripcion', fn () => view('vue'))->name('inscripcion')->middleware('throttle:inscription');
Route::get('/pagar', Checkout::class)->name('pagar');
Route::get('/renovar', Checkout::class)
    ->name('renovar')
    ->middleware('auth:wellcore')
    ->defaults('renewal', 1);
Route::get('/pago-exitoso', [PaymentController::class, 'result'])->name('pago-exitoso');
Route::get('/pago-confirmado', [PaymentController::class, 'result'])->name('pago-confirmado');

// Blog
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
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
    // Sprint 5 — login Livewire v2 (iOS-feel) ya en /login.
    // Migración SPA Vue → Livewire cerró 19 gaps de paridad funcional.
    // Backups en app/Livewire/Auth/backups/ y resources/views/livewire/auth/backups/.
    Route::get('/login', App\Livewire\Auth\Login::class)
        ->name('login')
        ->middleware('throttle:login');

    Route::get('/forgot-password', fn () => view('vue'))->name('password.request');
    Route::get('/reset-password/{token}', fn () => view('vue'))->name('password.reset');
    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
});

// Vue SPA portal routes — auth enforced by Vue Router + API middleware, not Laravel
// All routes serve the same Vue SPA shell. Named routes required by Livewire components,
// auth middleware, and blade layouts that reference route('x.y').

// Client portal
Route::view('/client', 'vue')->name('client.dashboard');
Route::view('/client/plan', 'vue')->name('client.plan');
Route::view('/client/workout', 'vue')->name('client.workout');
Route::view('/client/training', 'vue')->name('client.training');
Route::view('/client/checkin', 'vue')->name('client.checkin');
Route::view('/client/photos', 'vue')->name('client.photos');
Route::view('/client/metrics', 'vue')->name('client.metrics');
Route::view('/client/video-checkin', 'vue')->name('client.video-checkin');
Route::view('/client/community', 'vue')->name('client.community');
Route::view('/client/challenges', 'vue')->name('client.challenges');
Route::view('/client/chat', 'vue')->name('client.chat');
Route::view('/client/referrals', 'vue')->name('client.referrals');
Route::view('/client/nutrition', 'vue')->name('client.nutrition');
Route::view('/client/ai-nutrition', 'vue')->name('client.ai-nutrition');
Route::view('/client/settings', 'vue')->name('client.settings');
Route::view('/client/profile', 'vue')->name('client.profile');

// TODO remove en Fase 6 — dev page para visual review de los componentes Photos v2
Route::view('/dev/photos-components', 'vue')->name('dev.photos-components');

Route::get('/client/{any}', fn () => view('vue'))->where('any', '.*');

// RISE portal
Route::view('/rise', 'vue')->name('rise.dashboard');
Route::view('/rise/program', 'vue')->name('rise.program');
Route::view('/rise/workout/{day?}', 'vue')->name('rise.workout');
Route::view('/rise/tracking', 'vue')->name('rise.tracking');
Route::view('/rise/measurements', 'vue')->name('rise.measurements');
Route::view('/rise/photos', 'vue')->name('rise.photos');
Route::view('/rise/habits', 'vue')->name('rise.habits');
Route::view('/rise/chat', 'vue')->name('rise.chat');
Route::view('/rise/profile', 'vue')->name('rise.profile');
Route::get('/rise/{any}', fn () => view('vue'))->where('any', '.*');

// Public invitation routes (no auth required)
Route::get('/invitacion/{code}', [CoachInvitationPublicController::class, 'resolve'])
    ->middleware('throttle:120,1')
    ->name('coach.invitation.resolve');

Route::get('/invitacion-pixel/{code}', [CoachInvitationPublicController::class, 'pixel'])
    ->middleware('throttle:60,1')
    ->name('coach.invitation.pixel');

// Coach portal
Route::view('/coach', 'vue')->name('coach.dashboard');
Route::view('/coach/clients', 'vue')->name('coach.clients');
Route::view('/coach/kanban', 'vue')->name('coach.kanban');
Route::view('/coach/messages', 'vue')->name('coach.messages');
Route::view('/coach/notes', 'vue')->name('coach.notes');
Route::view('/coach/broadcast', 'vue')->name('coach.broadcast');
Route::view('/coach/checkins', 'vue')->name('coach.checkins');
Route::view('/coach/plans', 'vue')->name('coach.plans');
Route::view('/coach/analytics', 'vue')->name('coach.analytics');
Route::view('/coach/resources', 'vue')->name('coach.resources');
Route::view('/coach/features', 'vue')->name('coach.features');
Route::view('/coach/profile', 'vue')->name('coach.profile');
Route::view('/coach/brand', 'vue')->name('coach.brand');
Route::view('/coach/invitations', 'vue')->name('coach.invitations');

// Coach file viewer antes del catch-all — misma razón que admin.payment-proofs.view
Route::get('/coach/payment-proofs/{id}/view', [AdminPaymentProofViewController::class, 'view'])
    ->middleware(['auth:wellcore', 'role:coach,admin,superadmin,jefe'])
    ->whereNumber('id')
    ->name('coach.payment-proofs.view');

// Coach Food Photo Review — Vue SPA (consistente con resto de coach)
Route::view('/coach/food-photos', 'vue')->name('coach.food-photos');

Route::get('/coach/{any}', fn () => view('vue'))->where('any', '.*');

// Admin portal
Route::view('/admin', 'vue')->name('admin.dashboard');
Route::view('/admin/feed', 'vue')->name('admin.feed');
Route::view('/admin/clients', 'vue')->name('admin.clients');
Route::view('/admin/coaches', 'vue')->name('admin.coaches');
Route::view('/admin/plans', 'vue')->name('admin.plans');
Route::view('/admin/ai-generator', 'vue')->name('admin.ai-generator');
Route::view('/admin/rise', 'vue')->name('admin.rise');
Route::view('/admin/payments', 'vue')->name('admin.payments');
Route::view('/admin/inscriptions', 'vue')->name('admin.inscriptions');
Route::view('/admin/invitations', 'vue')->name('admin.invitations');
Route::view('/admin/send-invitation', 'vue')->name('admin.send-invitation');
Route::view('/admin/referral-rewards', 'vue')->name('admin.referral-rewards');
Route::view('/admin/campaign-tracker', 'vue')->name('admin.campaign-tracker');
Route::view('/admin/campaigns', 'vue')->name('admin.campaigns');
Route::view('/admin/chat', 'vue')->name('admin.chat');
Route::view('/admin/tools', 'vue')->name('admin.tools');
Route::view('/admin/tickets', 'vue')->name('admin.tickets');
Route::view('/admin/settings', 'vue')->name('admin.settings');
Route::view('/admin/payment-proofs', 'vue')->name('admin.payment-proofs');
Route::view('/admin/extensions', 'vue')->name('admin.extensions');

// Admin file viewer antes del catch-all — de lo contrario el catch-all /admin/{any}
// intercepta la URL y sirve HTML en lugar del stream de imagen/PDF.
Route::get('/admin/payment-proofs/{id}/view', [AdminPaymentProofViewController::class, 'view'])
    ->middleware(['auth:wellcore', 'role:admin,superadmin,jefe'])
    ->whereNumber('id')
    ->name('admin.payment-proofs.view');

Route::get('/admin/{any}', fn () => view('vue'))->where('any', '.*');

// Session logout + impersonation (POST routes — still need auth)
Route::middleware('auth:wellcore')->group(function () {
    Route::post('/logout', function () {
        $token = session('wc_token');
        if ($token) {
            AuthToken::where('token', $token)->delete();
        }
        session()->flush();

        return redirect('/login')
            ->header('Clear-Site-Data', '"cache", "cookies", "storage"')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate')
            ->header('Pragma', 'no-cache');
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

// Impersonation stop — auth:wellcore passes because during impersonation the active session
// token belongs to the client (not the admin), and the controller validates wc_admin_token in session.
Route::post('/admin/impersonate/stop', [ImpersonateController::class, 'stop'])
    ->name('admin.impersonate.stop')
    ->middleware(['auth:wellcore', 'throttle:10,1']);
Route::post('/admin/coach-impersonate/stop', [CoachImpersonateController::class, 'stop'])
    ->name('coach.impersonate.stop')
    ->middleware(['auth:wellcore', 'throttle:10,1']);

// Backwards-compat: /v/* redirects to /* without the /v prefix (301 permanent)
Route::get('/v/{any}', fn ($any) => redirect('/'.$any, 301))->where('any', '.*');

// Exercise GIFs — public, no auth required (used in <img> tags)
Route::get('/media/gif/{slug}', [GifController::class, 'serve'])
    ->where('slug', '[\w\-]+');

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
