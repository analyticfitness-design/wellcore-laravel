<?php

declare(strict_types=1);

return [
    'coach_strategy_enabled' => env('FEATURE_COACH_STRATEGY_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Plan Viewer V2 — tab Entrenamiento redesign
    |--------------------------------------------------------------------------
    | Default OFF. Rollout incremental por porcentaje.
    | Activación prod via Cache::forever('feature.plan_viewer_v2', true).
    | Pct via Cache::forever('feature.plan_viewer_v2_pct', 10).
    | Override local QA via localStorage `wc_force_plan_viewer_v2 = 1`.
    */
    'plan_viewer_v2' => env('FEATURE_PLAN_VIEWER_V2', false),
    'plan_viewer_v2_rollout_pct' => (int) env('FEATURE_PLAN_VIEWER_V2_PCT', 0),
];
