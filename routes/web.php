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

// Serve progress photos — local first, then proxy from PHP vanilla service via Docker network
Route::get('/uploads/photos/{filename}', function (string $filename) {
    // Security: only allow safe filenames (no path traversal)
    if (!preg_match('/^[\w\-\.]+\.(jpg|jpeg|png|webp|gif)$/i', $filename)) {
        abort(404);
    }

    $localPath = public_path('uploads/photos/' . $filename);
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
                $mime = match($ext) {
                    'jpg', 'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'webp' => 'image/webp',
                    default => 'image/jpeg',
                };
                return response($content, 200)->header('Content-Type', $mime);
            }
        } catch (\Throwable) {}
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
Route::view('/admin/chat', 'vue')->name('admin.chat');
Route::view('/admin/tools', 'vue')->name('admin.tools');
Route::view('/admin/tickets', 'vue')->name('admin.tickets');
Route::view('/admin/settings', 'vue')->name('admin.settings');
Route::get('/admin/{any}', fn () => view('vue'))->where('any', '.*');

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

// Exercise GIFs — public, no auth required (used in <img> tags)
Route::get('/media/gif/{slug}', [\App\Http\Controllers\Media\GifController::class, 'serve'])
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

// ── Temporary: run fix-gif-aliases via HTTP (remove after use) ──
Route::get('/run-fix-gif-aliases/{secret}', function (string $secret) {
    if ($secret !== 'WC2026fixgifs') abort(404);

    $CDN = 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/';
    $fixes = [
        'patada de gluteo en polea' => 'patada-trasera-en-polea.gif',
        'patada de glúteo en polea' => 'patada-trasera-en-polea.gif',
        'abductor en polea' => 'patada-lateral-en-polea.gif',
        'abduccion en polea' => 'patada-lateral-en-polea.gif',
        'remo con mancuerna un brazo en banco' => 'remo-con-mancuerna-a-una mano.gif',
        'remo con mancuerna a un brazo' => 'remo-con-mancuerna-a-una mano.gif',
        'face pull en polea alta con cuerda' => 'facepull-en-polea.gif',
        'face pull con cuerda' => 'facepull-en-polea.gif',
        'face pull' => 'facepull-en-polea.gif',
        'facepull' => 'facepull-en-polea.gif',
        'romanian deadlift con mancuernas' => 'peso-muerto-rumano-con-mancuerna.gif',
        'romanian deadlift con mancuerna' => 'peso-muerto-rumano-con-mancuerna.gif',
        'rdl con mancuernas' => 'peso-muerto-rumano-con-mancuerna.gif',
        'zancada con mancuerna' => 'zancada-frontal-con-mancuerna.gif',
        'zancada con mancuernas' => 'zancada-frontal-con-mancuerna.gif',
        'press en banco inclinado con mancuerna' => 'press-de-banca-con-mancuernas.gif',
        'press en banco inclinado con mancuernas' => 'press-de-banca-con-mancuernas.gif',
        'press inclinado con mancuerna' => 'press-de-banca-con-mancuernas.gif',
        'press inclinado con mancuernas' => 'press-de-banca-con-mancuernas.gif',
        'extension de triceps en polea alta' => 'extension-de-triceps-en-polea-con-cuerda.gif',
        'extensión de tríceps en polea alta' => 'extension-de-triceps-en-polea-con-cuerda.gif',
        'sentadilla búlgara con mancuerna' => 'sentadilla-bulgara-mancuerna.gif',
        'sentadilla bulgara con mancuerna' => 'sentadilla-bulgara-mancuerna.gif',
        'sentadilla búlgara con mancuernas' => 'sentadilla-bulgara-mancuerna.gif',
        'sentadilla bulgara con mancuernas' => 'sentadilla-bulgara-mancuerna.gif',
        'extension de cuádriceps en maquina' => 'extension-de-piernas-en-maquina.gif',
        'extension de cuadriceps en maquina' => 'extension-de-piernas-en-maquina.gif',
        'extensión de cuádriceps en máquina' => 'extension-de-piernas-en-maquina.gif',
        'zancada reversa con mancuerna' => 'zancada-inversa-con-mancuernas.gif',
        'zancada reversa con mancuernas' => 'zancada-inversa-con-mancuernas.gif',
    ];

    $norm = fn($s) => mb_strtolower(strtr(trim($s), ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n','ü'=>'u']), 'UTF-8');
    $normFixes = [];
    foreach ($fixes as $k => $v) $normFixes[$norm($k)] = $v;

    $output = [];
    $total = 0;

    $fixExercises = function (&$exercises) use ($normFixes, $CDN, $norm, &$total, &$output) {
        if (!is_array($exercises)) return;
        foreach ($exercises as &$ej) {
            if (!is_array($ej) || empty($ej['nombre'])) continue;
            $key = $norm($ej['nombre']);
            if (isset($normFixes[$key])) {
                $ej['gif_url'] = $CDN . $normFixes[$key];
                unset($ej['gif_filename']);
                $output[] = "✅ {$ej['nombre']} → {$normFixes[$key]}";
                $total++;
            }
        }
        unset($ej);
    };

    $fixPlan = function (&$plan) use ($fixExercises) {
        if (!is_array($plan)) return;
        if (!empty($plan['semanas'])) {
            foreach ($plan['semanas'] as &$sem) {
                foreach ($sem['dias'] ?? [] as &$dia) {
                    if (!empty($dia['ejercicios'])) $fixExercises($dia['ejercicios']);
                }
                unset($dia);
            }
            unset($sem);
        }
        if (!empty($plan['dias'])) {
            foreach ($plan['dias'] as &$dia) {
                if (!empty($dia['ejercicios'])) $fixExercises($dia['ejercicios']);
            }
            unset($dia);
        }
    };

    // assigned_plans
    $plans = \DB::table('assigned_plans')->where('plan_type', 'entrenamiento')->whereNotNull('content')->where('content', '!=', '')->get();
    foreach ($plans as $p) {
        $content = json_decode($p->content, true);
        if (!$content) continue;
        $before = $total;
        $fixPlan($content);
        if ($total > $before) {
            $output[] = "Plan #{$p->id} (client {$p->client_id}): " . ($total - $before) . " fixed";
            \DB::table('assigned_plans')->where('id', $p->id)->update(['content' => json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
        }
    }

    // rise_programs
    $rises = \DB::table('rise_programs')->whereNotNull('personalized_program')->where('personalized_program', '!=', '')->get();
    foreach ($rises as $r) {
        $prog = json_decode($r->personalized_program, true);
        if (!$prog || empty($prog['plan_entrenamiento'])) continue;
        $before = $total;
        $fixPlan($prog['plan_entrenamiento']);
        if ($total > $before) {
            $output[] = "RISE #{$r->id} (client {$r->client_id}): " . ($total - $before) . " fixed";
            \DB::table('rise_programs')->where('id', $r->id)->update(['personalized_program' => json_encode($prog, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
        }
    }

    $output[] = "TOTAL: {$total} exercises fixed";
    return response(implode("\n", $output), 200)->header('Content-Type', 'text/plain');
});
