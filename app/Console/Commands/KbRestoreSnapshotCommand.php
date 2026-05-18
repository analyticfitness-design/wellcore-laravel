<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * kb:restore-snapshot — inverso de kb:export-snapshot.
 *
 * Restaura un snapshot SQL a wellcore_kb. NO toca producción wellcore_fitness.
 *
 * Safety:
 *   - DRY_RUN por default (parsea SQL pero no ejecuta)
 *   - --confirm requerido para escribir real
 *   - --tables=lista filtra qué tablas restaurar (subset)
 *   - --truncate-first borra las tablas target antes de restaurar
 *
 * Casos de uso:
 *   - Rollback rápido si un seeder rompe algo
 *   - Restore en otra máquina dev
 *   - Pre-test setup: snapshot conocido → tests
 */
final class KbRestoreSnapshotCommand extends Command
{
    protected $signature = 'kb:restore-snapshot
                            {file : path al SQL snapshot}
                            {--confirm : escribir real (default = dry-run)}
                            {--tables= : lista CSV de tablas a restaurar (default = todas en el archivo)}
                            {--truncate-first : truncate tablas target antes de insertar}';

    protected $description = 'Restaura un wellcore_kb snapshot SQL (inverso de kb:export-snapshot).';

    public function handle(): int
    {
        $file = $this->argument('file');
        if (! is_file($file)) {
            $this->error("Archivo no encontrado: $file");
            return 2;
        }

        $sql = (string) file_get_contents($file);
        if ($sql === '') {
            $this->error('Archivo vacío.');
            return 2;
        }

        $statements = $this->parseStatements($sql);
        if ($statements === []) {
            $this->error('Sin sentencias SQL parseables en el archivo.');
            return 2;
        }

        $tablesFilter = $this->option('tables')
            ? array_map('trim', explode(',', $this->option('tables')))
            : null;

        $byTable = $this->groupStatementsByTable($statements);
        if ($tablesFilter !== null) {
            $byTable = array_intersect_key($byTable, array_flip($tablesFilter));
        }

        $dryRun = ! $this->option('confirm');
        $truncateFirst = (bool) $this->option('truncate-first');

        $this->info('═══ kb:restore-snapshot ═══');
        $this->line('Archivo:        ' . basename($file));
        $this->line('Modo:           ' . ($dryRun ? 'DRY-RUN (no escribe)' : '⚠️  WRITE (escribe real)'));
        $this->line('Truncate first: ' . ($truncateFirst ? 'sí' : 'no'));
        $this->line('Tablas:         ' . count($byTable));
        $totalStatements = array_sum(array_map('count', $byTable));
        $this->line('Statements:     ' . $totalStatements);
        $this->newLine();

        foreach ($byTable as $table => $stmts) {
            $this->line(sprintf('  · %-32s · %d statements', $table, count($stmts)));
        }
        $this->newLine();

        if ($dryRun) {
            $this->info('[DRY-RUN] Sin escribir. Re-ejecutar con --confirm para aplicar.');
            return 0;
        }

        // WRITE mode
        $stats = ['truncated' => 0, 'inserted' => 0, 'failed' => 0];
        try {
            DB::connection('kb')->statement('SET FOREIGN_KEY_CHECKS = 0');

            if ($truncateFirst) {
                foreach (array_keys($byTable) as $table) {
                    try {
                        DB::connection('kb')->table($table)->truncate();
                        $stats['truncated']++;
                    } catch (Throwable $e) {
                        $this->warn("  ! truncate $table falló: " . $e->getMessage());
                    }
                }
            }

            foreach ($byTable as $table => $stmts) {
                foreach ($stmts as $stmt) {
                    try {
                        DB::connection('kb')->statement($stmt);
                        $stats['inserted']++;
                    } catch (Throwable $e) {
                        $stats['failed']++;
                        if ($stats['failed'] <= 3) {
                            $this->warn('  ! INSERT falló: ' . mb_substr($e->getMessage(), 0, 200));
                        }
                    }
                }
            }
        } finally {
            DB::connection('kb')->statement('SET FOREIGN_KEY_CHECKS = 1');
        }

        $this->newLine();
        $this->info('✓ Restore completo:');
        $this->line("  · Truncated: {$stats['truncated']}");
        $this->line("  · Inserted:  {$stats['inserted']}");
        $this->line("  · Failed:    {$stats['failed']}");

        return $stats['failed'] > 0 ? 1 : 0;
    }

    /**
     * Parsea statements del SQL: solo extrae INSERT INTO y CREATE TABLE
     * (skip SET, comentarios, etc.).
     *
     * @return string[]
     */
    private function parseStatements(string $sql): array
    {
        $statements = [];
        // Cada line que empiece con INSERT INTO termina en ;
        // Es un SQL dump simple — una statement por línea.
        $lines = explode("\n", $sql);
        $current = '';
        foreach ($lines as $line) {
            $trimmed = ltrim($line);
            // Skip comentarios y SET FOREIGN_KEY_CHECKS
            if (str_starts_with($trimmed, '--') || $trimmed === '') {
                continue;
            }
            if (str_starts_with(strtoupper($trimmed), 'SET ')) {
                continue;
            }

            $current .= ($current === '' ? '' : "\n") . $line;
            if (str_ends_with(rtrim($line), ';')) {
                $statements[] = trim(rtrim($current, " \n\r\t;"));
                $current = '';
            }
        }
        return array_values(array_filter($statements, fn ($s) => $s !== ''));
    }

    /**
     * Agrupa INSERT statements por tabla destino.
     * @param string[] $statements
     * @return array<string, string[]>
     */
    private function groupStatementsByTable(array $statements): array
    {
        $byTable = [];
        foreach ($statements as $s) {
            if (preg_match('/^INSERT\s+INTO\s+`?([a-z_]+)`?/i', $s, $m)) {
                $byTable[$m[1]][] = $s;
            }
        }
        return $byTable;
    }
}
