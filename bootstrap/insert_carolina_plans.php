<?php
/**
 * insert_carolina_plans.php
 * Inserta 3 planes para Carolina Valero (client_id=93)
 * Ejecutar en container: php /code/bootstrap/insert_carolina_plans.php
 */

$pdo = new PDO(
    'mysql:host=wellcorefitness_wellcorefitness-mysql;dbname=wellcorefitness;charset=utf8mb4',
    'wellcorefitness',
    'fYCVgn4XZ7twq34',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$clientId  = 93;
$coachId   = 7;
$validFrom = '2026-05-11';
$expiresAt = '2026-06-08';
$gifBase   = 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/';
$g = fn(string $a): string => $gifBase . $a . '.gif';

// ─── PLAN ENTRENAMIENTO ───────────────────────────────────────────────────────

$weekParams = [
    1 => ['fase' => 'Adaptación',  'series' => 3, 'repeticiones' => '12-15', 'rir' => 3, 'descanso' => '60 seg'],
    2 => ['fase' => 'Hipertrofia', 'series' => 4, 'repeticiones' => '10-12', 'rir' => 2, 'descanso' => '75 seg'],
    3 => ['fase' => 'Fuerza',      'series' => 4, 'repeticiones' => '8-10',  'rir' => 1, 'descanso' => '90 seg'],
    4 => ['fase' => 'Peak',        'series' => 5, 'repeticiones' => '6-8',   'rir' => 0, 'descanso' => '90-120 seg'],
];

$planchaTime  = [1 => '30 seg', 2 => '40 seg', 3 => '45 seg', 4 => '60 seg'];
$biciReps     = [1 => '16 reps', 2 => '20 reps', 3 => '24 reps', 4 => '30 reps'];

// [gif_url, nombre, notas, optional_override_array]
$dayTemplates = [
    'Lunes' => [
        'grupo_muscular' => 'Cuádriceps + Pantorrilla',
        'ejercicios' => [
            [$g('sentadilla-goblet'),                    'Sentadilla goblet',                    'Mancuerna al pecho. Rodillas alineadas con los pies, baja hasta 90°.'],
            [$g('extension-de-piernas-en-maquina'),      'Extensión de piernas en máquina',      'Extensión completa arriba, baja en 3 seg. No uses impulso con la cadera.'],
            [$g('zancada-frontal-con-mancuerna'),        'Zancada frontal con mancuerna',        'Alterna piernas. Rodilla trasera baja sin tocar el suelo.'],
            [$g('sentadilla-bulgara-mancuerna'),         'Sentadilla búlgara con mancuerna',     'Pie trasero en el banco. Empuja desde el talón delantero. Haz ambas piernas.'],
            [$g('elevacion-de-talones-con-mancuerna'),   'Elevación de talones con mancuerna',   'Contrae la pantorrilla arriba y aguanta 1 seg. Baja lento y completo.'],
            [$g('elevacion-de-talones-sentado'),         'Elevación de talones sentado',         'Mancuerna en rodillas. Rango completo: baja hasta estirar la pantorrilla.'],
        ],
    ],
    'Martes' => [
        'grupo_muscular' => 'Hombros + Tríceps + Abs',
        'ejercicios' => [
            [$g('press-de-hombro-con-mancuerna'),        'Press de hombro con mancuerna',           'Sentada en banco con respaldo. Codos a 90° abajo, empuja sin bloquear arriba.'],
            [$g('elevacion-lateral-con-mancuerna'),      'Elevación lateral con mancuerna',         'Brazos ligeramente flexionados. Sube hasta la altura del hombro, no más.'],
            [$g('elevacion-frontal-con-mancuerna'),      'Elevación frontal con mancuerna',         'Palma hacia abajo. Baja controlado. Puedes alternar o ir bilateral.'],
            [$g('extension-de-triceps-con-mancuerna'),   'Extensión de tríceps sobre la cabeza',    'De pie o sentada. Codo pegado a la cabeza, solo el antebrazo se mueve.'],
            [$g('patada-de-triceps-con-mancuerna'),      'Patada de tríceps con mancuerna',         'Torso paralelo al suelo. Codo fijo, extiende y aprieta el tríceps arriba.'],
            [$g('elevacion-de-piernas-captain-chair'),   'Elevación de piernas en captain chair',   'Columna apoyada en el respaldo. Piernas rectas, sube hasta 90° sin balancear.'],
            [$g('bicicleta-crunch'),                     'Bicicleta crunch',                        'Lento y controlado. Codo al lado contrario de la rodilla. No uses el cuello.', ['override_reps' => true]],
        ],
    ],
    'Miércoles' => [
        'grupo_muscular' => 'Glúteos',
        'ejercicios' => [
            [$g('hipthrust-con-barra'),                  'Hip thrust con barra',                 'Espalda apoyada en el banco, barra en caderas. Empuja con talones, aprieta glúteos 1 seg arriba.'],
            [$g('sentadilla-con-mancuernas'),            'Sentadilla con mancuernas',            'Pies al ancho de hombros. Mancuernas a los lados o al pecho. Baja controlado hasta 90°.'],
            [$g('zancada-inversa-con-mancuernas'),       'Zancada inversa con mancuernas',       'Paso hacia atrás. Rodilla trasera baja sin tocar. Más segura para la rodilla que la frontal.'],
            [$g('zancada-curtsy-con-mancuerna'),         'Zancada curtsy con mancuerna',         'Pierna trasera cruza detrás de la delantera. Activa el glúteo externo y medio.'],
            [$g('abduccion-de-cadera-lateral'),          'Abducción de cadera lateral',          'En colchoneta de lado. Levanta la pierna con el glúteo, no con la cadera.'],
            [$g('zancada-lateral-con-mancuerna'),        'Zancada lateral con mancuerna',        'Paso amplio hacia el lado, baja como sentadilla lateral. Activa glúteo medio y externo.'],
        ],
    ],
    'Jueves' => [
        'grupo_muscular' => 'Espalda + Bíceps + Abs',
        'ejercicios' => [
            [$g('remo-con-mancuernas-sobre-banco-inclinado'), 'Remo en banco inclinado con mancuernas', 'Pecho apoyado en el banco. Codos cerca del cuerpo, jala hacia la cadera.'],
            [$g('remo-con-mancuernas'),                       'Remo bilateral con mancuernas',          'Torso inclinado 45°. Jala ambas mancuernas hacia el ombligo al mismo tiempo.'],
            [$g('pullover-con-mancuerna'),                    'Pullover con mancuerna',                 'Acostada en el banco. Baja la mancuerna detrás de la cabeza con brazos casi rectos.'],
            [$g('curl-biceps-con-mancuerna'),                 'Curl de bíceps con mancuerna',           'Codos pegados al cuerpo. Supina la muñeca al subir. Alterna brazos.'],
            [$g('curl-martillo-con-mancuerna'),               'Curl martillo con mancuerna',            'Agarre neutro (pulgares arriba). Trabaja el braquial además del bíceps.'],
            [$g('curl-concentrado-con-mancuerna'),            'Curl concentrado con mancuerna',         'Codo apoyado en el muslo. Solo el antebrazo se mueve. 1 brazo a la vez.'],
            [$g('elevacion-de-piernas-acostado'),              'Elevación de piernas acostada',          'Espalda plana en el suelo. Piernas rectas, baja sin tocar el suelo. Abdomen apretado.'],
            [$g('crunch-codo-a-rodilla'),                     'Crunch codo a rodilla',                  'Codo opuesto a la rodilla que sube. Movimiento lento y controlado. Sin jalarte del cuello.'],
        ],
    ],
    'Viernes' => [
        'grupo_muscular' => 'Femoral + Glúteo',
        'ejercicios' => [
            [$g('peso-muerto-rumano-con-mancuerna'),          'Peso muerto rumano con mancuerna',           'Espalda recta, rodilla ligeramente doblada. Siente el estiramiento del femoral al bajar.'],
            [$g('peso-muerto-a-una-pierna-con-mancuernas'),   'Peso muerto a una pierna con mancuernas',    'Si pierdes el equilibrio, apóyate en algo. Enfócate en el estiramiento del femoral.'],
            [$g('curl-femoral-acostado-en-maquina'),          'Curl femoral acostado en máquina',           'Contrae el femoral al subir y baja lento en 3 seg. No des impulso con la cadera.'],
            [$g('puente-de-gluteo-con-mancuerna'),            'Puente de glúteo con mancuerna',             'Mancuerna en caderas. Empuja desde los talones, aprieta el glúteo 1 seg arriba.'],
            [$g('abduccion-de-cadera-lateral'),               'Abducción de cadera lateral',                'Acostada de lado. Movimiento lento. Siente el glúteo medio, no la cadera.'],
            [$g('zancada-curtsy-con-mancuerna'),              'Zancada curtsy con mancuerna',               'Ideal para glúteo externo y femoral distal. Mantén el torso erguido.'],
        ],
    ],
];

$cardio = [
    'nombre'       => 'Caminadora inclinada',
    'gif_url'      => '',
    'series'       => 1,
    'repeticiones' => '20 min',
    'rir'          => null,
    'descanso'     => '-',
    'notas'        => 'Velocidad 4-5 km/h, inclinación 10-12%. Sin apoyarte en los parabrazos. Ritmo constante.',
    'tipo'         => 'cardio',
];

$semanas = [];
foreach ($weekParams as $weekNum => $params) {
    $dias = [];
    foreach ($dayTemplates as $diaNombre => $dayData) {
        $ejercicios = [];
        foreach ($dayData['ejercicios'] as $ejData) {
            [$gifUrl, $nombre, $notas] = $ejData;
            $meta = $ejData[3] ?? [];

            if (isset($meta['repeticiones']) && $meta['repeticiones'] === null) {
                // Plancha: reps = tiempo según semana, rir = null
                $reps = $planchaTime[$weekNum];
                $rir  = null;
            } elseif (isset($meta['override_reps'])) {
                // Bicicleta crunch: reps fijas por semana
                $reps = $biciReps[$weekNum];
                $rir  = $params['rir'];
            } else {
                $reps = $params['repeticiones'];
                $rir  = $params['rir'];
            }

            $ejercicios[] = [
                'nombre'       => $nombre,
                'gif_url'      => $gifUrl,
                'series'       => $params['series'],
                'repeticiones' => $reps,
                'rir'          => $rir,
                'descanso'     => $params['descanso'],
                'notas'        => $notas,
            ];
        }
        $ejercicios[] = $cardio;
        $dias[] = [
            'dia'            => $diaNombre,
            'dia_semana'     => $diaNombre,
            'grupo_muscular' => $dayData['grupo_muscular'],
            'ejercicios'     => $ejercicios,
        ];
    }
    $semanas[] = [
        'semana'       => $weekNum,
        'fase'         => $params['fase'],
        'series'       => $params['series'],
        'repeticiones' => $params['repeticiones'],
        'rir'          => $params['rir'],
        'descanso'     => $params['descanso'],
        'dias'         => $dias,
    ];
}

$trainingArr = [
    'titulo'           => 'Plan de Entrenamiento — Carolina Valero',
    'objetivo'         => 'Pérdida de grasa con preservación muscular',
    'nivel'            => 'Intermedio',
    'duracion_semanas' => 4,
    'fecha_inicio'     => '2026-05-11',
    'dias_semana'      => ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'],
    'split' => [
        'Lunes'     => 'Cuádriceps + Pantorrilla',
        'Martes'    => 'Hombros + Tríceps + Abs',
        'Miércoles' => 'Glúteos',
        'Jueves'    => 'Espalda + Bíceps + Abs',
        'Viernes'   => 'Femoral + Glúteo',
    ],
    'calentamiento' => [
        'descripcion' => '5 minutos de movilidad articular antes de cada sesión.',
        'ejercicios'  => [
            'Círculos de cadera 30 seg por lado',
            'Apertura de cadera (frog stretch) 30 seg',
            'Rotaciones de hombros 10 reps hacia adelante y atrás',
            'Sentadilla con peso corporal lenta x 10 reps',
            'Movilidad de tobillo en pared 10 reps por lado',
        ],
    ],
    'notas_coach' => "Carolina, con 41 años y 78 kg arrancas en un punto donde el entrenamiento con mancuernas te va a dar exactamente lo que necesitas. No tienes que ir a un gym con 15 máquinas para transformar tu cuerpo — con lo que tienes, bien usado, los resultados llegan.\n\nEste plan de 5 días combina fuerza con quema calórica en cada sesión: primero el trabajo de pesas para construir y preservar músculo, y al final 20 minutos de caminadora inclinada que activan la quema de grasa sin destruir la recuperación. Los días de glúteos, femoral y cuádriceps son los más importantes — esos grupos grandes son los que aceleran tu metabolismo de base.\n\nLas primeras 2 semanas van a sentirse manejables. Es la fase de adaptación. La semana 3 y 4 sube la intensidad y los descansos se reducen — ahí es cuando el cuerpo empieza a cambiar de verdad. Si algo se siente fácil, sube el peso; si no completas las reps, bájalo.\n\nEmpieza el lunes 11 de mayo. Primera semana: enfócate en la técnica, no en el peso. Anota cuánto moviste en cada ejercicio — eso te guía para progresar en semana 2.",
    'tips' => [
        'Sube el peso cuando puedas completar todas las reps con 2 de sobra (RIR 2 o más).',
        'Los descansos entre series son parte del plan. No los acortes más de lo indicado.',
        'Si un día no puedes ir, continúa al siguiente — no intentes recuperar sesiones perdidas.',
        'Toma agua antes, durante y después de cada sesión. Mínimo 500ml durante el entreno.',
        'Los días de glúteos (miércoles) y femoral+glúteo (viernes) son los más importantes para tu objetivo.',
        'Anota tu peso en cada ejercicio por semana. La progresión de carga es clave para el progreso.',
        'Las 4 semanas forman un bloque completo. Al terminar, diseñamos el ciclo siguiente.',
    ],
    'semanas' => $semanas,
];

$entrenamiento = json_encode($trainingArr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

// ─── PLAN NUTRICIÓN ───────────────────────────────────────────────────────────

$nutricionArr = [
    'titulo'           => 'Plan Nutricional — Carolina Valero',
    'objetivo'         => 'Déficit calórico para pérdida de grasa preservando músculo',
    'objetivo_cal'     => 1750,
    'duracion_semanas' => 4,
    'fecha_inicio'     => '2026-05-11',
    'macros' => [
        'proteina_g'       => 156,
        'carbohidratos_g'  => 124,
        'grasas_g'         => 70,
        'calorias'         => 1750,
    ],
    'hidratacion' => [
        'meta_litros' => 2.5,
        'notas'       => 'Mínimo 2.5 litros al día. Puedes incluir té verde sin azúcar o agua con limón.',
    ],
    'notas_coach' => "Carolina, tus macros están calculados exactamente para que quemes grasa sin perder el músculo que estás trabajando. 1,750 calorías con 156g de proteína es lo que necesitas para entrenar 5 días y que tu cuerpo use grasa — no músculo — como energía.\n\nLas opciones A, B y C en cada comida son equivalentes en macros aunque diferentes en sabor. Escoge la que tengas disponible ese día, no tienes que seguir siempre la misma. Lo que sí es fijo: siempre hay proteína en cada comida, siempre hay vegetales, y los carbos son de fuentes con fibra — no pan blanco, no cereales de caja, no azúcares simples.\n\nLas primeras 2 semanas vas a sentir que es mucho o poco — eso depende de cómo comías antes. A partir de la semana 3 el hambre se regula y la energía mejora. Si sientes mucha hambre entre comidas, añade una taza de caldo de verduras sin sal — llena sin calorías.\n\nEmpieza el lunes 11 con la opción que tengas más a mano. No busques la dieta perfecta, busca la que puedas mantener — esa es la que funciona.",
    'tips_nutricionales' => [
        'Pesa la proteína en crudo: el pollo pierde hasta 30% de peso al cocinarse.',
        'Aguacate y aceite de oliva son tus grasas principales. No los elimines aunque quieras acelerar.',
        'Si entrenas en la mañana, mueve la merienda a antes del entreno como pre-workout natural.',
        'Los días que no entrenes, mantén el mismo plan. El déficit funciona los 7 días, no solo 5.',
        'Evita alcohol las primeras 4 semanas. No prohíbe el progreso pero lo hace más lento.',
        'Toma 2.5 litros de agua mínimo al día. El hambre a veces es sed disfrazada.',
    ],
    'tips' => [
        'Prepara tus proteínas en batch los domingos: pollo, huevos duros y atún listos te salvan la semana.',
        'Los vegetales son libres: come la cantidad que quieras de brócoli, espinacas, pepino y lechuga.',
        'El yogur griego 0% y el requesón son tus mejores aliados para proteína rápida.',
        'Si comes fuera, elige proteína a la plancha + ensalada + una porción de carbos simples al lado.',
    ],
    'comidas' => [
        [
            'nombre'   => 'Desayuno',
            'hora'     => '7:00 - 8:00 AM',
            'calorias' => 380,
            'macros'   => ['proteina' => 35, 'carbohidratos' => 25, 'grasas' => 15],
            'opcion_a' => [
                '2 huevos enteros revueltos (100g)',
                '2 claras de huevo adicionales (60g)',
                '1/3 de aguacate (50g)',
                'Espinacas salteadas en spray (100g)',
                'Café negro sin azúcar',
            ],
            'opcion_b' => [
                '1 taza de yogur griego 0% grasa (200g)',
                '1/2 scoop de proteína whey (15g)',
                '1/3 taza de avena en hojuelas en remojo (30g)',
                '1/2 taza de fresas frescas (75g)',
                'Café negro sin azúcar',
            ],
            'opcion_c' => [
                'Omelette de 3 claras + 1 huevo entero',
                'Champiñones, pimiento y espinacas salteados al gusto',
                '1 rebanada de pan integral (35g)',
                '1 cucharadita de aceite de oliva para cocinar (5g)',
                'Café o té verde sin azúcar',
            ],
            'notas' => 'El desayuno es clave para llegar bien al entreno. Si entrenas muy temprano, toma solo el café y desayuna después.',
        ],
        [
            'nombre'   => 'Almuerzo',
            'hora'     => '12:00 - 1:00 PM',
            'calorias' => 550,
            'macros'   => ['proteina' => 50, 'carbohidratos' => 45, 'grasas' => 18],
            'opcion_a' => [
                '180g de pechuga de pollo al horno con especias',
                '1/2 taza de arroz integral cocido (90g)',
                'Brócoli y zanahoria al vapor (200g)',
                '1 cucharada de aceite de oliva extra virgen (10g)',
                'Agua o té frío sin azúcar',
            ],
            'opcion_b' => [
                '2 latas de atún en agua escurrido (160g)',
                '1 papa mediana cocida con cáscara (150g)',
                'Ensalada verde grande con tomate cherry (200g)',
                '1 cucharada de aceite de oliva + jugo de limón',
            ],
            'opcion_c' => [
                '150g de carne de res magra (lomo o cadera) a la plancha',
                '1/2 taza de lentejas cocidas (100g)',
                'Espárragos o judías verdes al vapor (200g)',
                'Vinagreta de limón y mostaza sin azúcar',
            ],
            'notas' => 'Es tu comida más grande. Si entrenas a mediodía, considera comer 1.5-2 horas antes.',
        ],
        [
            'nombre'   => 'Merienda',
            'hora'     => '3:30 - 4:30 PM',
            'calorias' => 280,
            'macros'   => ['proteina' => 28, 'carbohidratos' => 22, 'grasas' => 10],
            'opcion_a' => [
                '200g de yogur griego 0% grasa',
                '1.5 cucharadas de mantequilla de maní natural (20g)',
                '3/4 taza de fresas o arándanos frescos (100g)',
            ],
            'opcion_b' => [
                '150g de requesón (cottage cheese) bajo en grasa',
                '1/2 taza de arándanos o uvas (75g)',
                '1 cucharada de nueces picadas (14g)',
            ],
            'opcion_c' => [
                '1 scoop de proteína whey en 300ml de agua (30g)',
                '1 manzana mediana (130g)',
                '10 almendras (14g)',
            ],
            'notas' => 'Si entrenas por la tarde, toma esta merienda 1h antes del entreno. La opción C (shake) funciona bien como pre-workout.',
        ],
        [
            'nombre'   => 'Cena',
            'hora'     => '7:00 - 8:00 PM',
            'calorias' => 540,
            'macros'   => ['proteina' => 43, 'carbohidratos' => 32, 'grasas' => 27],
            'opcion_a' => [
                '180g de salmón al horno con hierbas y limón',
                '1/2 taza de quinoa cocida (90g)',
                'Ensalada de espinacas, pepino y tomate cherry',
                '1/2 aguacate (60g)',
                'Té de manzanilla sin azúcar',
            ],
            'opcion_b' => [
                '170g de pechuga de pollo a la plancha con especias',
                '1 batata mediana asada (150g)',
                'Vegetales mixtos al horno (200g)',
                '1 cucharada de aceite de oliva',
            ],
            'opcion_c' => [
                '4 huevos enteros en tortilla con champiñones, pimiento y espinacas',
                '1/2 batata mediana asada (100g)',
                '100g de requesón (cottage cheese) 0% grasa al lado',
                '1/3 aguacate (55g)',
                'Té de manzanilla o agua',
            ],
            'notas' => 'Cena alta en proteína y moderada en carbos. El salmón (opción A) suma omega 3 que ayuda a la recuperación muscular.',
        ],
    ],
];

$nutricion = json_encode($nutricionArr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

// ─── PLAN SUPLEMENTACIÓN ─────────────────────────────────────────────────────

$suplementacionArr = [
    'titulo'                => 'Protocolo de Suplementación — Carolina Valero',
    'descripcion_protocolo' => 'Stack básico para pérdida de grasa con preservación muscular',
    'perfil_cliente'        => 'Mujer 41 años, 78 kg, objetivo pérdida de grasa, 5 días de entrenamiento',
    'advertencia'           => 'Si tomas algún medicamento recetado, consulta con tu médico antes de añadir cafeína.',
    'categorias' => [
        [
            'nombre' => 'Rendimiento',
            'suplementos' => [
                [
                    'nombre'    => 'Creatina monohidrato',
                    'dosis'     => '5g',
                    'timing'    => 'Con el desayuno',
                    'prioridad' => 'esencial',
                    'notas'     => 'Toma diaria, incluso días de descanso. No necesita ciclo ni carga. Disuélvela en agua o jugo.',
                ],
                [
                    'nombre'    => 'Cafeína anhidra',
                    'dosis'     => '100-150mg',
                    'timing'    => 'Pre-entreno (30 min antes)',
                    'prioridad' => 'recomendado',
                    'notas'     => 'Solo días de pesas. No tomar después de las 3 PM. Si ya tomas café antes de entrenar, puede ser suficiente.',
                ],
            ],
        ],
        [
            'nombre' => 'Recuperación',
            'suplementos' => [
                [
                    'nombre'    => 'Proteína whey',
                    'dosis'     => '1 scoop (25-30g)',
                    'timing'    => 'Post-entreno',
                    'prioridad' => 'esencial',
                    'notas'     => 'En agua, no en leche. Si no tienes whey, 2 claras + 1 taza de leche descremada es alternativa funcional.',
                ],
            ],
        ],
        [
            'nombre' => 'Salud',
            'suplementos' => [
                [
                    'nombre'    => 'Omega 3 (EPA+DHA)',
                    'dosis'     => '2g (2 cápsulas)',
                    'timing'    => 'Con el almuerzo',
                    'prioridad' => 'recomendado',
                    'notas'     => 'Reduce inflamación y mejora la recuperación articular. Tómalo con comida que contenga grasa.',
                ],
                [
                    'nombre'    => 'Vitamina D3',
                    'dosis'     => '2000 UI',
                    'timing'    => 'Con el desayuno',
                    'prioridad' => 'recomendado',
                    'notas'     => 'En Minnesota con inviernos largos, probablemente la necesitas todo el año. Apoya la función muscular y el estado de ánimo.',
                ],
            ],
        ],
    ],
    'timing_diario' => [
        ['momento' => 'Con el desayuno',           'suplementos' => 'Creatina 5g + Vitamina D3 2000 UI'],
        ['momento' => 'Pre-entreno (30 min antes)', 'suplementos' => 'Cafeína 100-150mg (solo días de pesas)'],
        ['momento' => 'Post-entreno',               'suplementos' => 'Whey 1 scoop en agua'],
        ['momento' => 'Con el almuerzo',            'suplementos' => 'Omega 3 2g (2 cápsulas)'],
    ],
    'sinergias' => [
        [
            'titulo'      => 'Creatina + Carbos en el desayuno',
            'explicacion' => 'La insulina que genera el desayuno mejora la absorción de creatina. No hay que tomarla separada.',
        ],
        [
            'titulo'      => 'Omega 3 + Vitamina D3 con grasa',
            'explicacion' => 'Ambos son liposolubles. Tómalos con comidas que incluyan aguacate, huevo o aceite de oliva.',
        ],
    ],
    'notas_coach'   => 'Carolina, estos 5 suplementos son los que tienen evidencia real para tu objetivo. No gastes en "quemadores de grasa" — no existen. La creatina y el whey son los más importantes; si solo puedes comprar 2, son esos. La cafeína es opcional si ya tomas café antes de entrenar. El omega 3 y la D3 son inversión en salud a largo plazo.',
    'mensaje_final' => 'El suplemento más poderoso es la consistencia. Sin eso, ningún polvo funciona.',
];

$suplementacion = json_encode($suplementacionArr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

// ─── VALIDAR JSONS ────────────────────────────────────────────────────────────

echo "Iniciando inserción de planes para Carolina Valero (client_id={$clientId})...\n\n";

foreach (['entrenamiento' => $entrenamiento, 'nutricion' => $nutricion, 'suplementacion' => $suplementacion] as $tipo => $json) {
    if (json_decode($json) === null) {
        die("❌ JSON {$tipo} inválido: " . json_last_error_msg() . "\n");
    }
    echo "✅ JSON {$tipo} válido (" . strlen($json) . " bytes)\n";
}

// ─── DESACTIVAR PLANES EXISTENTES ────────────────────────────────────────────

$deactivate = $pdo->prepare("UPDATE assigned_plans SET active=0 WHERE client_id=? AND plan_type=? AND active=1");
foreach (['entrenamiento', 'nutricion', 'suplementacion'] as $tipo) {
    $deactivate->execute([$clientId, $tipo]);
    echo "✅ Plan anterior '{$tipo}' desactivado (affected: " . $deactivate->rowCount() . ")\n";
}

// ─── INSERTAR NUEVOS PLANES ───────────────────────────────────────────────────

$insert = $pdo->prepare(
    "INSERT INTO assigned_plans (client_id, plan_type, content, assigned_by, valid_from, expires_at, active, created_at) VALUES (?, ?, ?, ?, ?, ?, 1, NOW())"
);

$insert->execute([$clientId, 'entrenamiento',  $entrenamiento,  $coachId, $validFrom, $expiresAt]);
echo "✅ Plan entrenamiento insertado (ID: " . $pdo->lastInsertId() . ")\n";

$insert->execute([$clientId, 'nutricion',      $nutricion,      $coachId, $validFrom, $expiresAt]);
echo "✅ Plan nutricion insertado (ID: " . $pdo->lastInsertId() . ")\n";

$insert->execute([$clientId, 'suplementacion', $suplementacion, $coachId, $validFrom, $expiresAt]);
echo "✅ Plan suplementacion insertado (ID: " . $pdo->lastInsertId() . ")\n";

// ─── INVALIDAR CACHÉ ─────────────────────────────────────────────────────────

try {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379, 2);
    $keys = ["client_plan_v3_{$clientId}", "wp:plan:{$clientId}", "wp:weekdays:{$clientId}", "dashboard:{$clientId}"];
    foreach ($keys as $key) {
        $redis->del($key);
    }
    echo "✅ Cache Redis invalidado (" . count($keys) . " keys)\n";
} catch (\Exception $e) {
    echo "⚠️  Redis no disponible: " . $e->getMessage() . "\n";
}

// ─── VERIFICACIÓN ────────────────────────────────────────────────────────────

$planes = $pdo->query(
    "SELECT id, plan_type, active, valid_from, expires_at FROM assigned_plans WHERE client_id={$clientId} AND active=1 ORDER BY id DESC"
)->fetchAll(PDO::FETCH_ASSOC);

echo "\n--- Planes activos para client_id={$clientId} ---\n";
foreach ($planes as $p) {
    echo "  [{$p['id']}] {$p['plan_type']} | active={$p['active']} | {$p['valid_from']} → {$p['expires_at']}\n";
}

echo "\n🎉 ¡Listo! Los 3 planes de Carolina Valero están activos.\n";
echo "   Válidos: {$validFrom} → {$expiresAt}\n";
echo "\nVerificar: impersonar client_id=93 desde /admin/clients → revisar /client/plan\n";
