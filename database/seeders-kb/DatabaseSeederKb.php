<?php

declare(strict_types=1);

namespace Database\SeedersKb;

use Illuminate\Database\Seeder;

/**
 * Master seeder de wellcore_kb.
 *
 * Invoca los 7 seeders en orden:
 *   1. MethodologiesSeeder           (8 metodologías)
 *   2. PrinciplesSeeder              (15 principios)
 *   3. ExerciseMetadataSeeder        (30 ejercicios curados)
 *   4. MethodologyRulesSeeder        (15 rules — depende de #1)
 *   5. DecisionRulesSeeder           (10 rules — depende de #1)
 *   6. LintRulesSeeder               (20 rules iniciales)
 *   7. PlanTemplatesLocalSeeder      (5 placeholders)
 *
 * Idempotente: usa upsert por slug/code/alias donde aplica.
 *
 * Uso:
 *   php artisan kb:seed                # invoca este master
 *   php artisan db:seed --database=kb --class='Database\\SeedersKb\\DatabaseSeederKb'   # alternativa Laravel-nativa
 */
final class DatabaseSeederKb extends Seeder
{
    public function run(): void
    {
        $this->command?->info('═══ Seeding wellcore_kb ═══');

        $this->call([
            MethodologiesSeeder::class,
            PrinciplesSeeder::class,
            ExerciseMetadataSeeder::class,
            MethodologyRulesSeeder::class,
            DecisionRulesSeeder::class,
            LintRulesSeeder::class,
            PlanTemplatesLocalSeeder::class,
        ]);

        $this->command?->info('═══ Seed completado ═══');
        $this->command?->info('Próximo paso: Sprint 1 — construir el linter aislado y correrlo contra fixtures (CASOS-REALES/CRISTIAN_*).');
    }
}
