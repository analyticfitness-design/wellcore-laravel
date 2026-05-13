<?php
/**
 * insert_julie_mes2.php
 * Inserta Plan Esencial Entrenamiento — Mes 2 para Julie Rodriguez (client_id=57)
 * valid_from: 2026-05-19 | expires_at: 2026-06-16
 * Ejecutar via EasyPanel script o: php /code/bootstrap/insert_julie_mes2.php
 * ELIMINAR después de usar
 */

$pdo = new PDO(
    'mysql:host=wellcorefitness_wellcorefitness-mysql;dbname=wellcorefitness;charset=utf8mb4',
    'wellcorefitness',
    'fYCVgn4XZ7twq34',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$clientId  = 57;
$coachId   = 7;
$validFrom = '2026-05-19';
$expiresAt = '2026-06-16';
$now       = date('Y-m-d H:i:s');
$g = fn(string $a): string => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/' . $a . '.gif';

// ── PERIODIZACIÓN MES 2 ──────────────────────────────────────────────────────

$weekParams = [
    1 => ['fase' => 'Hipertrofia',    'series' => 4, 'reps' => '12-15', 'rir' => '2', 'descanso' => '75 seg'],
    2 => ['fase' => 'Fuerza',         'series' => 4, 'reps' => '10-12', 'rir' => '1', 'descanso' => '90 seg'],
    3 => ['fase' => 'Fuerza Máxima',  'series' => 5, 'reps' => '8-10',  'rir' => '1', 'descanso' => '90-120 seg'],
    4 => ['fase' => 'Peak',           'series' => 5, 'reps' => '6-8',   'rir' => '0', 'descanso' => '120 seg'],
];

$planchaTime = [1 => '30 seg', 2 => '40 seg', 3 => '45 seg', 4 => '60 seg'];

// ── PLANTILLAS DE DÍAS ────────────────────────────────────────────────────────

$dayTemplates = [

    // ── LUNES ─────────────────────────────────────────────────────────────────
    'Lunes' => [
        'grupo_muscular'   => 'Glúteos, Piernas, Pantorrilla',
        'tipo'             => 'piernas',
        'nombre_dia'       => 'Lunes — Glúteos, Piernas, Pantorrilla',
        'calentamiento'    => '5 min en caminadora a baja intensidad + movilidad de cadera y tobillos + 2 series de hip thrust sin peso.',
        'vuelta_calma'     => '5 min: estiramiento de cuádriceps, isquios, glúteos y pantorrillas.',
        'ejercicios' => [
            // [gif, nombre, notas, ['tipo'=>'normal'|'plancha'|'cardio'], variacion_nombre, variacion_gif]
            [$g('hipthrust-con-barra'), 'Hip Thrust con Barra',
                'Espalda en el banco, barra en caderas con acolchado. Empuja desde los talones, aprieta glúteos 1 seg arriba. Baja controlado.',
                [], 'Hip Thrust a Una Pierna con Barra', $g('hipthrust-a-una-pierna-con-barra')],

            [$g('step-up-mancuerna'), 'Step Up con Mancuerna',
                'Sube un pie a la caja o banco, empuja desde el talón de la pierna delantera. No te apoyes en la pierna trasera. Haz todas las reps por un lado, luego el otro.',
                [], 'Sentadilla Goblet', $g('sentadilla-goblet')],

            [$g('peso-muerto-rumano-con-mancuerna'), 'Peso Muerto Rumano con Mancuerna',
                'Espalda recta, rodillas levemente dobladas. Baja las mancuernas por los muslos hasta sentir el estiramiento del femoral. Sube apretando glúteos.',
                [], 'Peso Muerto Rumano con Barra', $g('peso-muerto-rumano-con-barra')],

            [$g('patada-trasera-en-polea'), 'Patada Trasera en Polea',
                'Cuerpo ligeramente inclinado, cadera fija. Extiende la pierna hacia atrás contrayendo el glúteo. Baja lento. Haz todas las reps de un lado, luego el otro.',
                [], 'Patada Trasera en Máquina', $g('patada-trasera-en-maquina')],

            [$g('abduccion-de-cadera-sentado-en-maquina'), 'Abducción de Cadera Sentado en Máquina',
                'Espalda recta. Empuja las almohadillas hacia afuera de forma controlada. No uses el impulso del torso. Aprieta el glúteo medio en la contracción.',
                [], 'Abducción de Cadera de Pie en Máquina', $g('abduccion-de-cadera-de-pie-en-maquina')],

            [$g('pantorrillas-en-prensa-de-pierna'), 'Pantorrillas en Prensa de Pierna',
                'Solo las puntas de los pies en el borde inferior de la plataforma. Baja hasta estirar la pantorrilla completamente, sube hasta la punta y aguanta 1 seg.',
                [], 'Elevación de Talones en Máquina', $g('elevacion-de-talones-en-maquina')],
        ],
    ],

    // ── MARTES ────────────────────────────────────────────────────────────────
    'Martes' => [
        'grupo_muscular'   => 'Core, Hombros, Tríceps',
        'tipo'             => 'empuje',
        'nombre_dia'       => 'Martes — Core, Hombros, Tríceps',
        'calentamiento'    => '5 min de movilidad articular + rotaciones de hombro + activación de core con los primeros 3 ejercicios de la sesión a menor intensidad.',
        'vuelta_calma'     => '5 min: estiramiento de hombros, tríceps y zona lumbar.',
        'ejercicios' => [
            // Core warmup #1
            [$g('elevacion-de-piernas-captain-chair'), 'Elevación de Piernas en Captain Chair',
                'Columna apoyada en el respaldo, brazos en los apoyabrazos. Piernas rectas o ligeramente dobladas, sube hasta 90°. Baja lento sin soltar el abdomen.',
                ['warmup' => true], 'Elevación de Piernas Acostado', $g('elevacion-de-piernas-acostado')],

            // Core warmup #2
            [$g('crunch-en-pelota-de-estabilidad'), 'Crunch en Pelota de Estabilidad',
                'Zona lumbar apoyada en la pelota. Contrae el abdomen y sube los hombros del suelo. Baja hasta estirar el abdomen. Rango corto pero controlado.',
                ['warmup' => true], 'Crunch Abdominal en Máquina Total', $g('crunch-abdominal-en-maquina-total')],

            // Core warmup #3 — plancha (timed)
            [$g('plancha-abdominal'), 'Plancha Abdominal',
                'Cuerpo en línea recta de cabeza a talones. Abdomen apretado, no dejes caer las caderas. Respira con normalidad. Si necesitas bajar las rodillas, está bien.',
                ['plancha' => true], 'Plancha Lateral', $g('plancha-lateral')],

            // Main exercises
            [$g('press-de-hombro-con-mancuerna'), 'Press de Hombro con Mancuerna',
                'Sentada o de pie. Codos a 90° abajo, empuja hasta casi extender sin bloquear. Baja controlado en 2 seg. No arquees la zona lumbar.',
                [], 'Press Arnold con Mancuerna', $g('press-arnold-con-mancuerna')],

            [$g('remo-al-menton-con-barra'), 'Remo al Mentón con Barra',
                'Agarre ligeramente más cerrado que los hombros. Jala la barra hacia la barbilla con los codos subiendo por encima de las muñecas. Baja lento.',
                [], 'Remo al Mentón con Mancuerna', $g('remo-al-menton-con-mancuerna')],

            [$g('elevacion-lateral-con-mancuerna'), 'Elevación Lateral con Mancuerna',
                'Brazos ligeramente doblados. Sube hasta la altura del hombro, no más alto. El dedo meñique un poco arriba del pulgar. Baja controlado en 2 seg.',
                [], 'Elevación Lateral en Polea', $g('elevacion-lateral-en-polea')],

            [$g('elevacion-posterior-con-mancuerna'), 'Elevación Posterior con Mancuerna',
                'Torso inclinado a 45°, brazos ligeramente doblados. Abre los brazos hacia los lados y atrás hasta la altura del hombro. Siente el deltoides posterior contraer.',
                [], 'Remo Inclinado en Banco con Mancuernas', $g('remo-con-mancuernas-sobre-banco-inclinado')],

            [$g('fondos-de-triceps-en-maquina'), 'Fondos de Tríceps en Máquina',
                'Torso vertical, empuja las manijas hacia abajo extendiendo los codos completamente. No te inclines hacia adelante. Sube lento y controlado.',
                [], 'Extensión de Tríceps en Polea con Cuerda', $g('extension-de-triceps-en-polea-con-cuerda')],

            [$g('extension-de-triceps-en-polea-con-cuerda'), 'Extensión de Tríceps en Polea con Cuerda',
                'Codos pegados al cuerpo, solo el antebrazo se mueve. Al bajar, separa la cuerda hacia afuera para mayor contracción del tríceps. Sube lento.',
                [], 'Fondos de Tríceps en Máquina', $g('fondos-de-triceps-en-maquina')],
        ],
    ],

    // ── MIÉRCOLES ─────────────────────────────────────────────────────────────
    'Miércoles' => [
        'grupo_muscular'   => 'Cuádriceps, Pantorrilla',
        'tipo'             => 'piernas',
        'nombre_dia'       => 'Miércoles — Cuádriceps, Pantorrilla',
        'calentamiento'    => '5 min en caminadora + 2 series de 15 reps de sentadillas sin peso + movilidad de rodilla y tobillo.',
        'vuelta_calma'     => '5 min: estiramiento de cuádriceps, pantorrillas y cadera flexora.',
        'ejercicios' => [
            [$g('sentadilla-con-barra'), 'Sentadilla con Barra',
                'Barra en trapecio, pies al ancho de hombros. Espalda recta, rodillas en línea con los pies. Baja hasta 90° o más si la movilidad lo permite. Sube explosivo.',
                [], 'Prensa de Piernas', $g('prensa-de-piernas-cerrado')],

            [$g('prensa-de-piernas-cerrado'), 'Prensa de Piernas Cerrado',
                'Pies juntos en la parte baja de la plataforma. Activa más el cuádricep. Baja hasta 90°, no bloquees las rodillas arriba. Empuja con toda la planta.',
                [], 'Sentadilla con Barra', $g('sentadilla-con-barra')],

            [$g('sentadilla-hacka'), 'Sentadilla Hack',
                'Espalda apoyada en la máquina. Pies al ancho de hombros o más cerrado para enfatizar cuádriceps. Baja lento, sube empujando desde los talones.',
                [], 'Prensa de Piernas Cerrado', $g('prensa-de-piernas-cerrado')],

            [$g('extension-de-piernas-en-maquina'), 'Extensión de Piernas en Máquina',
                'Extensión completa arriba, aguanta 1 seg apretando el cuádricep. Baja en 3 seg controlado. No uses impulso de la cadera.',
                [], 'Sentadilla Hack', $g('sentadilla-hacka')],

            [$g('zancada-frontal-con-mancuerna'), 'Zancada Frontal con Mancuerna',
                'Paso hacia adelante, rodilla trasera baja sin tocar el suelo. Empuja desde el talón de la pierna delantera para volver. Alterna piernas por rep o haz un lado completo.',
                [], 'Zancada Inversa con Mancuernas', $g('zancada-inversa-con-mancuernas')],

            [$g('elevacion-de-talones-sentado'), 'Elevación de Talones Sentado',
                'Mancuernas en las rodillas como carga. Rango completo: baja hasta estirar la pantorrilla, sube hasta la punta y aguanta 1 seg. Movimiento lento.',
                [], 'Pantorrillas en Prensa de Pierna', $g('pantorrillas-en-prensa-de-pierna')],
        ],
    ],

    // ── JUEVES ────────────────────────────────────────────────────────────────
    'Jueves' => [
        'grupo_muscular'   => 'Espalda, Bíceps, Core',
        'tipo'             => 'jale',
        'nombre_dia'       => 'Jueves — Espalda, Bíceps, Core',
        'calentamiento'    => '5 min de movilidad articular de hombros + 2 series de jalones ligeros + activación escapular.',
        'vuelta_calma'     => '5 min: estiramiento de espalda, bíceps y abdomen.',
        'ejercicios' => [
            [$g('jalon-al-pecho-en-maquina'), 'Jalón al Pecho en Máquina',
                'Agarre ligeramente más ancho que los hombros. Jala hacia el pecho con los codos apuntando al suelo. No te eches hacia atrás para hacer la fuerza.',
                [], 'Pulldown en Polea', $g('pulldown-en-polea')],

            [$g('pulldown-en-polea'), 'Pulldown en Polea',
                'Agarre prono. Aprieta los omóplatos hacia abajo y atrás mientras jalas. Lleva la barra al pecho. Sube controlado sin soltar la tensión de la espalda.',
                [], 'Jalón al Pecho Agarre Cerrado', $g('jalon-al-pecho-agarre-cerrado')],

            [$g('remo-sentado-en-polea-agarre-abierto'), 'Remo Sentado en Polea',
                'Torso erguido, jala el agarre hacia el ombligo con los codos pegados al cuerpo. Al final contrae los omóplatos. Extiende lento hacia adelante sin redondar la espalda.',
                [], 'Remo Sentado en Máquina', $g('remo-sentado-en-maquina')],

            [$g('curl-biceps-con-barra'), 'Curl Bíceps con Barra',
                'Codos pegados al cuerpo. Sube la barra hasta los hombros contrayendo el bíceps. Baja completamente en 2-3 seg. No uses el impulso de la cadera.',
                [], 'Curl Bíceps con Barra EZ', $g('curl-biceps-barra-ez')],

            [$g('curl-martillo-con-mancuerna'), 'Curl Martillo con Mancuerna',
                'Agarre neutro (pulgares arriba). Trabaja el braquial y braquiorradial además del bíceps. Alterna brazos o haz bilateral. Codos fijos al cuerpo.',
                [], 'Curl Martillo en Polea con Cuerda', $g('curl-martillo-en-polea-con-cuerda')],

            [$g('elevacion-de-piernas-acostado'), 'Elevación de Piernas Acostado',
                'Espalda plana en el suelo, manos debajo de la zona lumbar si necesitas apoyo. Piernas rectas, baja sin tocar el suelo. Abdomen apretado todo el tiempo.',
                [], 'Elevación de Piernas en Captain Chair', $g('elevacion-de-piernas-captain-chair')],

            [$g('bicicleta-crunch'), 'Bicicleta Crunch',
                'Lento y controlado. Codo opuesto a la rodilla que sube. No jales del cuello. Extiende bien la pierna que baja. Siente la contracción del oblicuo.',
                ['warmup' => true], 'Crunch en Pelota de Estabilidad', $g('crunch-en-pelota-de-estabilidad')],

            [$g('crunch-abdominal-en-maquina-total'), 'Crunch Abdominal en Máquina Total',
                'Ajusta el peso para sentir la contracción. Jala con el abdomen, no con los brazos. Contrae fuerte en la posición baja y sube lento. No uses impulso.',
                [], 'Crunch en Polea Arrodillado', $g('crunch-en-polea-arrodillado')],
        ],
    ],

    // ── VIERNES ───────────────────────────────────────────────────────────────
    'Viernes' => [
        'grupo_muscular'   => 'Glúteos, Isquiotibiales',
        'tipo'             => 'piernas',
        'nombre_dia'       => 'Viernes — Glúteos, Isquiotibiales',
        'calentamiento'    => '5 min movilidad de cadera y columna + 2 series de puente de glúteo sin peso + activación de femoral.',
        'vuelta_calma'     => '5 min: estiramiento profundo de glúteos, isquios y cadera.',
        'ejercicios' => [
            [$g('peso-muerto-rumano-con-barra'), 'Peso Muerto Rumano con Barra',
                'Espalda recta, rodillas ligeramente dobladas. Baja la barra por los muslos hasta sentir el estiramiento del femoral. Sube empujando las caderas hacia adelante.',
                [], 'Peso Muerto Rumano con Mancuerna', $g('peso-muerto-rumano-con-mancuerna')],

            [$g('hipthrust-con-barra'), 'Hip Thrust con Barra',
                'Espalda en el banco, barra en caderas con acolchado. Empuja desde los talones, aprieta glúteos 1 seg arriba. La cadera debe quedar paralela al suelo en el tope.',
                [], 'Hip Thrust a Una Pierna con Barra', $g('hipthrust-a-una-pierna-con-barra')],

            [$g('curl-femoral-acostado-en-maquina'), 'Curl Femoral Acostado en Máquina',
                'Contrae el femoral al subir. Baja en 3 seg controlado. No des impulso con la cadera. Tobillos bien apoyados en el rodillo.',
                [], 'Curl Femoral Sentado', $g('curl-femoral-sentado')],

            [$g('curl-femoral-sentado'), 'Curl Femoral Sentado',
                'Posición vertical activa más el bíceps femoral distal. Ajusta el respaldo para que la rodilla quede al borde del asiento. Baja completamente entre reps.',
                [], 'Curl Femoral Acostado en Máquina', $g('curl-femoral-acostado-en-maquina')],

            [$g('abduccion-de-cadera-de-pie-en-maquina'), 'Abducción de Cadera de Pie en Máquina',
                'Pierna de apoyo firme, empuja hacia afuera con el glúteo medio. Movimiento controlado en ambas direcciones. Siente el glúteo externo activarse.',
                [], 'Abducción de Cadera Sentado en Máquina', $g('abduccion-de-cadera-sentado-en-maquina')],

            [$g('patada-trasera-en-polea'), 'Patada Trasera en Polea',
                'Cuerpo ligeramente inclinado, cadera fija. Extiende la pierna hacia atrás contrayendo el glúteo. No dejes que la cadera rote. Haz todas las reps de un lado.',
                [], 'Patada Trasera en Máquina', $g('patada-trasera-en-maquina')],
        ],
    ],

    // ── SÁBADO ────────────────────────────────────────────────────────────────
    'Sábado' => [
        'grupo_muscular'   => 'Core, Full Body, Cardio',
        'tipo'             => 'full_body',
        'nombre_dia'       => 'Sábado — Full Body + Cardio',
        'calentamiento'    => '5 min movilidad general + activación de core antes de empezar.',
        'vuelta_calma'     => '5 min de estiramientos generales post-escaladora.',
        'ejercicios' => [
            // Core activation
            [$g('elevacion-de-piernas-sentado'), 'Elevación de Piernas Sentado',
                'Sentada al borde del banco, apóyate en los brazos. Sube las rodillas al pecho o extiende las piernas. Baja controlado sin tocar el suelo. Abdomen apretado.',
                ['warmup' => true], 'Elevación de Piernas en Captain Chair', $g('elevacion-de-piernas-captain-chair')],

            // Core activation (timed)
            [$g('plancha-abdominal'), 'Plancha Abdominal',
                'Cuerpo en línea recta de cabeza a talones. Abdomen apretado, no dejes caer las caderas. Respira con normalidad durante el tiempo indicado.',
                ['plancha' => true], 'Plancha Lateral', $g('plancha-lateral')],

            // Main exercises
            [$g('sentadilla-frontal-en-landmine'), 'Sentadilla Frontal en Landmine',
                'Barra en el hombro o frente al pecho. Espalda erguida, rodillas en línea con los pies. Más amigable para la espalda baja que la sentadilla frontal clásica.',
                [], 'Sentadilla Goblet', $g('sentadilla-goblet')],

            [$g('puente-de-gluteo-con-mancuerna'), 'Puente de Glúteo con Mancuerna',
                'Acostada en el suelo, pies apoyados. Mancuerna en las caderas. Empuja desde los talones, aprieta el glúteo 1 seg arriba. Espalda baja no hiperextiendas.',
                [], 'Hip Thrust con Barra', $g('hipthrust-con-barra')],

            [$g('press-militar-con-barra-de-pie'), 'Press Militar con Barra de Pie',
                'Core apretado, espalda en posición neutra. Barra sale del pecho y sube sobre la cabeza en línea vertical. No arquees la zona lumbar. Baja controlado.',
                [], 'Press de Hombro con Mancuerna', $g('press-de-hombro-con-mancuerna')],

            [$g('remo-con-mancuernas'), 'Remo con Mancuernas',
                'Torso inclinado 45°, espalda recta. Jala las mancuernas hacia el ombligo con los codos cerca del cuerpo. Contrae los omóplatos. Baja completamente.',
                [], 'Remo en Banco Inclinado con Mancuernas', $g('remo-con-mancuernas-sobre-banco-inclinado')],

            // Cardio al final
            [$g('escaladora'), 'Escaladora — 20 minutos',
                'Ritmo moderado-alto constante. Resistencia que te permita mantener la intensidad los 20 minutos completos. Esta es la quema calórica del día.',
                ['cardio' => true], null, null],
        ],
    ],
];

// ── CONSTRUCCIÓN DE SEMANAS ───────────────────────────────────────────────────

$semanas = [];
foreach ($weekParams as $weekNum => $params) {
    $dias = [];
    $diaNum = 1;

    foreach ($dayTemplates as $diaNombre => $dayData) {
        $ejercicios = [];

        foreach ($dayData['ejercicios'] as $ejData) {
            [$gifUrl, $nombre, $notas, $meta, $varNombre, $varGif] = $ejData;

            if (!empty($meta['cardio'])) {
                // Cardio: parámetros fijos
                $ej = [
                    'nombre'       => $nombre,
                    'gif_url'      => $gifUrl,
                    'is_cardio'    => true,
                    'series'       => 1,
                    'repeticiones' => '20 min',
                    'descanso'     => '-',
                    'rir'          => null,
                    'bloque'       => 'cardio',
                    'notas'        => $notas,
                ];
            } elseif (!empty($meta['plancha'])) {
                // Plancha: series fijas, reps = tiempo por semana, RIR null
                $ej = [
                    'nombre'       => $nombre,
                    'gif_url'      => $gifUrl,
                    'series'       => 3,
                    'repeticiones' => $planchaTime[$weekNum],
                    'descanso'     => '30-45 seg',
                    'rir'          => null,
                    'bloque'       => 'core',
                    'notas'        => $notas,
                ];
            } elseif (!empty($meta['warmup'])) {
                // Core warmup / activación: parámetros ligeros fijos
                $ej = [
                    'nombre'       => $nombre,
                    'gif_url'      => $gifUrl,
                    'series'       => 3,
                    'repeticiones' => '15',
                    'descanso'     => '30-45 seg',
                    'rir'          => '2',
                    'bloque'       => 'activacion',
                    'notas'        => $notas,
                ];
            } else {
                // Ejercicio normal: progresión por semana
                $ej = [
                    'nombre'       => $nombre,
                    'gif_url'      => $gifUrl,
                    'series'       => $params['series'],
                    'repeticiones' => $params['reps'],
                    'descanso'     => $params['descanso'],
                    'rir'          => $params['rir'],
                    'bloque'       => 'normal',
                    'notas'        => $notas,
                ];
            }

            if ($varNombre !== null) {
                $ej['variacion'] = [
                    'nombre'  => $varNombre,
                    'gif_url' => $varGif,
                ];
            }

            $ejercicios[] = $ej;
        }

        $dias[] = [
            'dia'                => $diaNum,
            'dia_semana'         => $diaNombre,
            'nombre'             => $dayData['nombre_dia'],
            'tipo'               => $dayData['tipo'],
            'grupo_muscular'     => $dayData['grupo_muscular'],
            'duracion_estimada'  => '60-75 min',
            'calentamiento'      => $dayData['calentamiento'],
            'vuelta_calma'       => $dayData['vuelta_calma'],
            'ejercicios'         => $ejercicios,
        ];
        $diaNum++;
    }

    $semanas[] = [
        'semana'       => $weekNum,
        'fase'         => $params['fase'],
        'series'       => $params['series'],
        'repeticiones' => $params['reps'],
        'rir'          => $params['rir'],
        'descanso'     => $params['descanso'],
        'dias'         => $dias,
    ];
}

// ── PLAN COMPLETO ─────────────────────────────────────────────────────────────

$planArr = [
    'plan_type'        => 'entrenamiento',
    'titulo'           => 'Plan Esencial Entrenamiento — Mes 2 — Julie Rodriguez',
    'programa'         => 'Plan Esencial Entrenamiento',
    'cliente'          => 'Julie Rodriguez',
    'plan'             => 'Esencial',
    'objetivo'         => 'Hipertrofia y fuerza funcional — Mes 2',
    'nivel'            => 'Intermedio',
    'duracion_semanas' => 4,
    'fecha_inicio'     => $validFrom,
    'dias_semana'      => ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
    'split' => [
        'Lunes'     => 'Glúteos, Piernas, Pantorrilla',
        'Martes'    => 'Core, Hombros, Tríceps',
        'Miércoles' => 'Cuádriceps, Pantorrilla',
        'Jueves'    => 'Espalda, Bíceps, Core',
        'Viernes'   => 'Glúteos, Isquiotibiales',
        'Sábado'    => 'Full Body + Cardio',
    ],
    'calentamiento' => [
        'descripcion' => '5 minutos de movilidad articular antes de cada sesión. El martes incluye activación de core como parte del calentamiento.',
        'ejercicios'  => [
            'Rotaciones de cadera 30 seg por lado',
            'Movilidad de hombros: círculos hacia adelante y atrás 10 reps',
            'Sentadilla con peso corporal lenta × 10 reps',
            'Activación de glúteos: puente sin peso × 15 reps',
            'Movilidad de tobillo en pared 10 reps por lado',
        ],
    ],
    'notas_coach'  => "Julie, llegaste al segundo mes con una base sólida ya construida. Este bloque es donde el cuerpo empieza a responder de verdad: pasamos de adaptar el movimiento a generar cambios musculares reales.\n\nLas 4 semanas progresan desde hipertrofia hasta peak, lo que significa que cada semana subes la intensidad y bajas un poco las reps. No te asustes por los pesos más altos en semana 3 y 4 — para eso construiste la base en el mes anterior.\n\nEl martes empieza con 3 ejercicios de core antes de entrar a hombros. Eso no es solo calentamiento — es trabajo real de abdomen que protege tu columna y mejora tu postura en todos los ejercicios del día. No lo saltes.\n\nEl sábado tiene una estructura diferente: core, piernas, cuerpo completo, y termina con 20 minutos en la escaladora. Ese orden es intencional — la escaladora al final maximiza la quema calórica sin afectar tu rendimiento en las pesas.\n\nAnota tus pesos cada semana. La progresión de carga es lo que convierte un plan en resultados.",
    'tips' => [
        'Sube el peso cuando puedas completar todas las reps con 2 de sobra (RIR 2 o más).',
        'Los descansos entre series son parte del plan. No los acortes más de lo indicado.',
        'El calentamiento de core del martes prepara la columna — hazlo siempre antes de los hombros.',
        'La escaladora del sábado va al final: primero el trabajo de fuerza, después el cardio.',
        'Anota el peso usado en cada ejercicio por semana para guiar la progresión.',
        'Si un día no puedes ir, continúa al siguiente — no intentes recuperar sesiones perdidas.',
        'Toma agua durante toda la sesión: mínimo 500ml.',
        'Las 4 semanas son un bloque completo. Al terminar, evaluamos resultados y diseñamos el mes 3.',
    ],
    'semanas' => $semanas,
];

$planJson = json_encode($planArr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

// ── INSERCIÓN EN DB ───────────────────────────────────────────────────────────

try {
    $pdo->beginTransaction();

    // 1. Desactivar SOLO el plan de entrenamiento activo previo del cliente
    $stmt = $pdo->prepare("UPDATE assigned_plans SET active=0 WHERE client_id=? AND plan_type='entrenamiento' AND active=1");
    $stmt->execute([$clientId]);
    $deactivated = $stmt->rowCount();
    echo "Planes entrenamiento desactivados: {$deactivated}\n";

    // 2. Insertar el nuevo plan
    $stmt = $pdo->prepare(
        "INSERT INTO assigned_plans (client_id, plan_type, content, assigned_by, valid_from, expires_at, active, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([$clientId, 'entrenamiento', $planJson, $coachId, $validFrom, $expiresAt, 1, $now]);
    $newId = $pdo->lastInsertId();

    $pdo->commit();

    echo "OK: Plan mes 2 insertado para client_id={$clientId}\n";
    echo "AP_ID={$newId} | valid_from={$validFrom} | expires_at={$expiresAt}\n";
    echo "\nInvalidar caches manualmente via tinker:\n";
    echo "  Cache::forget('client_plan_v3_{$clientId}');\n";
    echo "  Cache::forget('wp:plan:{$clientId}');\n";
    echo "  Cache::forget('wp:weekdays:{$clientId}');\n";
    echo "  Cache::forget('dashboard:{$clientId}');\n";

} catch (Exception $e) {
    $pdo->rollBack();
    die("ERROR: " . $e->getMessage() . "\n");
}
