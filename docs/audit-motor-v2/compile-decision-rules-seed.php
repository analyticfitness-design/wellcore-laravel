<?php
/**
 * compile-decision-rules-seed.php
 *
 * Consolida los 12 chunks de decision rules de Pieza 7 en un solo archivo
 * `decision-rules-seed.json` listo para consumo del motor v2.
 *
 * Aplica el patch mensual (feedback_planes_mensuales_solamente 2026-05-17)
 * a las rules afectadas: A6, A7, A9, C5, C7, C8.
 *
 * Output:
 *   - docs/audit-motor-v2/decision-rules-seed.json
 *   - docs/audit-motor-v2/decision-rules-COBERTURA-RETROACTIVA.md
 *   - stdout: stats por categoría y priority
 */

declare(strict_types=1);

$baseDir = __DIR__;
$chunkFiles = [];
for ($i = 1; $i <= 12; $i++) {
    $chunkFiles[] = sprintf('%s/decision-rules-CHUNK-%02d.json', $baseDir, $i);
}

$allRules = [];
$chunkStats = [];

foreach ($chunkFiles as $file) {
    if (!file_exists($file)) {
        fwrite(STDERR, "MISSING: $file\n");
        exit(1);
    }
    $data = json_decode(file_get_contents($file), true);
    if (!$data || !isset($data['rules'])) {
        fwrite(STDERR, "BAD JSON: $file\n");
        exit(1);
    }
    $chunkName = basename($file);
    $count = count($data['rules']);
    $chunkStats[$chunkName] = $count;
    foreach ($data['rules'] as $rule) {
        $allRules[] = $rule;
    }
}

// --------- APLICAR PATCH MENSUAL (PATCH-MENSUAL.md) ---------
// Rules afectadas: A6 (12sem metodo), A7 (4sem elite), A9 (8sem recomp),
// C5 (duracion_maxima), C7 (duracion_recomendada), C8 (duracion_maxima).
$patchAppliedCount = 0;
foreach ($allRules as &$rule) {
    $slug = $rule['slug'];

    // A6 — reformular input para usar bloque_progresion en lugar de duracion_semanas_solicitada
    if ($slug === 'select-metodologia-12sem-metodo-tier') {
        $rule['input_conditions']['all_of'] = [
            ['field' => 'client.tier', 'op' => 'equals', 'value' => 'metodo'],
            ['field' => 'client.level', 'op' => 'in', 'value' => ['intermedio', 'avanzado']],
        ];
        $rule['_patch_mensual_2026-05-17'] = 'Reformulada: ya no depende de duracion_semanas_solicitada=12. Tier metodo siempre = bloque conceptual de 4 meses con planes mensuales secuenciales (ver meta-rule E5).';
        $patchAppliedCount++;
    }

    // A7 — la duracion 4sem ya es default mensual, simplificar input
    if ($slug === 'select-metodologia-4sem-elite-mujer') {
        $rule['input_conditions']['all_of'] = [
            ['field' => 'client.tier', 'op' => 'equals', 'value' => 'elite'],
            ['field' => 'client.gender', 'op' => 'equals', 'value' => 'femenino'],
            ['field' => 'client.level', 'op' => 'in', 'value' => ['intermedio', 'avanzado']],
        ];
        $rule['_patch_mensual_2026-05-17'] = 'duracion_semanas_solicitada=4 removida del input (default mensual).';
        $patchAppliedCount++;
    }

    // A9 — recomp 8sem ahora se modela como 2 meses consecutivos
    if ($slug === 'select-metodologia-recomposicion-8sem') {
        $rule['input_conditions']['all_of'] = [
            ['field' => 'client.objective', 'op' => 'equals', 'value' => 'recomposicion'],
            ['field' => 'client.level', 'op' => 'in', 'value' => ['intermedio', 'avanzado']],
        ];
        $rule['name'] = 'Recomposición en bloque multi-mes para cliente intermedio/avanzado';
        $rule['_patch_mensual_2026-05-17'] = 'duracion_semanas_solicitada>=8 removida. Recomp ahora se materializa como 2 planes mensuales consecutivos (ver meta-rule E5 secuencia).';
        $patchAppliedCount++;
    }

    // C5/C7/C8 — campos duracion_maxima/recomendada en SEMANAS pasan a MESES
    if ($slug === 'ajuste-deficit-perdida-agresiva') {
        if (isset($rule['output']['duracion_maxima_recomendada_semanas'])) {
            $rule['output']['duracion_maxima_recomendada_meses'] = 2;
            $rule['output']['duracion_maxima_rationale'] = 'En modelo mensual: máximo 2 planes mensuales consecutivos de déficit agresivo. Después transitar a déficit moderado.';
            unset($rule['output']['duracion_maxima_recomendada_semanas']);
        }
        $rule['_patch_mensual_2026-05-17'] = 'duracion_maxima_recomendada_semanas:8 → duracion_maxima_recomendada_meses:2';
        $patchAppliedCount++;
    }
    if ($slug === 'ajuste-aumento-limpio') {
        if (isset($rule['output']['duracion_recomendada_semanas'])) {
            $rule['output']['duracion_recomendada_meses'] = [3, 6];
            unset($rule['output']['duracion_recomendada_semanas']);
        }
        $rule['_patch_mensual_2026-05-17'] = 'duracion_recomendada_semanas:[12,24] → duracion_recomendada_meses:[3,6]';
        $patchAppliedCount++;
    }
    if ($slug === 'ajuste-aumento-agresivo') {
        if (isset($rule['output']['duracion_recomendada_semanas'])) {
            $rule['output']['duracion_recomendada_meses'] = [2, 4];
            unset($rule['output']['duracion_recomendada_semanas']);
        }
        $rule['_patch_mensual_2026-05-17'] = 'duracion_recomendada_semanas:[8,16] → duracion_recomendada_meses:[2,4]';
        $patchAppliedCount++;
    }
}
unset($rule);

// --------- STATS POR CATEGORÍA Y PRIORITY ---------
$byCategory = [];
$byPriorityRange = ['50_100' => 0, '100_150' => 0, '150_200' => 0, '200_plus' => 0];
$needsValidationCount = 0;
$confidenceCounts = ['high' => 0, 'moderate' => 0, 'low' => 0];
$activeCount = 0;
$inactiveCount = 0;
$slugsByCategory = [];

foreach ($allRules as $rule) {
    $ruleType = $rule['rule_type'];
    $byCategory[$ruleType] = ($byCategory[$ruleType] ?? 0) + 1;
    $slugsByCategory[$ruleType][] = $rule['slug'];

    $p = $rule['priority'];
    if ($p < 100)        $byPriorityRange['50_100']++;
    elseif ($p < 150)    $byPriorityRange['100_150']++;
    elseif ($p < 200)    $byPriorityRange['150_200']++;
    else                  $byPriorityRange['200_plus']++;

    if (!empty($rule['needs_daniel_validation'])) $needsValidationCount++;
    $conf = $rule['confidence'] ?? 'moderate';
    $confidenceCounts[$conf] = ($confidenceCounts[$conf] ?? 0) + 1;

    if (!empty($rule['active'])) $activeCount++;
    else $inactiveCount++;
}

// --------- BUILD FINAL SEED ---------
$seed = [
    'version' => '1.0',
    'generated_at' => '2026-05-17',
    'generated_by' => 'Claude Opus 4.7 + Daniel Esparza',
    'scope' => 'MVP decision engine motor v2 — capa rules-based determinista',
    'memoria_autoritativa' => 'feedback_planes_mensuales_solamente.md (2026-05-17) — todos los planes son MENSUALES (4 sem)',
    'patch_aplicado' => 'PATCH-MENSUAL.md — ajustes retroactivos en A6, A7, A9, C5, C7, C8',
    'patch_rules_modificadas_count' => $patchAppliedCount,
    'pieza_7_dependencies' => [
        'methodologies_seed' => 'docs/audit-motor-v2/methodologies-seed.json (Pieza 1)',
        'exercise_patterns' => 'docs/audit-motor-v2/exercise-patterns-CHUNK-01.json + agrupacion-PROPUESTA.md (Pieza 2 — confidence moderate)',
        'nutrition_foods_seed' => 'docs/audit-motor-v2/nutrition-foods-seed.json (Pieza 3)',
        'supplement_catalog_seed' => 'docs/audit-motor-v2/supplement-catalog-seed.json (Pieza 4)',
        'hormonal_protocols' => 'Pieza 6 NO curada — Categoría G todas active:false',
    ],
    'total_rules' => count($allRules),
    'by_category' => $byCategory,
    'by_priority_range' => $byPriorityRange,
    'by_confidence' => $confidenceCounts,
    'needs_daniel_validation_count' => $needsValidationCount,
    'active_count' => $activeCount,
    'inactive_count' => $inactiveCount,
    'rule_types_supported' => [
        'select_methodology',
        'select_split',
        'select_split_addon',
        'calculate_macros',
        'select_exercises',
        'select_exercises_filter',
        'select_exercises_technique',
        'select_cardio',
        'periodize',
        'meta_periodize_sequence',
        'select_supplement_stack',
        'select_supplement_stack_addon',
        'adjust_for_cycle_phase',
        'adjust_for_injury',
        'override_preference',
    ],
    'operators_supported' => [
        'equals', 'not_equals', 'in', 'not_in',
        'includes', 'excludes', 'between',
        'greater_than', 'less_than', 'greater_than_or_equal', 'less_than_or_equal',
        'is_null', 'is_not_null',
    ],
    'execution_phases' => ['intake_validation', 'select', 'compose', 'validate_pre_persist'],
    'fases_canonicas_plan' => ['Adaptación', 'Hipertrofia', 'Fuerza', 'Fuerza Máxima', 'Peak', 'Deload', 'Recuperación', 'Preparación', 'Mantenimiento'],
    'voz' => 'voseo colombiano neutro amable (vos sos/podés/sabés)',
    'modelo_planes' => 'TODOS MENSUALES (4 semanas). Bloques largos (Método 12 sem, Elite 12 sem) = secuencia de planes mensuales coordinados por meta-rule E5 + coach humano.',
    'pending_verifications_perplexity_pro' => 'Refs científicas marcadas verified:false esperando verificación bulk via Perplexity Pro Chrome MCP. Ver _pending_verifications en cada chunk individual.',
    'rules' => $allRules,
];

$seedFile = $baseDir . '/decision-rules-seed.json';
$json = json_encode($seed, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
file_put_contents($seedFile, $json);

echo "=== DECISION RULES SEED COMPILADO ===\n";
echo "Output: $seedFile\n";
echo "Total rules: " . count($allRules) . "\n";
echo "Patch mensual aplicado a: $patchAppliedCount rules\n";
echo "Active: $activeCount · Inactive (Categoría G pendiente Pieza 6): $inactiveCount\n";
echo "Needs Daniel validation: $needsValidationCount\n\n";

echo "--- Por categoría (rule_type) ---\n";
foreach ($byCategory as $type => $count) {
    echo "  $type: $count\n";
}

echo "\n--- Por priority range ---\n";
foreach ($byPriorityRange as $range => $count) {
    echo "  $range: $count\n";
}

echo "\n--- Por confidence ---\n";
foreach ($confidenceCounts as $conf => $count) {
    echo "  $conf: $count\n";
}

echo "\n--- Chunks origen ---\n";
foreach ($chunkStats as $file => $count) {
    echo "  $file: $count rules\n";
}

echo "\n✅ Seed compilado correctamente.\n";
