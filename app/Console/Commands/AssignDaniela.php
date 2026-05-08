<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PDO;
use Redis;

class AssignDaniela extends Command
{
    protected $signature = 'wellcore:assign-daniela';
    protected $description = '[TMP] Asignar plan PPL×2 + nutrición + suplementación a Daniela (ID=96)';

    public function handle(): int
    {
        $pdo = new PDO(
            'mysql:host=wellcorefitness_wellcorefitness-mysql;dbname=wellcorefitness',
            'wellcorefitness',
            'fYCVgn4XZ7twq34'
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $clientId = 96;
        $coachId  = 7;
        $fi       = '2026-05-12';
        $G        = 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/';

        $ej = function ($nombre, $musculo, $gif, $notas, $equipo = '', $variNombre = '', $variGif = '') use ($G) {
            $e = [
                'nombre'   => $nombre,
                'musculo'  => $musculo,
                'notas'    => $notas,
                'gif_url'  => $G . $gif . '.gif',
                'bloque'   => 'normal',
                'descanso' => '90s',
            ];
            if ($equipo)     $e['equipo']    = $equipo;
            if ($variNombre) $e['variacion'] = ['nombre' => $variNombre, 'gif_url' => $G . $variGif . '.gif'];
            return $e;
        };

        $cardio = function ($notas = '5-6 km/h, 10-12% inclinación. FC 120-140 bpm. Post-pesas.') use ($G) {
            return [
                'nombre'       => 'Caminadora inclinada',
                'is_cardio'    => true,
                'repeticiones' => '30 min',
                'descanso'     => '-',
                'notas'        => $notas,
                'gif_url'      => $G . 'caminadora-inclinada.gif',
            ];
        };

        $makeWeek = function ($dias, $num, $fase, $series, $reps, $rir) {
            $d = [];
            foreach ($dias as $dia) {
                $nd = $dia;
                $ne = [];
                foreach ($dia['ejercicios'] as $ej) {
                    $nj = $ej;
                    if (empty($ej['is_cardio'])) {
                        $nj['series']       = $series;
                        $nj['repeticiones'] = $reps;
                        $nj['rir']          = $rir;
                    }
                    $ne[] = $nj;
                }
                $nd['ejercicios'] = $ne;
                $d[] = $nd;
            }
            return ['numero' => $num, 'fase' => $fase, 'dias' => $d];
        };

        $deactivate = function ($type) use ($pdo, $clientId) {
            $pdo->prepare("UPDATE assigned_plans SET active=0 WHERE client_id=? AND plan_type=? AND active=1")
                ->execute([$clientId, $type]);
            $this->line("  ↳ Planes anteriores '$type' desactivados");
        };

        $insertPlan = function ($type, $json, $weeks) use ($pdo, $clientId, $coachId, $fi) {
            $exp  = date('Y-m-d', strtotime($fi . ' + ' . ($weeks * 7) . ' days'));
            $stmt = $pdo->prepare(
                "INSERT INTO assigned_plans (client_id,plan_type,content,version,assigned_by,valid_from,expires_at,active,created_at) VALUES (?,?,?,1,?,?,?,1,NOW())"
            );
            $stmt->execute([
                $clientId,
                $type,
                json_encode($json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                $coachId,
                $fi,
                $exp,
            ]);
            return $pdo->lastInsertId();
        };

        // ---- DÍAS BASE PPL×2 ----
        $dias = [
            [
                'nombre'         => 'Lunes - Push A',
                'tipo'           => 'push',
                'grupo_muscular' => 'Pecho + Hombros + Tríceps',
                'duracion'       => '80 min',
                'calentamiento'  => '5 min bici + 2 series 15 reps press con barra vacía + rotaciones de hombro',
                'vuelta_calma'   => 'Estiramiento de pecho y hombros 5 min',
                'ejercicios'     => [
                    $ej('Press de banca con barra',           'Pecho',          'press-banca-barra',                        'Baja 2-3 seg, empuja explosivo. Escápulas retraídas siempre.',       'Barra + banco plano',        'Press de banca con mancuernas',  'press-de-banca-con-mancuernas'),
                    $ej('Press inclinado con mancuernas',     'Pecho superior', 'press-banca-inclinado-con-barra',          'Baja hasta sentir estiramiento. No juntes las mancuernas arriba.',  'Mancuernas + banco inclinado'),
                    $ej('Press de hombros con mancuernas',   'Hombro',         'press-de-hombro-con-mancuerna',            'No arquees la espalda. Lleva hasta la línea del oído.',             'Mancuernas'),
                    $ej('Elevaciones laterales',              'Hombro medial',  'elevacion-lateral-con-mancuerna',          'Sube hasta la altura del hombro. Codos ligeramente flexionados.',  'Mancuernas'),
                    $ej('Extensión tríceps polea con cuerda', 'Tríceps',        'extension-de-triceps-en-polea-con-cuerda', 'Abre la cuerda al final. Codos fijos al costado.',                  'Polea + cuerda',              'Fondos de tríceps',             'fondos-de-triceps'),
                    $cardio(),
                ],
            ],
            [
                'nombre'         => 'Martes - Pull A',
                'tipo'           => 'pull',
                'grupo_muscular' => 'Espalda + Bíceps',
                'duracion'       => '80 min',
                'calentamiento'  => '5 min bici + rotaciones de hombro + movilidad de escápulas',
                'vuelta_calma'   => 'Estiramiento de dorsal y bíceps 5 min',
                'ejercicios'     => [
                    $ej('Jalón al pecho en máquina',       'Dorsal',             'jalon-al-pecho-en-maquina',      'Lleva la barra al esternón. Aprieta el dorsal al final.',          'Polea alta',            'Dominadas',           'dominadas'),
                    $ej('Remo con barra',                  'Dorsal',             'remo-con-barra',                  'Tronco a 45°. Jala al abdomen. Aprieta escápulas 1 seg al final.', 'Barra'),
                    $ej('Remo sentado en polea',           'Dorsal',             'remo-en-polea-sentado',           'Pecho alto, espalda neutral. Codos van hacia atrás.',              'Polea + agarre neutro', 'Remo en máquina',     'remo-sentado-en-maquina'),
                    $ej('Curl bíceps barra EZ',            'Bíceps',             'curl-biceps-barra-ez',            'No balancees el torso. Pausa 1 seg arriba.',                       'Barra EZ'),
                    $ej('Curl martillo con mancuerna',     'Bíceps + Antebrazo', 'curl-martillo-con-mancuerna',     'Pulgar arriba. Alterna. Controla la bajada.',                      'Mancuernas'),
                    $cardio(),
                ],
            ],
            [
                'nombre'         => 'Miércoles - Legs A',
                'tipo'           => 'legs',
                'grupo_muscular' => 'Cuádriceps + Glúteo',
                'duracion'       => '85 min',
                'calentamiento'  => '5 min caminadora + sentadillas corporales 2×15 + movilidad de cadera',
                'vuelta_calma'   => 'Estiramiento cuádriceps, isquios y glúteo 8 min',
                'ejercicios'     => [
                    $ej('Sentadilla con barra',           'Cuádriceps + Glúteo', 'sentadilla-con-barra',              'Baja hasta paralelo. Rodillas siguen los pies. Pecho alto.',          'Barra + rack',          'Prensa con pies altos', 'prensa-de-piernas-cerrado'),
                    $ej('Prensa de piernas',              'Cuádriceps + Glúteo', 'prensa-de-piernas-cerrado',         'Pies arriba para más glúteo. No bloquees rodillas al extender.',      'Prensa'),
                    $ej('Zancada frontal con mancuernas', 'Cuádriceps + Glúteo', 'zancada-frontal-con-mancuerna',     'Paso largo para enfocar glúteo. Rodilla trasera cerca del piso.',    'Mancuernas'),
                    $ej('Curl femoral acostado',          'Femoral',             'curl-femoral-acostado-en-maquina',  'Aprieta isquios al final. No saques la cadera.',                      'Máquina curl femoral'),
                    $ej('Elevación de talones en máquina','Pantorrilla',         'elevacion-de-talones-en-maquina',   'Rango completo. Pausa 1 seg arriba y abajo.',                         'Máquina pantorrillas'),
                    $cardio('5-6 km/h, 8-10% inclinación. FC 120-130 bpm. Día de piernas — algo menos intenso el cardio.'),
                ],
            ],
            [
                'nombre'         => 'Jueves - Push B',
                'tipo'           => 'push',
                'grupo_muscular' => 'Hombros + Pecho variación + Tríceps',
                'duracion'       => '80 min',
                'calentamiento'  => '5 min bici + elevaciones frontales con banda + rotaciones de hombro',
                'vuelta_calma'   => 'Estiramiento hombros y tríceps 5 min',
                'ejercicios'     => [
                    $ej('Press de hombros con barra (militar)', 'Hombro',         'press-de-hombro-con-mancuerna',            'Activa el core durante todo el movimiento. No hiperextiendas la espalda.', 'Barra'),
                    $ej('Elevaciones frontales con mancuerna',  'Hombro frontal', 'elevacion-lateral-con-mancuerna',          'Sube hasta la altura del hombro. Pausa 1 seg arriba. Sin balanceo.',      'Mancuernas'),
                    $ej('Aperturas en banco plano',             'Pecho',          'press-de-banca-con-mancuernas',            'Baja con codos ligeramente flexionados. Siente el estiramiento en pecho.', 'Mancuernas + banco plano', 'Crossover en polea', 'extension-de-triceps-en-polea-con-cuerda'),
                    $ej('Elevaciones laterales en cable',       'Hombro medial',  'elevacion-lateral-con-mancuerna',          'Cable da tensión constante. No trampejes, usa un peso que permita contracción plena.', 'Polea baja'),
                    $ej('Fondos de tríceps en paralelas',       'Tríceps',        'fondos-de-triceps',                        'Cuerpo erguido para tríceps. Baja hasta 90° de codo.',                    'Paralelas', 'Extensión con mancuerna', 'extension-de-triceps-en-polea-con-cuerda'),
                    $cardio(),
                ],
            ],
            [
                'nombre'         => 'Viernes - Pull B',
                'tipo'           => 'pull',
                'grupo_muscular' => 'Espalda + Bíceps variación',
                'duracion'       => '80 min',
                'calentamiento'  => '5 min bici + rotaciones de hombro + movilidad de escápulas',
                'vuelta_calma'   => 'Estiramiento dorsal y bíceps 5 min',
                'ejercicios'     => [
                    $ej('Peso muerto rumano con barra',   'Femoral + Espalda baja', 'peso-muerto-rumano-con-barra',      'Barra pegada al cuerpo. Empuja cadera atrás. Espalda siempre neutral.', 'Barra'),
                    $ej('Remo con mancuerna a una mano',  'Dorsal',                 'remo-sentado-en-maquina',           'Jala hasta cadera, no al hombro. Columna paralela al piso.',            'Mancuerna + banco'),
                    $ej('Face pull en polea',             'Hombro posterior',       'elevaciones-posteriores-en-polea',  'Jala hacia la cara abriendo codos. Enfoca el posterior del hombro.',   'Polea + cuerda'),
                    $ej('Curl predicador en máquina',     'Bíceps',                 'curl-predicador-en-maquina',        'Rango completo. No impulses al subir. Controla la bajada.',             'Máquina predicador', 'Curl bíceps alterno', 'curl-biceps-barra-ez'),
                    $ej('Curl concentrado con mancuerna', 'Bíceps',                 'curl-biceps-barra-ez',              'Codo apoyado en el muslo. Rota la muñeca al subir.',                    'Mancuerna'),
                    $cardio(),
                ],
            ],
            [
                'nombre'         => 'Sábado - Legs B',
                'tipo'           => 'legs',
                'grupo_muscular' => 'Femoral + Glúteo + Pantorrilla',
                'duracion'       => '85 min',
                'calentamiento'  => '5 min caminadora + 2 series 10 reps RDL vacío + activación glúteo con banda',
                'vuelta_calma'   => 'Estiramiento femoral y glúteo profundo 10 min',
                'ejercicios'     => [
                    $ej('Peso muerto rumano con barra',       'Femoral + Glúteo', 'peso-muerto-rumano-con-barra',  'Barra pegada a las piernas. Siente el estiramiento isquiotibial.',       'Barra',               'RDL con mancuernas',    'peso-muerto-rumano-con-barra'),
                    $ej('Sentadilla búlgara con mancuernas',  'Glúteo + Cuád.',   'sentadilla-bulgara-mancuerna',  'Pie trasero en banco. Baja hasta que rodilla trasera roza el piso.',     'Mancuernas + banco'),
                    $ej('Curl femoral sentado',               'Femoral',          'curl-femoral-sentado',          'Controla la bajada. No sueltes la carga. Ahí está el estímulo.',          'Máquina curl sentado'),
                    $ej('Puente de glúteo con barra',         'Glúteo',           'puente-de-gluteo-con-barra',    'Aprieta glúteos arriba. Sostén 1 seg. Cadera bien alta.',                 'Barra + colchoneta',  'Hip thrust en máquina', 'puente-de-gluteo-con-barra'),
                    $ej('Elevación de talones sentado',       'Pantorrilla',      'elevacion-de-talones-sentado',  'Rango completo. Pausa 1 seg arriba. Carga sobre la punta del pie.',       'Máquina pantorrillas sentado'),
                    $cardio('4.5-5 km/h, 8% inclinación. Día fuerte de pierna — cardio moderado, FC 110-125 bpm.'),
                ],
            ],
        ];

        // ---- TRAINING PLAN ----
        $training = [
            'titulo'           => 'PPL×2 Recomposición - Daniela',
            'objetivo'         => 'Recomposición corporal: preservar y refinar el músculo ganado en 6 años mientras se reduce la grasa corporal con déficit moderado',
            'metodologia'      => 'Push / Pull / Legs repetido ×2 con progresión de intensidad semanal',
            'split'            => 'Push / Pull / Legs',
            'frecuencia'       => '6 dias/semana',
            'duracion_semanas' => 4,
            'fecha_inicio'     => $fi,
            'fecha_fin'        => date('Y-m-d', strtotime($fi . ' + 28 days')),
            'notas_coach'      => "Daniela, diseñé este PPL×2 pensando en tu nivel avanzado. Con 6 años de entrenamiento no necesitas aprender movimientos — necesitas intensidad progresiva y volumen bien estructurado.\n\nCada semana subimos la intensidad. Semana 1 arrancas con RIR 3 (3 repeticiones en reserva) — puede sentirse fácil, y está bien. El objetivo es calibrar pesos para las semanas que vienen. Anota TODO en el tracker: pesos, sensaciones, si quedaste con más en el tanque.\n\nEl cardio LISS al final de cada sesión (30 min caminadora inclinada) es parte del plan de recomposición. No lo saltes. 5-6 km/h, 10-12% inclinación, frecuencia cardíaca entre 120-140 bpm. No tiene que doler — tiene que durar.\n\nDomingo es descanso activo: camina 30 min tranquila si quieres, o descansa completo. No es día de gym.",
            'tips'             => [
                'Registra los pesos en el tracker — sin datos no hay progresión inteligente',
                'Sube carga solo cuando el último set se sienta a 4+ RIR (muy fácil)',
                'Duerme 7-8 horas: la recuperación es donde realmente creces',
                'Hidrátate mínimo 2.5L/día — más en días de piernas',
                'Si sientes dolor articular (no muscular), avísame antes de continuar',
            ],
            'semanas' => [
                $makeWeek($dias, 1, 'Adaptación · RIR 3',  3, '12', 3),
                $makeWeek($dias, 2, 'Hipertrofia · RIR 2', 4, '10', 2),
                $makeWeek($dias, 3, 'Fuerza · RIR 1',      4, '8',  1),
                $makeWeek($dias, 4, 'Peak · RIR 0',        5, '6',  0),
            ],
        ];

        // ---- NUTRITION PLAN ----
        $nutrition = [
            'titulo'           => 'Recomposición Bloque 1 - Daniela',
            'objetivo'         => 'Recomposición corporal: mantener el músculo ganado y reducir grasa con déficit moderado de 200 kcal. Solo pechuga de pollo como fuente de proteína animal.',
            'objetivo_cal'     => 2200,
            'duracion_semanas' => 4,
            'fecha_inicio'     => $fi,
            'peso_objetivo'    => 58.0,
            'macros'           => [
                'proteina_g'      => 145,
                'carbohidratos_g' => 281,
                'grasas_g'        => 55,
            ],
            'hidratacion' => [
                'agua_minima_litros' => 2.5,
                'electrolitos'       => 'Agrega una pizca de sal marina y limón al agua durante el entrenamiento',
            ],
            'notas_coach' => "Daniela, diseñé este plan pensando en tu nivel avanzado y tu objetivo de recomposición. Con 60 kg y 6 años de entrenamiento, ya tienes la base muscular — ahora es momento de refinarla.\n\nLas calorías están en 2200 kcal, que para tu TDEE es un déficit moderado de unos 200 kcal. En recomposición no necesitamos cortar agresivo: necesitamos proteína alta y consistencia total. Con 145g de proteína al día protegemos el músculo mientras reducimos la grasa.\n\nUsa solo pechuga de pollo como fuente de proteína animal, como indicaste. Cocínala a la plancha o al horno sin aceite — eso ya está calculado aparte. Pesa los alimentos la primera semana para calibrar las porciones reales, después ya lo haces a ojo.\n\nRegistra todo en el tracker. Si hay días que comes menos por lo que sea, no trates de compensar al otro día — sigue el plan normal. Consistencia es la clave, no perfección.",
            'tips' => [
                'Pesa los alimentos crudos la primera semana para calibrar las porciones',
                'Bebe 200 ml de agua antes de cada comida para controlar el apetito',
                'La pechuga se puede preparar el domingo para toda la semana (refrigerada 4 días)',
                'Si tienes hambre en la noche: 200g de yogur natural sin azúcar',
                'No saltes el post-entreno — es el momento más importante para la proteína',
            ],
            'comidas' => [
                [
                    'nombre'   => 'Desayuno',
                    'tipo'     => 'desayuno',
                    'hora'     => '7:00 AM',
                    'calorias' => 450,
                    'macros'   => ['proteina_g' => 35, 'carbohidratos_g' => 55, 'grasas_g' => 12],
                    'alimentos' => [
                        ['nombre' => 'Pechuga de pollo a la plancha', 'cantidad' => '120g'],
                        ['nombre' => 'Avena cocida en agua',          'cantidad' => '70g (cruda)'],
                        ['nombre' => 'Clara de huevo',                'cantidad' => '3 unidades'],
                        ['nombre' => 'Banano pequeño',                'cantidad' => '1 unidad (80g)'],
                        ['nombre' => 'Aceite de coco para cocinar',   'cantidad' => '5g'],
                    ],
                    'notas' => 'Cocina la pechuga la noche anterior o el domingo. La avena con agua, no leche.',
                ],
                [
                    'nombre'   => 'Merienda pre-entreno',
                    'tipo'     => 'pre-entreno',
                    'hora'     => '10:00 AM',
                    'calorias' => 300,
                    'macros'   => ['proteina_g' => 25, 'carbohidratos_g' => 45, 'grasas_g' => 5],
                    'alimentos' => [
                        ['nombre' => 'Proteína whey en agua', 'cantidad' => '1 scoop (30g)'],
                        ['nombre' => 'Arroz cocido',          'cantidad' => '100g'],
                        ['nombre' => 'Manzana mediana',       'cantidad' => '1 unidad (150g)'],
                    ],
                    'notas' => 'Tómalo 60-90 min antes de entrenar. No entrenes en ayunas con el volumen que manejas.',
                ],
                [
                    'nombre'   => 'Post-entreno / Almuerzo',
                    'tipo'     => 'post-entreno',
                    'hora'     => '1:30 PM',
                    'calorias' => 550,
                    'macros'   => ['proteina_g' => 45, 'carbohidratos_g' => 65, 'grasas_g' => 10],
                    'alimentos' => [
                        ['nombre' => 'Pechuga de pollo a la plancha',      'cantidad' => '180g'],
                        ['nombre' => 'Arroz cocido',                       'cantidad' => '120g'],
                        ['nombre' => 'Ensalada (lechuga, tomate, pepino)', 'cantidad' => '1 plato grande'],
                        ['nombre' => 'Aguacate',                           'cantidad' => '40g (aprox. ¼)'],
                    ],
                    'notas' => 'Come dentro de los 45 min post-entreno. El aguacate va sobre la ensalada, sin aderezo extra.',
                ],
                [
                    'nombre'   => 'Merienda tarde',
                    'tipo'     => 'merienda',
                    'hora'     => '5:00 PM',
                    'calorias' => 350,
                    'macros'   => ['proteina_g' => 20, 'carbohidratos_g' => 50, 'grasas_g' => 10],
                    'alimentos' => [
                        ['nombre' => 'Yogur griego natural sin azúcar', 'cantidad' => '200g'],
                        ['nombre' => 'Almendras',                       'cantidad' => '20g'],
                        ['nombre' => 'Fresas',                          'cantidad' => '100g'],
                        ['nombre' => 'Avena cruda',                     'cantidad' => '30g'],
                    ],
                    'notas' => 'Si no encuentras yogur griego, usa yogur natural sin azúcar + medio scoop de whey.',
                ],
                [
                    'nombre'   => 'Cena',
                    'tipo'     => 'cena',
                    'hora'     => '8:00 PM',
                    'calorias' => 550,
                    'macros'   => ['proteina_g' => 20, 'carbohidratos_g' => 66, 'grasas_g' => 18],
                    'alimentos' => [
                        ['nombre' => 'Pechuga de pollo a la plancha', 'cantidad' => '150g'],
                        ['nombre' => 'Papa cocida sin piel',          'cantidad' => '200g'],
                        ['nombre' => 'Brócoli al vapor',              'cantidad' => '200g'],
                        ['nombre' => 'Aceite de oliva',               'cantidad' => '1 cucharada (14g)'],
                    ],
                    'notas' => 'Cena 2 horas antes de dormir. El aceite va encima de los vegetales o la papa.',
                ],
            ],
            'plan_dia_descanso' => [
                'descripcion'       => 'Domingo sin entreno: bajamos 250 kcal de carbohidratos. La proteína se mantiene igual.',
                'calorias_objetivo' => 1950,
                'ajustes'           => [
                    'Elimina la merienda pre-entreno (no entrenas)',
                    'Reduce el arroz del almuerzo a 80g',
                    'Mantén desayuno, merienda tarde y cena iguales',
                ],
            ],
        ];

        // ---- SUPPLEMENTATION PLAN ----
        $supple = [
            'titulo'                => 'Suplementación Base - Daniela',
            'descripcion_protocolo' => 'Protocolo esencial para recomposición corporal. Mínimo, efectivo, sin excesos.',
            'perfil_cliente'        => 'Mujer 26 años, 60 kg, nivel avanzado, gym 6 días/semana',
            'advertencia'           => 'Consulta con un médico si tienes alguna condición médica o tomas medicamentos.',
            'categorias' => [
                [
                    'nombre'      => 'Rendimiento',
                    'suplementos' => [
                        [
                            'nombre'    => 'Creatina monohidrato',
                            'dosis'     => '5g',
                            'timing'    => 'Con el desayuno',
                            'prioridad' => 'esencial',
                            'notas'     => 'Diario, incluso los días de descanso. Sin ciclo. Disuelve en agua o en el batido de proteína.',
                        ],
                        [
                            'nombre'    => 'Cafeína (café negro sin azúcar)',
                            'dosis'     => '1-2 tazas',
                            'timing'    => '30 min antes de entrenar',
                            'prioridad' => 'recomendado',
                            'notas'     => 'Café negro preferiblemente. No tomar después de las 3 PM para proteger el sueño.',
                        ],
                    ],
                ],
                [
                    'nombre'      => 'Recuperación',
                    'suplementos' => [
                        [
                            'nombre'    => 'Proteína whey',
                            'dosis'     => '1 scoop (30g)',
                            'timing'    => 'Post-entreno y merienda pre-entreno',
                            'prioridad' => 'esencial',
                            'notas'     => 'En agua fría. No sustituye comidas, las complementa para alcanzar los 145g de proteína.',
                        ],
                    ],
                ],
                [
                    'nombre'      => 'Salud',
                    'suplementos' => [
                        [
                            'nombre'    => 'Omega 3 (EPA+DHA)',
                            'dosis'     => '2g',
                            'timing'    => 'Con el almuerzo',
                            'prioridad' => 'recomendado',
                            'notas'     => 'Reduce inflamación y mejora la recuperación articular. Tómalo con una comida que contenga grasa.',
                        ],
                        [
                            'nombre'    => 'Vitamina D3',
                            'dosis'     => '2000 UI',
                            'timing'    => 'Con el desayuno',
                            'prioridad' => 'recomendado',
                            'notas'     => 'Fundamental para la regulación hormonal, huesos y rendimiento deportivo. Tómala con grasa.',
                        ],
                        [
                            'nombre'    => 'Magnesio glicinato',
                            'dosis'     => '300-400mg',
                            'timing'    => 'Antes de dormir',
                            'prioridad' => 'opcional',
                            'notas'     => 'Mejora la calidad del sueño y la recuperación muscular. Si tienes calambres o sueño ligero, empieza por aquí.',
                        ],
                    ],
                ],
            ],
            'timing_diario' => [
                ['momento' => 'Desayuno (7:00 AM)',          'suplementos' => 'Creatina 5g + Vitamina D3 2000 UI'],
                ['momento' => 'Pre-entreno (30 min antes)',  'suplementos' => 'Café negro 1-2 tazas'],
                ['momento' => 'Post-entreno (inmediato)',    'suplementos' => 'Whey 1 scoop en agua'],
                ['momento' => 'Almuerzo',                    'suplementos' => 'Omega 3 2g'],
                ['momento' => 'Antes de dormir (10:00 PM)', 'suplementos' => 'Magnesio 300mg (opcional)'],
            ],
            'sinergias' => [
                ['titulo' => 'Creatina + Carbos', 'explicacion' => 'Tomar creatina con algo de carbo (la avena del desayuno) mejora su absorción celular.'],
                ['titulo' => 'D3 + Grasa',        'explicacion' => 'La D3 es liposoluble — tómala siempre con una comida que contenga grasa.'],
            ],
            'notas_coach'   => "Daniela, el stack es simple porque lo simple funciona. Creatina + whey + omega 3 + D3. No gastes en pre-workouts complejos ni en BCAAs — con la proteína que consumes en la dieta ya tienes todos los aminoácidos que necesitas.\n\nEl magnesio es opcional pero lo recomiendo si notas calambres o sueño de mala calidad. Pruébalo 2 semanas y me cuentas.",
            'mensaje_final' => 'Menos suplementos, más comida real. Los resultados los construye la dieta y el entrenamiento, no los polvos.',
        ];

        // ---- INSERTAR ----
        $this->info("\n=== ASIGNANDO PLANES A DANIELA (ID: {$clientId}) ===\n");

        $deactivate('entrenamiento');
        $id1 = $insertPlan('entrenamiento', $training, 4);
        $this->info("✓ Entrenamiento insertado — ID: {$id1}");

        $deactivate('nutricion');
        $id2 = $insertPlan('nutricion', $nutrition, 4);
        $this->info("✓ Nutrición insertada — ID: {$id2}");

        $deactivate('suplementacion');
        $id3 = $insertPlan('suplementacion', $supple, 4);
        $this->info("✓ Suplementación insertada — ID: {$id3}");

        // ---- REDIS ----
        try {
            $redis = new Redis();
            $redis->connect('wellcorefitness_wellcorefitness-redis', 6379);
            $redis->auth('VrBdKSNNGWrx5YN');
            $keys = [
                "client_plan_v3_{$clientId}",
                "wp:plan:{$clientId}",
                "wp:weekdays:{$clientId}",
                "dashboard:{$clientId}",
            ];
            foreach ($keys as $k) {
                $redis->del($k);
            }
            $this->info('✓ Caché Redis limpiada (' . implode(', ', $keys) . ')');
        } catch (\Exception $e) {
            $this->warn('⚠ Redis no disponible: ' . $e->getMessage());
        }

        // ---- VERIFICACIÓN ----
        $planes = $pdo->query(
            "SELECT id, plan_type, valid_from, expires_at FROM assigned_plans WHERE client_id={$clientId} AND active=1 ORDER BY id DESC LIMIT 6"
        )->fetchAll(PDO::FETCH_ASSOC);

        $this->info("\n=== VERIFICACIÓN — PLANES ACTIVOS ===");
        foreach ($planes as $p) {
            $this->line("  [{$p['id']}] {$p['plan_type']}  {$p['valid_from']} → {$p['expires_at']}");
        }

        $this->info("\n=== RESUMEN FINAL ===");
        $this->line("Cliente  : Daniela Lucia Barboza Cárdenas (ID: {$clientId})");
        $this->line("Inicio   : {$fi}");
        $this->line("Fin      : " . date('Y-m-d', strtotime($fi . ' + 28 days')));
        $this->line("Coach    : Anderson Ardila (ID: {$coachId})");
        $this->line("Entreno  → ID: {$id1}");
        $this->line("Nutrición → ID: {$id2}");
        $this->line("Suplement → ID: {$id3}");
        $this->info("\nListo. Ve a /client/plan impersonando a la cliente para verificar.\n");

        return self::SUCCESS;
    }
}
