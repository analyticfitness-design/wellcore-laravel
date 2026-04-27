<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\Marketing\DropStatus;
use App\Models\CoachContentDrop;
use Illuminate\Console\Command;

final class ArchiveOldDropsCommand extends Command
{
    protected $signature = 'wellcore:archive-old-drops {--days=30 : Días desde completed_at antes de archivar}';

    protected $description = 'Archiva drops completados con más de N días';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $count = CoachContentDrop::where('status', DropStatus::Completed)
            ->where('completed_at', '<=', $cutoff)
            ->update(['status' => DropStatus::Archived->value]);

        $this->info("Archivados {$count} drops completados antes de {$cutoff->toDateTimeString()}.");

        return self::SUCCESS;
    }
}
