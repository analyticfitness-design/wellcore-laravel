<?php

/**
 * WellCore Plans — SINGLE SOURCE OF TRUTH
 *
 * Todos los precios del sitio (checkout, schema.org, CurrencyService, displays)
 * deben leer de aquí. NO hardcodear precios en otros archivos.
 *
 * price_cop       = precio ACTUAL a cobrar (con descuento aplicado si hay promo)
 * price_cop_original = precio antes del descuento (para mostrar tachado)
 * Si no hay promo activa: price_cop == price_cop_original
 */

return [
    'promo' => [
        'active' => true,
        'label' => 'Promoción Mayo',
        'discount_pct' => 15,
        'ends_at' => '2026-05-31',
    ],
    'esencial' => [
        'name' => 'Esencial',
        'price_cop' => 254150,
        'price_cop_original' => 299000,
        'price_usd' => 62,
        'price_usd_original' => 73,
        'desc' => 'Entrenamiento personalizado + guia nutricional basica',
        'includes' => ['entrenamiento', 'nutricion', 'habitos'],
        'features_count' => 11,
    ],
    'metodo' => [
        'name' => 'Metodo',
        'price_cop' => 339150,
        'price_cop_original' => 399000,
        'price_usd' => 82,
        'price_usd_original' => 97,
        'desc' => 'Entreno + Nutricion + Ajustes semanales con coach',
        'includes' => ['entrenamiento', 'nutricion', 'habitos', 'suplementacion'],
        'features_count' => 21,
    ],
    'elite' => [
        'name' => 'Elite',
        'price_cop' => 466650,
        'price_cop_original' => 549000,
        'price_usd' => 114,
        'price_usd_original' => 134,
        'desc' => 'Todo incluido + Check-ins 1:1 + Protocolo habitos',
        'includes' => ['entrenamiento', 'nutricion', 'habitos', 'suplementacion', 'ciclo_hormonal', 'bloodwork'],
        'features_count' => 29,
    ],
    'rise' => [
        'name' => 'RISE',
        'price_cop' => 99900,
        'price_cop_original' => 99900,
        'price_usd' => 25,
        'price_usd_original' => 25,
        'desc' => 'Programa de 30 dias — entrenamiento + nutricion + habitos',
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
        'price_cop_original' => 0,
        'duration_days' => 3,
        'includes' => ['entrenamiento', 'nutricion_tips'],
        'limited' => true,
    ],
];
