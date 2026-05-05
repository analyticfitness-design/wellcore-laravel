<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Services\GroupPulseAggregator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class PrecomputeGroupPulse extends Command
{
    protected $signature = 'wellcore:precompute-group-pulse';

    protected $description = 'Warm Redis cache de Latido del Grupo para todos los coaches con clientes activos';

    public function handle(GroupPulseAggregator $aggregator): int
    {
        $coachIds = Client::where('status', 'activo')
            ->whereNotNull('coach_id')
            ->distinct()
            ->pluck('coach_id');

        $count = 0;

        foreach ($coachIds as $coachId) {
            $coachId = (int) $coachId;
            $stats = $aggregator->computeStats($coachId);
            $events = $aggregator->buildFeed($coachId, 'today', 'all');
            $bpm = max(40, min(180, $stats['workouts_today'] * 4 + 40));
            $activeNow = (int) (Cache::get('community:active-list-count') ?? 0);

            // Warm "shared" partial — sin user_vs_group, que depende del cliente.
            // El controller no consume este key todavía (Task 6 hace su propio
            // Cache::remember per-client). Hookup como fallback parcial es
            // tarea futura — esta key ya tiene el shape correcto para entonces.
            $key = "wc:group-pulse:v1:{$coachId}:summary:shared";
            Cache::put($key, [
                'active_now' => $activeNow,
                'bpm' => $bpm,
                'stats' => $stats,
                'top_events' => array_slice($events, 0, 3),
            ], 30);

            $count++;
        }

        $this->info("Precomputed group pulse for {$count} coaches");

        return self::SUCCESS;
    }
}
