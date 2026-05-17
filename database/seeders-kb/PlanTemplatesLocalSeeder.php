<?php

declare(strict_types=1);

namespace Database\SeedersKb;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 5 templates placeholder iniciales.
 *
 * Estos son SHELLS — el `structure_json` queda con un JSON mínimo válido pero
 * el contenido real se llena cuando se captura el primer plan exitoso de un
 * cliente real vía `/kb-capture-template` (Sprint 2+).
 *
 * Quality score 30 (placeholder — bajo intencionalmente). El motor v2 los va a
 * descartar como starting point hasta que tengan structure_json real y score >= 70.
 *
 * Decisión Daniel D4 (2026-05-17): source NUNCA puede ser 'ai_generated'.
 * Source MVP placeholder = 'manual_daniel' (Daniel completa cuando captura plan real).
 *
 * Ver docs/wellcore-engine-v2/03-knowledge-base-schema.md §3.5
 * Ver docs/wellcore-engine-v2/08-weekly-loop-daniel.md §5 (workflow captura)
 */
final class PlanTemplatesLocalSeeder extends Seeder
{
    public function run(): void
    {
        $now = now()->toDateTimeString();
        $placeholderJson = json_encode(['_placeholder' => true, '_todo' => 'Capturar plan real exitoso con /kb-capture-template']);

        $rows = [
            [
                'name' => 'Placeholder — Esencial Hombre Intermedio 5d Hipertrofia',
                'vertical' => 'entrenamiento',
                'target_profile_json' => json_encode([
                    'gender' => 'M',
                    'age_range' => [25, 45],
                    'level' => 'intermedio',
                    'goal' => 'hipertrofia',
                    'days' => 5,
                    'equipment' => ['gym_completo'],
                ]),
                'structure_json' => $placeholderJson,
                'source' => 'manual_daniel',
                'quality_score' => 30,
                'times_used' => 0,
                'last_used_at' => null,
                'created_by' => 'seed-mvp-sprint-0',
                'version' => '0.1-placeholder',
                'status' => 'experimental',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Placeholder — Esencial Mujer Intermedia 4d Recomposición',
                'vertical' => 'entrenamiento',
                'target_profile_json' => json_encode([
                    'gender' => 'F',
                    'age_range' => [25, 45],
                    'level' => 'intermedio',
                    'goal' => 'recomposicion',
                    'days' => 4,
                    'equipment' => ['gym_completo'],
                ]),
                'structure_json' => $placeholderJson,
                'source' => 'manual_daniel',
                'quality_score' => 30,
                'times_used' => 0,
                'last_used_at' => null,
                'created_by' => 'seed-mvp-sprint-0',
                'version' => '0.1-placeholder',
                'status' => 'experimental',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Placeholder — Esencial Cualquiera Pérdida de Grasa 5 comidas',
                'vertical' => 'nutricion',
                'target_profile_json' => json_encode([
                    'goal' => 'perdida_grasa',
                    'meals_per_day' => 5,
                    'dietary_restrictions' => [],
                ]),
                'structure_json' => $placeholderJson,
                'source' => 'manual_daniel',
                'quality_score' => 30,
                'times_used' => 0,
                'last_used_at' => null,
                'created_by' => 'seed-mvp-sprint-0',
                'version' => '0.1-placeholder',
                'status' => 'experimental',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Placeholder — Stack Esencial 6 suplementos',
                'vertical' => 'suplementacion',
                'target_profile_json' => json_encode([
                    'tier' => 'esencial',
                    'goal' => 'any',
                ]),
                'structure_json' => $placeholderJson,
                'source' => 'manual_daniel',
                'quality_score' => 30,
                'times_used' => 0,
                'last_used_at' => null,
                'created_by' => 'seed-mvp-sprint-0',
                'version' => '0.1-placeholder',
                'status' => 'experimental',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Placeholder — Hábitos Sueño + Hidratación + Movilidad',
                'vertical' => 'habitos',
                'target_profile_json' => json_encode([
                    'tier' => 'any',
                ]),
                'structure_json' => $placeholderJson,
                'source' => 'manual_daniel',
                'quality_score' => 30,
                'times_used' => 0,
                'last_used_at' => null,
                'created_by' => 'seed-mvp-sprint-0',
                'version' => '0.1-placeholder',
                'status' => 'experimental',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::connection('kb')
            ->table('plan_templates_local')
            ->insert($rows);

        $this->command?->info('Seeded ' . count($rows) . ' plan templates (placeholders — capture real plans with /kb-capture-template).');
    }
}
