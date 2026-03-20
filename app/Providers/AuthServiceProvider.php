<?php

namespace App\Providers;

use App\Auth\WellCoreGuard;
use App\Auth\WellCoreUserProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Auth::extend('wellcore', function ($app, $name, array $config) {
            return new WellCoreGuard($app['request']);
        });

        Auth::provider('wellcore', function ($app, array $config) {
            return new WellCoreUserProvider();
        });
    }
}
