<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AchievementService
{
    private const ACHIEVEMENTS = [
        // Onboarding
        ['id' => 'first_login', 'name' => 'Primer Paso', 'desc' => 'Iniciaste sesion por primera vez', 'icon' => 'rocket', 'xp' => 10, 'category' => 'onboarding'],
        ['id' => 'profile_complete', 'name' => 'Identidad Completa', 'desc' => 'Completaste tu perfil al 100%', 'icon' => 'user', 'xp' => 30, 'category' => 'onboarding'],
        ['id' => 'first_checkin', 'name' => 'Check-in Iniciado', 'desc' => 'Enviaste tu primer check-in', 'icon' => 'check', 'xp' => 50, 'category' => 'onboarding'],

        // Training
        ['id' => 'workout_1', 'name' => 'Primer Entrenamiento', 'desc' => 'Completaste tu primer entrenamiento', 'icon' => 'fire', 'xp' => 20, 'category' => 'training'],
        ['id' => 'workout_10', 'name' => 'Consistente', 'desc' => '10 entrenamientos completados', 'icon' => 'fire', 'xp' => 100, 'category' => 'training'],
        ['id' => 'workout_50', 'name' => 'Dedicacion Pura', 'desc' => '50 entrenamientos completados', 'icon' => 'fire', 'xp' => 300, 'category' => 'training'],
        ['id' => 'workout_100', 'name' => 'Centurion', 'desc' => '100 entrenamientos completados', 'icon' => 'trophy', 'xp' => 500, 'category' => 'training'],
        ['id' => 'workout_250', 'name' => 'Leyenda del Gym', 'desc' => '250 entrenamientos completados', 'icon' => 'crown', 'xp' => 1000, 'category' => 'training'],

        // Streaks
        ['id' => 'streak_3', 'name' => 'Racha Iniciada', 'desc' => '3 dias seguidos entrenando', 'icon' => 'flame', 'xp' => 30, 'category' => 'streak'],
        ['id' => 'streak_7', 'name' => 'Semana Perfecta', 'desc' => '7 dias seguidos entrenando', 'icon' => 'flame', 'xp' => 100, 'category' => 'streak'],
        ['id' => 'streak_14', 'name' => 'Imparable', 'desc' => '14 dias seguidos', 'icon' => 'flame', 'xp' => 200, 'category' => 'streak'],
        ['id' => 'streak_30', 'name' => 'Acero Puro', 'desc' => '30 dias seguidos', 'icon' => 'diamond', 'xp' => 500, 'category' => 'streak'],
        ['id' => 'streak_90', 'name' => 'Inquebrantable', 'desc' => '90 dias seguidos', 'icon' => 'diamond', 'xp' => 1500, 'category' => 'streak'],

        // Nutrition
        ['id' => 'nutrition_log_1', 'name' => 'Primer Registro', 'desc' => 'Registraste tu primera comida', 'icon' => 'apple', 'xp' => 15, 'category' => 'nutrition'],
        ['id' => 'nutrition_log_30', 'name' => 'Dieta Consciente', 'desc' => '30 registros nutricionales', 'icon' => 'apple', 'xp' => 150, 'category' => 'nutrition'],
        ['id' => 'macros_hit_7', 'name' => 'Precision Nutricional', 'desc' => '7 dias cumpliendo macros', 'icon' => 'target', 'xp' => 200, 'category' => 'nutrition'],

        // Progress
        ['id' => 'weight_loss_5', 'name' => 'Primeros 5 kg', 'desc' => 'Perdiste 5 kg desde tu inicio', 'icon' => 'scale', 'xp' => 200, 'category' => 'progress'],
        ['id' => 'weight_loss_10', 'name' => 'Transformacion', 'desc' => 'Perdiste 10 kg desde tu inicio', 'icon' => 'star', 'xp' => 500, 'category' => 'progress'],
        ['id' => 'muscle_gain', 'name' => 'Ganancia Muscular', 'desc' => 'Aumentaste 3% masa muscular', 'icon' => 'bicep', 'xp' => 300, 'category' => 'progress'],
        ['id' => 'photo_first', 'name' => 'Evidencia Visual', 'desc' => 'Subiste tu primera foto de progreso', 'icon' => 'camera', 'xp' => 25, 'category' => 'progress'],

        // Social
        ['id' => 'referral_1', 'name' => 'Embajador', 'desc' => 'Tu primer referido se inscribio', 'icon' => 'share', 'xp' => 200, 'category' => 'social'],
        ['id' => 'referral_5', 'name' => 'Influencer WellCore', 'desc' => '5 referidos inscritos', 'icon' => 'megaphone', 'xp' => 1000, 'category' => 'social'],
        ['id' => 'community_post_10', 'name' => 'Voz Activa', 'desc' => '10 publicaciones en la comunidad', 'icon' => 'chat', 'xp' => 100, 'category' => 'social'],
        ['id' => 'coach_feedback', 'name' => 'Feedback Valioso', 'desc' => 'Evaluaste a tu coach', 'icon' => 'star', 'xp' => 30, 'category' => 'social'],

        // Challenges
        ['id' => 'challenge_first', 'name' => 'Retador', 'desc' => 'Completaste tu primer reto', 'icon' => 'flag', 'xp' => 150, 'category' => 'challenge'],
        ['id' => 'challenge_3', 'name' => 'Triple Amenaza', 'desc' => 'Completaste 3 retos', 'icon' => 'medal', 'xp' => 400, 'category' => 'challenge'],
        ['id' => 'challenge_winner', 'name' => 'Campeon', 'desc' => 'Ganaste un reto grupal', 'icon' => 'trophy', 'xp' => 500, 'category' => 'challenge'],

        // Milestones
        ['id' => 'month_1', 'name' => '1 Mes Activo', 'desc' => 'Llevas 1 mes en WellCore', 'icon' => 'calendar', 'xp' => 50, 'category' => 'milestone'],
        ['id' => 'month_3', 'name' => '3 Meses Activo', 'desc' => 'Llevas 3 meses en WellCore', 'icon' => 'calendar', 'xp' => 150, 'category' => 'milestone'],
        ['id' => 'month_6', 'name' => 'Veterano', 'desc' => 'Llevas 6 meses en WellCore', 'icon' => 'medal', 'xp' => 300, 'category' => 'milestone'],
        ['id' => 'month_12', 'name' => 'Leyenda WellCore', 'desc' => '1 ano con WellCore', 'icon' => 'crown', 'xp' => 1000, 'category' => 'milestone'],

        // Special
        ['id' => 'rise_complete', 'name' => 'RISE Completado', 'desc' => 'Completaste los 30 dias del RISE', 'icon' => 'star', 'xp' => 500, 'category' => 'special'],
        ['id' => 'pr_set', 'name' => 'Record Personal', 'desc' => 'Estableciste un nuevo PR', 'icon' => 'trophy', 'xp' => 100, 'category' => 'special'],
        ['id' => 'early_bird', 'name' => 'Madrugador', 'desc' => 'Entrenaste antes de las 7am', 'icon' => 'sun', 'xp' => 25, 'category' => 'special'],
        ['id' => 'night_owl', 'name' => 'Nocturno', 'desc' => 'Entrenaste despues de las 9pm', 'icon' => 'moon', 'xp' => 25, 'category' => 'special'],
    ];

    public static function unlock(int $clientId, string $achievementId): bool
    {
        // Check if already unlocked
        $exists = DB::table('client_achievements')
            ->where('client_id', $clientId)
            ->where('achievement_id', $achievementId)
            ->exists();

        if ($exists) return false;

        $achievement = collect(self::ACHIEVEMENTS)->firstWhere('id', $achievementId);
        if (!$achievement) return false;

        try {
            DB::table('client_achievements')->insert([
                'client_id' => $clientId,
                'achievement_id' => $achievementId,
                'unlocked_at' => now(),
                'created_at' => now(),
            ]);

            // Award XP
            DB::table('clients')
                ->where('id', $clientId)
                ->increment('xp_total', $achievement['xp']);

            // Award WellCoins
            WellCoinsService::earn($clientId, 'achievement_unlocked', "Logro: {$achievement['name']}");

            return true;
        } catch (\Exception $e) {
            Log::error('Achievement unlock failed', ['client_id' => $clientId, 'achievement' => $achievementId]);
            return false;
        }
    }

    public static function getUnlocked(int $clientId): array
    {
        $unlockedIds = DB::table('client_achievements')
            ->where('client_id', $clientId)
            ->pluck('achievement_id')
            ->toArray();

        return collect(self::ACHIEVEMENTS)
            ->map(function ($a) use ($unlockedIds) {
                $a['unlocked'] = in_array($a['id'], $unlockedIds);
                return $a;
            })
            ->toArray();
    }

    public static function getProgress(int $clientId): array
    {
        $unlocked = DB::table('client_achievements')
            ->where('client_id', $clientId)
            ->count();
        $total = count(self::ACHIEVEMENTS);

        return [
            'unlocked' => $unlocked,
            'total' => $total,
            'percentage' => $total > 0 ? round(($unlocked / $total) * 100) : 0,
        ];
    }

    public static function getAllAchievements(): array
    {
        return self::ACHIEVEMENTS;
    }

    public static function getCategories(): array
    {
        return collect(self::ACHIEVEMENTS)
            ->groupBy('category')
            ->map(fn ($items) => $items->count())
            ->toArray();
    }
}
