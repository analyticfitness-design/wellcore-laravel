<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Checkin;
use App\Models\TrainingLog;
use App\Models\BiometricEntry;
use Illuminate\Support\Facades\DB;

class ProgressReportService
{
    public static function generate(int $clientId, ?string $startDate = null, ?string $endDate = null): array
    {
        $client = Client::find($clientId);
        if (!$client) return ['error' => 'Cliente no encontrado'];

        $start = $startDate ? \Carbon\Carbon::parse($startDate) : now()->subMonth();
        $end = $endDate ? \Carbon\Carbon::parse($endDate) : now();

        // Training data
        $trainingLogs = TrainingLog::where('client_id', $clientId)
            ->whereBetween('log_date', [$start, $end])
            ->get();

        $totalWorkouts = $trainingLogs->where('completed', true)->count();
        $totalDays = $start->diffInDays($end);
        $adherence = $totalDays > 0 ? round(($totalWorkouts / $totalDays) * 100) : 0;

        // Check-in data
        $checkins = Checkin::where('client_id', $clientId)
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $avgBienestar = $checkins->avg('bienestar') ?? 0;
        $avgRPE = $checkins->avg('rpe') ?? 0;

        // Biometric data
        $biometrics = BiometricEntry::where('client_id', $clientId)
            ->whereBetween('measured_at', [$start, $end])
            ->orderBy('measured_at')
            ->get();

        $firstWeight = $biometrics->first()?->peso;
        $lastWeight = $biometrics->last()?->peso;
        $weightChange = ($firstWeight && $lastWeight) ? round($lastWeight - $firstWeight, 1) : null;

        // Streak
        $currentStreak = $client->streak_days ?? 0;

        return [
            'client' => [
                'name' => $client->name ?? $client->nombre ?? 'Cliente',
                'plan' => $client->plan_slug ?? 'N/A',
                'start_date' => $start->format('d/m/Y'),
                'end_date' => $end->format('d/m/Y'),
            ],
            'training' => [
                'total_workouts' => $totalWorkouts,
                'total_days' => $totalDays,
                'adherence_percent' => $adherence,
                'avg_rpe' => round($avgRPE, 1),
            ],
            'wellbeing' => [
                'avg_bienestar' => round($avgBienestar, 1),
                'checkins_count' => $checkins->count(),
            ],
            'body' => [
                'weight_change' => $weightChange,
                'first_weight' => $firstWeight,
                'last_weight' => $lastWeight,
                'measurements_count' => $biometrics->count(),
            ],
            'gamification' => [
                'current_streak' => $currentStreak,
                'xp_total' => $client->xp_total ?? 0,
                'level' => $client->level ?? 1,
            ],
            'period' => "{$start->format('d M')} - {$end->format('d M Y')}",
            'generated_at' => now()->toISOString(),
        ];
    }
}
