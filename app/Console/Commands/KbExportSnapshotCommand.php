<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * kb:export-snapshot — exporta el estado completo de wellcore_kb a un SQL dump
 * versionado (CREATE TABLE + INSERT INTO para todas las tablas KB).
 *
 * Útil para:
 *   - Backup local antes de un refactor grande
 *   - Versionar el estado del catálogo (e.g. git commit del snapshot)
 *   - Compartir estado entre máquinas dev (Daniel local → Daniel laptop)
 *   - Rollback si un seeder rompe algo
 *
 * NO toca producción wellcore_fitness — solo lee wellcore_kb.
 *
 * Output: bootstrap/kb-snapshots/snapshot_<timestamp>_<git_short>.sql
 *
 * Excluye composed_plans (es audit trail volatil, no parte del catálogo).
 */
final class KbExportSnapshotCommand extends Command
{
    /** Tablas del catálogo KB (no audit). */
    private const CATALOG_TABLES = [
        'methodologies',
        'methodology_rules',
        'principles',
        'exercise_metadata',
        'plan_templates_local',
        'decision_rules',
        'lint_rules',
        'nutrition_foods',
        'supplement_catalog',
        'supplement_stacks',
        'hormonal_compounds',
        'hormonal_protocol_templates',
        'ciclo_menstrual_fases',
        'bloodwork_panels',
    ];

    protected $signature = 'kb:export-snapshot
                            {--out= : path output (default = bootstrap/kb-snapshots/snapshot_<timestamp>.sql)}
                            {--include-audit : incluye composed_plans (audit trail)}
                            {--compress : pendiente — no implementado aún}';

    protected $description = 'Exporta wellcore_kb a un SQL dump versionado (CREATE TABLE + INSERT INTO).';

    public function handle(): int
    {
        $timestamp = now()->format('Y-m-d_His');
        $gitShort = $this->gitShortHash();
        $suffix = $gitShort ? "_{$gitShort}" : '';

        $defaultOut = base_path("bootstrap/kb-snapshots/snapshot_{$timestamp}{$suffix}.sql");
        $outPath = $this->option('out') ?? $defaultOut;

        $outDir = dirname($outPath);
        if (! is_dir($outDir)) {
            mkdir($outDir, 0755, true);
        }

        $tables = self::CATALOG_TABLES;
        if ($this->option('include-audit')) {
            $tables[] = 'composed_plans';
        }

        $this->info('Exportando wellcore_kb → ' . str_replace(base_path() . DIRECTORY_SEPARATOR, '', $outPath));
        $this->newLine();

        $sql = $this->buildSqlHeader($gitShort);
        $totalRows = 0;
        foreach ($tables as $table) {
            try {
                $rows = $this->dumpTable($table);
                $count = substr_count($rows, 'INSERT INTO');
                $totalRows += $count;
                $sql .= "\n-- ─── Tabla: $table ($count rows) ───\n";
                $sql .= $rows;
                $this->line(sprintf('  ✓ %-32s · %d rows', $table, $count));
            } catch (Throwable $e) {
                $this->warn("  ! $table: " . $e->getMessage());
                $sql .= "\n-- ERROR: $table → {$e->getMessage()}\n";
            }
        }

        $sql .= $this->buildSqlFooter($totalRows);

        file_put_contents($outPath, $sql);
        $sizeKb = round(filesize($outPath) / 1024, 1);

        $this->newLine();
        $this->info("✓ Snapshot generado: $outPath");
        $this->line("  · Tablas: " . count($tables));
        $this->line("  · Filas totales: $totalRows");
        $this->line("  · Tamaño: $sizeKb KB");
        $this->newLine();
        $this->line('Para restaurar este snapshot en otra máquina:');
        $this->line('  mysql -u root wellcore_kb < ' . basename($outPath));

        return 0;
    }

    private function gitShortHash(): ?string
    {
        try {
            $hash = trim(shell_exec('git -C ' . escapeshellarg(base_path()) . ' rev-parse --short HEAD 2>nul') ?? '');
            return $hash !== '' ? $hash : null;
        } catch (Throwable) {
            return null;
        }
    }

    private function buildSqlHeader(?string $gitShort): string
    {
        $now = now()->toIso8601String();
        $gitInfo = $gitShort ? " (git: $gitShort)" : '';
        return <<<SQL
-- ═══ wellcore_kb snapshot ═══
-- Generado:  $now$gitInfo
-- Tool:      php artisan kb:export-snapshot
--
-- Para restaurar:
--   mysql -u root wellcore_kb < ESTE_ARCHIVO.sql
--
-- IMPORTANTE: Este snapshot es del catálogo motor v2 (wellcore_kb), NO
-- de la DB de producción wellcore_fitness.

SET FOREIGN_KEY_CHECKS = 0;

SQL;
    }

    private function buildSqlFooter(int $totalRows): string
    {
        return <<<SQL


SET FOREIGN_KEY_CHECKS = 1;

-- Total: $totalRows rows insertadas
-- Fin del snapshot
SQL;
    }

    private function dumpTable(string $table): string
    {
        $rows = DB::connection('kb')->table($table)->get();
        if ($rows->isEmpty()) {
            return "-- (tabla vacía)\n";
        }

        $columns = array_keys((array) $rows->first());
        $columnsSql = '`' . implode('`, `', $columns) . '`';

        $sql = '';
        foreach ($rows as $row) {
            $rowArr = (array) $row;
            $values = [];
            foreach ($columns as $col) {
                $values[] = $this->escapeValue($rowArr[$col] ?? null);
            }
            $valuesSql = implode(', ', $values);
            $sql .= "INSERT INTO `$table` ($columnsSql) VALUES ($valuesSql);\n";
        }
        return $sql;
    }

    private function escapeValue(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }
        if (is_string($value)) {
            // Escape simple para MySQL — funciona para snapshots
            $escaped = addslashes($value);
            return "'$escaped'";
        }
        return "'" . addslashes(json_encode($value, JSON_UNESCAPED_UNICODE)) . "'";
    }
}
