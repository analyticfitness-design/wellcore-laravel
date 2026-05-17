<?php

/**
 * Configuración del Motor v2 de creación de planes WellCore.
 *
 * Ver docs/wellcore-engine-v2/ para el diseño completo.
 * Ver app/PlanEngine/README.md para overview operativo.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Killswitch — activación del motor v2
    |--------------------------------------------------------------------------
    |
    | Cuando esté en false, cualquier llamada al motor responde
    | "desactivado, usa flujo manual" sin tocar DBs ni LLM. Si en cualquier
    | momento se rompe algo, el toggle de .env apaga el motor en <1 min.
    |
    | Default: false (apagado hasta arrancar Sprint 4 / Fase 1 rollout)
    | Ver docs/wellcore-engine-v2/07-strangler-fig-rollout.md §4
    */
    'enabled' => env('WC_ENGINE_V2_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Versión del motor — se escribe en plan_engine_runs.prompt_version
    |--------------------------------------------------------------------------
    |
    | Marca cada run con esta versión para reproducibilidad. Cuando cambien
    | prompts significativamente, bumpear versión (v2.1, v2.2, etc.) y dejar
    | la versión anterior viva 1 sprint por si hay que rollback.
    */
    'version' => env('WC_ENGINE_VERSION', 'v2.0'),

    /*
    |--------------------------------------------------------------------------
    | Cost budget por run (en USD)
    |--------------------------------------------------------------------------
    |
    | El orchestrator aborta el run si los tokens acumulados superan este
    | budget. Defensa contra retry loops o prompts mal calibrados que
    | quemen tokens. Estimado normal por plan: $0.18-0.26 (doc 05 §8).
    */
    'cost_budget_usd_per_run' => env('WC_ENGINE_COST_BUDGET', 1.00),

    /*
    |--------------------------------------------------------------------------
    | Concurrencia local — file lock path
    |--------------------------------------------------------------------------
    |
    | Solo 1 run del motor a la vez por laptop. flock LOCK_EX | LOCK_NB.
    | Ver doc 04 §11.
    */
    'lock_file' => storage_path('locks/plan_engine.lock'),

    /*
    |--------------------------------------------------------------------------
    | Reintentos del LLM en COMPOSE
    |--------------------------------------------------------------------------
    |
    | Política decidida 2026-05-16: 1 retry con prompt corregido si el output
    | del LLM no parsea a JSON válido. Si el retry también falla, fatal.
    */
    'llm_compose_retries' => 1,
];
