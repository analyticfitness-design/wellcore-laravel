<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FeatureFlagService
{
    private static array $defaults = [
        'ai_chatbot' => false,
        'wompi_live' => false,
        'reverb_websockets' => false,
        'email_notifications' => false,
        'push_notifications' => true,
        'coupon_system' => true,
        'coach_marketplace' => false,
        'wearables_integration' => false,
        'multi_language' => false,
        'shop' => false,  // Shop not ready for production
    ];

    public static function isEnabled(string $feature): bool
    {
        $flags = self::all();
        $value = $flags[$feature] ?? false;

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public static function all(): array
    {
        return Cache::remember('feature_flags', now()->addMinutes(5), function () {
            try {
                $dbFlags = DB::table('settings')
                    ->where('group', 'feature_flags')
                    ->pluck('value', 'key')
                    ->toArray();

                return array_merge(self::$defaults, $dbFlags);
            } catch (\Exception) {
                return self::$defaults;
            }
        });
    }

    public static function clearCache(): void
    {
        Cache::forget('feature_flags');
    }

    /**
     * Verifica si una feature flag de rollout (ENV-based) está activa para un usuario.
     * Distinto del método isEnabled() que lee de DB settings table.
     *
     * Lógica: enabled=false → false; userId in force_users → true;
     *         crc32(userId) % 100 < percentage → true; else false.
     */
    public static function isEnabledForUser(string $flag, ?int $userId = null): bool
    {
        $config = config("wellcore.{$flag}");

        if (! $config || ! filter_var($config['enabled'], FILTER_VALIDATE_BOOLEAN)) {
            return false;
        }

        if ($userId && in_array((string) $userId, (array) $config['force_users'])) {
            return true;
        }

        if ($userId && $config['percentage'] > 0) {
            return (abs(crc32((string) $userId)) % 100) < $config['percentage'];
        }

        return false;
    }
}
