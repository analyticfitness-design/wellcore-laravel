<?php

declare(strict_types=1);

namespace Database\SeedersKb;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 20 lint rules iniciales del motor v2.
 *
 * Distribución (Sprint 0-1):
 *   - 10 schema (5 entrenamiento + 3 nutrición + 2 suplementación)
 *   - 7 heuristic (anti-monotonía, voz, marketing, mención de IA)
 *   - 2 external_head (verificación GIFs vivos)
 *   - 1 sql (plan_type ∉ enum oficial)
 *
 * Sprint 2 agrega: 1 prompt_injection + 2 sql extras = 23 totales
 * Sprint 6+ agrega: 2 llm_review opt-in = 25 totales
 *
 * Cubre los 6 errores del caso Cristian (cada uno tiene su rule explícita).
 * Idempotente por code. Ver docs/wellcore-engine-v2/06-lint-rules.md
 */
final class LintRulesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now()->toDateTimeString();
        $rows = $this->definitions($now);

        DB::connection('kb')
            ->table('lint_rules')
            ->upsert($rows, ['code'], [
                'vertical', 'severity', 'description', 'check_type',
                'check_definition_json', 'fix_hint_template',
                'enabled', 'auto_fix_available', 'created_by', 'updated_at',
            ]);

        $this->command?->info('Seeded ' . count($rows) . ' lint rules.');
    }

    private function definitions(string $now): array
    {
        $r = [];

        // ─── SCHEMA: entrenamiento (5) ───────────────────────────────────────
        $r[] = $this->rule('schema_train_missing_objetivo', 'entrenamiento', 'error', 'schema',
            'El JSON debe tener `objetivo` (string) en root',
            ['json_path' => '$.objetivo', 'validator' => 'exists_and_non_empty'],
            'Agrega `objetivo` en el root del JSON con una frase clara (ej. "Recomposición: bajar grasa preservando músculo").',
            false, $now);

        $r[] = $this->rule('schema_train_missing_split', 'entrenamiento', 'error', 'schema',
            'El JSON debe tener `split{}` top-level con keys Lunes/Martes/... para renderizar HORARIO SEMANAL (caso Cristian error #1)',
            ['json_path' => '$.split', 'validator' => 'object_with_keys', 'required_keys_any_of' => ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes']],
            'Agrega `split{}` en el root con las keys Lunes a Viernes/Sábado, valor string del grupo muscular (ej. "Pecho + Tríceps + Abs").',
            false, $now);

        $r[] = $this->rule('schema_train_missing_phase_field', 'entrenamiento', 'error', 'schema',
            'Cada `semanas[i].fase` es REQUIRED — alimenta topbar y subtítulo dinámico del header',
            ['json_path' => '$.semanas[*].fase', 'validator' => 'exists_in_each'],
            'Agrega `fase` en cada semana con uno de los 9 nombres oficiales: Adaptación, Hipertrofia, Fuerza, Fuerza Máxima, Peak, Deload, Recuperación, Preparación, Mantenimiento.',
            false, $now);

        $r[] = $this->rule('schema_train_invalid_phase_name', 'entrenamiento', 'error', 'schema',
            '`semanas[i].fase` debe empezar con uno de los 9 nombres oficiales con tildes correctas',
            [
                'json_path' => '$.semanas[*].fase',
                'validator' => 'startsWith',
                'allowed_values' => ['Adaptación', 'Hipertrofia', 'Fuerza', 'Fuerza Máxima', 'Peak', 'Deload', 'Recuperación', 'Preparación', 'Mantenimiento'],
                'case_sensitive' => true,
                'auto_fix' => ['type' => 'fuzzy_replace', 'max_distance' => 2, 'min_confidence' => 0.85],
            ],
            'Reemplaza el valor de fase por uno oficial con tildes (ej. "adaptacion" → "Adaptación").',
            true, $now);

        $r[] = $this->rule('schema_train_missing_dias_meta', 'entrenamiento', 'error', 'schema',
            'Cada `dias[i]` debe tener `dia_semana` (string) y `grupo_muscular` (string) — alimenta el grid HORARIO SEMANAL',
            ['json_path' => '$.semanas[*].dias[*]', 'validator' => 'has_required_keys', 'required_keys' => ['dia_semana', 'grupo_muscular']],
            'Agrega `dia_semana` (ej. "Lunes") y `grupo_muscular` (ej. "Pecho, Tríceps, Core") en cada día.',
            false, $now);

        // ─── SCHEMA: nutrición (3) ───────────────────────────────────────────
        $r[] = $this->rule('schema_nutr_missing_objetivo_cal', 'nutricion', 'error', 'schema',
            'El JSON debe tener `objetivo_cal` (int) — alimenta hero "Calorías Diarias"',
            ['json_path' => '$.objetivo_cal', 'validator' => 'exists_and_int_positive'],
            'Agrega `objetivo_cal` en el root con el target calórico calculado (ej. 2400).',
            false, $now);

        $r[] = $this->rule('schema_nutr_invalid_macros_keys_with_g', 'nutricion', 'error', 'schema',
            'En `comidas[i].macros` los keys NO usan `_g` (la UI no los lee). Caso Cristian error #6',
            [
                'json_path' => '$.comidas[*].macros',
                'validator' => 'object_keys_not_in',
                'forbidden_keys' => ['proteina_g', 'carbohidratos_g', 'grasas_g'],
                'auto_fix' => ['type' => 'rename_keys', 'mapping' => ['proteina_g' => 'proteina', 'carbohidratos_g' => 'carbohidratos', 'grasas_g' => 'grasas']],
            ],
            'Usa keys sin sufijo `_g` en `comidas[].macros` (proteina, carbohidratos, grasas). En root `macros{}` sí van con `_g`.',
            true, $now);

        $r[] = $this->rule('schema_nutr_invalid_opciones_shape', 'nutricion', 'error', 'schema',
            '`comidas[i].opcion_a/b/c` deben ser arrays de strings simples, NO objetos {item, cantidad}. Caso Cristian error #4',
            ['json_path' => '$.comidas[*].opcion_a', 'validator' => 'array_of_strings'],
            'Reescribe `opcion_a/b/c` como array de strings con cantidad embebida: ["Huevos enteros (3 unidades)", "Tostadas integrales (2 rebanadas, 60g)"].',
            false, $now);

        // ─── SCHEMA: suplementación (2) ──────────────────────────────────────
        $r[] = $this->rule('schema_supl_missing_array', 'suplementacion', 'error', 'schema',
            'El JSON debe tener `suplementos[]` con al menos 1 item',
            ['json_path' => '$.suplementos', 'validator' => 'array_non_empty'],
            'Agrega `suplementos[]` con al menos: Whey, Creatina, Multivitamínico.',
            false, $now);

        $r[] = $this->rule('schema_supl_uses_timing_instead_momento', 'suplementacion', 'warning', 'schema',
            '`suplementos[i].timing` (inglés) debe ser `momento` (canónico español)',
            [
                'json_path' => '$.suplementos[*]',
                'validator' => 'object_keys_not_in',
                'forbidden_keys' => ['timing'],
                'auto_fix' => ['type' => 'rename_keys', 'mapping' => ['timing' => 'momento']],
            ],
            'Renombra `timing` → `momento` en cada suplemento.',
            true, $now);

        // ─── HEURISTIC (7) ───────────────────────────────────────────────────
        $r[] = $this->rule('heur_monotonia_3x12', 'entrenamiento', 'warning', 'heuristic',
            'Más del 60% de ejercicios usan misma combinación series=3 reps="12" (o 4×10) — señal de plan genérico',
            ['rule' => 'percentage_same_sets_reps', 'threshold_pct' => 60, 'patterns' => [['series' => 3, 'reps' => '12'], ['series' => 4, 'reps' => '10']]],
            'Variar entre series/reps a lo largo de la semana. Considerar pirámides en compuestos principales y progresión semana a semana.',
            false, $now);

        $r[] = $this->rule('heur_missing_progression', 'entrenamiento', 'warning', 'heuristic',
            'El plan tiene duracion_semanas >= 4 pero todas las semanas son idénticas — sin periodización efectiva',
            ['rule' => 'weeks_are_identical', 'min_weeks' => 4],
            'Cambiar series/reps/RIR semana a semana siguiendo periodización (ej. Sem1 Adaptación 3×12 RIR 3 → Sem4 Peak 5×6 RIR 0).',
            false, $now);

        $r[] = $this->rule('heur_cardio_excessive', 'entrenamiento', 'warning', 'heuristic',
            'Sesión Hipertrofia con >40 min de cardio puede interferir con recuperación',
            ['rule' => 'cardio_min_per_session', 'max_minutes_in_phase' => ['Hipertrofia' => 40, 'Fuerza' => 30, 'Peak' => 20]],
            'Reducir cardio a 25-30 min en sesiones de hipertrofia o moverlo a día separado.',
            false, $now);

        $r[] = $this->rule('heur_volume_imbalance', 'entrenamiento', 'warning', 'heuristic',
            'Desbalance de volumen >2× entre grupos antagonistas (pecho/espalda, cuad/isquio, bíceps/tríceps) genera adaptaciones asimétricas y riesgo postural',
            ['rule' => 'volume_balance_per_muscle', 'max_ratio' => 2.0, 'min_series_per_group' => 4],
            'Aumentar volumen del grupo subordinado o reducir del dominante para mantener ratio ≤2:1 entre antagonistas. Estándar: 0.8-1.2 es ideal, máx 1.5 antes de revisar.',
            false, $now);

        $r[] = $this->rule('heur_progression_inverted', 'entrenamiento', 'warning', 'heuristic',
            'RIR aumenta semana a semana en una fase no-deload — rompe sobrecarga progresiva (debería bajar o mantenerse)',
            ['rule' => 'progression_adequate', 'tolerance' => 0.5],
            'En periodización lineal: RIR semana 1 > RIR semana 2 > ... > RIR semana 4 (peak). Solo Deload/Recuperación rompen esta regla. Revisar y bajar RIR de semanas posteriores.',
            false, $now);

        $r[] = $this->rule('heur_frequency_methodology_mismatch', 'entrenamiento', 'error', 'heuristic',
            'frecuencia_dias del plan no coincide con target_days_min/max de la methodology declarada',
            ['rule' => 'frequency_matches_methodology'],
            'Revisar split del plan o cambiar la methodology declarada. Ej: PPL 6d requiere 6 días disponibles; con 4 días usar Upper/Lower o Full Body.',
            false, $now);

        $r[] = $this->rule('heur_min_volume_per_muscle', 'entrenamiento', 'warning', 'heuristic',
            'Grupo muscular mayor con menos de 10 series/semana — volumen insuficiente para hipertrofia (Schoenfeld 2017)',
            ['rule' => 'min_volume_per_muscle', 'min_series_per_week' => 10],
            'Agregar 1-2 ejercicios adicionales (o aumentar series) para el grupo subordinado. Estándar mínimo viable: 10 series/sem para hipertrofia.',
            false, $now);

        $r[] = $this->rule('heur_push_pull_imbalance', 'entrenamiento', 'warning', 'heuristic',
            'Desbalance >1.5× entre series push (empuje) vs pull (jalón) — riesgo de síndrome cruzado superior y mal postura',
            ['rule' => 'push_pull_balance', 'max_ratio' => 1.5, 'min_series' => 8],
            'Equilibrar series de empuje y jalón. Cada vez que empujás (press, fondos) debe haber un jalón equivalente (remo, dominadas, jalón polea).',
            false, $now);

        $r[] = $this->rule('heur_warmup_missing', 'entrenamiento', 'warning', 'heuristic',
            'Plan sin mención de calentamiento — riesgo de lesión aguda elevado',
            ['rule' => 'warmup_missing'],
            'Agregar al menos un tip o nota_coach recomendando warmup específico de 5-10 min antes de cada sesión.',
            false, $now);

        // LEY DURA del motor v2 (autoritativa Daniel · 2026-05-18):
        // Todos los ejercicios deben tener gif del repo oficial wellcore-exercise-gifs-v2.
        // Cualquier ejercicio fuera del catálogo → severity=ERROR, bloquea PERSIST.
        $r[] = $this->rule('hard_exercise_gif_from_v2_repo', 'entrenamiento', 'error', 'heuristic',
            'Ejercicio con gif_url fuera del repo wellcore-exercise-gifs-v2 o sin entry en exercise_metadata (LEY DURA)',
            ['rule' => 'exercise_gif_from_v2_repo'],
            'Reemplazar el ejercicio por un alias que SÍ exista en exercise_metadata, o agregar el GIF al repo + correr `php artisan kb:import-exercise-catalog`. NO inventar gif_url.',
            false, $now);

        // LEY DURA voz (autoritativa Daniel · 2026-05-18):
        // Ningún string visible al cliente puede delatar IA/sistema/automatización.
        // Validator escanea todos los strings del plan recursivamente.
        $r[] = $this->rule('hard_no_ai_leak_in_strings', null, 'error', 'heuristic',
            'Texto del plan delata IA / sistema / automatización al cliente (LEY DURA voz)',
            ['rule' => 'anti_ai_leak'],
            'Reformular el texto en 2da persona voseo colombiano amable, como si lo escribiera el coach humano. Eliminar metarreferencias al plan/sistema/motor/IA. Eliminar jerga técnica en inglés.',
            false, $now);

        $r[] = $this->rule('heur_supl_creatina_missing', 'suplementacion', 'warning', 'heuristic',
            'Plan de suplementación SIN creatina monohidrato — el suplemento con mejor evidencia costo/beneficio',
            ['rule' => 'creatina_missing'],
            'Agregar creatina monohidrato 5g/día al stack. Excepciones: contraindicación renal documentada.',
            false, $now);

        $r[] = $this->rule('heur_supl_omega3_missing', 'suplementacion', 'warning', 'heuristic',
            'Plan de suplementación SIN omega-3 (EPA+DHA) — evidencia consistente para anti-inflamación y recuperación',
            ['rule' => 'omega3_missing'],
            'Agregar omega-3 con 1-2g EPA+DHA combinados/día. Origen: pescado azul o suplemento purificado.',
            false, $now);

        $r[] = $this->rule('heur_proteina_daily_mismatch', 'nutricion', 'warning', 'heuristic',
            'Mismatch entre macros.proteina_g target y suma de proteína de las comidas (>10% diff)',
            ['rule' => 'proteina_daily_target', 'tolerance_pct' => 10.0],
            'Revisar MealsBuilder o ajustar manualmente los macros por comida para que sumen al target.',
            false, $now);

        $r[] = $this->rule('heur_warmup_lesion_specific', 'entrenamiento', 'warning', 'heuristic',
            'Plan menciona lesión/dolor en zona específica pero NO incluye warmup dirigido a esa zona',
            ['rule' => 'warmup_lesion_specific'],
            'Para cada zona afectada agregar movilidad/activación específica (ej. hombro: rotación externa banda + pull-aparts antes de presses).',
            false, $now);

        $r[] = $this->rule('heur_hydration_target', 'nutricion', 'warning', 'heuristic',
            'Plan de nutrición SIN target de hidratación o con target <25 ml/kg/día',
            ['rule' => 'hydration_target'],
            'Agregar `macros.hidratacion_ml_dia` (recomendado 30-35 ml/kg/día). Para un adulto de 70 kg, ~2100-2450 ml/día.',
            false, $now);

        $r[] = $this->rule('heur_cooldown_missing', 'entrenamiento', 'warning', 'heuristic',
            'Plan de entrenamiento sin mención de vuelta a la calma / cooldown / enfriamiento',
            ['rule' => 'cooldown_missing'],
            'Agregar al menos un tip o nota recomendando 5 min de estiramiento final, caminata suave o respiración para bajar pulsaciones.',
            false, $now);

        $r[] = $this->rule('heur_macros_coherencia', 'nutricion', 'warning', 'heuristic',
            'Macros incoherentes: kcal declarada NO coincide con (proteina*4 + carbs*4 + grasa*9) ±5%',
            ['rule' => 'macros_coherencia', 'tolerance_pct' => 5.0],
            'Revisar MacroCalculator o ajustar manualmente macros.proteina_g/carbohidratos_g/grasa_g para que la suma (4P + 4C + 9G) coincida con macros.kcal.',
            false, $now);

        $r[] = $this->rule('heur_sleep_tracking', 'habitos', 'warning', 'heuristic',
            'Plan de hábitos SIN tracking explícito de sueño (categoria=sueno o keywords)',
            ['rule' => 'sleep_tracking'],
            'Agregar al menos 1 habit con categoria="sueno" y tracking_method="diario". Ej: nombre="Sueño 7-9h", objetivo="dormir 7-9h y registrar calidad 1-5".',
            false, $now);

        $r[] = $this->rule('heur_rest_day_missing', 'entrenamiento', 'warning', 'heuristic',
            'Plan de entrenamiento con 7 días seguidos sin descanso explícito',
            ['rule' => 'rest_day_missing'],
            'Marcar al menos 1 día (típicamente Domingo) como "Descanso", "Off", o "Movilidad + caminata suave" en split{}.',
            false, $now);

        $r[] = $this->rule('heur_supl_vitamina_d3_missing', 'suplementacion', 'warning', 'heuristic',
            'Plan de suplementación SIN vitamina D3',
            ['rule' => 'vitamina_d3_missing'],
            'Agregar vitamina D3 con 2000-5000 IU/día con grasa. Considerar combinación con K2 (MK-7).',
            false, $now);

        $r[] = $this->rule('heur_warmup_min_duration', 'entrenamiento', 'warning', 'heuristic',
            'Calentamiento con duración <5 min (insuficiente fisiológicamente)',
            ['rule' => 'warmup_min_duration', 'min_min' => 5],
            'Aumentar a 5-10 min: 2-3 min cardio liviano + 2-3 min movilidad + 2-3 min activación específica del primer ejercicio.',
            false, $now);

        $r[] = $this->rule('heur_unilateral_balance', 'entrenamiento', 'warning', 'heuristic',
            'Plan de entrenamiento sin ejercicios unilaterales en la semana',
            ['rule' => 'unilateral_balance'],
            'Agregar al menos 1 unilateral por semana (zancada, búlgara, press alterno, remo unilateral, paso adelante).',
            false, $now);

        // ─── SCHEMA: habitos (2) ─────────────────────────────────────────────
        $r[] = $this->rule('schema_habitos_missing_array', 'habitos', 'error', 'schema',
            'Plan de hábitos debe tener `habitos[]` con al menos 1 item',
            ['json_path' => '$.habitos', 'validator' => 'array_non_empty'],
            'Agregá `habitos[]` con al menos 3 hábitos básicos (sueño, hidratación, registro).',
            false, $now);

        $r[] = $this->rule('schema_habitos_missing_keys', 'habitos', 'error', 'schema',
            'Cada item de `habitos[i]` debe tener `nombre`, `categoria`, `objetivo` y `tracking_method`',
            ['json_path' => '$.habitos[*]', 'validator' => 'has_required_keys', 'required_keys' => ['nombre', 'categoria', 'objetivo', 'tracking_method']],
            'Agregá los 4 campos canónicos: nombre (string), categoria (sueño|hidratacion|registro|tracking|ciclo), objetivo (string), tracking_method (string).',
            false, $now);

        $r[] = $this->rule('heur_voz_castellano_peninsular', null, 'error', 'heuristic',
            'Detecta vocabulario peninsular (vosotros, habéis, vuestro) — viola voz LATAM',
            [
                'json_paths' => ['$.notas_coach', '$.tips[*]', '$..ejercicios[*].notas', '$..comidas[*].notas_comida'],
                'patterns' => [
                    ['regex' => '\\bvosotros\\b', 'case_insensitive' => true],
                    ['regex' => '\\bhabéis\\b', 'case_insensitive' => true],
                    ['regex' => '\\bvuestro[as]?\\b', 'case_insensitive' => true],
                    ['regex' => '\\bos\\s+(recomiendo|sugiero|aconsejo)\\b', 'case_insensitive' => true],
                ],
                'auto_fix' => [
                    'type' => 'regex_replace_table',
                    'replacements' => [
                        'vosotros' => 'ustedes', 'habéis' => 'han',
                        'vuestro' => 'su', 'vuestra' => 'su',
                        'os recomiendo' => 'te recomiendo', 'os sugiero' => 'te sugiero',
                    ],
                ],
            ],
            'Cambiar vocabulario peninsular por neutro latino. Auto-fix disponible para los patterns comunes.',
            true, $now);

        $r[] = $this->rule('heur_voz_usted', null, 'error', 'heuristic',
            'Detecta tratamiento de usted (usted, su plan, le recomiendo) — viola tuteo obligatorio',
            [
                'json_paths' => ['$.notas_coach', '$.tips[*]', '$..ejercicios[*].notas', '$..comidas[*].notas_comida'],
                'patterns' => [
                    ['regex' => '\\busted\\b', 'case_insensitive' => true],
                    ['regex' => '\\bsu\\s+plan\\b', 'case_insensitive' => true],
                    ['regex' => '\\ble\\s+(recomiendo|sugiero)\\b', 'case_insensitive' => true],
                ],
                'auto_fix' => [
                    'type' => 'regex_replace_table',
                    'replacements' => [
                        'usted' => 'tú', 'Usted' => 'Tú',
                        'su plan' => 'tu plan',
                        'le recomiendo' => 'te recomiendo', 'le sugiero' => 'te sugiero',
                    ],
                ],
            ],
            'Cambiar tratamiento de usted por tuteo. Auto-fix disponible.',
            true, $now);

        $r[] = $this->rule('heur_voz_marketing', null, 'warning', 'heuristic',
            'Detecta tono marketing prohibido (potenciar, innovador, revolucionario, experimentar nuevas sensaciones)',
            [
                'json_paths' => ['$.notas_coach', '$.tips[*]', '$..ejercicios[*].notas'],
                'patterns' => [
                    ['regex' => '\\bpotenciar\\b', 'case_insensitive' => true],
                    ['regex' => '\\binnovador[a]?\\b', 'case_insensitive' => true],
                    ['regex' => '\\brevolucionario[a]?\\b', 'case_insensitive' => true],
                    ['regex' => 'experimentar\\s+nuevas\\s+sensaciones', 'case_insensitive' => true],
                ],
            ],
            'Reescribir con verbos directos: "potenciar" → "subir/mejorar", "innovador" → quitar, "revolucionario" → quitar.',
            false, $now);

        $r[] = $this->rule('heur_mention_of_ia', null, 'error', 'heuristic',
            'Detecta mención de IA / Claude / Anthropic / "generado por" — coaches y clientes NUNCA deben saber que es IA',
            [
                'json_paths' => ['$.notas_coach', '$.tips[*]', '$..ejercicios[*].notas', '$..comidas[*].notas_comida'],
                'patterns' => [
                    ['regex' => '\\bIA\\b'],
                    ['regex' => '\\bClaude\\b'],
                    ['regex' => '\\bAnthropic\\b'],
                    ['regex' => 'generad[oa]\\s+por\\s+', 'case_insensitive' => true],
                    ['regex' => 'inteligencia\\s+artificial', 'case_insensitive' => true],
                    ['regex' => 'generated\\s+by\\s+AI', 'case_insensitive' => true],
                ],
                'auto_fix' => ['type' => 'remove_sentence_containing_trigger'],
            ],
            'Eliminar cualquier mención al sistema. Auto-fix remueve la oración completa que contiene el trigger. Memoria autoritativa: feedback_ia_confidencial.',
            true, $now);

        // ─── EXTERNAL_HEAD (2) ───────────────────────────────────────────────
        $r[] = $this->rule('external_gif_url_pattern_wrong', 'entrenamiento', 'error', 'external_head',
            '`gif_url` no matchea el repo oficial GitHub raw — caso Cristian error #2',
            [
                'json_path' => '$..ejercicios[*].gif_url',
                'expected_pattern' => '^https://raw\\.githubusercontent\\.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/',
                'auto_fix' => [
                    'type' => 'rewrite_domain',
                    'from_pattern' => '^https?://(?:www\\.)?wellcorefitness\\.com/storage/exercises/',
                    'to_prefix' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/',
                ],
            ],
            'La URL canónica es: https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/{alias}.gif. NO usar wellcorefitness.com/storage/exercises/ (no existe en producción).',
            true, $now);

        $r[] = $this->rule('external_gif_url_inaccessible', 'entrenamiento', 'error', 'external_head',
            'HEAD check a `gif_url` retorna != 2xx en 8s — el GIF está roto',
            [
                'json_path' => '$..ejercicios[*].gif_url',
                'method' => 'HEAD',
                'timeout_ms' => 8000,
                'follow_redirects' => true,
                'cache_ttl_hours' => 24,
            ],
            'Verificar que el alias existe en el repo: https://github.com/analyticfitness-design/wellcore-exercise-gifs. Si no, sustituir por uno cercano del catálogo.',
            false, $now);

        // ─── SQL (1) ─────────────────────────────────────────────────────────
        $r[] = $this->rule('sql_plan_type_not_in_enum', null, 'error', 'sql',
            'plan_type ∉ {entrenamiento, nutricion, habitos, suplementacion, ciclo} — defense in depth contra el ENUM permisivo de prod',
            [
                'json_path' => '$.plan_type',
                'allowed_values' => ['entrenamiento', 'nutricion', 'habitos', 'suplementacion', 'ciclo'],
            ],
            'plan_type debe ser uno de: entrenamiento, nutricion, habitos, suplementacion, ciclo. Cualquier otro valor (incluido string vacío) es rechazado.',
            false, $now);

        // ─── BLOQUE D Sprints 91-95: lint rules sin validator nuevo ────────────
        $r[] = $this->rule('heur_voz_anglicismos_innecesarios', null, 'warning', 'heuristic',
            'Anglicismos innecesarios en voz del coach (workout, set, rep, bulk, cut)',
            [
                'json_paths' => ['$.notas_coach', '$.tips[*]'],
                'patterns' => [
                    ['regex' => '\\bworkout(s)?\\b', 'case_insensitive' => true],
                    ['regex' => '\\bbulk(ing)?\\b', 'case_insensitive' => true],
                    ['regex' => '\\bcut(ting)?\\b', 'case_insensitive' => true],
                    ['regex' => '\\bset(s)?\\b(?!\\s+de)', 'case_insensitive' => true],
                ],
                'auto_fix' => [
                    'type' => 'regex_replace_table',
                    'replacements' => [
                        'workout' => 'entreno', 'workouts' => 'entrenos',
                        'bulk' => 'volumen', 'bulking' => 'fase de volumen',
                        'cut' => 'definición', 'cutting' => 'fase de definición',
                    ],
                ],
            ],
            'Sustituir anglicismos por equivalentes en castellano latino: workout→entreno, bulk→volumen, cut→definición.',
            true, $now);

        $r[] = $this->rule('heur_marketing_garantizado', null, 'error', 'heuristic',
            'Palabras prohibidas de marketing engañoso (garantizado, milagroso, secreto, transformación 100%)',
            [
                'json_paths' => ['$.notas_coach', '$.tips[*]', '$.objetivo'],
                'patterns' => [
                    ['regex' => '\\bgarantizad[oa]s?\\b', 'case_insensitive' => true],
                    ['regex' => '\\bmilagros[oa]s?\\b', 'case_insensitive' => true],
                    ['regex' => '\\bsecret[oa]s?\\b\\s+(formula|metodo)', 'case_insensitive' => true],
                    ['regex' => '\\btransformación\\s+(100|garantizada|asegurada)', 'case_insensitive' => true],
                    ['regex' => '\\bresultados?\\s+(garantizados?|asegurados?)', 'case_insensitive' => true],
                ],
            ],
            'Eliminar palabras de marketing engañoso. Reemplazar por lenguaje preciso: "garantizado"→"esperable si sigues el plan", "milagroso"→eliminar.',
            false, $now);

        $r[] = $this->rule('heur_objetivo_demasiado_corto', null, 'warning', 'heuristic',
            'Campo objetivo con menos de 30 caracteres — probablemente vago o incompleto',
            [
                'json_paths' => ['$.objetivo'],
                'patterns' => [
                    ['regex' => '^.{0,29}$', 'case_insensitive' => false],
                ],
            ],
            'Expandir objetivo con métrica + plazo + contexto. Ej: "Hipertrofia tren superior — ganar 2-3 kg masa magra en 12 semanas, foco pecho y espalda".',
            false, $now);

        $r[] = $this->rule('heur_macros_proteina_minima', 'nutricion', 'warning', 'heuristic',
            'macros.proteina_g por debajo de 1.2 g/kg estimado — riesgo de catabolismo en déficit/hipertrofia',
            [
                'json_paths' => ['$.macros'],
                'patterns' => [
                    ['regex' => '"proteina_g"\\s*:\\s*[0-9]{1,2}([^0-9]|$)', 'case_insensitive' => false],
                ],
            ],
            'Subir macros.proteina_g a mínimo 1.6 g/kg (recomp: 2.0-2.4 g/kg, hipertrofia: 1.6-2.2 g/kg, mantenimiento: 1.2 g/kg).',
            false, $now);

        $r[] = $this->rule('heur_supl_creatina_overlap', 'suplementacion', 'warning', 'heuristic',
            'Detectados múltiples productos de creatina en el mismo stack (duplicación)',
            [
                'json_paths' => ['$.notas_coach', '$.tips[*]'],
                'patterns' => [
                    ['regex' => '(creatina[^.]{1,80}){2,}', 'case_insensitive' => true],
                ],
            ],
            'Verificar que solo hay UN producto de creatina (monohidrato 5g/día). Eliminar suplementos redundantes (creatina HCL + monohidrato simultáneo).',
            false, $now);

        return $r;
    }

    private function rule(string $code, ?string $vertical, string $severity, string $checkType, string $description, array $checkDefinition, string $fixHint, bool $autoFix, string $now): array
    {
        return [
            'code' => $code,
            'vertical' => $vertical,
            'severity' => $severity,
            'description' => $description,
            'check_type' => $checkType,
            'check_definition_json' => json_encode($checkDefinition),
            'fix_hint_template' => $fixHint,
            'enabled' => true,
            'auto_fix_available' => $autoFix,
            'created_by' => 'seed-mvp-sprint-0',
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }
}
