<?php

namespace App\Services;

use App\Models\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WellCoinsService
{
    // Earning actions and their coin values
    private const EARN_RULES = [
        'daily_checkin' => 10,
        'weekly_checkin' => 50,
        'workout_completed' => 15,
        'habit_completed' => 5,
        'streak_7_days' => 100,
        'streak_30_days' => 500,
        'photo_upload' => 20,
        'referral_signup' => 200,
        'referral_conversion' => 500,
        'challenge_completed' => 150,
        'challenge_won' => 300,
        'first_checkin' => 50,
        'profile_completed' => 30,
        'review_submitted' => 25,
    ];

    // Tier thresholds
    private const TIERS = [
        'bronce' => 0,
        'plata' => 500,
        'oro' => 2000,
        'platino' => 5000,
        'diamante' => 15000,
    ];

    public static function earn(int $clientId, string $action, ?string $description = null): int
    {
        $coins = self::EARN_RULES[$action] ?? 0;
        if ($coins === 0) return 0;

        try {
            DB::table('wellcoins_transactions')->insert([
                'client_id' => $clientId,
                'type' => 'earn',
                'action' => $action,
                'amount' => $coins,
                'description' => $description ?? self::getActionLabel($action),
                'created_at' => now(),
            ]);

            // Update client's total coins
            DB::table('clients')
                ->where('id', $clientId)
                ->increment('wellcoins_balance', $coins);

            return $coins;
        } catch (\Exception $e) {
            Log::error('WellCoins earn failed', ['client_id' => $clientId, 'action' => $action, 'error' => $e->getMessage()]);
            return 0;
        }
    }

    public static function spend(int $clientId, int $amount, string $description): bool
    {
        $balance = self::getBalance($clientId);
        if ($balance < $amount) return false;

        try {
            DB::table('wellcoins_transactions')->insert([
                'client_id' => $clientId,
                'type' => 'spend',
                'action' => 'redeem',
                'amount' => -$amount,
                'description' => $description,
                'created_at' => now(),
            ]);

            DB::table('clients')
                ->where('id', $clientId)
                ->decrement('wellcoins_balance', $amount);

            return true;
        } catch (\Exception $e) {
            Log::error('WellCoins spend failed', ['client_id' => $clientId, 'error' => $e->getMessage()]);
            return false;
        }
    }

    public static function getBalance(int $clientId): int
    {
        return (int) DB::table('clients')
            ->where('id', $clientId)
            ->value('wellcoins_balance') ?? 0;
    }

    public static function getTier(int $totalEarned): string
    {
        $tier = 'bronce';
        foreach (self::TIERS as $name => $threshold) {
            if ($totalEarned >= $threshold) $tier = $name;
        }
        return $tier;
    }

    public static function getTierProgress(int $totalEarned): array
    {
        $currentTier = self::getTier($totalEarned);
        $tiers = array_keys(self::TIERS);
        $currentIndex = array_search($currentTier, $tiers);
        $nextIndex = $currentIndex + 1;

        if ($nextIndex >= count($tiers)) {
            return ['current' => $currentTier, 'next' => null, 'progress' => 100, 'remaining' => 0];
        }

        $nextTier = $tiers[$nextIndex];
        $nextThreshold = self::TIERS[$nextTier];
        $currentThreshold = self::TIERS[$currentTier];
        $progress = (($totalEarned - $currentThreshold) / ($nextThreshold - $currentThreshold)) * 100;

        return [
            'current' => $currentTier,
            'next' => $nextTier,
            'progress' => min(100, round($progress)),
            'remaining' => $nextThreshold - $totalEarned,
        ];
    }

    public static function getHistory(int $clientId, int $limit = 20): array
    {
        return DB::table('wellcoins_transactions')
            ->where('client_id', $clientId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    private static function getActionLabel(string $action): string
    {
        return match($action) {
            'daily_checkin' => 'Check-in diario',
            'weekly_checkin' => 'Check-in semanal completado',
            'workout_completed' => 'Entrenamiento completado',
            'habit_completed' => 'Habito cumplido',
            'streak_7_days' => 'Racha de 7 dias!',
            'streak_30_days' => 'Racha de 30 dias!',
            'photo_upload' => 'Foto de progreso subida',
            'referral_signup' => 'Referido se registro',
            'referral_conversion' => 'Referido se convirtio en cliente',
            'challenge_completed' => 'Reto completado',
            'challenge_won' => 'Reto ganado!',
            'first_checkin' => 'Primer check-in',
            'profile_completed' => 'Perfil completado',
            'review_submitted' => 'Resena enviada',
            default => ucfirst(str_replace('_', ' ', $action)),
        };
    }

    public static function getEarnRules(): array
    {
        return self::EARN_RULES;
    }

    public static function getTiers(): array
    {
        return self::TIERS;
    }
}
