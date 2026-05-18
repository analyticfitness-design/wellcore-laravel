<?php
/**
 * update_julie_plan.php
 *
 * Modifica plan de entrenamiento ACTIVO de Julie Rodriguez (client_id=57).
 * UPDATE del content (no INSERT) → preserva valid_from y contador "Semana X / 4".
 *
 * Modificaciones solicitadas por Daniel (2026-05-17):
 *   Lunes:     hipthrust 4x8-10, step-up 4x10-12, PMR mancuerna 4x8-10. Resto igual.
 *   Martes:    elev.piernas captain chair 4x15, crunch pelota 4x15, plancha 3x45seg,
 *              press hombro mancuerna 4x10-12. Resto igual.
 *   Miércoles: sentadilla barra 4x10-12, prensa cerrado 4x10-12. Resto igual.
 *   Jueves:    jalón pecho máquina 4x10-12, elev.piernas acostado 3x12-15,
 *              bicicleta crunch 3x20, crunch abdominal máquina total 3x15-20.
 *              Resto igual.
 *   Viernes:   PMR barra 4x10-12, hipthrust 4x8-10, patada trasera polea 3x12-15.
 *              Resto igual.
 *   Sábado:    todo igual.
 *
 * Periodización: TODAS las semanas en "Intensificación" (sin peak ni deload).
 * RIR descendente sem 1→4: 2 → 1-2 → 1 → 0-1.
 *
 * Ejecutar en container EasyPanel:
 *   php /code/bootstrap/update_julie_plan.php
 */

if (!(defined('DRY_RUN') && DRY_RUN)) {
    $pdo = new PDO(
        'mysql:host=wellcorefitness_wellcorefitness-mysql;dbname=wellcorefitness;charset=utf8mb4',
        'wellcorefitness',
        'fYCVgn4XZ7twq34',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
}

$clientId = 57;
$gifBase  = 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/';
$g = fn(?string $a): ?string => $a === null ? null : $gifBase . $a . '.gif';

// ─── PARÁMETROS POR SEMANA (todas intensificación, sin peak ni deload) ───────
$weekParams = [
    1 => ['fase' => 'Intensificación',           'rir' => '2',   'descanso' => '75 seg'],
    2 => ['fase' => 'Intensificación progresiva', 'rir' => '1-2', 'descanso' => '75-90 seg'],
    3 => ['fase' => 'Intensificación alta',      'rir' => '1',   'descanso' => '90 seg'],
    4 => ['fase' => 'Intensificación máxima',    'rir' => '0-1', 'descanso' => '90-120 seg'],
];

// ─── DÍAS BASE: ejercicios con reps fijos (definidos por Daniel) ─────────────
// Estructura: [gif_alias, nombre, series, reps, notas, var_alias, var_nombre, overrides?]
// overrides: ['rir_override' => '—', 'descanso_override' => '45 seg']

$dayTemplates = [
    'Lunes' => [
        'grupo_muscular' => 'Glúteos, Piernas, Pantorrilla',
        'tipo'           => 'piernas',
        'calentamiento'  => '5 min en caminadora a baja intensidad + movilidad de cadera y tobillos + 2 series de hip thrust sin peso.',
        'vuelta_calma'   => '5 min: estiramiento de cuádriceps, isquios, glúteos y pantorrillas.',
        'ejercicios' => [
            // MODIFICADO: 4x8-10 (era 4x12-15)
            ['hipthrust-con-barra',                'Hip Thrust con Barra',                  4, '8-10',  'Espalda apoyada en el banco, barra en caderas con acolchado. Empujás desde los talones, apretás glúteo 1 seg arriba. Costillas abajo, no arquees lumbar.',                          'hipthrust-a-una-pierna-con-barra',     'Hip Thrust a Una Pierna con Barra'],
            // MODIFICADO: 4x10-12 (era 4x12-15)
            ['step-up-mancuerna',                  'Step Up con Mancuerna',                 4, '10-12', 'Subís un pie a la caja o banco, empujás desde el talón de la pierna delantera. No te ayudés con la pierna trasera. Hacés ambas piernas.',                                            'sentadilla-goblet',                    'Sentadilla Goblet'],
            // MODIFICADO: 4x8-10 (era 4x12-15)
            ['peso-muerto-rumano-con-mancuerna',   'Peso Muerto Rumano con Mancuerna',      4, '8-10',  'Espalda recta, rodillas levemente dobladas. Bajás las mancuernas por los muslos hasta sentir estiramiento profundo en isquios. Subís apretando glúteo.',                          'peso-muerto-rumano-con-barra',         'Peso Muerto Rumano con Barra'],
            // IGUAL
            ['patada-trasera-en-polea',            'Patada Trasera en Polea',               4, '12-15', 'Cuerpo ligeramente inclinado, cadera fija. Extendés la pierna hacia atrás apretando glúteo arriba 1 seg. NO arquees lumbar — el movimiento es de cadera, no de espalda.',          'patada-trasera-en-maquina',            'Patada Trasera en Máquina'],
            // IGUAL
            ['abduccion-de-cadera-sentado-en-maquina', 'Abducción de Cadera Sentado en Máquina', 4, '12-15', 'Espalda recta. Inclinás el torso ligeramente hacia adelante (12-15°) para activar más glúteo medio. Abrís controlada, apretás 1 seg, volvés en 2 seg.',                          'abduccion-de-cadera-de-pie-en-maquina','Abducción de Cadera de Pie en Máquina'],
            // IGUAL
            ['pantorrillas-en-prensa-de-pierna',   'Pantorrillas en Prensa de Pierna',      4, '12-15', 'Solo las puntas de los pies en el borde inferior de la plataforma. Bajás hasta estiramiento completo, subís apretando pantorrilla 1 seg arriba.',                                 'elevacion-de-talones-en-maquina',      'Elevación de Talones en Máquina'],
        ],
    ],

    'Martes' => [
        'grupo_muscular' => 'Core, Hombros, Tríceps',
        'tipo'           => 'empuje',
        'calentamiento'  => '5 min de movilidad articular + rotaciones de hombro + activación de core con los primeros 3 ejercicios de la sesión a menor intensidad.',
        'vuelta_calma'   => '5 min: estiramiento de hombros, tríceps y zona lumbar.',
        'ejercicios' => [
            // MODIFICADO: 4x15 (era 3x15)
            ['elevacion-de-piernas-captain-chair', 'Elevación de Piernas en Captain Chair', 4, '15',    'Columna apoyada en el respaldo, brazos en los apoyabrazos. Piernas rectas o ligeramente flexionadas. Subís hasta 90°, bajás controlada. Sin balanceo.', 'elevacion-de-piernas-acostado',    'Elevación de Piernas Acostada', ['descanso_override' => '45 seg']],
            // MODIFICADO: 4x15 (era 3x15)
            ['crunch-en-pelota-de-estabilidad',    'Crunch en Pelota de Estabilidad',       4, '15',    'Zona lumbar apoyada en la pelota. Contraés el abdomen y subís los hombros del soporte, apretás 1 seg arriba. Bajás controlada estirando.',          'crunch-abdominal-en-maquina-total', 'Crunch Abdominal en Máquina Total', ['descanso_override' => '45 seg']],
            // MODIFICADO: 3x45 seg (era 3x30 seg)
            ['plancha-abdominal',                  'Plancha Abdominal',                     3, '45 seg', 'Codos bajo los hombros, cuerpo recto de cabeza a talones. Apretás abdomen y glúteo. Si tiembla, vas bien. Respiración constante.',                    'plancha-lateral',                  'Plancha Lateral', ['rir_override' => '—', 'descanso_override' => '45 seg']],
            // MODIFICADO: 4x10-12 (era 4x12-15)
            ['press-de-hombro-con-mancuerna',      'Press de Hombro con Mancuerna',         4, '10-12', 'Sentada con espalda apoyada. Codos a 90° abajo, empujás hasta casi extender sin bloquear arriba. Codos ligeramente al frente, no abiertos del todo.', 'press-arnold-con-mancuerna',       'Press Arnold con Mancuerna'],
            // IGUAL
            ['remo-al-menton-con-barra',           'Remo al Mentón con Barra',              4, '12-15', 'Agarre ligeramente más cerrado que los hombros. Jalás la barra hacia el mentón con codos altos. Si te duele el hombro, andá más liviana.',           'remo-al-menton-con-mancuerna',     'Remo al Mentón con Mancuerna'],
            // IGUAL
            ['elevacion-lateral-con-mancuerna',    'Elevación Lateral con Mancuerna',       4, '12-15', 'Codos ligeramente flexionados. Subís a la altura del hombro (no más), apretás deltoides medio 1 seg arriba, bajás en 2 seg. Sin balanceo.',         'elevacion-lateral-en-polea',       'Elevación Lateral en Polea'],
            // IGUAL
            ['elevacion-posterior-con-mancuerna',  'Elevación Posterior con Mancuerna',     4, '12-15', 'Torso inclinado a 45°, brazos ligeramente doblados. Abrís los brazos hacia los lados apretando deltoides posterior arriba.',                       'elevaciones-posteriores-en-polea', 'Elevaciones Posteriores en Polea'],
            // IGUAL
            ['fondos-de-triceps-en-maquina',       'Fondos de Tríceps en Máquina',          4, '12-15', 'Torso vertical, empujás las manijas hacia abajo extendiendo los codos completamente. Apretás tríceps 1 seg abajo.',                                 'extension-de-triceps-en-polea-con-cuerda', 'Extensión de Tríceps en Polea con Cuerda'],
            // IGUAL
            ['extension-de-triceps-en-polea-con-cuerda', 'Extensión de Tríceps en Polea con Cuerda', 4, '12-15', 'Codos pegados al cuerpo, fijos — solo el antebrazo se mueve. Al final abrís la cuerda separando las manos. Apretás tríceps 1 seg abajo.', 'fondos-de-triceps-en-maquina',     'Fondos de Tríceps en Máquina'],
        ],
    ],

    'Miércoles' => [
        'grupo_muscular' => 'Cuádriceps, Pantorrilla',
        'tipo'           => 'piernas',
        'calentamiento'  => '5 min en caminadora + 2 series de 15 reps de sentadillas sin peso + movilidad de rodilla y tobillo.',
        'vuelta_calma'   => '5 min: estiramiento de cuádriceps, pantorrillas y cadera flexora.',
        'ejercicios' => [
            // MODIFICADO: 4x10-12 (era 4x12-15)
            ['sentadilla-con-barra',               'Sentadilla con Barra',                  4, '10-12', 'Barra en trapecio, pies al ancho de hombros. Bajás hasta que los muslos pasen la paralela, empujás con los talones. Pecho arriba siempre.',                            'prensa-de-piernas-cerrado',       'Prensa de Piernas'],
            // MODIFICADO: 4x10-12 (era 4x12-15)
            ['prensa-de-piernas-cerrado',          'Prensa de Piernas Cerrado',             4, '10-12', 'Pies juntos en la parte baja de la plataforma — activa más el cuádricep. Bajás hasta 90° de rodilla, empujás sin bloquear arriba.',                                     'sentadilla-con-barra',            'Sentadilla con Barra'],
            // IGUAL
            ['sentadilla-hacka',                   'Sentadilla Hack',                       4, '12-15', 'Espalda apoyada en la máquina. Pies al ancho de hombros o más cerrado para enfatizar cuádriceps. Rango completo de movimiento.',                                       'prensa-de-piernas-cerrado',       'Prensa de Piernas Cerrado'],
            // IGUAL
            ['extension-de-piernas-en-maquina',    'Extensión de Piernas en Máquina',       4, '12-15', 'Extensión completa arriba, aguantás 1 seg apretando el cuádricep. Bajás en 3 seg controlada.',                                                                          'sentadilla-hacka',                'Sentadilla Hack'],
            // IGUAL
            ['zancada-frontal-con-mancuerna',      'Zancada Frontal con Mancuerna',         4, '12-15', 'Paso hacia adelante, rodilla trasera baja sin tocar el suelo. Empujás desde el talón delantero para volver. Hacés ambas piernas.',                                       'zancada-inversa-con-mancuernas',  'Zancada Inversa con Mancuernas'],
            // IGUAL
            ['elevacion-de-talones-sentado',       'Elevación de Talones Sentado',          4, '12-15', 'Mancuernas en las rodillas como carga. Rango completo: bajás hasta estirar la pantorrilla, subís apretando 1 seg arriba.',                                              'pantorrillas-en-prensa-de-pierna','Pantorrillas en Prensa de Pierna'],
        ],
    ],

    'Jueves' => [
        'grupo_muscular' => 'Espalda, Bíceps, Core',
        'tipo'           => 'jale',
        'calentamiento'  => '5 min de movilidad articular de hombros + 2 series de jalones ligeros + activación escapular.',
        'vuelta_calma'   => '5 min: estiramiento de espalda, bíceps y abdomen.',
        'ejercicios' => [
            // MODIFICADO: 4x10-12 (era 4x12-15)
            ['jalon-al-pecho-en-maquina',          'Jalón al Pecho en Máquina',             4, '10-12', 'Agarre ligeramente más ancho que los hombros. Jalás hacia el pecho con los codos bajando y atrás. Apretás dorsales abajo 1 seg.',                                       'pulldown-en-polea',                'Pulldown en Polea'],
            // IGUAL
            ['pulldown-en-polea',                  'Pulldown en Polea',                     4, '12-15', 'Agarre prono. Apretás los omóplatos hacia abajo y atrás mientras jalás. Llevás la barra al pecho alto.',                                                                'jalon-al-pecho-agarre-cerrado',    'Jalón al Pecho Agarre Cerrado'],
            // IGUAL
            ['remo-sentado-en-polea-agarre-abierto','Remo Sentado en Polea',                4, '12-15', 'Torso erguido, jalás el agarre hacia el ombligo con los codos pegados al cuerpo. Espalda neutra, no curva.',                                                              'remo-sentado-en-maquina',          'Remo Sentado en Máquina'],
            // IGUAL
            ['curl-biceps-con-barra',              'Curl Bíceps con Barra',                 4, '12-15', 'Codos pegados al cuerpo, fijos. Subís la barra hasta los hombros contrayendo el bíceps. Bajás controlada, no sueltes el peso.',                                          'curl-biceps-barra-ez',             'Curl Bíceps con Barra EZ'],
            // IGUAL
            ['curl-martillo-con-mancuerna',        'Curl Martillo con Mancuerna',           4, '12-15', 'Agarre neutro (pulgares arriba). Trabaja el braquial y braquiorradial además del bíceps. Codos fijos al torso.',                                                          'curl-martillo-en-polea-con-cuerda','Curl Martillo en Polea con Cuerda'],
            // MODIFICADO: 3x12-15 (era 4x12-15)
            ['elevacion-de-piernas-acostado',      'Elevación de Piernas Acostada',         3, '12-15', 'Espalda plana en el suelo, manos debajo de la zona lumbar si necesitás apoyo. Piernas con leve flexión, subís a 90°, bajás controlada.',                                'elevacion-de-piernas-captain-chair','Elevación de Piernas en Captain Chair', ['descanso_override' => '45 seg']],
            // MODIFICADO: 3x20 (era 3x15)
            ['bicicleta-crunch',                   'Bicicleta Crunch',                      3, '20',    'Lento y controlado. Codo opuesto a la rodilla que sube. NO jales del cuello — la fuerza viene del abdomen.',                                                              'crunch-en-pelota-de-estabilidad',  'Crunch en Pelota de Estabilidad', ['descanso_override' => '45 seg']],
            // MODIFICADO: 3x15-20 (era 4x12-15)
            ['crunch-abdominal-en-maquina-total',  'Crunch Abdominal en Máquina Total',     3, '15-20', 'Ajustás el peso para sentir la contracción. Jalás con el abdomen, no con los brazos. Curva el tronco, no flexión de cadera.',                                            'crunch-en-polea-arrodillado',      'Crunch en Polea Arrodillado', ['descanso_override' => '45 seg']],
        ],
    ],

    'Viernes' => [
        'grupo_muscular' => 'Glúteos, Isquiotibiales',
        'tipo'           => 'piernas',
        'calentamiento'  => '5 min movilidad de cadera y columna + 2 series de puente de glúteo sin peso + activación de femoral.',
        'vuelta_calma'   => '5 min: estiramiento profundo de glúteos, isquios y cadera.',
        'ejercicios' => [
            // MODIFICADO: 4x10-12 (era 4x12-15)
            ['peso-muerto-rumano-con-barra',       'Peso Muerto Rumano con Barra',          4, '10-12', 'Espalda recta, rodillas ligeramente dobladas (no se mueven durante el ejercicio). Bajás la barra pegada a las piernas hasta estiramiento profundo en isquios.',          'peso-muerto-rumano-con-mancuerna',   'Peso Muerto Rumano con Mancuerna'],
            // MODIFICADO: 4x8-10 (era 4x12-15)
            ['hipthrust-con-barra',                'Hip Thrust con Barra',                  4, '8-10',  'Espalda apoyada en el banco, barra en caderas con acolchado. Empujás desde los talones, apretás glúteo 1 seg arriba. Costillas abajo.',                                  'hipthrust-a-una-pierna-con-barra',   'Hip Thrust a Una Pierna con Barra'],
            // IGUAL
            ['curl-femoral-acostado-en-maquina',   'Curl Femoral Acostado en Máquina',      4, '12-15', 'Contraés el femoral al subir. Bajás en 3 seg controlada. No des impulso con la cadera.',                                                                                    'curl-femoral-sentado',               'Curl Femoral Sentado'],
            // IGUAL
            ['curl-femoral-sentado',               'Curl Femoral Sentado',                  4, '12-15', 'Posición vertical activa más el bíceps femoral distal. Ajustás el respaldo para que la rodilla coincida con el eje de la máquina.',                                       'curl-femoral-acostado-en-maquina',   'Curl Femoral Acostado en Máquina'],
            // IGUAL
            ['abduccion-de-cadera-de-pie-en-maquina','Abducción de Cadera de Pie en Máquina',4, '12-15', 'Pierna de apoyo firme, empujás hacia afuera con el glúteo medio. Movimiento controlado, sin balanceo.',                                                                    'abduccion-de-cadera-sentado-en-maquina','Abducción de Cadera Sentado en Máquina'],
            // MODIFICADO: 3x12-15 (era 4x12-15)
            ['patada-trasera-en-polea',            'Patada Trasera en Polea',               3, '12-15', 'Cuerpo ligeramente inclinado, cadera fija. Extendés la pierna hacia atrás apretando glúteo arriba 1 seg. Hacés ambas piernas.',                                          'patada-trasera-en-maquina',          'Patada Trasera en Máquina'],
        ],
    ],

    'Sábado' => [
        'grupo_muscular' => 'Core, Full Body, Cardio',
        'tipo'           => 'full_body',
        'calentamiento'  => '5 min movilidad general + activación de core antes de empezar.',
        'vuelta_calma'   => '5 min de estiramientos generales post-escaladora.',
        'ejercicios' => [
            // TODO IGUAL
            ['elevacion-de-piernas-sentado',       'Elevación de Piernas Sentada',          3, '15',    'Sentada al borde del banco, apoyás los brazos. Subís las rodillas al pecho o piernas rectas a 90°. Sin balanceo.',                                                       'elevacion-de-piernas-captain-chair', 'Elevación de Piernas en Captain Chair', ['descanso_override' => '45 seg']],
            ['plancha-abdominal',                  'Plancha Abdominal',                     3, '30 seg','Codos bajo los hombros, cuerpo recto de cabeza a talones. Abdomen apretado, no dejes caer las caderas.',                                                                  'plancha-lateral',                    'Plancha Lateral', ['rir_override' => '—', 'descanso_override' => '45 seg']],
            ['sentadilla-frontal-en-landmine',     'Sentadilla Frontal en Landmine',        4, '12-15', 'Barra en el hombro o frente al pecho. Espalda erguida, rodillas en línea con los pies. Profundidad cómoda y segura.',                                                       'sentadilla-goblet',                  'Sentadilla Goblet'],
            ['puente-de-gluteo-con-mancuerna',     'Puente de Glúteo con Mancuerna',        4, '12-15', 'Acostada en el suelo, pies apoyados. Mancuerna sobre las caderas. Empujás desde los talones apretando glúteo arriba 1 seg.',                                              'hipthrust-con-barra',                'Hip Thrust con Barra'],
            ['press-militar-con-barra-de-pie',     'Press Militar con Barra de Pie',        4, '12-15', 'Core apretado, espalda neutra. Barra sale del pecho y sube sobre la cabeza con codos al frente del cuerpo.',                                                              'press-de-hombro-con-mancuerna',      'Press de Hombro con Mancuerna'],
            ['remo-con-mancuernas',                'Remo con Mancuernas',                   4, '12-15', 'Torso inclinado 45°, espalda recta. Jalás las mancuernas hacia el ombligo con los codos pegados al torso.',                                                                'remo-con-mancuernas-sobre-banco-inclinado','Remo en Banco Inclinado con Mancuernas'],
            ['escaladora',                         'Escaladora — 20 minutos',               1, '20 min','Ritmo moderado-alto constante. Resistencia que te permita mantener la intensidad. Si te falta el aire, bajás un escalón.', null, null, ['rir_override' => '—', 'descanso_override' => '-', 'is_cardio' => true]],
        ],
    ],
];

// ─── HELPER: construir ejercicio por semana ──────────────────────────────────
function buildExercise(int $weekNum, array $entry, array $weekParams, callable $g): array
{
    $wp = $weekParams[$weekNum];

    $ej = [
        'nombre'       => $entry[1],
        'gif_url'      => $g($entry[0]),
        'series'       => $entry[2],
        'repeticiones' => $entry[3],
        'descanso'     => $wp['descanso'],
        'rir'          => $wp['rir'],
        'notas'        => $entry[4],
    ];

    // Variación (si existe)
    if (!empty($entry[5])) {
        $ej['variacion'] = [
            'nombre'  => $entry[6],
            'gif_url' => $g($entry[5]),
        ];
    }

    // Overrides para abs/cardio
    if (isset($entry[7]) && is_array($entry[7])) {
        if (isset($entry[7]['rir_override']))      $ej['rir']      = $entry[7]['rir_override'];
        if (isset($entry[7]['descanso_override'])) $ej['descanso'] = $entry[7]['descanso_override'];
        if (!empty($entry[7]['is_cardio']))        $ej['is_cardio'] = true;
    }

    return $ej;
}

// ─── CONSTRUIR SEMANAS ───────────────────────────────────────────────────────
$diaIndexBase = ['Lunes' => 1, 'Martes' => 2, 'Miércoles' => 3, 'Jueves' => 4, 'Viernes' => 5, 'Sábado' => 6];
$semanas = [];

$notaFase = [
    1 => 'Semana 1 de intensificación. Cargás un poco menos del máximo (RIR 2: te queda margen de 2 reps). Foco en técnica perfecta antes de empujar más.',
    2 => 'Subís un 5% la carga respecto a la semana 1. RIR 1-2 (te queda 1-2 reps en el tanque). Si la técnica se mantiene impecable, dale.',
    3 => 'Subís otro 5% la carga. RIR 1 (te queda 1 rep). Acá empezás a sentir la intensidad. Descansos un poquito más largos para recuperar entre series.',
    4 => 'Última semana del bloque. Carga máxima del mes. RIR 0-1 (el último set te queda al fallo). NO empujés al fallo en compuestos pesados (sentadilla, peso muerto, hip thrust) — fallo solo en aislamientos.',
];

for ($w = 1; $w <= 4; $w++) {
    $wp = $weekParams[$w];
    $dias = [];

    foreach ($dayTemplates as $diaNombre => $tpl) {
        $ejs = [];
        foreach ($tpl['ejercicios'] as $entry) {
            $ejs[] = buildExercise($w, $entry, $weekParams, $g);
        }

        $dias[] = [
            'dia'               => $diaIndexBase[$diaNombre],
            'dia_semana'        => $diaNombre,
            'nombre'            => $diaNombre . ' — ' . $tpl['grupo_muscular'],
            'grupo_muscular'    => $tpl['grupo_muscular'],
            'tipo'              => $tpl['tipo'],
            'duracion_estimada' => $diaNombre === 'Sábado' ? '60-75 min' : '75-90 min',
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

// ─── BUSCAR PLAN ACTIVO PARA PRESERVAR FECHAS ────────────────────────────────
if (!(defined('DRY_RUN') && DRY_RUN)) {
    $stmt = $pdo->prepare("SELECT id, valid_from, expires_at FROM assigned_plans WHERE client_id=? AND plan_type='entrenamiento' AND active=1 ORDER BY id DESC LIMIT 1");
    $stmt->execute([$clientId]);
    $current = $stmt->fetch(PDO::FETCH_OBJ);
    if (!$current) {
        die("✗ No existe plan de entrenamiento activo para client_id=$clientId. Abortando.\n");
    }
    $planId    = $current->id;
    $validFrom = $current->valid_from;
    $expiresAt = $current->expires_at;
} else {
    $planId    = 'DRY_RUN';
    $validFrom = '2026-05-19';
    $expiresAt = '2026-06-16';
}

// ─── PLAN ENTRENAMIENTO COMPLETO ─────────────────────────────────────────────
$trainPlan = [
    'plan_type'        => 'entrenamiento',
    'titulo'           => 'Plan Esencial Entrenamiento — Mes 2 (Intensificación) — Julie Rodriguez',
    'programa'         => 'Hipertrofia y fuerza · Body Part Split 6 días · 4 semanas de intensificación pura',
    'cliente'          => 'Julie Rodriguez',
    'plan'             => 'Esencial',
    'objetivo'         => 'Hipertrofia y fuerza funcional — Mes 2 con foco en glúteo, piernas y core. Bloque de intensificación pura (sin peak ni deload).',
    'genero'           => 'Femenino',
    'nivel'            => 'Intermedio',
    'metodologia'      => 'Body Part Split 6 días · Intensificación lineal (sin deload intra-bloque)',
    'frecuencia'       => '6 días por semana',
    'frecuencia_dias'  => 6,
    'duracion_sesion'  => '75-90 minutos',
    'equipamiento'     => 'Gimnasio completo',
    'duracion_semanas' => 4,
    'fecha_inicio'     => $validFrom,
    'fecha_fin'        => $expiresAt,

    'split' => [
        'Lunes'     => 'Glúteos, Piernas, Pantorrilla',
        'Martes'    => 'Core, Hombros, Tríceps',
        'Miércoles' => 'Cuádriceps, Pantorrilla',
        'Jueves'    => 'Espalda, Bíceps, Core',
        'Viernes'   => 'Glúteos, Isquiotibiales',
        'Sábado'    => 'Full Body + Cardio',
    ],

    'tecnicas_avanzadas' => [
        'Sobrecarga progresiva — subís 2.5-5 kg cada semana en los compuestos cuando completés las reps con técnica impecable.',
        'Reps en reserva (RIR) — cada semana baja un escalón el margen hasta llegar al fallo controlado en la semana 4 (solo aislamientos).',
        'Tempo controlado — 2 seg bajada, 1 seg pausa según el ejercicio, 1 seg subida. Sin rebotes.',
    ],
    'principios' => [
        'tecnica_primero'   => 'La técnica perfecta precede a cualquier carga. Si la forma se rompe, bajás el peso.',
        'sobrecarga'        => 'Subís carga semanalmente cuando complets todas las reps con margen (RIR).',
        'registro'          => 'Anotás pesos y reps de cada sesión en la app. Sin registro no hay progresión.',
        'descanso_completo' => 'Respetás el descanso entre series. Si descansás de menos perdés fuerza para el siguiente set.',
    ],

    'semanas'         => $semanas,
    'notas_generales' => 'Bloque de intensificación pura: las 4 semanas son progresivas en carga y RIR, sin peak ni semana de descarga. Cardio adicional opcional 1-2 días por semana (15-20 min ligero), no obligatorio.',
    'notas_coach'     => "Julie, este es el Mes 2 con ajustes que afinamos en tu última sesión. El esqueleto del split es el mismo (lunes glúteo/pierna, martes core/hombro/tríceps, miércoles cuádriceps, jueves espalda/bíceps/core, viernes posterior, sábado full body), pero modifiqué reps en los ejercicios clave para llevarte a un rango de fuerza más bajo (8-10 / 10-12) en los compuestos pesados — hip thrust, peso muerto rumano, sentadilla con barra, jalón al pecho.\n\nLas 4 semanas son TODAS de intensificación. No hay semana de peak ni de descarga este mes. Eso significa que cada semana subís un poquito la carga y bajás el margen (RIR) hasta llegar a 0-1 en la semana 4. NO empujés al fallo en compuestos pesados (sentadilla, peso muerto, hip thrust con barra) — fallo solo en aislamientos.\n\nDos reglas que no se negocian este mes: registrás cada sesión (peso y reps) en la app, y respetás los descansos. Si descansás de menos, el siguiente set lo hacés con menos fuerza y se cae la progresión. 75-90 seg en pesado, 45 seg en abs.\n\nSi un día amanecés muerta, NO te saltés el entreno: bajás carga 15% y hacés la sesión completa igual. Cualquier duda me escribís. Vamos.",
];

// ─── ENCODE Y UPDATE ─────────────────────────────────────────────────────────
$trainJson = json_encode($trainPlan, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if ($trainJson === false) die("ERROR encoding: " . json_last_error_msg() . "\n");

echo "─── DRY RUN INFO ─────────────────────────────────────\n";
echo "Cliente:          Julie Rodriguez (client_id=$clientId)\n";
echo "Plan ID:          $planId\n";
echo "Valid from:       $validFrom\n";
echo "Expires at:       $expiresAt\n";
echo "Semanas:          " . count($semanas) . "\n";
echo "Días por semana:  " . count($dayTemplates) . "\n";
echo "Ejercicios w1:    " . array_sum(array_map(fn($d) => count($d['ejercicios']), $semanas[0]['dias'])) . "\n";
echo "Train JSON size:  " . strlen($trainJson) . " bytes\n";
echo "──────────────────────────────────────────────────────\n";

if (defined('DRY_RUN') && DRY_RUN) {
    $back = json_decode($trainJson, true);
    if ($back === null) die("✗ JSON NO parseable: " . json_last_error_msg() . "\n");
    echo "✓ JSON parseable. plan_type=" . $back['plan_type'] . "\n";
    echo "✓ DRY_RUN OK.\n";
    return;
}

try {
    $stmt = $pdo->prepare("UPDATE assigned_plans SET content = :content WHERE id = :id");
    $stmt->execute(['content' => $trainJson, 'id' => $planId]);
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
    } else {
        echo "⚠ vendor/autoload.php no disponible — ejecutá manualmente: php artisan cache:clear\n";
    }

    echo "\n✓ Plan de Julie Rodriguez modificado correctamente.\n";

} catch (Throwable $e) {
    fwrite(STDERR, "✗ ERROR: " . $e->getMessage() . "\n");
    exit(1);
}
