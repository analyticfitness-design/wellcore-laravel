<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Token Configuration
    |--------------------------------------------------------------------------
    */
    'token_expiry_days' => env('WC_TOKEN_EXPIRY_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Feature Flags (Strangler Fig Migration)
    |--------------------------------------------------------------------------
    | Controls which parts are served by Laravel vs legacy PHP app.
    | Values: 'laravel' or 'legacy'
    */
    'features' => [
        'auth'             => env('FEATURE_AUTH', 'laravel'),
        'public_pages'     => env('FEATURE_PUBLIC_PAGES', 'laravel'),
        'client_dashboard' => env('FEATURE_CLIENT_DASHBOARD', 'legacy'),
        'admin_dashboard'  => env('FEATURE_ADMIN_DASHBOARD', 'legacy'),
        'coach_portal'     => env('FEATURE_COACH_PORTAL', 'legacy'),
        'rise_dashboard'   => env('FEATURE_RISE_DASHBOARD', 'legacy'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard Routes (redirect targets after login)
    |--------------------------------------------------------------------------
    */
    'dashboards' => [
        'superadmin' => '/admin',
        'admin'      => '/admin',
        'jefe'       => '/admin',
        'coach'      => '/coach',
        'client'     => '/client',
        'rise'       => '/client',
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
        'model' => env('CLAUDE_MODEL', 'claude-3-5-haiku-20241022'),
        'base_url' => env('CLAUDE_BASE_URL', 'https://api.anthropic.com'),
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
    | Application Identity
    |--------------------------------------------------------------------------
    */
    'name' => 'WellCore Fitness',
    'version' => '2.0.0',
    'environment' => env('APP_ENV', 'production'),
];
