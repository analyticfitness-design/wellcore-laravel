<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$plan = App\Models\AssignedPlan::find(115);
$content = $plan->content;

echo "=== TOP-LEVEL KEYS ===\n";
echo json_encode(array_keys($content), JSON_PRETTY_PRINT) . "\n\n";

echo "=== TOP-LEVEL VALUES (no semanas/notas) ===\n";
$light = $content;
unset($light['semanas']);
unset($light['notas_coach']);
unset($light['notas_generales']);
unset($light['principios']);
echo json_encode($light, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

echo "=== SEMANA 1 KEYS ===\n";
echo json_encode(array_keys($content['semanas'][0] ?? []), JSON_PRETTY_PRINT) . "\n\n";

echo "=== SEMANA 1 — DIA 0 (LUNES) COMPLETO ===\n";
echo json_encode($content['semanas'][0]['dias'][0] ?? null, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

echo "=== SEMANA 1 — DIA 0 — EJERCICIO 0 ===\n";
echo json_encode($content['semanas'][0]['dias'][0]['ejercicios'][0] ?? null, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

echo "=== EJERCICIO con variacion (buscar primer ej con key 'variacion') ===\n";
$found = null;
foreach ($content['semanas'][0]['dias'] as $d) {
    foreach (($d['ejercicios'] ?? []) as $e) {
        if (isset($e['variacion'])) { $found = $e; break 2; }
    }
}
echo json_encode($found, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
