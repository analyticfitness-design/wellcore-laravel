<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Kb\ExerciseMetadata;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * plan:gif-recheck — revalida con HTTP HEAD el gif_url_status de exercise_metadata.
 *
 * Complementa kb:verify-gifs pero permite scope:
 *   --only-broken         Solo re-check rows que están en 'broken'
 *   --only-stale=DAYS     Solo rows con gif_url_verified_at más viejo que N días
 *   --limit=N             Máximo N rows
 *   --dry-run             No escribe gif_url_status, solo reporta diff
 *
 * Útil después de subir GIFs nuevos al repo v2 — re-marca como OK los que
 * antes estaban broken.
 */
final class PlanGifRecheckCommand extends Command
{
    protected $signature = 'plan:gif-recheck
                            {--only-broken : solo re-check rows con gif_url_status=broken}
                            {--only-stale=0 : solo rows con verified_at más viejo que N días}
                            {--limit=50 : tope de rows a procesar}
                            {--dry-run : no escribe, solo reporta}';

    protected $description = 'Re-valida con HTTP HEAD el gif_url_status del catálogo (con scoping).';

    public function handle(): int
    {
        $query = ExerciseMetadata::query();

        if ($this->option('only-broken')) {
            $query->where('gif_url_status', 'broken');
        }

        $staleDays = (int) $this->option('only-stale');
        if ($staleDays > 0) {
            $query->where(function ($q) use ($staleDays) {
                $q->whereNull('gif_url_verified_at')
                    ->orWhere('gif_url_verified_at', '<', now()->subDays($staleDays));
            });
        }

        $rows = $query->limit((int) $this->option('limit'))->get();
        $this->info('Procesando ' . $rows->count() . ' rows…');

        $stats = ['ok' => 0, 'broken' => 0, 'changed' => 0, 'skipped' => 0];
        $dryRun = (bool) $this->option('dry-run');

        foreach ($rows as $row) {
            if (empty($row->gif_filename)) {
                $stats['skipped']++;
                continue;
            }

            $url = ExerciseMetadata::GIF_REPO_BASE_URL . $row->gif_filename;
            try {
                $resp = Http::timeout(10)->head($url);
                $newStatus = $resp->successful() ? 'ok' : 'broken';
            } catch (\Throwable) {
                $newStatus = 'broken';
            }

            $oldStatus = $row->gif_url_status;
            $stats[$newStatus]++;
            if ($oldStatus !== $newStatus) {
                $stats['changed']++;
                $this->line("  · #{$row->id} {$row->alias}: $oldStatus → $newStatus");
            }

            if (! $dryRun) {
                $row->gif_url_status = $newStatus;
                $row->gif_url_verified_at = now();
                $row->save();
            }
        }

        $this->newLine();
        $this->info('═══ resumen ═══');
        $this->line('OK:      ' . $stats['ok']);
        $this->line('Broken:  ' . $stats['broken']);
        $this->line('Changed: ' . $stats['changed']);
        $this->line('Skipped: ' . $stats['skipped']);
        if ($dryRun) {
            $this->warn('[DRY-RUN] No se escribió. Quitá --dry-run para aplicar.');
        }
        return 0;
    }
}
