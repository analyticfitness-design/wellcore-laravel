<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Token Configuration
    |--------------------------------------------------------------------------
    */
    'token_expiry_days' => env('WC_TOKEN_EXPIRY_DAYS', 30),

    // WhatsApp real Silvia: +57 312 4904720
    'whatsapp_silvia' => env('WC_WHATSAPP_SILVIA', '573124904720'),

    // WhatsApp coach presencial Bucaramanga: +57 312 4904720 (mismo que Silvia main).
    // Si en el futuro un coach distinto atiende presencial, override en .env con WC_WHATSAPP_PRESENCIAL.
    'whatsapp_presencial' => env('WC_WHATSAPP_PRESENCIAL', '573124904720'),

    // Coach marketplace split — fracción que recibe el coach por cada cliente activo.
    // USO INTERNO en CoachesController (calculadora ingresos). El copy público NO
    // expone el número exacto: usa "split competitivo por encima del estándar".
    'coach_split' => (float) env('WC_COACH_SPLIT', 0.6),

    // Plan Método mensual COP — usado en la calculadora de ingresos del coach.
    // Si cambia el pricing real, ajustar acá; el copy público se mantiene en intervalos.
    'coach_calc_plan_cop' => (int) env('WC_COACH_CALC_PLAN_COP', 380000),

    /*
    |--------------------------------------------------------------------------
    | Feature Flags (Strangler Fig Migration)
    |--------------------------------------------------------------------------
    | Controls which parts are served by Laravel vs legacy PHP app.
    | Values: 'laravel' or 'legacy'
    */
    'features' => [
        'auth' => env('FEATURE_AUTH', 'laravel'),
        'public_pages' => env('FEATURE_PUBLIC_PAGES', 'laravel'),
        'client_dashboard' => env('FEATURE_CLIENT_DASHBOARD', 'legacy'),
        'admin_dashboard' => env('FEATURE_ADMIN_DASHBOARD', 'legacy'),
        'coach_portal' => env('FEATURE_COACH_PORTAL', 'legacy'),
        'rise_dashboard' => env('FEATURE_RISE_DASHBOARD', 'legacy'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard Routes (redirect targets after login)
    |--------------------------------------------------------------------------
    */
    'dashboards' => [
        'superadmin' => '/admin',
        'admin' => '/admin',
        'jefe' => '/admin',
        'coach' => '/coach',
        'client' => '/client',
        'rise' => '/client',
    ],

    /*
    |--------------------------------------------------------------------------
    | Legacy App URL (for Strangler Fig redirects)
    |--------------------------------------------------------------------------
    */
    'legacy_url' => env('WC_LEGACY_URL', 'http://wellcorefitness.test'),

    /*
    |--------------------------------------------------------------------------
    | Wompi Payment Gateway
    |--------------------------------------------------------------------------
    */
    'wompi' => [
        'base_url' => env('WOMPI_BASE_URL', 'https://production.wompi.co/v1'),
        'public_key' => env('WOMPI_PUBLIC_KEY', ''),
        'private_key' => env('WOMPI_PRIVATE_KEY', ''),
        'events_secret' => env('WOMPI_EVENTS_SECRET', ''),
        'integrity_secret' => env('WOMPI_INTEGRITY_SECRET', ''),
        'sandbox' => env('WOMPI_SANDBOX', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Claude AI Integration
    |--------------------------------------------------------------------------
    */
    'ai' => [
        'api_key' => env('CLAUDE_API_KEY', ''),
        'model' => env('CLAUDE_MODEL', 'claude-haiku-4-5-20251001'),
        'base_url' => env('CLAUDE_BASE_URL', 'https://api.anthropic.com'),
        // Kill-switch para el Generador IA del admin v2. Por defecto bloqueado
        // para no gastar tokens hasta que Daniel decida activarlo.
        'generator_enabled' => env('WC_AI_GENERATOR_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | VAPID Web Push Notifications
    |--------------------------------------------------------------------------
    */
    'vapid' => [
        'public_key' => env('VAPID_PUBLIC_KEY', ''),
        'private_key' => env('VAPID_PRIVATE_KEY', ''),
        'subject' => env('VAPID_SUBJECT', 'mailto:info@wellcorefitness.com'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Push Notifications (alias kept for WompiService / PushNotificationService)
    |--------------------------------------------------------------------------
    */
    'push' => [
        'public_key' => env('VAPID_PUBLIC_KEY', ''),
        'private_key' => env('VAPID_PRIVATE_KEY', ''),
        'subject' => env('VAPID_SUBJECT', 'mailto:info@wellcorefitness.com'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Error Tracking (Sentry)
    |--------------------------------------------------------------------------
    */
    'error_tracking' => [
        'enabled' => env('ERROR_TRACKING_ENABLED', false),
        'dsn' => env('SENTRY_DSN', ''),
        'sample_rate' => env('SENTRY_SAMPLE_RATE', 0.1),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Tuning
    |--------------------------------------------------------------------------
    */
    'performance' => [
        'cache_ttl' => env('WC_CACHE_TTL', 300),
        'query_log' => env('WC_QUERY_LOG', false),
        'slow_query_threshold' => env('WC_SLOW_QUERY_MS', 500),
    ],

    /*
    |--------------------------------------------------------------------------
    | Application Limits
    |--------------------------------------------------------------------------
    */
    'limits' => [
        'max_upload_size' => env('WC_MAX_UPLOAD_MB', 10),
        'max_chat_message_length' => 500,
        'max_bio_length' => 1000,
        'refund_window_days' => 7,
    ],

    /*
    |--------------------------------------------------------------------------
    | Application Base URL (used in Mail classes, links, etc.)
    |--------------------------------------------------------------------------
    */
    'base_url' => env('APP_URL', 'https://wellcorefitness.com'),

    /*
    |--------------------------------------------------------------------------
    | Application Identity
    |--------------------------------------------------------------------------
    */
    'name' => 'WellCore Fitness',
    'version' => '2.0.0',
    'environment' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Coach Contract Acceptance Gate
    |--------------------------------------------------------------------------
    | Controls the digital acceptance flow of the Coach Alliance Agreement.
    | When 'enabled' is false, the gate API short-circuits and the middleware
    | becomes a no-op.
    */
    'coach_contract' => [
        'enabled' => env('COACH_CONTRACT_GATE_ENABLED', true),
        'version' => env('COACH_CONTRACT_VERSION', '1.0'),
        'is_draft' => env('COACH_CONTRACT_IS_DRAFT', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Workout Player v2 Feature Flag (rollout gradual)
    |--------------------------------------------------------------------------
    | WC_WORKOUT_PLAYER_V2=false        — off para todos (default)
    | WC_WORKOUT_PLAYER_V2_PCT=10       — on para 10% de usuarios
    | WC_WORKOUT_PLAYER_V2_USERS=1,2,3  — forzar para user IDs específicos
    */
    'workout_player_v2' => [
        'enabled' => env('WC_WORKOUT_PLAYER_V2', false),
        'percentage' => (int) env('WC_WORKOUT_PLAYER_V2_PCT', 0),
        'force_users' => array_filter(array_map('trim', explode(',', env('WC_WORKOUT_PLAYER_V2_USERS', '')))),
    ],

    /*
    |--------------------------------------------------------------------------
    | Profile Editor v2 Feature Flag (rollout gradual)
    |--------------------------------------------------------------------------
    | WC_PROFILE_V2=false        — off para todos (default)
    | WC_PROFILE_V2_PCT=10       — on para 10% de usuarios
    | WC_PROFILE_V2_USERS=1,2,3  — forzar para user IDs específicos
    */
    'profile_v2' => [
        'enabled' => env('WC_PROFILE_V2', false),
        'percentage' => (int) env('WC_PROFILE_V2_PCT', 0),
        'force_users' => array_filter(array_map('trim', explode(',', env('WC_PROFILE_V2_USERS', '')))),
    ],
];
