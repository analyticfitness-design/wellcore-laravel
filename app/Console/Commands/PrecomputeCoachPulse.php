<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Admin;
use App\Services\CoachCommunityService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class PrecomputeCoachPulse extends Command
{
    protected $signature = 'wellcore:precompute-coach-pulse {--coach= : Specific coach ID}';

    protected $description = 'Precompute coach community pulse for active coaches (every 5min)';

    public function handle(CoachCommunityService $service): int
    {
        $coachIds = $this->option('coach')
            ? [(int) $this->option('coach')]
            : Admin::query()
                ->where('role', 'coach')
                ->whereExists(fn ($q) => $q->selectRaw('1')
                    ->from('clients')
                    ->whereColumn('clients.coach_id', 'admins.id')
                    ->where('clients.status', 'activo')
                )
                ->pluck('id')
                ->all();

        $count = 0;
        foreach ($coachIds as $coachId) {
            try {
                Cache::put(
                    "wc:coach-pulse:v1:{$coachId}",
                    [
                        'team_health_score' => $service->teamHealthScore($coachId),
                        'top_performers' => $service->topPerformers($coachId, days: 7, limit: 3),
                        'at_risk_clients' => $service->atRiskClients($coachId, days: 5),
                        'computed_at' => now()->toIso8601String(),
                    ],
                    300
                );
                $count++;
            } catch (\Throwable $e) {
                $this->error("Failed coach {$coachId}: {$e->getMessage()}");
            }
        }

        $this->info("Precomputed pulse for {$count} coaches.");

        return self::SUCCESS;
    }
}
