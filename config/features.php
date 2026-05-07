<?php

declare(strict_types=1);

return [
    'coach_strategy_enabled' => env('FEATURE_COACH_STRATEGY_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Plan Viewer V2 — tab Entrenamiento redesign
    |--------------------------------------------------------------------------
    | Default ON 100% (decisión Daniel 2026-05-07: V2 es la única versión).
    | El cache puede sobreescribir esto pero el default es true por si se descachea.
    */
    'plan_viewer_v2' => env('FEATURE_PLAN_VIEWER_V2', true),
    'plan_viewer_v2_rollout_pct' => (int) env('FEATURE_PLAN_VIEWER_V2_PCT', 100),
];
