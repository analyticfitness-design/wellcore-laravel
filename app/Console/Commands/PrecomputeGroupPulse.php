<?php

namespace App\Console\Commands;

use App\Models\AssignedPlan;
use App\Models\Client;
use App\Models\CoachMessage;
use App\Services\GroupPulseAggregator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class PrecomputeGroupPulse extends Command
{
    protected $signature = 'wellcore:precompute-group-pulse';

    protected $description = 'Warm Redis cache de Latido del Grupo para todos los coaches con clientes activos';

    public function handle(GroupPulseAggregator $aggregator): int
    {
        // Coaches con clientes activos via 3-fallback (clients.coach_id es
        // sparso en prod; la mayoría se asigna via assigned_plans/coach_messages)
        $coachIds = collect();

        if (Schema::hasColumn('clients', 'coach_id')) {
            $coachIds = $coachIds->merge(
                Client::where('status', 'activo')
                    ->whereNotNull('coach_id')
                    ->distinct()
                    ->pluck('coach_id')
            );
        }

        $coachIds = $coachIds->merge(
            AssignedPlan::whereNotNull('assigned_by')
                ->whereIn('client_id', Client::where('status', 'activo')->pluck('id'))
                ->distinct()
                ->pluck('assigned_by')
        );

        $coachIds = $coachIds->merge(
            CoachMessage::whereNotNull('coach_id')
                ->whereIn('client_id', Client::where('status', 'activo')->pluck('id'))
                ->distinct()
                ->pluck('coach_id')
        );

        $coachIds = $coachIds->map(fn ($id) => (int) $id)->unique()->values();

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
