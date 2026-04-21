<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Centraliza la invalidación de cache del dashboard del cliente.
 *
 * Cualquier acción que cambie datos del dashboard (check-in, métrica,
 * hábito, agua, suplemento, post de comunidad, foto, workout) debe llamar
 * {@see self::invalidateDashboard()} para evitar que el cliente vea datos
 * viejos por hasta 90 segundos tras una acción.
 */
final class ClientCacheService
{
    /**
     * Invalida el cache del dashboard para un cliente específico.
     */
    public static function invalidateDashboard(int $clientId): void
    {
        Cache::forget("dashboard:{$clientId}");
    }
}
