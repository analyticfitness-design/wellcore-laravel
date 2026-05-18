<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Kb\ExerciseMetadata;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * kb:clean-exercise-catalog — sincroniza wellcore_kb.exercise_metadata con la
 * lista REAL de gifs en el repo v2.
 *
 * Acciones:
 *   1. Descarga lista de archivos de `wellcore-exercise-gifs-v2`.
 *   2. Marca como `gif_url_status='broken'` cualquier row que apunte a un
 *      filename que NO está en el repo v2.
 *   3. (Opcional con --delete) elimina filas de status broken.
 *   4. Para los 3 nombres que difieren entre repo viejo y v2, actualiza el
 *      gif_filename.
 *
 * Idempotente. Solo wellcore_kb local.
 */
final class KbCleanExerciseCatalogCommand extends Command
{
    protected $signature = 'kb:clean-exercise-catalog
                            {--delete : elimina filas broken además de marcarlas}
                            {--dry-run : muestra qué se haría sin tocar DB}';

    protected $description = 'Sincroniza exercise_metadata con el repo v2 (marca broken los que sobran).';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $delete = (bool) $this->option('delete');

        $this->info('Descargando lista del repo wellcore-exercise-gifs-v2...');
        $repoFiles = $this->fetchRepoFiles();
        if ($repoFiles === null) {
            $this->error('No pude obtener la lista del repo v2.');
            return 2;
        }
        $repoSet = array_flip($repoFiles);
        $this->info('Repo v2 contiene ' . count($repoFiles) . ' .gif files.');

        $rows = ExerciseMetadata::query()->get(['id', 'alias', 'gif_filename', 'gif_url_status']);
        $this->info('exercise_metadata tiene ' . $rows->count() . ' rows.');

        $stats = ['ok' => 0, 'marked_broken' => 0, 'deleted' => 0, 'fixed_filename' => 0];

        foreach ($rows as $row) {
            $currentFilename = $row->gif_filename ?? ($row->alias . '.gif');

            if (isset($repoSet[$currentFilename])) {
                // OK: el filename existe en el repo v2
                if ($row->gif_url_status !== 'ok') {
                    if (! $dryRun) {
                        $row->update(['gif_url_status' => 'ok', 'gif_url_verified_at' => now()]);
                    }
                    $this->line("  ✓ {$row->alias}: status → ok");
                }
                $stats['ok']++;
                continue;
            }

            // Filename no existe en repo v2 — buscar alternativa con nombre normalizado
            $normalized = $this->normalizeFilename($currentFilename);
            if ($normalized !== $currentFilename && isset($repoSet[$normalized])) {
                if (! $dryRun) {
                    $row->update([
                        'gif_filename' => $normalized,
                        'gif_url_status' => 'ok',
                        'gif_url_verified_at' => now(),
                    ]);
                }
                $this->line("  ~ {$row->alias}: $currentFilename → $normalized");
                $stats['fixed_filename']++;
                continue;
            }

            // No matchea con ningún archivo del repo v2 — broken o delete
            if ($delete) {
                if (! $dryRun) {
                    $row->delete();
                }
                $this->line("  ✗ {$row->alias}: DELETE ($currentFilename no existe en repo v2)");
                $stats['deleted']++;
            } else {
                if (! $dryRun) {
                    $row->update(['gif_url_status' => 'broken', 'gif_url_verified_at' => now()]);
                }
                $this->line("  ✗ {$row->alias}: broken ($currentFilename no existe en repo v2)");
                $stats['marked_broken']++;
            }
        }

        $this->newLine();
        $this->info(($dryRun ? '[DRY-RUN] ' : '') . 'Resumen:');
        $this->line("  ✓ ok:               {$stats['ok']}");
        $this->line("  ~ fixed filename:   {$stats['fixed_filename']}");
        $this->line("  ✗ marked broken:    {$stats['marked_broken']}");
        $this->line("  ✗ deleted:          {$stats['deleted']}");

        return 0;
    }

    /**
     * Normaliza un filename del repo viejo al estilo del v2:
     *  - "Crunches-abdominales.gif" → "crunches-abdominales.gif" (lowercase)
     *  - "a-una mano.gif" → "a-una-mano.gif" (espacios → guiones)
     */
    private function normalizeFilename(string $filename): string
    {
        $base = preg_replace('/\.gif$/i', '', $filename);
        $base = mb_strtolower($base);
        $base = preg_replace('/\s+/', '-', $base);
        $base = preg_replace('/-+/', '-', $base);
        return trim($base, '-') . '.gif';
    }

    /**
     * @return string[]|null
     */
    private function fetchRepoFiles(): ?array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders(['Accept' => 'application/vnd.github.v3+json'])
                ->get('https://api.github.com/repos/analyticfitness-design/wellcore-exercise-gifs-v2/git/trees/main?recursive=1');

            if (! $response->successful()) {
                return null;
            }

            $tree = $response->json('tree') ?? [];
            $gifs = array_filter($tree, fn ($n) => str_ends_with($n['path'] ?? '', '.gif'));
            return array_values(array_map(fn ($n) => $n['path'], $gifs));
        } catch (Throwable) {
            return null;
        }
    }
}
