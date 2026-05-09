<?php
$p = new PDO(
    'mysql:host=wellcorefitness_wellcorefitness-mysql;dbname=wellcorefitness;charset=utf8mb4',
    'wellcorefitness', 'fYCVgn4XZ7twq34',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
$row  = $p->query('SELECT content FROM assigned_plans WHERE id=183')->fetch(PDO::FETCH_ASSOC);
$plan = json_decode($row['content'], true);

$semanasKey = isset($plan['semanas']) ? 'semanas' : (isset($plan['weeks']) ? 'weeks' : null);
$semana1    = $plan[$semanasKey][0];
$diasKey    = isset($semana1['dias']) ? 'dias' : 'days';
$ejsKey     = isset($semana1[$diasKey][0]['ejercicios']) ? 'ejercicios' : 'exercises';

echo "SEMANA 1 — EJERCICIOS POR DÍA:\n";
foreach ($semana1[$diasKey] as $dia) {
    $dn  = $dia['nombre'] ?? ($dia['dia'] ?? '?');
    echo "\n[$dn]\n";
    $ejs = $dia[$ejsKey];
    $flat = [];
    if (!empty($ejs) && isset($ejs[0]['ejercicios'])) {
        foreach ($ejs as $b) {
            foreach ($b['ejercicios'] as $e) $flat[] = $e;
        }
    } else {
        $flat = $ejs;
    }
    foreach ($flat as $e) {
        $r = $e['repeticiones'] ?? ($e['duracion'] ?? '?');
        printf("  s=%-2s r=%-14s %s\n", $e['series'] ?? '?', $r, $e['nombre'] ?? '?');
    }
}
