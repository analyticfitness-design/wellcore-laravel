<?php
/**
 * Corrige GIFs incorrectos en plan 183 de Daniela.
 * - Sentadilla hack: barra-sentadilla-completa.gif → sentadilla-hack.gif
 * - Verifica y corrige todos los ejercicios nuevos/modificados
 * Run: php /code/scripts/fix-daniela-gifs-v2.php
 */
$p = new PDO(
    'mysql:host=wellcorefitness_wellcorefitness-mysql;dbname=wellcorefitness;charset=utf8mb4',
    'wellcorefitness', 'fYCVgn4XZ7twq34',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$gifBase = 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/';

// Mapa correcto: nombre del ejercicio (lowercase parcial) → gif exacto desde exercise_aliases
// Consultado directamente con alias específicos de alta confianza
function bestGif(PDO $p, string $alias): ?string
{
    $s = $p->prepare("SELECT gif_filename FROM exercise_aliases WHERE alias = ? AND gif_filename IS NOT NULL LIMIT 1");
    $s->execute([$alias]);
    $r = $s->fetch(PDO::FETCH_ASSOC);
    return $r ? $r['gif_filename'] : null;
}

// Mapa ejercicio → alias exacto en exercise_aliases → gif confirmado
$corrections = [
    // nombre parcial (lowercase)     alias exacto en BD
    'sentadilla hack'                 => bestGif($p, 'hack squat')          ?? 'sentadilla-hack.gif',
    'sentadilla goblet'               => bestGif($p, 'goblet squat')         ?? bestGif($p, 'sentadilla goblet') ?? 'mancuerna-sentadilla-goblet.gif',
    'hip thrust'                      => bestGif($p, 'hip thrust')           ?? 'barra-hip-thrust.gif',
    'face pull'                       => bestGif($p, 'face pull')            ?? 'cable-face-pull.gif',
    'remo al mentón'                  => bestGif($p, 'upright row')          ?? bestGif($p, 'remo al menton')   ?? 'barra-remo-al-menton.gif',
    'remo con mancuernas inclinado'   => bestGif($p, 'incline dumbbell row') ?? 'mancuerna-agarre-inverso-inclinado-banco-dos-brazo-remo.gif',
    'abducción de cadera'             => bestGif($p, 'hip abduction')        ?? 'lateral-puente-abduccion-de-cadera.gif',
    'sentadilla búlgara'              => bestGif($p, 'bulgarian squat')      ?? 'sentadilla-bulgara-barra.gif',
    'patada de glúteo'                => bestGif($p, 'donkey kick')          ?? 'cable-patada-trasera-gluteo.gif',
    'elevaciones posteriores'         => bestGif($p, 'rear delt fly')        ?? 'banda-flexionado-sobre-elevacion-lateral-posterior.gif',
    'elevaciones frontales'           => bestGif($p, 'front raise')          ?? 'mancuerna-elevacion-frontal.gif',
    'hiperextensiones en banco'       => bestGif($p, 'hyperextension')       ?? 'hiperextensiones-banco.gif',
    'patada trasera en máquina'       => bestGif($p, 'cable kickback')       ?? 'cable-patada-trasera.gif',
    'peso muerto convencional'        => bestGif($p, 'deadlift')             ?? 'barra-peso-muerto.gif',
    'press arnold'                    => bestGif($p, 'arnold press')         ?? 'mancuerna-arnold-press-2.gif',
    'fondos en banco'                 => bestGif($p, 'bench dip')            ?? 'banco-fondos-en-suelo.gif',
    'jalón al pecho agarre supino'    => bestGif($p, 'reverse grip pulldown') ?? 'agarre-inverso-maquina-jalon-al-pecho.gif',
];

echo "=== GIFs resueltos ===\n";
foreach ($corrections as $ejercicio => $gif) {
    printf("  %-40s => %s\n", $ejercicio, $gif);
}

// ─── Aplicar correcciones al plan ───────────────────────────────────────────
$row  = $p->query('SELECT content FROM assigned_plans WHERE id=183')->fetch(PDO::FETCH_ASSOC);
$plan = json_decode($row['content'], true);

$semanasKey = isset($plan['semanas']) ? 'semanas' : 'weeks';
$changes    = 0;

function fixGifInEj(array &$e, array $corrections, string $gifBase, int &$changes): void
{
    $nombre = mb_strtolower($e['nombre'] ?? '');
    foreach ($corrections as $keyword => $correctGif) {
        if (str_contains($nombre, mb_strtolower($keyword))) {
            $newUrl = $gifBase . $correctGif;
            if (($e['gif_url'] ?? '') !== $newUrl) {
                echo "  FIX: [{$e['nombre']}]\n";
                echo "       {$e['gif_url']}\n    => $newUrl\n";
                $e['gif_url'] = $newUrl;
                $changes++;
            }
            return;
        }
    }
}

foreach ($plan[$semanasKey] as &$semana) {
    $diasKey = isset($semana['dias']) ? 'dias' : 'days';
    $ejsKey  = isset($semana[$diasKey][0]['ejercicios']) ? 'ejercicios' : 'exercises';
    foreach ($semana[$diasKey] as &$dia) {
        $ejs = &$dia[$ejsKey];
        if (!empty($ejs) && isset($ejs[0]['ejercicios'])) {
            foreach ($ejs as &$bloque) {
                foreach ($bloque['ejercicios'] as &$e) {
                    fixGifInEj($e, $corrections, $gifBase, $changes);
                }
                unset($e);
            }
            unset($bloque);
        } else {
            foreach ($ejs as &$e) {
                fixGifInEj($e, $corrections, $gifBase, $changes);
            }
            unset($e);
        }
    }
    unset($dia);
}
unset($semana);

echo "\nTotal correcciones de GIF: $changes\n";

if ($changes > 0) {
    $json = json_encode($plan, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $p->prepare('UPDATE assigned_plans SET content=? WHERE id=183')->execute([$json]);
    echo "✅ Plan 183 actualizado en BD\n";
} else {
    echo "✅ Todos los GIFs ya estaban correctos\n";
}

echo "\n=== DONE ===\n";
