<?php

return [
    'paths' => ['api/*'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => array_values(array_filter([
        env('APP_ENV') === 'local' ? 'http://wellcore-laravel.test' : null,
        'https://wellcorefitness.com',
        'https://www.wellcorefitness.com',
    ])),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With', 'Accept'],

    'exposed_headers' => [],

    'max_age' => 3600,

    'supports_credentials' => true,
];
