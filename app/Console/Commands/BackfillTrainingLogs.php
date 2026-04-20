<?php

namespace App\Console\Commands;

use App\Models\TrainingLog;
use App\Models\WorkoutSession;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Backfill training_logs.completed from historical workout_sessions.
 *
 * Until the fix in TrainingController::finishWorkout, completing a workout
 * only wrote to workout_sessions — never to training_logs. This left the
 * TrainingView weekly grid, dashboard KPIs, and client_xp streak empty for
 * clients who had actually been training via the workout player.
 *
 * This command is idempotent and additive:
 *  - Only reads completed workout_sessions per client
 *  - Uses updateOrCreate on (client_id, log_date) unique index
 *  - NEVER sets completed to false; only promotes missing/false to true
 *  - Derives year_num / week_num from the session_date (ISO week)
 */
class BackfillTrainingLogs extends Command
{
    protected $signature = 'wellcore:backfill-training-logs
        {--client= : Backfill a single client_id only}
        {--dry-run : Report what would change without writing}';

    protected $description = 'Backfill training_logs.completed from completed workout_sessions (non-destructive)';

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

        // Pre-load valid client IDs — skip workout_sessions de clientes borrados
        // (FK training_logs -> clients CASCADE ya debió haberlos removido, pero
        // workout_sessions no tiene esa restriccion, por eso hay huerfanos).
        $validClientIds = DB::table('clients')->pluck('id')->all();
        $validSet = array_flip($validClientIds);

        $created = 0;
        $promoted = 0;
        $alreadyOk = 0;
        $skippedOrphan = 0;
        $touchedClients = [];

        $query->orderBy('id')->chunkById(500, function ($sessions) use (
            $dryRun,
            $validSet,
            &$created,
            &$promoted,
            &$alreadyOk,
            &$skippedOrphan,
            &$touchedClients,
        ) {
            foreach ($sessions as $session) {
                $sessionDate = Carbon::parse($session->session_date ?? $session->created_at);
                $logDate = $sessionDate->toDateString();
                $clientId = (int) $session->client_id;

                // Skip huerfanos: workout_sessions sin cliente en clients.
                if (! isset($validSet[$clientId])) {
                    $skippedOrphan++;

                    continue;
                }

                $existing = TrainingLog::where('client_id', $clientId)
                    ->where('log_date', $logDate)
                    ->first();

                if ($existing && $existing->completed) {
                    $alreadyOk++;

                    continue;
                }

                if ($dryRun) {
                    $existing ? $promoted++ : $created++;
                    $touchedClients[$clientId] = true;

                    continue;
                }

                TrainingLog::updateOrCreate(
                    ['client_id' => $clientId, 'log_date' => $logDate],
                    [
                        'completed' => true,
                        'year_num' => (int) $sessionDate->isoFormat('GGGG'),
                        'week_num' => (int) $sessionDate->isoFormat('W'),
                    ],
                );

                $existing ? $promoted++ : $created++;
                $touchedClients[$clientId] = true;
            }
        });

        if (! $dryRun) {
            foreach (array_keys($touchedClients) as $clientId) {
                Cache::forget("dashboard:{$clientId}");
                Cache::forget("training:month_sessions:{$clientId}:".now()->format('Y-m'));
            }
        }

        $this->table(
            ['Metric', 'Count'],
            [
                ['training_logs rows created', $created],
                ['rows updated (completed false -> true)', $promoted],
                ['rows already completed=true', $alreadyOk],
                ['skipped orphan sessions (no client)', $skippedOrphan],
                ['distinct clients touched', count($touchedClients)],
                ['dry-run', $dryRun ? 'yes' : 'no'],
            ],
        );

        return self::SUCCESS;
    }
}
