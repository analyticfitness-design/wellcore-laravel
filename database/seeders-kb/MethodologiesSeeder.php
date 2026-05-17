<?php

declare(strict_types=1);

namespace Database\SeedersKb;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seed inicial de metodologías para el motor v2.
 *
 * 8 metodologías mínimas MVP (decisión D3 = SÍ ciclo incluido):
 *   - 3 de entrenamiento (Body Part Split 5d, Upper/Lower 4d, PPL 6d)
 *   - 2 de nutrición (IIFYM déficit, Mediterránea recomposición)
 *   - 1 de suplementación (Stack básico)
 *   - 1 de hábitos (Sueño + Hidratación básico)
 *   - 1 de ciclo hormonal (Ciclo básico Elite)
 *
 * Idempotente: usa upsert por slug.
 * Ver docs/wellcore-engine-v2/03-knowledge-base-schema.md §3.1
 * Ver docs/wellcore-engine-v2/09-open-questions-and-risks.md §1 (decisiones aplicadas)
 */
final class MethodologiesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now()->toDateTimeString();

        $periodization4w = json_encode([
            ['weeks' => 1, 'fase' => 'Adaptación', 'rir_objetivo' => 3, 'volumen_pct' => 70],
            ['weeks' => 1, 'fase' => 'Hipertrofia', 'rir_objetivo' => 2, 'volumen_pct' => 100],
            ['weeks' => 1, 'fase' => 'Fuerza', 'rir_objetivo' => 1, 'volumen_pct' => 90],
            ['weeks' => 1, 'fase' => 'Peak', 'rir_objetivo' => 0, 'volumen_pct' => 75],
        ]);

        $periodization6w = json_encode([
            ['weeks' => 2, 'fase' => 'Adaptación', 'rir_objetivo' => 3, 'volumen_pct' => 70],
            ['weeks' => 2, 'fase' => 'Hipertrofia', 'rir_objetivo' => 2, 'volumen_pct' => 100],
            ['weeks' => 1, 'fase' => 'Fuerza', 'rir_objetivo' => 1, 'volumen_pct' => 90],
            ['weeks' => 1, 'fase' => 'Peak', 'rir_objetivo' => 0, 'volumen_pct' => 75],
        ]);

        $rows = [
            // ─── ENTRENAMIENTO (3) ─────────────────────────────────────────────
            [
                'slug' => 'body_part_split_5d',
                'name' => 'Body Part Split 5 días',
                'vertical' => 'entrenamiento',
                'description' => "Split clásico por grupo muscular grande, 5 días por semana. Permite frecuencia 1× por grupo con volumen alto. Ideal para intermedio-avanzado buscando hipertrofia.\n\nDistribución típica: Lunes Pecho+Tríceps, Martes Espalda+Bíceps, Miércoles Piernas Cuádriceps, Jueves Hombros+Brazos, Viernes Piernas Posterior+Glúteo.\n\nCardio post pesas opcional.",
                'target_days_min' => 5,
                'target_days_max' => 5,
                'target_level' => 'intermedio',
                'target_goal' => 'hipertrofia',
                'periodization_pattern' => $periodization4w,
                'status' => 'active',
                'created_by' => 'seed-mvp-sprint-0',
                'version' => '1.0',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'upper_lower_4d',
                'name' => 'Upper / Lower 4 días',
                'vertical' => 'entrenamiento',
                'description' => "Alternancia tren superior / tren inferior, 4 días por semana. Frecuencia 2× por grupo. Balance entre recuperación y volumen — ideal para principiantes que están subiendo de nivel y para intermedios que tienen 4 días disponibles.\n\nDistribución típica: L Upper A · Ma Lower A · J Upper B · V Lower B.\n\nProgresión ondulante (DUP) entre sesiones A y B.",
                'target_days_min' => 4,
                'target_days_max' => 4,
                'target_level' => 'any',
                'target_goal' => 'hipertrofia',
                'periodization_pattern' => $periodization4w,
                'status' => 'active',
                'created_by' => 'seed-mvp-sprint-0',
                'version' => '1.0',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'ppl_6d',
                'name' => 'PPL (Push / Pull / Legs) 6 días',
                'vertical' => 'entrenamiento',
                'description' => "División en empuje, jalón y piernas, repetido 2 veces por semana (6 días). Frecuencia 2× por patrón de movimiento. Volumen alto por grupo muscular — requiere buena recuperación.\n\nDistribución típica: L Push A · Ma Pull A · Mi Legs A · J Push B · V Pull B · S Legs B.\n\nReservado a avanzados o intermedios con tiempo + buena recuperación.",
                'target_days_min' => 6,
                'target_days_max' => 6,
                'target_level' => 'avanzado',
                'target_goal' => 'hipertrofia',
                'periodization_pattern' => $periodization6w,
                'status' => 'active',
                'created_by' => 'seed-mvp-sprint-0',
                'version' => '1.0',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ─── NUTRICIÓN (2) ─────────────────────────────────────────────────
            [
                'slug' => 'iifym_deficit',
                'name' => 'IIFYM con déficit calórico moderado',
                'vertical' => 'nutricion',
                'description' => "Flexible Dieting con déficit de 300-500 kcal sobre el GET. Proteína 1.8-2.4 g/kg (alto para preservar masa). Carbos y grasas distribuidos según preferencias y tolerancia.\n\nMacros se calculan con Mifflin-St Jeor → GET → ajuste por objetivo. Comidas estructuradas en 5 (Desayuno, Snack AM, Almuerzo, Pre-entreno, Cena). Cada comida con 3 opciones intercambiables (A/B/C).",
                'target_days_min' => null,
                'target_days_max' => null,
                'target_level' => 'any',
                'target_goal' => 'perdida_grasa',
                'periodization_pattern' => null,
                'status' => 'active',
                'created_by' => 'seed-mvp-sprint-0',
                'version' => '1.0',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'mediterranea_recomp',
                'name' => 'Mediterránea para recomposición',
                'vertical' => 'nutricion',
                'description' => "Basada en alimentos enteros estilo mediterráneo: aceite de oliva, pescado, vegetales abundantes, granos integrales, lácteos moderados. Calorías ligeramente bajo mantenimiento o iso-calóricas según punto de partida.\n\nProteína 1.6-2.0 g/kg. Énfasis en saciedad y calidad cardiovascular. Buena opción para clientes con historial de dietas restrictivas que necesitan reset metabólico.",
                'target_days_min' => null,
                'target_days_max' => null,
                'target_level' => 'any',
                'target_goal' => 'recomposicion',
                'periodization_pattern' => null,
                'status' => 'active',
                'created_by' => 'seed-mvp-sprint-0',
                'version' => '1.0',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ─── SUPLEMENTACIÓN (1) ────────────────────────────────────────────
            [
                'slug' => 'stack_basico',
                'name' => 'Stack Básico WellCore',
                'vertical' => 'suplementacion',
                'description' => "Stack mínimo basado en evidencia para soporte de cualquier objetivo. Compuesto por: proteína whey (30g post-entreno), creatina monohidrato (5g diario), multivitamínico (1× diario), vitamina D3 (2000-4000 IU según latitud), Omega-3 (1-2g EPA+DHA), magnesio (300-400mg antes de dormir).\n\nNo incluye ergogénicos avanzados ni quemadores. Costo mensual estimado: 80-120 USD según marca.",
                'target_days_min' => null,
                'target_days_max' => null,
                'target_level' => 'any',
                'target_goal' => 'any',
                'periodization_pattern' => null,
                'status' => 'active',
                'created_by' => 'seed-mvp-sprint-0',
                'version' => '1.0',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ─── HÁBITOS (1) ────────────────────────────────────────────────────
            [
                'slug' => 'habitos_sueno_hidratacion_basico',
                'name' => 'Hábitos básicos: sueño + hidratación',
                'vertical' => 'habitos',
                'description' => "Pilares básicos no-negociables de rendimiento y recuperación. Sueño objetivo 7-9 horas consistentes (mismo horario fines de semana ±30 min). Hidratación mínima 35 ml/kg de peso corporal, más 500 ml por hora de entrenamiento.\n\nTracking diario via app WellCore. Apto para todos los niveles y todos los objetivos. Suele combinarse con plan de entrenamiento o nutrición.",
                'target_days_min' => null,
                'target_days_max' => null,
                'target_level' => 'any',
                'target_goal' => 'any',
                'periodization_pattern' => null,
                'status' => 'active',
                'created_by' => 'seed-mvp-sprint-0',
                'version' => '1.0',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ─── CICLO HORMONAL Elite (1 — por decisión D3) ────────────────────
            [
                'slug' => 'ciclo_hormonal_basico',
                'name' => 'Ciclo hormonal básico (Elite)',
                'vertical' => 'ciclo',
                'description' => "Adaptación del entrenamiento y nutrición según fase del ciclo menstrual (folicular / ovulatoria / lútea / menstrual). Pensado para clientes Elite con tracking de ciclo activo.\n\nFolicular: ventana de fuerza máxima, mayor volumen tolerable. Lútea: priorizar resistencia, evitar deficits calóricos severos. Suple recomendaciones específicas por fase (magnesio en lútea, hierro post-menstrual).\n\nRequiere intake adicional: día 1 del último ciclo + duración promedio.",
                'target_days_min' => null,
                'target_days_max' => null,
                'target_level' => 'intermedio',
                'target_goal' => 'any',
                'periodization_pattern' => null,
                'status' => 'experimental',
                'created_by' => 'seed-mvp-sprint-0',
                'version' => '1.0',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::connection('kb')
            ->table('methodologies')
            ->upsert($rows, ['slug'], [
                'name', 'vertical', 'description',
                'target_days_min', 'target_days_max',
                'target_level', 'target_goal',
                'periodization_pattern', 'status',
                'created_by', 'version', 'updated_at',
            ]);

        $this->command?->info('Seeded ' . count($rows) . ' methodologies.');
    }
}
