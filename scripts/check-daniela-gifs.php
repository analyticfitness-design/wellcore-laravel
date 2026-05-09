<?php
/**
 * Verifica qué GIFs están asignados en plan 183 y cuáles existen en exercise_aliases
 * Run: php /code/scripts/check-daniela-gifs.php
 */
$p = new PDO(
    'mysql:host=wellcorefitness_wellcorefitness-mysql;dbname=wellcorefitness;charset=utf8mb4',
    'wellcorefitness', 'fYCVgn4XZ7twq34',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// 1) Buscar específicamente hack en exercise_aliases
echo "=== exercise_aliases: HACK ===\n";
$stmt = $p->query("SELECT alias, gif_filename, score FROM exercise_aliases WHERE alias LIKE '%hack%' OR gif_filename LIKE '%hack%' ORDER BY score DESC");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
    printf("  %-50s => %s\n", $r['alias'], $r['gif_filename']);
}

// 2) Todos los gif_url usados en plan 183 semana 1
echo "\n=== GIFs en plan 183 (semana 1) ===\n";
$row  = $p->query('SELECT content FROM assigned_plans WHERE id=183')->fetch(PDO::FETCH_ASSOC);
$plan = json_decode($row['content'], true);

$semanasKey = isset($plan['semanas']) ? 'semanas' : 'weeks';
$semana1    = $plan[$semanasKey][0];
$diasKey    = isset($semana1['dias']) ? 'dias' : 'days';
$ejsKey     = isset($semana1[$diasKey][0]['ejercicios']) ? 'ejercicios' : 'exercises';

$gifBase = 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/';

foreach ($semana1[$diasKey] as $dia) {
    $dn = $dia['nombre'] ?? ($dia['dia'] ?? '?');
    echo "\n[$dn]\n";
    $ejs = $dia[$ejsKey];
    $flat = [];
    if (!empty($ejs) && isset($ejs[0]['ejercicios'])) {
        foreach ($ejs as $b) foreach ($b['ejercicios'] as $e) $flat[] = $e;
    } else {
        $flat = $ejs;
    }
    foreach ($flat as $e) {
        $nombre  = $e['nombre'] ?? '?';
        $gifUrl  = $e['gif_url'] ?? '(sin gif)';
        $gifFile = str_replace($gifBase, '', $gifUrl);
        // Verificar si existe en exercise_aliases
        $check = $p->prepare("SELECT COUNT(*) FROM exercise_aliases WHERE gif_filename=?");
        $check->execute([$gifFile]);
        $exists = $check->fetchColumn() > 0 ? '✅' : '❌ NO EN ALIASES';
        printf("  %-40s => %-55s %s\n", $nombre, $gifFile, $exists);
    }
}
