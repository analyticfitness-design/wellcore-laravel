<?php
$path = __DIR__ . '/supplement-stacks-seed.json';
$raw = file_get_contents($path);
$d = json_decode($raw, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "JSON_ERROR: " . json_last_error_msg() . PHP_EOL;
    exit(1);
}
echo "JSON OK" . PHP_EOL;
echo "total_stacks_field=" . ($d['total_stacks'] ?? 'NULL') . PHP_EOL;
echo "stacks_array_count=" . count($d['stacks']) . PHP_EOL;
echo "by_category_sum=" . array_sum($d['by_category']) . PHP_EOL;

// Cargar slugs disponibles del catálogo Pieza 4
$catalogPath = __DIR__ . '/supplement-catalog-seed.json';
$catalog = json_decode(file_get_contents($catalogPath), true);
$availableSlugs = [];
foreach ($catalog['supplements'] as $s) {
    $availableSlugs[$s['slug']] = true;
}
echo "catalog_slugs_count=" . count($availableSlugs) . PHP_EOL;

// Verificar que todos los slugs referenciados en stacks existen en catálogo
$missingSlugs = [];
$totalRefs = 0;
foreach ($d['stacks'] as $stack) {
    foreach (['components_essential', 'components_recommended', 'components_optional'] as $level) {
        foreach (($stack[$level] ?? []) as $c) {
            $slug = $c['supplement_slug'] ?? null;
            if ($slug && $slug !== 'NO_APLICA') {
                $totalRefs++;
                if (!isset($availableSlugs[$slug])) {
                    $missingSlugs[$slug][] = $stack['slug'];
                }
            }
        }
    }
}
echo "total_supplement_references=" . $totalRefs . PHP_EOL;
echo "missing_slugs_count=" . count($missingSlugs) . PHP_EOL;
foreach ($missingSlugs as $slug => $stacks) {
    echo "  MISSING: $slug -> in stacks: " . implode(', ', $stacks) . PHP_EOL;
}

// Listar stacks con su count de componentes
echo PHP_EOL . "STACKS LIST:" . PHP_EOL;
foreach ($d['stacks'] as $i => $stack) {
    $e = count($stack['components_essential'] ?? []);
    $r = count($stack['components_recommended'] ?? []);
    $o = count($stack['components_optional'] ?? []);
    printf("  %2d. %-50s e=%d r=%d o=%d  confidence=%s\n",
        $i + 1,
        $stack['slug'],
        $e, $r, $o,
        $stack['confidence'] ?? '?'
    );
}

// Bonus: cobertura retroactiva vs clientes audit
echo PHP_EOL . "BONUS — COBERTURA RETROACTIVA (vs audit):" . PHP_EOL;
$audits = [
    'lizetd_esencial_femenino_perdida_grasa' => [
        'expected_stack_slug' => 'stack-perdida-grasa-femenina-intermedia',
        'real_components' => [
            'proteina-whey-concentrada',
            'creatina-monohidrato',
            'magnesio-glicinato',
            'omega-3-epa-dha',
            'multivitaminico-base',
            'vitamina-d3',
            'cafeina-anhidra',
        ],
    ],
    'silvia_elite_femenino_recomposicion' => [
        'expected_stack_slug' => 'stack-recomposicion-femenina-elite',
        'real_components' => [
            'proteina-whey-isolada',
            'creatina-monohidrato',
            'omega-3-epa-dha',
            'vitamina-d3-k2',
            'magnesio-glicinato',
            'multivitaminico-femenino',
            'colageno-vit-c',
        ],
    ],
    'daniel_elite_masculino_volumen' => [
        'expected_stack_slug' => 'stack-volumen-masculino-elite',
        'real_components' => [
            'proteina-whey-isolada',
            'multivitaminico-base',
            'neuro-freak-blend-pre-workout',
            'creatina-monohidrato',
            'l-glutamina',
            'beta-alanina',
            'l-citrulina-malato',
        ],
    ],
];

foreach ($audits as $clientKey => $audit) {
    $stack = null;
    foreach ($d['stacks'] as $s) {
        if ($s['slug'] === $audit['expected_stack_slug']) { $stack = $s; break; }
    }
    if (!$stack) {
        echo "  $clientKey: STACK NOT FOUND ({$audit['expected_stack_slug']})" . PHP_EOL;
        continue;
    }
    // Combinar todos los componentes del stack
    $stackComponents = [];
    foreach (['components_essential', 'components_recommended', 'components_optional'] as $level) {
        foreach (($stack[$level] ?? []) as $c) {
            if (!empty($c['supplement_slug']) && $c['supplement_slug'] !== 'NO_APLICA') {
                $stackComponents[] = $c['supplement_slug'];
            }
        }
    }
    $real = $audit['real_components'];
    $matched = array_intersect($real, $stackComponents);
    $missing = array_diff($real, $stackComponents);
    $extra = array_diff($stackComponents, $real);
    $matchPct = count($real) > 0 ? round(count($matched) / count($real) * 100) : 0;

    printf("  %s\n", $clientKey);
    printf("    -> stack=%s\n", $audit['expected_stack_slug']);
    printf("    -> match_pct=%d%% (matched %d/%d real components)\n", $matchPct, count($matched), count($real));
    if (!empty($missing)) {
        printf("    -> MISSING in stack: %s\n", implode(', ', $missing));
    }
    if (!empty($extra)) {
        printf("    -> EXTRA in stack (not in real): %s\n", implode(', ', array_slice($extra, 0, 6)));
    }
}
