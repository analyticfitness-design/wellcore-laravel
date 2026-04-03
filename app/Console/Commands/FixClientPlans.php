<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixClientPlans extends Command
{
    protected $signature = 'wellcore:fix-client-plans';

    protected $description = 'Insert/update nutrition, training, and habits plans for 6 RISE clients';

    public function handle(): int
    {
        DB::transaction(function () {
            $this->fixLina();
            $this->fixVanessa();
            $this->fixAdriana();
            $this->fixNelson();
            $this->fixLeidy();
            $this->fixDanna();
        });

        $this->info('All client plans applied successfully.');

        return self::SUCCESS;
    }

    // -------------------------------------------------------------------------
    // LINA (ID: 28) — nutricion + entrenamiento (habitos ya existe)
    // -------------------------------------------------------------------------

    private function fixLina(): void
    {
        $clientId = 28;

        $nutricion = [
            'objetivo' => 'Déficit calórico moderado para reducción de grasa preservando músculo y tonificación de glúteos y piernas',
            'macros' => ['proteina_g' => 120, 'carbohidratos_g' => 140, 'grasas_g' => 50],
            'calorias_diarias' => 1600,
            'comidas' => [
                [
                    'nombre' => 'Desayuno',
                    'calorias' => 380,
                    'alimentos' => ['Avena 50g con agua o leche descremada', '2 huevos revueltos', '1 fruta mediana (banano o manzana)'],
                    'notas' => 'Consumir 30 min después de despertar',
                    'macros' => ['proteina_g' => 25, 'carbohidratos_g' => 42, 'grasas_g' => 9],
                ],
                [
                    'nombre' => 'Almuerzo',
                    'calorias' => 480,
                    'alimentos' => ['Pollo a la plancha 140g', 'Arroz integral 70g', 'Ensalada verde grande', 'Aguacate 25g'],
                    'notas' => 'Principal comida del día',
                    'macros' => ['proteina_g' => 38, 'carbohidratos_g' => 50, 'grasas_g' => 13],
                ],
                [
                    'nombre' => 'Merienda',
                    'calorias' => 230,
                    'alimentos' => ['Yogur griego 150g', 'Nueces 15g'],
                    'notas' => 'Snack post-entrenamiento o media tarde',
                    'macros' => ['proteina_g' => 18, 'carbohidratos_g' => 14, 'grasas_g' => 10],
                ],
                [
                    'nombre' => 'Cena',
                    'calorias' => 370,
                    'alimentos' => ['Atún en agua 120g', 'Brócoli al vapor 200g', 'Papa pequeña 90g'],
                    'notas' => 'Cena ligera 2h antes de dormir',
                    'macros' => ['proteina_g' => 30, 'carbohidratos_g' => 27, 'grasas_g' => 10],
                ],
                [
                    'nombre' => 'Snack nocturno',
                    'calorias' => 140,
                    'alimentos' => ['Caseína o yogur griego 100g', '5 almendras'],
                    'notas' => 'Opcional si hay hambre nocturna',
                    'macros' => ['proteina_g' => 12, 'carbohidratos_g' => 10, 'grasas_g' => 5],
                ],
            ],
            'notas_coach' => 'Enfócate en consumir proteína en cada comida. Hidratación mínima 2L de agua al día. Entrenamiento en casa con bandas y mancuernas 2kg.',
            'tips' => [
                'Pesa los alimentos crudos para mayor precisión',
                'Prepara tus comidas la noche anterior',
                'Prioriza proteína y verduras en cada plato',
                'Come cada 3-4 horas para mantener el metabolismo activo',
            ],
        ];

        $semanas = $this->buildLinaTraining();

        $entrenamiento = [
            'nombre' => 'Plan Glúteos y Piernas en Casa — Lina',
            'objetivo' => 'Tonificación y desarrollo de glúteos y piernas con bandas y mancuernas 2kg',
            'semanas' => $semanas,
        ];

        $this->upsertPlan($clientId, 'nutricion', $nutricion);
        $this->upsertPlan($clientId, 'entrenamiento', $entrenamiento);

        $this->info("Lina (ID:{$clientId}) — nutricion + entrenamiento OK");
    }

    private function buildLinaTraining(): array
    {
        $progressionNotes = [
            1 => 'Semana 1: Aprende la técnica. Descansa 60 seg entre series.',
            2 => 'Semana 2: Añade una repetición extra por serie respecto a la semana anterior.',
            3 => 'Semana 3: Aumenta el tiempo bajo tensión (baja más lento en 3 seg). Descansa 45 seg.',
            4 => 'Semana 4: Máxima intensidad — ejecuta cada serie hasta casi el fallo técnico.',
        ];

        $dias = [
            [
                'dia' => 'Lunes',
                'nombre_sesion' => 'Glúteos y Piernas A',
                'ejercicios' => [
                    ['nombre' => 'Sentadilla con banda en rodillas', 'series' => 4, 'repeticiones' => '15-20', 'descanso' => '60 seg', 'notas' => 'Banda a la altura de rodillas. Empuja rodillas hacia afuera al bajar. Activa glúteos al subir.'],
                    ['nombre' => 'Hip thrust en el suelo con banda', 'series' => 4, 'repeticiones' => '15-20', 'descanso' => '60 seg', 'notas' => 'Apoya espalda alta en sofá o silla. Banda sobre cadera. Sube y aprieta glúteos arriba.'],
                    ['nombre' => 'Patada de glúteo en cuadrupedia con banda', 'series' => 3, 'repeticiones' => '15 por pierna', 'descanso' => '45 seg', 'notas' => 'Banda en el tobillo. Mantén la espalda neutra. Extiende la pierna hacia atrás apretando el glúteo.'],
                    ['nombre' => 'Sentadilla sumo con mancuerna 2kg', 'series' => 3, 'repeticiones' => '15', 'descanso' => '60 seg', 'notas' => 'Pies más anchos que los hombros, puntas hacia afuera. Sostén las mancuernas frente al cuerpo.'],
                    ['nombre' => 'Abducción lateral acostada con banda', 'series' => 3, 'repeticiones' => '20 por pierna', 'descanso' => '45 seg', 'notas' => 'Acostada de lado. Banda sobre rodillas. Eleva la pierna de arriba manteniendo el pie en flexión.'],
                    ['nombre' => 'Puente de glúteo con banda isométrico (pulsos)', 'series' => 3, 'repeticiones' => '20 pulsos', 'descanso' => '45 seg', 'notas' => 'En la posición alta del hip thrust, realiza pequeños pulsos apretando glúteos.'],
                ],
            ],
            [
                'dia' => 'Miércoles',
                'nombre_sesion' => 'Piernas Completas + Core',
                'ejercicios' => [
                    ['nombre' => 'Sentadilla con mancuernas 2kg', 'series' => 4, 'repeticiones' => '15', 'descanso' => '60 seg', 'notas' => 'Una mancuerna en cada mano. Pies a la anchura de hombros. Baja hasta paralelo manteniendo pecho arriba.'],
                    ['nombre' => 'Zancada estática alternada con banda', 'series' => 3, 'repeticiones' => '12 por pierna', 'descanso' => '60 seg', 'notas' => 'Da un paso al frente. Baja la rodilla trasera sin tocar el suelo. Banda añade resistencia en rodillas.'],
                    ['nombre' => 'Peso muerto con mancuernas 2kg', 'series' => 3, 'repeticiones' => '15', 'descanso' => '60 seg', 'notas' => 'Mancuernas al frente de los muslos. Inclínate manteniendo espalda recta. Siente el estiramiento en isquiotibiales.'],
                    ['nombre' => 'Elevación de pantorrilla de pie', 'series' => 3, 'repeticiones' => '25', 'descanso' => '30 seg', 'notas' => 'Puede ser sobre un escalón para mayor rango. Sube lento y baja controlado.'],
                    ['nombre' => 'Plancha frontal', 'series' => 3, 'repeticiones' => '30-45 seg', 'descanso' => '30 seg', 'notas' => 'Cuerpo recto de cabeza a talones. No dejes caer las caderas.'],
                    ['nombre' => 'Crunch abdominal', 'series' => 3, 'repeticiones' => '20', 'descanso' => '30 seg', 'notas' => 'Manos detrás de la nuca. Despega solo los hombros del suelo. Exhala al subir.'],
                ],
            ],
            [
                'dia' => 'Viernes',
                'nombre_sesion' => 'Glúteos y Piernas B — Alta Activación',
                'ejercicios' => [
                    ['nombre' => 'Activación de glúteo en cuadrupedia (fire hydrant) con banda', 'series' => 3, 'repeticiones' => '15 por pierna', 'descanso' => '30 seg', 'notas' => 'Banda en rodillas. Abre la pierna hacia el lado manteniendo la rodilla a 90°. Aprieta glúteo arriba.'],
                    ['nombre' => 'Hip thrust con mancuernas 2kg sobre cadera', 'series' => 4, 'repeticiones' => '15', 'descanso' => '60 seg', 'notas' => 'Agrega las mancuernas sobre las caderas para más carga. Aprieta glúteos en la posición alta.'],
                    ['nombre' => 'Sentadilla búlgara con banda', 'series' => 3, 'repeticiones' => '12 por pierna', 'descanso' => '60 seg', 'notas' => 'Pie trasero apoyado en silla o sofá. Baja controlado. Activa el glúteo de la pierna delantera.'],
                    ['nombre' => 'Patada trasera con mancuerna en tobillo', 'series' => 3, 'repeticiones' => '15 por pierna', 'descanso' => '45 seg', 'notas' => 'En cuadrupedia. Mancuerna en el tobillo. Extiende la pierna hacia atrás y arriba, apretando el glúteo.'],
                    ['nombre' => 'Peso muerto con una pierna (sin peso)', 'series' => 3, 'repeticiones' => '10 por pierna', 'descanso' => '45 seg', 'notas' => 'Equilibrio y coordinación. Inclínate hacia delante levantando la pierna trasera. Mantén cadera nivelada.'],
                    ['nombre' => 'Sentadilla isométrica en pared (wall sit)', 'series' => 3, 'repeticiones' => '40 seg', 'descanso' => '30 seg', 'notas' => 'Espalda apoyada en la pared. Caderas y rodillas a 90°. Aguanta la posición.'],
                ],
            ],
        ];

        $semanas = [];
        for ($s = 1; $s <= 4; $s++) {
            $diasSemana = [];
            foreach ($dias as $dia) {
                $ejerciciosConProgresion = array_map(function ($ej) use ($s, $progressionNotes) {
                    $ej['notas'] .= ' | '.$progressionNotes[$s];

                    return $ej;
                }, $dia['ejercicios']);

                $diasSemana[] = [
                    'dia' => $dia['dia'],
                    'nombre_sesion' => $dia['nombre_sesion'],
                    'ejercicios' => $ejerciciosConProgresion,
                ];
            }
            $semanas[] = ['semana' => $s, 'dias' => $diasSemana];
        }

        return $semanas;
    }

    // -------------------------------------------------------------------------
    // VANESSA DIAZ (ID: 58) — nutricion + entrenamiento + habitos
    // -------------------------------------------------------------------------

    private function fixVanessa(): void
    {
        $clientId = 58;

        $nutricion = [
            'objetivo' => 'Recomposición corporal: reducir grasa y aumentar masa muscular magra',
            'macros' => ['proteina_g' => 130, 'carbohidratos_g' => 155, 'grasas_g' => 56],
            'calorias_diarias' => 1700,
            'comidas' => [
                [
                    'nombre' => 'Desayuno',
                    'calorias' => 410,
                    'alimentos' => ['Avena 60g con agua', '3 claras de huevo + 1 huevo entero revueltos', '1 fruta mediana'],
                    'notas' => 'Consumir dentro de la primera hora del día',
                    'macros' => ['proteina_g' => 30, 'carbohidratos_g' => 44, 'grasas_g' => 10],
                ],
                [
                    'nombre' => 'Almuerzo',
                    'calorias' => 510,
                    'alimentos' => ['Pechuga de pollo a la plancha 150g', 'Arroz integral 80g', 'Ensalada verde con tomate', 'Aceite de oliva 1 cdta'],
                    'notas' => 'Principal comida del día. No omitir los carbohidratos.',
                    'macros' => ['proteina_g' => 40, 'carbohidratos_g' => 56, 'grasas_g' => 14],
                ],
                [
                    'nombre' => 'Pre-entreno',
                    'calorias' => 200,
                    'alimentos' => ['Pan integral 2 tajadas', 'Jamón de pavo 2 lonjas'],
                    'notas' => '45-60 min antes del entrenamiento',
                    'macros' => ['proteina_g' => 15, 'carbohidratos_g' => 28, 'grasas_g' => 4],
                ],
                [
                    'nombre' => 'Post-entreno / Merienda',
                    'calorias' => 230,
                    'alimentos' => ['Yogur griego 170g', 'Nueces 15g'],
                    'notas' => 'Dentro de los 30 min post-entrenamiento',
                    'macros' => ['proteina_g' => 20, 'carbohidratos_g' => 14, 'grasas_g' => 11],
                ],
                [
                    'nombre' => 'Cena',
                    'calorias' => 350,
                    'alimentos' => ['Salmón al horno 120g', 'Verduras salteadas 200g', 'Camote 80g'],
                    'notas' => 'Cena balanceada 2h antes de dormir',
                    'macros' => ['proteina_g' => 28, 'carbohidratos_g' => 28, 'grasas_g' => 14],
                ],
            ],
            'notas_coach' => 'Consistencia ante todo. Hidratación mínima 2.5L al día. Ajusta porciones los días de descanso reduciendo carbohidratos en un 20%.',
            'tips' => [
                'Pesa los alimentos crudos para mayor precisión',
                'Prepara batch cooking los domingos para la semana',
                'En días de gym aumenta los carbohidratos del pre y post entreno',
                'Prioriza el sueño — es cuando ocurre la recuperación muscular',
            ],
        ];

        $entrenamiento = [
            'nombre' => 'Plan Gym 4 Días — Vanessa Diaz',
            'objetivo' => 'Hipertrofia y recomposición corporal con pesas en gym',
            'semanas' => $this->buildVanessaTraining(),
        ];

        $habitos = $this->standardRiseHabitsWoman();

        $this->upsertPlan($clientId, 'nutricion', $nutricion);
        $this->upsertPlan($clientId, 'entrenamiento', $entrenamiento);
        $this->upsertPlan($clientId, 'habitos', $habitos);

        $this->info("Vanessa Diaz (ID:{$clientId}) — nutricion + entrenamiento + habitos OK");
    }

    private function buildVanessaTraining(): array
    {
        $dias = [
            [
                'dia' => 'Lunes',
                'nombre_sesion' => 'Glúteos y Piernas A',
                'ejercicios' => [
                    ['nombre' => 'Sentadilla con barra', 'series' => 4, 'repeticiones' => '10-12', 'descanso' => '90 seg', 'notas' => 'Barra sobre trapecios. Baja hasta paralelo. Rodillas alineadas con los pies.'],
                    ['nombre' => 'Hip thrust con barra', 'series' => 4, 'repeticiones' => '12-15', 'descanso' => '90 seg', 'notas' => 'Banco a la altura del omóplato. Aprieta glúteos arriba y mantén 1 seg.'],
                    ['nombre' => 'Prensa de piernas', 'series' => 3, 'repeticiones' => '12-15', 'descanso' => '90 seg', 'notas' => 'Pies a la anchura de hombros. No bloquees las rodillas arriba.'],
                    ['nombre' => 'Peso muerto rumano con barra', 'series' => 3, 'repeticiones' => '12', 'descanso' => '90 seg', 'notas' => 'Mantén la barra cerca del cuerpo. Siente el estiramiento en isquiotibiales.'],
                    ['nombre' => 'Abducción en máquina', 'series' => 3, 'repeticiones' => '15-20', 'descanso' => '60 seg', 'notas' => 'Aprieta glúteos en la posición abierta. Controla el retorno.'],
                ],
            ],
            [
                'dia' => 'Martes',
                'nombre_sesion' => 'Hombros y Brazos',
                'ejercicios' => [
                    ['nombre' => 'Press de hombro con mancuernas sentada', 'series' => 4, 'repeticiones' => '10-12', 'descanso' => '90 seg', 'notas' => 'Espalda recta. Empuja arriba sin arquear la zona lumbar.'],
                    ['nombre' => 'Elevaciones laterales con mancuernas', 'series' => 4, 'repeticiones' => '12-15', 'descanso' => '60 seg', 'notas' => 'Codos ligeramente flexionados. Eleva hasta la altura de los hombros.'],
                    ['nombre' => 'Curl de bíceps con mancuernas', 'series' => 3, 'repeticiones' => '12', 'descanso' => '60 seg', 'notas' => 'Codos pegados al cuerpo. Supina la muñeca al subir.'],
                    ['nombre' => 'Extensión de tríceps en polea alta', 'series' => 3, 'repeticiones' => '12-15', 'descanso' => '60 seg', 'notas' => 'Codos fijos a los lados. Extiende completamente y contrae el tríceps.'],
                    ['nombre' => 'Elevaciones frontales con mancuernas', 'series' => 3, 'repeticiones' => '12', 'descanso' => '60 seg', 'notas' => 'Eleva al frente hasta la altura de los hombros. Control en el descenso.'],
                ],
            ],
            [
                'dia' => 'Jueves',
                'nombre_sesion' => 'Espalda y Bíceps',
                'ejercicios' => [
                    ['nombre' => 'Jalón al pecho con barra', 'series' => 4, 'repeticiones' => '10-12', 'descanso' => '90 seg', 'notas' => 'Agarre prono ligeramente más ancho que los hombros. Lleva la barra al esternón.'],
                    ['nombre' => 'Remo con barra', 'series' => 4, 'repeticiones' => '10-12', 'descanso' => '90 seg', 'notas' => 'Espalda recta a 45°. Lleva la barra al ombligo. Aprieta escápulas al final.'],
                    ['nombre' => 'Remo en polea baja sentada', 'series' => 3, 'repeticiones' => '12', 'descanso' => '90 seg', 'notas' => 'Tira hacia el abdomen. Mantén el pecho erguido y escápulas juntas.'],
                    ['nombre' => 'Curl de bíceps con barra', 'series' => 3, 'repeticiones' => '12', 'descanso' => '60 seg', 'notas' => 'Codos fijos. Extiende completamente abajo antes de curlar.'],
                    ['nombre' => 'Curl martillo con mancuernas', 'series' => 3, 'repeticiones' => '12', 'descanso' => '60 seg', 'notas' => 'Agarre neutro (pulgares arriba). Trabaja braquiorradial y bíceps.'],
                ],
            ],
            [
                'dia' => 'Viernes',
                'nombre_sesion' => 'Glúteos y Piernas B',
                'ejercicios' => [
                    ['nombre' => 'Sentadilla búlgara con mancuernas', 'series' => 4, 'repeticiones' => '10 por pierna', 'descanso' => '90 seg', 'notas' => 'Pie trasero en banco. Baja controlado. Activa el glúteo de la pierna delantera.'],
                    ['nombre' => 'Peso muerto con mancuernas', 'series' => 4, 'repeticiones' => '12', 'descanso' => '90 seg', 'notas' => 'Mantén espalda neutra. Baja las mancuernas por delante de los muslos.'],
                    ['nombre' => 'Extensión de cuádriceps en máquina', 'series' => 3, 'repeticiones' => '15', 'descanso' => '60 seg', 'notas' => 'Contrae el cuádriceps en la posición alta. Baja controlado.'],
                    ['nombre' => 'Curl femoral acostada', 'series' => 3, 'repeticiones' => '12-15', 'descanso' => '60 seg', 'notas' => 'Caderas pegadas al banco. Sube completamente y baja controlado.'],
                    ['nombre' => 'Hip thrust en máquina', 'series' => 3, 'repeticiones' => '15', 'descanso' => '60 seg', 'notas' => 'Aprieta glúteos al máximo arriba. Mantén 1 segundo.'],
                ],
            ],
        ];

        return $this->buildFourWeeks($dias);
    }

    // -------------------------------------------------------------------------
    // ADRIANA SARMIENTO (ID: 59) — nutricion + entrenamiento + habitos
    // -------------------------------------------------------------------------

    private function fixAdriana(): void
    {
        $clientId = 59;

        $nutricion = [
            'objetivo' => 'Déficit calórico moderado para reducción de grasa con preservación de masa muscular',
            'macros' => ['proteina_g' => 125, 'carbohidratos_g' => 148, 'grasas_g' => 54],
            'calorias_diarias' => 1650,
            'comidas' => [
                [
                    'nombre' => 'Desayuno',
                    'calorias' => 400,
                    'alimentos' => ['Avena 55g con agua o leche descremada', '2 huevos revueltos con espinaca', '1 fruta mediana'],
                    'notas' => 'Consumir dentro de los primeros 45 min de levantarse',
                    'macros' => ['proteina_g' => 27, 'carbohidratos_g' => 43, 'grasas_g' => 9],
                ],
                [
                    'nombre' => 'Almuerzo',
                    'calorias' => 490,
                    'alimentos' => ['Pollo a la plancha 140g', 'Arroz integral 75g', 'Ensalada verde + pepino + tomate', 'Aceite de oliva 1 cdta'],
                    'notas' => 'Principal comida del día',
                    'macros' => ['proteina_g' => 38, 'carbohidratos_g' => 52, 'grasas_g' => 13],
                ],
                [
                    'nombre' => 'Pre-entreno',
                    'calorias' => 190,
                    'alimentos' => ['Arepa pequeña de maíz', 'Jamón de pavo 2 lonjas'],
                    'notas' => '60 min antes del entrenamiento',
                    'macros' => ['proteina_g' => 12, 'carbohidratos_g' => 27, 'grasas_g' => 4],
                ],
                [
                    'nombre' => 'Merienda post-entreno',
                    'calorias' => 220,
                    'alimentos' => ['Yogur griego 150g', 'Almendras 15g'],
                    'notas' => 'Dentro de 30 min post-entrenamiento',
                    'macros' => ['proteina_g' => 18, 'carbohidratos_g' => 13, 'grasas_g' => 10],
                ],
                [
                    'nombre' => 'Cena',
                    'calorias' => 350,
                    'alimentos' => ['Salmón o tilapia 120g', 'Verduras al vapor 200g', 'Camote 80g'],
                    'notas' => 'Cena ligera 2h antes de dormir',
                    'macros' => ['proteina_g' => 28, 'carbohidratos_g' => 26, 'grasas_g' => 12],
                ],
            ],
            'notas_coach' => 'Prioriza la proteína en cada comida. Hidratación mínima 2.5L de agua al día. Realizar 20 min de cardio al finalizar cada sesión de pesas.',
            'tips' => [
                'Pesa los alimentos crudos para mayor precisión',
                'Batch cooking los domingos facilita la semana',
                'Lleva tu comida al gym para no improvisar',
                'El cardio post-entreno maximiza la quema de grasa',
            ],
        ];

        $entrenamiento = [
            'nombre' => 'Plan Gym 5 Días — Adriana Sarmiento',
            'objetivo' => 'Reducción de grasa y tonificación con pesas en gym. Todos los ejercicios en español.',
            'semanas' => $this->buildAdrianaTraining(),
        ];

        $habitos = $this->standardRiseHabitsWoman();

        $this->upsertPlan($clientId, 'nutricion', $nutricion);
        $this->upsertPlan($clientId, 'entrenamiento', $entrenamiento);
        $this->upsertPlan($clientId, 'habitos', $habitos);

        $this->info("Adriana Sarmiento (ID:{$clientId}) — nutricion + entrenamiento + habitos OK");
    }

    private function buildAdrianaTraining(): array
    {
        $cardioNote = 'Cardio al finalizar: 20 minutos en caminadora inclinada o elíptica.';

        $dias = [
            [
                'dia' => 'Día 1',
                'nombre_sesion' => 'Piernas y Glúteos',
                'ejercicios' => [
                    ['nombre' => 'Sentadilla en máquina Smith', 'series' => 4, 'repeticiones' => '4 series de 10-12 repeticiones', 'descanso' => '90 segundos', 'notas' => 'Pies ligeramente adelantados en la Smith. Baja hasta paralelo manteniendo espalda recta.'],
                    ['nombre' => 'Zancadas con mancuernas', 'series' => 3, 'repeticiones' => '3 series de 12 repeticiones por pierna', 'descanso' => '90 segundos', 'notas' => 'Da un paso al frente. Baja la rodilla trasera sin tocar el suelo. Empuja con el talón delantero.'],
                    ['nombre' => 'Prensa de piernas', 'series' => 4, 'repeticiones' => '4 series de 12-15 repeticiones', 'descanso' => '90 segundos', 'notas' => 'Pies a la anchura de caderas. No bloquees las rodillas en la posición alta.'],
                    ['nombre' => 'Curl femoral en máquina acostada', 'series' => 3, 'repeticiones' => '3 series de 12-15 repeticiones', 'descanso' => '60 segundos', 'notas' => 'Caderas pegadas al banco. Sube hasta los 90° y baja controlado.'],
                    ['nombre' => 'Hip thrust en máquina', 'series' => 4, 'repeticiones' => '4 series de 15 repeticiones', 'descanso' => '90 segundos', 'notas' => 'Aprieta glúteos en la posición alta y mantén 1 segundo. Cadera completamente extendida.'],
                    ['nombre' => 'Cardio final', 'series' => 1, 'repeticiones' => '20 minutos continuos', 'descanso' => '-', 'notas' => $cardioNote],
                ],
            ],
            [
                'dia' => 'Día 2',
                'nombre_sesion' => 'Empuje — Hombros y Tríceps',
                'ejercicios' => [
                    ['nombre' => 'Press de hombro con mancuernas sentada', 'series' => 4, 'repeticiones' => '4 series de 10-12 repeticiones', 'descanso' => '90 segundos', 'notas' => 'Espalda apoyada en banco vertical. Empuja arriba sin arquear la zona lumbar.'],
                    ['nombre' => 'Press en banco inclinado con mancuernas', 'series' => 4, 'repeticiones' => '4 series de 10-12 repeticiones', 'descanso' => '90 segundos', 'notas' => 'Banco a 30-45°. Lleva las mancuernas al pecho y empuja hacia arriba y adentro.'],
                    ['nombre' => 'Elevaciones laterales en máquina', 'series' => 4, 'repeticiones' => '4 series de 12-15 repeticiones', 'descanso' => '60 segundos', 'notas' => 'Ajusta la máquina a la altura de tus hombros. Contrae el deltoides lateral en la posición alta.'],
                    ['nombre' => 'Fondos en banco (tríceps)', 'series' => 3, 'repeticiones' => '3 series de 12-15 repeticiones', 'descanso' => '60 segundos', 'notas' => 'Manos en el borde del banco. Baja hasta que los codos formen 90°. Empuja con los tríceps.'],
                    ['nombre' => 'Extensión de tríceps en polea alta', 'series' => 3, 'repeticiones' => '3 series de 15 repeticiones', 'descanso' => '60 segundos', 'notas' => 'Codos fijos a los lados. Extiende completamente y contrae el tríceps abajo.'],
                    ['nombre' => 'Cardio final', 'series' => 1, 'repeticiones' => '20 minutos continuos', 'descanso' => '-', 'notas' => $cardioNote],
                ],
            ],
            [
                'dia' => 'Día 3',
                'nombre_sesion' => 'Posterior — Isquiotibiales y Espalda',
                'ejercicios' => [
                    ['nombre' => 'Peso muerto rumano con mancuernas', 'series' => 4, 'repeticiones' => '4 series de 12 repeticiones', 'descanso' => '90 segundos', 'notas' => 'Mantén espalda neutra. Baja las mancuernas por delante de los muslos sintiendo el estiramiento en isquiotibiales.'],
                    ['nombre' => 'Patada diagonal en polea (cable kickback)', 'series' => 3, 'repeticiones' => '3 series de 15 repeticiones por pierna', 'descanso' => '60 segundos', 'notas' => 'Cable en el tobillo. Lleva la pierna hacia atrás y en diagonal. Aprieta el glúteo al final.'],
                    ['nombre' => 'Abducción en máquina', 'series' => 3, 'repeticiones' => '3 series de 20 repeticiones', 'descanso' => '60 segundos', 'notas' => 'Aprieta los glúteos al abrir. Controla el retorno sin rebotar.'],
                    ['nombre' => 'Remo con mancuernas a una mano', 'series' => 4, 'repeticiones' => '4 series de 12 repeticiones por brazo', 'descanso' => '90 segundos', 'notas' => 'Apoya la rodilla y la mano contraria en el banco. Lleva la mancuerna al costado del abdomen. Aprieta la escápula.'],
                    ['nombre' => 'Cardio final', 'series' => 1, 'repeticiones' => '20 minutos continuos', 'descanso' => '-', 'notas' => $cardioNote],
                ],
            ],
            [
                'dia' => 'Día 4',
                'nombre_sesion' => 'Descanso Activo',
                'ejercicios' => [
                    ['nombre' => 'Cardio en caminadora', 'series' => 1, 'repeticiones' => '20 minutos a ritmo moderado', 'descanso' => '-', 'notas' => 'Intensidad baja-moderada. Inclinación al gusto. Mantén el ritmo cardíaco entre 120-140 ppm.'],
                    ['nombre' => 'Estiramiento de cuádriceps de pie', 'series' => 2, 'repeticiones' => '30 segundos por pierna', 'descanso' => '-', 'notas' => 'Toma el pie detrás. Mantén las rodillas juntas. Empuja la cadera hacia adelante.'],
                    ['nombre' => 'Estiramiento de isquiotibiales sentada', 'series' => 2, 'repeticiones' => '30 segundos por pierna', 'descanso' => '-', 'notas' => 'Pierna extendida. Inclínate hacia adelante desde la cadera, no desde la espalda.'],
                    ['nombre' => 'Estiramiento de glúteo en paloma', 'series' => 2, 'repeticiones' => '30 segundos por lado', 'descanso' => '-', 'notas' => 'Pierna delantera en ángulo de 90°. Baja el torso hacia el suelo suavemente.'],
                    ['nombre' => 'Estiramiento de hombros cruzados', 'series' => 2, 'repeticiones' => '20 segundos por brazo', 'descanso' => '-', 'notas' => 'Lleva el brazo al pecho. Presiona con el otro brazo suavemente.'],
                ],
            ],
            [
                'dia' => 'Día 5',
                'nombre_sesion' => 'Glúteos — Enfoque Total',
                'ejercicios' => [
                    ['nombre' => 'Peso muerto con barra', 'series' => 4, 'repeticiones' => '4 series de 10 repeticiones', 'descanso' => '90 segundos', 'notas' => 'Posición de los pies al ancho de caderas. Barra cerca de las espinillas. Empuja el suelo al subir.'],
                    ['nombre' => 'Cable pull through', 'series' => 4, 'repeticiones' => '4 series de 15 repeticiones', 'descanso' => '90 segundos', 'notas' => 'Cable entre las piernas. Bisagra de cadera. Contrae glúteos al extender la cadera.'],
                    ['nombre' => 'Hip thrust en máquina', 'series' => 4, 'repeticiones' => '4 series de 12-15 repeticiones', 'descanso' => '90 segundos', 'notas' => 'Máxima contracción del glúteo arriba. Mantén 2 segundos en la posición alta.'],
                    ['nombre' => 'Curl femoral sentado en máquina', 'series' => 3, 'repeticiones' => '3 series de 15 repeticiones', 'descanso' => '60 segundos', 'notas' => 'Ajusta el rodillo sobre los tobillos. Baja completo y sube contrayendo isquiotibiales.'],
                    ['nombre' => 'Cardio final', 'series' => 1, 'repeticiones' => '20 minutos continuos', 'descanso' => '-', 'notas' => $cardioNote],
                ],
            ],
        ];

        $progressionNotes = [
            1 => 'Semana 1: Establece la técnica. Elige un peso con el que puedas completar todas las repeticiones con buena forma.',
            2 => 'Semana 2: Aumenta el peso 2-5% respecto a la semana anterior si completaste todas las repeticiones correctamente.',
            3 => 'Semana 3: Añade 1-2 repeticiones por serie o incrementa el peso ligeramente. Reduce el descanso en 10 seg en ejercicios accesorios.',
            4 => 'Semana 4: Semana de máximo esfuerzo. Busca superar los pesos de las semanas anteriores manteniendo la técnica perfecta.',
        ];

        $semanas = [];
        for ($s = 1; $s <= 4; $s++) {
            $diasSemana = [];
            foreach ($dias as $dia) {
                $ejerciciosConProgresion = array_map(function ($ej) use ($s, $progressionNotes) {
                    if ($ej['nombre'] !== 'Cardio final' && strpos($ej['nombre'], 'Estiramiento') === false && $ej['nombre'] !== 'Cardio en caminadora') {
                        $ej['notas'] .= ' | '.$progressionNotes[$s];
                    }

                    return $ej;
                }, $dia['ejercicios']);

                $diasSemana[] = [
                    'dia' => $dia['dia'],
                    'nombre_sesion' => $dia['nombre_sesion'],
                    'ejercicios' => $ejerciciosConProgresion,
                ];
            }
            $semanas[] = ['semana' => $s, 'dias' => $diasSemana];
        }

        return $semanas;
    }

    // -------------------------------------------------------------------------
    // NELSON IVÁN ROA (ID: 63) — solo entrenamiento
    // -------------------------------------------------------------------------

    private function fixNelson(): void
    {
        $clientId = 63;

        $entrenamiento = [
            'nombre' => 'Plan Pesas Gym 4 Días — Nelson Iván Roa',
            'objetivo' => 'Ganar masa muscular y fuerza con pesas en gym. Push/Pull/Legs split. Sin ejercicios funcionales.',
            'semanas' => $this->buildNelsonTraining(),
        ];

        $this->upsertPlan($clientId, 'entrenamiento', $entrenamiento);

        $this->info("Nelson Iván Roa (ID:{$clientId}) — entrenamiento OK");
    }

    private function buildNelsonTraining(): array
    {
        $dias = [
            [
                'dia' => 'Lunes',
                'nombre_sesion' => 'Pecho y Tríceps',
                'ejercicios' => [
                    ['nombre' => 'Press de banca con barra', 'series' => 4, 'repeticiones' => '8-10', 'descanso' => '120 seg', 'notas' => 'Ejercicio principal de pecho. Agarre ligeramente más ancho que los hombros. Baja la barra hasta rozar el pecho. Empuja de forma explosiva. Trabaja: pectoral mayor, tríceps, deltoides anterior.'],
                    ['nombre' => 'Aperturas con mancuernas en banco plano', 'series' => 3, 'repeticiones' => '12', 'descanso' => '90 seg', 'notas' => 'Codos ligeramente flexionados durante todo el movimiento. Baja hasta que los codos queden a la altura del pecho. Sube trazando un arco. Trabaja: pectoral mayor (porción esternal), serrato anterior.'],
                    ['nombre' => 'Press inclinado con mancuernas', 'series' => 4, 'repeticiones' => '10-12', 'descanso' => '90 seg', 'notas' => 'Banco a 30-45°. Activa la porción clavicular del pecho. Lleva las mancuernas al pecho y empuja hacia arriba y adentro. Trabaja: pectoral mayor (porción clavicular), deltoides anterior.'],
                    ['nombre' => 'Fondos en barra paralela', 'series' => 3, 'repeticiones' => '10-12', 'descanso' => '90 seg', 'notas' => 'Para enfatizar el tríceps mantén el torso más erguido. Baja hasta que los codos estén a 90°. No bajes más para proteger el hombro. Trabaja: tríceps braquial (todos los vientres), pectoral inferior.'],
                    ['nombre' => 'Extensión de tríceps en polea alta con cuerda', 'series' => 3, 'repeticiones' => '12-15', 'descanso' => '60 seg', 'notas' => 'Codos pegados a los costados durante todo el recorrido. Al final del movimiento, abre la cuerda hacia afuera para máxima contracción. Trabaja: tríceps braquial (cabeza larga, porción lateral y medial).'],
                ],
            ],
            [
                'dia' => 'Martes',
                'nombre_sesion' => 'Espalda y Bíceps',
                'ejercicios' => [
                    ['nombre' => 'Jalón al pecho con barra en polea alta', 'series' => 4, 'repeticiones' => '10-12', 'descanso' => '90 seg', 'notas' => 'Agarre prono, ligeramente más ancho que los hombros. Inclínate 10-15° hacia atrás. Lleva la barra hasta el esternón apretando las escápulas. Trabaja: dorsal ancho, redondo mayor, bíceps braquial.'],
                    ['nombre' => 'Remo con barra en T o barra', 'series' => 4, 'repeticiones' => '10-12', 'descanso' => '90 seg', 'notas' => 'Espalda recta a 45°, rodillas ligeramente flexionadas. Lleva la barra al ombligo. Aprieta las escápulas en la posición final. Trabaja: dorsal ancho, trapecio medio, romboides, bíceps.'],
                    ['nombre' => 'Remo en polea baja sentado', 'series' => 3, 'repeticiones' => '12', 'descanso' => '90 seg', 'notas' => 'Tira hacia el abdomen con los codos pegados al cuerpo. Mantén el pecho erguido y no te eches hacia atrás. Aprieta escápulas al final. Trabaja: dorsal ancho, trapecio, romboides, bíceps.'],
                    ['nombre' => 'Curl con barra recta', 'series' => 3, 'repeticiones' => '10-12', 'descanso' => '60 seg', 'notas' => 'Codos fijos a los costados del cuerpo. Extiende completamente el codo abajo antes de curlar. Supina la muñeca al subir. No balancees el torso. Trabaja: bíceps braquial (cabeza larga y corta), braquial.'],
                    ['nombre' => 'Curl martillo con mancuernas', 'series' => 3, 'repeticiones' => '12', 'descanso' => '60 seg', 'notas' => 'Agarre neutro (pulgares apuntando arriba) en todo el recorrido. Alterna brazos o realiza simultáneo. Trabaja: braquiorradial, bíceps braquial, braquial anterior.'],
                ],
            ],
            [
                'dia' => 'Jueves',
                'nombre_sesion' => 'Piernas',
                'ejercicios' => [
                    ['nombre' => 'Sentadilla con barra', 'series' => 4, 'repeticiones' => '8-10', 'descanso' => '120 seg', 'notas' => 'Barra sobre los trapecios. Pies al ancho de hombros con puntas ligeramente hacia afuera. Baja hasta que los muslos queden paralelos al suelo. Rodillas alineadas con los pies. Trabaja: cuádriceps, glúteos, isquiotibiales, erectores espinales.'],
                    ['nombre' => 'Prensa de piernas', 'series' => 4, 'repeticiones' => '12-15', 'descanso' => '90 seg', 'notas' => 'Pies a la anchura de caderas en la plataforma. Desciende hasta que las rodillas formen 90°. No bloquees las rodillas en la posición alta. Trabaja: cuádriceps, glúteos, isquiotibiales.'],
                    ['nombre' => 'Extensión de cuádriceps en máquina', 'series' => 3, 'repeticiones' => '15', 'descanso' => '60 seg', 'notas' => 'Ajusta el rodillo sobre los tobillos. Extiende completamente y contrae el cuádriceps en la posición alta. Baja controlado. Trabaja: cuádriceps (vasto medial, lateral, intermedio y recto femoral).'],
                    ['nombre' => 'Curl femoral acostado en máquina', 'series' => 3, 'repeticiones' => '12-15', 'descanso' => '60 seg', 'notas' => 'Caderas pegadas al banco. Sube hasta los 90° y baja controlado sin rebotar. Trabaja: isquiotibiales (bíceps femoral, semitendinoso, semimembranoso).'],
                    ['nombre' => 'Gemelos en máquina de pie (calf raises)', 'series' => 4, 'repeticiones' => '15-20', 'descanso' => '60 seg', 'notas' => 'Rodillas ligeramente flexionadas para trabajar el sóleo o completamente extendidas para el gastrocnemio. Baja completamente para estirar y sube lento. Trabaja: gastrocnemio, sóleo.'],
                ],
            ],
            [
                'dia' => 'Viernes',
                'nombre_sesion' => 'Hombros y Trapecios',
                'ejercicios' => [
                    ['nombre' => 'Press militar con barra de pie', 'series' => 4, 'repeticiones' => '8-10', 'descanso' => '120 seg', 'notas' => 'Barra a la altura del pecho. Empuja verticalmente sin arquear la zona lumbar. Activa el core durante todo el movimiento. Trabaja: deltoides anterior y medial, tríceps, trapecio.'],
                    ['nombre' => 'Elevaciones laterales con mancuernas', 'series' => 4, 'repeticiones' => '12-15', 'descanso' => '60 seg', 'notas' => 'Codos ligeramente flexionados. Eleva hasta la altura de los hombros sin subir más. El meñique ligeramente más alto que el pulgar ("vierte la jarra"). Trabaja: deltoides medial (cabeza lateral).'],
                    ['nombre' => 'Elevaciones frontales con mancuernas', 'series' => 3, 'repeticiones' => '12', 'descanso' => '60 seg', 'notas' => 'Alterna brazos o simultáneo. Eleva al frente hasta la horizontal. Control en el descenso. Trabaja: deltoides anterior, pectoral mayor (porción clavicular).'],
                    ['nombre' => 'Remo al cuello con barra', 'series' => 3, 'repeticiones' => '12', 'descanso' => '60 seg', 'notas' => 'Agarre prono estrecho. Lleva la barra hacia el mentón con los codos bien arriba. Controla el descenso. Trabaja: deltoides medial, trapecio, bíceps.'],
                    ['nombre' => 'Encogimientos con barra (shrugs)', 'series' => 4, 'repeticiones' => '15', 'descanso' => '60 seg', 'notas' => 'Sostén la barra con agarre prono. Encoge los hombros hacia las orejas de forma vertical, sin hacer movimientos circulares. Mantén 1 seg arriba. Trabaja: trapecio superior, elevador de la escápula.'],
                ],
            ],
        ];

        $progressionNotes = [
            1 => 'Semana 1: Establece tus pesos de trabajo con buena técnica. Registra los pesos usados para hacer seguimiento.',
            2 => 'Semana 2: Aumenta el peso 2.5-5kg en ejercicios principales y 1-2.5kg en accesorios si completaste todas las series y repeticiones la semana anterior.',
            3 => 'Semana 3: Nuevo aumento de carga. Prioriza completar todas las repeticiones antes de subir más peso. Puedes agregar 1 serie extra en el ejercicio principal de cada día.',
            4 => 'Semana 4: Semana de máximo esfuerzo o deload (reduce el peso al 60% y haz las mismas repeticiones para recuperar). Decide según cómo te sientes.',
        ];

        $semanas = [];
        for ($s = 1; $s <= 4; $s++) {
            $diasSemana = [];
            foreach ($dias as $dia) {
                $ejerciciosConProgresion = array_map(function ($ej) use ($s, $progressionNotes) {
                    $ej['notas'] .= ' | '.$progressionNotes[$s];

                    return $ej;
                }, $dia['ejercicios']);

                $diasSemana[] = [
                    'dia' => $dia['dia'],
                    'nombre_sesion' => $dia['nombre_sesion'],
                    'ejercicios' => $ejerciciosConProgresion,
                ];
            }
            $semanas[] = ['semana' => $s, 'dias' => $diasSemana];
        }

        return $semanas;
    }

    // -------------------------------------------------------------------------
    // LEIDY VANNESA (ID: 61) — nutricion + entrenamiento + habitos
    // -------------------------------------------------------------------------

    private function fixLeidy(): void
    {
        $clientId = 61;

        $nutricion = [
            'objetivo' => 'Recomposición corporal: reducir grasa y tonificar con énfasis en glúteos y hombros',
            'macros' => ['proteina_g' => 120, 'carbohidratos_g' => 143, 'grasas_g' => 52],
            'calorias_diarias' => 1600,
            'comidas' => [
                [
                    'nombre' => 'Desayuno',
                    'calorias' => 385,
                    'alimentos' => ['Avena 55g con agua', '2 huevos enteros + 1 clara revueltos', '1 fruta mediana'],
                    'notas' => 'Consumir dentro de la primera hora del día',
                    'macros' => ['proteina_g' => 26, 'carbohidratos_g' => 42, 'grasas_g' => 9],
                ],
                [
                    'nombre' => 'Almuerzo',
                    'calorias' => 470,
                    'alimentos' => ['Pollo a la plancha 135g', 'Arroz integral 70g', 'Ensalada verde', 'Aceite de oliva 1 cdta'],
                    'notas' => 'Principal comida del día',
                    'macros' => ['proteina_g' => 37, 'carbohidratos_g' => 50, 'grasas_g' => 12],
                ],
                [
                    'nombre' => 'Pre-entreno',
                    'calorias' => 185,
                    'alimentos' => ['Pan integral 2 tajadas', 'Jamón de pavo 2 lonjas'],
                    'notas' => '45-60 min antes del entrenamiento',
                    'macros' => ['proteina_g' => 13, 'carbohidratos_g' => 26, 'grasas_g' => 4],
                ],
                [
                    'nombre' => 'Merienda',
                    'calorias' => 210,
                    'alimentos' => ['Yogur griego 150g', 'Almendras 10g'],
                    'notas' => 'Post-entrenamiento o media tarde',
                    'macros' => ['proteina_g' => 17, 'carbohidratos_g' => 13, 'grasas_g' => 9],
                ],
                [
                    'nombre' => 'Cena',
                    'calorias' => 350,
                    'alimentos' => ['Atún o tilapia 120g', 'Brócoli al vapor 200g', 'Camote 75g'],
                    'notas' => 'Cena ligera 2h antes de dormir',
                    'macros' => ['proteina_g' => 27, 'carbohidratos_g' => 25, 'grasas_g' => 10],
                ],
            ],
            'notas_coach' => 'Prioriza la proteína en cada comida. Hidratación mínima 2L al día. Recuerda que el músculo se construye comiendo suficiente proteína.',
            'tips' => [
                'Pesa los alimentos crudos para mayor precisión',
                'Prepara tus comidas con anticipación',
                'Come suficiente antes del entrenamiento para tener energía',
                'Duerme 7-8 horas para maximizar la recuperación',
            ],
        ];

        $entrenamiento = [
            'nombre' => 'Plan Gym 5 Días — Leidy Vannesa',
            'objetivo' => 'Tonificación glúteos, hombros y piernas con pesas en gym',
            'semanas' => $this->buildLeidyTraining(),
        ];

        $habitos = $this->standardRiseHabitsWoman();

        $this->upsertPlan($clientId, 'nutricion', $nutricion);
        $this->upsertPlan($clientId, 'entrenamiento', $entrenamiento);
        $this->upsertPlan($clientId, 'habitos', $habitos);

        $this->info("Leidy Vannesa (ID:{$clientId}) — nutricion + entrenamiento + habitos OK");
    }

    private function buildLeidyTraining(): array
    {
        $dias = [
            [
                'dia' => 'Lunes',
                'nombre_sesion' => 'Glúteos — Activación y Fuerza',
                'ejercicios' => [
                    ['nombre' => 'Abducción sentada con banda', 'series' => 3, 'repeticiones' => '20', 'descanso' => '45 seg', 'notas' => 'Banda sobre las rodillas. Siéntate en el borde del banco. Abre las rodillas hacia afuera apretando los glúteos.'],
                    ['nombre' => 'Sentadilla sumo con mancuerna o barra', 'series' => 4, 'repeticiones' => '12-15', 'descanso' => '90 seg', 'notas' => 'Pies más anchos que los hombros, puntas hacia afuera. Baja controlado hasta paralelo. Activa glúteos y aductores al subir.'],
                    ['nombre' => 'Hip thrust con barra', 'series' => 4, 'repeticiones' => '12-15', 'descanso' => '90 seg', 'notas' => 'Banco a la altura del omóplato. Aprieta los glúteos en la posición alta y mantén 1 segundo.'],
                    ['nombre' => 'Patada de glúteo en polea (cable kickback)', 'series' => 3, 'repeticiones' => '15 por pierna', 'descanso' => '60 seg', 'notas' => 'Cable en el tobillo. Extiende la pierna hacia atrás apretando el glúteo. Mantén la cadera estable.'],
                ],
            ],
            [
                'dia' => 'Martes',
                'nombre_sesion' => 'Hombros y Brazos',
                'ejercicios' => [
                    ['nombre' => 'Press de hombro con mancuernas sentada', 'series' => 4, 'repeticiones' => '10-12', 'descanso' => '90 seg', 'notas' => 'Espalda apoyada. Empuja arriba sin arquear la zona lumbar. Baja hasta los 90°.'],
                    ['nombre' => 'Curl de bíceps con mancuernas', 'series' => 3, 'repeticiones' => '12', 'descanso' => '60 seg', 'notas' => 'Codos pegados al cuerpo. Supina la muñeca al subir. Extiende completamente abajo.'],
                    ['nombre' => 'Extensión de tríceps con mancuerna sobre la cabeza', 'series' => 3, 'repeticiones' => '12', 'descanso' => '60 seg', 'notas' => 'Codos apuntando hacia el techo. Baja la mancuerna detrás de la cabeza. Extiende completamente.'],
                    ['nombre' => 'Elevaciones frontales con mancuernas', 'series' => 3, 'repeticiones' => '12', 'descanso' => '60 seg', 'notas' => 'Eleva al frente hasta la horizontal. Alterna brazos. Control en el descenso.'],
                ],
            ],
            [
                'dia' => 'Miércoles',
                'nombre_sesion' => 'Espalda y Cadena Posterior',
                'ejercicios' => [
                    ['nombre' => 'Peso muerto con mancuernas', 'series' => 4, 'repeticiones' => '12', 'descanso' => '90 seg', 'notas' => 'Mancuernas al frente de los muslos. Espalda neutra. Siente el estiramiento en isquiotibiales al bajar.'],
                    ['nombre' => 'Remo con mancuernas a una mano', 'series' => 4, 'repeticiones' => '12 por brazo', 'descanso' => '90 seg', 'notas' => 'Rodilla y mano en el banco. Lleva la mancuerna al costado del abdomen. Aprieta la escápula.'],
                    ['nombre' => 'Jalón al pecho en polea alta', 'series' => 3, 'repeticiones' => '12', 'descanso' => '90 seg', 'notas' => 'Agarre prono más ancho que los hombros. Lleva la barra al esternón. Aprieta el dorsal.'],
                ],
            ],
            [
                'dia' => 'Jueves',
                'nombre_sesion' => 'Hombros y Brazos — Volumen',
                'ejercicios' => [
                    ['nombre' => 'Remo con mancuernas a una mano', 'series' => 3, 'repeticiones' => '12 por brazo', 'descanso' => '90 seg', 'notas' => 'Enfatiza la contracción de la escápula al final del movimiento.'],
                    ['nombre' => 'Elevación lateral con mancuernas', 'series' => 4, 'repeticiones' => '12-15', 'descanso' => '60 seg', 'notas' => 'Codos ligeramente flexionados. Eleva hasta la altura de los hombros. El meñique ligeramente más alto.'],
                    ['nombre' => 'Curl martillo con mancuernas', 'series' => 3, 'repeticiones' => '12', 'descanso' => '60 seg', 'notas' => 'Agarre neutro. Alterna brazos o simultáneo. Trabaja braquiorradial y bíceps.'],
                ],
            ],
            [
                'dia' => 'Viernes',
                'nombre_sesion' => 'Piernas — Cuádriceps y Posterior',
                'ejercicios' => [
                    ['nombre' => 'Peso muerto con mancuernas', 'series' => 4, 'repeticiones' => '12', 'descanso' => '90 seg', 'notas' => 'Ejercicio principal de pierna posterior. Mantén espalda neutra durante todo el recorrido.'],
                    ['nombre' => 'Sentadilla con barra o mancuernas', 'series' => 4, 'repeticiones' => '12', 'descanso' => '90 seg', 'notas' => 'Pies al ancho de hombros. Baja hasta paralelo. Empuja desde los talones.'],
                    ['nombre' => 'Prensa de piernas', 'series' => 3, 'repeticiones' => '15', 'descanso' => '90 seg', 'notas' => 'Pies a la anchura de caderas. No bloquees rodillas en la posición alta.'],
                    ['nombre' => 'Extensión de cuádriceps en máquina', 'series' => 3, 'repeticiones' => '15', 'descanso' => '60 seg', 'notas' => 'Contrae el cuádriceps en la posición alta. Baja controlado.'],
                ],
            ],
        ];

        return $this->buildFourWeeks($dias);
    }

    // -------------------------------------------------------------------------
    // DANNA SARMIENTO (ID: 64) — nutricion + entrenamiento + habitos
    // -------------------------------------------------------------------------

    private function fixDanna(): void
    {
        $clientId = 64;

        $nutricion = [
            'objetivo' => 'Recomposición corporal con énfasis en glúteos y piernas. Déficit calórico moderado.',
            'macros' => ['proteina_g' => 125, 'carbohidratos_g' => 148, 'grasas_g' => 54],
            'calorias_diarias' => 1650,
            'comidas' => [
                [
                    'nombre' => 'Desayuno',
                    'calorias' => 400,
                    'alimentos' => ['Avena 55g con leche descremada o agua', '2 huevos revueltos', '1 fruta mediana (mango o banano)'],
                    'notas' => 'Consumir dentro de los primeros 45 min de levantarse',
                    'macros' => ['proteina_g' => 27, 'carbohidratos_g' => 43, 'grasas_g' => 9],
                ],
                [
                    'nombre' => 'Almuerzo',
                    'calorias' => 490,
                    'alimentos' => ['Pechuga de pollo a la plancha 140g', 'Arroz integral 75g', 'Ensalada verde con tomate', 'Aguacate 25g'],
                    'notas' => 'Principal comida del día',
                    'macros' => ['proteina_g' => 38, 'carbohidratos_g' => 52, 'grasas_g' => 13],
                ],
                [
                    'nombre' => 'Pre-entreno',
                    'calorias' => 190,
                    'alimentos' => ['Arepa pequeña de maíz', 'Huevo duro 1'],
                    'notas' => '60 min antes del entrenamiento',
                    'macros' => ['proteina_g' => 10, 'carbohidratos_g' => 28, 'grasas_g' => 5],
                ],
                [
                    'nombre' => 'Merienda post-entreno',
                    'calorias' => 220,
                    'alimentos' => ['Yogur griego 150g', 'Nueces 15g'],
                    'notas' => 'Dentro de 30 min post-entrenamiento',
                    'macros' => ['proteina_g' => 18, 'carbohidratos_g' => 13, 'grasas_g' => 11],
                ],
                [
                    'nombre' => 'Cena',
                    'calorias' => 350,
                    'alimentos' => ['Salmón o atún 120g', 'Verduras salteadas 200g', 'Papa o camote 80g'],
                    'notas' => 'Cena ligera 2h antes de dormir',
                    'macros' => ['proteina_g' => 28, 'carbohidratos_g' => 26, 'grasas_g' => 12],
                ],
            ],
            'notas_coach' => 'Prioriza la proteína en cada comida. Hidratación mínima 2.5L de agua al día. Programa de 6 días intensos — asegura descanso y sueño de calidad.',
            'tips' => [
                'Pesa los alimentos crudos para mayor precisión',
                'Prepara batch cooking los domingos',
                'El día de abdomen es ligero — no necesitas comer más carbohidratos',
                'Lleva siempre tu botella de agua al gym',
            ],
        ];

        $entrenamiento = [
            'nombre' => 'Plan Gym 6 Días — Danna Sarmiento',
            'objetivo' => 'Desarrollo de glúteos, piernas y tono general con pesas en gym. 6 días semanales.',
            'semanas' => $this->buildDannaTraining(),
        ];

        $habitos = $this->standardRiseHabitsWoman();

        $this->upsertPlan($clientId, 'nutricion', $nutricion);
        $this->upsertPlan($clientId, 'entrenamiento', $entrenamiento);
        $this->upsertPlan($clientId, 'habitos', $habitos);

        $this->info("Danna Sarmiento (ID:{$clientId}) — nutricion + entrenamiento + habitos OK");
    }

    private function buildDannaTraining(): array
    {
        $dias = [
            [
                'dia' => 'Lunes',
                'nombre_sesion' => 'Glúteos — Fuerza Principal',
                'ejercicios' => [
                    ['nombre' => 'Sentadilla búlgara con mancuernas', 'series' => 4, 'repeticiones' => '10 por pierna', 'descanso' => '90 seg', 'notas' => 'Pie trasero en banco. Baja controlado hasta que la rodilla trasera casi toque el suelo. Activa el glúteo de la pierna delantera.'],
                    ['nombre' => 'Hip thrust con barra', 'series' => 4, 'repeticiones' => '12-15', 'descanso' => '90 seg', 'notas' => 'Banco a la altura del omóplato. Barra con almohadilla sobre la cadera. Aprieta glúteos en la posición alta y mantén 1 segundo.'],
                    ['nombre' => 'Peso muerto rumano con barra', 'series' => 4, 'repeticiones' => '12', 'descanso' => '90 seg', 'notas' => 'Mantén la barra cerca del cuerpo. Espalda neutra. Siente el estiramiento en isquiotibiales.'],
                    ['nombre' => 'Abducción en máquina', 'series' => 3, 'repeticiones' => '20', 'descanso' => '60 seg', 'notas' => 'Aprieta glúteos al abrir. Controla el retorno sin rebotar. Peso moderado para alto volumen.'],
                ],
            ],
            [
                'dia' => 'Martes',
                'nombre_sesion' => 'Abdomen',
                'ejercicios' => [
                    ['nombre' => 'Crunch abdominal', 'series' => 4, 'repeticiones' => '20', 'descanso' => '45 seg', 'notas' => 'Manos detrás de la nuca. Despega solo los hombros del suelo. Exhala al subir. Baja controlado. 4 series de 20 repeticiones.'],
                    ['nombre' => 'Plancha frontal', 'series' => 4, 'repeticiones' => '45 seg', 'descanso' => '30 seg', 'notas' => 'Cuerpo recto de cabeza a talones. No dejes caer las caderas. Respira de forma controlada. 4 series de 45 segundos.'],
                ],
            ],
            [
                'dia' => 'Miércoles',
                'nombre_sesion' => 'Piernas y Glúteos — Volumen',
                'ejercicios' => [
                    ['nombre' => 'Sentadilla sumo con mancuerna', 'series' => 4, 'repeticiones' => '12-15', 'descanso' => '90 seg', 'notas' => 'Pies más anchos que los hombros, puntas hacia afuera. Sostén la mancuerna verticalmente al frente. Activa glúteos y aductores al subir.'],
                    ['nombre' => 'Zancadas caminando con mancuernas', 'series' => 3, 'repeticiones' => '12 por pierna', 'descanso' => '90 seg', 'notas' => 'Da pasos largos hacia adelante. Baja la rodilla trasera sin tocar el suelo. Mantén el torso erguido.'],
                    ['nombre' => 'Extensión de cuádriceps en máquina', 'series' => 3, 'repeticiones' => '15', 'descanso' => '60 seg', 'notas' => 'Contrae el cuádriceps en la posición alta. Baja lento.'],
                    ['nombre' => 'Curl femoral acostada', 'series' => 3, 'repeticiones' => '12-15', 'descanso' => '60 seg', 'notas' => 'Caderas pegadas al banco. Sube hasta los 90° y baja controlado.'],
                ],
            ],
            [
                'dia' => 'Jueves',
                'nombre_sesion' => 'Hombros y Espalda',
                'ejercicios' => [
                    ['nombre' => 'Press de hombro con mancuernas sentada', 'series' => 4, 'repeticiones' => '10-12', 'descanso' => '90 seg', 'notas' => 'Espalda apoyada en banco vertical. Empuja arriba sin arquear la zona lumbar.'],
                    ['nombre' => 'Remo con mancuernas a una mano', 'series' => 4, 'repeticiones' => '12 por brazo', 'descanso' => '90 seg', 'notas' => 'Rodilla y mano contraria en el banco. Lleva la mancuerna al costado del abdomen. Aprieta la escápula.'],
                    ['nombre' => 'Elevaciones laterales con mancuernas', 'series' => 3, 'repeticiones' => '12-15', 'descanso' => '60 seg', 'notas' => 'Codos ligeramente flexionados. Eleva hasta la altura de los hombros.'],
                    ['nombre' => 'Jalón al pecho en polea alta', 'series' => 3, 'repeticiones' => '12', 'descanso' => '90 seg', 'notas' => 'Agarre prono más ancho que los hombros. Lleva la barra al esternón. Aprieta el dorsal.'],
                ],
            ],
            [
                'dia' => 'Viernes',
                'nombre_sesion' => 'Glúteos 2 — Enfoque Máquinas',
                'ejercicios' => [
                    ['nombre' => 'Hip thrust en máquina', 'series' => 4, 'repeticiones' => '12-15', 'descanso' => '90 seg', 'notas' => 'Máxima contracción del glúteo arriba. Mantén 2 segundos en la posición alta.'],
                    ['nombre' => 'Curl femoral sentado en máquina', 'series' => 3, 'repeticiones' => '15', 'descanso' => '60 seg', 'notas' => 'Ajusta el rodillo sobre los tobillos. Baja completo y sube contrayendo isquiotibiales.'],
                    ['nombre' => 'Patada de glúteo en polea (cable kickback)', 'series' => 3, 'repeticiones' => '15 por pierna', 'descanso' => '60 seg', 'notas' => 'Cable en el tobillo. Extiende la pierna hacia atrás apretando el glúteo. Mantén la cadera estable.'],
                    ['nombre' => 'Sentadilla hack en máquina', 'series' => 4, 'repeticiones' => '12', 'descanso' => '90 seg', 'notas' => 'Pies ligeramente adelantados en la plataforma. Baja hasta paralelo. Activa cuádriceps y glúteos.'],
                ],
            ],
            [
                'dia' => 'Sábado',
                'nombre_sesion' => 'Piernas — Completo',
                'ejercicios' => [
                    ['nombre' => 'Avanzadas (zancadas) con mancuernas', 'series' => 4, 'repeticiones' => '12 por pierna', 'descanso' => '90 seg', 'notas' => 'Pasos largos hacia adelante alternando piernas. Torso erguido. Activa el glúteo de la pierna delantera.'],
                    ['nombre' => 'Peso muerto con mancuernas', 'series' => 4, 'repeticiones' => '12', 'descanso' => '90 seg', 'notas' => 'Mantén espalda neutra. Baja las mancuernas por delante de los muslos sintiendo el estiramiento.'],
                    ['nombre' => 'Prensa de piernas', 'series' => 3, 'repeticiones' => '15', 'descanso' => '90 seg', 'notas' => 'Pies a la anchura de caderas. No bloquees rodillas en la posición alta.'],
                ],
            ],
        ];

        return $this->buildFourWeeks($dias);
    }

    // -------------------------------------------------------------------------
    // Shared helpers
    // -------------------------------------------------------------------------

    private function buildFourWeeks(array $dias): array
    {
        $progressionNotes = [
            1 => 'Semana 1: Establece la técnica. Elige un peso con el que puedas completar todas las repeticiones correctamente.',
            2 => 'Semana 2: Aumenta el peso 2-5% si completaste todas las series la semana anterior.',
            3 => 'Semana 3: Añade 1-2 repeticiones por serie o incrementa el peso ligeramente.',
            4 => 'Semana 4: Semana de máximo esfuerzo. Supera los pesos de las semanas anteriores manteniendo la técnica.',
        ];

        $semanas = [];
        for ($s = 1; $s <= 4; $s++) {
            $diasSemana = [];
            foreach ($dias as $dia) {
                $ejerciciosConProgresion = array_map(function ($ej) use ($s, $progressionNotes) {
                    $ej['notas'] .= ' | '.$progressionNotes[$s];

                    return $ej;
                }, $dia['ejercicios']);

                $diasSemana[] = [
                    'dia' => $dia['dia'],
                    'nombre_sesion' => $dia['nombre_sesion'],
                    'ejercicios' => $ejerciciosConProgresion,
                ];
            }
            $semanas[] = ['semana' => $s, 'dias' => $diasSemana];
        }

        return $semanas;
    }

    private function standardRiseHabitsWoman(): array
    {
        return [
            'objetivo' => 'Construir hábitos saludables sostenibles que apoyen los objetivos físicos y mentales del programa RISE',
            'descripcion' => 'Protocolo de hábitos RISE para mujeres. Seguimiento diario durante las 4 semanas del programa.',
            'habitos' => [
                [
                    'id' => 'agua',
                    'nombre' => 'Hidratación diaria',
                    'descripcion' => 'Tomar mínimo 2 litros de agua al día',
                    'meta_diaria' => '2L de agua',
                    'tipo' => 'booleano',
                    'icono' => '💧',
                    'puntos' => 10,
                ],
                [
                    'id' => 'proteina',
                    'nombre' => 'Consumo de proteína',
                    'descripcion' => 'Incluir proteína en cada comida principal (desayuno, almuerzo y cena)',
                    'meta_diaria' => '3 comidas con proteína',
                    'tipo' => 'booleano',
                    'icono' => '🥩',
                    'puntos' => 10,
                ],
                [
                    'id' => 'entrenamiento',
                    'nombre' => 'Completar entrenamiento',
                    'descripcion' => 'Realizar la sesión de entrenamiento del día según el plan',
                    'meta_diaria' => '1 sesión completa',
                    'tipo' => 'booleano',
                    'icono' => '🏋️',
                    'puntos' => 20,
                ],
                [
                    'id' => 'sueno',
                    'nombre' => 'Calidad del sueño',
                    'descripcion' => 'Dormir entre 7 y 9 horas. Evitar pantallas 30 min antes de dormir.',
                    'meta_diaria' => '7-9 horas de sueño',
                    'tipo' => 'booleano',
                    'icono' => '😴',
                    'puntos' => 10,
                ],
                [
                    'id' => 'pasos',
                    'nombre' => 'Actividad diaria',
                    'descripcion' => 'Caminar o alcanzar al menos 7000 pasos durante el día',
                    'meta_diaria' => '7000 pasos',
                    'tipo' => 'numerico',
                    'icono' => '👟',
                    'puntos' => 5,
                ],
                [
                    'id' => 'foto_comida',
                    'nombre' => 'Registro de alimentación',
                    'descripcion' => 'Fotografiar o registrar al menos una comida del día en la app',
                    'meta_diaria' => '1 registro',
                    'tipo' => 'booleano',
                    'icono' => '📸',
                    'puntos' => 5,
                ],
                [
                    'id' => 'estres',
                    'nombre' => 'Gestión del estrés',
                    'descripcion' => 'Realizar 5-10 minutos de respiración, meditación o journaling',
                    'meta_diaria' => '5-10 minutos',
                    'tipo' => 'booleano',
                    'icono' => '🧘',
                    'puntos' => 5,
                ],
            ],
            'puntos_meta_semanal' => 350,
            'notas_coach' => 'Los hábitos son la base del cambio real. Sé constante, no perfecta. Un día difícil no arruina el proceso.',
        ];
    }

    private function upsertPlan(int $clientId, string $planType, array $content): void
    {
        DB::table('assigned_plans')
            ->where('client_id', $clientId)
            ->where('plan_type', $planType)
            ->update(['active' => false]);

        DB::table('assigned_plans')->updateOrInsert(
            [
                'client_id' => $clientId,
                'plan_type' => $planType,
            ],
            [
                'content' => json_encode($content, JSON_UNESCAPED_UNICODE),
                'active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}
