<?php

use App\Providers\AppServiceProvider;
use App\Providers\AuthServiceProvider;
use App\Providers\ComposeEngineServiceProvider;
use App\Providers\DecisionEngineServiceProvider;
use App\Providers\LintEngineServiceProvider;
use App\Providers\PersistEngineServiceProvider;

return [
    AppServiceProvider::class,
    AuthServiceProvider::class,
    LintEngineServiceProvider::class,
    DecisionEngineServiceProvider::class,
    ComposeEngineServiceProvider::class,
    PersistEngineServiceProvider::class,
];
