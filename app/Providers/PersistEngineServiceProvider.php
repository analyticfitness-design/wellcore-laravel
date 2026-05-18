<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\PersistEngine\PersistService;
use Illuminate\Support\ServiceProvider;

final class PersistEngineServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PersistService::class);
    }
}
