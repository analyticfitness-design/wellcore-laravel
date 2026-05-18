<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * kb:diff-snapshots — compara 2 SQL dumps generados por kb:export-snapshot.
 *
 * No es un diff exhaustivo SQL parser. Solo cuenta INSERT INTO `<tabla>`
 * y reporta cambios de count por tabla. Para diff fino, usar herramientas externas.
 *
 * Uso típico: comparar el snapshot de hoy con el de la semana pasada para ver
 * cuántos principles / methodologies / lint_rules se agregaron.
 *
 * Args:
 *   {old}  Path al snapshot anterior
 *   {new}  Path al snapshot nuevo
 */
final class KbDiffSnapshotsCommand extends Command
{
    protected $signature = 'kb:diff-snapshots {old : path SQL anterior} {new : path SQL nuevo} {--json : output JSON}';

    protected $description = 'Compara 2 snapshots SQL kb por counts de INSERTs por tabla.';

    public function handle(): int
    {
        $old = $this->argument('old');
        $new = $this->argument('new');

        foreach (['old' => $old, 'new' => $new] as $label => $path) {
            if (! is_file($path)) {
                $this->error("$label ($path) no existe o no es archivo.");
                return 2;
            }
        }

        $oldCounts = $this->countInserts((string) $old);
        $newCounts = $this->countInserts((string) $new);

        $allTables = array_unique(array_merge(array_keys($oldCounts), array_keys($newCounts)));
        sort($allTables);

        $diff = [];
        foreach ($allTables as $t) {
            $o = $oldCounts[$t] ?? 0;
            $n = $newCounts[$t] ?? 0;
            $diff[$t] = ['old' => $o, 'new' => $n, 'delta' => $n - $o];
        }

        if ($this->option('json')) {
            $this->line(json_encode([
                'old' => $old,
                'new' => $new,
                'diff' => $diff,
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            return 0;
        }

        $this->info('═══ kb:diff-snapshots ═══');
        $this->line('old: ' . $old);
        $this->line('new: ' . $new);
        $this->newLine();
        $this->line(sprintf('%-30s %8s %8s %8s', 'TABLA', 'OLD', 'NEW', 'Δ'));
        $this->line(str_repeat('-', 58));
        foreach ($diff as $t => $d) {
            $sym = $d['delta'] > 0 ? '+' : ($d['delta'] < 0 ? '' : ' ');
            $this->line(sprintf('%-30s %8d %8d %s%d', $t, $d['old'], $d['new'], $sym, $d['delta']));
        }
        return 0;
    }

    /**
     * @return array<string, int>
     */
    private function countInserts(string $path): array
    {
        $contents = file_get_contents($path);
        if ($contents === false) {
            return [];
        }
        $counts = [];
        preg_match_all('/INSERT\s+INTO\s+`?([a-z_]+)`?/i', $contents, $matches);
        foreach ($matches[1] as $tabla) {
            $counts[$tabla] = ($counts[$tabla] ?? 0) + 1;
        }
        return $counts;
    }
}
