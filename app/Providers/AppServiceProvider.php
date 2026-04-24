<?php

namespace App\Providers;

use App\Models\WorkoutSession;
use App\Observers\WorkoutSessionObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        $this->configureRateLimiting();
        $this->configureCsp();

        WorkoutSession::observe(WorkoutSessionObserver::class);
    }

    /**
     * Register CSP Blade directive and configure Livewire nonce support.
     *
     * The @cspNonce directive emits the per-request nonce that was set by
     * ContentSecurityPolicy middleware. Use it on every inline <script>:
     *   <script nonce="@cspNonce">...</script>
     *
     * Livewire::useScriptTagAttributes() propagates the nonce automatically
     * to all Livewire-injected <script> tags so Livewire works without
     * needing 'unsafe-inline' to be trusted by modern browsers.
     */
    protected function configureCsp(): void
    {
        // Blade directive — outputs the nonce value already HTML-escaped via e()
        Blade::directive('cspNonce', function () {
            return "<?php echo e(request()->attributes->get('csp_nonce', '')); ?>";
        });

        // Livewire 3: propagate nonce to all injected script tags
        \Livewire\Livewire::useScriptTagAttributes([
            'nonce' => fn () => request()->attributes->get('csp_nonce', ''),
        ]);
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function ($request) {
            $key = optional($request->user())->id ?? $request->ip();
            return Limit::perMinute(60)->by($key);
        });

        RateLimiter::for('login', function ($request) {
            $key = optional($request->user())->id ?? $request->ip();
            return Limit::perMinute(5)->by($key);
        });

        RateLimiter::for('chat', function ($request) {
            return Limit::perMinute(20)->by($request->ip());
        });

        RateLimiter::for('newsletter', function ($request) {
            return Limit::perMinute(3)->by($request->ip());
        });

        RateLimiter::for('inscription', function ($request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('webhook', function ($request) {
            return Limit::perMinute(100)->by($request->ip());
        });

        // P2.2 — Rate limiters for sensitive admin/coach actions
        RateLimiter::for('impersonate', function ($request) {
            $userId = optional($request->user())->id;
            $key = $userId ? ('user:'.$userId) : ('ip:'.$request->ip());

            return Limit::perMinute(10)->by($key);
        });

        RateLimiter::for('coach-create', function ($request) {
            $userId = optional($request->user())->id;
            $key = $userId ? ('user:'.$userId) : ('ip:'.$request->ip());

            return Limit::perMinute(3)->by($key);
        });

        // P2.4 — password change limiter (per authenticated user)
        RateLimiter::for('change-password', function ($request) {
            $userId = optional($request->user())->id;
            $key = $userId ? ('user:'.$userId) : ('ip:'.$request->ip());

            return Limit::perMinute(5)->by($key);
        });

        // Phase 1 audit — sensitive write endpoints, throttled per user
        RateLimiter::for('checkin', function ($request) {
            $userId = optional($request->user())->id;
            $key = $userId ? ('user:'.$userId) : ('ip:'.$request->ip());

            return Limit::perHour(5)->by($key)->response(fn () => response()->json([
                'message' => 'Ya enviaste tu check-in. Puedes enviar otro en una hora.',
            ], 429));
        });

        RateLimiter::for('community-write', function ($request) {
            $userId = optional($request->user())->id;
            $key = $userId ? ('user:'.$userId) : ('ip:'.$request->ip());

            return Limit::perHour(20)->by($key)->response(fn () => response()->json([
                'message' => 'Publicaste demasiado rápido. Espera unos minutos.',
            ], 429));
        });

        RateLimiter::for('referrals', function ($request) {
            $userId = optional($request->user())->id;
            $key = $userId ? ('user:'.$userId) : ('ip:'.$request->ip());

            return Limit::perDay(10)->by($key)->response(fn () => response()->json([
                'message' => 'Alcanzaste el límite de invitaciones por hoy.',
            ], 429));
        });

        RateLimiter::for('ticket-create', function ($request) {
            $userId = optional($request->user())->id;
            $key = $userId ? ('user:'.$userId) : ('ip:'.$request->ip());

            return Limit::perHour(5)->by($key)->response(fn () => response()->json([
                'message' => 'Enviaste demasiadas solicitudes. Intenta de nuevo en una hora.',
            ], 429));
        });
    }
}
