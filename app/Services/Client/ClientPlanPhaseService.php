<?php

namespace App\Services\Client;

use App\Models\AssignedPlan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

final class ClientPlanPhaseService
{
    public function topbarLabel(int $clientId): ?string
    {
        return Cache::remember("topbar_label_v2_{$clientId}", 300, function () use ($clientId) {
            $plan = AssignedPlan::where('client_id', $clientId)
                ->where('plan_type', 'entrenamiento')
                ->where('active', true)
                ->orderByDesc('valid_from')
                ->select('valid_from', 'content')
                ->first();

            if (! $plan) {
                return null;
            }

            $content = $this->normalizePlanContent($plan->content);
            $semanas = is_array($content['semanas'] ?? null) ? $content['semanas'] : [];
            $totalWeeks = count($semanas);

            $validFrom = Carbon::parse($plan->getRawOriginal('valid_from'));
            $weeksElapsed = (int) floor($validFrom->diffInDays(now()) / 7) + 1;
            $currentWeek = $totalWeeks > 0
                ? max(1, min($totalWeeks, $weeksElapsed))
                : max(1, $weeksElapsed);

            $weekIndex = $currentWeek - 1;
            $phaseName = null;

            if (isset($semanas[$weekIndex]['fase']) && filled($semanas[$weekIndex]['fase'])) {
                $faseRaw = trim((string) $semanas[$weekIndex]['fase']);
                $fullPhase = trim(explode('·', $faseRaw)[0]) ?: null;
                // Use only the first word of the phase name to keep the badge compact
                $phaseName = $fullPhase ? explode(' ', $fullPhase)[0] : null;
            }

            return $phaseName
                ? "S{$currentWeek} · {$phaseName}"
                : "S{$currentWeek}";
        });
    }

    private function normalizePlanContent(mixed $content): array
    {
        if (is_array($content)) {
            return $content;
        }
        if (is_string($content) && filled($content)) {
            $decoded = json_decode($content, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }
}
