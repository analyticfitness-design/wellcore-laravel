<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class FixRisePrograms extends Command
{
    protected $signature = 'wellcore:fix-rise-programs';

    protected $description = 'Corrige personalized_program en rise_programs para 6 clientes RISE';

    public function handle(): int
    {
        DB::transaction(function () {
            $this->fixLina();
            $this->fixVanessa();
            $this->fixAdriana();
            $this->fixLeidy();
            $this->fixNelson();
            $this->fixDanna();
        });

        $this->info('Todos los programas RISE han sido actualizados correctamente.');

        return self::SUCCESS;
    }

    // ─────────────────────────────────────────────────────────────────
    // Lina — solo actualizar plan_nutricion y plan_habitos
    // ─────────────────────────────────────────────────────────────────

    private function fixLina(): void
    {
        $data = $this->loadProgram('l.gizethmm29@gmail.com');
        if ($data === null) {
            return;
        }

        $data['plan_nutricion'] = [
            'calorias_diarias' => 1600,
            'proteina_g' => 120,
            'carbohidratos_g' => 165,
            'grasas_g' => 50,
            'objetivo' => 'Déficit calórico moderado para reducción de grasa, tonificación de glúteos y piernas.',
            'tips' => [
                'Tomar mínimo 2 litros de agua al día.',
                'Pesar los alimentos para mayor precisión en los macros.',
                'Incluir fuente de proteína en cada comida.',
                'Preparar las comidas con anticipación para evitar improvisaciones.',
            ],
            'comidas_sugeridas' => [
                [
                    'nombre' => 'Desayuno',
                    'opciones' => [
                        '3 claras de huevo + 1 huevo entero revuelto + tostada integral + café negro',
                        'Avena con proteína en polvo (1 scoop) + fruta pequeña (manzana o banano)',
                        'Yogur griego sin azúcar + granola baja en azúcar + 1 fruta',
                    ],
                ],
                [
                    'nombre' => 'Media mañana',
                    'opciones' => [
                        '1 fruta + 20 g almendras o maní',
                        'Batido de proteína con agua o leche descremada',
                        '100 g queso cottage + pepino o zanahoria en tiras',
                    ],
                ],
                [
                    'nombre' => 'Almuerzo',
                    'opciones' => [
                        '150 g pechuga de pollo a la plancha + 1/2 taza arroz integral + ensalada verde con aceite de oliva',
                        '150 g atún en agua + 1/2 taza papa cocida + verduras al vapor',
                        '150 g carne magra + 1/2 taza lenteja cocida + ensalada mixta',
                    ],
                ],
                [
                    'nombre' => 'Merienda',
                    'opciones' => [
                        '1 fruta + 1 cucharada mantequilla de maní natural',
                        '100 g yogur griego sin azúcar + 1/2 taza frutos rojos',
                        'Batido de proteína con agua',
                    ],
                ],
                [
                    'nombre' => 'Cena',
                    'opciones' => [
                        '150 g salmón al horno + 1 taza verduras asadas (zucchini, brócoli, pimentón)',
                        '150 g pechuga de pollo a la plancha + ensalada grande con aderezo limón/aceite',
                        '3 claras de huevo + tortilla integral + aguacate (1/4 unidad)',
                    ],
                ],
            ],
        ];

        $data['plan_habitos'] = $this->habitosEstandar(120);

        $this->saveProgram('l.gizethmm29@gmail.com', $data, 'Lina — nutrición y hábitos actualizados (entrenamiento sin cambios)');
    }

    // ─────────────────────────────────────────────────────────────────
    // Vanessa Diaz — solo actualizar plan_nutricion y plan_habitos
    // ─────────────────────────────────────────────────────────────────

    private function fixVanessa(): void
    {
        $data = $this->loadProgram('Angiev.diaz20@gmail.com');
        if ($data === null) {
            return;
        }

        $data['plan_nutricion'] = [
            'calorias_diarias' => 1700,
            'proteina_g' => 130,
            'carbohidratos_g' => 160,
            'grasas_g' => 55,
            'objetivo' => 'Composición corporal: reducción de grasa manteniendo músculo.',
            'tips' => [
                'Tomar mínimo 2 litros de agua al día.',
                'Incluir fuente de proteína en cada comida.',
                'Distribuir los carbohidratos alrededor del entrenamiento (pre y post).',
                'Preparar las comidas con anticipación.',
                'No saltarse ninguna comida, especialmente el post-entrenamiento.',
            ],
            'comidas_sugeridas' => [
                [
                    'nombre' => 'Desayuno',
                    'opciones' => [
                        '3 claras + 1 huevo entero revuelto + arepa integral pequeña + café negro',
                        'Avena (60 g) + proteína en polvo (1 scoop) + 1 fruta pequeña',
                        'Yogur griego sin azúcar (200 g) + granola baja en azúcar (30 g) + 1 fruta',
                    ],
                ],
                [
                    'nombre' => 'Media mañana',
                    'opciones' => [
                        '1 fruta + 25 g almendras',
                        'Batido de proteína con agua',
                        '100 g queso cottage + zanahoria',
                    ],
                ],
                [
                    'nombre' => 'Almuerzo',
                    'opciones' => [
                        '160 g pechuga de pollo + 1/2 taza arroz integral + ensalada verde con aceite de oliva',
                        '160 g atún en agua + 1/2 taza papa cocida + verduras al vapor',
                        '160 g carne magra + 1/2 taza lenteja + ensalada mixta',
                    ],
                ],
                [
                    'nombre' => 'Merienda',
                    'opciones' => [
                        '1 fruta + 1 cda mantequilla de maní natural',
                        '150 g yogur griego sin azúcar + frutos rojos',
                        'Batido de proteína + agua',
                    ],
                ],
                [
                    'nombre' => 'Cena',
                    'opciones' => [
                        '160 g salmón al horno + verduras asadas (brócoli, zucchini, pimentón)',
                        '160 g pechuga de pollo + ensalada grande con limón y aceite',
                        '3 claras de huevo + tortilla integral + aguacate (1/4)',
                    ],
                ],
            ],
        ];

        $data['plan_habitos'] = $this->habitosEstandar(130);

        $this->saveProgram('Angiev.diaz20@gmail.com', $data, 'Vanessa — nutrición y hábitos actualizados (entrenamiento sin cambios)');
    }

    // ─────────────────────────────────────────────────────────────────
    // Adriana Sarmiento — actualizar nutrición + entrenamiento + hábitos
    // ─────────────────────────────────────────────────────────────────

    private function fixAdriana(): void
    {
        $data = $this->loadProgram('asarmientoslm@gmail.com');
        if ($data === null) {
            return;
        }

        $data['plan_nutricion'] = [
            'calorias_diarias' => 1650,
            'proteina_g' => 125,
            'carbohidratos_g' => 148,
            'grasas_g' => 54,
            'objetivo' => 'Reducción de grasa y tonificación muscular con entrenamiento de gimnasio.',
            'tips' => [
                'Incluir proteína en cada comida para alcanzar la meta de 125 g/día.',
                'Realizar cardio después de pesas para optimizar la quema de grasa.',
                'Hidratarse con mínimo 2 litros de agua al día.',
                'Preparar comidas con anticipación para no improvisar.',
            ],
            'comidas_sugeridas' => [
                [
                    'nombre' => 'Desayuno',
                    'opciones' => [
                        '3 claras + 1 huevo entero revuelto + arepa integral pequeña + café negro',
                        'Avena (50 g) + proteína en polvo (1 scoop) + 1 fruta pequeña',
                        'Yogur griego sin azúcar (180 g) + granola baja en azúcar (25 g) + fruta',
                    ],
                ],
                [
                    'nombre' => 'Almuerzo',
                    'opciones' => [
                        '150 g pechuga de pollo a la plancha + 1/2 taza arroz integral + ensalada verde',
                        '150 g atún en agua + 1/2 taza papa cocida + verduras al vapor',
                        '150 g carne magra + 1/2 taza lenteja + ensalada mixta con aceite de oliva',
                    ],
                ],
                [
                    'nombre' => 'Pre-entreno',
                    'opciones' => [
                        '1 banana + 30 g avena con agua',
                        '1 fruta + 1 rebanada pan integral con mantequilla de maní (1 cda)',
                        'Batido de proteína con 1 banana',
                    ],
                ],
                [
                    'nombre' => 'Merienda post-entreno',
                    'opciones' => [
                        'Batido de proteína + 1 fruta',
                        '150 g yogur griego sin azúcar + frutos rojos',
                        '3 claras de huevo cocidas + 1 fruta',
                    ],
                ],
                [
                    'nombre' => 'Cena',
                    'opciones' => [
                        '150 g salmón al horno + brócoli y zucchini asados',
                        '150 g pechuga de pollo + ensalada grande con limón y aceite',
                        '150 g tilapia al horno + 1/2 taza espinacas salteadas + aguacate (1/4)',
                    ],
                ],
            ],
        ];

        $data['plan_entrenamiento'] = $this->entrenamientoAdriana();

        $data['plan_habitos'] = [
            [
                'nombre' => 'Proteína en cada comida',
                'descripcion' => 'Asegurarse de incluir una fuente de proteína en cada una de las 5 comidas del día.',
                'razon' => 'Alcanzar la meta de 125 g/día es esencial para preservar y tonificar el músculo durante el déficit calórico.',
                'frecuencia' => 'Diario',
            ],
            [
                'nombre' => 'Hidratación 2 litros de agua',
                'descripcion' => 'Tomar mínimo 2 litros de agua distribuidos a lo largo del día.',
                'razon' => 'La hidratación adecuada mejora el rendimiento en el entreno y favorece la quema de grasa.',
                'frecuencia' => 'Diario',
            ],
            [
                'nombre' => 'Sueño reparador 7-8 horas',
                'descripcion' => 'Dormir entre 7 y 8 horas cada noche, manteniendo horarios regulares.',
                'razon' => 'El sueño es cuando el cuerpo se recupera y quema grasa de forma eficiente.',
                'frecuencia' => 'Diario',
            ],
            [
                'nombre' => 'Cardio 20 min después del entreno',
                'descripcion' => 'Realizar 20 minutos de caminadora inclinada o elíptica al finalizar cada sesión de pesas.',
                'razon' => 'El cardio post-pesas acelera la quema de grasa aprovechando las reservas de glucógeno ya utilizadas.',
                'frecuencia' => 'Cada día de entrenamiento',
            ],
            [
                'nombre' => 'Registro de entrenamiento',
                'descripcion' => 'Anotar los pesos utilizados en cada ejercicio después de cada sesión.',
                'razon' => 'El seguimiento permite aplicar sobrecarga progresiva y visualizar el avance semana a semana.',
                'frecuencia' => 'Cada día de entrenamiento',
            ],
        ];

        $this->saveProgram('asarmientoslm@gmail.com', $data, 'Adriana — nutrición, entrenamiento y hábitos actualizados');
    }

    // ─────────────────────────────────────────────────────────────────
    // Leidy Vannesa — actualizar nutrición, hábitos + plan entrenamiento con sustituciones
    // ─────────────────────────────────────────────────────────────────

    private function fixLeidy(): void
    {
        $data = $this->loadProgram('vane08_26@hotmail.com');
        if ($data === null) {
            return;
        }

        $data['plan_nutricion'] = [
            'calorias_diarias' => 1550,
            'proteina_g' => 120,
            'carbohidratos_g' => 160,
            'grasas_g' => 48,
            'objetivo' => 'Recomposición corporal: reducción de grasa manteniendo músculo.',
            'tips' => [
                'Incluir proteína en cada comida para alcanzar la meta de 120 g/día.',
                'Hidratarse con mínimo 2 litros de agua al día.',
                'No saltarse la merienda post-entrenamiento: es clave para la recuperación muscular.',
                'Preparar las comidas con anticipación para mantener la consistencia.',
            ],
            'comidas_sugeridas' => [
                [
                    'nombre' => 'Desayuno',
                    'opciones' => [
                        '3 claras + 1 huevo entero revuelto + arepa integral pequeña + café negro',
                        'Avena (50 g) + proteína en polvo (1 scoop) + 1 fruta pequeña',
                        'Yogur griego sin azúcar (180 g) + granola baja en azúcar (25 g) + fruta',
                    ],
                ],
                [
                    'nombre' => 'Media mañana',
                    'opciones' => [
                        '1 fruta pequeña + 20 g almendras',
                        'Batido de proteína con agua',
                        '100 g queso cottage + zanahoria en tiras',
                    ],
                ],
                [
                    'nombre' => 'Almuerzo',
                    'opciones' => [
                        '140 g pechuga de pollo + 1/2 taza arroz integral + ensalada verde con aceite de oliva',
                        '140 g atún en agua + 1/2 taza papa cocida + verduras al vapor',
                        '140 g carne magra + 1/2 taza lenteja + ensalada mixta',
                    ],
                ],
                [
                    'nombre' => 'Merienda post-entreno',
                    'opciones' => [
                        'Batido de proteína + 1 fruta',
                        '150 g yogur griego + frutos rojos',
                        '3 claras de huevo cocidas + 1 fruta pequeña',
                    ],
                ],
                [
                    'nombre' => 'Cena',
                    'opciones' => [
                        '140 g salmón al horno + brócoli y zucchini asados',
                        '140 g pechuga de pollo + ensalada grande con limón y aceite',
                        '3 claras de huevo + tortilla integral + aguacate (1/4)',
                    ],
                ],
            ],
        ];

        // Aplicar sustituciones al plan_entrenamiento existente si está disponible,
        // de lo contrario escribir plan nuevo con sustituciones ya aplicadas
        $existingEntrenamiento = $data['plan_entrenamiento'] ?? null;

        if (! empty($existingEntrenamiento['semanas'])) {
            $sustituciones = [
                'Abducción de pie con banda' => 'Abducción sentada con banda',
                'Press de hombro con botella' => 'Press de hombro con mancuerna',
                'Curl de bíceps con botella' => 'Curl de bíceps con mancuerna',
                'Extensión de tríceps sobre cabeza con botella' => 'Extensión de tríceps sobre cabeza con mancuerna',
                'Buenos días con banda' => 'Peso muerto con mancuerna',
                'Remo invertido con mesa' => 'Remo con mancuernas',
                'Elevación lateral con botella' => 'Elevación lateral con mancuerna',
                'Curl martillo con botella' => 'Curl martillo con mancuerna',
                'Peso muerto rumano con botellas' => 'Peso muerto con mancuernas',
            ];

            foreach ($existingEntrenamiento['semanas'] as &$semana) {
                foreach ($semana['dias'] as &$dia) {
                    foreach ($dia['ejercicios'] as &$ejercicio) {
                        $ejercicio['nombre'] = strtr($ejercicio['nombre'], $sustituciones);
                    }
                    unset($ejercicio);
                }
                unset($dia);
            }
            unset($semana);

            $data['plan_entrenamiento'] = $existingEntrenamiento;
            $mensaje = 'Leidy — nutrición, hábitos actualizados + sustituciones de ejercicios aplicadas al plan existente';
        } else {
            $data['plan_entrenamiento'] = $this->entrenamientoLeidy();
            $mensaje = 'Leidy — nutrición, hábitos y entrenamiento actualizados (plan nuevo con sustituciones aplicadas)';
        }

        $data['plan_habitos'] = [
            [
                'nombre' => 'Hidratación 2 litros de agua',
                'descripcion' => 'Tomar mínimo 2 litros de agua distribuidos durante el día.',
                'razon' => 'La hidratación óptima mejora el rendimiento en el entreno y favorece la pérdida de grasa.',
                'frecuencia' => 'Diario',
            ],
            [
                'nombre' => 'Proteína en cada comida',
                'descripcion' => 'Incluir una fuente de proteína en cada una de las 5 comidas del día.',
                'razon' => 'Esencial para alcanzar la meta de 120 g/día y preservar masa muscular durante la recomposición.',
                'frecuencia' => 'Diario',
            ],
            [
                'nombre' => 'Sueño reparador 7-8 horas',
                'descripcion' => 'Dormir entre 7 y 8 horas cada noche manteniendo un horario constante.',
                'razon' => 'La recuperación ocurre durante el sueño; dormir mal frena el progreso y aumenta el cortisol.',
                'frecuencia' => 'Diario',
            ],
            [
                'nombre' => 'Entrenamiento constante 5 días a la semana',
                'descripcion' => 'Completar las 5 sesiones de entrenamiento semanales (lunes a viernes) sin excusas.',
                'razon' => 'La consistencia es el factor más importante en la recomposición corporal.',
                'frecuencia' => '5 días a la semana',
            ],
            [
                'nombre' => 'Gestión del estrés',
                'descripcion' => 'Dedicar 10 minutos diarios a meditación guiada o ejercicios de respiración profunda.',
                'razon' => 'El estrés crónico eleva el cortisol, lo que dificulta la pérdida de grasa abdominal.',
                'frecuencia' => 'Diario',
            ],
        ];

        $this->saveProgram('vane08_26@hotmail.com', $data, $mensaje);
    }

    // ─────────────────────────────────────────────────────────────────
    // Nelson Iván Roa — solo actualizar plan_entrenamiento
    // ─────────────────────────────────────────────────────────────────

    private function fixNelson(): void
    {
        $data = $this->loadProgram('nelsonroasotelo@gmail.com');
        if ($data === null) {
            return;
        }

        $data['plan_entrenamiento'] = $this->entrenamientoNelson();

        $this->saveProgram('nelsonroasotelo@gmail.com', $data, 'Nelson — entrenamiento actualizado a pesas en gimnasio (nutrición y hábitos sin cambios)');
    }

    // ─────────────────────────────────────────────────────────────────
    // Danna Sarmiento — actualizar nutrición, hábitos + sustituciones entrenamiento
    // ─────────────────────────────────────────────────────────────────

    private function fixDanna(): void
    {
        // Buscar por nombre ya que no se proporcionó email, intentar variantes
        $client = DB::table('clients')
            ->where(function ($q) {
                $q->whereRaw("LOWER(name) LIKE '%danna%sarmiento%'")
                    ->orWhereRaw("LOWER(name) LIKE '%sarmiento%danna%'");
            })
            ->first();

        if (! $client) {
            $this->warn('Danna Sarmiento — cliente no encontrado. Verifica el nombre en la tabla clients.');

            return;
        }

        $rp = DB::table('rise_programs')->where('client_id', $client->id)->first();
        if (! $rp) {
            $this->warn("Danna Sarmiento — sin rise_program (client_id={$client->id}).");

            return;
        }

        $data = json_decode($rp->personalized_program ?? '{}', true) ?? [];

        $data['plan_nutricion'] = [
            'calorias_diarias' => 1700,
            'proteina_g' => 130,
            'carbohidratos_g' => 175,
            'grasas_g' => 55,
            'objetivo' => 'Recomposición corporal: reducción de grasa y tonificación muscular.',
            'tips' => [
                'Tomar mínimo 2 litros de agua al día.',
                'Incluir proteína en cada comida para alcanzar la meta de 130 g/día.',
                'Distribuir los carbohidratos alrededor del entrenamiento.',
                'Preparar las comidas con anticipación.',
                'Dormir 7-8 horas para optimizar la recuperación.',
            ],
            'comidas_sugeridas' => [
                [
                    'nombre' => 'Desayuno',
                    'opciones' => [
                        '3 claras + 2 huevos enteros revueltos + arepa integral + café negro',
                        'Avena (60 g) + proteína en polvo (1 scoop) + 1 fruta',
                        'Yogur griego sin azúcar (200 g) + granola baja en azúcar (30 g) + fruta',
                    ],
                ],
                [
                    'nombre' => 'Media mañana',
                    'opciones' => [
                        '1 fruta + 25 g almendras o maní',
                        'Batido de proteína con agua',
                        '100 g queso cottage + zanahoria o pepino',
                    ],
                ],
                [
                    'nombre' => 'Almuerzo',
                    'opciones' => [
                        '160 g pechuga de pollo + 1/2 taza arroz integral + ensalada verde con aceite de oliva',
                        '160 g atún en agua + 1/2 taza papa cocida + verduras al vapor',
                        '160 g carne magra + 1/2 taza lenteja + ensalada mixta',
                    ],
                ],
                [
                    'nombre' => 'Merienda',
                    'opciones' => [
                        '1 fruta + 1 cda mantequilla de maní natural',
                        '150 g yogur griego + frutos rojos',
                        'Batido de proteína + agua',
                    ],
                ],
                [
                    'nombre' => 'Cena',
                    'opciones' => [
                        '160 g salmón al horno + brócoli y zucchini asados',
                        '160 g pechuga de pollo + ensalada grande con limón y aceite',
                        '3 claras de huevo + tortilla integral + aguacate (1/4)',
                    ],
                ],
            ],
        ];

        // Aplicar sustituciones al entrenamiento existente si está disponible
        $existingEntrenamiento = $data['plan_entrenamiento'] ?? null;

        if (! empty($existingEntrenamiento['semanas'])) {
            $sustituciones = [
                'Sentadilla sumo en Smith' => 'Sentadilla búlgara con mancuernas',
                'Sentadilla sumo con barra Smith' => 'Sentadilla sumo con mancuerna',
                'Sentadilla sumo con Smith' => 'Sentadilla sumo con mancuerna',
                'Leg press' => 'Hip thrust en máquina',
                'Puente de glúteo con barra' => 'Curl femoral sentado',
                'Zancada con rotación de tronco' => 'Avanzadas con mancuernas',
            ];

            // Ejercicios a eliminar por día
            $eliminarEnDia = [
                'martes' => ['Crunch abdominal', 'Plancha frontal'],   // mantener solo estos dos, eliminar los demás abdominales
                'jueves' => ['Press Arnold con mancuerna', 'Elevación de piernas colgada'],
            ];

            foreach ($existingEntrenamiento['semanas'] as &$semana) {
                foreach ($semana['dias'] as &$dia) {
                    $nombreDia = mb_strtolower($dia['nombre'] ?? '');

                    // Sustituciones generales
                    foreach ($dia['ejercicios'] as &$ejercicio) {
                        $ejercicio['nombre'] = strtr($ejercicio['nombre'], $sustituciones);
                    }
                    unset($ejercicio);

                    // Lunes: Sentadilla sumo en Smith → Sentadilla búlgara con mancuernas (ya cubierto arriba)

                    // Martes: dejar solo Crunch abdominal y Plancha frontal (eliminar otros abs)
                    if (str_contains($nombreDia, 'martes')) {
                        $keepNombres = ['Crunch abdominal', 'Plancha frontal'];
                        $ejerciciosFiltrados = array_filter($dia['ejercicios'], function ($ej) use ($keepNombres) {
                            // Conservar todos los ejercicios que NO sean abdominales, más los dos permitidos
                            $esAbdominal = str_contains(mb_strtolower($ej['nombre']), 'abdom')
                                || str_contains(mb_strtolower($ej['nombre']), 'plancha')
                                || str_contains(mb_strtolower($ej['nombre']), 'crunch')
                                || str_contains(mb_strtolower($ej['nombre']), 'sit-up')
                                || str_contains(mb_strtolower($ej['nombre']), 'oblicuo')
                                || str_contains(mb_strtolower($ej['nombre']), 'bicicleta')
                                || str_contains(mb_strtolower($ej['nombre']), 'mountain climber')
                                || str_contains(mb_strtolower($ej['nombre']), 'russian twist');

                            if (! $esAbdominal) {
                                return true;
                            }

                            return in_array($ej['nombre'], $keepNombres, true);
                        });
                        $dia['ejercicios'] = array_values($ejerciciosFiltrados);
                    }

                    // Miércoles: Sentadilla sumo en Smith → Sentadilla sumo con mancuerna (sustitución adicional)
                    if (str_contains($nombreDia, 'miércoles') || str_contains($nombreDia, 'miercoles')) {
                        foreach ($dia['ejercicios'] as &$ejercicio) {
                            if (str_contains($ejercicio['nombre'], 'Sentadilla búlgara con mancuernas')) {
                                // Si ya fue sustituida por sumo→búlgara en miércoles, revertir a sumo con mancuerna
                                // (Lunes=búlgara, Miércoles=sumo con mancuerna)
                                $ejercicio['nombre'] = 'Sentadilla sumo con mancuerna';
                            }
                        }
                        unset($ejercicio);
                    }

                    // Jueves: eliminar Press Arnold y Elevación de piernas colgada
                    if (str_contains($nombreDia, 'jueves')) {
                        $dia['ejercicios'] = array_values(array_filter(
                            $dia['ejercicios'],
                            fn ($ej) => ! in_array($ej['nombre'], ['Press Arnold con mancuerna', 'Elevación de piernas colgada'], true)
                        ));
                    }

                    // Sábado: Zancada con rotación → Avanzadas con mancuernas (ya cubierto por sustituciones)
                }
                unset($dia);
            }
            unset($semana);

            $data['plan_entrenamiento'] = $existingEntrenamiento;
            $mensaje = 'Danna — nutrición, hábitos actualizados + sustituciones de ejercicios aplicadas al plan existente';
        } else {
            $data['plan_entrenamiento'] = $this->entrenamientoDanna();
            $mensaje = 'Danna — nutrición, hábitos y entrenamiento actualizados (plan nuevo con sustituciones aplicadas)';
        }

        $data['plan_habitos'] = [
            [
                'nombre' => 'Hidratación 2 litros de agua',
                'descripcion' => 'Tomar mínimo 2 litros de agua distribuidos durante el día.',
                'razon' => 'La hidratación óptima es fundamental para el rendimiento, la recuperación y la quema de grasa.',
                'frecuencia' => 'Diario',
            ],
            [
                'nombre' => 'Proteína en cada comida',
                'descripcion' => 'Incluir una fuente de proteína en cada una de las 5 comidas del día.',
                'razon' => 'Alcanzar los 130 g/día de proteína es esencial para tonificar y reducir grasa simultáneamente.',
                'frecuencia' => 'Diario',
            ],
            [
                'nombre' => 'Sueño reparador 7-8 horas',
                'descripcion' => 'Dormir entre 7 y 8 horas cada noche con horarios regulares.',
                'razon' => 'El sueño es cuando el cuerpo produce hormona de crecimiento y se recupera de los entrenamientos.',
                'frecuencia' => 'Diario',
            ],
            [
                'nombre' => 'Entrenamiento constante 6 días a la semana',
                'descripcion' => 'Completar las 6 sesiones de entrenamiento semanales sin saltarse ninguna.',
                'razon' => 'La consistencia de 6 días garantiza el estímulo necesario para la recomposición corporal.',
                'frecuencia' => '6 días a la semana',
            ],
            [
                'nombre' => 'Gestión del estrés y bienestar mental',
                'descripcion' => 'Dedicar 10-15 minutos al día a actividades de relajación: meditación, respiración, lectura o contacto con la naturaleza.',
                'razon' => 'El estrés crónico eleva el cortisol, dificultando la pérdida de grasa y la recuperación muscular.',
                'frecuencia' => 'Diario',
            ],
        ];

        DB::table('rise_programs')
            ->where('client_id', $client->id)
            ->update(['personalized_program' => json_encode($data, JSON_UNESCAPED_UNICODE)]);

        $this->info("Danna Sarmiento (client_id={$client->id}) — {$mensaje}");
    }

    // ─────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────

    /** @return array<string, mixed>|null */
    private function loadProgram(string $email): ?array
    {
        $client = DB::table('clients')
            ->whereRaw('LOWER(email) = ?', [strtolower($email)])
            ->first();

        if (! $client) {
            $this->warn("Cliente no encontrado: {$email}");

            return null;
        }

        $rp = DB::table('rise_programs')
            ->where('client_id', $client->id)
            ->first();

        if (! $rp) {
            $this->warn("Sin rise_program para: {$email} (client_id={$client->id})");

            return null;
        }

        return json_decode($rp->personalized_program ?? '{}', true) ?? [];
    }

    /** @param array<string, mixed> $data */
    private function saveProgram(string $email, array $data, string $mensaje): void
    {
        $client = DB::table('clients')
            ->whereRaw('LOWER(email) = ?', [strtolower($email)])
            ->first();

        if (! $client) {
            return;
        }

        DB::table('rise_programs')
            ->where('client_id', $client->id)
            ->update(['personalized_program' => json_encode($data, JSON_UNESCAPED_UNICODE)]);

        $this->info("{$client->name} (client_id={$client->id}) — {$mensaje}");
    }

    /** @return array<int, array<string, mixed>> */
    private function habitosEstandar(int $metaProteina): array
    {
        return [
            [
                'nombre' => 'Sueño reparador 8 horas',
                'descripcion' => 'Dormir entre 7 y 8 horas cada noche, manteniendo horarios regulares de sueño.',
                'razon' => 'El sueño es cuando el cuerpo se recupera, quema grasa y regula el apetito.',
                'frecuencia' => 'Diario',
            ],
            [
                'nombre' => 'Hidratación 2 litros de agua',
                'descripcion' => 'Tomar mínimo 2 litros de agua distribuidos a lo largo del día.',
                'razon' => 'La hidratación óptima mejora el rendimiento, la digestión y la quema de grasa.',
                'frecuencia' => 'Diario',
            ],
            [
                'nombre' => 'Proteína en cada comida',
                'descripcion' => "Incluir una fuente de proteína en cada una de las comidas del día. Meta: {$metaProteina} g/día.",
                'razon' => 'Mantener la proteína alta preserva la masa muscular durante el déficit y satura el apetito.',
                'frecuencia' => 'Diario',
            ],
            [
                'nombre' => 'Entrenamiento 3 veces por semana',
                'descripcion' => 'Completar las 3 sesiones de entrenamiento semanales sin saltarse ninguna.',
                'razon' => 'La consistencia es el factor más determinante en la transformación corporal.',
                'frecuencia' => '3 días a la semana',
            ],
            [
                'nombre' => 'Gestión del estrés',
                'descripcion' => 'Dedicar 10 minutos al día a respiración profunda, meditación o caminata tranquila.',
                'razon' => 'El estrés crónico eleva el cortisol, dificultando la pérdida de grasa abdominal.',
                'frecuencia' => 'Diario',
            ],
        ];
    }

    // ─────────────────────────────────────────────────────────────────
    // Entrenamiento completo — Adriana (5 días, 4 semanas, gym)
    // ─────────────────────────────────────────────────────────────────

    /** @return array<string, mixed> */
    private function entrenamientoAdriana(): array
    {
        $diasBase = [
            [
                'nombre' => 'Día 1 - Glúteos y Cuádriceps',
                'tipo' => 'piernas',
                'ejercicios' => [
                    ['nombre' => 'Sentadilla en máquina Smith',        'series' => 4, 'repeticiones' => '10-12', 'descanso' => '90s', 'notas' => 'Espalda recta, rodillas alineadas con los pies.'],
                    ['nombre' => 'Zancadas con mancuerna',             'series' => 3, 'repeticiones' => '12 c/pierna', 'descanso' => '60s', 'notas' => 'Paso largo, torso erguido.'],
                    ['nombre' => 'Prensa de piernas',                  'series' => 4, 'repeticiones' => '12-15', 'descanso' => '90s', 'notas' => 'Pies a la anchura de hombros, no bloquear rodillas al extender.'],
                    ['nombre' => 'Curl femoral en máquina acostada',   'series' => 3, 'repeticiones' => '12-15', 'descanso' => '60s', 'notas' => 'Movimiento controlado en la bajada.'],
                    ['nombre' => 'Hip thrust en máquina',              'series' => 4, 'repeticiones' => '15', 'descanso' => '60s', 'notas' => 'Apretar glúteos en el punto más alto, mantener 1 segundo.'],
                    ['nombre' => 'Cardio final (caminadora inclinada o elíptica)', 'series' => 1, 'repeticiones' => '20 min', 'descanso' => '-', 'notas' => 'Intensidad moderada, zona aeróbica.'],
                ],
            ],
            [
                'nombre' => 'Día 2 - Hombros y Pecho',
                'tipo' => 'empuje',
                'ejercicios' => [
                    ['nombre' => 'Press de hombro con mancuerna sentada', 'series' => 4, 'repeticiones' => '10-12', 'descanso' => '90s', 'notas' => 'Codos a 90° al bajar, no arquear la espalda.'],
                    ['nombre' => 'Press en banco inclinado con mancuerna', 'series' => 4, 'repeticiones' => '10-12', 'descanso' => '90s', 'notas' => 'Banco a 30-45°, bajar hasta sentir estiramiento en pecho.'],
                    ['nombre' => 'Elevaciones laterales en máquina',      'series' => 4, 'repeticiones' => '15', 'descanso' => '60s', 'notas' => 'Codos ligeramente doblados, subir hasta la altura del hombro.'],
                    ['nombre' => 'Fondos en banco (tríceps)',              'series' => 3, 'repeticiones' => '12-15', 'descanso' => '60s', 'notas' => 'Cuerpo cerca del banco, codos apuntando atrás.'],
                    ['nombre' => 'Extensión de tríceps en polea alta',    'series' => 3, 'repeticiones' => '12-15', 'descanso' => '60s', 'notas' => 'Codos fijos al cuerpo, extensión completa.'],
                    ['nombre' => 'Cardio final (caminadora inclinada o elíptica)', 'series' => 1, 'repeticiones' => '20 min', 'descanso' => '-', 'notas' => 'Intensidad moderada.'],
                ],
            ],
            [
                'nombre' => 'Día 3 - Isquios y Glúteos Posterior',
                'tipo' => 'posterior',
                'ejercicios' => [
                    ['nombre' => 'Peso muerto rumano con mancuernas', 'series' => 4, 'repeticiones' => '10-12', 'descanso' => '90s', 'notas' => 'Espalda recta, bajar sintiendo estiramiento en isquios.'],
                    ['nombre' => 'Patada diagonal en polea',          'series' => 3, 'repeticiones' => '15 c/pierna', 'descanso' => '60s', 'notas' => 'Movimiento controlado, apretar glúteo al finalizar.'],
                    ['nombre' => 'Abducción en máquina',              'series' => 4, 'repeticiones' => '20', 'descanso' => '60s', 'notas' => 'Movimiento lento y controlado, sentir glúteo medio.'],
                    ['nombre' => 'Hip thrust en máquina',             'series' => 4, 'repeticiones' => '15', 'descanso' => '60s', 'notas' => 'Empuje potente, apretar glúteos arriba.'],
                    ['nombre' => 'Cardio final (caminadora inclinada o elíptica)', 'series' => 1, 'repeticiones' => '20 min', 'descanso' => '-', 'notas' => 'Intensidad moderada.'],
                ],
            ],
            [
                'nombre' => 'Día 4 - Espalda y Bíceps',
                'tipo' => 'jalar',
                'ejercicios' => [
                    ['nombre' => 'Jalón al pecho en polea',          'series' => 4, 'repeticiones' => '10-12', 'descanso' => '90s', 'notas' => 'Pecho erguido, bajar la barra hasta la barbilla.'],
                    ['nombre' => 'Remo con mancuerna',               'series' => 4, 'repeticiones' => '10 c/lado', 'descanso' => '60s', 'notas' => 'Espalda paralela al suelo, codo hacia el techo.'],
                    ['nombre' => 'Remo en máquina',                  'series' => 3, 'repeticiones' => '12', 'descanso' => '60s', 'notas' => 'Comprimir omóplatos al final del movimiento.'],
                    ['nombre' => 'Curl de bíceps con mancuerna',     'series' => 3, 'repeticiones' => '12-15', 'descanso' => '60s', 'notas' => 'Codos fijos al cuerpo, supinación completa.'],
                    ['nombre' => 'Curl martillo',                    'series' => 3, 'repeticiones' => '12', 'descanso' => '60s', 'notas' => 'Agarre neutro, movimiento controlado.'],
                    ['nombre' => 'Cardio final (caminadora inclinada o elíptica)', 'series' => 1, 'repeticiones' => '20 min', 'descanso' => '-', 'notas' => 'Intensidad moderada.'],
                ],
            ],
            [
                'nombre' => 'Día 5 - Glúteos y Femorales',
                'tipo' => 'posterior',
                'ejercicios' => [
                    ['nombre' => 'Peso muerto con barra',      'series' => 4, 'repeticiones' => '8-10', 'descanso' => '120s', 'notas' => 'Movimiento compuesto principal, técnica perfecta.'],
                    ['nombre' => 'Cable pull through',         'series' => 4, 'repeticiones' => '15', 'descanso' => '60s', 'notas' => 'Bisagra de cadera, apretar glúteos al finalizar.'],
                    ['nombre' => 'Hip thrust en máquina',      'series' => 4, 'repeticiones' => '15', 'descanso' => '60s', 'notas' => 'Máximo empuje, squeeze en la cima.'],
                    ['nombre' => 'Curl femoral sentado',       'series' => 3, 'repeticiones' => '12-15', 'descanso' => '60s', 'notas' => 'Bajada controlada de 3 segundos.'],
                    ['nombre' => 'Abducción en máquina',       'series' => 3, 'repeticiones' => '20', 'descanso' => '60s', 'notas' => 'Activar glúteo medio conscientemente.'],
                    ['nombre' => 'Cardio final (caminadora inclinada o elíptica)', 'series' => 1, 'repeticiones' => '20 min', 'descanso' => '-', 'notas' => 'Finalizar la semana con cardio de baja-media intensidad.'],
                ],
            ],
        ];

        $notas = [
            1 => 'Establece la técnica. Elige un peso con el que puedas completar todas las repeticiones con buena forma.',
            2 => 'Aumenta 5-10% el peso en ejercicios principales. Mantén técnica perfecta.',
            3 => 'Semana de volumen. Agrega 1 serie extra a los ejercicios compuestos principales.',
            4 => 'Semana de intensidad. Lleva las últimas 2 repeticiones al fallo muscular controlado.',
        ];

        $titulos = [
            1 => 'Base y Técnica',
            2 => 'Aumento de Carga',
            3 => 'Volumen',
            4 => 'Intensidad',
        ];

        $semanas = [];
        for ($s = 1; $s <= 4; $s++) {
            $dias = $diasBase;
            if ($s >= 3) {
                // Semana 3 y 4: incrementar series en compuestos
                foreach ($dias as &$dia) {
                    foreach ($dia['ejercicios'] as &$ej) {
                        if (! str_contains(strtolower($ej['nombre']), 'cardio')) {
                            $ej['series'] = $s === 3 ? $ej['series'] + 1 : $ej['series'];
                        }
                    }
                    unset($ej);
                }
                unset($dia);
            }

            $semanas[] = [
                'semana' => $s,
                'titulo' => $titulos[$s],
                'nota' => "Semana {$s}: {$notas[$s]}",
                'dias' => $dias,
            ];
        }

        return [
            'duracion_semanas' => 4,
            'dias_por_semana' => 5,
            'objetivo' => 'Tonificación muscular, reducción de grasa y fortalecimiento general con énfasis en glúteos y piernas.',
            'nivel' => 'Intermedio',
            'lugar' => 'Gimnasio',
            'semanas' => $semanas,
        ];
    }

    // ─────────────────────────────────────────────────────────────────
    // Entrenamiento completo — Nelson (4 días, 4 semanas, pesas gym)
    // ─────────────────────────────────────────────────────────────────

    /** @return array<string, mixed> */
    private function entrenamientoNelson(): array
    {
        $diasBase = [
            [
                'nombre' => 'Día 1 - Pecho y Tríceps',
                'tipo' => 'empuje',
                'ejercicios' => [
                    ['nombre' => 'Press de banca con barra',            'series' => 4, 'repeticiones' => '8-10',  'descanso' => '120s', 'notas' => 'Movimiento compuesto principal. Escápulas retraídas y pies firmes en el suelo.'],
                    ['nombre' => 'Press inclinado con mancuernas',      'series' => 3, 'repeticiones' => '10-12', 'descanso' => '90s',  'notas' => 'Banco a 30-45°. Bajar hasta sentir estiramiento en la parte alta del pecho.'],
                    ['nombre' => 'Aperturas con mancuernas',            'series' => 3, 'repeticiones' => '12-15', 'descanso' => '60s',  'notas' => 'Codos ligeramente doblados, movimiento en arco.'],
                    ['nombre' => 'Fondos en paralelas',                 'series' => 3, 'repeticiones' => '10-12', 'descanso' => '90s',  'notas' => 'Cuerpo ligeramente inclinado hacia adelante para mayor activación del pecho.'],
                    ['nombre' => 'Extensión de tríceps en polea alta',  'series' => 4, 'repeticiones' => '12-15', 'descanso' => '60s',  'notas' => 'Codos fijos al cuerpo, extensión completa abajo.'],
                    ['nombre' => 'Press de tríceps con barra Z',        'series' => 3, 'repeticiones' => '10-12', 'descanso' => '60s',  'notas' => 'Codos apuntando al techo, bajar controlado hasta la frente.'],
                ],
            ],
            [
                'nombre' => 'Día 2 - Espalda y Bíceps',
                'tipo' => 'jalar',
                'ejercicios' => [
                    ['nombre' => 'Peso muerto con barra',            'series' => 4, 'repeticiones' => '6-8',   'descanso' => '150s', 'notas' => 'Ejercicio principal. Espalda recta, cadera abajo al inicio, empujar el suelo.'],
                    ['nombre' => 'Jalón al pecho en polea',          'series' => 4, 'repeticiones' => '10-12', 'descanso' => '90s',  'notas' => 'Pecho erguido, tirar hasta la barbilla.'],
                    ['nombre' => 'Remo con barra',                   'series' => 4, 'repeticiones' => '8-10',  'descanso' => '90s',  'notas' => 'Torso a 45°, tirar la barra hacia el ombligo.'],
                    ['nombre' => 'Remo con mancuerna',               'series' => 3, 'repeticiones' => '10 c/lado', 'descanso' => '60s', 'notas' => 'Rodilla apoyada en banco, codo hacia el techo.'],
                    ['nombre' => 'Curl de bíceps con barra',         'series' => 4, 'repeticiones' => '10-12', 'descanso' => '60s',  'notas' => 'Codos fijos al cuerpo, supinar al subir.'],
                    ['nombre' => 'Curl martillo con mancuernas',     'series' => 3, 'repeticiones' => '12',    'descanso' => '60s',  'notas' => 'Agarre neutro, activa braquial y braquiorradial.'],
                ],
            ],
            [
                'nombre' => 'Día 3 - Piernas',
                'tipo' => 'piernas',
                'ejercicios' => [
                    ['nombre' => 'Sentadilla con barra',                  'series' => 4, 'repeticiones' => '8-10',  'descanso' => '150s', 'notas' => 'Ejercicio rey de piernas. Barra en trapecios, bajar hasta paralelo o más.'],
                    ['nombre' => 'Prensa 45 grados',                      'series' => 4, 'repeticiones' => '10-12', 'descanso' => '90s',  'notas' => 'Pies a la anchura de hombros. No bloquear rodillas al extender.'],
                    ['nombre' => 'Extensión de cuádriceps en máquina',    'series' => 3, 'repeticiones' => '12-15', 'descanso' => '60s',  'notas' => 'Mantener 2 segundos en la cima, bajada controlada.'],
                    ['nombre' => 'Curl femoral acostado',                 'series' => 3, 'repeticiones' => '12-15', 'descanso' => '60s',  'notas' => 'Bajada lenta de 3 segundos.'],
                    ['nombre' => 'Hip thrust con barra',                  'series' => 4, 'repeticiones' => '10-12', 'descanso' => '90s',  'notas' => 'Hombros en banco, barra sobre cadera con pad. Apretar glúteos arriba.'],
                    ['nombre' => 'Elevación de talones de pie',           'series' => 4, 'repeticiones' => '15-20', 'descanso' => '60s',  'notas' => 'Rango completo de movimiento, mantener 1 segundo arriba.'],
                ],
            ],
            [
                'nombre' => 'Día 4 - Hombros y Trapecio',
                'tipo' => 'hombros',
                'ejercicios' => [
                    ['nombre' => 'Press militar con barra de pie',          'series' => 4, 'repeticiones' => '8-10',  'descanso' => '120s', 'notas' => 'Ejercicio principal de hombros. Core activo, no arquear la espalda.'],
                    ['nombre' => 'Elevaciones laterales con mancuernas',    'series' => 4, 'repeticiones' => '15',    'descanso' => '60s',  'notas' => 'Codos ligeramente doblados, subir hasta la altura del hombro.'],
                    ['nombre' => 'Elevaciones frontales con mancuernas',    'series' => 3, 'repeticiones' => '12',    'descanso' => '60s',  'notas' => 'Subir alternado o simultáneo hasta la altura del hombro.'],
                    ['nombre' => 'Remo al mentón con barra',                'series' => 3, 'repeticiones' => '10-12', 'descanso' => '60s',  'notas' => 'Agarre cerrado, tirar hacia el mentón con codos altos.'],
                    ['nombre' => 'Encogimiento de hombros con mancuernas',  'series' => 4, 'repeticiones' => '12-15', 'descanso' => '60s',  'notas' => 'Subir directamente, sin rotar los hombros.'],
                    ['nombre' => 'Face pulls en polea',                     'series' => 3, 'repeticiones' => '15',    'descanso' => '60s',  'notas' => 'Polea a altura de cara, tirar hacia la frente con rotación externa.'],
                ],
            ],
        ];

        $notas = [
            1 => 'Establece la técnica. Elige un peso con el que puedas completar todas las repeticiones con buena forma.',
            2 => 'Aumenta 5-10% el peso en ejercicios principales. Mantén técnica perfecta.',
            3 => 'Semana de volumen. Agrega 1 serie extra a los ejercicios compuestos principales.',
            4 => 'Semana de intensidad. Lleva las últimas 2 repeticiones al fallo muscular controlado.',
        ];

        $titulos = [
            1 => 'Base y Técnica',
            2 => 'Aumento de Carga',
            3 => 'Volumen',
            4 => 'Intensidad',
        ];

        $semanas = [];
        for ($s = 1; $s <= 4; $s++) {
            $dias = $diasBase;
            if ($s === 3) {
                foreach ($dias as &$dia) {
                    foreach ($dia['ejercicios'] as &$ej) {
                        $ej['series'] = $ej['series'] + 1;
                    }
                    unset($ej);
                }
                unset($dia);
            }

            $semanas[] = [
                'semana' => $s,
                'titulo' => $titulos[$s],
                'nota' => "Semana {$s}: {$notas[$s]}",
                'dias' => $dias,
            ];
        }

        return [
            'duracion_semanas' => 4,
            'dias_por_semana' => 4,
            'objetivo' => 'Ganancia de fuerza y masa muscular con entrenamiento de pesas en gimnasio.',
            'nivel' => 'Intermedio',
            'lugar' => 'Gimnasio',
            'semanas' => $semanas,
        ];
    }

    // ─────────────────────────────────────────────────────────────────
    // Entrenamiento fallback — Leidy (5 días, 4 semanas, sustituciones aplicadas)
    // ─────────────────────────────────────────────────────────────────

    /** @return array<string, mixed> */
    private function entrenamientoLeidy(): array
    {
        $diasBase = [
            [
                'nombre' => 'Lunes - Glúteos y Cuádriceps',
                'tipo' => 'piernas',
                'ejercicios' => [
                    ['nombre' => 'Sentadilla con mancuernas',        'series' => 4, 'repeticiones' => '12-15', 'descanso' => '90s', 'notas' => 'Pies a la anchura de hombros, espalda recta.'],
                    ['nombre' => 'Zancadas con mancuernas',          'series' => 3, 'repeticiones' => '12 c/pierna', 'descanso' => '60s', 'notas' => 'Torso erguido, rodilla de atrás cerca del suelo.'],
                    ['nombre' => 'Hip thrust con mancuerna',         'series' => 4, 'repeticiones' => '15', 'descanso' => '60s', 'notas' => 'Hombros en banco o silla, apretar glúteos arriba.'],
                    ['nombre' => 'Abducción sentada con banda',      'series' => 3, 'repeticiones' => '20', 'descanso' => '60s', 'notas' => 'Banda sobre rodillas, empujar hacia afuera activando glúteo medio.'],
                    ['nombre' => 'Sentadilla sumo con mancuerna',    'series' => 3, 'repeticiones' => '15', 'descanso' => '60s', 'notas' => 'Pies abiertos, punta de pies hacia afuera.'],
                ],
            ],
            [
                'nombre' => 'Martes - Hombros y Brazos',
                'tipo' => 'empuje',
                'ejercicios' => [
                    ['nombre' => 'Press de hombro con mancuerna',                  'series' => 4, 'repeticiones' => '10-12', 'descanso' => '90s', 'notas' => 'Sentada o de pie, codos a 90° al bajar.'],
                    ['nombre' => 'Elevación lateral con mancuerna',                'series' => 3, 'repeticiones' => '15', 'descanso' => '60s', 'notas' => 'Codos ligeramente doblados, subir hasta altura de hombros.'],
                    ['nombre' => 'Curl de bíceps con mancuerna',                   'series' => 3, 'repeticiones' => '12', 'descanso' => '60s', 'notas' => 'Codos fijos al cuerpo.'],
                    ['nombre' => 'Extensión de tríceps sobre cabeza con mancuerna', 'series' => 3, 'repeticiones' => '12', 'descanso' => '60s', 'notas' => 'Core activo, codos apuntando al techo.'],
                    ['nombre' => 'Elevación frontal con mancuerna',                'series' => 3, 'repeticiones' => '12', 'descanso' => '60s', 'notas' => 'Hasta altura de hombros, movimiento controlado.'],
                ],
            ],
            [
                'nombre' => 'Miércoles - Isquios y Glúteos Posterior',
                'tipo' => 'posterior',
                'ejercicios' => [
                    ['nombre' => 'Peso muerto con mancuerna',          'series' => 4, 'repeticiones' => '10-12', 'descanso' => '90s', 'notas' => 'Espalda recta, bajar sintiendo estiramiento en isquios.'],
                    ['nombre' => 'Patada de glúteo con banda',         'series' => 3, 'repeticiones' => '15 c/pierna', 'descanso' => '60s', 'notas' => 'En cuadrupedia, apretar glúteo al extender la pierna.'],
                    ['nombre' => 'Puente de glúteo con mancuerna',     'series' => 4, 'repeticiones' => '15', 'descanso' => '60s', 'notas' => 'Mancuerna sobre cadera, elevar hasta alineación cadera-rodillas-hombros.'],
                    ['nombre' => 'Abducción con banda de pie',         'series' => 3, 'repeticiones' => '20 c/lado', 'descanso' => '60s', 'notas' => 'De pie apoyada en pared, llevar pierna al costado.'],
                ],
            ],
            [
                'nombre' => 'Jueves - Espalda y Core',
                'tipo' => 'jalar',
                'ejercicios' => [
                    ['nombre' => 'Remo con mancuernas',            'series' => 4, 'repeticiones' => '10-12', 'descanso' => '90s', 'notas' => 'Torso inclinado, codos hacia el techo.'],
                    ['nombre' => 'Jalón con banda de resistencia', 'series' => 3, 'repeticiones' => '12-15', 'descanso' => '60s', 'notas' => 'Simular jalón al pecho, activar dorsales.'],
                    ['nombre' => 'Elevación lateral con mancuerna', 'series' => 3, 'repeticiones' => '15', 'descanso' => '60s', 'notas' => 'Enfoque en deltoides medial.'],
                    ['nombre' => 'Curl martillo con mancuerna',    'series' => 3, 'repeticiones' => '12', 'descanso' => '60s', 'notas' => 'Agarre neutro, activar braquial.'],
                    ['nombre' => 'Plancha frontal',                'series' => 3, 'repeticiones' => '30-45 seg', 'descanso' => '45s', 'notas' => 'Core activo, cuerpo en línea recta.'],
                ],
            ],
            [
                'nombre' => 'Viernes - Glúteos y Femorales',
                'tipo' => 'posterior',
                'ejercicios' => [
                    ['nombre' => 'Peso muerto con mancuernas',     'series' => 4, 'repeticiones' => '10-12', 'descanso' => '90s', 'notas' => 'Bisagra de cadera, espalda recta.'],
                    ['nombre' => 'Hip thrust con mancuerna',       'series' => 4, 'repeticiones' => '15', 'descanso' => '60s', 'notas' => 'Máxima activación glútea en la cima.'],
                    ['nombre' => 'Sentadilla búlgara con mancuernas', 'series' => 3, 'repeticiones' => '12 c/pierna', 'descanso' => '60s', 'notas' => 'Pie trasero en silla, descender controlado.'],
                    ['nombre' => 'Abducción sentada con banda',    'series' => 3, 'repeticiones' => '20', 'descanso' => '60s', 'notas' => 'Activar glúteo medio.'],
                    ['nombre' => 'Curl femoral con banda acostada', 'series' => 3, 'repeticiones' => '12-15', 'descanso' => '60s', 'notas' => 'Bajada controlada.'],
                ],
            ],
        ];

        $notas = [
            1 => 'Establece la técnica. Elige un peso con el que puedas completar todas las repeticiones con buena forma.',
            2 => 'Aumenta 5-10% el peso o resistencia. Mantén técnica perfecta.',
            3 => 'Semana de volumen. Agrega 1 serie extra a los ejercicios principales.',
            4 => 'Semana de intensidad. Lleva las últimas 2 repeticiones al fallo muscular controlado.',
        ];

        $titulos = [1 => 'Base y Técnica', 2 => 'Aumento de Carga', 3 => 'Volumen', 4 => 'Intensidad'];

        $semanas = [];
        for ($s = 1; $s <= 4; $s++) {
            $dias = $diasBase;
            if ($s === 3) {
                foreach ($dias as &$dia) {
                    foreach ($dia['ejercicios'] as &$ej) {
                        $ej['series'] = $ej['series'] + 1;
                    }
                    unset($ej);
                }
                unset($dia);
            }

            $semanas[] = [
                'semana' => $s,
                'titulo' => $titulos[$s],
                'nota' => "Semana {$s}: {$notas[$s]}",
                'dias' => $dias,
            ];
        }

        return [
            'duracion_semanas' => 4,
            'dias_por_semana' => 5,
            'objetivo' => 'Recomposición corporal con énfasis en glúteos, piernas y tonificación general.',
            'nivel' => 'Principiante-Intermedio',
            'lugar' => 'Casa o gimnasio con mancuernas',
            'semanas' => $semanas,
        ];
    }

    // ─────────────────────────────────────────────────────────────────
    // Entrenamiento fallback — Danna (6 días, 4 semanas, sustituciones aplicadas)
    // ─────────────────────────────────────────────────────────────────

    /** @return array<string, mixed> */
    private function entrenamientoDanna(): array
    {
        $diasBase = [
            [
                'nombre' => 'Lunes - Glúteos y Cuádriceps',
                'tipo' => 'piernas',
                'ejercicios' => [
                    ['nombre' => 'Sentadilla búlgara con mancuernas',  'series' => 4, 'repeticiones' => '10-12 c/pierna', 'descanso' => '90s', 'notas' => 'Pie trasero en banco, descender controlado.'],
                    ['nombre' => 'Prensa de piernas',                  'series' => 4, 'repeticiones' => '12-15', 'descanso' => '90s', 'notas' => 'Pies a la anchura de hombros, rango completo.'],
                    ['nombre' => 'Hip thrust en máquina',              'series' => 4, 'repeticiones' => '15', 'descanso' => '60s', 'notas' => 'Apretar glúteos en la cima, mantener 1 seg.'],
                    ['nombre' => 'Extensión de cuádriceps en máquina', 'series' => 3, 'repeticiones' => '15', 'descanso' => '60s', 'notas' => 'Mantener 2 segundos arriba.'],
                    ['nombre' => 'Zancadas con mancuernas',            'series' => 3, 'repeticiones' => '12 c/pierna', 'descanso' => '60s', 'notas' => 'Torso erguido.'],
                ],
            ],
            [
                'nombre' => 'Martes - Core y Abdomen',
                'tipo' => 'core',
                'ejercicios' => [
                    ['nombre' => 'Crunch abdominal', 'series' => 4, 'repeticiones' => '20', 'descanso' => '45s', 'notas' => 'Exhalar al subir, no jalar del cuello.'],
                    ['nombre' => 'Plancha frontal',  'series' => 4, 'repeticiones' => '45 seg', 'descanso' => '45s', 'notas' => 'Core activo, cuerpo en línea recta.'],
                ],
            ],
            [
                'nombre' => 'Miércoles - Isquios y Glúteos Posterior',
                'tipo' => 'posterior',
                'ejercicios' => [
                    ['nombre' => 'Sentadilla sumo con mancuerna',  'series' => 4, 'repeticiones' => '12-15', 'descanso' => '90s', 'notas' => 'Pies abiertos, punta de pies hacia afuera.'],
                    ['nombre' => 'Peso muerto rumano con barra',   'series' => 4, 'repeticiones' => '10-12', 'descanso' => '90s', 'notas' => 'Espalda recta, bajar sintiendo isquios.'],
                    ['nombre' => 'Curl femoral acostado',          'series' => 3, 'repeticiones' => '12-15', 'descanso' => '60s', 'notas' => 'Bajada controlada.'],
                    ['nombre' => 'Abducción en máquina',           'series' => 4, 'repeticiones' => '20', 'descanso' => '60s', 'notas' => 'Activar glúteo medio.'],
                    ['nombre' => 'Hip thrust en máquina',          'series' => 4, 'repeticiones' => '15', 'descanso' => '60s', 'notas' => 'Máxima activación glútea.'],
                ],
            ],
            [
                'nombre' => 'Jueves - Hombros y Espalda',
                'tipo' => 'empuje-jalar',
                'ejercicios' => [
                    ['nombre' => 'Press de hombro con mancuerna sentada', 'series' => 4, 'repeticiones' => '10-12', 'descanso' => '90s', 'notas' => 'Codos a 90° al bajar.'],
                    ['nombre' => 'Elevaciones laterales con mancuernas',  'series' => 4, 'repeticiones' => '15', 'descanso' => '60s', 'notas' => 'Hasta altura de hombros.'],
                    ['nombre' => 'Jalón al pecho en polea',               'series' => 4, 'repeticiones' => '10-12', 'descanso' => '90s', 'notas' => 'Pecho erguido, tirar hasta la barbilla.'],
                    ['nombre' => 'Remo con mancuerna',                    'series' => 3, 'repeticiones' => '10 c/lado', 'descanso' => '60s', 'notas' => 'Codo hacia el techo.'],
                    ['nombre' => 'Face pulls en polea',                   'series' => 3, 'repeticiones' => '15', 'descanso' => '60s', 'notas' => 'Rotación externa, apretar deltoides posterior.'],
                ],
            ],
            [
                'nombre' => 'Viernes - Glúteos y Femorales',
                'tipo' => 'posterior',
                'ejercicios' => [
                    ['nombre' => 'Hip thrust en máquina',      'series' => 4, 'repeticiones' => '15', 'descanso' => '90s', 'notas' => 'Empuje máximo, apretar glúteos arriba.'],
                    ['nombre' => 'Peso muerto con barra',      'series' => 4, 'repeticiones' => '8-10', 'descanso' => '120s', 'notas' => 'Técnica perfecta, espalda recta.'],
                    ['nombre' => 'Curl femoral sentado',       'series' => 3, 'repeticiones' => '12-15', 'descanso' => '60s', 'notas' => 'Bajada controlada de 3 segundos.'],
                    ['nombre' => 'Cable pull through',         'series' => 3, 'repeticiones' => '15', 'descanso' => '60s', 'notas' => 'Bisagra de cadera, apretar glúteos al finalizar.'],
                    ['nombre' => 'Patada diagonal en polea',   'series' => 3, 'repeticiones' => '15 c/pierna', 'descanso' => '60s', 'notas' => 'Apretar glúteo en extensión.'],
                ],
            ],
            [
                'nombre' => 'Sábado - Full Body y Movilidad',
                'tipo' => 'full-body',
                'ejercicios' => [
                    ['nombre' => 'Avanzadas con mancuernas',             'series' => 3, 'repeticiones' => '12 c/pierna', 'descanso' => '60s', 'notas' => 'Paso largo, core activo.'],
                    ['nombre' => 'Press de banca con mancuernas',        'series' => 3, 'repeticiones' => '12', 'descanso' => '60s', 'notas' => 'Rango completo de movimiento.'],
                    ['nombre' => 'Sentadilla goblet con mancuerna',      'series' => 3, 'repeticiones' => '15', 'descanso' => '60s', 'notas' => 'Mancuerna al pecho, talones en el suelo.'],
                    ['nombre' => 'Curl de bíceps con mancuerna',         'series' => 3, 'repeticiones' => '12', 'descanso' => '60s', 'notas' => 'Codos fijos al cuerpo.'],
                    ['nombre' => 'Plancha frontal',                      'series' => 3, 'repeticiones' => '45 seg', 'descanso' => '45s', 'notas' => 'Finalizar con core.'],
                ],
            ],
        ];

        $notas = [
            1 => 'Establece la técnica. Elige un peso con el que puedas completar todas las repeticiones con buena forma.',
            2 => 'Aumenta 5-10% el peso en ejercicios principales. Mantén técnica perfecta.',
            3 => 'Semana de volumen. Agrega 1 serie extra a los ejercicios compuestos principales.',
            4 => 'Semana de intensidad. Lleva las últimas 2 repeticiones al fallo muscular controlado.',
        ];

        $titulos = [1 => 'Base y Técnica', 2 => 'Aumento de Carga', 3 => 'Volumen', 4 => 'Intensidad'];

        $semanas = [];
        for ($s = 1; $s <= 4; $s++) {
            $dias = $diasBase;
            if ($s === 3) {
                foreach ($dias as &$dia) {
                    foreach ($dia['ejercicios'] as &$ej) {
                        $ej['series'] = $ej['series'] + 1;
                    }
                    unset($ej);
                }
                unset($dia);
            }

            $semanas[] = [
                'semana' => $s,
                'titulo' => $titulos[$s],
                'nota' => "Semana {$s}: {$notas[$s]}",
                'dias' => $dias,
            ];
        }

        return [
            'duracion_semanas' => 4,
            'dias_por_semana' => 6,
            'objetivo' => 'Recomposición corporal: reducción de grasa y tonificación muscular con énfasis en glúteos y piernas.',
            'nivel' => 'Intermedio',
            'lugar' => 'Gimnasio',
            'semanas' => $semanas,
        ];
    }
}
