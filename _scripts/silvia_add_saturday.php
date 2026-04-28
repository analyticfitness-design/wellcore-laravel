<?php
/**
 * Agrega un día sábado full body a las 4 semanas del plan de entrenamiento
 * de Silvia Mesa (client_id 66, plan 115). Idempotente: si ya hay sábado, no
 * lo duplica. Mantiene resto del plan exactamente igual.
 *
 * 6 ejercicios full body con campo "variacion" (formato del plan existente).
 * GIFs del banco oficial WellCore en
 * raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AssignedPlan;
use Illuminate\Support\Facades\Cache;

$GIF_BASE = 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/';

$ejercicios = [
    [
        'nombre' => 'Sentadilla con Mancuernas',
        'series' => 4,
        'repeticiones' => '10-12',
        'descanso' => '90s',
        'rir' => '2',
        'gif_url' => $GIF_BASE . 'sentadilla-con-mancuernas.gif',
        'notas' => 'Pies a ancho de hombros, mancuernas a los costados. Baja hasta paralelo manteniendo torso erguido. Ideal para apertura del bloque full body sin cargar la columna.',
        'variacion' => [
            'nombre' => 'Sentadilla Goblet',
            'gif_url' => $GIF_BASE . 'sentadilla-goblet.gif',
        ],
    ],
    [
        'nombre' => 'Peso Muerto Rumano con Mancuerna',
        'series' => 4,
        'repeticiones' => '10-12',
        'descanso' => '90s',
        'rir' => '2',
        'gif_url' => $GIF_BASE . 'peso-muerto-rumano-con-mancuerna.gif',
        'notas' => 'Empuja caderas hacia atrás. Espalda neutra, mancuernas pegadas a la pierna. Siente el estiramiento en isquios al bajar y aprieta glúteo al subir.',
        'variacion' => [
            'nombre' => 'Puente de Glúteo con Barra',
            'gif_url' => $GIF_BASE . 'puente-de-gluteo-con-barra.gif',
        ],
    ],
    [
        'nombre' => 'Press de Banca con Mancuernas',
        'series' => 3,
        'repeticiones' => '10-12',
        'descanso' => '75s',
        'rir' => '2',
        'gif_url' => $GIF_BASE . 'press-de-banca-con-mancuernas.gif',
        'notas' => 'Codos a 45 grados del torso. Baja controlado en 2 segundos y empuja explosivo. Mantén las escápulas pegadas al banco durante todo el movimiento.',
        'variacion' => [
            'nombre' => 'Press de Banca Inclinado con Mancuerna',
            'gif_url' => $GIF_BASE . 'press-de-banca-inclinado-con-mancuerna.gif',
        ],
    ],
    [
        'nombre' => 'Remo con Mancuerna a una Mano',
        'series' => 3,
        'repeticiones' => '10-12 x lado',
        'descanso' => '75s',
        'rir' => '2',
        'gif_url' => $GIF_BASE . 'remo-con-mancuerna-a-una-mano.gif',
        'notas' => 'Apóyate en banco. Tira con el codo hacia atrás llevando la mancuerna a la cadera, no al pecho. Aprieta dorsal en la contracción 1 segundo.',
        'variacion' => [
            'nombre' => 'Remo Sentado en Máquina',
            'gif_url' => $GIF_BASE . 'remo-sentado-en-maquina.gif',
        ],
    ],
    [
        'nombre' => 'Press Militar con Mancuerna',
        'series' => 3,
        'repeticiones' => '10-12',
        'descanso' => '75s',
        'rir' => '2',
        'gif_url' => $GIF_BASE . 'press-militar-con-mancuerna.gif',
        'notas' => 'Sentada con respaldo o de pie con core firme. Empuja arriba sin bloquear codos. No arquees lumbar. Baja a la altura del oído.',
        'variacion' => [
            'nombre' => 'Press Arnold con Mancuerna',
            'gif_url' => $GIF_BASE . 'press-arnold-con-mancuerna.gif',
        ],
    ],
    [
        'nombre' => 'Plancha Abdominal',
        'series' => 3,
        'repeticiones' => '30-45 seg',
        'descanso' => '60s',
        'rir' => '0',
        'gif_url' => $GIF_BASE . 'plancha-abdominal.gif',
        'notas' => 'Caderas neutras, no las levantes ni las dejes caer. Aprieta abdomen y glúteos. Respira de forma continua. Cierre del bloque para core.',
        'variacion' => [
            'nombre' => 'Plancha Lateral',
            'gif_url' => $GIF_BASE . 'plancha-lateral.gif',
        ],
    ],
];

$saturdayDay = [
    'dia' => 6,
    'nombre' => 'Sábado — Full Body Refuerzo',
    'calentamiento' => '8 min movilidad articular general (rotaciones de hombro, cadera y tobillo) + 1 serie liviana de sentadilla y press al 50% del peso de trabajo',
    'ejercicios' => $ejercicios,
];

$plan = AssignedPlan::find(115);
if (!$plan) { fwrite(STDERR, "Plan 115 NOT FOUND\n"); exit(1); }

$content = $plan->content;
$backupPath = '/tmp/silvia_plan_115_backup_' . time() . '.json';
file_put_contents($backupPath, json_encode($content, JSON_UNESCAPED_UNICODE));

$report = ['plan_id' => 115, 'backup' => $backupPath, 'weeks' => []];
$weeksAffected = 0;

foreach ($content['semanas'] as $i => $week) {
    $hasSat = false;
    foreach (($week['dias'] ?? []) as $d) {
        $name = mb_strtolower($d['nombre'] ?? '');
        if (str_contains($name, 'sábado') || str_contains($name, 'sabado')) {
            $hasSat = true; break;
        }
    }

    $before = count($week['dias'] ?? []);

    if (!$hasSat) {
        $content['semanas'][$i]['dias'][] = $saturdayDay;
        $weeksAffected++;
        $action = 'added';
    } else {
        $action = 'skipped (already had sabado)';
    }

    $after = count($content['semanas'][$i]['dias']);
    $report['weeks'][] = [
        'week' => $i + 1,
        'days_before' => $before,
        'days_after' => $after,
        'action' => $action,
    ];
}

if ((int) ($content['frecuencia_dias'] ?? 0) === 5 && $weeksAffected > 0) {
    $content['frecuencia_dias'] = 6;
    $report['frecuencia_dias'] = '5 -> 6';
}

$plan->content = $content;
$plan->save();

Cache::forget('wp:plan:66');

$report['weeks_affected'] = $weeksAffected;
$report['updated_at'] = (string) $plan->updated_at;

echo "=== SILVIA SABADO ADD ===\n";
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
