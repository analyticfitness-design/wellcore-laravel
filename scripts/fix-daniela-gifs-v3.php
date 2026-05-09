<?php
/**
 * Fix GIFs plan 183 Daniela — valida existencia real en GitHub con HEAD request.
 * Corrige orden en Martes (facepull) y Jueves (remo menton + frontales).
 * Run: php /code/scripts/fix-daniela-gifs-v3.php
 */

$pdo = new PDO(
    'mysql:host=wellcorefitness_wellcorefitness-mysql;dbname=wellcorefitness;charset=utf8mb4',
    'wellcorefitness', 'fYCVgn4XZ7twq34',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$gifBase   = 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/';
$headCache = [];

function gifExists(string $filename, string $gifBase, array &$cache): bool
{
    if (isset($cache[$filename])) return $cache[$filename];
    $url = $gifBase . $filename;
    $ch  = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 6);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $cache[$filename] = ($code === 200);
    return $cache[$filename];
}

function findGif(PDO $pdo, array $keywords, string $gifBase, array &$cache): ?string
{
    foreach ($keywords as $kw) {
        $stmt = $pdo->prepare(
            "SELECT gif_filename FROM exercise_aliases
             WHERE alias LIKE ? AND gif_filename IS NOT NULL
             GROUP BY gif_filename ORDER BY MAX(score) DESC LIMIT 8"
        );
        $stmt->execute(["%$kw%"]);
        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($rows as $gif) {
            echo "    trying $gif ... ";
            if (gifExists($gif, $gifBase, $cache)) { echo "OK\n"; return $gif; }
            echo "404\n";
        }
    }
    return null;
}

// ═══════════════════════════════════════════════════════════════
// 1. MAPA ejercicio → keywords de búsqueda
// ═══════════════════════════════════════════════════════════════
$searchMap = [
    'sentadilla hack'               => ['hack squat','maquina hack','hack machine','hacka'],
    'sentadilla goblet'             => ['goblet squat','goblet','sentadilla goblet'],
    'jalon al pecho'                => ['lat pulldown','jalon al pecho','pull down'],
    'remo con barra'                => ['barbell row','bent over row','remo barra inclinado'],
    'face pull'                     => ['face pull','facepull','cable face'],
    'hip thrust'                    => ['hip thrust','barbell hip thrust','barra hip thrust'],
    'abduccion de cadera'           => ['hip abduction machine','abduccion maquina','hip abduction'],
    'patada de gluteo'              => ['donkey kick','cable kickback glute','gluteo cable patada'],
    'elevaciones laterales'         => ['cable lateral raise','lateral raise cable','elevacion lateral cable'],
    'elevaciones posteriores'       => ['rear delt fly','rear lateral raise','bent over lateral','posterior delt'],
    'remo al menton'                => ['upright row barbell','upright row','remo al menton'],
    'elevaciones frontales'         => ['front raise dumbbell','dumbbell front raise','front raise'],
    'peso muerto convencional'      => ['conventional deadlift','barbell deadlift','deadlift barbell'],
    'curl femoral acostado'         => ['lying leg curl','prone leg curl','curl femoral acostado'],
    'curl femoral sentado'          => ['seated leg curl','curl femoral sentado'],
    'hiperextensiones'              => ['hyperextension','back extension','roman chair hyperextension'],
    'puente de gluteo'              => ['hip thrust','barbell hip thrust','glute bridge barbell'],
    'patada trasera en maquina'     => ['cable kickback','machine kickback','donkey kick cable'],
    'press arnold'                  => ['arnold press','arnold dumbbell','mancuerna arnold'],
    'fondos en banco'               => ['bench dip','tricep dip','dips banco'],
    'jalon al pecho agarre supino'  => ['reverse grip pulldown','underhand pulldown','agarre inverso jalon'],
    'remo con mancuernas inclinado' => ['incline dumbbell row','prone dumbbell row','two arm dumbbell row'],
];

echo "=== VALIDANDO GIFs CON HTTP HEAD ===\n";
$resolved = [];
foreach ($searchMap as $ej => $kws) {
    echo "\n[$ej]\n";
    $gif = findGif($pdo, $kws, $gifBase, $headCache);
    $resolved[$ej] = $gif;
    echo $gif ? "  RESULT: $gif\n" : "  RESULT: SIN GIF VALIDO\n";
}

// ═══════════════════════════════════════════════════════════════
// 2. CARGAR PLAN Y APLICAR
// ═══════════════════════════════════════════════════════════════
$row  = $pdo->query('SELECT content FROM assigned_plans WHERE id=183')->fetch(PDO::FETCH_ASSOC);
$plan = json_decode($row['content'], true);
$semanasKey = isset($plan['semanas']) ? 'semanas' : 'weeks';

$gifChanges   = 0;
$orderChanges = 0;

function applyGifFixes(array &$ejs, array $resolved, string $gifBase, int &$gifChanges): void
{
    foreach ($ejs as &$e) {
        $n = mb_strtolower($e['nombre'] ?? '');
        foreach ($resolved as $keyword => $gif) {
            if (!$gif) continue;
            if (str_contains($n, $keyword)) {
                $newUrl = $gifBase . $gif;
                if (($e['gif_url'] ?? '') !== $newUrl) {
                    echo "  GIF: [{$e['nombre']}] => $gif\n";
                    $e['gif_url'] = $newUrl;
                    $gifChanges++;
                }
                break;
            }
        }
    }
    unset($e);
}

function reorderMartes(array &$ejs, int &$orderChanges): void
{
    $remoIdx = $facepullIdx = -1;
    foreach ($ejs as $i => $e) {
        $n = mb_strtolower($e['nombre'] ?? '');
        if (str_contains($n, 'remo') && (str_contains($n, 'sentado') || str_contains($n, 'polea') || str_contains($n, 'neutro'))) $remoIdx = $i;
        if (str_contains($n, 'face pull') || str_contains($n, 'facepull')) $facepullIdx = $i;
    }
    if ($remoIdx >= 0 && $facepullIdx >= 0 && $facepullIdx !== $remoIdx + 1) {
        $fp = array_splice($ejs, $facepullIdx, 1)[0];
        $newRemo = $remoIdx > $facepullIdx ? $remoIdx - 1 : $remoIdx;
        array_splice($ejs, $newRemo + 1, 0, [$fp]);
        echo "  ORDER Martes: face pull -> despues del remo sentado\n";
        $orderChanges++;
    }
}

function reorderJueves(array &$ejs, int &$orderChanges): void
{
    $postIdx = $mentonIdx = $frontalIdx = -1;
    foreach ($ejs as $i => $e) {
        $n = mb_strtolower($e['nombre'] ?? '');
        if (str_contains($n, 'posterior')) $postIdx    = $i;
        if (str_contains($n, 'ment'))      $mentonIdx  = $i;
        if (str_contains($n, 'frontal'))   $frontalIdx = $i;
    }
    if ($postIdx < 0) return;

    $toMove  = [];
    $indices = [];
    if ($mentonIdx  >= 0) { $toMove[] = $ejs[$mentonIdx];  $indices[] = $mentonIdx; }
    if ($frontalIdx >= 0) { $toMove[] = $ejs[$frontalIdx]; $indices[] = $frontalIdx; }
    if (empty($toMove)) return;

    rsort($indices);
    foreach ($indices as $idx) array_splice($ejs, $idx, 1);

    $removed = count(array_filter($indices, fn($i) => $i <= $postIdx));
    $newPost = $postIdx - $removed;

    foreach (array_reverse($toMove) as $ej) {
        array_splice($ejs, $newPost + 1, 0, [$ej]);
    }
    echo "  ORDER Jueves: menton+frontales -> despues de posteriores\n";
    $orderChanges++;
}

echo "\n=== APLICANDO A TODAS LAS SEMANAS ===\n";
foreach ($plan[$semanasKey] as $si => &$semana) {
    $diasKey = isset($semana['dias']) ? 'dias' : 'days';
    $ejsKey  = isset($semana[$diasKey][0]['ejercicios']) ? 'ejercicios' : 'exercises';
    echo "\n-- Semana " . ($si + 1) . " --\n";

    foreach ($semana[$diasKey] as &$dia) {
        $dn = mb_strtolower($dia['nombre'] ?? ($dia['dia'] ?? ''));
        $hasBloques = !empty($dia[$ejsKey]) && isset($dia[$ejsKey][0]['ejercicios']);

        if ($hasBloques) {
            foreach ($dia[$ejsKey] as &$bloque) {
                applyGifFixes($bloque['ejercicios'], $resolved, $gifBase, $gifChanges);
                if (str_contains($dn, 'martes')) reorderMartes($bloque['ejercicios'], $orderChanges);
                if (str_contains($dn, 'jueves')) reorderJueves($bloque['ejercicios'], $orderChanges);
            }
            unset($bloque);
        } else {
            applyGifFixes($dia[$ejsKey], $resolved, $gifBase, $gifChanges);
            if (str_contains($dn, 'martes')) reorderMartes($dia[$ejsKey], $orderChanges);
            if (str_contains($dn, 'jueves')) reorderJueves($dia[$ejsKey], $orderChanges);
        }
    }
    unset($dia);
}
unset($semana);

echo "\nGIF changes: $gifChanges | Order changes: $orderChanges\n";

if ($gifChanges > 0 || $orderChanges > 0) {
    $json = json_encode($plan, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $pdo->prepare('UPDATE assigned_plans SET content=? WHERE id=183')->execute([$json]);
    echo "Plan 183 guardado en BD\n";
}
echo "\n=== DONE ===\n";
