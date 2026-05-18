<?php

declare(strict_types=1);

namespace Database\SeedersKb;

use Illuminate\Database\Seeder;

/**
 * Master seeder de wellcore_kb.
 *
 * Invoca los seeders en orden:
 *   ─ Sprint 0 (legacy, schema hardcoded) ───────────────────────────
 *   1. MethodologiesSeeder           (8 metodologías hardcoded)
 *   2. PrinciplesSeeder              (15 principios)
 *   3. ExerciseMetadataSeeder        (30 ejercicios curados)
 *   4. MethodologyRulesSeeder        (15 rules — depende de #1)
 *   5. DecisionRulesSeeder           (10 rules — depende de #1)
 *   6. LintRulesSeeder               (20 rules iniciales)
 *   7. PlanTemplatesLocalSeeder      (5 placeholders)
 *
 *   ─ Sprint 0.5 (lee JSONs producidos por prompts Piezas 3-6) ──────
 *   8. NutritionFoodsSeeder          (100 alimentos desde JSON)
 *   9. SupplementCatalogSeeder       (28 suplementos desde JSON)
 *  10. SupplementStacksSeeder        (15 stacks desde JSON)
 *  11. HormonalProtocolsSeeder       (4 sub-tablas: compounds, templates, ciclo, bloodwork)
 *
 * Idempotente: usa upsert por slug/code/alias donde aplica.
 *
 * Uso:
 *   php artisan kb:seed                # invoca este master
 */
final class DatabaseSeederKb extends Seeder
{
    public function run(): void
    {
        $this->command?->info('═══ Seeding wellcore_kb ═══');

        $this->call([
            // Sprint 0 (legacy)
            MethodologiesSeeder::class,
            PrinciplesSeeder::class,
            ExerciseMetadataSeeder::class,
            MethodologyRulesSeeder::class,
            DecisionRulesSeeder::class,
            LintRulesSeeder::class,
            PlanTemplatesLocalSeeder::class,

            // Sprint 0.5 (JSON-driven desde docs/audit-motor-v2/)
            NutritionFoodsSeeder::class,
            SupplementCatalogSeeder::class,
            SupplementStacksSeeder::class,
            HormonalProtocolsSeeder::class,

            // Sprint 5 (overrides gif_filename — mapeo alias→repo real)
            ExerciseGifFilenameOverrideSeeder::class,
        ]);

        $this->command?->info('═══ Seed completado ═══');
        $this->command?->info('Próximo paso: php artisan kb:status para verificar counts por tabla.');
    }
}
