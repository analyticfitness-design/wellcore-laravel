<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class LoadSilviaPlan extends Command
{
    protected $signature = 'wellcore:load-silvia-plan';
    protected $description = 'Carga plan de entrenamiento nuevo para Silvia Gomez Roa (client_id=54) con estructura semanas[] y GIFs confirmados';

    public function handle(): int
    {
        $clientId = 54;

        // ── 1. Desactivar planes de entrenamiento duplicados ──────────────────
        DB::table('assigned_plans')
            ->where('client_id', $clientId)
            ->where('plan_type', 'entrenamiento')
            ->where('active', 1)
            ->update(['active' => 0]);

        $this->info('Planes de entrenamiento anteriores desactivados.');

        // ── 2. Construir los 5 días de entrenamiento ──────────────────────────
        $dias = [
            [
                'nombre' => 'Lunes — Glúteo A (Carga Pesada)',
                'ejercicios' => [
                    [
                        'nombre'        => 'Hip thrust con barra en banco',
                        'series'        => 5,
                        'repeticiones'  => '5-8',
                        'descanso'      => '2.5 min',
                        'notas'         => 'Serie estrella de la semana. Escápulas sobre el banco al nivel del esternón, pies al ancho de cadera. Empuja con los talones, contrae glúteo al máximo en la cima y sostén 1 seg. Anota el peso cada semana — aquí va la progresión principal.',
                    ],
                    [
                        'nombre'        => 'Peso muerto rumano con barra',
                        'series'        => 4,
                        'repeticiones'  => '8-10',
                        'descanso'      => '2 min',
                        'notas'         => 'Barra pegada al cuerpo, espalda neutra, empuje de cadera hacia atrás hasta sentir estiramiento profundo en el isquiotibial. No es sentadilla — es bisagra pura.',
                    ],
                    [
                        'nombre'        => 'Abductor en máquina sentado',
                        'series'        => 4,
                        'repeticiones'  => '12-15',
                        'descanso'      => '90 seg',
                        'notas'         => 'Inclínate 10-15° hacia adelante para activar glúteo medio en lugar del TFL. Excéntrico controlado de 3 seg. Cuando alcances 15 reps con facilidad, sube peso.',
                    ],
                    [
                        'nombre'        => 'Curl femoral acostado en máquina',
                        'series'        => 4,
                        'repeticiones'  => '10-12',
                        'descanso'      => '90 seg',
                        'notas'         => 'Caderas pegadas al banco. Concéntrico explosivo, excéntrico lento 3-4 seg. No despegues las caderas durante el movimiento.',
                    ],
                    [
                        'nombre'        => 'Good morning con barra',
                        'series'        => 3,
                        'repeticiones'  => '10-12',
                        'descanso'      => '90 seg',
                        'notas'         => 'Barra en espalda alta. Pies al ancho de caderas, rodillas ligeramente flexionadas. Bisagra profunda con espalda completamente neutra. Excelente para glúteo en rango elongado.',
                    ],
                ],
            ],
            [
                'nombre' => 'Martes — Hombros, Tríceps y Core',
                'ejercicios' => [
                    [
                        'nombre'        => 'Press militar con barra de pie',
                        'series'        => 5,
                        'repeticiones'  => '5-7',
                        'descanso'      => '2.5 min',
                        'notas'         => 'Agarre ligeramente más ancho que hombros. Barra baja hasta la barbilla, empuja directo arriba. Core tensionado. Movimiento compuesto principal de la sesión — carga máxima aquí.',
                    ],
                    [
                        'nombre'        => 'Press Arnold con mancuernas',
                        'series'        => 4,
                        'repeticiones'  => '8-10',
                        'descanso'      => '2 min',
                        'notas'         => 'Empieza con mancuernas frente al pecho con palmas hacia ti, rota mientras subes hasta terminar con palmas al frente. Trabaja deltoides anterior y medial con mayor rango que el press militar.',
                    ],
                    [
                        'nombre'        => 'Elevaciones laterales con mancuernas',
                        'series'        => 4,
                        'repeticiones'  => '12-15',
                        'descanso'      => '90 seg',
                        'notas'         => 'Ligera inclinación del tronco 10-15°. Codo ligeramente doblado, sube hasta línea de hombros. Excéntrico 3 seg. El deltoides medial determina el ancho visual del hombro — este volumen es clave.',
                    ],
                    [
                        'nombre'        => 'Face pulls en polea alta con cuerda',
                        'series'        => 3,
                        'repeticiones'  => '15-20',
                        'descanso'      => '60 seg',
                        'notas'         => 'Polea a la altura de los ojos. Jala la cuerda hacia la cara separando los extremos, codos por encima de los hombros. Trabaja deltoides posterior y manguito rotador. Fundamental para salud del hombro.',
                    ],
                    [
                        'nombre'        => 'Extensión de tríceps en polea alta con cuerda',
                        'series'        => 4,
                        'repeticiones'  => '10-12',
                        'descanso'      => '90 seg',
                        'notas'         => 'Codos pegados al cuerpo y fijos. Extiende completamente. Excéntrico controlado 2-3 seg.',
                    ],
                    [
                        'nombre'        => 'Cable crunch en polea alta con cuerda',
                        'series'        => 3,
                        'repeticiones'  => '12-15',
                        'descanso'      => '60 seg',
                        'notas'         => 'Arrodillada, cuerda detrás de la cabeza. Flexiona el tronco hacia abajo contrayendo el abdomen. No jales con los brazos — el movimiento es del core.',
                    ],
                ],
            ],
            [
                'nombre' => 'Miércoles — Espalda y Bíceps',
                'ejercicios' => [
                    [
                        'nombre'        => 'Jalón al pecho en polea alta',
                        'series'        => 4,
                        'repeticiones'  => '8-10',
                        'descanso'      => '2 min',
                        'notas'         => 'Agarre prono ancho. Jala hacia el pecho superior, codos hacia abajo y atrás. Pecho elevado durante todo el movimiento. Excéntrico controlado 2-3 seg.',
                    ],
                    [
                        'nombre'        => 'Remo con barra',
                        'series'        => 4,
                        'repeticiones'  => '8-10',
                        'descanso'      => '2 min',
                        'notas'         => 'Torso a 45-60° del suelo, barra hacia el ombligo. Retrae las escápulas antes de jalar. Excelente para densidad y grosor de espalda media.',
                    ],
                    [
                        'nombre'        => 'Remo con mancuerna a una mano en banco',
                        'series'        => 3,
                        'repeticiones'  => '10-12',
                        'descanso'      => '90 seg',
                        'notas'         => 'Rodilla y mano del mismo lado en el banco. Jala el codo hacia atrás y arriba, no hacia el lado. Contracción máxima en la cima 1 seg.',
                    ],
                    [
                        'nombre'        => 'Face pulls en polea alta con cuerda',
                        'series'        => 3,
                        'repeticiones'  => '15',
                        'descanso'      => '60 seg',
                        'notas'         => 'Complemento de salud articular. Misma técnica que el martes. Mantener el volumen posterior del hombro balanceado con el trabajo anterior.',
                    ],
                    [
                        'nombre'        => 'Curl con barra EZ de pie',
                        'series'        => 3,
                        'repeticiones'  => '10-12',
                        'descanso'      => '90 seg',
                        'notas'         => 'Codos pegados al cuerpo y fijos. Sube controlado, baja con excéntrico de 3 seg. La barra EZ reduce estrés en la muñeca vs barra recta.',
                    ],
                    [
                        'nombre'        => 'Curl con mancuernas alterno',
                        'series'        => 3,
                        'repeticiones'  => '10-12',
                        'descanso'      => '90 seg',
                        'notas'         => 'Supina la muñeca al subir para activar el bíceps completamente. Alterna brazos sin balancear el torso.',
                    ],
                ],
            ],
            [
                'nombre' => 'Jueves — Glúteo B (Volumen y Femoral)',
                'ejercicios' => [
                    [
                        'nombre'        => 'Sentadilla con barra libre',
                        'series'        => 4,
                        'repeticiones'  => '8-10',
                        'descanso'      => '2 min',
                        'notas'         => 'Barra en posición alta o media. Pies al ancho de hombros o ligeramente más, rodillas siguen la dirección de los pies. Profundidad mínima hasta paralela. Trabajo complementario de cuádriceps y glúteo.',
                    ],
                    [
                        'nombre'        => 'Prensa de piernas 45°',
                        'series'        => 4,
                        'repeticiones'  => '10-12',
                        'descanso'      => '2 min',
                        'notas'         => 'Pies altos en la plataforma para mayor activación de glúteo. No bloquees las rodillas al extender. Rango completo de movimiento.',
                    ],
                    [
                        'nombre'        => 'Zancada con mancuernas',
                        'series'        => 3,
                        'repeticiones'  => '10-12 por pierna',
                        'descanso'      => '90 seg',
                        'notas'         => 'Paso largo hacia adelante, rodilla trasera casi toca el suelo. Empuja con el talón de la pierna delantera para volver. Mantén el torso erguido.',
                    ],
                    [
                        'nombre'        => 'Curl femoral sentado en máquina',
                        'series'        => 4,
                        'repeticiones'  => '10-12',
                        'descanso'      => '90 seg',
                        'notas'         => 'Trabaja el isquiotibial en posición sentada (elongado en la cadera), diferente estímulo al curl acostado del lunes. Excéntrico lento 3 seg.',
                    ],
                    [
                        'nombre'        => 'Abductor en máquina sentado',
                        'series'        => 3,
                        'repeticiones'  => '15-20',
                        'descanso'      => '60 seg',
                        'notas'         => 'Segunda sesión semanal de abductor — esta vez con más reps y tiempo bajo tensión. Finalizador metabólico del glúteo medio.',
                    ],
                    [
                        'nombre'        => 'Extensión de cuádriceps en máquina',
                        'series'        => 3,
                        'repeticiones'  => '12-15',
                        'descanso'      => '60 seg',
                        'notas'         => 'Finalizador para cuádriceps. Contracción isométrica 1 seg en la cima. Excéntrico controlado 3 seg.',
                    ],
                ],
            ],
            [
                'nombre' => 'Viernes — Pecho, Hombros y Core',
                'ejercicios' => [
                    [
                        'nombre'        => 'Press de banca con barra',
                        'series'        => 4,
                        'repeticiones'  => '8-10',
                        'descanso'      => '2 min',
                        'notas'         => 'Agarre al ancho de hombros o ligeramente más. Barra baja hasta el pecho con control, codos a 45-75°. Pies en el suelo, glúteo en el banco.',
                    ],
                    [
                        'nombre'        => 'Press inclinado con barra',
                        'series'        => 3,
                        'repeticiones'  => '8-10',
                        'descanso'      => '2 min',
                        'notas'         => 'Banco a 30-45°. Enfatiza la porción clavicular del pectoral. Complemento del press plano para desarrollo completo de pecho superior.',
                    ],
                    [
                        'nombre'        => 'Elevaciones laterales con mancuernas',
                        'series'        => 4,
                        'repeticiones'  => '12-15',
                        'descanso'      => '90 seg',
                        'notas'         => 'Segunda sesión semanal de laterales. Misma técnica: inclinación leve, excéntrico 3 seg. El volumen acumulado de dos sesiones es clave para desarrollar el ancho del hombro.',
                    ],
                    [
                        'nombre'        => 'Pájaros con mancuernas',
                        'series'        => 3,
                        'repeticiones'  => '12-15',
                        'descanso'      => '90 seg',
                        'notas'         => 'Torso inclinado 45-60°. Codo ligeramente doblado. Sube las mancuernas hacia los lados hasta la línea del hombro. Trabaja deltoides posterior — esencial para el "ancho completo" del hombro.',
                    ],
                    [
                        'nombre'        => 'Plancha isométrica',
                        'series'        => 3,
                        'repeticiones'  => '45-60 seg',
                        'descanso'      => '60 seg',
                        'notas'         => 'Codos debajo de los hombros, cadera neutra (no elevar ni hundir). Aprieta glúteos y abdomen durante toda la duración. Cierre de semana para estabilidad del core.',
                    ],
                    [
                        'nombre'        => 'Cable crunch en polea alta con cuerda',
                        'series'        => 3,
                        'repeticiones'  => '12-15',
                        'descanso'      => '60 seg',
                        'notas'         => 'Segunda sesión semanal de crunch en polea. Mantener calidad de ejecución — no es la cantidad sino la contracción consciente del abdomen.',
                    ],
                ],
            ],
        ];

        // ── 3. Construir 4 semanas con progresión RIR ─────────────────────────
        $rirNotes = [
            1 => 'Semana 1 — RIR 3: Semana de calibración. Registra los pesos exactos en todos los ejercicios. Deja 3 repeticiones en el tanque en cada serie. Esta semana establece la base para las progresiones.',
            2 => 'Semana 2 — RIR 2: Acumulación. Sube 2.5-5 kg en compuestos (hip thrust, peso muerto, sentadilla, press) y 1-2 kg en aislamiento si la técnica lo permite. Quedan 2 reps de margen.',
            3 => 'Semana 3 — RIR 1: Intensificación. Vuelve a subir carga. Solo queda 1 repetición en el tanque al final de cada serie. Semana más exigente — espera fatiga alta en glúteo y cuádriceps.',
            4 => 'Semana 4 — Deload: Reduce la carga al 60% del peso de semana 3. Mismos ejercicios, mismas series, mitad del esfuerzo. El cuerpo se adapta y consolida las ganancias durante la recuperación activa.',
        ];

        $semanas = [];
        for ($s = 1; $s <= 4; $s++) {
            $semanas[] = [
                'semana'      => $s,
                'descripcion' => $rirNotes[$s],
                'dias'        => $dias,
            ];
        }

        $content = [
            'objetivo_principal' => 'Hipertrofia con prioridad en glúteo y volumen de hombros',
            'duracion_semanas'   => 4,
            'dias_por_semana'    => 5,
            'progresion'         => 'RIR 3→2→1→Deload. Sube peso cuando completas todas las reps con RIR mayor al objetivo.',
            'notas_coach'        => 'Plan diseñado para mujer con 10+ años de experiencia. 90 minutos por sesión. Registra cargas semanalmente. GIFs disponibles en cada ejercicio para verificar técnica.',
            'semanas'            => $semanas,
        ];

        // ── 4. Insertar nuevo plan ────────────────────────────────────────────
        $newId = DB::table('assigned_plans')->insertGetId([
            'client_id' => $clientId,
            'plan_type' => 'entrenamiento',
            'content'   => json_encode($content, JSON_UNESCAPED_UNICODE),
            'active'    => 1,
        ]);

        $this->info("Nuevo plan entrenamiento creado con id={$newId}");
        $this->info('Días: ' . count($dias));
        $this->info('Semanas: 4 (RIR 3→2→1→Deload)');

        foreach ($dias as $d) {
            $this->line("  • {$d['nombre']}: " . count($d['ejercicios']) . ' ejercicios');
        }

        return self::SUCCESS;
    }
}
