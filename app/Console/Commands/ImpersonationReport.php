<?php

namespace App\Console\Commands;

use App\Models\ImpersonationLog;
use Illuminate\Console\Command;

class ImpersonationReport extends Command
{
    protected $signature = 'wellcore:impersonation-report {--days=7}';
    protected $description = 'Outputs an impersonation activity report for the last N days (default 7).';

    public function handle(): int
    {
        $days  = (int) $this->option('days');
        $since = now()->subDays($days);

        $logs = ImpersonationLog::where('started_at', '>=', $since)->get();

        $this->info("Impersonation report — last {$days} days ({$logs->count()} sessions)");
        $this->newLine();

        $byActor = $logs->groupBy('actor_id');
        $rows = [];
        foreach ($byActor as $actorId => $group) {
            $totalSec = $group->sum(function ($l) {
                return $l->ended_at ? $l->ended_at->diffInSeconds($l->started_at) : 0;
            });
            $rows[] = [
                'actor'          => ($group->first()->actor_name ?? '?')." (#$actorId)",
                'sessions'       => $group->count(),
                'unique_targets' => $group->pluck('target_id')->unique()->count(),
                'avg_minutes'    => $group->count() ? round(($totalSec / 60) / $group->count(), 1) : 0,
                'open'           => $group->whereNull('ended_at')->count(),
            ];
        }
        $this->table(['Actor', 'Sessions', 'Unique targets', 'Avg minutes', 'Still open'], $rows);

        $longSessions = $logs->filter(fn ($l) =>
            $l->ended_at && $l->ended_at->diffInMinutes($l->started_at) > 50
        );
        if ($longSessions->count() > 0) {
            $this->warn("⚠ {$longSessions->count()} sesiones duraron más de 50 min.");
        }

        $heavyTargets = $logs->groupBy('target_id')
            ->filter(fn ($g) => $g->count() > 5);
        foreach ($heavyTargets as $targetId => $group) {
            $this->warn("⚠ Target #{$targetId} fue impersonificado {$group->count()} veces en {$days}d.");
        }

        return self::SUCCESS;
    }
}
