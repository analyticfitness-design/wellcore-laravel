<?php
/**
 * update_lizeth_plan_2026-05-16.php
 *
 * Modificación del plan de entrenamiento de Lizeth Chávez (assigned_plan_id=188)
 * según ajustes de Daniel — 2026-05-16.
 *
 * Aplica los cambios día por día a TODAS las 4 semanas (las prescripciones de
 * series×reps especificadas sobrescriben la periodización progresiva por
 * decisión explícita del coach). Sábado HIIT (día 6) intacto.
 *
 * Idempotente: lee el JSON actual, aplica modificaciones por nombre de ejercicio.
 * Si un ejercicio ya fue reemplazado en una corrida previa, el lookup por
 * nombre fallaría y el día se deja como está → seguro re-ejecutar.
 *
 * Ejecutar en container:
 *   php /code/bootstrap/update_lizeth_plan_2026-05-16.php
 */

$pdo = new PDO(
    'mysql:host=wellcorefitness_wellcorefitness-mysql;dbname=wellcorefitness;charset=utf8mb4',
    'wellcorefitness',
    'fYCVgn4XZ7twq34',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$gifBase = 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/';
$g = fn(string $a): string => $gifBase . $a . '.gif';

// ─── Definición de modificaciones por día ────────────────────────────────────
// Tipos de operación por ejercicio:
//   'replace':  { match, new_name, new_gif_alias, new_series, new_reps, new_variacion? }
//   'update':   { match, new_series, new_reps }     ← solo cambia series/reps
//   'remove':   { match }                            ← elimina del array
//   'prepend':  { new_name, new_gif_alias, new_series, new_reps, new_variacion? }
//   'append':   idem prepend pero al final

$mods = [
    'Lunes' => [
        // Hip thrust con barra → 4×10-12
        ['op' => 'update', 'match' => 'Hip thrust con barra', 'series' => 4, 'reps' => '10-12'],
        // Peso muerto rumano con barra → 4×10-12
        ['op' => 'update', 'match' => 'Peso muerto rumano con barra', 'series' => 4, 'reps' => '10-12'],
        // Sentadilla búlgara con mancuerna → 4×10-12
        ['op' => 'update', 'match' => 'Sentadilla búlgara con mancuerna', 'series' => 4, 'reps' => '10-12'],
        // Patada de glúteo en máquina → reemplazar por Patada de glúteo en polea (3×12-15)
        ['op' => 'replace', 'match' => 'Patada de glúteo en máquina',
         'new_name' => 'Patada de glúteo en polea',
         'new_gif' => $g('patada-trasera-en-polea'),
         'new_series' => 3, 'new_reps' => '12-15',
         'new_notas' => 'Apóyate al frente. Patada controlada hacia atrás, aprieta glúteo arriba 1 seg.',
         'new_var_name' => 'Patada trasera en máquina',
         'new_var_gif' => $g('patada-trasera-en-maquina')],
        // Zancada curtsy con mancuerna → reemplazar por Abducción sentada en máquina (3×12-15)
        ['op' => 'replace', 'match' => 'Zancada curtsy con mancuerna',
         'new_name' => 'Abducción sentada en máquina (glúteo medio)',
         'new_gif' => $g('abduccion-de-cadera-sentado-en-maquina'),
         'new_series' => 3, 'new_reps' => '12-15',
         'new_notas' => 'Sentada con espalda apoyada. Abre las piernas con rango completo, aprieta glúteo medio 1 seg.',
         'new_var_name' => 'Abducción de cadera de pie en máquina',
         'new_var_gif' => $g('abduccion-de-cadera-de-pie-en-maquina')],
    ],

    'Martes' => [
        // Press de hombro con mancuerna → 4×10-12
        ['op' => 'update', 'match' => 'Press de hombro con mancuerna', 'series' => 4, 'reps' => '10-12'],
        // Eliminar Press francés con barra EZ
        ['op' => 'remove', 'match' => 'Press francés con barra EZ'],
        // El resto a 4×12-15
        ['op' => 'update', 'match' => 'Elevación lateral con mancuerna', 'series' => 4, 'reps' => '12-15'],
        ['op' => 'update', 'match' => 'Elevación posterior con mancuerna', 'series' => 4, 'reps' => '12-15'],
        ['op' => 'update', 'match' => 'Extensión de tríceps en polea con cuerda', 'series' => 4, 'reps' => '12-15'],
        ['op' => 'update', 'match' => 'Patada de tríceps con mancuerna', 'series' => 4, 'reps' => '12-15'],
    ],

    'Miércoles' => [
        // Sentadilla con barra → 4×10-12
        ['op' => 'update', 'match' => 'Sentadilla con barra', 'series' => 4, 'reps' => '10-12'],
        // Prensa de piernas (postura abierta) → 4×10-12
        ['op' => 'update', 'match' => 'Prensa de piernas (postura abierta)', 'series' => 4, 'reps' => '10-12'],
        // Extensión de piernas en máquina → 4×12-15
        ['op' => 'update', 'match' => 'Extensión de piernas en máquina', 'series' => 4, 'reps' => '12-15'],
        // Elevación de talones de pie en máquina → reemplazar por Elevación de talones con mancuerna (4×15)
        ['op' => 'replace', 'match' => 'Elevación de talones de pie en máquina',
         'new_name' => 'Elevación de talones con mancuerna',
         'new_gif' => $g('elevacion-de-talones-con-mancuerna'),
         'new_series' => 4, 'new_reps' => '15',
         'new_notas' => 'De pie con mancuerna en una mano. Rango completo: baja hasta estirar el gemelo, sube hasta la punta del pie. Aguanta arriba 1 seg.',
         'new_var_name' => 'Elevación de talones en máquina',
         'new_var_gif' => $g('elevacion-de-talones-en-maquina')],
    ],

    'Jueves' => [
        // Jalón al pecho en máquina → 4×10-12
        ['op' => 'update', 'match' => 'Jalón al pecho en máquina', 'series' => 4, 'reps' => '10-12'],
        // Remo sentado en máquina → reemplazar por Remo sentado con V-barra (4×12-15)
        ['op' => 'replace', 'match' => 'Remo sentado en máquina',
         'new_name' => 'Remo sentado con V-barra',
         'new_gif' => $g('remo-sentado-con-v-barra-sentado'),
         'new_series' => 4, 'new_reps' => '12-15',
         'new_notas' => 'Sentada, pies firmes en el reposapiés. Jala la V-barra al abdomen, codos cerca del cuerpo. Aprieta omóplatos 1 seg.',
         'new_var_name' => 'Remo en polea sentado',
         'new_var_gif' => $g('remo-en-polea-sentado')],
        // Remo a una mano con mancuerna → reemplazar por Facepull (4×12-15)
        ['op' => 'replace', 'match' => 'Remo a una mano con mancuerna',
         'new_name' => 'Facepull en polea',
         'new_gif' => $g('facepull-en-polea'),
         'new_series' => 4, 'new_reps' => '12-15',
         'new_notas' => 'Polea a altura de la cara. Jala la cuerda hacia la frente con codos altos. Trabaja deltoides posterior y romboides.',
         'new_var_name' => 'Apertura posterior sentada en máquina',
         'new_var_gif' => $g('apertura-posteriores-sentado-en-maquina')],
        // Eliminar Pullover con mancuerna
        ['op' => 'remove', 'match' => 'Pullover con mancuerna'],
        // Curl predicador en máquina → reemplazar por Curl bíceps en polea (4×12-15)
        ['op' => 'replace', 'match' => 'Curl predicador en máquina',
         'new_name' => 'Curl de bíceps en polea',
         'new_gif' => $g('curl-biceps-en-polea'),
         'new_series' => 4, 'new_reps' => '12-15',
         'new_notas' => 'Polea baja, agarre con barra recta. Codos pegados al cuerpo. Sube con bíceps, baja en 2 seg.',
         'new_var_name' => 'Curl de bíceps en polea agarre cerrado',
         'new_var_gif' => $g('curl-biceps-en-polea-agarre-cerrado')],
        // Curl de bíceps con mancuerna → 4×12-15
        ['op' => 'update', 'match' => 'Curl de bíceps con mancuerna', 'series' => 4, 'reps' => '12-15'],
        // Eliminar Curl martillo con mancuerna
        ['op' => 'remove', 'match' => 'Curl martillo con mancuerna'],
    ],

    'Viernes' => [
        // Reemplazar Peso muerto pierna rígida → Peso muerto con mancuernas + PREPEND (primero)
        ['op' => 'remove', 'match' => 'Peso muerto pierna rígida con mancuerna'],
        ['op' => 'prepend',
         'new_name' => 'Peso muerto con mancuernas',
         'new_gif' => $g('peso-muerto-con-mancuernas'),
         'new_series' => 4, 'new_reps' => '10-12',
         'new_notas' => 'Mancuernas pegadas a las piernas. Rodillas semiflexionadas, espalda neutra. Baja hasta sentir estiramiento en isquios, sube apretando glúteo.',
         'new_var_name' => 'Peso muerto rumano con mancuerna',
         'new_var_gif' => $g('peso-muerto-rumano-con-mancuerna')],
        // Hip thrust a una pierna → 4×10-12 (debería quedar 2do tras prepend)
        ['op' => 'update', 'match' => 'Hip thrust a una pierna con barra', 'series' => 4, 'reps' => '10-12'],
        // Curl femoral acostado en máquina → reemplazar por Curl femoral sentado en máquina (4×12-15)
        ['op' => 'replace', 'match' => 'Curl femoral acostado en máquina',
         'new_name' => 'Curl femoral sentado en máquina',
         'new_gif' => $g('curl-femoral-sentado'),
         'new_series' => 4, 'new_reps' => '12-15',
         'new_notas' => 'Sentada, talones detrás de la almohadilla. Lleva los talones hacia el glúteo, aprieta isquios 1 seg.',
         'new_var_name' => 'Curl femoral acostado en máquina',
         'new_var_gif' => $g('curl-femoral-acostado-en-maquina')],
        // Curl femoral arrodillado → reemplazar por Curl femoral en polea (3×12-15)
        ['op' => 'replace', 'match' => 'Curl femoral arrodillado',
         'new_name' => 'Curl femoral en polea',
         'new_gif' => $g('curl-femora-en-polea'),
         'new_series' => 3, 'new_reps' => '12-15',
         'new_notas' => 'Tobillera en polea baja. De pie o acostada, lleva el talón al glúteo. Activación unilateral del isquio.',
         'new_var_name' => 'Curl femoral arrodillado',
         'new_var_gif' => $g('curl-femoral-arrodillado-en-maquina')],
        // Patada lateral en polea (glúteo medio) → 3×12-15
        ['op' => 'update', 'match' => 'Patada lateral en polea (glúteo medio)', 'series' => 3, 'reps' => '12-15'],
        // Zancada inversa con mancuernas → 3×12-15
        ['op' => 'update', 'match' => 'Zancada inversa con mancuernas', 'series' => 3, 'reps' => '12-15'],
        // Elevación de talones de pie en máquina → reemplazar por Elevación de talones con mancuerna (4×15)
        ['op' => 'replace', 'match' => 'Elevación de talones de pie en máquina',
         'new_name' => 'Elevación de talones con mancuerna',
         'new_gif' => $g('elevacion-de-talones-con-mancuerna'),
         'new_series' => 4, 'new_reps' => '15',
         'new_notas' => 'De pie con mancuerna en una mano. Rango completo: baja hasta estirar el gemelo, sube hasta la punta del pie. Aguanta arriba 1 seg.',
         'new_var_name' => 'Elevación de talones en máquina',
         'new_var_gif' => $g('elevacion-de-talones-en-maquina')],
    ],
];

// ─── Helpers ───────────────────────────────────────────────────────────────

function findExerciseIdx(array $ejercicios, string $matchName): int
{
    foreach ($ejercicios as $i => $ej) {
        if (isset($ej['nombre']) && trim($ej['nombre']) === trim($matchName)) {
            return $i;
        }
    }
    return -1;
}

function applyMod(array $ej, array $mod): array
{
    if (!empty($mod['series'])) $ej['series'] = $mod['series'];
    if (!empty($mod['reps']))   $ej['repeticiones'] = $mod['reps'];
    return $ej;
}

function buildExerciseFromMod(array $mod): array
{
    $ej = [
        'nombre'        => $mod['new_name'],
        'gif_url'       => $mod['new_gif'],
        'series'        => $mod['new_series'],
        'repeticiones'  => $mod['new_reps'],
        'descanso'      => '75 seg',
        'rir'           => '2',
        'notas'         => $mod['new_notas'] ?? '',
    ];
    if (!empty($mod['new_var_name']) && !empty($mod['new_var_gif'])) {
        $ej['variacion'] = [
            'nombre'  => $mod['new_var_name'],
            'gif_url' => $mod['new_var_gif'],
        ];
    }
    return $ej;
}

// ─── Main ───────────────────────────────────────────────────────────────────

$assignedPlanId = 188;
$clientId = 98;

$row = $pdo->prepare('SELECT content FROM assigned_plans WHERE id=?');
$row->execute([$assignedPlanId]);
$record = $row->fetch(PDO::FETCH_ASSOC);
if (!$record) {
    fwrite(STDERR, "ERROR: assigned_plan $assignedPlanId not found\n");
    exit(1);
}

$content = json_decode($record['content'], true);
if (!is_array($content)) {
    fwrite(STDERR, "ERROR: content is not valid JSON\n");
    exit(1);
}

$log = [];

// Aplicar mods a cada semana.
// IMPORTANT: el `??` produciría una expresion temporal y romperia las referencias.
// Usamos el array directo y validamos con isset antes del foreach.
if (!isset($content['semanas']) || !is_array($content['semanas'])) {
    fwrite(STDERR, "ERROR: content sin semanas[]\n");
    exit(1);
}
foreach ($content['semanas'] as $iSem => &$sem) {
    if (!isset($sem['dias']) || !is_array($sem['dias'])) continue;
    foreach ($sem['dias'] as $iDia => &$dia) {
        $diaNombre = $dia['dia_semana'] ?? '';
        if (!isset($mods[$diaNombre])) continue;

        $ejs = $dia['ejercicios'] ?? [];

        foreach ($mods[$diaNombre] as $mod) {
            switch ($mod['op']) {
                case 'update':
                    $idx = findExerciseIdx($ejs, $mod['match']);
                    if ($idx >= 0) {
                        $ejs[$idx] = applyMod($ejs[$idx], $mod);
                        $log[] = "Sem".($iSem+1)." $diaNombre: update '$mod[match]' → {$mod['series']}×{$mod['reps']}";
                    }
                    break;
                case 'remove':
                    $idx = findExerciseIdx($ejs, $mod['match']);
                    if ($idx >= 0) {
                        array_splice($ejs, $idx, 1);
                        $log[] = "Sem".($iSem+1)." $diaNombre: removed '$mod[match]'";
                    }
                    break;
                case 'replace':
                    $idx = findExerciseIdx($ejs, $mod['match']);
                    if ($idx >= 0) {
                        $ejs[$idx] = buildExerciseFromMod($mod);
                        $log[] = "Sem".($iSem+1)." $diaNombre: replaced '$mod[match]' → '{$mod['new_name']}' ({$mod['new_series']}×{$mod['new_reps']})";
                    }
                    break;
                case 'prepend':
                    array_unshift($ejs, buildExerciseFromMod($mod));
                    $log[] = "Sem".($iSem+1)." $diaNombre: prepended '{$mod['new_name']}'";
                    break;
                case 'append':
                    $ejs[] = buildExerciseFromMod($mod);
                    $log[] = "Sem".($iSem+1)." $diaNombre: appended '{$mod['new_name']}'";
                    break;
            }
        }

        $dia['ejercicios'] = array_values($ejs);
    }
    unset($dia);
}
unset($sem);

// ─── UPDATE en transacción ──────────────────────────────────────────────────

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('UPDATE assigned_plans SET content=? WHERE id=?');
    $stmt->execute([
        json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        $assignedPlanId,
    ]);
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    fwrite(STDERR, "ERROR UPDATE: " . $e->getMessage() . "\n");
    exit(1);
}

echo "✓ OK — plan $assignedPlanId actualizado (".count($log)." mods aplicadas)\n\n";
foreach ($log as $line) echo "  · $line\n";

echo "\nSiguiente: invalidar caches del cliente $clientId:\n";
echo "  php artisan tinker --execute=\"\\Cache::forget('client_plan_v3_$clientId'); \\Cache::forget('wp:plan:$clientId'); \\Cache::forget('wp:weekdays:$clientId'); \\Cache::forget('dashboard:$clientId'); echo 'OK';\"\n";
