<?php

namespace App\Console\Commands;

use App\Models\RiseTracking;
use App\Models\WorkoutSession;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Backfill rise_tracking.training_done from historical workout_sessions.
 *
 * Until the fix in RiseController::finishWorkout, completing a RISE workout
 * only wrote to workout_sessions — never to rise_tracking. This left the
 * RISE dashboard KPIs (streak, workoutsThisWeek, adherence, weekly grid)
 * permanently at 0 for clients who had actually been training.
 *
 * This command is idempotent and additive:
 *  - Only reads completed workout_sessions per client
 *  - Uses updateOrCreate on (client_id, log_date) unique index
 *  - NEVER sets training_done to false; only promotes missing/false to true
 *  - Leaves nutrition_done, water_liters, sleep_hours, note untouched
 */
class BackfillRiseTracking extends Command
{
    protected $signature = 'wellcore:backfill-rise-tracking
        {--client= : Backfill a single client_id only}
        {--dry-run : Report what would change without writing}';

    protected $description = 'Backfill rise_tracking.training_done from completed workout_sessions (non-destructive)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $clientFilter = $this->option('client');

        $query = WorkoutSession::query()
            ->where('completed', true)
            ->when($clientFilter, fn ($q) => $q->where('client_id', (int) $clientFilter));

        $totalSessions = (clone $query)->count();
        $this->info("Completed workout_sessions to inspect: {$totalSessions}");

        if ($totalSessions === 0) {
            $this->info('Nothing to backfill.');

            return self::SUCCESS;
        }

        $created = 0;
        $promoted = 0;
        $alreadyOk = 0;
        $touchedClients = [];

        $query->orderBy('id')->chunkById(500, function ($sessions) use (
            $dryRun,
            &$created,
            &$promoted,
            &$alreadyOk,
            &$touchedClients,
        ) {
            foreach ($sessions as $session) {
                $logDate = Carbon::parse($session->session_date ?? $session->created_at)->toDateString();
                $clientId = (int) $session->client_id;

                $existing = RiseTracking::where('client_id', $clientId)
                    ->where('log_date', $logDate)
                    ->first();

                if ($existing && $existing->training_done) {
                    $alreadyOk++;

                    continue;
                }

                if ($dryRun) {
                    $existing ? $promoted++ : $created++;
                    $touchedClients[$clientId] = true;

                    continue;
                }

                RiseTracking::updateOrCreate(
                    ['client_id' => $clientId, 'log_date' => $logDate],
                    ['training_done' => true],
                );

                $existing ? $promoted++ : $created++;
                $touchedClients[$clientId] = true;
            }
        });

        if (! $dryRun) {
            foreach (array_keys($touchedClients) as $clientId) {
                Cache::forget("rise:dashboard:{$clientId}");
            }
        }

        $this->table(
            ['Metric', 'Count'],
            [
                ['rise_tracking rows created', $created],
                ['rows updated (training_done false -> true)', $promoted],
                ['rows already training_done=true', $alreadyOk],
                ['distinct clients touched', count($touchedClients)],
                ['dry-run', $dryRun ? 'yes' : 'no'],
            ],
        );

        return self::SUCCESS;
    }
}
