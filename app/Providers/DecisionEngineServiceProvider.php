<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\DecisionEngine\DecisionEngine;
use App\Services\DecisionEngine\WhenMatcher;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

/**
 * Registra el DecisionEngine (Stage 2 SELECT del motor v2) como singleton.
 */
final class DecisionEngineServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(WhenMatcher::class);

        $this->app->singleton(DecisionEngine::class, function ($app) {
            return new DecisionEngine(
                matcher: $app->make(WhenMatcher::class),
                logger: $app->make(LoggerInterface::class),
            );
        });
    }
}
