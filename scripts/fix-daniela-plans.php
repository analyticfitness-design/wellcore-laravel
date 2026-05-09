<?php
/**
 * Fix plans for Daniela Lucia Barboza Cardenas (client_id=96)
 *
 * Plan 183 (training):
 *   - Escaladora → Caminadora inclinada (sin variación)
 *   - Crunch en máquina → Crunch codo a rodilla (+2 reps, equipo libre)
 *   - Crunch oblicuo en máquina → Crunches oblicuos acostado (+2 reps, equipo libre)
 *
 * Plan 184 (nutrition):
 *   - Ajustar horarios para entrenamiento 5pm
 *   - Pre-entrenamiento ~4:00-4:30 PM
 *   - Post-entrenamiento ~7:00 PM
 *   - Cena/Comida nocturna ~8:00 PM
 *
 * Run: php /code/storage/app/fix_daniela_plans.php
 */

$pdo = new PDO(
    'mysql:host=wellcorefitness_wellcorefitness-mysql;dbname=wellcorefitness;charset=utf8mb4',
    'wellcorefitness',
    'fYCVgn4XZ7twq34',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$gifBase = 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/';

// ================================================================
// PLAN 183 — ENTRENAMIENTO
// ================================================================
echo "\n=== PLAN 183 — ENTRENAMIENTO ===\n\n";

$row = $pdo->query('SELECT content FROM assigned_plans WHERE id=183')->fetch(PDO::FETCH_ASSOC);
$plan = json_decode($row['content'], true);

$changes = 0;

function fixEjercicio(array &$ej, string $gifBase, int &$changes): void
{
    $nombre = $ej['nombre'] ?? '';

    if ($nombre === 'Escaladora') {
        $ej['nombre']  = 'Caminadora inclinada';
        $ej['gif_url'] = $gifBase . 'caminadora-inclinada.gif';
        $ej['notas']   = 'Velocidad moderada. FC objetivo 130-145 bpm. Inclinación 4-6%. Activa glúteos y posterior de pierna.';
        unset($ej['variacion']); // sin variación bicicleta
        $changes++;
        echo "  ✅ Escaladora → Caminadora inclinada\n";
        return;
    }

    if ($nombre === 'Crunch en máquina') {
        $ej['nombre']  = 'Crunch codo a rodilla';
        $ej['gif_url'] = $gifBase . 'crunch-codo-a-rodilla.gif';
        $ej['equipo']  = 'Sin equipo';
        // Remove machine-based notas if present
        if (isset($ej['equipo_original'])) unset($ej['equipo_original']);
        // +2 reps
        if (isset($ej['repeticiones'])) {
            if ($ej['repeticiones'] === '8' || $ej['repeticiones'] === 8) $ej['repeticiones'] = '10';
            elseif ($ej['repeticiones'] === '6' || $ej['repeticiones'] === 6) $ej['repeticiones'] = '8';
            elseif ($ej['repeticiones'] === '10' || $ej['repeticiones'] === 10) $ej['repeticiones'] = '12';
        }
        // Update variation to free alternative
        if (isset($ej['variacion'])) {
            $ej['variacion']['nombre']  = 'Bicicleta crunch';
            $ej['variacion']['gif_url'] = $gifBase . 'bicicleta-crunch.gif';
            unset($ej['variacion']['equipo']);
        }
        $changes++;
        echo "  ✅ Crunch en máquina → Crunch codo a rodilla (reps +2, equipo libre)\n";
        return;
    }

    if ($nombre === 'Crunch oblicuo en máquina') {
        $ej['nombre']  = 'Crunches oblicuos acostado';
        $ej['gif_url'] = $gifBase . 'crunches-oblicuos-acostado.gif';
        $ej['equipo']  = 'Sin equipo';
        // +2 reps
        if (isset($ej['repeticiones'])) {
            if ($ej['repeticiones'] === '8' || $ej['repeticiones'] === 8) $ej['repeticiones'] = '10';
            elseif ($ej['repeticiones'] === '6' || $ej['repeticiones'] === 6) $ej['repeticiones'] = '8';
            elseif ($ej['repeticiones'] === '10' || $ej['repeticiones'] === 10) $ej['repeticiones'] = '12';
        }
        unset($ej['variacion']); // quitar variación de máquina
        $changes++;
        echo "  ✅ Crunch oblicuo en máquina → Crunches oblicuos acostado (reps +2, equipo libre)\n";
        return;
    }
}

function traversePlan(mixed &$node, string $gifBase, int &$changes): void
{
    if (!is_array($node)) return;

    // Duck-type: if this IS an exercise node, fix it directly
    if (
        isset($node['nombre']) &&
        (isset($node['gif_url']) || isset($node['series']) || isset($node['is_cardio']))
    ) {
        fixEjercicio($node, $gifBase, $changes);
        // Still traverse children (e.g., variacion)
        foreach ($node as $key => &$child) {
            if ($key !== 'nombre' && $key !== 'gif_url' && is_array($child)) {
                traversePlan($child, $gifBase, $changes);
            }
        }
        unset($child);
        return;
    }

    // Otherwise walk every child array
    foreach ($node as $key => &$child) {
        if (is_array($child)) {
            traversePlan($child, $gifBase, $changes);
        }
    }
    unset($child);
}

traversePlan($plan, $gifBase, $changes);

echo "\nTotal cambios plan 183: $changes\n";

if ($changes > 0) {
    $json = json_encode($plan, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $pdo->prepare('UPDATE assigned_plans SET content=? WHERE id=183')->execute([$json]);
    echo "✅ Plan 183 guardado en BD\n";
    file_put_contents(
        '/code/storage/app/plan183_daniela_v2.json',
        json_encode($plan, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
    );
    echo "✅ Copia en /code/storage/app/plan183_daniela_v2.json\n";
} else {
    echo "⚠️  Sin cambios — revisar nombres de ejercicios en el JSON\n";
    // Dump exercise names to debug
    echo "\n--- Ejercicios encontrados ---\n";
    dumpEjercicios($plan);
}

function dumpEjercicios(mixed $node, int $depth = 0): void
{
    if (!is_array($node)) return;
    if (isset($node['nombre']) && (isset($node['gif_url']) || isset($node['series']) || isset($node['is_cardio']))) {
        echo str_repeat('  ', $depth) . '- ' . $node['nombre'] . "\n";
        return;
    }
    foreach ($node as $child) {
        if (is_array($child)) dumpEjercicios($child, $depth + 1);
    }
}

// ================================================================
// PLAN 184 — NUTRICIÓN
// ================================================================
echo "\n=== PLAN 184 — NUTRICIÓN ===\n\n";

$row  = $pdo->query('SELECT content FROM assigned_plans WHERE id=184')->fetch(PDO::FETCH_ASSOC);
$nutr = json_decode($row['content'], true);

// Save current copy for inspection
file_put_contents(
    '/code/storage/app/plan184_daniela_current.json',
    json_encode($nutr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
);
echo "Copia de inspección → /code/storage/app/plan184_daniela_current.json\n\n";

// Print top-level keys and meal structure
echo "Top-level keys: " . implode(', ', array_keys($nutr)) . "\n";

// Locate meals array — try common keys
$mealsKey  = null;
$mealsNode = null;
foreach (['comidas', 'meals', 'alimentos', 'plan_comidas', 'dias', 'comidas_dia'] as $k) {
    if (isset($nutr[$k]) && is_array($nutr[$k])) {
        $mealsKey  = $k;
        $mealsNode = &$nutr[$k];
        echo "Clave de comidas detectada: '$k' (" . count($nutr[$k]) . " items)\n";
        break;
    }
}

if ($mealsKey === null) {
    echo "⚠️  No se encontró clave de comidas. Estructura completa:\n";
    echo json_encode($nutr, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
    exit(1);
}

// Map target times for each meal type
// Daniela trains at 5pm → pre-workout ~4:00 PM, post ~7:00 PM, dinner ~8:00 PM
$timeMap = [
    // nombre (lowercase) → nueva hora
    'desayuno'           => '7:00 AM',
    'media mañana'       => '10:00 AM',
    'merienda mañana'    => '10:00 AM',
    'almuerzo'           => '12:30 PM',
    'pre-entrenamiento'  => '4:00 PM',
    'pre entrenamiento'  => '4:00 PM',
    'merienda'           => '4:00 PM',   // reemplaza merienda → pre-entreno
    'snack'              => '4:00 PM',
    'post-entrenamiento' => '7:00 PM',
    'post entrenamiento' => '7:00 PM',
    'cena'               => '8:00 PM',
    'comida nocturna'    => '8:00 PM',
    'comida'             => '8:00 PM',
];

$nutrChanges = 0;

function adjustMealTiming(array &$meal, array $timeMap, int &$nutrChanges): void
{
    $nombre = strtolower(trim($meal['nombre'] ?? $meal['name'] ?? ''));
    foreach ($timeMap as $key => $newTime) {
        if (str_contains($nombre, $key)) {
            $oldTime = $meal['hora'] ?? $meal['time'] ?? '—';
            if ($oldTime !== $newTime) {
                $meal['hora'] = $newTime;
                if (isset($meal['time'])) $meal['time'] = $newTime;
                $nutrChanges++;
                echo "  ✅ '{$meal['nombre']}': $oldTime → $newTime\n";
            }
            return;
        }
    }
    echo "  ⏭  '{$meal['nombre']}' — sin cambio (hora: " . ($meal['hora'] ?? '—') . ")\n";
}

// Handle flat array of meals or nested dias > comidas
$firstItem = $mealsNode[0] ?? null;
if ($firstItem !== null && isset($firstItem['nombre']) && (isset($firstItem['hora']) || isset($firstItem['alimentos']))) {
    // Flat: comidas[] directly
    echo "\nEstructura: array plano de comidas\n\n";
    foreach ($mealsNode as &$meal) {
        adjustMealTiming($meal, $timeMap, $nutrChanges);
    }
    unset($meal);
} elseif ($firstItem !== null && isset($firstItem['comidas'])) {
    // Nested: dias[].comidas[]
    echo "\nEstructura: dias[].comidas[]\n\n";
    foreach ($mealsNode as &$dia) {
        foreach ($dia['comidas'] as &$meal) {
            adjustMealTiming($meal, $timeMap, $nutrChanges);
        }
        unset($meal);
    }
    unset($dia);
} else {
    echo "⚠️  Estructura desconocida del primer elemento:\n";
    echo json_encode($firstItem, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
    exit(1);
}

echo "\nTotal cambios plan 184: $nutrChanges\n";

if ($nutrChanges > 0) {
    $jsonN = json_encode($nutr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $pdo->prepare('UPDATE assigned_plans SET content=? WHERE id=184')->execute([$jsonN]);
    echo "✅ Plan 184 guardado en BD\n";
    file_put_contents(
        '/code/storage/app/plan184_daniela_v2.json',
        json_encode($nutr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
    );
    echo "✅ Copia en /code/storage/app/plan184_daniela_v2.json\n";
}

echo "\n=== DONE ===\n";
