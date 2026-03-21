<?php

return [
    'esencial' => [
        'name' => 'Esencial',
        'price_cop' => 299000,
        'price_usd' => 65,
        'includes' => ['entrenamiento', 'habitos'],
        'features_count' => 11,
    ],
    'metodo' => [
        'name' => 'Metodo',
        'price_cop' => 399000,
        'price_usd' => 95,
        'includes' => ['entrenamiento', 'nutricion', 'habitos', 'suplementacion'],
        'features_count' => 21,
    ],
    'elite' => [
        'name' => 'Elite',
        'price_cop' => 549000,
        'price_usd' => 150,
        'includes' => ['entrenamiento', 'nutricion', 'habitos', 'suplementacion', 'ciclo_hormonal', 'bloodwork'],
        'features_count' => 29,
    ],
    'rise' => [
        'name' => 'RISE',
        'price_cop' => 99900,
        'price_usd' => 25,
        'one_time' => true,
        'duration_days' => 30,
        'includes' => ['entrenamiento', 'nutricion_tips', 'habitos', 'suplementacion'],
    ],
    'presencial' => [
        'name' => 'Presencial',
        'price_cop_range' => [450000, 650000],
        'includes' => ['entrenamiento', 'nutricion', 'habitos', 'suplementacion'],
    ],
    'trial' => [
        'name' => 'Trial',
        'price_cop' => 0,
        'duration_days' => 3,
        'includes' => ['entrenamiento', 'nutricion_tips'],
        'limited' => true,
    ],
];
