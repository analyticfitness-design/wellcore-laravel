<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\Marketing\DropStatus;
use App\Models\CoachContentDrop;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class RolloverDropsCommand extends Command
{
    protected $signature   = 'marketing:rollover-drops
                                {--from-week= : ISO week origen (default: semana pasada)}
                                {--from-year= : ISO year origen (default: año de semana pasada)}
                                {--dry-run    : Solo muestra qué haría, sin crear registros}';

    protected $description = 'Clona los drops de la semana anterior a la semana actual para coaches que ya tenían drop visible.';

    public function handle(): int
    {
        $prevMonday = now()->subWeek()->startOfWeek();
        $thisMonday = now()->startOfWeek();

        $fromYear = (int) ($this->option('from-year') ?? $prevMonday->isoFormat('GGGG'));
        $fromWeek = (int) ($this->option('from-week') ?? $prevMonday->isoFormat('W'));
        $toYear   = (int) $thisMonday->isoFormat('GGGG');
        $toWeek   = (int) $thisMonday->isoFormat('W');

        $this->info("Origen:  año={$fromYear} semana={$fromWeek}");
        $this->info("Destino: año={$toYear}   semana={$toWeek}");

        $sources = CoachContentDrop::where('iso_year', $fromYear)
            ->where('iso_week', $fromWeek)
            ->whereIn('status', [
                DropStatus::Ready->value,
                DropStatus::InProgress->value,
                DropStatus::Completed->value,
            ])
            ->get();

        if ($sources->isEmpty()) {
            $this->warn("No hay drops visibles en semana {$fromYear}-W{$fromWeek}.");
            return self::SUCCESS;
        }

        $this->info("Encontrados {$sources->count()} drops para clonar.");

        $existingCoachIds = CoachContentDrop::where('iso_year', $toYear)
            ->where('iso_week', $toWeek)
            ->pluck('coach_id')
            ->flip();

        $created = 0;
        $skipped = 0;

        foreach ($sources as $source) {
            if (isset($existingCoachIds[$source->coach_id])) {
                $this->line("  [SKIP] Coach {$source->coach_id} ya tiene drop en semana {$toYear}-W{$toWeek}");
                $skipped++;
                continue;
            }

            $this->line("  [CLONE] Coach {$source->coach_id} (drop #{$source->id}) → semana {$toYear}-W{$toWeek}");

            if ($this->option('dry-run')) {
                $created++;
                continue;
            }

            DB::transaction(function () use ($source, $toYear, $toWeek, $thisMonday) {
                $newDrop = CoachContentDrop::create([
                    'coach_id'         => $source->coach_id,
                    'iso_year'         => $toYear,
                    'iso_week'         => $toWeek,
                    'week_starts_on'   => $thisMonday->toDateString(),
                    'status'           => DropStatus::Ready->value,
                    'content'          => $source->content,
                    'intake_snapshot'  => $source->intake_snapshot,
                    'original_content' => $source->content,
                    'generated_at'     => now(),
                    'reviewed_at'      => now(),
                    'approved_at'      => now(),
                    'ready_at'         => now(),
                    'reviewed_by_id'   => $source->reviewed_by_id,
                    'approved_by_id'   => $source->approved_by_id,
                ]);

                Cache::forget("coach_drop_v3:{$newDrop->coach_id}:{$toYear}:{$toWeek}");
            });

            $created++;
        }

        $dryTag = $this->option('dry-run') ? ' (dry-run)' : '';
        $this->info("Resultado{$dryTag}: {$created} clonados, {$skipped} omitidos.");

        return self::SUCCESS;
    }
}
