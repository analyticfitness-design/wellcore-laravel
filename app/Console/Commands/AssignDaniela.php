<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PDO;

class AssignDaniela extends Command
{
    protected $signature = 'wellcore:assign-daniela';
    protected $description = '[TMP] Reemplazar plan de entrenamiento de Daniela con split correcto (Cuad/Esp+Bic/Glut/Hom+Tri/Fem+Glut/FullBody)';

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

        $ej = function ($nombre, $musculo, $gif, $notas, $equipo = '', $variNombre = '', $variGif = '', $bloque = 'normal') use ($G) {
            $e = [
                'nombre'   => $nombre,
                'musculo'  => $musculo,
                'notas'    => $notas,
                'gif_url'  => $G . $gif . '.gif',
                'bloque'   => $bloque,
                'descanso' => '90s',
            ];
            if ($equipo)     $e['equipo']    = $equipo;
            if ($variNombre) $e['variacion'] = ['nombre' => $variNombre, 'gif_url' => $G . $variGif . '.gif'];
            return $e;
        };

        $cardio = function ($tipo = 'caminadora', $duracion = '15 min', $notas = '') use ($G) {
            $gif  = $tipo === 'escaladora' ? 'escaladora' : 'caminadora-inclinada';
            $nota = $notas ?: ($tipo === 'escaladora'
                ? 'Velocidad moderada. FC objetivo 130-145 bpm. Escaladora activa glúteos y posterior.'
                : '5-6 km/h, 10-12% inclinación. FC 120-140 bpm. Post-pesas, no en ayunas.');
            return [
                'nombre'       => $tipo === 'escaladora' ? 'Escaladora' : 'Caminadora inclinada',
                'is_cardio'    => true,
                'repeticiones' => $duracion,
                'descanso'     => '-',
                'notas'        => $nota,
                'gif_url'      => $G . $gif . '.gif',
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

        // ============================================================
        // DÍAS BASE — split real de Daniela
        // ============================================================
        $dias = [

            // ── LUN: CUÁDRICEPS ─────────────────────────────────────
            [
                'nombre'         => 'Lunes - Cuádriceps',
                'tipo'           => 'legs',
                'grupo_muscular' => 'Cuádriceps',
                'duracion'       => '95 min',
                'calentamiento'  => '5 min bici + sentadillas corporales 2×15 + movilidad de cadera y rodilla',
                'vuelta_calma'   => 'Estiramiento de cuádriceps y flexor de cadera 5 min',
                'ejercicios'     => [
                    $ej('Sentadilla con barra',
                        'Cuádriceps + Glúteo',
                        'sentadilla-con-barra',
                        'Baja hasta paralelo o más. Rodillas siguen la punta del pie. Pecho alto. REST-PAUSE en semanas 3-4: lleva al fallo, descansa 15 seg, 3-4 reps más.',
                        'Barra + rack',
                        'Prensa de piernas pies juntos',
                        'prensa-de-piernas-cerrado'),
                    $ej('Prensa de piernas pies juntos',
                        'Cuádriceps',
                        'prensa-de-piernas-cerrado',
                        'Pies juntos en el centro bajo de la plataforma. Enfoca el recto anterior. Rodillas NO bloquees al extender.',
                        'Prensa',
                        'Prensa pies altos (más glúteo)',
                        'prensa-de-piernas-cerrado'),
                    $ej('Extensión de cuádriceps en máquina',
                        'Cuádriceps',
                        'extension-de-cuadriceps-en-maquina',
                        'DROP SET en sem 3-4: lleva al fallo, baja el peso 30%, sigue sin descanso. Pausa 1 seg arriba apretando el cuádriceps.',
                        'Máquina extensión',
                        'Sentadilla hack en máquina',
                        'sentadilla-con-barra'),
                    $ej('Zancada frontal con mancuernas',
                        'Cuádriceps + Glúteo',
                        'zancada-frontal-con-mancuerna',
                        'Paso largo, tronco erguido. Rodilla delantera no pasa la punta del pie. Alterna piernas.',
                        'Mancuernas',
                        'Zancada reversa (más control)',
                        'zancada-frontal-con-mancuerna'),
                    $ej('Curl femoral acostado en máquina',
                        'Femoral',
                        'curl-femoral-acostado-en-maquina',
                        'Complemento de equilibrio cuad/femoral. Aprieta isquios al final. No saques la cadera.',
                        'Máquina curl femoral',
                        'Curl femoral sentado',
                        'curl-femoral-sentado'),
                    $cardio('caminadora', '15 min'),
                ],
            ],

            // ── MAR: ESPALDA + BÍCEPS + ABS ─────────────────────────
            [
                'nombre'         => 'Martes - Espalda, Bíceps y Abdomen',
                'tipo'           => 'pull',
                'grupo_muscular' => 'Espalda + Bíceps + Abdomen',
                'duracion'       => '95 min',
                'calentamiento'  => '5 min bici + rotaciones de hombro + movilidad de escápulas y muñecas',
                'vuelta_calma'   => 'Estiramiento de dorsal y bíceps 5 min',
                'ejercicios'     => [
                    $ej('Jalón al pecho en máquina',
                        'Dorsal',
                        'jalon-al-pecho-en-maquina',
                        'Lleva la barra al esternón. Pecho alto. Aprieta el dorsal al final. REST-PAUSE en sem 3-4: fallo + 15 seg descanso + 3 reps más.',
                        'Polea alta',
                        'Dominadas asistidas',
                        'dominadas'),
                    $ej('Remo con barra',
                        'Dorsal + Trapecio',
                        'remo-con-barra',
                        'Tronco a 45°. Jala al abdomen. Aprieta escápulas al final. Espalda siempre neutral, no redondees.',
                        'Barra',
                        'Remo con mancuerna a una mano',
                        'remo-sentado-en-maquina'),
                    $ej('Remo sentado en polea agarre neutro',
                        'Dorsal',
                        'remo-en-polea-sentado',
                        'Pecho alto, espalda neutral. Codos atrás, aprieta escápulas 1 seg al final.',
                        'Polea + agarre neutro',
                        'Remo en máquina',
                        'remo-sentado-en-maquina'),
                    $ej('Curl bíceps barra EZ',
                        'Bíceps',
                        'curl-biceps-barra-ez',
                        'No balancees el torso. Pausa 1 seg arriba. Controla la bajada 2-3 seg.',
                        'Barra EZ',
                        'Curl predicador en máquina',
                        'curl-predicador-en-maquina'),
                    $ej('Curl martillo con mancuerna',
                        'Bíceps + Braquiorradial',
                        'curl-martillo-con-mancuerna',
                        'DROP SET en sem 3-4: falla con el peso, baja 30% sin descanso. Pulgar arriba, controla la bajada.',
                        'Mancuernas',
                        'Curl concentrado',
                        'curl-biceps-barra-ez'),
                    // ABS
                    $ej('Elevación de piernas colgado',
                        'Abdomen (recto)',
                        'elevacion-de-piernas-colgado',
                        'Cuelga de la barra con grip supino o neutro. Sube piernas a 90° o más. No balancees el cuerpo. 15-20 reps.',
                        'Barra o paralelas',
                        'Elevación de piernas en banco inclinado',
                        'elevacion-de-piernas-colgado'),
                    $ej('Crunch en máquina',
                        'Abdomen (recto + oblicuos)',
                        'crunch-en-maquina',
                        'Contrae el abdomen, no jales con el cuello. Rango completo. 3 series de 15-20 reps.',
                        'Máquina abdominales',
                        'Crunch con peso en polea',
                        'crunch-en-maquina'),
                    $cardio('escaladora', '15 min'),
                ],
            ],

            // ── MIÉ: GLÚTEO ─────────────────────────────────────────
            [
                'nombre'         => 'Miércoles - Glúteo',
                'tipo'           => 'legs',
                'grupo_muscular' => 'Glúteo',
                'duracion'       => '95 min',
                'calentamiento'  => '5 min caminadora + activación glúteo con banda (30 clams + 20 monster walks) + 2×10 hip thrust con barra vacía',
                'vuelta_calma'   => 'Estiramiento glúteo profundo y piriforme 8 min',
                'ejercicios'     => [
                    $ej('Puente de glúteo con barra (Hip Thrust)',
                        'Glúteo',
                        'puente-de-gluteo-con-barra',
                        'Hombros sobre el banco. Aprieta glúteos arriba, sostén 1 seg. REST-PAUSE en sem 3-4: fallo + 15 seg + 3 reps. Cadera bien alta, zona lumbar neutral.',
                        'Barra + colchoneta + banco',
                        'Hip thrust en máquina',
                        'puente-de-gluteo-con-barra'),
                    $ej('Sentadilla búlgara con mancuernas',
                        'Glúteo + Cuádriceps',
                        'sentadilla-bulgara-mancuerna',
                        'Pie trasero en banco. Baja hasta que rodilla trasera roza el piso. Tronco ligeramente inclinado hacia adelante para mayor activación de glúteo.',
                        'Mancuernas + banco',
                        'Sentadilla búlgara con barra',
                        'sentadilla-con-barra'),
                    $ej('Abducción de cadera en máquina',
                        'Glúteo mediano',
                        'abduccion-de-cadera-en-maquina',
                        'DROP SET en sem 3-4: falla, baja el peso 30%, sigue. Espalda recta contra el respaldo. Aprieta en el tope.',
                        'Máquina abductora',
                        'Abducción con cable',
                        'elevacion-lateral-con-mancuerna'),
                    $ej('Patada de glúteo en cable (Kickback)',
                        'Glúteo',
                        'patada-gluteo-cable',
                        'Manilla en tobillo, coloca la mano en la polea para equilibrio. Lleva la pierna atrás y arriba. Aprieta 1 seg arriba.',
                        'Polea baja + manilla tobillo',
                        'Extensión cadera en máquina',
                        'puente-de-gluteo-con-barra'),
                    $ej('Peso muerto rumano con barra',
                        'Femoral + Glúteo',
                        'peso-muerto-rumano-con-barra',
                        'Barra pegada a las piernas. Empuja cadera atrás. Siente el estiramiento isquiotibial. Espalda siempre neutral.',
                        'Barra',
                        'RDL con mancuernas',
                        'peso-muerto-rumano-con-barra'),
                    $cardio('caminadora', '15 min', '5 km/h, 10% inclinación. Día de glúteo — activa y refuerza sin sobrecargar.'),
                ],
            ],

            // ── JUE: HOMBROS + TRÍCEPS + ABS ────────────────────────
            [
                'nombre'         => 'Jueves - Hombros, Tríceps y Abdomen',
                'tipo'           => 'push',
                'grupo_muscular' => 'Hombros + Tríceps + Abdomen',
                'duracion'       => '95 min',
                'calentamiento'  => '5 min bici + elevaciones frontales con banda 2×15 + rotaciones de hombro con banda',
                'vuelta_calma'   => 'Estiramiento de hombros y tríceps 5 min',
                'ejercicios'     => [
                    $ej('Press de hombros con mancuernas',
                        'Hombro frontal + medial',
                        'press-de-hombro-con-mancuerna',
                        'No arquees la espalda. Lleva hasta la línea del oído. REST-PAUSE en sem 3-4: fallo + 15 seg descanso + 3 reps.',
                        'Mancuernas',
                        'Press militar con barra',
                        'press-de-hombro-con-mancuerna'),
                    $ej('Elevaciones laterales en cable',
                        'Hombro medial',
                        'elevacion-lateral-con-mancuerna',
                        'El cable da tensión constante en todo el rango. DROP SET en sem 3-4. Codos ligeramente flexionados, sube a la altura del hombro.',
                        'Polea baja',
                        'Elevaciones laterales con mancuerna',
                        'elevacion-lateral-con-mancuerna'),
                    $ej('Face pull en polea',
                        'Hombro posterior + Trapecio',
                        'elevaciones-posteriores-en-polea',
                        'Jala hacia la cara abriendo codos hacia afuera. Enfoca el deltoides posterior. Imprescindible para salud del manguito rotador.',
                        'Polea + cuerda',
                        'Elevaciones posteriores con mancuernas',
                        'elevaciones-posteriores-en-polea'),
                    $ej('Extensión tríceps en polea con cuerda',
                        'Tríceps',
                        'extension-de-triceps-en-polea-con-cuerda',
                        'Abre la cuerda al final. Codos fijos al costado. DROP SET en sem 3-4: falla, baja peso 30%, continúa.',
                        'Polea + cuerda',
                        'Extensión tríceps sobre la cabeza en polea',
                        'extension-de-triceps-en-polea-con-cuerda'),
                    $ej('Fondos de tríceps en paralelas',
                        'Tríceps + Pecho inferior + Hombro anterior',
                        'fondos-de-triceps',
                        'Cuerpo erguido para enfocar tríceps. Baja hasta 90° de codo. REST-PAUSE en sem 3-4 si se usan con peso corporal.',
                        'Paralelas',
                        'Extensión tríceps con mancuerna sobre la cabeza',
                        'extension-de-triceps-en-polea-con-cuerda'),
                    // ABS
                    $ej('Crunch oblicuo en máquina',
                        'Abdomen (oblicuos)',
                        'crunch-en-maquina',
                        'Lleva el codo hacia la rodilla opuesta. Contrae el oblicuo. 15 reps por lado. Sem 3-4: añade peso.',
                        'Máquina abdominales o polea',
                        'Russian twist con disco',
                        'crunch-en-maquina'),
                    $ej('Plancha con soporte',
                        'Abdomen (estabilizadores + core)',
                        'plancha',
                        '30-45 seg sem 1-2, 45-60 seg sem 3-4. Core activo, glúteos apretados, no dejes caer las caderas. Variante: plancha lateral.',
                        'Colchoneta',
                        'Dead bug',
                        'plancha'),
                    $cardio('escaladora', '15 min'),
                ],
            ],

            // ── VIE: FEMORALES + GLÚTEOS ─────────────────────────────
            [
                'nombre'         => 'Viernes - Femorales y Glúteos',
                'tipo'           => 'legs',
                'grupo_muscular' => 'Femorales + Glúteos',
                'duracion'       => '95 min',
                'calentamiento'  => '5 min caminadora lenta + 2 series 10 reps RDL vacío + activación glúteo con banda',
                'vuelta_calma'   => 'Estiramiento femoral y glúteo profundo 10 min',
                'ejercicios'     => [
                    $ej('Peso muerto rumano con barra',
                        'Femoral + Glúteo',
                        'peso-muerto-rumano-con-barra',
                        'Barra pegada a las piernas. Empuja cadera atrás. Siente el estiramiento. REST-PAUSE en sem 3-4. Espalda siempre neutral.',
                        'Barra',
                        'RDL con mancuernas',
                        'peso-muerto-rumano-con-barra'),
                    $ej('Curl femoral acostado en máquina',
                        'Femoral',
                        'curl-femoral-acostado-en-maquina',
                        'DROP SET en sem 3-4. Aprieta isquios al final. No saques la cadera. Controla la bajada 3 seg.',
                        'Máquina curl femoral',
                        'Curl femoral sentado',
                        'curl-femoral-sentado'),
                    $ej('Curl femoral sentado en máquina',
                        'Femoral',
                        'curl-femoral-sentado',
                        'Posición sentada activa el femoral de forma diferente. Doble acción femoral el mismo día = máximo estímulo. Controla la bajada.',
                        'Máquina curl sentado',
                        'Curl nórdico asistido',
                        'curl-femoral-acostado-en-maquina'),
                    $ej('Sentadilla sumo con mancuerna',
                        'Glúteo + Aductor + Femoral',
                        'sentadilla-bulgara-mancuerna',
                        'Pies abiertos más allá del ancho de hombros, puntas hacia afuera. Baja la mancuerna entre las piernas. Activa glúteo y aductor.',
                        'Mancuerna grande',
                        'Sentadilla sumo con barra',
                        'sentadilla-con-barra'),
                    $ej('Puente de glúteo con barra (Hip Thrust)',
                        'Glúteo',
                        'puente-de-gluteo-con-barra',
                        'Segundo día de glúteo en la semana. Carga acumulada. Aprieta glúteos 1-2 seg arriba. Zona lumbar neutral, no hiperextiendas.',
                        'Barra + colchoneta + banco',
                        'Hip thrust una pierna',
                        'puente-de-gluteo-con-barra'),
                    $cardio('caminadora', '15 min', '5-6 km/h, 10-12% inclinación. Post piernas — activo, no exhaustivo. FC 120-135 bpm.'),
                ],
            ],

            // ── SÁB: FULL BODY (AB + PUSH + PULL) ───────────────────
            [
                'nombre'         => 'Sábado - Full Body (Abdomen + Push + Pull)',
                'tipo'           => 'full',
                'grupo_muscular' => 'Abdomen + Empuje + Jalón',
                'duracion'       => '100 min',
                'calentamiento'  => '5 min bici + movilidad de hombros y cadera + activación general',
                'vuelta_calma'   => 'Estiramiento general de cuerpo completo 8 min',
                'ejercicios'     => [
                    // ABS (2)
                    $ej('Elevación de piernas colgado',
                        'Abdomen (recto)',
                        'elevacion-de-piernas-colgado',
                        'Sube las piernas a 90° o más. No balancees. 3 series × 15-20 reps. Agarre neutro o supino.',
                        'Barra',
                        'Elevación de rodillas en paralelas',
                        'elevacion-de-piernas-colgado'),
                    $ej('Crunch en máquina',
                        'Abdomen',
                        'crunch-en-maquina',
                        'Rango completo. Pausa en la contracción. 3 series × 15-20 reps. Agrega peso sem 3-4.',
                        'Máquina abdominales',
                        'Crunch con peso en polea',
                        'crunch-en-maquina'),
                    // PUSH (2)
                    $ej('Press de banca inclinado con mancuernas',
                        'Pecho superior + Hombro',
                        'press-banca-inclinado-con-barra',
                        'Baja hasta sentir estiramiento en pecho superior. Empuja explosivo. No juntes las mancuernas arriba.',
                        'Mancuernas + banco inclinado',
                        'Press de pecho con barra plano',
                        'press-banca-barra'),
                    $ej('Press de hombros con mancuernas',
                        'Hombro + Tríceps',
                        'press-de-hombro-con-mancuerna',
                        'Segundo press del día. Carga moderada, buen rango. No arquees la espalda.',
                        'Mancuernas',
                        'Press Arnold',
                        'press-de-hombro-con-mancuerna'),
                    // PULL (2)
                    $ej('Jalón al pecho en máquina',
                        'Dorsal',
                        'jalon-al-pecho-en-maquina',
                        'Lleva la barra al esternón. Aprieta el dorsal. Full ROM.',
                        'Polea alta',
                        'Dominadas asistidas',
                        'dominadas'),
                    $ej('Remo con mancuerna a una mano',
                        'Dorsal + Romboides',
                        'remo-sentado-en-maquina',
                        'Jala hasta la cadera, no al hombro. Columna paralela al piso. Aprieta el dorsal al final.',
                        'Mancuerna + banco',
                        'Remo sentado en polea',
                        'remo-en-polea-sentado'),
                    $cardio('escaladora', '20 min', 'Intensidad moderada. FC 130-145 bpm. Cierra bien la semana. 20 min de escaladora = quema extra significativa.'),
                ],
            ],
        ];

        // ============================================================
        // TRAINING PLAN
        // ============================================================
        $training = [
            'titulo'           => 'Fuerza & Definición 6D - Daniela',
            'objetivo'         => 'Recomposición corporal: preservar músculo con énfasis en glúteo y femoral mientras se reduce grasa. Split de 6 días por grupos musculares con progresión semanal.',
            'metodologia'      => 'Split por grupos musculares · Progresión lineal semanal · Drop sets y Rest-pause en semanas 3-4',
            'split'            => 'Cuad / Esp+Bic / Glut / Hom+Tri / Fem+Glut / Full',
            'frecuencia'       => '6 días/semana',
            'duracion_semanas' => 4,
            'fecha_inicio'     => $fi,
            'fecha_fin'        => date('Y-m-d', strtotime($fi . ' + 28 days')),
            'notas_coach'      => "Daniela, rediseñé tu plan con el split que usas: cuádriceps, espalda+bíceps, glúteo, hombros+tríceps, femorales+glúteos, y un full body el sábado. Esto me contaste que es como trabajas mejor.\n\nCada semana subimos la intensidad. La semana 1 arranca con RIR 3 — puede sentirse controlado. Está bien. El objetivo es calibrar pesos. Semana 3 y 4 introduzco DROP SETS en algunos ejercicios de aislamiento y REST-PAUSE en los compuestos principales. Están marcados en las notas de cada ejercicio.\n\nEl cardio está al final de cada sesión porque preserva el glucógeno para los pesos. Escaladora para tren superior, caminadora inclinada para los días de pierna. No te saltes el cardio — es parte de la recomposición.\n\nDomingo: descanso activo. Camina 30 min si quieres, o descansa completo.",
            'tips'             => [
                'Registra los pesos en el tracker — sin datos no hay progresión inteligente',
                'Drop set: lleva al fallo, baja el peso 30% SIN descanso y sigue hasta fallar de nuevo',
                'Rest-pause: lleva al fallo, descansa 15 seg respirando, 3-4 reps más con el mismo peso',
                'Sube carga solo cuando el último set se sienta a 4+ RIR (muy fácil)',
                'Hidrátate mínimo 2.5L/día — más en días de piernas y glúteo',
            ],
            'semanas' => [
                $makeWeek($dias, 1, 'Adaptación · RIR 3',  3, '12', 3),
                $makeWeek($dias, 2, 'Hipertrofia · RIR 2', 4, '10', 2),
                $makeWeek($dias, 3, 'Fuerza + Drop Sets · RIR 1', 4, '8', 1),
                $makeWeek($dias, 4, 'Peak + Rest-Pause · RIR 0',  5, '6', 0),
            ],
        ];

        // ============================================================
        // PLAN DE NUTRICIÓN — 3 OPCIONES POR COMIDA
        // ============================================================
        $nutrition = [
            'titulo'           => 'Recomposición Bloque 1 - Daniela',
            'objetivo'         => 'Recomposición corporal con déficit moderado de 200 kcal. 3 opciones por comida para variedad y adherencia. Solo pechuga de pollo como proteína animal.',
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
            'notas_coach' => "Daniela, rediseñé el plan de nutrición con 3 opciones por comida para que no te aburras comiendo lo mismo todos los días.\n\nElige la opción que más te guste o que tengas disponible. Todas tienen aproximadamente las mismas calorías y macros, así que no importa cuál elijas — solo sé consistente con la que elijas cada día.\n\nLa proteína viene casi toda de pechuga de pollo + whey. Con 145g de proteína al día protegemos el músculo mientras reducimos grasa. Las 2200 kcal son un déficit de ~200 kcal sobre tu TDEE — lo suficiente para ver cambios sin sacrificar rendimiento ni músculo.\n\nPesa los alimentos la primera semana para calibrar las porciones. Después ya lo haces a ojo.",
            'tips' => [
                'Elige la opción que más te guste — todas tienen los mismos macros',
                'Pesa los alimentos crudos la primera semana para calibrar las porciones',
                'La pechuga se puede preparar el domingo para toda la semana (refrigerada 4 días)',
                'No saltes el post-entreno — es el momento más importante para la proteína',
                'Si tienes hambre extra en la noche: 200g de yogur natural sin azúcar',
            ],
            'comidas' => [
                // ── DESAYUNO ────────────────────────────────────────
                [
                    'nombre'   => 'Desayuno',
                    'tipo'     => 'desayuno',
                    'hora'     => '7:00 AM',
                    'calorias' => 450,
                    'macros'   => ['proteina_g' => 35, 'carbohidratos_g' => 55, 'grasas_g' => 12],
                    'notas'    => 'Elige una opción. Todas son intercambiables.',
                    'opcion_a' => [
                        ['nombre' => 'Pechuga de pollo a la plancha', 'cantidad' => '120g'],
                        ['nombre' => 'Avena cocida en agua',          'cantidad' => '70g (cruda)'],
                        ['nombre' => 'Clara de huevo',                'cantidad' => '3 unidades'],
                        ['nombre' => 'Banano pequeño',                'cantidad' => '80g (1 unidad)'],
                        ['nombre' => 'Aceite de coco para cocinar',   'cantidad' => '5g'],
                    ],
                    'opcion_b' => [
                        ['nombre' => 'Huevo entero',                  'cantidad' => '2 unidades'],
                        ['nombre' => 'Clara de huevo',                'cantidad' => '3 unidades'],
                        ['nombre' => 'Arepa de maíz peto pequeña',    'cantidad' => '80g'],
                        ['nombre' => 'Aguacate',                      'cantidad' => '40g (¼ unidad)'],
                        ['nombre' => 'Manzana o fruta de tu gusto',   'cantidad' => '150g'],
                    ],
                    'opcion_c' => [
                        ['nombre' => 'Yogur griego natural sin azúcar', 'cantidad' => '200g'],
                        ['nombre' => 'Avena cruda',                     'cantidad' => '50g'],
                        ['nombre' => 'Proteína whey en agua',           'cantidad' => '½ scoop (15g)'],
                        ['nombre' => 'Nueces o almendras',              'cantidad' => '20g'],
                        ['nombre' => 'Fresas o arándanos',              'cantidad' => '100g'],
                    ],
                ],
                // ── PRE-ENTRENO ──────────────────────────────────────
                [
                    'nombre'   => 'Merienda pre-entreno',
                    'tipo'     => 'pre-entreno',
                    'hora'     => '10:00 AM',
                    'calorias' => 300,
                    'macros'   => ['proteina_g' => 25, 'carbohidratos_g' => 45, 'grasas_g' => 5],
                    'notas'    => 'Tómalo 60-90 min antes de entrenar. No entrenes en ayunas con el volumen que manejas.',
                    'opcion_a' => [
                        ['nombre' => 'Proteína whey en agua', 'cantidad' => '1 scoop (30g)'],
                        ['nombre' => 'Arroz cocido',          'cantidad' => '100g'],
                        ['nombre' => 'Manzana mediana',       'cantidad' => '150g'],
                    ],
                    'opcion_b' => [
                        ['nombre' => 'Pechuga de pollo a la plancha', 'cantidad' => '100g'],
                        ['nombre' => 'Papa cocida pequeña',           'cantidad' => '120g'],
                        ['nombre' => 'Fruta de temporada',            'cantidad' => '100g'],
                    ],
                    'opcion_c' => [
                        ['nombre' => 'Yogur natural sin azúcar', 'cantidad' => '150g'],
                        ['nombre' => 'Avena cruda',              'cantidad' => '40g'],
                        ['nombre' => 'Banano maduro',            'cantidad' => '100g'],
                        ['nombre' => 'Maní natural sin sal',     'cantidad' => '15g'],
                    ],
                ],
                // ── POST-ENTRENO ─────────────────────────────────────
                [
                    'nombre'   => 'Post-entreno / Almuerzo',
                    'tipo'     => 'post-entreno',
                    'hora'     => '1:30 PM',
                    'calorias' => 550,
                    'macros'   => ['proteina_g' => 45, 'carbohidratos_g' => 65, 'grasas_g' => 10],
                    'notas'    => 'Come dentro de los 45 min post-entreno. La proteína alta aquí protege el músculo.',
                    'opcion_a' => [
                        ['nombre' => 'Pechuga de pollo a la plancha',      'cantidad' => '180g'],
                        ['nombre' => 'Arroz cocido',                       'cantidad' => '120g'],
                        ['nombre' => 'Ensalada (lechuga, tomate, pepino)', 'cantidad' => '1 plato grande'],
                        ['nombre' => 'Aguacate',                           'cantidad' => '40g (¼ unidad)'],
                    ],
                    'opcion_b' => [
                        ['nombre' => 'Pechuga de pollo a la plancha', 'cantidad' => '180g'],
                        ['nombre' => 'Papa mediana cocida',           'cantidad' => '200g'],
                        ['nombre' => 'Brócoli al vapor',              'cantidad' => '150g'],
                        ['nombre' => 'Aceite de oliva',               'cantidad' => '1 cdta (7g)'],
                    ],
                    'opcion_c' => [
                        ['nombre' => 'Pechuga de pollo a la plancha',        'cantidad' => '180g'],
                        ['nombre' => 'Quinoa cocida',                         'cantidad' => '100g (cruda 60g)'],
                        ['nombre' => 'Vegetales salteados (pimentón, zanahoria, espinaca)', 'cantidad' => '200g'],
                        ['nombre' => 'Aceite de oliva o coco',               'cantidad' => '1 cdta (7g)'],
                    ],
                ],
                // ── MERIENDA TARDE ───────────────────────────────────
                [
                    'nombre'   => 'Merienda tarde',
                    'tipo'     => 'merienda',
                    'hora'     => '5:00 PM',
                    'calorias' => 350,
                    'macros'   => ['proteina_g' => 20, 'carbohidratos_g' => 50, 'grasas_g' => 10],
                    'notas'    => 'Snack de media tarde. Elige lo que más te apetezca del día.',
                    'opcion_a' => [
                        ['nombre' => 'Yogur griego natural sin azúcar', 'cantidad' => '200g'],
                        ['nombre' => 'Almendras',                       'cantidad' => '20g'],
                        ['nombre' => 'Fresas frescas',                  'cantidad' => '100g'],
                        ['nombre' => 'Avena cruda',                     'cantidad' => '30g'],
                    ],
                    'opcion_b' => [
                        ['nombre' => 'Pechuga de pollo',   'cantidad' => '100g'],
                        ['nombre' => 'Arroz cocido',       'cantidad' => '80g'],
                        ['nombre' => 'Tomate cherry',      'cantidad' => '100g'],
                        ['nombre' => 'Aguacate',           'cantidad' => '30g'],
                    ],
                    'opcion_c' => [
                        ['nombre' => 'Proteína whey en agua',        'cantidad' => '1 scoop (30g)'],
                        ['nombre' => 'Banano maduro mediano',        'cantidad' => '100g'],
                        ['nombre' => 'Mantequilla de maní natural',  'cantidad' => '15g'],
                        ['nombre' => 'Avena cruda',                  'cantidad' => '30g'],
                    ],
                ],
                // ── CENA ────────────────────────────────────────────
                [
                    'nombre'   => 'Cena',
                    'tipo'     => 'cena',
                    'hora'     => '8:00 PM',
                    'calorias' => 550,
                    'macros'   => ['proteina_g' => 20, 'carbohidratos_g' => 66, 'grasas_g' => 18],
                    'notas'    => 'Cena 2 horas antes de dormir. Elige tu opción favorita del día.',
                    'opcion_a' => [
                        ['nombre' => 'Pechuga de pollo a la plancha', 'cantidad' => '150g'],
                        ['nombre' => 'Papa cocida sin piel',          'cantidad' => '200g'],
                        ['nombre' => 'Brócoli al vapor',              'cantidad' => '200g'],
                        ['nombre' => 'Aceite de oliva',               'cantidad' => '1 cucharada (14g)'],
                    ],
                    'opcion_b' => [
                        ['nombre' => 'Pechuga de pollo a la plancha',      'cantidad' => '150g'],
                        ['nombre' => 'Arroz cocido',                       'cantidad' => '120g'],
                        ['nombre' => 'Ensalada verde (lechuga, pepino, tomate)', 'cantidad' => '1 plato'],
                        ['nombre' => 'Aguacate',                           'cantidad' => '50g'],
                    ],
                    'opcion_c' => [
                        ['nombre' => 'Pechuga de pollo a la plancha', 'cantidad' => '150g'],
                        ['nombre' => 'Yuca cocida o batata',          'cantidad' => '200g'],
                        ['nombre' => 'Espinaca salteada',             'cantidad' => '150g'],
                        ['nombre' => 'Aceite de oliva',               'cantidad' => '1 cucharada (14g)'],
                    ],
                ],
            ],
            'plan_dia_descanso' => [
                'descripcion'       => 'Domingo sin entreno: bajamos 250 kcal de carbohidratos. Proteína se mantiene igual.',
                'calorias_objetivo' => 1950,
                'ajustes'           => [
                    'Elimina la merienda pre-entreno (no entrenas)',
                    'Reduce el arroz/papa del almuerzo a la mitad',
                    'Mantén desayuno, merienda tarde y cena iguales',
                ],
            ],
        ];

        // ============================================================
        // INSERTAR TRAINING + NUTRITION
        // ============================================================
        $insert = function ($type, $json) use ($pdo, $clientId, $coachId, $fi) {
            $exp  = date('Y-m-d', strtotime($fi . ' + 28 days'));
            $pdo->prepare("UPDATE assigned_plans SET active=0 WHERE client_id=? AND plan_type=? AND active=1")
                ->execute([$clientId, $type]);
            $this->line("  ↳ Planes anteriores '$type' desactivados");
            $pdo->prepare("INSERT INTO assigned_plans (client_id,plan_type,content,version,assigned_by,valid_from,expires_at,active,created_at) VALUES (?,?,?,1,?,?,?,1,NOW())")
                ->execute([
                    $clientId,
                    $type,
                    json_encode($json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    $coachId,
                    $fi,
                    $exp,
                ]);
            return $pdo->lastInsertId();
        };

        $this->info("\n=== ACTUALIZANDO PLANES DE DANIELA (ID: {$clientId}) ===\n");

        $idE = $insert('entrenamiento', $training);
        $this->info("✓ Entrenamiento insertado — ID: {$idE}");

        $idN = $insert('nutricion', $nutrition);
        $this->info("✓ Nutrición (3 opciones) insertada — ID: {$idN}");

        $planes = $pdo->query(
            "SELECT id, plan_type, valid_from, expires_at FROM assigned_plans WHERE client_id={$clientId} AND active=1 ORDER BY id DESC LIMIT 6"
        )->fetchAll(PDO::FETCH_ASSOC);

        $this->info("\n=== VERIFICACIÓN — PLANES ACTIVOS ===");
        foreach ($planes as $p) {
            $this->line("  [{$p['id']}] {$p['plan_type']}  {$p['valid_from']} → {$p['expires_at']}");
        }

        $this->info("\n=== ESTRUCTURA ENTRENAMIENTO ===");
        $this->line("  Split: " . $training['split']);
        $this->line("  Días: 6 — Cuad | Esp+Bic | Glut | Hom+Tri | Fem+Glut | Full");

        $this->info("\n=== ESTRUCTURA NUTRICIÓN ===");
        $this->line("  Comidas: " . count($nutrition['comidas']));
        $this->line("  Opciones por comida: 3 (A / B / C)");
        $this->line("  Calorías: " . $nutrition['objetivo_cal'] . " kcal");

        $this->info("\nListo. Limpia caché manualmente: php artisan cache:forget client_plan_v3_96\n");

        return self::SUCCESS;
    }
}
