<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Database\SeedersKb\DatabaseSeederKb;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * kb:seed — corre el master seeder de wellcore_kb (8 methodologies + 15 principles +
 * 30 exercises + 15 methodology_rules + 10 decision_rules + 20 lint_rules + 5 templates).
 *
 * Idempotente: usa upsert por slug/code/alias donde aplica. Re-correrlo NO duplica.
 *
 * Uso:
 *   php artisan kb:seed
 *
 * Ver docs/wellcore-engine-v2/03-knowledge-base-schema.md §4 (estrategia de seeding)
 */
final class KbSeedCommand extends Command
{
    protected $signature = 'kb:seed';

    protected $description = 'Corre el seed inicial de wellcore_kb (motor v2 — 7 seeders)';

    public function handle(): int
    {
        $this->info('═══ kb:seed — invocando DatabaseSeederKb ═══');

        $exitCode = Artisan::call('db:seed', [
            '--database' => 'kb',
            '--class' => DatabaseSeederKb::class,
            '--force' => true,
        ], $this->output);

        if ($exitCode !== 0) {
            $this->error('Seed falló. Verifica que `kb:install` ya haya creado la DB y migrado las 8 tablas.');
            return self::FAILURE;
        }

        $this->info('');
        $this->info('✓ Seed completado. Verifica con: php artisan kb:status');
        return self::SUCCESS;
    }
}
