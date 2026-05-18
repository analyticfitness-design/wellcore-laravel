<?php
/**
 * add_escaladora_lizeth.php
 *
 * Modificación quirúrgica del plan de entrenamiento ACTIVO de Lizeth Tatiana
 * Chávez (client_id=98). Agrega ejercicio "Escaladora" 20-25 min al FINAL del
 * array ejercicios[] en cada día Lunes-Viernes de TODAS las semanas.
 *
 * - NO toca Sábado.
 * - NO reconstruye el plan: solo agrega un ejercicio al final.
 * - Idempotente: si la escaladora ya existe al final de un día, NO la duplica.
 * - UPDATE preserva valid_from, expires_at, y el resto del plan intacto.
 *
 * Ejecutar en container EasyPanel:
 *   php /code/bootstrap/add_escaladora_lizeth.php
 *
 * Dry-run local (lee plan desde archivo si se pasa via PLAN_FROM_FILE):
 *   php -d display_errors=1 -r "define('DRY_RUN', true); require 'bootstrap/add_escaladora_lizeth.php';"
 */

$clientId = 98;
$DIAS_AFECTADOS = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];

$ejercicioEscaladora = [
    'nombre'       => 'Escaladora',
    'gif_url'      => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/escaladora.gif',
    'series'       => 1,
    'repeticiones' => '20-25 min',
    'descanso'     => '-',
    'rir'          => '—',
    'notas'        => 'Cardio de cierre. Ritmo constante moderado donde puedas mantener el ritmo (zona 2: podés hablar pero no cantar). Resistencia que te permita mantener la intensidad. Si te falta el aire, bajás un escalón.',
    'is_cardio'    => true,
    'cardio_type'  => 'continuous_low',
    'intensidad'   => [
        'zona_fc'              => 2,
        'rpe'                  => '4-5',
        'descripcion_cliente'  => 'Ritmo donde podés hablar pero no cantar.',
    ],
    'duracion_min' => 22,
    'momento'      => 'Post pesas',
    'variacion'    => [
        'nombre'  => 'Caminadora inclinada (12% inclinación, 5.5 km/h)',
        'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/caminadora-inclinada.gif',
    ],
];

if (!(defined('DRY_RUN') && DRY_RUN)) {
    $pdo = new PDO(
        'mysql:host=wellcorefitness_wellcorefitness-mysql;dbname=wellcorefitness;charset=utf8mb4',
        'wellcorefitness',
        'fYCVgn4XZ7twq34',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $stmt = $pdo->prepare("SELECT id, valid_from, expires_at, content FROM assigned_plans WHERE client_id=? AND plan_type='entrenamiento' AND active=1 ORDER BY id DESC LIMIT 1");
    $stmt->execute([$clientId]);
    $row = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$row) {
        die("✗ No existe plan de entrenamiento activo para client_id=$clientId. Abortando.\n");
    }

    $planId    = $row->id;
    $validFrom = $row->valid_from;
    $expiresAt = $row->expires_at;
    $contentJson = $row->content;
} else {
    // Dry-run: simular plan vacío
    $planId    = 'DRY_RUN';
    $validFrom = '2026-05-18';
    $expiresAt = '2026-06-15';
    $contentJson = json_encode([
        'plan_type' => 'entrenamiento',
        'titulo'    => 'Mock Lizeth',
        'semanas'   => [
            ['numero' => 1, 'fase' => 'Adaptación', 'dias' => [
                ['dia_semana' => 'Lunes',     'ejercicios' => [['nombre' => 'Hipthrust', 'series' => 3]]],
                ['dia_semana' => 'Martes',    'ejercicios' => [['nombre' => 'Press hombro', 'series' => 4]]],
                ['dia_semana' => 'Miércoles', 'ejercicios' => [['nombre' => 'Sentadilla', 'series' => 4]]],
                ['dia_semana' => 'Jueves',    'ejercicios' => [['nombre' => 'Jalón', 'series' => 4]]],
                ['dia_semana' => 'Viernes',   'ejercicios' => [['nombre' => 'PMR', 'series' => 4]]],
                ['dia_semana' => 'Sábado',    'ejercicios' => [['nombre' => 'HIIT', 'series' => 1]]],
            ]],
        ],
    ]);
}

$content = json_decode($contentJson, true);
if ($content === null) {
    die("✗ JSON del plan no parseable: " . json_last_error_msg() . "\n");
}

if (empty($content['semanas']) || !is_array($content['semanas'])) {
    die("✗ El plan no tiene array 'semanas'. Abortando.\n");
}

// ─── INSERCIÓN QUIRÚRGICA ────────────────────────────────────────────────────
$adds = 0;
$skips = 0;
$semCount = count($content['semanas']);

foreach ($content['semanas'] as $semIdx => &$semana) {
    if (empty($semana['dias']) || !is_array($semana['dias'])) continue;

    foreach ($semana['dias'] as &$dia) {
        $diaNombre = $dia['dia_semana'] ?? $dia['nombre'] ?? '';

        // Match flexible: el nombre puede ser "Lunes — Glúteo + Cardio" o solo "Lunes"
        $diaPlano = '';
        foreach ($DIAS_AFECTADOS as $d) {
            if (stripos($diaNombre, $d) === 0 || stripos($diaNombre, $d . ' ') !== false || stripos($diaNombre, $d . '—') !== false || stripos($diaNombre, $d . ' —') !== false) {
                $diaPlano = $d;
                break;
            }
        }
        // Caso: $dia['dia_semana'] = "Lunes" exacto
        if (!$diaPlano && in_array($diaNombre, $DIAS_AFECTADOS, true)) {
            $diaPlano = $diaNombre;
        }

        if (!$diaPlano) continue; // Sábado u otro, skip
        if (empty($dia['ejercicios']) || !is_array($dia['ejercicios'])) {
            $dia['ejercicios'] = [];
        }

        // Idempotencia: skip si el último ejercicio ya es escaladora
        $ultimo = end($dia['ejercicios']);
        if ($ultimo && stripos($ultimo['nombre'] ?? '', 'escaladora') === 0) {
            $skips++;
            continue;
        }

        $dia['ejercicios'][] = $ejercicioEscaladora;
        $adds++;
    }
    unset($dia);
}
unset($semana);

// ─── ENCODE ──────────────────────────────────────────────────────────────────
$newJson = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if ($newJson === false) die("✗ ERROR encoding JSON: " . json_last_error_msg() . "\n");

echo "─── RESUMEN ──────────────────────────────────────────\n";
echo "Cliente:       Lizeth Tatiana Chávez (client_id=$clientId)\n";
echo "Plan ID:       $planId\n";
echo "Valid from:    $validFrom\n";
echo "Expires at:    $expiresAt\n";
echo "Semanas:       $semCount\n";
echo "Escaladoras añadidas: $adds (esperado: " . ($semCount * 5) . ")\n";
echo "Skips (ya existían): $skips\n";
echo "Tamaño JSON antes: " . strlen($contentJson) . " B\n";
echo "Tamaño JSON después: " . strlen($newJson) . " B\n";
echo "Delta: +" . (strlen($newJson) - strlen($contentJson)) . " B\n";
echo "──────────────────────────────────────────────────────\n";

if (defined('DRY_RUN') && DRY_RUN) {
    echo "✓ DRY_RUN OK. JSON re-parseable: " . (json_decode($newJson) !== null ? 'sí' : 'NO') . "\n";
    return;
}

if ($adds === 0 && $skips === 0) {
    echo "⚠ No se encontró ningún día Lun-Vie para modificar. Abortando UPDATE.\n";
    exit(1);
}

try {
    $stmt = $pdo->prepare("UPDATE assigned_plans SET content = :content WHERE id = :id");
    $stmt->execute(['content' => $newJson, 'id' => $planId]);
    echo "✓ UPDATE OK — assigned_plans.id=$planId actualizado ({$stmt->rowCount()} fila).\n";

    // Invalidar caches del cliente
    $cacheKeys = [
        "client_plan_v3_{$clientId}",
        "wp:plan:{$clientId}",
        "wp:weekdays:{$clientId}",
        "dashboard:{$clientId}",
    ];
    if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
        require_once __DIR__ . '/../vendor/autoload.php';
        $app = require_once __DIR__ . '/../bootstrap/app.php';
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();
        foreach ($cacheKeys as $k) {
            \Illuminate\Support\Facades\Cache::forget($k);
        }
        echo "✓ Caches invalidadas: " . implode(', ', $cacheKeys) . "\n";
    }

    echo "\n✓ Escaladora añadida al final de Lun-Vie en todas las semanas del plan de Lizeth.\n";

} catch (Throwable $e) {
    fwrite(STDERR, "✗ ERROR: " . $e->getMessage() . "\n");
    exit(1);
}
