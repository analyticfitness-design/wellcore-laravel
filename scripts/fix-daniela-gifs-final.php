<?php
/**
 * Fix GIFs plan 183 Daniela — filenames confirmados del repo real GitHub.
 * Aplica sobre todas las semanas del plan.
 * Run: php /code/scripts/fix-daniela-gifs-final.php
 */

$pdo = new PDO(
    'mysql:host=wellcorefitness_wellcorefitness-mysql;dbname=wellcorefitness;charset=utf8mb4',
    'wellcorefitness', 'fYCVgn4XZ7twq34',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$base = 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/';

// Mapa: keyword (lowercase, en nombre del ejercicio) => gif real del repo
$gifMap = [
    'sentadilla hack'                => 'sentadilla-hacka.gif',
    'sentadilla goblet'              => 'sentadilla-goblet.gif',
    'jalón al pecho agarre supino'   => 'jalon-al-pecho-agarre-supino.gif',
    'jalon al pecho agarre supino'   => 'jalon-al-pecho-agarre-supino.gif',
    'jalón al pecho'                 => 'jalon-al-pecho-en-maquina.gif',
    'jalon al pecho'                 => 'jalon-al-pecho-en-maquina.gif',
    'remo con mancuernas'            => 'remo-con-mancuernas-sobre-banco-inclinado.gif',
    'remo con barra'                 => 'remo-con-barra.gif',
    'face pull'                      => 'facepull-en-polea.gif',
    'hip thrust'                     => 'hipthrust-con-barra.gif',
    'abducción de cadera'            => 'abduccion-de-cadera-de-pie-en-maquina.gif',
    'abduccion de cadera'            => 'abduccion-de-cadera-de-pie-en-maquina.gif',
    'patada de glúteo'               => 'patada-trasera-en-polea.gif',
    'patada de gluteo'               => 'patada-trasera-en-polea.gif',
    'elevaciones laterales'          => 'elevacion-lateral-con-mancuerna.gif',
    'elevaciones posteriores'        => 'elevacion-posterior-con-mancuerna.gif',
    'remo al mentón'                 => 'remo-al-menton-con-barra.gif',
    'remo al menton'                 => 'remo-al-menton-con-barra.gif',
    'elevaciones frontales'          => 'elevacion-frontal-con-mancuerna.gif',
    'peso muerto convencional'       => 'peso-muerto-con-barra.gif',
    'curl femoral acostado'          => 'curl-femoral-acostado-en-maquina.gif',
    'curl femoral sentado'           => 'curl-femoral-sentado.gif',
    'hiperextensiones'               => 'hiperextension.gif',
    'puente de glúteo'               => 'puente-de-gluteo-con-barra.gif',
    'puente de gluteo'               => 'puente-de-gluteo-con-barra.gif',
    'patada trasera en máquina'      => 'patada-trasera-en-maquina.gif',
    'patada trasera en maquina'      => 'patada-trasera-en-maquina.gif',
    'press arnold'                   => 'press-arnold-con-mancuerna.gif',
    'fondos en banco'                => 'fondos-de-triceps-entre-bancos.gif',
];

$row  = $pdo->query('SELECT content FROM assigned_plans WHERE id=183')->fetch(PDO::FETCH_ASSOC);
$plan = json_decode($row['content'], true);

$semanasKey = isset($plan['semanas']) ? 'semanas' : 'weeks';
$changes = 0;

function fixEj(array &$e, array $gifMap, string $base, int &$changes): void
{
    $nombre = mb_strtolower($e['nombre'] ?? '');
    foreach ($gifMap as $keyword => $gif) {
        if (str_contains($nombre, $keyword)) {
            $newUrl = $base . $gif;
            if (($e['gif_url'] ?? '') !== $newUrl) {
                echo "  FIX [{$e['nombre']}]\n    old: " . ($e['gif_url'] ?? '(vacío)') . "\n    new: $newUrl\n";
                $e['gif_url'] = $newUrl;
                $changes++;
            }
            return;
        }
    }
}

foreach ($plan[$semanasKey] as $si => &$semana) {
    $diasKey = isset($semana['dias']) ? 'dias' : 'days';
    $ejsKey  = isset($semana[$diasKey][0]['ejercicios']) ? 'ejercicios' : 'exercises';
    echo "\n--- Semana " . ($si + 1) . " ---\n";

    foreach ($semana[$diasKey] as &$dia) {
        $dn = $dia['nombre'] ?? ($dia['dia'] ?? '?');
        echo "[$dn]\n";
        $hasBloques = !empty($dia[$ejsKey]) && isset($dia[$ejsKey][0]['ejercicios']);

        if ($hasBloques) {
            foreach ($dia[$ejsKey] as &$bloque) {
                foreach ($bloque['ejercicios'] as &$e) fixEj($e, $gifMap, $base, $changes);
                unset($e);
            }
            unset($bloque);
        } else {
            foreach ($dia[$ejsKey] as &$e) fixEj($e, $gifMap, $base, $changes);
            unset($e);
        }
    }
    unset($dia);
}
unset($semana);

echo "\nTotal cambios: $changes\n";

if ($changes > 0) {
    $json = json_encode($plan, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $pdo->prepare('UPDATE assigned_plans SET content=? WHERE id=183')->execute([$json]);
    echo "Plan 183 actualizado en BD.\n";
} else {
    echo "Sin cambios necesarios.\n";
}

echo "\n=== DONE ===\n";
