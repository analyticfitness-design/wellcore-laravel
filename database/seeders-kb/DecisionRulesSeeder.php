<?php

declare(strict_types=1);

namespace Database\SeedersKb;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 10 reglas de decisión iniciales — input pattern → metodología recomendada.
 *
 * SELECT stage las usa para boost de score más allá del filtro hard/soft.
 * Confidence 0.0-1.0. Mayor confidence = boost mayor.
 *
 * Ver docs/wellcore-engine-v2/03-knowledge-base-schema.md §3.6
 *
 * IMPORTANTE: corre DESPUÉS de MethodologiesSeeder.
 */
final class DecisionRulesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now()->toDateTimeString();

        $ids = DB::connection('kb')
            ->table('methodologies')
            ->pluck('id', 'slug')
            ->toArray();

        if (empty($ids)) {
            throw new \RuntimeException('DecisionRulesSeeder requiere MethodologiesSeeder corrida primero.');
        }

        $rules = [
            // ── Entrenamiento — match preciso por días + nivel + goal ──
            [
                'name' => 'Hipertrofia intermedio 5 días → Body Part Split',
                'when' => ['vertical' => 'entrenamiento', 'goal' => 'hipertrofia', 'level' => 'intermedio', 'days' => 5],
                'methodology' => 'body_part_split_5d',
                'confidence' => 0.92,
                'rationale' => 'Combinación canónica: 5 días + intermedio + hipertrofia matchea exacto el target de Body Part Split. Score boost alto.',
            ],
            [
                'name' => 'Hipertrofia intermedio 4 días → Upper/Lower',
                'when' => ['vertical' => 'entrenamiento', 'goal' => 'hipertrofia', 'level' => 'intermedio', 'days' => 4],
                'methodology' => 'upper_lower_4d',
                'confidence' => 0.88,
                'rationale' => 'Upper/Lower 4d es el sweet spot para intermedios con 4 días. Frecuencia 2× por grupo, volumen suficiente.',
            ],
            [
                'name' => 'Hipertrofia avanzado 6 días → PPL',
                'when' => ['vertical' => 'entrenamiento', 'goal' => 'hipertrofia', 'level' => 'avanzado', 'days' => 6],
                'methodology' => 'ppl_6d',
                'confidence' => 0.93,
                'rationale' => 'PPL 6d demanda recuperación de avanzado + 6 días disponibles. Cliente avanzado con 6 días casi siempre busca PPL.',
            ],
            [
                'name' => 'Hipertrofia principiante (cualquier días) → Upper/Lower 4d',
                'when' => ['vertical' => 'entrenamiento', 'goal' => 'hipertrofia', 'level' => 'principiante'],
                'methodology' => 'upper_lower_4d',
                'confidence' => 0.75,
                'rationale' => 'Principiantes progresan mejor con frecuencia 2× por grupo y compuestos. Upper/Lower 4d cumple sin sobrecargar.',
            ],
            [
                'name' => 'Fuerza intermedio 4 días → Upper/Lower',
                'when' => ['vertical' => 'entrenamiento', 'goal' => 'fuerza', 'level' => 'intermedio', 'days' => 4],
                'methodology' => 'upper_lower_4d',
                'confidence' => 0.80,
                'rationale' => 'Upper/Lower con periodización fuerza-orientada (reps bajas, RIR 1-2) sirve a goal fuerza intermedio.',
            ],

            // ── Entrenamiento — perdida_grasa / recomposicion / mantenimiento ──
            [
                'name' => 'Pérdida de grasa intermedio 5 días → Body Part Split',
                'when' => ['vertical' => 'entrenamiento', 'goal' => 'perdida_grasa', 'level' => 'intermedio', 'days' => 5],
                'methodology' => 'body_part_split_5d',
                'confidence' => 0.85,
                'rationale' => 'Body Part Split 5d con SplitBuilder sesga glúteo+pierna para perfil F/perdida_grasa. Volumen preserva masa en déficit.',
            ],
            [
                'name' => 'Pérdida de grasa intermedio 4 días → Upper/Lower',
                'when' => ['vertical' => 'entrenamiento', 'goal' => 'perdida_grasa', 'level' => 'intermedio', 'days' => 4],
                'methodology' => 'upper_lower_4d',
                'confidence' => 0.82,
                'rationale' => 'Upper/Lower mantiene frecuencia 2× por grupo en déficit moderado.',
            ],
            [
                'name' => 'Pérdida de grasa principiante (cualquier días) → Upper/Lower',
                'when' => ['vertical' => 'entrenamiento', 'goal' => 'perdida_grasa', 'level' => 'principiante'],
                'methodology' => 'upper_lower_4d',
                'confidence' => 0.78,
                'rationale' => 'Principiantes en perdida_grasa progresan con compuestos básicos. Upper/Lower 4d es el mínimo viable.',
            ],
            [
                'name' => 'Recomposición intermedio 5 días → Body Part Split',
                'when' => ['vertical' => 'entrenamiento', 'goal' => 'recomposicion', 'level' => 'intermedio', 'days' => 5],
                'methodology' => 'body_part_split_5d',
                'confidence' => 0.85,
                'rationale' => 'Recomposición pide volumen alto + déficit ligero. Body Part Split 5d cubre ambos.',
            ],
            [
                'name' => 'Recomposición avanzado 6 días → PPL',
                'when' => ['vertical' => 'entrenamiento', 'goal' => 'recomposicion', 'level' => 'avanzado', 'days' => 6],
                'methodology' => 'ppl_6d',
                'confidence' => 0.85,
                'rationale' => 'Avanzados toleran PPL 6d en iso/leve déficit. Frecuencia 2× sostiene masa.',
            ],
            [
                'name' => 'Mantenimiento cualquier nivel → Upper/Lower',
                'when' => ['vertical' => 'entrenamiento', 'goal' => 'mantenimiento'],
                'methodology' => 'upper_lower_4d',
                'confidence' => 0.72,
                'rationale' => 'Mantenimiento no requiere volumen alto. Upper/Lower 4d es eficiente.',
            ],

            // ── Nutrición — match por goal ──
            [
                'name' => 'Pérdida de grasa → IIFYM con déficit',
                'when' => ['vertical' => 'nutricion', 'goal' => 'perdida_grasa'],
                'methodology' => 'iifym_deficit',
                'confidence' => 0.88,
                'rationale' => 'IIFYM permite flexibilidad alta en alimentos manteniendo déficit controlado y proteína alta. Ideal para perdida_grasa.',
            ],
            [
                'name' => 'Recomposición → Mediterránea',
                'when' => ['vertical' => 'nutricion', 'goal' => 'recomposicion'],
                'methodology' => 'mediterranea_recomp',
                'confidence' => 0.82,
                'rationale' => 'Recomposición pide calorías iso o levemente bajo mantenimiento con calidad alta. Mediterránea cubre ambos.',
            ],
            [
                'name' => 'Mantenimiento → Mediterránea',
                'when' => ['vertical' => 'nutricion', 'goal' => 'mantenimiento'],
                'methodology' => 'mediterranea_recomp',
                'confidence' => 0.78,
                'rationale' => 'Mantenimiento es el target natural de la dieta Mediterránea — alimentos enteros, sostenible largo plazo.',
            ],
            [
                'name' => 'Hipertrofia → IIFYM con superávit',
                'when' => ['vertical' => 'nutricion', 'goal' => 'hipertrofia'],
                'methodology' => 'iifym_deficit',
                'confidence' => 0.80,
                'rationale' => 'IIFYM (con MacroCalculator en superávit +250 kcal) ofrece la flexibilidad necesaria para sostener volumen calórico alto. Mediterránea es alternativa si el cliente prefiere alimentos enteros.',
            ],
            [
                'name' => 'Fuerza → IIFYM',
                'when' => ['vertical' => 'nutricion', 'goal' => 'fuerza'],
                'methodology' => 'iifym_deficit',
                'confidence' => 0.75,
                'rationale' => 'Entrenamiento de fuerza pide proteína alta (2.0-2.2 g/kg) y calorías iso/leve superávit. IIFYM cumple con flexibilidad alta.',
            ],

            // ── Ciclo — Elite femenino con tracking ──
            [
                'name' => 'Cualquier vertical ciclo → Ciclo hormonal básico',
                'when' => ['vertical' => 'ciclo'],
                'methodology' => 'ciclo_hormonal_basico',
                'confidence' => 0.90,
                'rationale' => 'Único methodology para vertical=ciclo. Aplica solo a clientas femeninas Elite con tracking activo.',
            ],

            // ── Suplementación — default stack ──
            [
                'name' => 'Cualquier vertical suplementación → Stack Básico',
                'when' => ['vertical' => 'suplementacion'],
                'methodology' => 'stack_basico',
                'confidence' => 0.95,
                'rationale' => 'Default WellCore para todo cliente. Cubre lo basal (whey, creatina, multi, D3, Omega-3, magnesio).',
            ],

            // ── Hábitos — default ──
            [
                'name' => 'Cualquier vertical hábitos → Hábitos Sueño + Hidratación',
                'when' => ['vertical' => 'habitos'],
                'methodology' => 'habitos_sueno_hidratacion_basico',
                'confidence' => 0.95,
                'rationale' => 'Pilares básicos no negociables — todos los clientes deberían empezar acá independiente de objetivo.',
            ],
        ];

        $rows = array_map(function (array $r) use ($ids, $now): array {
            if (! isset($ids[$r['methodology']])) {
                throw new \RuntimeException("Methodology slug no encontrada en DecisionRule: {$r['methodology']}");
            }

            return [
                'name' => $r['name'],
                'when_json' => json_encode($r['when']),
                'then_methodology_id' => $ids[$r['methodology']],
                'confidence' => $r['confidence'],
                'rationale' => $r['rationale'],
                'author' => 'seed-mvp-sprint-0',
                'status' => 'active',
                'times_fired' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $rules);

        // upsert por `name` (UNIQUE constraint en la tabla) → idempotente.
        // En cada kb:seed actualiza si existe, inserta si no. Evita duplicados.
        DB::connection('kb')
            ->table('decision_rules')
            ->upsert(
                $rows,
                ['name'],
                ['when_json', 'then_methodology_id', 'confidence', 'rationale', 'status', 'updated_at']
            );

        $this->command?->info('Upserted ' . count($rows) . ' decision rules.');
    }
}
