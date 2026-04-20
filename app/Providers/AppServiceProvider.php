<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
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
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function ($request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        RateLimiter::for('login', function ($request) {
            return Limit::perMinute(5)->by($request->ip());
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
    }
}
