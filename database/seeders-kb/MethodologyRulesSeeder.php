<?php

declare(strict_types=1);

namespace Database\SeedersKb;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 15 reglas de elegibilidad para las 8 metodologías del seed inicial.
 *
 * Patrón: cada methodology tiene ~2 rules (1 hard_filter + 1 preference).
 * Hard filters descartan candidatas. Preferences suman/restan score.
 *
 * Ver docs/wellcore-engine-v2/03-knowledge-base-schema.md §3.2
 *
 * IMPORTANTE: este seeder se corre DESPUÉS de MethodologiesSeeder
 * (orden establecido en DatabaseSeederKb).
 */
final class MethodologyRulesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now()->toDateTimeString();

        // Map slug → id (las methodologies ya están en DB)
        $ids = DB::connection('kb')
            ->table('methodologies')
            ->pluck('id', 'slug')
            ->toArray();

        if (empty($ids)) {
            throw new \RuntimeException(
                'MethodologyRulesSeeder requiere que MethodologiesSeeder haya corrido primero. ' .
                'Ejecuta `php artisan kb:seed` (master) o `MethodologiesSeeder` antes.'
            );
        }

        $rows = [];

        // ─── body_part_split_5d (necesita 5 días, prefiere intermedio+hipertrofia) ──
        $rows[] = $this->row($ids, 'body_part_split_5d', 'hard_filter',
            ['min_days_required' => 5],
            0.00,
            'Body Part Split 5d requiere exactamente 5 días disponibles — si el cliente tiene menos, descartar.'
        );
        $rows[] = $this->row($ids, 'body_part_split_5d', 'preference',
            ['goal' => 'hipertrofia', 'level_in' => ['intermedio', 'avanzado']],
            2.50,
            'Body Part Split brilla en hipertrofia con clientes intermedios/avanzados que toleran volumen alto por grupo muscular.'
        );

        // ─── upper_lower_4d (necesita 4 días, flex en nivel) ─────────────────
        $rows[] = $this->row($ids, 'upper_lower_4d', 'hard_filter',
            ['days_in' => [4]],
            0.00,
            'Upper/Lower 4d requiere 4 días — con menos no se completan ambos splits, con más se desperdicia.'
        );
        $rows[] = $this->row($ids, 'upper_lower_4d', 'preference',
            ['goal_in' => ['hipertrofia', 'fuerza', 'recomposicion']],
            2.00,
            'Upper/Lower es el balance ideal entre volumen y frecuencia. Apto para hipertrofia, fuerza o recomposición.'
        );

        // ─── ppl_6d (necesita 6 días + avanzado) ─────────────────────────────
        $rows[] = $this->row($ids, 'ppl_6d', 'hard_filter',
            ['min_days_required' => 6],
            0.00,
            'PPL 6d requiere 6 días disponibles.'
        );
        $rows[] = $this->row($ids, 'ppl_6d', 'hard_filter',
            ['level_not_in' => ['principiante']],
            0.00,
            'PPL 6d demanda recuperación de avanzado. Para principiantes, recomendar Upper/Lower o Full Body 3d.'
        );

        // ─── iifym_deficit (perdida_grasa, any level) ────────────────────────
        $rows[] = $this->row($ids, 'iifym_deficit', 'hard_filter',
            ['vertical' => 'nutricion', 'goal' => 'perdida_grasa'],
            0.00,
            'IIFYM con déficit aplica específicamente a perdida_grasa.'
        );
        $rows[] = $this->row($ids, 'iifym_deficit', 'preference',
            ['dietary_restrictions_max' => 1],
            1.50,
            'Flexible Dieting funciona mejor cuando el cliente NO tiene restricciones rígidas. Si tiene 2+ restricciones (vegano + sin gluten + alergias), considerar otra metodología.'
        );

        // ─── mediterranea_recomp (recomposición, cualquier nivel) ────────────
        $rows[] = $this->row($ids, 'mediterranea_recomp', 'hard_filter',
            ['vertical' => 'nutricion', 'goal_in' => ['recomposicion', 'mantenimiento']],
            0.00,
            'Mediterránea aplica para recomposición o mantenimiento (no para déficit agresivo).'
        );
        $rows[] = $this->row($ids, 'mediterranea_recomp', 'preference',
            ['dietary_restrictions_includes' => ['mediterranea_friendly']],
            1.50,
            'Cliente con preferencia por alimentos enteros, pescado y aceite de oliva se adapta naturalmente.'
        );

        // ─── stack_basico (any) ──────────────────────────────────────────────
        $rows[] = $this->row($ids, 'stack_basico', 'hard_filter',
            ['vertical' => 'suplementacion'],
            0.00,
            'Stack básico aplica a cualquier cliente que pida vertical suplementación. Defaults seguros.'
        );

        // ─── habitos_sueno_hidratacion_basico (any) ──────────────────────────
        $rows[] = $this->row($ids, 'habitos_sueno_hidratacion_basico', 'hard_filter',
            ['vertical' => 'habitos'],
            0.00,
            'Hábitos básicos aplican a todo cliente. No tiene contraindicación.'
        );

        // ─── ciclo_hormonal_basico (Elite, intermedio+) ──────────────────────
        $rows[] = $this->row($ids, 'ciclo_hormonal_basico', 'hard_filter',
            ['vertical' => 'ciclo', 'gender' => 'F', 'tier_in' => ['elite', 'rise']],
            0.00,
            'Ciclo hormonal aplica solo a clientes femeninas con tier Elite o RISE (feature premium).'
        );
        $rows[] = $this->row($ids, 'ciclo_hormonal_basico', 'preference',
            ['has_cycle_tracking' => true],
            2.00,
            'Requiere que la cliente tenga tracking de ciclo activo (día 1 + duración promedio).'
        );
        $rows[] = $this->row($ids, 'ciclo_hormonal_basico', 'soft_filter',
            ['level' => 'principiante'],
            -1.50,
            'No descarta, pero penaliza score — principiantes deben consolidar hábitos básicos antes de adaptación por fase.'
        );

        // No hay key natural simple (applies_when_json es JSON, no soporta UNIQUE).
        // Las methodology_rules son 100% seed-controlled (no las edita un humano):
        // truncate-and-reinsert es idempotente y seguro acá.
        // FK methodology_id es cascadeOnDelete → no rompe nada.
        DB::connection('kb')->table('methodology_rules')->delete();

        DB::connection('kb')
            ->table('methodology_rules')
            ->insert($rows);

        $this->command?->info('Reseeded ' . count($rows) . ' methodology rules (truncate-and-reinsert).');
    }

    /**
     * Helper para construir una row de manera consistente.
     */
    private function row(array $ids, string $methodologySlug, string $ruleType, array $appliesWhen, float $weight, string $explanation): array
    {
        $now = now()->toDateTimeString();

        if (! isset($ids[$methodologySlug])) {
            throw new \RuntimeException("Methodology slug no encontrada: $methodologySlug");
        }

        return [
            'methodology_id' => $ids[$methodologySlug],
            'rule_type' => $ruleType,
            'applies_when_json' => json_encode($appliesWhen),
            'weight' => $weight,
            'explanation' => $explanation,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }
}
