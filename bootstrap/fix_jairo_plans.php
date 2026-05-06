<?php
$pdo = new PDO(
    'mysql:host=wellcorefitness_wellcorefitness-mysql;dbname=wellcorefitness;charset=utf8mb4',
    'wellcorefitness', 'fYCVgn4XZ7twq34',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// ─── 1. GIF alias mapping (incorrect underscore names → correct dash names) ───
$gifMap = [
    'abduccion_maquina'               => 'abduccion-de-cadera-sentado-en-maquina',
    'bulgara_smith'                   => 'sentadilla-bulgara-barra',
    'copa_triceps'                    => 'extension-de-triceps-sobre-cabeza-con-cuerda',
    'cruce_polea'                     => 'crossover-en-polea-de-pie',
    'crunch_polea'                    => 'crunch-en-polea-arrodillado',
    'curl_biceps_barra_z'             => 'curl-biceps-barra-ez',
    'curl_biceps_mancuerna'           => 'curl-biceps-con-mancuerna',
    'curl_femoral_acostado'           => 'curl-femoral-acostado-en-maquina',
    'curl_femoral_sentado'            => 'curl-femoral-sentado',
    'curl_martillo'                   => 'curl-martillo-con-mancuerna',
    'elevacion_piernas'               => 'elevacion-de-piernas-acostado',
    'elevacion_talones_smith'         => 'elevacion-de-talones-en-maquina',
    'elevaciones_laterales_mancuerna' => 'elevacion-lateral-con-mancuerna',
    'elevaciones_laterales_polea'     => 'elevacion-lateral-en-polea',
    'estocada_inversa_mancuerna'      => 'zancada-inversa-con-mancuernas',
    'extension_cuadriceps'            => 'extension-de-piernas-en-maquina',
    'extension_triceps_soga'          => 'extension-de-triceps-en-polea-con-cuerda',
    'facepull'                        => 'facepull-en-polea',
    'hip_thrust'                      => 'hipthrust-con-barra',
    'jalon_al_pecho'                  => 'jalon-al-pecho-en-maquina',
    'jalon_estrecho'                  => 'jalon-en-polea-con-agarre-v',
    'pec_deck'                        => 'aperturas-en-peck-deck',
    'peso_muerto_rumano_mancuernas'   => 'peso-muerto-rumano-con-mancuerna',
    'plancha_isometrica'              => 'plancha-abdominal',
    'prensa_45'                       => 'presa-de-piernas-abierto',
    'press_inclinado_barra'           => 'press-banca-inclinado-con-barra',
    'press_inclinado_smith'           => 'press-de-pecho-inclinado-en-maquina',
    'press_militar_mancuernas'        => 'press-de-hombro-con-mancuerna',
    'press_plano_maquina'             => 'press-de-pecho-en-maquina',
    'pullover'                        => 'pullover-con-mancuerna',
    'remo_mancuernas'                 => 'remo-con-mancuernas',
    'remo_maquina'                    => 'remo-sentado-en-maquina',
    'remo_polea_baja'                 => 'remo-en-polea-sentado',
    'sentadilla_hack'                 => 'sentadilla-hacka',
];
$base = 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/';

function fixGifUrl($url, $map, $base) {
    if (!$url) return $url;
    $name = basename($url, '.gif');
    return isset($map[$name]) ? $base . $map[$name] . '.gif' : $url;
}

function fixGifsInPlan(&$data, $map, $base) {
    if (!is_array($data)) return;
    if (isset($data['gif_url'])) $data['gif_url'] = fixGifUrl($data['gif_url'], $map, $base);
    if (isset($data['variacion']['gif_url'])) $data['variacion']['gif_url'] = fixGifUrl($data['variacion']['gif_url'], $map, $base);
    foreach ($data as $k => &$v) { if (is_array($v)) fixGifsInPlan($v, $map, $base); }
}

// ─── 2. Patch entrenamiento (ID 169) ────────────────────────────────────
$stmt = $pdo->prepare('SELECT content FROM assigned_plans WHERE id=169');
$stmt->execute();
$row  = $stmt->fetch();
$train = json_decode($row['content'], true);

fixGifsInPlan($train, $gifMap, $base);

$train['split'] = [
    'Lunes'     => 'Piernas Anterior + Abs',
    'Martes'    => 'Pecho + Hombros',
    'Miercoles' => 'Espalda + Biceps',
    'Jueves'    => 'Piernas Posterior + Core',
    'Viernes'   => 'Hombros + Triceps',
    'Sabado'    => 'HIIT + Full Body',
];
$train['fecha_inicio'] = '2026-05-05';
$train['valid_from']   = '2026-05-05';
$train['fecha_fin']    = '2026-06-02';

$fasesCorrectas = ['Hipertrofia', 'Fuerza', 'Fuerza Maxima', 'Peak'];
if (isset($train['semanas'])) {
    foreach ($train['semanas'] as $i => &$sem) {
        if (isset($fasesCorrectas[$i])) $sem['fase'] = $fasesCorrectas[$i];
    }
    unset($sem);
}

$trainJson = json_encode($train, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$pdo->prepare('UPDATE assigned_plans SET content=? WHERE id=169')->execute([$trainJson]);
echo 'Entrenamiento ID 169 actualizado. GIFs corregidos: ' . count($gifMap) . "\n";

// ─── 3. New nutricion plan (ID 170) ────────────────────────────────────
$nutri = [
    'plan_type'          => 'nutricion',
    'titulo'             => 'Plan Nutricional Mes 2 - Jairo Rivera',
    'cliente'            => 'Jairo Rivera',
    'metodologia'        => 'Recomposicion corporal + Peak CrossFit 28 mayo',
    'objetivo_calorico'  => 2100,
    'objetivo_cal'       => 2100,
    'objetivo'           => 'Recomposicion avanzada. 2100 kcal base con protocolo de carga 26-28 mayo para peak de rendimiento en CrossFit.',
    'macros'             => ['proteina_g' => 200, 'carbohidratos_g' => 130, 'grasas_g' => 65],
    'hidratacion'        => [
        'agua_minima_litros' => 3.5,
        'electrolitos'       => 'Semana de competencia 26-28 mayo: 4L/dia + electrolitos en cada sesion de entrenamiento.',
    ],
    'fecha_inicio'       => '2026-05-05',
    'tips_nutricionales' => [
        'Proteina primero: si no llegas a las calorias pero cumples 200g de proteina, el dia sigue siendo productivo.',
        'Las 3 opciones por comida tienen macros equivalentes - elige la que tengas a mano ese dia.',
        'Los dias 26-28 mayo duplicas carbos a 280g para saturar glucogeno. La sensacion de llenura es normal.',
        'Post-entreno: whey + carbo en los primeros 30 minutos despues de entrenar. Ventana critica.',
        'Hidratacion base 3.5L. Semana de competencia sube a 4L. Si orinas amarillo oscuro, toma mas.',
        'Grasas bajas los dias de carga 26-28 mayo: no mas de 60g para no enlentecer la digestion.',
        'Carbos mas altos pre-entreno = mas energia y fuerza en el entreno. No te lo saltes.',
    ],
    'tips' => [
        'Proteina primero: si no llegas a las calorias pero cumples 200g de proteina, el dia sigue siendo productivo.',
        'Las 3 opciones por comida tienen macros equivalentes.',
        'Los dias 26-28 mayo duplicas carbos a 280g para el CrossFit.',
        'Post-entreno: whey + carbo en los primeros 30 minutos.',
        'Hidratacion base 3.5L diarios minimo.',
    ],
    'notas_coach' => "Jairo, la base de macros de este mes es identica al Mes 1: 2100 kcal, 200g proteina. No cambio nada porque venias respondiendo bien.\n\nLo que si cambia son los 3 dias antes del CrossFit del 28. El protocolo de carga del 26-28 mayo no es opcional - es la diferencia entre rendir al 85% o al 100% en competencia. Carbos a 280g esos dias: arroz blanco, papa, avena, banano. Sin grasas extras. Sin fibra excesiva el dia antes. Simple, limpio, abundante.\n\nLas 3 opciones por comida son intercambiables - los macros son casi iguales. Si un dia no tenes salmon, usa atun o pollo. Lo importante es la cantidad de proteina y los tiempos de comida.\n\nNo te saltes el post-entreno. Whey en los primeros 30 minutos despues de entrenar, con banano o arroz. Esa ventana es critica para recuperacion y para sostener la masa muscular mientras hacemos recomposicion.",
    'comidas' => [
        [
            'nombre'   => 'Desayuno',
            'hora'     => '7:00am',
            'calorias' => 520,
            'macros'   => ['proteina' => 38, 'carbohidratos' => 68, 'grasas' => 10],
            'opcion_a' => [
                'Claras de huevo (4 unidades)',
                'Huevo entero (1 unidad)',
                'Avena en hojuelas (80g)',
                'Banano mediano (1 unidad)',
                'Cafe negro sin azucar',
            ],
            'opcion_b' => [
                'Yogur griego natural 0% grasa (250g)',
                'Granola sin azucar (50g)',
                'Fresas o arandanos (100g)',
                'Claras de huevo (3 unidades)',
                'Cafe negro sin azucar',
            ],
            'opcion_c' => [
                'Arepa de maiz integral (1 grande, 80g)',
                'Queso cottage bajo en grasa (150g)',
                'Huevos enteros (2 unidades)',
                'Papaya o pina (100g)',
                'Cafe negro sin azucar',
            ],
            'notas_comida' => 'Primera comida del dia. Proteina + carbos de digestion media para energia sostenida hasta el entreno.',
        ],
        [
            'nombre'   => 'Pre-entreno',
            'hora'     => '11:00am',
            'calorias' => 445,
            'macros'   => ['proteina' => 45, 'carbohidratos' => 38, 'grasas' => 12],
            'opcion_a' => [
                'Pollo a la plancha (150g)',
                'Arroz integral cocido (120g)',
                'Aguacate (40g, aprox 1/4 unidad)',
                'Vegetales al gusto',
            ],
            'opcion_b' => [
                'Atun en agua escurrido (200g)',
                'Papa cocida con cascara (200g)',
                'Aceite de oliva (1 cdta)',
                'Ensalada de tomate y pepino',
            ],
            'opcion_c' => [
                'Pechuga de pollo al horno (150g)',
                'Pasta integral cocida (100g en seco)',
                'Aceite de oliva (1 cdta)',
                'Brocoli o zanahoria al vapor',
            ],
            'notas_comida' => 'Cometela 60-90 minutos antes de entrenar. Carbos de digestion media para energia sostenida en el gym.',
        ],
        [
            'nombre'   => 'Post-entreno',
            'hora'     => '2:00pm',
            'calorias' => 440,
            'macros'   => ['proteina' => 55, 'carbohidratos' => 43, 'grasas' => 5],
            'opcion_a' => [
                'Whey Isolate (60g, 2 scoops en agua fria)',
                'Banano maduro (1 unidad grande)',
                'Arroz blanco cocido (80g)',
            ],
            'opcion_b' => [
                'Whey Isolate (40g, 1.5 scoops)',
                'Avena en hojuelas (60g)',
                'Miel pura (1 cdta)',
                'Banano (1/2 unidad)',
            ],
            'opcion_c' => [
                'Claras de huevo revueltas (8 unidades)',
                'Camote o yuca cocida (100g)',
                'Fresas (100g)',
                'Agua con electrolitos',
            ],
            'notas_comida' => 'CRITICO: dentro de los 30 minutos post-entreno. Esta ventana es cuando el musculo absorbe mas eficientemente los nutrientes. Whey en agua, no en leche.',
        ],
        [
            'nombre'   => 'Merienda',
            'hora'     => '5:30pm',
            'calorias' => 385,
            'macros'   => ['proteina' => 40, 'carbohidratos' => 30, 'grasas' => 12],
            'opcion_a' => [
                'Carne de res magra (150g, lomo o solomo)',
                'Papa cocida o yuca (100g)',
                'Verduras de hoja verde al gusto',
                'Aceite de oliva (1 cdta)',
            ],
            'opcion_b' => [
                'Pechuga de pollo asada (150g)',
                'Arroz blanco cocido (80g)',
                'Zanahoria y pepino fresco',
            ],
            'opcion_c' => [
                'Atun en agua escurrido (200g)',
                'Aguacate (40g)',
                'Galletas de arroz integral (30g)',
                'Tomate y lechuga',
            ],
            'notas_comida' => 'Merienda de la tarde. Mantiene aminoacidos en sangre entre post-entreno y cena. No te la saltes aunque no tengas hambre.',
        ],
        [
            'nombre'   => 'Cena',
            'hora'     => '8:30pm',
            'calorias' => 435,
            'macros'   => ['proteina' => 55, 'carbohidratos' => 5, 'grasas' => 22],
            'opcion_a' => [
                'Salmon al horno (180g)',
                'Huevos enteros (2 unidades)',
                'Espinacas salteadas con ajo (150g)',
                'Aceite de oliva (1 cdta)',
            ],
            'opcion_b' => [
                'Atun en agua escurrido (200g)',
                'Huevos enteros (3 unidades)',
                'Brocoli al vapor (200g)',
                'Aceite de coco o oliva (1 cdta)',
            ],
            'opcion_c' => [
                'Pechuga de pollo (200g)',
                'Aguacate (60g, 1/3 unidad)',
                'Ensalada verde grande con tomate y pepino',
                'Almendras naturales (20g)',
            ],
            'notas_comida' => 'Cena alta en proteina y grasas saludables. Carbos minimos en la noche para que el cuerpo use reservas de grasa durante el sueno.',
        ],
    ],
    'protocolo_carga_competencia' => [
        'titulo'   => 'CARGA DE CARBOHIDRATOS - CrossFit 28 mayo',
        'objetivo' => 'Saturar glucogeno muscular para rendimiento maximo en competencia',
        'dias'     => [
            ['fecha' => 'Martes 26 mayo', 'etapa' => 'DIA 1 CARGA', 'calorias' => 2800, 'proteina_g' => 200, 'carbohidratos_g' => 280, 'grasas_g' => 60, 'instrucciones' => 'Duplica los carbos: arroz blanco, papa, avena, banano. Proteina igual. Grasas al minimo. Sin entreno intenso.'],
            ['fecha' => 'Miercoles 27 mayo', 'etapa' => 'DIA 2 CARGA', 'calorias' => 2800, 'proteina_g' => 200, 'carbohidratos_g' => 280, 'grasas_g' => 60, 'instrucciones' => 'Igual que DIA 1. Hidratacion 4L. Solo movilidad leve o descanso total.'],
            ['fecha' => 'Jueves 28 mayo AM', 'etapa' => 'PRE-COMPETENCIA', 'calorias' => 2400, 'proteina_g' => 180, 'carbohidratos_g' => 210, 'grasas_g' => 55, 'instrucciones' => 'Desayuno 3h antes: avena 100g + 2 huevos + banano + miel. Snack 1h antes: arroz blanco 60g + banano. Sin fibra excesiva.'],
            ['fecha' => '28 mayo - Entre WODs', 'etapa' => 'DIA D', 'instrucciones' => 'Carbos rapidos entre WODs: gel de glucosa o arroz blanco 30-40g + agua con electrolitos. Proteina solo post-competencia.'],
        ],
        'notas' => 'La sensacion de llenura los dias 1 y 2 es glucogeno entrando a los musculos. Es exactamente lo que buscamos.',
    ],
];

$nutriJson = json_encode($nutri, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$pdo->prepare('UPDATE assigned_plans SET content=? WHERE id=170')->execute([$nutriJson]);
echo 'Nutricion ID 170 actualizada con opciones A/B/C + tips_nutricionales' . "\n";

// ─── 4. Clear Redis cache for Jairo (client_id=23) ──────────────────────
$redis = new Redis();
$redis->connect('wellcorefitness_wellcorefitness-redis', 6379);
$redis->auth('5s2umyip1fht8m5s2zm4');
$keys = ['plan_lock_status:23','client_plan_v3_23','wp:plan:23','wp:weekdays:23','dashboard:23'];
foreach ($keys as $k) { $redis->del($k); echo 'Cache cleared: ' . $k . "\n"; }
echo "DONE\n";
