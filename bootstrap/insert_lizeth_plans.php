<?php
/**
 * insert_lizeth_plans.php
 * Inserta 3 planes (entrenamiento + nutricion + suplementacion) para
 * Lizeth Tatiana Chávez Díaz (client_id=98). Plan Esencial.
 * Periodización 4 semanas, déficit agresivo 1650 kcal, split 6 días.
 *
 * Ejecutar en container EasyPanel:
 *   php /code/bootstrap/insert_lizeth_plans.php
 */

if (!(defined('DRY_RUN') && DRY_RUN)) {
    $pdo = new PDO(
        'mysql:host=wellcorefitness_wellcorefitness-mysql;dbname=wellcorefitness;charset=utf8mb4',
        'wellcorefitness',
        'fYCVgn4XZ7twq34',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
}

$clientId  = 98;
$coachId   = 7;
$validFrom = '2026-05-18';
$expiresAt = '2026-06-15';
$now       = date('Y-m-d H:i:s');
$gifBase   = 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/';
$g = fn(string $a): string => $gifBase . $a . '.gif';

// ─── PERIODIZACIÓN 4 SEMANAS ─────────────────────────────────────────────────
// Mujer intermedia en déficit: foco hipertrofia + preservación. RIR mínimo 1
// en compuestos (no llevamos al fallo durante déficit prolongado).
$weekParams = [
    1 => ['fase' => 'Adaptación',  'series' => 3, 'repeticiones' => '12-15', 'rir' => '3',   'descanso' => '60 seg'],
    2 => ['fase' => 'Hipertrofia', 'series' => 4, 'repeticiones' => '10-12', 'rir' => '2',   'descanso' => '75 seg'],
    3 => ['fase' => 'Fuerza',      'series' => 4, 'repeticiones' => '8-10',  'rir' => '1-2', 'descanso' => '90 seg'],
    4 => ['fase' => 'Peak',        'series' => 4, 'repeticiones' => '6-8',   'rir' => '1',   'descanso' => '90-120 seg'],
];

// Abs y cardio HIIT tienen progresión propia por semana
$planchaTime  = [1 => '30 seg', 2 => '40 seg', 3 => '50 seg', 4 => '60 seg'];
$absReps      = [1 => '15 reps', 2 => '18 reps', 3 => '20 reps', 4 => '25 reps'];
$hiitRounds   = [1 => 6, 2 => 7, 3 => 8, 4 => 10];

// ─── DÍAS BASE: ejercicios principales (cada uno con variación) ──────────────
// Estructura: [gif_url, nombre, notas, variacion_alias, variacion_nombre, override?]
// override = ['reps' => '...', 'descanso' => '...'] para abs/cardio que no siguen weekParams

$dayTemplates = [
    'Lunes' => [
        'grupo_muscular' => 'Glúteo + Cardio',
        'tipo'           => 'legs',
        'calentamiento'  => '8 min: 5 min bicicleta + 2 series de 15 puentes de glúteo sin peso + activación con banda (caminata lateral 20 pasos cada lado).',
        'vuelta_calma'   => '5 min: estiramiento de glúteo (figura 4) 30 seg cada lado + estiramiento de cuádriceps de pie 30 seg cada lado + isquios sentada.',
        'ejercicios'     => [
            // [gif, nombre, notas, variacion_alias, variacion_nombre]
            [$g('hipthrust-con-barra'),                'Hip thrust con barra',                'Espalda apoyada en banco, barra acolchada en caderas. Empuja desde talones, aprieta glúteo 1 seg arriba. Costillas abajo.',          'puente-de-gluteo-con-barra',                'Puente de glúteo con barra (en suelo)'],
            [$g('peso-muerto-rumano-con-barra'),       'Peso muerto rumano con barra',        'Rodillas semiflexionadas. Baja la barra pegada a las piernas hasta sentir estiramiento en isquios. Glúteos al subir.',           'peso-muerto-rumano-con-mancuerna',          'Peso muerto rumano con mancuerna'],
            [$g('sentadilla-bulgara-mancuerna'),       'Sentadilla búlgara con mancuerna',    'Pie trasero en banco, mancuerna a cada lado. Baja recta, empuja con el talón delantero. Haz ambas piernas.',                   'sentadilla-bulgara-barra',                  'Sentadilla búlgara con barra'],
            [$g('patada-trasera-en-maquina'),          'Patada de glúteo en máquina',         'Apóyate al frente. Patada controlada hacia atrás, aprieta glúteo arriba 1 seg. No arquees la espalda.',                          'patada-trasera-en-polea',                   'Patada trasera en polea'],
            [$g('zancada-curtsy-con-mancuerna'),       'Zancada curtsy con mancuerna',        'Pierna trasera cruza detrás de la delantera (como reverencia). Activa glúteo medio y externo. Haz ambas piernas.',           'zancada-curtsy-con-barra',                  'Zancada curtsy con barra'],
        ],
        'cardio' => [
            'nombre'       => 'Escaladora',
            'gif_url'      => $g('escaladora'),
            'duracion_min' => 25,
            'momento'      => 'Después de las pesas',
            'notas'        => '25 min a ritmo constante. Mantén la cadencia donde puedas hablar pero no cantar (zona 2). FC 130-145 bpm aprox.',
            'variacion'    => [
                'nombre'  => 'Caminadora inclinada (12% inclinación, 5.5 km/h)',
                'gif_url' => $g('caminadora-inclinada'),
            ],
        ],
    ],

    'Martes' => [
        'grupo_muscular' => 'Hombro + Tríceps + Abs + HIIT',
        'tipo'           => 'upper',
        'calentamiento'  => '7 min: rotaciones de hombro (10 cada dirección) + 2 series de 12 elevaciones laterales con mancuernas livianas + 10 rotaciones externas con banda.',
        'vuelta_calma'   => '5 min: estiramiento de deltoides cruzado 30 seg cada lado + tríceps por encima de la cabeza 30 seg cada lado.',
        'ejercicios'     => [
            [$g('press-de-hombro-con-mancuerna'),      'Press de hombro con mancuerna',       'Sentada con respaldo, codos a 90° abajo. Empuja sin bloquear arriba. Costillas hacia abajo.',                                  'press-de-hombro-en-maquina-sentado',        'Press de hombro en máquina sentada'],
            [$g('elevacion-lateral-con-mancuerna'),    'Elevación lateral con mancuerna',     'Codos ligeramente flexionados. Sube hasta la altura del hombro, no más. Baja en 2 seg.',                                     'elevaciones-laterales-en-maquina',          'Elevaciones laterales en máquina'],
            [$g('elevacion-posterior-con-mancuerna'),  'Elevación posterior con mancuerna',   'Torso inclinado 45°. Lleva las mancuernas a los lados con palmas hacia abajo. Aprieta deltoide posterior.',                  'apertura-posteriores-sentado-en-maquina',   'Apertura posterior sentada en máquina'],
            [$g('press-frances-con-barra-ez'),         'Press francés con barra EZ',          'Acostada en banco, codos pegados apuntando arriba. Baja la barra a la frente. Solo el antebrazo se mueve.',                  'press-frances-barra-acostado',              'Press francés barra recto acostada'],
            [$g('extension-de-triceps-en-polea-con-cuerda'), 'Extensión de tríceps en polea con cuerda', 'Codos pegados al cuerpo. Abre la cuerda al final del movimiento, aprieta tríceps abajo.',                              'extension-de-triceps-en-maquina',           'Extensión de tríceps en máquina'],
            [$g('patada-de-triceps-con-mancuerna'),    'Patada de tríceps con mancuerna',     'Torso paralelo al piso, codo fijo arriba. Extiende y aprieta tríceps 1 seg. No balancear.',                                   'patada-de-triceps-en-polea',                'Patada de tríceps en polea'],
            // Abs (2 ejercicios con progresión propia)
            ['__abs1__'],
            ['__abs2__'],
        ],
        'cardio' => [
            'nombre'       => 'HIIT 20 min — Circuito intervalos',
            'gif_url'      => $g('escaladores'),
            'duracion_min' => 20,
            'momento'      => 'Cierre del día',
            'notas'        => 'Formato 30 seg trabajo / 30 seg descanso. Rotar 4 estaciones: jumping jacks, escaladores, salto cuerda y sentadilla con salto. Haz las rondas que indica la semana.',
            'is_hiit'      => true,
            'variacion'    => [
                'nombre'  => 'HIIT en escaladora (30s alta intensidad / 30s baja)',
                'gif_url' => $g('escaladora'),
            ],
        ],
    ],

    'Miércoles' => [
        'grupo_muscular' => 'Cuádriceps + Aductor + Gemelos + Cardio',
        'tipo'           => 'legs',
        'calentamiento'  => '8 min: 5 min bicicleta + 2 series de 15 sentadillas sin peso + 10 elevaciones de talones en cada lado.',
        'vuelta_calma'   => '5 min: estiramiento de cuádriceps de pie 30 seg cada lado + aductor en mariposa + gemelo apoyada en pared.',
        'ejercicios'     => [
            [$g('sentadilla-con-barra'),               'Sentadilla con barra',                'Pies al ancho de hombros, baja a 90° o más si tu movilidad lo permite. Rodillas alineadas con los pies.',                       'sentadilla-hacka',                          'Sentadilla en hack squat'],
            [$g('presa-de-piernas-abierto'),           'Prensa de piernas (postura abierta)',  'Pies arriba en la plataforma, separación más ancha que hombros, puntas afuera. Foco en cuádriceps interno y aductor.',         'prensa-de-piernas-cerrado',                 'Prensa de piernas (postura cerrada)'],
            [$g('extension-de-piernas-en-maquina'),    'Extensión de piernas en máquina',     'Extensión completa arriba, aguanta 1 seg. Baja en 3 seg. No uses impulso con la cadera.',                                     'sentadilla-isometrica',                     'Sentadilla isométrica en pared'],
            [$g('zancada-frontal-con-mancuerna'),      'Zancada frontal con mancuerna',       'Alterna piernas. Rodilla trasera baja sin tocar el suelo. Tronco erguido.',                                                   'zancada-dinamica',                          'Zancada dinámica caminando'],
            [$g('aduccion-de-cadera-sentado-en-maquina'), 'Aductor sentado en máquina',       'Sentada con espalda apoyada. Cierra las piernas con rango completo. Aprieta el aductor al juntar 1 seg.',                       'aduccion-de-cadera-en-polea',               'Aducción de cadera en polea'],
            [$g('elevacion-de-talones-en-maquina'),    'Elevación de talones de pie en máquina', 'De pie, hombros bajo las almohadillas. Sube en la punta del pie, aprieta gemelo 1 seg arriba. Rango completo.',           'elevacion-de-talones-con-mancuerna',        'Elevación de talones con mancuerna'],
        ],
        'cardio' => [
            'nombre'       => 'Escaladora',
            'gif_url'      => $g('escaladora'),
            'duracion_min' => 25,
            'momento'      => 'Después de las pesas',
            'notas'        => '25 min ritmo constante zona 2. Si el cuádriceps quedó muy quemado, baja la intensidad — el cardio no es la sesión.',
            'variacion'    => [
                'nombre'  => 'Caminadora inclinada (12% inclinación, 5.5 km/h)',
                'gif_url' => $g('caminadora-inclinada'),
            ],
        ],
    ],

    'Jueves' => [
        'grupo_muscular' => 'Espalda + Bíceps + Abs + HIIT',
        'tipo'           => 'upper',
        'calentamiento'  => '7 min: 5 min remo ergómetro suave o cinta + 2 series de 12 pull-aparts con banda + 10 jalones bajos con poco peso.',
        'vuelta_calma'   => '5 min: estiramiento de dorsal (brazo arriba inclinada a un lado) + bíceps en marco de puerta + cuello.',
        'ejercicios'     => [
            [$g('jalon-al-pecho-en-maquina'),          'Jalón al pecho en máquina',           'Agarre amplio, lleva la barra al esternón. Aprieta omóplatos 1 seg. Tronco ligeramente atrás.',                                'jalon-al-pecho-agarre-cerrado',             'Jalón al pecho agarre cerrado'],
            [$g('remo-sentado-en-maquina'),            'Remo sentado en máquina',             'Pecho contra el almohadillado, jala con codos cercanos al cuerpo hasta el abdomen. Aprieta omóplatos.',                       'remo-en-polea-sentado',                     'Remo sentado en polea'],
            [$g('remo-con-mancuerna-a-una-mano'),      'Remo a una mano con mancuerna',       'Rodilla y mano del lado contrario en banco. Jala la mancuerna hacia la cadera, codo cerca del cuerpo.',                       'remo-con-mancuernas',                       'Remo bilateral con mancuernas'],
            [$g('pullover-con-mancuerna'),             'Pullover con mancuerna',              'Acostada en banco con brazos casi rectos. Baja la mancuerna detrás de la cabeza. Trabaja dorsal y serrato.',                  'pullover-en-polea-con-cuerda',              'Pullover en polea con cuerda'],
            [$g('curl-biceps-con-mancuerna'),          'Curl de bíceps con mancuerna',        'Codos pegados al cuerpo. Supina la muñeca al subir. Alterna o bilateral según prefieras.',                                  'curl-biceps-con-barra',                     'Curl de bíceps con barra'],
            [$g('curl-predicador-en-maquina'),         'Curl predicador en máquina',          'Brazos apoyados en el cojín. Rango completo, baja en 2 seg sin dejar caer.',                                                 'curl-predicador-con-barra-ez',              'Curl predicador con barra EZ'],
            [$g('curl-martillo-con-mancuerna'),        'Curl martillo con mancuerna',         'Agarre neutro (pulgares arriba). Trabaja braquial además del bíceps.',                                                       'curl-martillo-en-polea-con-cuerda',         'Curl martillo en polea con cuerda'],
            ['__abs1__'],
            ['__abs2__'],
        ],
        'cardio' => [
            'nombre'       => 'HIIT 20 min — Circuito intervalos',
            'gif_url'      => $g('escaladores'),
            'duracion_min' => 20,
            'momento'      => 'Cierre del día',
            'notas'        => 'Formato 30 seg trabajo / 30 seg descanso. Rotar 4 estaciones: jumping jacks, escaladores, salto cuerda y sentadilla con salto. Haz las rondas que indica la semana.',
            'is_hiit'      => true,
            'variacion'    => [
                'nombre'  => 'HIIT en escaladora (30s alta intensidad / 30s baja)',
                'gif_url' => $g('escaladora'),
            ],
        ],
    ],

    'Viernes' => [
        'grupo_muscular' => 'Glúteo + Femoral + Gemelos + Cardio',
        'tipo'           => 'legs',
        'calentamiento'  => '8 min: 5 min bicicleta + 2 series de 15 puentes de glúteo + caminata lateral con banda 20 pasos cada lado.',
        'vuelta_calma'   => '5 min: estiramiento de isquios (sentada y de pie) + glúteo en figura 4 + gemelo en pared.',
        'ejercicios'     => [
            [$g('hipthrust-a-una-pierna-con-barra'),   'Hip thrust a una pierna con barra',   'Misma posición que el bilateral pero con una pierna apoyada. Si pesa mucho, comienza sin peso o con barra vacía.',           'puente-de-gluteo-con-mancuerna',            'Puente de glúteo con mancuerna'],
            [$g('peso-muerto-pierna-rigida-con-mancuerna'), 'Peso muerto pierna rígida con mancuerna', 'Rodillas casi rectas (suaves). Baja lento sintiendo isquios estirar. Sube apretando glúteo.',                              'peso-muerto-rumano-en-landmine',            'Peso muerto rumano en landmine'],
            [$g('curl-femoral-acostado-en-maquina'),   'Curl femoral acostado en máquina',    'Boca abajo, talones detrás. Lleva los talones hacia el glúteo, aprieta isquios 1 seg arriba.',                                'curl-femoral-sentado',                      'Curl femoral sentado en máquina'],
            [$g('curl-femoral-arrodillado-en-maquina'), 'Curl femoral arrodillado',           'Una pierna a la vez. Mejor activación unilateral del isquio. Haz ambas.',                                                  'curl-femora-en-polea',                      'Curl femoral en polea'],
            [$g('patada-lateral-en-polea'),            'Patada lateral en polea (glúteo medio)', 'Pierna externa hacia el lado, controlado. Aprieta glúteo medio arriba 1 seg.',                                            'abduccion-de-cadera-de-pie-en-maquina',     'Abducción de cadera de pie en máquina'],
            [$g('zancada-inversa-con-mancuernas'),     'Zancada inversa con mancuernas',      'Paso hacia atrás (más amable con la rodilla). Rodilla trasera no toca el suelo. Empuja desde el talón delantero.',           'zancada',                                   'Zancada estática alterna'],
            [$g('elevacion-de-talones-en-maquina'),    'Elevación de talones de pie en máquina', 'De pie. Rango completo: baja hasta estirar el gemelo, sube hasta la punta del pie. Aguanta arriba 1 seg.',                  'pantorrillas-en-prensa-de-pierna',          'Gemelos en prensa de piernas'],
        ],
        'cardio' => [
            'nombre'       => 'Escaladora',
            'gif_url'      => $g('escaladora'),
            'duracion_min' => 25,
            'momento'      => 'Después de las pesas',
            'notas'        => '25 min ritmo constante zona 2. Cierre suave de la semana de pesas.',
            'variacion'    => [
                'nombre'  => 'Caminadora inclinada (12% inclinación, 5.5 km/h)',
                'gif_url' => $g('caminadora-inclinada'),
            ],
        ],
    ],

    'Sábado' => [
        'grupo_muscular' => 'Cardio HIIT puro',
        'tipo'           => 'cardio',
        'calentamiento'  => '5 min: trote suave en sitio + rotaciones articulares + 20 jumping jacks lentos.',
        'vuelta_calma'   => '5 min caminando + estiramiento general de piernas y core.',
        'ejercicios'     => [
            // Bloque circuito de 4 ejercicios, rotativo, 30 min total
            ['__hiit_jj__'],
            ['__hiit_sc__'],
            ['__hiit_jc__'],
            ['__hiit_mc__'],
        ],
    ],
];

// ─── HELPERS para construir ejercicios por semana ────────────────────────────

function buildExercise(int $weekNum, array $entry, array $weekParams, array $planchaTime, array $absReps, array $hiitRounds, callable $g): array
{
    // Tokens especiales para abs / HIIT que tienen progresión propia
    if (count($entry) === 1) {
        $token = $entry[0];

        if ($token === '__abs1__') {
            return [
                'nombre'        => 'Plancha abdominal',
                'gif_url'       => $g('plancha-abdominal'),
                'series'        => 3,
                'repeticiones'  => $planchaTime[$weekNum],
                'descanso'      => '45 seg',
                'rir'           => '—',
                'notas'         => 'Codos bajo los hombros, cuerpo recto. Aprieta abdomen y glúteo. Si tiembla está bien.',
                'variacion'     => [
                    'nombre'  => 'Plancha lateral (alterna lados)',
                    'gif_url' => $g('plancha-lateral'),
                ],
            ];
        }
        if ($token === '__abs2__') {
            return [
                'nombre'        => 'Bicicleta crunch',
                'gif_url'       => $g('bicicleta-crunch'),
                'series'        => 3,
                'repeticiones'  => $absReps[$weekNum] . ' c/lado',
                'descanso'      => '45 seg',
                'rir'           => '2',
                'notas'         => 'Lenta y controlada. Codo al lado contrario de la rodilla. No jales del cuello.',
                'variacion'     => [
                    'nombre'  => 'Crunch codo a rodilla',
                    'gif_url' => $g('crunch-codo-a-rodilla'),
                ],
            ];
        }
        // Estaciones del HIIT del sábado
        $rounds = $hiitRounds[$weekNum];
        if ($token === '__hiit_jj__') {
            return [
                'nombre'        => 'Jumping jacks',
                'gif_url'       => $g('jumping-jack'),
                'series'        => $rounds,
                'repeticiones'  => '30 seg',
                'descanso'      => '15 seg (pasar a siguiente)',
                'rir'           => '—',
                'bloque'        => 'circuito',
                'grupo_id'      => 'HIIT_SAB',
                'rondas'        => $rounds,
                'is_cardio'     => true,
                'notas'         => 'Estación 1 de 4. Ritmo alto, brazos extendidos arriba.',
            ];
        }
        if ($token === '__hiit_sc__') {
            return [
                'nombre'        => 'Salto de cuerda',
                'gif_url'       => $g('salto-cuerda'),
                'series'        => $rounds,
                'repeticiones'  => '30 seg',
                'descanso'      => '15 seg (pasar a siguiente)',
                'rir'           => '—',
                'bloque'        => 'circuito',
                'grupo_id'      => 'HIIT_SAB',
                'rondas'        => $rounds,
                'is_cardio'     => true,
                'notas'         => 'Estación 2 de 4. Si no tienes cuerda, simula el movimiento.',
            ];
        }
        if ($token === '__hiit_jc__') {
            return [
                'nombre'        => 'Escaladores (mountain climbers)',
                'gif_url'       => $g('escaladores'),
                'series'        => $rounds,
                'repeticiones'  => '30 seg',
                'descanso'      => '15 seg (pasar a siguiente)',
                'rir'           => '—',
                'bloque'        => 'circuito',
                'grupo_id'      => 'HIIT_SAB',
                'rondas'        => $rounds,
                'is_cardio'     => true,
                'notas'         => 'Estación 3 de 4. En posición de plancha, alterna rodillas al pecho rápido.',
            ];
        }
        if ($token === '__hiit_mc__') {
            return [
                'nombre'        => 'Sentadilla con salto',
                'gif_url'       => $g('sentadilla-con-mancuernas'),
                'series'        => $rounds,
                'repeticiones'  => '30 seg',
                'descanso'      => '60 seg (fin de ronda)',
                'rir'           => '—',
                'bloque'        => 'circuito',
                'grupo_id'      => 'HIIT_SAB',
                'rondas'        => $rounds,
                'is_cardio'     => true,
                'notas'         => 'Estación 4 de 4. Sin peso (bodyweight). Baja a 90° y explota arriba con salto.',
            ];
        }
    }

    // Ejercicio normal: [gif, nombre, notas, variacion_alias, variacion_nombre]
    $wp = $weekParams[$weekNum];
    return [
        'nombre'        => $entry[1],
        'gif_url'       => $entry[0],
        'series'        => $wp['series'],
        'repeticiones'  => $wp['repeticiones'],
        'descanso'      => $wp['descanso'],
        'rir'           => $wp['rir'],
        'notas'         => $entry[2],
        'variacion'     => isset($entry[3]) ? [
            'nombre'  => $entry[4],
            'gif_url' => $g($entry[3]),
        ] : null,
    ];
}

// ─── CONSTRUIR SEMANAS ───────────────────────────────────────────────────────

$diaIndexBase = ['Lunes' => 1, 'Martes' => 2, 'Miércoles' => 3, 'Jueves' => 4, 'Viernes' => 5, 'Sábado' => 6];
$semanas = [];

for ($w = 1; $w <= 4; $w++) {
    $wp = $weekParams[$w];
    $dias = [];

    foreach ($dayTemplates as $diaNombre => $tpl) {
        $ejs = [];
        foreach ($tpl['ejercicios'] as $entry) {
            $ejs[] = buildExercise($w, $entry, $weekParams, $planchaTime, $absReps, $hiitRounds, $g);
        }

        $dia = [
            'dia'                => $diaIndexBase[$diaNombre],
            'dia_semana'         => $diaNombre,
            'nombre'             => $diaNombre . ' — ' . $tpl['grupo_muscular'],
            'grupo_muscular'     => $tpl['grupo_muscular'],
            'tipo'               => $tpl['tipo'],
            'duracion_estimada'  => $diaNombre === 'Sábado' ? '40 min' : '90 min',
            'calentamiento'      => $tpl['calentamiento'],
            'vuelta_calma'       => $tpl['vuelta_calma'],
            'ejercicios'         => $ejs,
        ];

        if (isset($tpl['cardio'])) {
            $dia['cardio'] = $tpl['cardio'];
        }

        $dias[] = $dia;
    }

    // Nota semanal por fase
    $notaFase = [
        1 => 'Semana de adaptación. Foco TÉCNICA: aprende los movimientos con peso moderado, RIR 3 (te quedan 3 reps en el tanque). Cargas conservadoras esta semana.',
        2 => 'Hipertrofia. Subes carga un poco respecto a la semana 1 y reduces reps. Mantén la técnica impecable. RIR 2.',
        3 => 'Fuerza. Bajas reps, subes carga. Descansos más largos. RIR 1-2 en compuestos. Si la técnica se rompe, baja el peso.',
        4 => 'Peak. Última semana del bloque. Carga más alta, reps más bajas. RIR 1. NO al fallo en compuestos (estás en déficit, riesgo de lesión). Este es el bloque donde se ve el cambio en el espejo.',
    ];

    $semanas[] = [
        'numero'       => $w,
        'semana'       => $w,
        'fase'         => $wp['fase'],
        'fase_nombre'  => 'Semana ' . $w . ' — ' . $wp['fase'],
        'nombre_bloque'=> 'Semana ' . $w . ' — ' . $wp['fase'],
        'rpe_objetivo' => $w === 1 ? '7' : ($w === 2 ? '8' : ($w === 3 ? '8.5' : '9')),
        'nota_semana'  => $notaFase[$w],
        'dias'         => $dias,
    ];
}

// ─── PLAN ENTRENAMIENTO ─────────────────────────────────────────────────────

$trainPlan = [
    'plan_type'           => 'entrenamiento',
    'titulo'              => 'Plan Esencial Entrenamiento — Lizeth Chávez',
    'programa'            => 'Pérdida de Grasa con Foco en Glúteo + Hipertrofia 4 semanas',
    'cliente'             => 'Lizeth Tatiana Chávez Díaz',
    'plan'                => 'Esencial',
    'objetivo'            => 'Pérdida de grasa con preservación de masa muscular y foco estético en glúteo, pierna y core.',
    'genero'              => 'Femenino',
    'nivel'               => 'Intermedio',
    'metodologia'         => 'Body Part Split 5 días + HIIT sábado · Periodización lineal Adaptación → Hipertrofia → Fuerza → Peak',
    'frecuencia'          => '6 días por semana (5 pesas + 1 HIIT)',
    'frecuencia_dias'     => 6,
    'duracion_sesion'     => '90 minutos (pesas) · 40 min (sábado HIIT)',
    'equipamiento'        => 'Gimnasio completo',
    'duracion_semanas'    => 4,
    'peso_cliente'        => '75 kg',
    'estatura'            => '165 cm',
    'fecha_inicio'        => $validFrom,
    'fecha_fin'           => $expiresAt,

    // ⭐ HORARIO SEMANAL (alimenta el grid de días en el tab)
    'split' => [
        'Lunes'     => 'Glúteo + Escaladora 25 min',
        'Martes'    => 'Hombro + Tríceps + Abs + HIIT 20 min',
        'Miércoles' => 'Cuádriceps + Aductor + Gemelos + Escaladora 25 min',
        'Jueves'    => 'Espalda + Bíceps + Abs + HIIT 20 min',
        'Viernes'   => 'Glúteo + Femoral + Gemelos + Escaladora 25 min',
        'Sábado'    => 'Cardio HIIT 30 min',
    ],

    'tecnicas_avanzadas' => [
        'Sobrecarga progresiva — añade 1.25-2.5 kg o 1-2 reps cada semana en los compuestos',
        'Tempo controlado — bajada 2-3 seg, subida explosiva en los aislamientos de glúteo',
        'Pausa de contracción — aguanta 1 seg arriba en hip thrust, patadas y elevaciones laterales',
        'Variación de ejercicio — cada movimiento tiene una alternativa: úsala si la máquina está ocupada o quieres romper meseta',
    ],
    'principios' => [
        'sobrecarga_progresiva' => 'Mover el peso un poco hacia arriba cada semana es lo que produce el cambio. No te quedes estancada.',
        'tecnica_primero'       => 'La técnica perfecta precede a la carga. Si la técnica se rompe, bajas el peso. Sin excepción.',
        'registro'              => 'Anota pesos y repeticiones de cada sesión en una libreta o en tu celular. Sin registro, no hay progresión.',
    ],

    'semanas' => $semanas,

    'notas_generales' => 'Plan periodizado 4 semanas con foco en glúteo (2 días directos) + estímulos a todo el cuerpo. El déficit calórico está controlado para preservar masa muscular. El cardio es complementario, NO el motor del cambio: las pesas son lo principal.',

    'notas_coach' => "Lizeth, este bloque te lo armé pensando justo en lo que me dijiste: querés bajar grasa y subir masa, con foco en el tren inferior. Por eso tenés DOS días directos de glúteo (lunes y viernes), un día completo de cuádriceps con aductor y gemelos como pediste, y los días de upper distribuidos para que tengas recuperación completa entre estímulos.\n\nLa periodización es lineal: semana 1 técnica y adaptación (RIR 3), semana 2 hipertrofia (RIR 2), semana 3 fuerza (RIR 1-2), semana 4 peak. Esto significa que cada semana subís un poco la carga y bajás las reps. Anotá los pesos, no improvisés.\n\nEl HIIT del martes y jueves es corto (20 min, 30/30) y el del sábado son 30 min en circuito. Si un día llegás muy cansada al sábado, lo haces el domingo o lo saltás — el sábado es el menos crítico. NO faltés a las pesas L-V, ahí está el cambio real.\n\nUna advertencia: estás en déficit agresivo (1650 kcal). No vamos al fallo en los compuestos. Si una semana sentís bajón fuerte o la fuerza se cae mucho, me avisás y ajustamos. El cuerpo manda. Vamos.",
];

// ─── PLAN NUTRICIÓN ─────────────────────────────────────────────────────────
// 1650 kcal · P150 / C125 / G60 · 4 comidas A/B/C
// SIN brócoli, SIN zucchini, SIN arándanos

$comidas = [
    [
        'nombre'      => 'Desayuno',
        'tipo'        => 'desayuno',
        'hora'        => '7:00 AM',
        'subtitulo'   => 'Proteína completa + carbo moderado + 1 fruta',
        'calorias'    => 430,
        'macros'      => ['proteina' => 35, 'carbohidratos' => 40, 'grasas' => 12],
        'opcion_a' => [
            'Huevos enteros (2 unidades) + claras (3 unidades) revueltos con tomate y cebolla',
            'Arepa de maíz blanco (1 unidad mediana, 60g)',
            'Banano mediano (1 unidad, ~120g)',
            'Café negro o té sin azúcar',
        ],
        'opcion_b' => [
            'Claras de huevo (5 unidades) + 1 huevo entero — tortilla con espinaca',
            'Tostadas integrales (2 rebanadas, ~60g)',
            'Fresas frescas (150g)',
            'Café negro o té sin azúcar',
        ],
        'opcion_c' => [
            'Huevos pochados o tibios (3 unidades enteros)',
            'Avena en agua (50g en seco)',
            'Manzana mediana (1 unidad, ~150g)',
            'Café negro o té sin azúcar',
        ],
        'notas' => 'La proteína del desayuno la cocinás sin aceite — usá sartén antiadherente o spray. El carbo y la fruta te dan energía para entrenar 2-3 horas después. Si no entrenás en la mañana, dejá la fruta para el snack PM.',
    ],
    [
        'nombre'      => 'Almuerzo',
        'tipo'        => 'almuerzo',
        'hora'        => '1:00 PM',
        'subtitulo'   => 'Proteína magra + carbo moderado + ensalada + aguacate',
        'calorias'    => 530,
        'macros'      => ['proteina' => 45, 'carbohidratos' => 40, 'grasas' => 20],
        'opcion_a' => [
            'Pechuga de pollo a la plancha (180g) con limón y orégano',
            'Arroz blanco cocido (100g)',
            'Ensalada de hojas verdes con tomate, pepino y zanahoria rallada',
            'Aguacate (½ unidad pequeña, ~50g)',
            'Aceite de oliva (1 cdita) y vinagre o limón para la ensalada',
        ],
        'opcion_b' => [
            'Carne de res magra (lomo o sobrebarriga, 160g) a la plancha con cebolla',
            'Batata o camote al horno (180g)',
            'Ensalada de lechuga, tomate, pepino y rábano',
            'Aguacate (½ unidad pequeña, ~50g)',
            'Aceite de oliva (1 cdita) y limón',
        ],
        'opcion_c' => [
            'Tilapia al horno o a la plancha (200g) con limón y ajo',
            'Papa cocida (180g)',
            'Ensalada de espinaca, tomate cherry, cebolla morada y zanahoria',
            'Aguacate (½ unidad pequeña, ~50g)',
            'Aceite de oliva (1 cdita) y limón',
        ],
        'notas' => 'La proteína se cocina a la plancha, al horno o al vapor — nada frito. El aguacate es UNA porción, no media palta entera. La ensalada llénala (las verduras son libres) — eso te ayuda con la saciedad.',
    ],
    [
        'nombre'      => 'Snack PM',
        'tipo'        => 'merienda',
        'hora'        => '5:00 PM',
        'subtitulo'   => 'Snack para llegar bien a la cena (rota entre las 3 opciones)',
        'calorias'    => 280,
        'macros'      => ['proteina' => 20, 'carbohidratos' => 30, 'grasas' => 8],
        'opcion_a' => [
            'Yogur griego natural sin azúcar (200g)',
            'Avena en hojuelas (30g)',
            'Fresas o mora (100g)',
            'Canela al gusto',
        ],
        'opcion_b' => [
            'Tostadas de arroz (4 unidades)',
            'Crema de maní natural sin azúcar (15g, 1 cda)',
            'Banano pequeño (1 unidad, ~100g)',
            'Café o té sin azúcar',
        ],
        'opcion_c' => [
            'Yogur griego natural sin azúcar (150g)',
            'Granola sin azúcar añadida (30g)',
            'Manzana en cubos (1 unidad pequeña, ~120g)',
            'Canela al gusto',
        ],
        'notas' => 'Este snack es para que NO llegués famélica a la cena (ahí es donde la gente se sale del plan). Si entrenaste en la tarde, podés agregar 1 scoop de whey en agua a este snack como post-entreno.',
    ],
    [
        'nombre'      => 'Cena',
        'tipo'        => 'cena',
        'hora'        => '8:00 PM',
        'subtitulo'   => 'Proteína + carbo bajo + ensalada generosa',
        'calorias'    => 410,
        'macros'      => ['proteina' => 50, 'carbohidratos' => 15, 'grasas' => 18],
        'opcion_a' => [
            'Pechuga de pollo a la plancha (200g) con hierbas',
            'Arroz blanco cocido (50g) o papa cocida pequeña (80g)',
            'Ensalada de hojas verdes con tomate, pepino y pimentón',
            'Aceite de oliva (1 cdita)',
        ],
        'opcion_b' => [
            'Tilapia al horno o a la plancha (220g) con limón',
            'Batata pequeña al horno (80g)',
            'Ensalada de espinaca, tomate cherry, cebolla morada',
            'Aceite de oliva (1 cdita)',
        ],
        'opcion_c' => [
            'Lomo de cerdo magro a la plancha (180g) con romero y ajo',
            'Papa cocida pequeña (80g)',
            'Ensalada de lechuga, tomate, zanahoria rallada y pepino',
            'Aceite de oliva (1 cdita)',
        ],
        'notas' => 'La cena tiene carbo bajo a propósito — la noche no necesita tantos. Si llegaste muy tarde o sin hambre, comé solo la proteína + ensalada (sin el carbo). Pero NO te saltés la proteína, eso es lo que preserva tu músculo.',
    ],
];

$nutriPlan = [
    'plan_type'        => 'nutricion',
    'titulo'           => 'Plan Nutricional Pérdida de Grasa — Lizeth Chávez',
    'cliente'          => 'Lizeth Tatiana Chávez Díaz',
    'metodologia'      => 'Pérdida de grasa · Déficit calórico moderado-agresivo · Alta proteína · Carbohidratos moderado-bajos',
    'objetivo_calorico'=> 1650,
    'objetivo_cal'     => 1650,
    'objetivo'         => 'Pérdida de grasa con preservación muscular — déficit agresivo de ~650 kcal. Dieta moderada-baja en carbohidratos (30% del total) con proteína alta (2.0 g/kg) para proteger masa muscular durante el déficit. Proyección: -2 a -3 kg en 4 semanas.',
    'duracion_semanas' => 4,
    'fecha_inicio'     => $validFrom,
    'peso_objetivo'    => 72,

    'macros' => [
        'calorias'        => 1650,
        'proteina_g'      => 150,
        'carbohidratos_g' => 125,
        'grasas_g'        => 60,
    ],

    'periodizacion' => [
        'dias_entrenamiento' => ['calorias' => 1700, 'carbs_extra_g' => 12],
        'dias_descanso'      => ['calorias' => 1600, 'carbs_reduccion_g' => 12],
    ],

    'hidratacion' => [
        'agua_minima_litros' => 3.0,
        'electrolitos'       => 'Con HIIT y escaladora vas a sudar bastante. Si entrenás de noche, añadí electrolitos sin azúcar al agua (sodio + potasio) para no calambrear ni dormir mal.',
    ],

    'tips_nutricionales' => [
        'La proteína es lo NO negociable: si un día no llegás a las 1650 kcal totales pero sí cumpliste con los 150g de proteína, el día es exitoso.',
        'Las 3 opciones (A, B, C) de cada comida son intercambiables — los macros son equivalentes. Elegí según tu antojo o lo que tengas en la nevera.',
        'Cocina la proteína a la plancha, al horno o al vapor. Nada frito. Usá sartén antiadherente o spray.',
        'Las verduras de la ensalada son LIBRES — agregá tantas como quieras. Son tu mejor aliada para la saciedad sin sumar calorías.',
        'Prepará la comida del día anterior o haz batch cooking dominical — la planificación es lo que sostiene un déficit.',
        'En días de entreno tenés 50 kcal extra de carbos (puede ser 1 fruta más o 20g más de arroz/papa). En días de descanso, restá esos 50.',
        'Hidratación: 3 L de agua mínimo al día. El hambre muchas veces es sed disfrazada.',
        'Si te dan ganas de dulce en la noche, té con canela o 1 cdita de miel en agua caliente. NO galletas, NO chocolate (saca del plan).',
        'Café negro y té son libres todo el día. Sin azúcar, sin leche entera (leche descremada ok, pero medila).',
        'Cheat meal opcional: 1 vez por semana podés tener UNA comida fuera del plan (no el día entero). Después volvés sin culpa al plan.',
    ],
    'tips' => [
        'Proteína primero: si cumplís los 150g, el día sigue siendo productivo.',
        'Las 3 opciones por comida son intercambiables — macros equivalentes.',
        'Cocina sin aceite (plancha, horno, vapor). Spray o sartén antiadherente.',
        'Verduras de ensalada = libres. Llenate el plato.',
        'Batch cooking dominical te salva la semana.',
        'En días de entreno: 50 kcal extra carbos. Descanso: -50.',
        '3 L de agua mínimo. El hambre muchas veces es sed.',
        'Antojo nocturno: té con canela o agua caliente con miel (1 cdita).',
        'Café y té libres, sin azúcar.',
        'Cheat meal: 1 comida/semana (no día entero).',
    ],

    'notas_coach' => "Lizeth, este plan está calculado para que bajés grasa sin perder músculo. El déficit es agresivo (650 kcal por debajo de tu gasto) — eso significa que vas a sentir hambre algunos días, especialmente en la primera semana. Es normal y va a pasar después del día 5-7 cuando tu cuerpo se ajuste.\n\nLa proteína es 150g (2.0 g/kg de tu peso). Eso es ALTO a propósito — es lo que evita que pierdas músculo en el déficit. NO la negociés. Si un día solo podés cumplir un macro, que sea la proteína.\n\nLos carbohidratos están moderados (30% del total) como me pediste. Te van a alcanzar para entrenar bien — los pusimos en el desayuno (pre-entreno si entrenás en la mañana) y el almuerzo (post-entreno si entrenás al medio día). La cena los baja a propósito porque la noche no necesita tantos.\n\nNo tenés brócoli, zucchini ni arándanos en ninguna opción, como pediste. Si querés sumar más vegetales, la ensalada es libre — agregá lechuga, espinaca, tomate, pepino, zanahoria, pimentón, rábano, cebolla, los que te gusten.\n\nEl carb cycling es sutil: días de entreno +50 kcal en carbos (una fruta más o un poco más de arroz/papa), días de descanso -50. No tenés que pesar cada gramo, es más una guía mental.\n\nUna regla de oro: NO te saltés comidas. Si llegás tarde, comé una versión chica de la cena, pero comé. Saltarse comidas es el principal sabotaje del déficit — te lleva a comer demasiado en la siguiente. Vamos.",

    'comidas' => $comidas,

    'plan_dia_descanso' => [
        'descripcion'        => 'En días que NO entrenás (domingo o el día que descansés), bajamos 50 kcal en carbos. La proteína se mantiene en 150g.',
        'calorias_objetivo'  => 1600,
        'ajustes'            => [
            'Reducí 20g de arroz/papa/batata en el almuerzo',
            'Mantené el desayuno, snack y cena iguales',
            'La proteína NO se toca — sigue en 150g',
            'Hidratación igual: 3L mínimo',
        ],
    ],
];

// ─── PLAN SUPLEMENTACIÓN ────────────────────────────────────────────────────

$supPlan = [
    'plan_type'              => 'suplementacion',
    'titulo'                 => 'Stack Esencial Suplementación — Lizeth Chávez',
    'descripcion_protocolo'  => 'Stack básico para pérdida de grasa con preservación muscular en mujer intermedia. Solo lo que mueve la aguja, sin gastar en cosas que no funcionan.',
    'perfil_cliente'         => 'Mujer 27 años, 75 kg, intermedia, objetivo pérdida de grasa con foco glúteo',
    'objetivo'               => 'Soporte de pérdida de grasa con preservación muscular y rendimiento en entreno.',
    'fecha_inicio'           => $validFrom,
    'advertencia'            => 'Si tomás anticonceptivos, antidepresivos o cualquier medicamento crónico, consultá con tu médico antes de empezar la creatina y la cafeína.',

    'suplementos' => [
        [
            'nombre'      => 'Proteína de Suero Whey Concentrada',
            'dosis'       => '1 scoop (30g) en agua o leche descremada',
            'momento'     => 'Post-entreno o como snack si no llegás a los 150g de proteína del día',
            'frecuencia'  => 'Diario (entrenos) — opcional en días de descanso si la proteína de las comidas alcanza',
            'notas'       => 'Es la herramienta más útil para cerrar la cuota de proteína sin agregar muchas calorías. 1 scoop = 24g de proteína por solo ~120 kcal.',
        ],
        [
            'nombre'      => 'Creatina Monohidrato',
            'dosis'       => '5g (1 cdita rasa)',
            'momento'     => 'Cualquier momento del día (con desayuno o con el batido post-entreno)',
            'frecuencia'  => 'Diario, TODOS los días incluyendo descanso',
            'notas'       => 'Es el suplemento más estudiado y efectivo que existe. Mejora la fuerza, la recuperación y preserva masa muscular en déficit. Tomá 5g constantes (no hace falta fase de carga). Tomá agua extra.',
        ],
        [
            'nombre'      => 'Cafeína (en cápsula o café fuerte)',
            'dosis'       => '150-200 mg',
            'momento'     => 'Pre-entreno, 30-45 min antes de la sesión',
            'frecuencia'  => 'Solo días de entreno (5-6 días por semana)',
            'notas'       => 'Aumenta el rendimiento y oxida un poco más de grasa durante el cardio. NO tomes después de las 5 PM — te corta el sueño. Si entrenás en la noche, omitila.',
        ],
        [
            'nombre'      => 'Omega-3 (EPA + DHA)',
            'dosis'       => '2g de EPA+DHA combinados (leer etiqueta, no las cápsulas totales)',
            'momento'     => 'Con el almuerzo (necesita grasa de la comida para absorberse)',
            'frecuencia'  => 'Diario',
            'notas'       => 'Mejora recuperación articular, baja inflamación y favorece composición corporal. Buscá una marca con sello IFOS o NSF (que no tenga metales pesados).',
        ],
        [
            'nombre'      => 'Vitamina D3',
            'dosis'       => '2000 UI',
            'momento'     => 'Con el desayuno (es liposoluble, necesita grasa)',
            'frecuencia'  => 'Diario',
            'notas'       => 'Mejora densidad ósea, función inmune y producción hormonal (importante en mujeres). Especialmente útil si no te da el sol a diario en piel.',
        ],
        [
            'nombre'      => 'Multivitamínico para mujeres',
            'dosis'       => '1 tableta',
            'momento'     => 'Con el desayuno',
            'frecuencia'  => 'Diario',
            'notas'       => 'Seguro nutricional durante el déficit. Cualquier marca decente (Centrum Women, GNC Women, One A Day Women). El hierro y B12 son importantes para mujeres entrenando duro.',
        ],
        [
            'nombre'      => 'Magnesio Bisglicinato',
            'dosis'       => '300-400 mg',
            'momento'     => '30-45 min antes de dormir',
            'frecuencia'  => 'Diario',
            'notas'       => 'Mejora calidad de sueño, recuperación muscular y reduce calambres (importante con cardio diario). El bisglicinato no produce diarrea como el óxido. No es estimulante.',
        ],
    ],

    'notas_coach' => "Lizeth, este stack es lo justo y necesario para tu objetivo. Nada de pre-workouts con 15 ingredientes ni quemadores de grasa caros — eso no funciona o solo te pone nerviosa.\n\nLos 3 ESENCIALES que no podés saltar:\n1. Whey (te ayuda a cerrar la cuota de proteína)\n2. Creatina (preserva músculo en déficit + mejora fuerza)\n3. Magnesio (mejor sueño = mejor recuperación = mejores resultados)\n\nLos otros 4 (cafeína, omega-3, vit D3, multi) son recomendados pero no críticos. Si tu presupuesto es ajustado, empezá con los 3 esenciales y los demás los sumás cuando puedas.\n\nIMPORTANTE: los suplementos son SUPLEMENTOS, no reemplazos. Si no comés bien y no entrenás duro, ningún polvo va a hacer la diferencia. Primero la comida y el gym, después los polvos.",

    'mensaje_final' => 'Menos es más. No llenés tu cocina de polvos caros. Estos 7 cubren todo lo que necesitás.',
];

// ─── INSERT EN DB CON TRANSACTION ────────────────────────────────────────────

// Guard para dry-run: si DRY_RUN está definido, no toca DB ni intenta conectarse.
if (defined('DRY_RUN') && DRY_RUN) {
    $GLOBALS['__dryrun_train'] = $trainPlan;
    $GLOBALS['__dryrun_nutri'] = $nutriPlan;
    $GLOBALS['__dryrun_sup']   = $supPlan;
    return;
}

try {
    $pdo->beginTransaction();

    // 1. Desactivar planes previos activos del cliente (los 3 tipos)
    $stmt = $pdo->prepare(
        "UPDATE assigned_plans SET active=0 WHERE client_id=? AND active=1 AND plan_type IN ('entrenamiento','nutricion','suplementacion')"
    );
    $stmt->execute([$clientId]);
    $desactivados = $stmt->rowCount();

    // 2. Insertar los 3 nuevos
    $stmt = $pdo->prepare(
        "INSERT INTO assigned_plans (client_id, plan_type, content, assigned_by, valid_from, expires_at, active, created_at)
         VALUES (?, ?, ?, ?, ?, ?, 1, ?)"
    );

    $stmt->execute([
        $clientId, 'entrenamiento',
        json_encode($trainPlan, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        $coachId, $validFrom, $expiresAt, $now,
    ]);
    $idTrain = $pdo->lastInsertId();

    $stmt->execute([
        $clientId, 'nutricion',
        json_encode($nutriPlan, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        $coachId, $validFrom, $expiresAt, $now,
    ]);
    $idNutri = $pdo->lastInsertId();

    $stmt->execute([
        $clientId, 'suplementacion',
        json_encode($supPlan, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        $coachId, $validFrom, $expiresAt, $now,
    ]);
    $idSup = $pdo->lastInsertId();

    $pdo->commit();

    echo "✓ OK — 3 planes insertados para Lizeth Chávez (client_id=$clientId)\n";
    echo "  · Planes previos desactivados: $desactivados\n";
    echo "  · Entrenamiento: assigned_plan_id=$idTrain (4 semanas, 6 días, " . count($trainPlan['semanas'][0]['dias']) . " dias/semana, " . array_sum(array_map(fn($d) => count($d['ejercicios']), $trainPlan['semanas'][0]['dias'])) . " ejercicios/semana)\n";
    echo "  · Nutrición:      assigned_plan_id=$idNutri (1650 kcal, P150/C125/G60, 4 comidas A/B/C)\n";
    echo "  · Suplementación: assigned_plan_id=$idSup (" . count($supPlan['suplementos']) . " suplementos)\n";
    echo "  · valid_from=$validFrom, expires_at=$expiresAt\n";
    echo "\nSiguiente paso: invalidar caches:\n";
    echo "  php artisan tinker --execute=\"\\Cache::forget('client_plan_v3_$clientId'); \\Cache::forget('wp:plan:$clientId'); \\Cache::forget('wp:weekdays:$clientId'); \\Cache::forget('dashboard:$clientId'); echo 'OK';\"\n";
} catch (Exception $e) {
    $pdo->rollBack();
    fwrite(STDERR, "✗ ERROR: " . $e->getMessage() . "\n");
    fwrite(STDERR, $e->getTraceAsString() . "\n");
    exit(1);
}
