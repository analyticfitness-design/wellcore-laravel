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
        $activeClientIds = Client::where('status', 'activo')->pluck('id');
        $coachIds = collect();

        if (Schema::hasColumn('clients', 'coach_id')) {
            $coachIds = $coachIds->merge(
                Client::where('status', 'activo')
                    ->whereNotNull('coach_id')
                    ->distinct()
                    ->pluck('coach_id')
            );
        }

        if (Schema::hasTable('assigned_plans')) {
            $coachIds = $coachIds->merge(
                AssignedPlan::whereNotNull('assigned_by')
                    ->whereIn('client_id', $activeClientIds)
                    ->distinct()
                    ->pluck('assigned_by')
            );
        }

        if (Schema::hasTable('coach_messages')) {
            $coachIds = $coachIds->merge(
                CoachMessage::whereNotNull('coach_id')
                    ->whereIn('client_id', $activeClientIds)
                    ->distinct()
                    ->pluck('coach_id')
            );
        }

        $coachIds = $coachIds->map(fn ($id) => (int) $id)->unique()->values();

        $count = 0;

        foreach ($coachIds as $coachId) {
            $coachId = (int) $coachId;
            $clientIds = $aggregator->resolveCoachClientIds($coachId);
            $stats = $aggregator->computeStats($coachId, $clientIds);
            $events = $aggregator->buildFeed($coachId, 'today', 'all', $clientIds);
            $isQuiet = $stats['workouts_today'] === 0
                && $stats['prs_week'] === 0
                && $stats['achievements_today'] === 0;
            $activeNow = (int) (Cache::get('community:active-now-count') ?? 0);

            // Shape MUST match GroupPulseController::summary() shared payload —
            // el controller hace Cache::remember sobre esta key, así que si
            // pre-existe no recomputa. Audit fix 2026-05-05: antes esta key
            // era huérfana (controller usaba :summary:{clientId}).
            $key = "wc:group-pulse:v1:{$coachId}:summary:shared";
            Cache::put($key, [
                'active_now' => $activeNow,
                'bpm' => $isQuiet ? 50 : max(60, min(180, $stats['workouts_today'] * 4 + 60)),
                'is_quiet' => $isQuiet,
                'group_size' => $clientIds->count(),
                'stats' => $stats,
                'top_events' => array_slice($events, 0, 3),
            ], 30);

            $count++;
        }

        $this->info("Precomputed group pulse for {$count} coaches");

        return self::SUCCESS;
    }
}
