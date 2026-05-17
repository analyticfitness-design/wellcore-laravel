<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * kb:status — muestra el estado de wellcore_kb: tablas presentes y cuenta de filas por tabla.
 *
 * Alerta si alguna tabla esperada falta o está vacía.
 *
 * Uso:
 *   php artisan kb:status
 */
final class KbStatusCommand extends Command
{
    protected $signature = 'kb:status';

    protected $description = 'Muestra estado de wellcore_kb (tablas + cuenta filas) y alerta si falta seed';

    /**
     * Tablas esperadas y mínimo de filas que debería tener cada una post-seed.
     * Si alguna está por debajo del mínimo, se considera "necesita seed".
     */
    private const EXPECTED_TABLES = [
        'methodologies' => 8,
        'principles' => 15,
        'exercise_metadata' => 30,
        'methodology_rules' => 15,
        'decision_rules' => 10,
        'lint_rules' => 20,
        'plan_templates_local' => 5,
        'corpus_embeddings' => 0, // opcional Sprint 3+
    ];

    public function handle(): int
    {
        $this->info('═══ kb:status — wellcore_kb ═══');
        $this->newLine();

        try {
            // Trigger conexión para fallar rápido si no está disponible
            DB::connection('kb')->getPdo();
        } catch (\Throwable $e) {
            $this->error('No pude conectar a wellcore_kb: ' . $e->getMessage());
            $this->warn('Corre `php artisan kb:install` primero.');
            return self::FAILURE;
        }

        $rows = [];
        $needsSeed = false;
        $missingTables = false;

        foreach (self::EXPECTED_TABLES as $table => $minRows) {
            if (! Schema::connection('kb')->hasTable($table)) {
                $rows[] = [$table, '—', '✗ NO EXISTE', '— migrar primero'];
                $missingTables = true;
                continue;
            }

            $count = (int) DB::connection('kb')->table($table)->count();
            $status = $count >= $minRows ? '✓' : ($minRows === 0 ? '○ vacío (ok MVP)' : '⚠ necesita seed');
            $note = match (true) {
                $count >= $minRows && $minRows > 0 => "min esperado: $minRows",
                $minRows === 0 => 'opcional Sprint 3+',
                default => "esperado >= $minRows",
            };

            if ($count < $minRows && $minRows > 0) {
                $needsSeed = true;
            }

            $rows[] = [$table, (string) $count, $status, $note];
        }

        $this->table(['Tabla', 'Filas', 'Status', 'Nota'], $rows);
        $this->newLine();

        if ($missingTables) {
            $this->error('Hay tablas faltantes. Corre: php artisan migrate --database=kb --path=database/migrations-kb');
            return self::FAILURE;
        }

        if ($needsSeed) {
            $this->warn('Una o más tablas necesitan seed. Corre: php artisan kb:seed');
            return self::SUCCESS;
        }

        $this->info('✓ wellcore_kb está sana y completamente seedeada para MVP.');

        // Engine killswitch info
        $enabled = config('plan_engine.enabled', env('WC_ENGINE_V2_ENABLED', false));
        if (! $enabled) {
            $this->newLine();
            $this->warn("ℹ Motor v2 está DESACTIVADO (WC_ENGINE_V2_ENABLED=false). Para activar: editar .env y restart.");
        } else {
            $this->newLine();
            $this->info('✓ Motor v2 ACTIVADO (WC_ENGINE_V2_ENABLED=true).');
        }

        return self::SUCCESS;
    }
}
