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

        DB::connection('kb')
            ->table('decision_rules')
            ->insert($rows);

        $this->command?->info('Seeded ' . count($rows) . ' decision rules.');
    }
}
