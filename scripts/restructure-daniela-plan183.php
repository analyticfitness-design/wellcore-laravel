<?php
/**
 * Reestructura plan 183 de Daniela Lucia Barboza Cardenas (client_id=96)
 * Aplica cambios por día a las 4 semanas del plan de entrenamiento.
 *
 * Run: php /code/scripts/restructure-daniela-plan183.php
 */

$pdo = new PDO(
    'mysql:host=wellcorefitness_wellcorefitness-mysql;dbname=wellcorefitness;charset=utf8mb4',
    'wellcorefitness',
    'fYCVgn4XZ7twq34',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$gifBase = 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/';

// ════════════════════════════════════════════════════════
// 1. LOOKUP GIFs desde exercise_aliases
// ════════════════════════════════════════════════════════
function findGif(PDO $pdo, string $keyword): ?string
{
    $stmt = $pdo->prepare(
        "SELECT gif_filename FROM exercise_aliases
         WHERE (alias LIKE ? OR gif_filename LIKE ?)
           AND gif_filename IS NOT NULL
         ORDER BY score DESC LIMIT 1"
    );
    $stmt->execute(["%$keyword%", "%$keyword%"]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $row['gif_filename'] : null;
}

$lookup = [
    'goblet'             => 'goblet',
    'hip_thrust'         => 'hip thrust',
    'fondos'             => 'fondos',
    'jalon_supino'       => 'inverso jalon',
    'elev_posteriores'   => 'posterior elevacion',
    'remo_menton'        => 'menton',
    'abduccion'          => 'abduccion cadera',
    'bulgara'            => 'bulgara',
    'remo_mancuernas'    => 'remo mancuerna',
    'muerto_conv'        => 'peso muerto',
    'hiperext'           => 'hiper',
    'press_arnold'       => 'arnold',
    'elev_frontales'     => 'frontal',
    'patada_trasera'     => 'patada trasera',
    'facepull'           => 'face pull',
    'sentadilla_hack'    => 'hack',
    'prensa_piernas'     => 'prensa pierna',
    'curl_femoral_sent'  => 'curl femoral sentado',
    'curl_femoral_acost' => 'curl femoral acostado',
    'peso_muerto_rumano' => 'rumano',
    'patada_gluteo'      => 'patada gluteo',
    'press_hombros'      => 'press hombro mancuerna',
    'elev_lateral'       => 'elevacion lateral',
    'remo_barra'         => 'remo barra',
    'jalon_pecho'        => 'jalon al pecho',
];

$gifs = [];
echo "=== LOOKUP DE GIFs ===\n";
foreach ($lookup as $key => $kw) {
    $gif = findGif($pdo, $kw);
    $gifs[$key] = $gif;
    printf("  %-25s => %s\n", $key, $gif ?? '❌ NOT FOUND');
}

// Fallbacks hard-coded si el alias-lookup falla
$fallbacks = [
    'goblet'             => 'mancuerna-sentadilla-goblet.gif',
    'hip_thrust'         => 'barra-hip-thrust.gif',
    'fondos'             => 'banco-fondos-en-suelo.gif',
    'jalon_supino'       => 'agarre-inverso-maquina-jalon-al-pecho.gif',
    'elev_posteriores'   => 'banda-flexionado-sobre-elevacion-lateral-posterior.gif',
    'remo_menton'        => 'barra-remo-al-menton.gif',
    'abduccion'          => 'lateral-puente-abduccion-de-cadera.gif',
    'bulgara'            => 'sentadilla-bulgara-barra.gif',
    'remo_mancuernas'    => 'mancuerna-agarre-inverso-inclinado-banco-dos-brazo-remo.gif',
    'muerto_conv'        => 'barra-peso-muerto.gif',
    'hiperext'           => 'hiperextensiones-banco.gif',
    'press_arnold'       => 'mancuerna-press-arnold.gif',
    'elev_frontales'     => 'mancuerna-elevacion-frontal.gif',
    'patada_trasera'     => 'cable-patada-trasera.gif',
    'facepull'           => 'cable-face-pull.gif',
    'sentadilla_hack'    => 'maquina-sentadilla-hack.gif',
    'prensa_piernas'     => 'prensa-pierna.gif',
    'curl_femoral_sent'  => 'curl-femoral-sentado.gif',
    'curl_femoral_acost' => 'curl-femoral-acostado.gif',
    'peso_muerto_rumano' => 'peso-muerto-rumano.gif',
    'patada_gluteo'      => 'cable-patada-trasera-gluteo.gif',
    'press_hombros'      => 'press-de-hombro-con-mancuerna.gif',
    'elev_lateral'       => 'mancuerna-elevacion-lateral.gif',
    'remo_barra'         => 'barra-remo-al-pecho-inclinado.gif',
    'jalon_pecho'        => 'jalon-al-pecho-con-barra.gif',
];

foreach ($fallbacks as $key => $fb) {
    if (empty($gifs[$key])) {
        $gifs[$key] = $fb;
        echo "  [FALLBACK] $key => $fb\n";
    }
}

function g(array $gifs, string $key, string $gifBase): string
{
    return $gifBase . ($gifs[$key] ?? 'placeholder.gif');
}

// ════════════════════════════════════════════════════════
// 2. CARGAR PLAN 183
// ════════════════════════════════════════════════════════
$row  = $pdo->query('SELECT content FROM assigned_plans WHERE id=183')->fetch(PDO::FETCH_ASSOC);
$plan = json_decode($row['content'], true);

// ════════════════════════════════════════════════════════
// 3. DUMP estructura actual (semana 1) para diagnóstico
// ════════════════════════════════════════════════════════
echo "\n=== ESTRUCTURA ACTUAL semana 1 ===\n";
$semana1 = $plan['semanas'][0] ?? $plan['weeks'][0] ?? null;
if ($semana1 === null) {
    // Try flat structure
    echo "Top-level keys: " . implode(', ', array_keys($plan)) . "\n";
    // Check if 'dias' is at top level
    if (isset($plan['dias'])) {
        $semana1 = ['dias' => $plan['dias']];
        echo "(Plan es de una sola semana plana)\n";
    }
}

$diasKey  = null;
$ejsKey   = null;
$nombreKey = null;

if ($semana1) {
    foreach (['dias', 'days', 'sesiones'] as $dk) {
        if (isset($semana1[$dk])) { $diasKey = $dk; break; }
    }
    if ($diasKey) {
        $primerDia = $semana1[$diasKey][0] ?? null;
        if ($primerDia) {
            foreach (['ejercicios', 'exercises', 'bloques'] as $ek) {
                if (isset($primerDia[$ek])) { $ejsKey = $ek; break; }
            }
            foreach (['nombre', 'name', 'dia', 'day'] as $nk) {
                if (isset($primerDia[$nk])) { $nombreKey = $nk; break; }
            }
            echo "diasKey=$diasKey, ejsKey=$ejsKey, nombreKey=$nombreKey\n";
        }
    }

    if ($diasKey && $ejsKey) {
        foreach ($semana1[$diasKey] as $dia) {
            $diaNombre = $dia[$nombreKey ?? 'nombre'] ?? $dia['dia'] ?? '?';
            echo "\n  DÍA: $diaNombre\n";
            $ejs = $dia[$ejsKey] ?? [];
            // Handle bloques > ejercicios nesting
            if (!empty($ejs) && isset($ejs[0]['ejercicios'])) {
                foreach ($ejs as $bloque) {
                    foreach ($bloque['ejercicios'] ?? [] as $e) {
                        $n = $e['nombre'] ?? '?';
                        $s = $e['series'] ?? '?';
                        $r = $e['repeticiones'] ?? $e['duracion'] ?? '?';
                        echo "    - [$n] s=$s r=$r\n";
                    }
                }
            } else {
                foreach ($ejs as $e) {
                    $n = $e['nombre'] ?? '?';
                    $s = $e['series'] ?? '?';
                    $r = $e['repeticiones'] ?? $e['duracion'] ?? '?';
                    echo "    - [$n] s=$s r=$r\n";
                }
            }
        }
    }
}

// ════════════════════════════════════════════════════════
// 4. FUNCIONES HELPER para construir ejercicios
// ════════════════════════════════════════════════════════

function ej(
    string $nombre,
    string $gifUrl,
    int $series,
    string $reps,
    string $equipo = 'Libre',
    string $notas = '',
    string $grupoMuscular = '',
    bool $isCardio = false,
    ?array $variacion = null
): array {
    $e = [
        'nombre'          => $nombre,
        'gif_url'         => $gifUrl,
        'series'          => $series,
        'repeticiones'    => $reps,
        'equipo'          => $equipo,
        'grupo_muscular'  => $grupoMuscular,
        'notas'           => $notas,
    ];
    if ($isCardio) $e['is_cardio'] = true;
    if ($variacion) $e['variacion'] = $variacion;
    return $e;
}

// ════════════════════════════════════════════════════════
// 5. DEFINIR NUEVOS EJERCICIOS POR DÍA
// ════════════════════════════════════════════════════════

// Helper para preservar campos extra de un ejercicio existente
function mergeEj(array $existing, array $overrides): array
{
    return array_merge($existing, $overrides);
}

$changes = 0;

function applyDayChanges(
    array &$dia,
    string $ejsKey,
    string $diaNombre,
    string $gifBase,
    array $gifs,
    int &$changes
): void {
    $ejs = &$dia[$ejsKey];

    // Detectar si hay nesting de bloques
    $hasBloques = !empty($ejs) && isset($ejs[0]['ejercicios']);

    // Flatten para procesamiento, guardar estructura de bloques
    $flat = [];
    if ($hasBloques) {
        foreach ($ejs as &$bloque) {
            foreach ($bloque['ejercicios'] as &$e) {
                $flat[] = &$e;
            }
            unset($e);
        }
        unset($bloque);
    } else {
        foreach ($ejs as &$e) {
            $flat[] = &$e;
        }
        unset($e);
    }

    $diaNombreLower = mb_strtolower(trim($diaNombre));

    switch (true) {
        // ──────────────────────────────────────────────
        case str_contains($diaNombreLower, 'lunes'):
            applyLunes($flat, $dia, $ejsKey, $gifBase, $gifs, $changes, $hasBloques);
            break;
        case str_contains($diaNombreLower, 'martes'):
            applyMartes($flat, $dia, $ejsKey, $gifBase, $gifs, $changes, $hasBloques);
            break;
        case str_contains($diaNombreLower, 'miércoles') || str_contains($diaNombreLower, 'miercoles'):
            applyMiercoles($flat, $dia, $ejsKey, $gifBase, $gifs, $changes, $hasBloques);
            break;
        case str_contains($diaNombreLower, 'jueves'):
            applyJueves($flat, $dia, $ejsKey, $gifBase, $gifs, $changes, $hasBloques);
            break;
        case str_contains($diaNombreLower, 'viernes'):
            applyViernes($flat, $dia, $ejsKey, $gifBase, $gifs, $changes, $hasBloques);
            break;
        case str_contains($diaNombreLower, 'sábado') || str_contains($diaNombreLower, 'sabado'):
            applySabado($flat, $dia, $ejsKey, $gifBase, $gifs, $changes, $hasBloques);
            break;
        default:
            echo "  ⏭  Día no reconocido: $diaNombre\n";
    }
}

// ──────────────────────────────────────────────
// LUNES
// ──────────────────────────────────────────────
function applyLunes(array &$flat, array &$dia, string $ejsKey, string $gifBase, array $gifs, int &$changes, bool $hasBloques): void
{
    echo "  [LUNES]\n";
    foreach ($flat as &$e) {
        $n = $e['nombre'] ?? '';
        // Sentadilla hack → 4 series + rest pause
        if (str_contains(mb_strtolower($n), 'hack') && str_contains(mb_strtolower($n), 'sentadilla')) {
            $e['series']      = 4;
            $e['repeticiones'] = '10-8-8-8+4+4';
            $e['notas']       = 'Progresión: 10-8-8. Última serie: rest pause 8-4-4 (20s entre mini-sets). Glúteos atrás, rodillas en línea con pies.';
            $e['gif_url']     = $gifBase . $gifs['sentadilla_hack'];
            $changes++; echo "    ✅ Sentadilla hack → 4s + rest pause\n";
        }
        // Presa / Prensa de piernas → 4 series 12-10-10-8
        elseif (preg_match('/pres[ao]|prensa/i', $n) && str_contains(mb_strtolower($n), 'pierna')) {
            $e['series']      = 4;
            $e['repeticiones'] = '12-10-10-8';
            $e['notas']       = 'Progresión descendente. Pies a la anchura de hombros, rodillas al pecho en el descenso.';
            $e['gif_url']     = $gifBase . $gifs['prensa_piernas'];
            $changes++; echo "    ✅ Prensa piernas → 4s 12-10-10-8\n";
        }
        // Curl femoral acostado → Sentadilla goblet
        elseif (preg_match('/curl femoral acost/i', $n)) {
            $e['nombre']      = 'Sentadilla goblet con mancuerna';
            $e['gif_url']     = $gifBase . $gifs['goblet'];
            $e['series']      = 3;
            $e['repeticiones'] = '12';
            $e['equipo']      = 'Mancuerna';
            $e['notas']       = 'Última serie: drop set (peso completo → mitad del peso → sin peso, sin descanso entre drops). Core activo, espalda recta.';
            if (isset($e['variacion'])) unset($e['variacion']);
            $changes++; echo "    ✅ Curl femoral acostado → Sentadilla goblet (drop set última serie)\n";
        }
    }
    unset($e);
}

// ──────────────────────────────────────────────
// MARTES
// ──────────────────────────────────────────────
function applyMartes(array &$flat, array &$dia, string $ejsKey, string $gifBase, array $gifs, int &$changes, bool $hasBloques): void
{
    echo "  [MARTES]\n";
    $addedFacepull = false;
    $lastEjIdx = -1;

    foreach ($flat as $idx => &$e) {
        $n  = $e['nombre'] ?? '';
        $nl = mb_strtolower($n);
        // Jalón al pecho (no máquina) → 4 series 12-10-10-8
        if (str_contains($nl, 'jalón') || str_contains($nl, 'jalon')) {
            $e['series']      = 4;
            $e['repeticiones'] = '12-10-10-8';
            $e['notas']       = 'Progresión descendente. Barra baja hasta la barbilla, escápulas contraídas.';
            $e['gif_url']     = $gifBase . $gifs['jalon_pecho'];
            $changes++; echo "    ✅ Jalón al pecho → 4s 12-10-10-8\n";
        }
        // Remo con barra → 4 series 12-10-10-8
        elseif (preg_match('/remo.*(barra|inclinado)/i', $n) || (str_contains($nl, 'remo') && str_contains($nl, 'barra'))) {
            $e['series']      = 4;
            $e['repeticiones'] = '12-10-10-8';
            $e['notas']       = 'Torso a 45°, barra sube al abdomen bajo, retracción escapular completa.';
            $e['gif_url']     = $gifBase . $gifs['remo_barra'];
            $changes++; echo "    ✅ Remo con barra → 4s 12-10-10-8\n";
        }
        if (!str_contains($nl, 'caminadora') && !str_contains($nl, 'cardio')) {
            $lastEjIdx = $idx;
        }
    }
    unset($e);

    // Agregar Facepull antes del cardio
    $facepull = [
        'nombre'         => 'Face pull con cable',
        'gif_url'        => $gifBase . $gifs['facepull'],
        'series'         => 4,
        'repeticiones'   => '12',
        'equipo'         => 'Cable',
        'grupo_muscular' => 'Deltoides posterior / Manguito',
        'notas'          => 'Codos a altura de hombros, manos hacia la cara. Control total en retorno.',
    ];

    if (!$hasBloques) {
        // Insertar antes del cardio (último elemento si es caminadora)
        $lastIdx = count($dia[$ejsKey]) - 1;
        $lastN   = mb_strtolower($dia[$ejsKey][$lastIdx]['nombre'] ?? '');
        if (str_contains($lastN, 'caminadora') || str_contains($lastN, 'cardio')) {
            array_splice($dia[$ejsKey], $lastIdx, 0, [$facepull]);
        } else {
            $dia[$ejsKey][] = $facepull;
        }
    } else {
        // En estructura de bloques, agregar al último bloque antes del cardio
        $lastBloqueIdx = count($dia[$ejsKey]) - 1;
        $dia[$ejsKey][$lastBloqueIdx]['ejercicios'][] = $facepull;
    }
    $changes++; echo "    ✅ Facepull agregado (4×12)\n";
}

// ──────────────────────────────────────────────
// MIÉRCOLES
// ──────────────────────────────────────────────
function applyMiercoles(array &$flat, array &$dia, string $ejsKey, string $gifBase, array $gifs, int &$changes, bool $hasBloques): void
{
    echo "  [MIÉRCOLES]\n";
    foreach ($flat as &$e) {
        $n  = $e['nombre'] ?? '';
        $nl = mb_strtolower($n);

        // Puente de glúteo → Hip Thrust
        if ((str_contains($nl, 'puente') && str_contains($nl, 'glúteo')) || str_contains($nl, 'hip thrust')) {
            $e['nombre']      = 'Hip thrust con barra';
            $e['gif_url']     = $gifBase . $gifs['hip_thrust'];
            $e['series']      = 4;
            $e['repeticiones'] = '12-10-10-8+4+4';
            $e['equipo']      = 'Barra';
            $e['notas']       = 'Progresión: 12-10-10. Última serie: rest pause 8-4-4 (20s entre mini-sets). Cadera sube hasta alinearse con rodillas y hombros.';
            if (isset($e['variacion'])) unset($e['variacion']);
            $changes++; echo "    ✅ Puente de glúteo → Hip thrust 4s rest pause\n";
        }
        // Peso muerto rumano → 4 series 10-10-8-8 (2nd position)
        elseif (str_contains($nl, 'rumano') || str_contains($nl, 'peso muerto')) {
            $e['series']      = 4;
            $e['repeticiones'] = '10-10-8-8';
            $e['notas']       = 'Posición 2: agarre neutro, énfasis en isquiotibiales. Barra cerca del cuerpo, rodillas ligeramente flexionadas.';
            $changes++; echo "    ✅ Peso muerto rumano → 4s 10-10-8-8 (pos. 2)\n";
        }
        // Sentadilla búlgara: actualizar si existe, sino se agrega abajo
        elseif (str_contains($nl, 'búlgara') || str_contains($nl, 'bulgara')) {
            $e['series']      = 4;
            $e['repeticiones'] = '10';
            $e['gif_url']     = $gifBase . $gifs['bulgara'];
            $e['notas']       = 'Pie trasero en banco. Torso ligeramente inclinado, rodilla sin pasar la punta del pie. 10 reps cada pierna.';
            $changes++; echo "    ✅ Sentadilla búlgara → 4×10\n";
        }
    }
    unset($e);

    // Verificar qué ejercicios nuevos hay que agregar
    $nombres = array_map(fn($e) => mb_strtolower($e['nombre'] ?? ''), $hasBloques
        ? array_merge(...array_column($dia[$ejsKey], 'ejercicios'))
        : $dia[$ejsKey]
    );
    $tieneBulgara   = array_filter($nombres, fn($n) => str_contains($n, 'búlgar') || str_contains($n, 'bulgar'));
    $tieneAbduccion = array_filter($nombres, fn($n) => str_contains($n, 'abduc'));
    $tienePatada    = array_filter($nombres, fn($n) => str_contains($n, 'patada'));

    $nuevos = [];

    if (empty($tieneBulgara)) {
        $nuevos[] = [
            'nombre'         => 'Sentadilla búlgara',
            'gif_url'        => $gifBase . $gifs['bulgara'],
            'series'         => 4,
            'repeticiones'   => '10',
            'equipo'         => 'Barra / Mancuernas',
            'grupo_muscular' => 'Cuádriceps / Glúteo',
            'notas'          => 'Pie trasero en banco. 10 reps cada pierna.',
        ];
    }
    if (empty($tieneAbduccion)) {
        $nuevos[] = [
            'nombre'         => 'Abducción de cadera',
            'gif_url'        => $gifBase . $gifs['abduccion'],
            'series'         => 4,
            'repeticiones'   => '20-15',
            'equipo'         => 'Libre / Banda',
            'grupo_muscular' => 'Glúteo medio',
            'notas'          => 'Progresión 20-15. Cadera sin compensar, pelvis neutral.',
        ];
    }
    if (empty($tienePatada)) {
        $nuevos[] = [
            'nombre'         => 'Patada de glúteo',
            'gif_url'        => $gifBase . $gifs['patada_gluteo'],
            'series'         => 4,
            'repeticiones'   => '15-12',
            'equipo'         => 'Cable / Libre',
            'grupo_muscular' => 'Glúteo',
            'notas'          => 'Extensión completa de cadera, glúteo contraído en pico. 15-12 cada pierna.',
        ];
    }

    if (!empty($nuevos)) {
        // Insertar antes del cardio
        if (!$hasBloques) {
            $lastIdx = count($dia[$ejsKey]) - 1;
            $lastN   = mb_strtolower($dia[$ejsKey][$lastIdx]['nombre'] ?? '');
            if (str_contains($lastN, 'caminadora') || str_contains($lastN, 'cardio')) {
                foreach (array_reverse($nuevos) as $nv) {
                    array_splice($dia[$ejsKey], $lastIdx, 0, [$nv]);
                }
            } else {
                foreach ($nuevos as $nv) $dia[$ejsKey][] = $nv;
            }
        } else {
            $lb = count($dia[$ejsKey]) - 1;
            foreach ($nuevos as $nv) $dia[$ejsKey][$lb]['ejercicios'][] = $nv;
        }
        $changes += count($nuevos);
        echo "    ✅ Agregados " . count($nuevos) . " ejercicios nuevos (búlgara / abducción / patada)\n";
    }
}

// ──────────────────────────────────────────────
// JUEVES
// ──────────────────────────────────────────────
function applyJueves(array &$flat, array &$dia, string $ejsKey, string $gifBase, array $gifs, int &$changes, bool $hasBloques): void
{
    echo "  [JUEVES]\n";
    foreach ($flat as &$e) {
        $n  = $e['nombre'] ?? '';
        $nl = mb_strtolower($n);

        // Press hombros mancuernas → 4s 12-10-8-8
        if ((str_contains($nl, 'press') && str_contains($nl, 'hombro') && !str_contains($nl, 'arnold'))) {
            $e['series']      = 4;
            $e['repeticiones'] = '12-10-8-8';
            $e['notas']       = 'Progresión descendente de reps. Codos a 90° en la bajada, extensión completa arriba.';
            $e['gif_url']     = $gifBase . $gifs['press_hombros'];
            $changes++; echo "    ✅ Press hombros → 4s 12-10-8-8\n";
        }
        // Elevación lateral → 4s 15-12
        elseif (str_contains($nl, 'elevaci') && str_contains($nl, 'lateral') && !str_contains($nl, 'posterior')) {
            $e['series']      = 4;
            $e['repeticiones'] = '15-12';
            $e['notas']       = 'Codos ligeramente flexionados. Subir hasta altura de hombros, control en bajada.';
            $e['gif_url']     = $gifBase . $gifs['elev_lateral'];
            $changes++; echo "    ✅ Elevación lateral → 4s 15-12\n";
        }
        // Face pull → Elevaciones posteriores con mancuernas
        elseif (str_contains($nl, 'face') || str_contains($nl, 'facepull')) {
            $e['nombre']      = 'Elevaciones posteriores con mancuernas';
            $e['gif_url']     = $gifBase . $gifs['elev_posteriores'];
            $e['series']      = 4;
            $e['repeticiones'] = '15-12';
            $e['equipo']      = 'Mancuernas';
            $e['notas']       = 'Torso paralelo al suelo o casi, codos ligeramente flexionados. Apertura lateral, deltoides posterior.';
            $changes++; echo "    ✅ Facepull → Elevaciones posteriores 4s 15-12\n";
        }
    }
    unset($e);

    // Verificar ejercicios nuevos a agregar
    $nombres = array_map(fn($e) => mb_strtolower($e['nombre'] ?? ''), $hasBloques
        ? array_merge(...array_column($dia[$ejsKey], 'ejercicios'))
        : $dia[$ejsKey]
    );
    $tieneMenton   = array_filter($nombres, fn($n) => str_contains($n, 'mentón') || str_contains($n, 'menton'));
    $tieneFrontales = array_filter($nombres, fn($n) => str_contains($n, 'frontal'));

    $nuevos = [];
    if (empty($tieneMenton)) {
        $nuevos[] = [
            'nombre'         => 'Remo al mentón con barra',
            'gif_url'        => $gifBase . $gifs['remo_menton'],
            'series'         => 3,
            'repeticiones'   => '12',
            'equipo'         => 'Barra',
            'grupo_muscular' => 'Deltoides / Trapecio',
            'notas'          => 'Agarre cerrado, codos suben por encima de hombros. Movimiento controlado.',
        ];
    }
    if (empty($tieneFrontales)) {
        $nuevos[] = [
            'nombre'         => 'Elevaciones frontales con mancuernas',
            'gif_url'        => $gifBase . $gifs['elev_frontales'],
            'series'         => 3,
            'repeticiones'   => '12',
            'equipo'         => 'Mancuernas',
            'grupo_muscular' => 'Deltoides anterior',
            'notas'          => 'Brazos extendidos, subir hasta altura de hombros. Sin balanceo de torso.',
        ];
    }

    if (!empty($nuevos)) {
        if (!$hasBloques) {
            $lastIdx = count($dia[$ejsKey]) - 1;
            $lastN   = mb_strtolower($dia[$ejsKey][$lastIdx]['nombre'] ?? '');
            if (str_contains($lastN, 'caminadora') || str_contains($lastN, 'cardio')) {
                foreach (array_reverse($nuevos) as $nv) {
                    array_splice($dia[$ejsKey], $lastIdx, 0, [$nv]);
                }
            } else {
                foreach ($nuevos as $nv) $dia[$ejsKey][] = $nv;
            }
        } else {
            $lb = count($dia[$ejsKey]) - 1;
            foreach ($nuevos as $nv) $dia[$ejsKey][$lb]['ejercicios'][] = $nv;
        }
        $changes += count($nuevos);
        echo "    ✅ Agregados: remo al mentón (3×12) + elevaciones frontales (3×12)\n";
    }
}

// ──────────────────────────────────────────────
// VIERNES
// ──────────────────────────────────────────────
function applyViernes(array &$flat, array &$dia, string $ejsKey, string $gifBase, array $gifs, int &$changes, bool $hasBloques): void
{
    echo "  [VIERNES]\n";
    foreach ($flat as &$e) {
        $n  = $e['nombre'] ?? '';
        $nl = mb_strtolower($n);

        // Peso muerto rumano → Peso muerto convencional
        if (str_contains($nl, 'rumano') || (str_contains($nl, 'peso muerto') && !str_contains($nl, 'hip'))) {
            $e['nombre']      = 'Peso muerto convencional con barra';
            $e['gif_url']     = $gifBase . $gifs['muerto_conv'];
            $e['series']      = 4;
            $e['repeticiones'] = '10-10-8-8';
            $e['equipo']      = 'Barra';
            $e['notas']       = 'Espalda neutral, cadera empuja hacia adelante al subir. Barra cerca del cuerpo en todo momento.';
            $changes++; echo "    ✅ Peso muerto rumano → Convencional 4s 10-10-8-8\n";
        }
        // Curl femoral sentado → 4s 15-12-10-10
        elseif (str_contains($nl, 'curl femoral') && str_contains($nl, 'sentado')) {
            $e['series']      = 4;
            $e['repeticiones'] = '15-12-10-10';
            $e['gif_url']     = $gifBase . $gifs['curl_femoral_sent'];
            $e['notas']       = 'Progresión descendente. Control total en excéntrico, no soltarlo.';
            $changes++; echo "    ✅ Curl femoral sentado → 4s 15-12-10-10\n";
        }
        // Curl femoral acostado → 4s 15-12-10-10
        elseif (str_contains($nl, 'curl femoral') && str_contains($nl, 'acostado')) {
            $e['series']      = 4;
            $e['repeticiones'] = '15-12-10-10';
            $e['gif_url']     = $gifBase . $gifs['curl_femoral_acost'];
            $e['notas']       = 'Caderas pegadas al banco. Flexión completa, excéntrico de 2-3 segundos.';
            $changes++; echo "    ✅ Curl femoral acostado → 4s 15-12-10-10\n";
        }
        // Sentadilla sumo con mancuerna → Hiperextensiones en banco
        elseif (str_contains($nl, 'sumo')) {
            $e['nombre']      = 'Hiperextensiones en banco';
            $e['gif_url']     = $gifBase . $gifs['hiperext'];
            $e['series']      = 3;
            $e['repeticiones'] = '12';
            $e['equipo']      = 'Banco romano / GHD';
            $e['notas']       = 'Extensión completa de cadera, glúteo contraído en pico. Sin hiperextender lumbar.';
            if (isset($e['variacion'])) unset($e['variacion']);
            $changes++; echo "    ✅ Sentadilla sumo → Hiperextensiones 3×12\n";
        }
        // Puente de glúteo → 4s 15-12 (2nd position)
        elseif ((str_contains($nl, 'puente') && str_contains($nl, 'glúteo')) || str_contains($nl, 'hip thrust')) {
            $e['series']      = 4;
            $e['repeticiones'] = '15-12';
            $e['notas']       = 'Posición 2: pies más alejados del cuerpo, énfasis en glúteo. Pausa 1s en la cima.';
            $e['gif_url']     = $gifBase . $gifs['hip_thrust'];
            $changes++; echo "    ✅ Puente de glúteo → 4s 15-12 (pos. 2)\n";
        }
    }
    unset($e);

    // Agregar Patada trasera en máquina
    $nombres = array_map(fn($e) => mb_strtolower($e['nombre'] ?? ''), $hasBloques
        ? array_merge(...array_column($dia[$ejsKey], 'ejercicios'))
        : $dia[$ejsKey]
    );
    $tienePatada = array_filter($nombres, fn($n) => str_contains($n, 'patada trasera') || str_contains($n, 'patada'));

    if (empty($tienePatada)) {
        $nuevaPatada = [
            'nombre'         => 'Patada trasera en máquina',
            'gif_url'        => $gifBase . $gifs['patada_trasera'],
            'series'         => 3,
            'repeticiones'   => '20-15',
            'equipo'         => 'Máquina / Cable',
            'grupo_muscular' => 'Glúteo',
            'notas'          => 'Extensión completa. Glúteo contraído 1s en pico. 20-15 reps cada pierna.',
        ];
        if (!$hasBloques) {
            $lastIdx = count($dia[$ejsKey]) - 1;
            $lastN   = mb_strtolower($dia[$ejsKey][$lastIdx]['nombre'] ?? '');
            if (str_contains($lastN, 'caminadora') || str_contains($lastN, 'cardio')) {
                array_splice($dia[$ejsKey], $lastIdx, 0, [$nuevaPatada]);
            } else {
                $dia[$ejsKey][] = $nuevaPatada;
            }
        } else {
            $lb = count($dia[$ejsKey]) - 1;
            $dia[$ejsKey][$lb]['ejercicios'][] = $nuevaPatada;
        }
        $changes++;
        echo "    ✅ Patada trasera en máquina agregada (3×20-15)\n";
    }
}

// ──────────────────────────────────────────────
// SÁBADO
// ──────────────────────────────────────────────
function applySabado(array &$flat, array &$dia, string $ejsKey, string $gifBase, array $gifs, int &$changes, bool $hasBloques): void
{
    echo "  [SÁBADO]\n";
    foreach ($flat as &$e) {
        $n  = $e['nombre'] ?? '';
        $nl = mb_strtolower($n);

        // Todos los ejercicios → 4 series
        if (!isset($e['is_cardio']) && !str_contains($nl, 'caminadora') && !str_contains($nl, 'cardio')) {
            $e['series'] = 4;
        }

        // Press banca inclinado → Press Arnold
        if (str_contains($nl, 'press') && (str_contains($nl, 'banca') || str_contains($nl, 'banco')) && str_contains($nl, 'inclinado')) {
            $e['nombre']  = 'Press Arnold con mancuernas';
            $e['gif_url'] = $gifBase . $gifs['press_arnold'];
            $e['equipo']  = 'Mancuernas';
            $e['notas']   = '4 series. Rotación interna → externa completa. Trabajar rango completo de hombro.';
            $changes++; echo "    ✅ Press banca inclinado → Press Arnold\n";
        }
        // Press hombros con mancuernas → Fondos en banco
        elseif (str_contains($nl, 'press') && str_contains($nl, 'hombro') && !str_contains($nl, 'arnold')) {
            $e['nombre']  = 'Fondos en banco';
            $e['gif_url'] = $gifBase . $gifs['fondos'];
            $e['equipo']  = 'Banco';
            $e['notas']   = '4 series. Codos hacia atrás, no hacia los lados. Baja hasta 90° de flexión de codo.';
            $changes++; echo "    ✅ Press hombros → Fondos en banco\n";
        }
        // Jalón al pecho en máquina → Jalón agarre supino
        elseif ((str_contains($nl, 'jalón') || str_contains($nl, 'jalon')) && str_contains($nl, 'máquina')) {
            $e['nombre']  = 'Jalón al pecho agarre supino';
            $e['gif_url'] = $gifBase . $gifs['jalon_supino'];
            $e['equipo']  = 'Polea';
            $e['notas']   = '4 series. Agarre supino (palmas hacia ti), barra baja hasta barbilla. Mayor activación bíceps.';
            $changes++; echo "    ✅ Jalón en máquina → Jalón agarre supino\n";
        }
        // Remo con mancuerna a una mano → Remo con mancuernas (dos brazos)
        elseif (str_contains($nl, 'remo') && (str_contains($nl, 'una mano') || str_contains($nl, 'mancuerna'))) {
            $e['nombre']  = 'Remo con mancuernas inclinado';
            $e['gif_url'] = $gifBase . $gifs['remo_mancuernas'];
            $e['equipo']  = 'Mancuernas';
            $e['notas']   = '4 series. Torso paralelo al suelo apoyado en banco. Ambos brazos simultáneos. Retracción escapular.';
            $changes++; echo "    ✅ Remo una mano → Remo mancuernas (2 brazos)\n";
        }
    }
    unset($e);
}

// ════════════════════════════════════════════════════════
// 6. APLICAR CAMBIOS A TODAS LAS SEMANAS
// ════════════════════════════════════════════════════════
echo "\n=== APLICANDO CAMBIOS ===\n";

// Detectar estructura del plan
$semanasKey = null;
foreach (['semanas', 'weeks', 'dias'] as $sk) {
    if (isset($plan[$sk])) { $semanasKey = $sk; break; }
}

if ($semanasKey === 'dias') {
    // Plan de una sola "semana" plana
    echo "Estructura: plan plano (una semana)\n";
    foreach (['ejercicios', 'exercises', 'bloques'] as $ek) {
        if (isset($plan['dias'][0][$ek])) { $ejsKey = $ek; break; }
    }
    if (!isset($ejsKey)) $ejsKey = 'ejercicios';

    foreach ($plan['dias'] as &$dia) {
        $diaN = $dia['nombre'] ?? $dia['dia'] ?? $dia['name'] ?? '?';
        echo "\nDía: $diaN\n";
        $hasBloques = !empty($dia[$ejsKey]) && isset($dia[$ejsKey][0]['ejercicios']);
        $flat = [];
        if ($hasBloques) {
            foreach ($dia[$ejsKey] as &$bloque) {
                foreach ($bloque['ejercicios'] as &$e) $flat[] = &$e;
                unset($e);
            }
            unset($bloque);
        } else {
            foreach ($dia[$ejsKey] as &$e) $flat[] = &$e;
            unset($e);
        }
        applyDayChanges($dia, $ejsKey, $diaN, $gifBase, $gifs, $changes);
    }
    unset($dia);
} elseif ($semanasKey) {
    // Plan multi-semana
    echo "Estructura: " . count($plan[$semanasKey]) . " semanas\n";
    foreach ($plan[$semanasKey] as $sIdx => &$semana) {
        echo "\n--- Semana " . ($sIdx + 1) . " ---\n";

        $diasKeyS = null;
        foreach (['dias', 'days', 'sesiones'] as $dk) {
            if (isset($semana[$dk])) { $diasKeyS = $dk; break; }
        }
        if (!$diasKeyS) { echo "  ⚠️ No se encontró clave de días en semana $sIdx\n"; continue; }

        $ejsKeyS = null;
        foreach (['ejercicios', 'exercises', 'bloques'] as $ek) {
            if (isset($semana[$diasKeyS][0][$ek])) { $ejsKeyS = $ek; break; }
        }
        if (!$ejsKeyS) $ejsKeyS = 'ejercicios';

        foreach ($semana[$diasKeyS] as &$dia) {
            $diaN = $dia['nombre'] ?? $dia['dia'] ?? $dia['name'] ?? '?';
            echo "\nDía: $diaN\n";
            applyDayChanges($dia, $ejsKeyS, $diaN, $gifBase, $gifs, $changes);
        }
        unset($dia);
    }
    unset($semana);
} else {
    echo "❌ No se reconoció la estructura del plan. Keys: " . implode(', ', array_keys($plan)) . "\n";
    exit(1);
}

// ════════════════════════════════════════════════════════
// 7. GUARDAR EN BD
// ════════════════════════════════════════════════════════
echo "\n=== RESUMEN ===\n";
echo "Total cambios aplicados: $changes\n";

if ($changes > 0) {
    $json = json_encode($plan, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $pdo->prepare('UPDATE assigned_plans SET content=? WHERE id=183')->execute([$json]);
    echo "✅ Plan 183 actualizado en BD\n";

    file_put_contents(
        '/code/storage/app/plan183_daniela_reestructurado.json',
        json_encode($plan, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
    );
    echo "✅ Copia en /code/storage/app/plan183_daniela_reestructurado.json\n";
} else {
    echo "⚠️  Sin cambios — revisar nombres de días y ejercicios\n";
}

echo "\n=== DONE ===\n";
