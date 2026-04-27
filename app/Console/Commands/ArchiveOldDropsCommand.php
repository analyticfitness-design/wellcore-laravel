<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\Marketing\DropStatus;
use App\Models\CoachContentDrop;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

final class ArchiveOldDropsCommand extends Command
{
    protected $signature = 'wellcore:archive-old-drops {--days=30 : Días desde completed_at antes de archivar}';

    protected $description = 'Archiva drops completados con más de N días';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $affected = CoachContentDrop::where('status', DropStatus::Completed)
            ->where('completed_at', '<=', $cutoff)
            ->get(['id', 'coach_id', 'iso_year', 'iso_week']);

        $count = CoachContentDrop::where('status', DropStatus::Completed)
            ->where('completed_at', '<=', $cutoff)
            ->update(['status' => DropStatus::Archived->value]);

        foreach ($affected as $row) {
            Cache::forget("coach_drop_v3:{$row->coach_id}:{$row->iso_year}:{$row->iso_week}");
        }

        $this->info("Archivados {$count} drops completados antes de {$cutoff->toDateTimeString()}.");
        $this->info('Caches invalidadas: '.$affected->count());

        return self::SUCCESS;
    }
}
