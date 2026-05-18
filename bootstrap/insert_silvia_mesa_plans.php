<?php
/**
 * insert_silvia_mesa_plans.php
 *
 * Renovación 2026-05-18 para Silvia Mesa (client_id=66). Plan Esencial.
 * Renueva ÚNICAMENTE entrenamiento + nutricion (suplementación vigente se respeta).
 *
 * Entrenamiento: 4 semanas × 6 días, todas las semanas en intensificación
 *   (sin deload/acumulación). Foco aumento de masa muscular.
 * Nutrición: 2250 kcal aumento, 5 comidas con opciones A/B/C (cantidades en crudo).
 *
 * Voz: voseo colombiano neutro (regla autoritativa 2026-05-17).
 *
 * Ejecutar en container EasyPanel:
 *   php /code/bootstrap/insert_silvia_mesa_plans.php
 *
 * Para dry-run local (sin tocar DB):
 *   php -d display_errors=1 -r "define('DRY_RUN', true); require 'bootstrap/insert_silvia_mesa_plans.php';"
 */

if (!(defined('DRY_RUN') && DRY_RUN)) {
    $pdo = new PDO(
        'mysql:host=wellcorefitness_wellcorefitness-mysql;dbname=wellcorefitness;charset=utf8mb4',
        'wellcorefitness',
        'fYCVgn4XZ7twq34',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
}

$clientId  = 66;
$coachId   = 7;                  // Anderson Ardila (admin_id default)
$validFrom = '2026-05-18';
$expiresAt = '2026-06-14';       // 4 semanas exactas
$now       = date('Y-m-d H:i:s');
$gifBase   = 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/';
$g = fn(string $a): string => $gifBase . $a . '.gif';

// ─── PARÁMETROS POR SEMANA (todas intensificación, NO hay deload) ────────────
// Reps las fija el coach por ejercicio (no progresión de reps). Solo varía RIR.
$weekParams = [
    1 => ['fase' => 'Intensificación',           'rir' => '2',   'descanso' => '75 seg'],
    2 => ['fase' => 'Intensificación progresiva', 'rir' => '1-2', 'descanso' => '75-90 seg'],
    3 => ['fase' => 'Intensificación alta',      'rir' => '1',   'descanso' => '90 seg'],
    4 => ['fase' => 'Intensificación máxima',    'rir' => '0-1', 'descanso' => '90-120 seg'],
];

// ─── DÍAS BASE ───────────────────────────────────────────────────────────────
// Estructura ejercicio: [gif_alias, nombre, series, reps, notas, variacion_alias, variacion_nombre, extra?]
// extra puede llevar 'descanso_override' o 'rir_override' (para abs/cardio).

$dayTemplates = [
    'Lunes' => [
        'grupo_muscular' => 'Piernas (Cuádriceps + Pantorrilla)',
        'tipo'           => 'legs',
        'calentamiento'  => '8 min: 5 min bici suave + 2 series de 15 sentadillas sin peso + 10 elevaciones de talones + activación de glúteo con banda (10 monster walks cada lado).',
        'vuelta_calma'   => '6 min: estiramiento de cuádriceps de pie 30 seg cada lado + isquios sentada + figura 4 para glúteo + estiramiento de pantorrilla en pared 30 seg cada lado.',
        'ejercicios' => [
            [$g('sentadilla-con-barra'),              'Sentadilla con barra',                  4, '12-10-10-8', 'Pies a la anchura de los hombros, barra apoyada en trapecios. Bajás hasta que los muslos pasen la paralela, empujás con los talones. Pecho arriba siempre.',                  'sentadilla-hacka',                  'Sentadilla hack'],
            [$g('presa-de-piernas-abierto'),          'Prensa de piernas',                     4, '10-12',      'Pies en el centro de la plataforma, anchura de hombros. Bajás controlada hasta 90° de rodilla, empujás sin bloquear arriba. No saqués las lumbares del respaldo.',         'prensa-de-piernas-cerrado',         'Prensa de piernas pies juntos'],
            [$g('extension-de-piernas-en-maquina'),   'Extensión de piernas en máquina',       4, '12-15',      'Ajustás el respaldo para que la rodilla coincida con el eje de la máquina. Subís controlada, apretás cuádriceps 1 seg arriba, bajás en 2 seg.',                            'sentadilla-isometrica',             'Sentadilla isométrica en pared'],
            [$g('sentadilla-bulgara-mancuerna'),      'Sentadilla búlgara con mancuerna',      4, '12-15',      'Pie trasero apoyado en banco, una mancuerna en cada mano. Bajás vertical (no hacia adelante), empujás con el talón delantero. Hacés ambas piernas.',                       'sentadilla-bulgara-barra',          'Sentadilla búlgara con barra'],
            [$g('sentadilla-goblet'),                 'Sentadilla goblet con mancuerna',       3, '10-12',      'Mancuerna pegada al pecho con ambas manos, codos hacia adentro. Bajás profundo manteniendo el torso erguido. Ideal para cerrar piernas.',                                  'sentadilla-con-mancuernas',         'Sentadilla con mancuernas'],
            [$g('elevacion-de-talones-en-maquina'),   'Elevación de talones en máquina',       4, '15-20',      'Subís lo más alto posible, apretás la pantorrilla 1 seg arriba, bajás controlada con estiramiento completo. Movimiento lento.',                                              'elevacion-de-talones-con-mancuerna','Elevación de talones con mancuerna'],
        ],
    ],

    'Martes' => [
        'grupo_muscular' => 'Hombro + Tríceps + Abdomen',
        'tipo'           => 'push',
        'calentamiento'  => '7 min: rotaciones de hombro (10 cada dirección) + 2 series de 12 elevaciones laterales con mancuernas livianas + 10 rotaciones externas con banda + 1 serie de 10 press con barra vacía.',
        'vuelta_calma'   => '5 min: estiramiento de deltoides cruzado 30 seg cada lado + tríceps por encima de la cabeza 30 seg cada lado + apertura torácica en pared.',
        'ejercicios' => [
            [$g('press-de-hombro-con-mancuerna'),     'Press de hombro con mancuerna',         4, '10-12', 'Sentada con espalda apoyada. Bajás hasta que los codos queden a 90°, empujás sin bloquear codos arriba. Codos ligeramente al frente, no abiertos del todo.',         'press-de-hombro-en-maquina-sentado','Press de hombro en máquina'],
            [$g('elevacion-lateral-con-mancuerna'),   'Elevación lateral con mancuerna',       4, '12-15', 'Codos ligeramente flexionados. Subís a la altura del hombro (no más), aprietás deltoides medio 1 seg arriba, bajás en 2 seg. Sin balanceo.',                       'elevacion-lateral-en-polea',        'Elevación lateral en polea'],
            [$g('elevacion-posterior-con-mancuerna'), 'Elevaciones posteriores con mancuerna', 4, '12-15', 'Torso inclinado al frente 45°, brazos colgando. Abrís los brazos hacia atrás apretando deltoides posterior. Movimiento limpio, sin impulso.',                       'elevaciones-posteriores-en-polea',  'Elevaciones posteriores en polea'],
            [$g('remo-al-menton-con-barra'),          'Remo al mentón con barra',              4, '10-12', 'Agarre ligeramente más ancho que los hombros, codos altos durante todo el movimiento. Subís la barra hasta la mitad del pecho. Si te duele el hombro, andá más liviana.', 'remo-al-menton-con-mancuerna',     'Remo al mentón con mancuerna'],
            [$g('extension-de-triceps-en-polea-con-cuerda'), 'Extensión de tríceps en polea con cuerda', 4, '12-15', 'Codos pegados al torso, fijos. Solo se mueve el antebrazo. Al final abrís la cuerda separando las manos. Apretás tríceps 1 seg abajo.',                          'extension-de-triceps-en-polea-agarre-inverso', 'Extensión de tríceps polea agarre inverso'],
            [$g('extension-de-triceps-con-mancuerna'), 'Extensión de tríceps con mancuerna',   4, '10-12', 'Sentada o de pie, mancuerna por encima de la cabeza con ambas manos. Bajás detrás de la nuca controlada, subís apretando tríceps. Codos quietos.',                     'extension-de-triceps-sobre-cabeza-con-cuerda', 'Extensión de tríceps sobre cabeza con cuerda'],
            [$g('crunch-en-polea-arrodillado'),       'Crunch en polea arrodillado',           4, '15-20', 'De rodillas, cuerda detrás de la nuca. Llevás los codos hacia las rodillas redondeando la espalda (curvás el tronco, no flexionás cadera). Apretás abdomen abajo.',     'crunch-sentado-en-maquina',         'Crunch sentado en máquina'],
            [$g('plancha-abdominal'),                 'Plancha abdominal',                     4, '45-60 seg', 'Codos bajo los hombros, cuerpo recto desde la cabeza hasta los talones. Apretás abdomen y glúteo. Si tiembla, vas bien.',                                          'plancha-lateral',                   'Plancha lateral (alterná lados)', ['rir_override' => '—', 'descanso_override' => '45 seg']],
        ],
    ],

    'Miércoles' => [
        'grupo_muscular' => 'Glúteo + Femoral (Cadera dominante)',
        'tipo'           => 'legs',
        'calentamiento'  => '8 min: 5 min bici + activación de glúteo con banda (caminata lateral 20 pasos cada lado + 15 puentes sin peso + 10 abducciones de pie).',
        'vuelta_calma'   => '6 min: estiramiento de glúteo en figura 4 cada lado + isquios sentada + estiramiento del psoas (lunge bajo) 30 seg cada lado.',
        'ejercicios' => [
            [$g('hipthrust-con-barra'),               'Hip thrust con barra',                  4, '12-10-8-8', 'Espalda apoyada en banco a la altura del omóplato, barra acolchada sobre la cadera. Empujás desde los talones, aprietás glúteo 1 seg arriba. Costillas hacia abajo, no arquees lumbar.', 'puente-de-gluteo-con-barra',        'Puente de glúteo con barra (en suelo)'],
            [$g('peso-muerto-sumo-con-barra'),        'Peso muerto sumo con barra',            4, '12-10-8-8', 'Pies abiertos más anchos que los hombros, puntas hacia afuera 30°. Barra pegada al cuerpo, empujás el piso con los talones. Glúteos aprietan arriba.',                                'peso-muerto-rumano-con-barra',      'Peso muerto rumano con barra'],
            [$g('step-up-mancuerna'),                 'Step up con mancuerna',                 4, '10-12',     'Cajón a la altura de la rodilla, una mancuerna en cada mano. Subís empujando con el talón completo (no con la punta). Bajás controlada, sin saltar. Hacés ambas piernas.',           'zancada-frontal-con-mancuerna',     'Zancada frontal con mancuerna'],
            [$g('abduccion-de-cadera-sentado-en-maquina'), 'Abducción de cadera sentada en máquina', 4, '12-15', 'Inclinás el torso ligeramente hacia adelante (12-15° aprox) para activar más glúteo medio. Abrís controlada, apretás 1 seg, volvés en 2 seg.',                                  'abduccion-de-cadera-de-pie-en-maquina', 'Abducción de cadera de pie en máquina'],
            [$g('patada-trasera-en-polea'),           'Patada trasera en polea',               4, '12-15',     'Polea baja, tobillera en el tobillo. Pierna casi extendida, llevás hacia atrás apretando glúteo arriba 1 seg. NO arquees lumbar — el movimiento es de cadera, no de espalda.',     'patada-trasera-en-maquina',         'Patada trasera en máquina'],
        ],
    ],

    'Jueves' => [
        'grupo_muscular' => 'Espalda + Bíceps + Abdomen',
        'tipo'           => 'pull',
        'calentamiento'  => '7 min: 3 min remo ergómetro + 2 series de 12 jalones livianos en polea + 10 rotaciones de hombro + activación de dorsal con banda (10 face pulls livianos).',
        'vuelta_calma'   => '5 min: estiramiento de dorsal colgada de barra 20 seg + bíceps en pared 30 seg cada lado + estiramiento de hombro cruzado.',
        'ejercicios' => [
            [$g('jalon-en-polea'),                    'Jalón en polea',                        4, '10-12', 'Pecho fuera, ligera inclinación hacia atrás. Jalás llevando los codos hacia abajo y atrás, barra al pecho alto. Apretás dorsales abajo 1 seg.',                       'jalon-al-pecho-en-maquina',         'Jalón al pecho en máquina'],
            [$g('remo-en-polea-sentado'),             'Remo en polea sentado',                 4, '10-12', 'Espalda neutra (no curvada). Jalás la cuerda hacia el ombligo apretando escápulas atrás. Codos pegados al torso, no abiertos.',                                       'remo-sentado-en-maquina',           'Remo sentado en máquina'],
            [$g('remo-con-mancuerna-a-una-mano'),     'Remo con mancuerna a una mano',         4, '12-15', 'Una rodilla y mano apoyadas en banco, espalda paralela al piso. Jalás la mancuerna hacia la cadera (no al pecho), codo pegado al torso. Apretás dorsal arriba.',       'remo-con-mancuernas-sobre-banco-inclinado', 'Remo con mancuernas en banco inclinado'],
            [$g('facepull-en-polea'),                 'Face pull en polea',                    4, '12-15', 'Cuerda a la altura de la cara, codos altos. Jalás separando las manos a los costados de la cabeza. Apretás escápulas y deltoides posterior. Ideal para postura.',     'remo-polea-para-deltoides',         'Remo en polea para deltoides'],
            [$g('curl-biceps-con-mancuerna'),         'Curl bíceps con mancuerna',             4, '12-15', 'Codos pegados al torso, fijos. Subís controlada apretando bíceps arriba 1 seg, bajás en 2 seg. Podés alternar brazos o hacer ambos a la vez.',                          'curl-biceps-con-mancuerna-en-banco-inclinado', 'Curl bíceps mancuerna banco inclinado'],
            [$g('curl-predicador-con-barra'),         'Curl bíceps predicador con barra',      4, '12-15', 'Pecho apoyado en almohadilla, brazos completamente extendidos abajo (estiramiento total). Subís hasta arriba apretando, bajás controlada — no sueltes el peso.',         'curl-predicador-con-barra-ez',      'Curl predicador con barra EZ'],
            [$g('elevacion-de-piernas-acostado'),     'Elevación de piernas acostada',         4, '15-20', 'Acostada en colchoneta, manos a los lados o bajo glúteos. Subís las piernas con leve flexión de rodillas hasta 90°, bajás controlada sin tocar el piso. Abdomen siempre tenso.', 'inclinacion-piernas-cadera-banco-inclinado', 'Elevación de piernas en banco inclinado'],
            [$g('plancha-abdominal'),                 'Plancha abdominal',                     4, '45-60 seg', 'Codos bajo los hombros, cuerpo recto. Apretás abdomen y glúteo. Respiración constante — no aguantes el aire.',                                                      'plancha-lateral',                   'Plancha lateral', ['rir_override' => '—', 'descanso_override' => '45 seg']],
        ],
    ],

    'Viernes' => [
        'grupo_muscular' => 'Femorales + Glúteo',
        'tipo'           => 'legs',
        'calentamiento'  => '8 min: 5 min bici + 15 puentes de glúteo sin peso + 10 hiperextensiones suaves + activación con banda (10 monster walks cada lado).',
        'vuelta_calma'   => '6 min: estiramiento de isquios sentada cada lado + figura 4 para glúteo + cuádriceps de pie + estiramiento del psoas.',
        'ejercicios' => [
            [$g('peso-muerto-rumano-con-barra'),      'Peso muerto rumano con barra',          4, '8-10',  'Rodillas semiflexionadas (no se mueven durante el ejercicio). Bajás la barra pegada a las piernas hasta sentir estiramiento profundo en isquios. Subís apretando glúteo.', 'peso-muerto-rumano-con-mancuerna',  'Peso muerto rumano con mancuerna'],
            [$g('curl-femoral-acostado-en-maquina'),  'Curl femoral acostado en máquina',      4, '10-12', 'Acostada boca abajo, tobillos apoyados en el rodillo. Flexionás las rodillas llevando talones a glúteos, apretás 1 seg arriba, bajás controlada en 2 seg.',                'curl-femora-en-polea',              'Curl femoral en polea'],
            [$g('curl-femoral-sentado'),              'Curl femoral sentado en máquina',       4, '12-15', 'Espalda apoyada, ajustás el respaldo para que la rodilla coincida con el eje. Flexionás controlada apretando isquios atrás, bajás sin soltar el peso.',                  'curl-femoral-arrodillado-en-maquina','Curl femoral arrodillada en máquina'],
            [$g('puente-de-gluteo-con-mancuerna'),    'Puente de glúteo con mancuerna',        3, '15-20', 'Acostada de espaldas, mancuerna sobre la cadera. Empujás caderas hacia arriba apretando glúteo 1 seg arriba. Costillas abajo, no arquees lumbar.',                       'puente-de-gluteo-con-barra',        'Puente de glúteo con barra'],
            [$g('hiperextension'),                    'Hiperextensión',                        3, '15-20', 'Cadera apoyada en el almohadillado, talones fijos. Bajás controlada con espalda neutra (no curva), subís apretando glúteos hasta línea recta. Sin pasarte de la horizontal.',  'extension-de-espalda-en-maquina',   'Extensión de espalda en máquina'],
        ],
    ],

    'Sábado' => [
        'grupo_muscular' => 'Abdomen + Funcional + Hombros',
        'tipo'           => 'full',
        'calentamiento'  => '7 min: 5 min bici suave + 10 rotaciones de hombro + 10 sentadillas sin peso + activación con banda.',
        'vuelta_calma'   => '5 min: estiramiento de abdomen (cobra) + hombros cruzados + cuádriceps de pie.',
        'ejercicios' => [
            [$g('crunch-en-pelota-de-estabilidad'),   'Crunch en pelota de estabilidad',       4, '15-20', 'Cadera y baja espalda sobre la pelota, manos detrás de la nuca (sin jalar el cuello). Subís curvando el tronco, apretás abdomen arriba 1 seg, bajás controlada estirando.',     'crunches-sobre-pelota-de-estabilidad', 'Crunch abdominal sobre pelota'],
            [$g('elevacion-de-piernas-acostado'),     'Elevación de piernas acostada',         4, '15-20', 'Manos bajo los glúteos para proteger lumbar. Subís las piernas a 90°, bajás controlada sin tocar el piso. Movimiento lento.',                                                  'elevacion-de-piernas-sentado',      'Elevación de piernas sentada'],
            [$g('sentadilla-con-mancuernas'),         'Sentadilla con mancuerna',              4, '12-15', 'Mancuerna en cada mano a los costados o una sola en el pecho. Bajás profundo manteniendo el torso erguido. Empujás con talones.',                                              'sentadilla-goblet',                 'Sentadilla goblet'],
            [$g('peso-muerto-rumano-con-mancuerna'),  'Peso muerto rumano con mancuerna',      4, '12-15', 'Mancuernas pegadas a los muslos, rodillas semiflexionadas. Bajás manteniendo la espalda neutra hasta sentir estiramiento en isquios, subís apretando glúteo.',                'peso-muerto-rumano-con-barra',      'Peso muerto rumano con barra'],
            [$g('press-arnold-con-mancuerna'),        'Press Arnold con mancuernas',           4, '12-15', 'Sentada, mancuernas al frente con palmas hacia vos (posición curl). Rotás mientras subís hasta que las palmas miren al frente arriba. Movimiento fluido, sin pausas.',         'press-de-hombro-con-mancuerna',     'Press de hombro con mancuerna'],
            [$g('elevacion-lateral-en-polea'),        'Elevaciones laterales en polea',        4, '12-15', 'Polea baja por el lado contrario al brazo que trabaja. Subís hasta la altura del hombro con codo semiflexionado. Tensión constante en todo el rango. Hacés ambos lados.',  'elevacion-lateral-con-mancuerna',   'Elevación lateral con mancuerna'],
        ],
    ],
];

// ─── HELPER: construir ejercicio para una semana específica ──────────────────
function buildExercise(int $weekNum, array $entry, array $weekParams): array
{
    $wp = $weekParams[$weekNum];
    $ej = [
        'nombre'       => $entry[1],
        'gif_url'      => $entry[0],
        'series'       => $entry[2],
        'repeticiones' => $entry[3],
        'descanso'     => $wp['descanso'],
        'rir'          => $wp['rir'],
        'notas'        => $entry[4],
        'variacion'    => [
            'nombre'  => $entry[6],
            'gif_url' => $entry[5],
        ],
    ];

    // Overrides para abs/cardio (último parámetro opcional)
    if (isset($entry[7]) && is_array($entry[7])) {
        if (isset($entry[7]['rir_override']))      $ej['rir']      = $entry[7]['rir_override'];
        if (isset($entry[7]['descanso_override'])) $ej['descanso'] = $entry[7]['descanso_override'];
    }

    return $ej;
}

// ─── CONSTRUIR SEMANAS ───────────────────────────────────────────────────────
$diaIndexBase = ['Lunes' => 1, 'Martes' => 2, 'Miércoles' => 3, 'Jueves' => 4, 'Viernes' => 5, 'Sábado' => 6];
$semanas = [];

$notaFase = [
    1 => 'Semana 1 de intensificación. Cargás un poco menos del máximo (RIR 2: te queda margen de 2 reps). El objetivo es activar y dejar el cuerpo listo para empujar más en las siguientes semanas. NO bajés la técnica.',
    2 => 'Subís un 5% la carga respecto a la semana 1. RIR 1-2 (te queda 1-2 reps en el tanque). Si la técnica se mantiene impecable, dale.',
    3 => 'Subís otro 5% la carga. RIR 1 (te queda 1 rep). Acá empezás a sentir la intensidad. Descansos un poquito más largos para recuperarte entre series.',
    4 => 'Última semana del bloque. Carga máxima del mes. RIR 0-1 (el último set te queda al fallo). NO empujés al fallo en compuestos pesados (sentadilla, peso muerto, hip thrust) — fallo solo en aislamientos. Esta semana se ve el resultado en el espejo.',
];

for ($w = 1; $w <= 4; $w++) {
    $wp = $weekParams[$w];
    $dias = [];

    foreach ($dayTemplates as $diaNombre => $tpl) {
        $ejs = [];
        foreach ($tpl['ejercicios'] as $entry) {
            $ejs[] = buildExercise($w, $entry, $weekParams);
        }

        $dias[] = [
            'dia'               => $diaIndexBase[$diaNombre],
            'dia_semana'        => $diaNombre,
            'nombre'            => $diaNombre . ' — ' . $tpl['grupo_muscular'],
            'grupo_muscular'    => $tpl['grupo_muscular'],
            'tipo'              => $tpl['tipo'],
            'duracion_estimada' => '75-90 min',
            'calentamiento'     => $tpl['calentamiento'],
            'vuelta_calma'      => $tpl['vuelta_calma'],
            'ejercicios'        => $ejs,
        ];
    }

    $semanas[] = [
        'numero'        => $w,
        'semana'        => $w,
        'fase'          => $wp['fase'],
        'fase_nombre'   => 'Semana ' . $w . ' — ' . $wp['fase'],
        'nombre_bloque' => 'Semana ' . $w . ' — ' . $wp['fase'],
        'rpe_objetivo'  => $w === 1 ? '7-8' : ($w === 2 ? '8' : ($w === 3 ? '8.5' : '9-9.5')),
        'nota_semana'   => $notaFase[$w],
        'dias'          => $dias,
    ];
}

// ─── PLAN ENTRENAMIENTO ──────────────────────────────────────────────────────
$trainPlan = [
    'plan_type'        => 'entrenamiento',
    'titulo'           => 'Plan Esencial Entrenamiento — Silvia Mesa',
    'programa'         => 'Aumento de masa muscular · Body Part Split 6 días · 4 semanas de intensificación pura',
    'cliente'          => 'Silvia Mesa',
    'plan'             => 'Esencial',
    'objetivo'         => 'Aumento de masa muscular con foco en piernas, glúteo y tren superior. Bloque de intensificación pura — todas las semanas suben carga.',
    'genero'           => 'Femenino',
    'nivel'            => 'Intermedio',
    'metodologia'      => 'Body Part Split 6 días · Intensificación lineal (sin deload intra-bloque)',
    'frecuencia'       => '6 días por semana',
    'frecuencia_dias'  => 6,
    'duracion_sesion'  => '75-90 minutos',
    'equipamiento'     => 'Gimnasio completo',
    'duracion_semanas' => 4,
    'peso_cliente'     => '60 kg',
    'estatura'         => '165 cm',
    'fecha_inicio'     => $validFrom,
    'fecha_fin'        => $expiresAt,

    // ⭐ HORARIO SEMANAL — alimenta el grid de días en /client/plan
    'split' => [
        'Lunes'     => 'Piernas (Cuádriceps + Pantorrilla)',
        'Martes'    => 'Hombro + Tríceps + Abdomen',
        'Miércoles' => 'Glúteo + Femoral (Cadera dominante)',
        'Jueves'    => 'Espalda + Bíceps + Abdomen',
        'Viernes'   => 'Femorales + Glúteo',
        'Sábado'    => 'Abdomen + Funcional + Hombros',
    ],

    'tecnicas_avanzadas' => [
        'Sobrecarga progresiva — subís 2.5-5 kg cada semana en los compuestos cuando completés las reps con técnica impecable.',
        'Reps en reserva (RIR) — cada semana baja un escalón el margen hasta llegar al fallo controlado en la semana 4.',
        'Tempo controlado — 2 seg bajada, 1 seg pausa abajo o arriba según el ejercicio, 1 seg subida. Sin rebotes.',
    ],
    'principios' => [
        'tecnica_primero'   => 'La técnica perfecta precede a cualquier carga. Si la forma se rompe, bajás el peso.',
        'sobrecarga'        => 'Subís carga semanalmente cuando complets todas las reps con margen (RIR).',
        'registro'          => 'Anotás pesos y reps de cada sesión en la app o en el cuaderno. Sin registro no hay progresión.',
        'descanso_completo' => 'Respetás el descanso entre series. Si descansás de menos perdés fuerza para el siguiente set.',
    ],

    'semanas'         => $semanas,
    'notas_generales' => 'Bloque de intensificación pura: las 4 semanas son progresivas en carga y RIR, sin semana de descarga. Cardio opcional 1-2 días por semana entre sesiones (15-20 min ligero), no obligatorio.',
    'notas_coach'     => "Silvia, este bloque está armado para empujar 4 semanas seguidas sin freno — no hay semana de descarga, todas son de intensificación. Eso quiere decir que cada semana subís un poquito la carga y bajás el margen (RIR) hasta llegar al fallo controlado en la semana 4.\n\nEl split que armé respeta tu día doble de pierna (lunes cuádriceps + miércoles glúteo/cadera + viernes femoral/glúteo), y deja sábado más liviano funcional para no sobrecargar la semana. Cada ejercicio tiene su variación — si la máquina está ocupada en el gym, tocás 'Ver variación' en la app y seguís sin perder tiempo.\n\nDos reglas que no se negocian este mes: registrás cada sesión (peso y reps) en la app, y respetás los descansos. Si descansás de menos, el siguiente set lo hacés con menos fuerza y se cae la progresión. Si descansás de más, se enfría el músculo y también perdés intensidad. 75-90 seg en pesado, 60 seg en aislamientos.\n\nEn nutrición vamos con 2250 calorías priorizando proteína y carbohidrato — necesitás el combustible para empujar este bloque. Si un día amanecés muerta, NO te saltás el entreno: bajás carga un 15% y hacés la sesión completa igual. Saltarte días es lo único que mata este bloque. Cualquier duda me escribís. Vamos.",
];

// ────────────────────────────────────────────────────────────────────────────
// PLAN NUTRICIÓN — 2250 kcal aumento masa muscular
// ────────────────────────────────────────────────────────────────────────────

$nutriPlan = [
    'plan_type' => 'nutricion',
    'titulo'    => 'Plan Nutricional — Aumento de Masa Muscular | Silvia Mesa',
    'cliente'   => 'Silvia Mesa',

    'metodologia' => 'Aumento de masa muscular · Superávit calórico moderado · Alta proteína y carbohidrato',

    'objetivo_calorico' => 2250,
    'objetivo_cal'      => 2250,
    'objetivo'          => 'Aumento de masa muscular limpio. Superávit calórico moderado priorizando proteína (~2 g/kg) y carbohidrato como combustible principal para los entrenamientos. Todas las cantidades están en CRUDO.',

    'macros' => [
        'proteina_g'       => 130,
        'carbohidratos_g'  => 290,
        'grasas_g'         => 65,
    ],

    'periodizacion' => [
        'dias_entrenamiento' => ['calorias' => 2350, 'carbs_extra_g' => 25, 'nota' => 'Días de pesas: 25 g extra de carbohidrato como combustible (preferible en pre-entreno y post-entreno).'],
        'dias_descanso'      => ['calorias' => 2150, 'carbs_reduccion_g' => 25, 'nota' => 'Días de descanso: 25 g menos de carbohidrato. Proteína se mantiene igual.'],
    ],

    'hidratacion' => [
        'agua_minima_litros' => 3.0,
        'electrolitos'       => 'En días de entrenamiento intenso añadí electrolitos sin azúcar (1 sobre en la botella). Te ayuda a no calambrear y a recuperarte.',
    ],

    'fecha_inicio' => $validFrom,

    // ⭐ Tips para card verde "CONSEJOS DE TU COACH"
    'tips_nutricionales' => [
        'Las cantidades están en CRUDO. Si pesás el alimento cocido, los gramos van a ser distintos (el arroz cocido pesa más por el agua, la carne cocida pesa menos por la deshidratación).',
        'Proteína primero: si un día no llegás a las 2250 cal pero cumplís los 130 g de proteína, el día sigue siendo productivo. La proteína es la prioridad.',
        'Las 3 opciones (A / B / C) de cada comida son intercambiables. Macros equivalentes, podés elegir la que más te apetezca o la que tengas disponible.',
        'En días de entrenamiento subí carbohidrato (+25g) sobre todo en pre y post entreno — es el combustible que te va a permitir empujar las pesas.',
        'En días de descanso bajá 25 g de carbo (sacá la mitad del arroz del almuerzo o cena). Proteína se mantiene igual.',
        'Hidratación: 3 L de agua al día es la base. Si entrenás intenso o hace calor, subís a 3.5 L.',
        'Comé despacio y masticá bien — ayuda a la digestión y a la saciedad. Sin pantallas en la mesa idealmente.',
        'Si tenés un evento o querés salir a comer, no te saltés comidas previas para "ahorrar calorías". Comé normal y disfrutá la salida. Un día no rompe el plan.',
    ],
    'tips' => [
        'Las cantidades están en CRUDO. Si pesás el alimento cocido, los gramos van a ser distintos (el arroz cocido pesa más por el agua, la carne cocida pesa menos por la deshidratación).',
        'Proteína primero: si un día no llegás a las 2250 cal pero cumplís los 130 g de proteína, el día sigue siendo productivo. La proteína es la prioridad.',
        'Las 3 opciones (A / B / C) de cada comida son intercambiables. Macros equivalentes, podés elegir la que más te apetezca o la que tengas disponible.',
        'En días de entrenamiento subí carbohidrato (+25g) sobre todo en pre y post entreno — es el combustible que te va a permitir empujar las pesas.',
        'En días de descanso bajá 25 g de carbo (sacá la mitad del arroz del almuerzo o cena). Proteína se mantiene igual.',
        'Hidratación: 3 L de agua al día es la base. Si entrenás intenso o hace calor, subís a 3.5 L.',
        'Comé despacio y masticá bien — ayuda a la digestión y a la saciedad. Sin pantallas en la mesa idealmente.',
        'Si tenés un evento o querés salir a comer, no te saltés comidas previas para "ahorrar calorías". Comé normal y disfrutá la salida. Un día no rompe el plan.',
    ],

    'notas_coach' => "Silvia, este plan es de 2250 calorías y está armado para acompañar el bloque de intensificación de las pesas. Como vas a estar empujando carga las 4 semanas seguidas, necesitás combustible — por eso el carbohidrato sube respecto a un plan de mantenimiento o pérdida.\n\nLas cantidades están todas en CRUDO. Eso es clave: si pesás el pollo o el arroz ya cocidos, los gramos cambian (el arroz cocido pesa más por el agua, la carne cocida pesa menos por la deshidratación). Pesar crudo es lo más exacto.\n\nCada comida tiene 3 opciones (A, B, C) — son intercambiables, no las hacés todas. Elegís la que más te apetezca o la que tengas más a mano. Los macros son equivalentes entre las 3 opciones.\n\nSi un día no llegás a las 2250 cal pero cumplís los 130 g de proteína, el día sigue siendo productivo. La proteína es lo que sostiene la masa muscular — el resto son ajustes finos. Vamos.",

    'comidas' => [
        // ─── PRE-ENTRENO ─────────────────────────────────────────────────
        [
            'nombre'    => 'Pre-entreno',
            'tipo'      => 'pre-entreno',
            'hora'      => '5:30 AM',
            'subtitulo' => '30-45 min antes de entrenar',
            'calorias'  => 220,
            'macros'    => ['proteina' => 8, 'carbohidratos' => 45, 'grasas' => 2],
            'opcion_a'  => [
                'Banano maduro (1 unidad mediana, 120g)',
                'Miel de abeja (1 cda, 15g)',
            ],
            'opcion_b'  => [
                'Yogur griego natural (100g)',
                'Fresas (100g)',
                'Miel de abeja (10g)',
            ],
            'opcion_c'  => [
                'Pan tostado integral (60g, 2 rebanadas)',
                'Miel de abeja (15g)',
            ],
            'notas_comida' => 'Carbohidrato rápido para que tengas energía en el entreno. Si entrenás muy temprano y te cae pesado, hacé media porción y completás el resto en el desayuno post-entreno.',
        ],

        // ─── DESAYUNO ────────────────────────────────────────────────────
        [
            'nombre'    => 'Desayuno',
            'tipo'      => 'desayuno',
            'hora'      => '7:30 AM',
            'subtitulo' => 'Post-entreno o primera comida del día',
            'calorias'  => 560,
            'macros'    => ['proteina' => 35, 'carbohidratos' => 65, 'grasas' => 18],
            'opcion_a'  => [
                'Huevos enteros (3 unidades)',
                'Tostadas de pan integral (60g, 2 rebanadas)',
                'Aguacate (50g)',
                'Tomate fresco (50g)',
            ],
            'opcion_b'  => [
                'Overnight oats: Avena en hojuelas (60g cruda)',
                'Proteína whey (1 scoop, 30g)',
                'Banano (100g)',
                'Canela (al gusto)',
                'Leche de almendras sin azúcar (200ml)',
                'Mantequilla de maní natural (15g)',
                'Semillas de chía (10g)',
            ],
            'opcion_c'  => [
                'Pollo desmechado (100g cocido, ~130g crudo)',
                'Tostadas de pan integral (60g, 2 rebanadas)',
                'Aguacate (50g)',
            ],
            'notas_comida' => 'Si es post-entreno: priorizá la opción B (whey + carbo rápido) para acelerar la recuperación. Si es comida normal del día, cualquiera de las 3 funciona.',
        ],

        // ─── ALMUERZO ────────────────────────────────────────────────────
        [
            'nombre'    => 'Almuerzo',
            'tipo'      => 'almuerzo',
            'hora'      => '12:30 PM',
            'subtitulo' => 'Comida principal del día',
            'calorias'  => 720,
            'macros'    => ['proteina' => 45, 'carbohidratos' => 95, 'grasas' => 20],
            'opcion_a'  => [
                'Pechuga de pollo (150g cruda)',
                'Arroz blanco (60g crudo)',
                'Lentejas cocidas (80g)',
                'Aguacate (50g)',
                'Ensalada de pepino y tomate (libre)',
            ],
            'opcion_b'  => [
                'Carne de res magra, lomo o sobrebarriga (150g cruda)',
                'Arroz blanco (60g crudo)',
                'Plátano maduro (80g)',
                'Frijoles cocidos (80g)',
                'Ensalada verde (libre)',
            ],
            'opcion_c'  => [
                'Lomo de cerdo magro (150g crudo)',
                'Arroz blanco (50g crudo)',
                'Puré de papa (150g de papa cruda)',
                'Ensalada de aguacate (40g) + tomate + zanahoria (libre)',
            ],
            'notas_comida' => 'Esta es la comida más alta en carbohidrato del día. Ideal después del entreno o antes si entrenás en la tarde. Ensaladas libres = todo lo que quieras, sin contar.',
        ],

        // ─── SNACK PM ────────────────────────────────────────────────────
        [
            'nombre'    => 'Snack PM',
            'tipo'      => 'merienda',
            'hora'      => '4:00 PM',
            'subtitulo' => 'Media tarde',
            'calorias'  => 320,
            'macros'    => ['proteina' => 20, 'carbohidratos' => 35, 'grasas' => 12],
            'opcion_a'  => [
                'Yogur griego natural (150g)',
                'Banano (100g)',
                'Granola sin azúcar (30g)',
            ],
            'opcion_b'  => [
                'Huevos duros (2 unidades)',
                'Pan integral (40g, 1 rebanada)',
                'Tomate cherry (libre)',
            ],
            'opcion_c'  => [
                'Queso cottage (150g)',
                'Manzana (1 unidad mediana)',
                'Nueces (20g)',
            ],
            'notas_comida' => 'Snack para mantener energía y proteína distribuida. Si entrenás en la tarde, esta puede ser tu pre-entreno (priorizá opción A).',
        ],

        // ─── CENA ────────────────────────────────────────────────────────
        [
            'nombre'    => 'Cena',
            'tipo'      => 'cena',
            'hora'      => '8:00 PM',
            'subtitulo' => 'Última comida del día',
            'calorias'  => 530,
            'macros'    => ['proteina' => 38, 'carbohidratos' => 50, 'grasas' => 18],
            'opcion_a'  => [
                'Pechuga de pollo desmenuzada (150g cruda)',
                'Puré de papa (150g de papa cruda)',
                'Aguacate (40g)',
                'Ensalada de tomate y cebolla (libre)',
            ],
            'opcion_b'  => [
                'Carne de res magra (130g cruda)',
                'Pasta cocida (60g de pasta seca)',
                'Salsa de tomate natural (50g)',
                'Ensalada de zanahoria y tomate (libre)',
            ],
            'opcion_c'  => [
                'Huevos enteros (2 unidades)',
                'Claras de huevo (3 unidades)',
                'Arroz blanco (50g crudo)',
                'Ensalada de tomate y pepino (libre)',
            ],
            'notas_comida' => 'Cena ligera pero completa. Ideal terminar de comer al menos 2 horas antes de dormir para una mejor digestión. Si te da hambre antes de acostarte, podés tomarte 1 vaso de leche o un yogur natural.',
        ],
    ],
];

// ─── ENCODE Y INSERT ─────────────────────────────────────────────────────────
$trainJson = json_encode($trainPlan, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$nutriJson = json_encode($nutriPlan, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

if ($trainJson === false) die("ERROR encoding trainPlan: " . json_last_error_msg() . "\n");
if ($nutriJson === false) die("ERROR encoding nutriPlan: " . json_last_error_msg() . "\n");

echo "─── DRY RUN INFO ─────────────────────────────────────\n";
echo "Cliente:          Silvia Mesa (client_id=$clientId)\n";
echo "Plan:             Esencial\n";
echo "Coach:            admin_id=$coachId (Anderson Ardila)\n";
echo "Valid from:       $validFrom\n";
echo "Expires at:       $expiresAt\n";
echo "Semanas:          " . count($semanas) . "\n";
echo "Días por semana:  " . count($dayTemplates) . "\n";
echo "Ejercicios totales semana 1: " . array_sum(array_map(fn($d) => count($d['ejercicios']), $semanas[0]['dias'])) . "\n";
echo "Comidas nutrición: " . count($nutriPlan['comidas']) . "\n";
echo "Train JSON size:  " . strlen($trainJson) . " bytes\n";
echo "Nutri JSON size:  " . strlen($nutriJson) . " bytes\n";
echo "──────────────────────────────────────────────────────\n";

if (defined('DRY_RUN') && DRY_RUN) {
    echo "DRY_RUN activo — no se toca la base de datos. Validando JSON parseable...\n";
    $back1 = json_decode($trainJson, true);
    $back2 = json_decode($nutriJson, true);
    if ($back1 === null) die("✗ trainJson NO parseable: " . json_last_error_msg() . "\n");
    if ($back2 === null) die("✗ nutriJson NO parseable: " . json_last_error_msg() . "\n");
    echo "✓ Ambos JSON son parseables. Tipos de plan: train.plan_type=" . $back1['plan_type'] . ", nutri.plan_type=" . $back2['plan_type'] . "\n";
    echo "✓ DRY_RUN OK. Quitá la constante DRY_RUN para insertar.\n";
    return;
}

try {
    $pdo->beginTransaction();

    // 1. Desactivar planes previos de entrenamiento + nutrición (suplementación se respeta)
    $stmt = $pdo->prepare(
        "UPDATE assigned_plans SET active=0
         WHERE client_id = :cid
           AND plan_type IN ('entrenamiento','nutricion')
           AND active = 1"
    );
    $stmt->execute(['cid' => $clientId]);
    $deactivated = $stmt->rowCount();
    echo "→ Desactivados $deactivated planes previos (entrenamiento + nutricion).\n";

    // 2. Insertar los 2 nuevos
    $insert = $pdo->prepare(
        "INSERT INTO assigned_plans
            (client_id, plan_type, content, assigned_by, valid_from, expires_at, active, created_at)
         VALUES (:cid, :ptype, :content, :coach, :vfrom, :exp, 1, :now)"
    );

    $insert->execute([
        'cid'     => $clientId,
        'ptype'   => 'entrenamiento',
        'content' => $trainJson,
        'coach'   => $coachId,
        'vfrom'   => $validFrom,
        'exp'     => $expiresAt,
        'now'     => $now,
    ]);
    $trainId = $pdo->lastInsertId();

    $insert->execute([
        'cid'     => $clientId,
        'ptype'   => 'nutricion',
        'content' => $nutriJson,
        'coach'   => $coachId,
        'vfrom'   => $validFrom,
        'exp'     => $expiresAt,
        'now'     => $now,
    ]);
    $nutriId = $pdo->lastInsertId();

    $pdo->commit();
    echo "✓ INSERT OK\n";
    echo "  assigned_plans.id entrenamiento = $trainId\n";
    echo "  assigned_plans.id nutricion    = $nutriId\n";

    // 3. Invalidar caches del cliente
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
    } else {
        echo "⚠ vendor/autoload.php no disponible — ejecutá manualmente: php artisan cache:clear\n";
    }

    echo "\n✓ Plan de Silvia Mesa renovado correctamente.\n";
    echo "  Verificar en https://wellcorefitness.com/admin/clients/{$clientId} → 'Ver portal del cliente'\n";

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, "✗ ERROR: " . $e->getMessage() . "\n");
    fwrite(STDERR, $e->getTraceAsString() . "\n");
    exit(1);
}
