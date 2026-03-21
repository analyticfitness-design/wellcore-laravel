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
        'model' => env('CLAUDE_MODEL', 'claude-haiku-4-5-20251001'),
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
];
