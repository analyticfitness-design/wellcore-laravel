<?php
/**
 * One-time script: Actualizar el programa RISE de Luis Eduardo Angarita (client_id=16)
 * con su plan real extraído del plan RISE 30 Días asignado anteriormente.
 *
 * Estructura: 6 días/semana (Lun-Sáb), 4 semanas con progresión RIR.
 * - Lunes   : Piernas (Cuádriceps + Glúteos)
 * - Martes  : Empuje (Pecho + Hombros + Tríceps)
 * - Miércoles: Cardio Zona 2
 * - Jueves  : Jalón (Espalda + Bíceps)
 * - Viernes : Piernas Posterior + Core
 * - Sábado  : Cardio Zona 2 + Movilidad
 * - Domingo : Descanso
 *
 * Run: php storage/app/update_luis_program.php
 */

require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\RiseProgram;

// ─── HELPERS ──────────────────────────────────────────────────────────────────
function diaEntren(string $nombre, string $tipo, string $duracion, array $ejercicios): array
{
    return compact('nombre', 'tipo', 'duracion', 'ejercicios');
}

function ejercicio(string $nombre, int $series, string $repeticiones, string $descanso, string $notas = ''): array
{
    $e = compact('nombre', 'series', 'repeticiones', 'descanso');
    if ($notas !== '') {
        $e['notas'] = $notas;
    }
    return $e;
}

// ─── DÍAS BASE (se repiten cada semana con nota RIR diferente) ─────────────────

function getLunes(string $rir): array
{
    return diaEntren('Lunes — Piernas', 'Piernas', '85-90 min', [
        ejercicio('Back Squat (Sentadilla con barra)', 4, '10-12', '120s',
            "Rodillas alineadas con pies; bajada controlada 2s; mantén el pecho arriba. $rir"),
        ejercicio('Leg Press 45°', 4, '12-15', '90s',
            "Pies a anchura de hombros; no bloquees rodillas en la extensión. $rir"),
        ejercicio('Extensión de Cuádriceps (máquina)', 4, '12-15', '60s',
            "Rango completo; pausa 1s en contracción máxima. $rir"),
        ejercicio('Hip Thrust con Barra', 4, '10-12', '90s',
            "Apoya escápulas en banco; aprieta glúteos al tope; mantén 1s arriba. $rir"),
        ejercicio('Walking Lunges (mancuernas)', 4, '10 reps / pierna', '90s',
            "Pasos amplios; rodilla delantera no pasa la punta del pie. $rir"),
    ]);
}

function getMartes(string $rir): array
{
    return diaEntren('Martes — Empuje', 'Empuje', '85-90 min', [
        ejercicio('Press de Banca con Barra', 4, '10-12', '120s',
            "Omóplatos retraídos y deprimidos; arco lumbar natural; bajada 2s al pecho. $rir"),
        ejercicio('Press Inclinado con Mancuernas', 4, '10-12', '90s',
            "Ángulo 30-45°; codos a 45° del torso; rango completo. $rir"),
        ejercicio('Cable Flyes (cruce de poleas)', 4, '12-15', '60s',
            "Brazos ligeramente flexionados; enfoca la contracción en el centro del pecho. $rir"),
        ejercicio('Press Militar con Barra (de pie)', 4, '10-12', '90s',
            "Core activo; no bloquees codos arriba; trayectoria recta. $rir"),
        ejercicio('Elevaciones Laterales con Mancuernas', 4, '12-15', '60s',
            "Codos ligeramente flexionados; no uses impulso; controla la bajada. $rir"),
        ejercicio('Tríceps en Polea (barra en V)', 4, '12-15', '60s',
            "Codos fijos al costado del cuerpo; extensión completa en cada repetición. $rir"),
    ]);
}

function getMiercoles(): array
{
    return diaEntren('Miércoles — Cardio Zona 2', 'Cardio', '30-35 min', [
        ejercicio('Cardio Zona 2 (cinta, bici o elíptica)', 1, '30 min continuos', '—',
            'FC objetivo: 65-70% de tu FC máxima. Prueba: deberías poder hablar en frases cortas. Si no puedes, baja la intensidad.'),
        ejercicio('Estiramientos finales', 1, '5 min', '—',
            'Psoas, cuádriceps, isquiotibiales y gemelos. 30-40s por posición.'),
    ]);
}

function getJueves(string $rir): array
{
    return diaEntren('Jueves — Jalón', 'Jale', '85-90 min', [
        ejercicio('Jalón al Pecho (polea o máquina)', 4, '10-12', '90s',
            "Agarre ancho, pronado; jala los codos hacia las caderas; pecho ligeramente hacia atrás. $rir"),
        ejercicio('Remo con Barra (Barbell Row)', 4, '10-12', '120s',
            "Torso a 45°; jala hacia el ombligo; retracción escapular completa en cada rep. $rir"),
        ejercicio('Remo Sentado con Polea (Seated Cable Row)', 4, '12-15', '90s',
            "Pecho erguido; no te balancees; codos pegados al torso. $rir"),
        ejercicio('Pullover con Mancuerna', 4, '12-15', '60s',
            "Codo ligeramente flexionado; siente el estiramiento completo del dorsal. $rir"),
        ejercicio('Curl con Barra (Barbell Curl)', 4, '10-12', '60s',
            "Codos fijos; no uses el cuerpo para levantar; bajada controlada 2s. $rir"),
        ejercicio('Curl de Martillo (Hammer Curl)', 4, '12-15', '60s',
            "Agarre neutro; rango completo; alterna brazos o simultáneo. $rir"),
    ]);
}

function getViernes(string $rir): array
{
    return diaEntren('Viernes — Piernas Posterior + Core', 'Piernas', '85-90 min', [
        ejercicio('RDL con Barra (Romanian Deadlift)', 4, '10-12', '120s',
            "Caderas atrás, espalda neutra; siente el estiramiento isquiotibial; baja a media espinilla. $rir"),
        ejercicio('Curl de Isquiotibiales (máquina tumbado)', 4, '12-15', '60s',
            "Contracción completa arriba; baja lento 2s; no rebotes. $rir"),
        ejercicio('Bulgarian Split Squat', 4, '10 reps / pierna', '90s',
            "Pie trasero sobre banco; mantén torso erguido; rodilla trasera casi toca el suelo. $rir"),
        ejercicio('Hip Abduction (máquina o cable)', 4, '15-20', '60s',
            "Activa glúteo medio conscientemente en cada repetición; no uses impulso. $rir"),
        ejercicio('Plank Frontal', 4, '30-40s', '45s',
            'Cuerpo recto de cabeza a pies; no dejes caer caderas ni elevar glúteos.'),
        ejercicio('Cable Crunch (polea alta)', 4, '12-15', '60s',
            'Rodillas en el suelo; encoge el abdomen hasta los muslos; no tires con el cuello.'),
        ejercicio('Knee Raises en barra', 4, '12-15', '60s',
            'Rodillas hacia el pecho; baja controlado sin balanceo; agarre firme en la barra.'),
    ]);
}

function getSabado(): array
{
    return diaEntren('Sábado — Cardio + Movilidad', 'Cardio', '35-40 min', [
        ejercicio('Cardio Zona 2 (caminata rápida, bici o cinta)', 1, '25-30 min', '—',
            'FC objetivo: 65-70%. Ritmo conversacional. Ideal al aire libre para el bienestar mental.'),
        ejercicio('Movilidad de cadera (90/90 + hip flexor stretch)', 1, '5 min', '—',
            '90 segundos por posición en cada lado. Relaja la tensión acumulada de la semana.'),
        ejercicio('Movilidad torácica (rotaciones + gato-vaca)', 1, '3 min', '—',
            '10 repeticiones lentas por movimiento. Mejora la postura y reduce compensaciones en el press.'),
        ejercicio('Foam Roller (piernas y espalda baja)', 1, '5 min', '—',
            'Foco en cuádriceps, IT-band y lumbares. 30s por zona, respiración profunda.'),
    ]);
}

// ─── 4 SEMANAS CON PROGRESIÓN RIR ─────────────────────────────────────────────
$semanas = [
    [
        'numero' => 1,
        'fase'   => 'Semana 1 — Adaptación (RIR 3)',
        'dias'   => [
            getLunes('RIR 3: deja 3 repeticiones en el tanque. Aprende el peso correcto.'),
            getMartes('RIR 3: enfócate en la técnica, no en la carga.'),
            getMiercoles(),
            getJueves('RIR 3: establece la base técnica en todos los jalones.'),
            getViernes('RIR 3: aprende la posición correcta del RDL y el split squat.'),
            getSabado(),
        ],
    ],
    [
        'numero' => 2,
        'fase'   => 'Semana 2 — Progresión (RIR 2)',
        'dias'   => [
            getLunes('RIR 2: sube 2-5 kg en los compuestos si la técnica fue perfecta en S1.'),
            getMartes('RIR 2: más carga, misma técnica. El dolor muscular de S1 ya cedió.'),
            getMiercoles(),
            getJueves('RIR 2: mayor activación del dorsal; más control en el descenso.'),
            getViernes('RIR 2: carga más glúteos e isquiotibiales. Siente la diferencia.'),
            getSabado(),
        ],
    ],
    [
        'numero' => 3,
        'fase'   => 'Semana 3 — Peak (RIR 1)',
        'dias'   => [
            getLunes('RIR 1: máximo esfuerzo. Detente 1 sola repetición antes del fallo. Cargas máximas de este ciclo.'),
            getMartes('RIR 1: empuja al límite. Mantén técnica impecable bajo fatiga.'),
            getMiercoles(),
            getJueves('RIR 1: máxima activación dorsal y bíceps. El entrenamiento más exigente del ciclo.'),
            getViernes('RIR 1: desafía glúteos e isquiotibiales al máximo. Es la semana clave de esta fase.'),
            getSabado(),
        ],
    ],
    [
        'numero' => 4,
        'fase'   => 'Semana 4 — Deload (Recuperación activa)',
        'dias'   => [
            getLunes('Deload RIR 4: reduce el peso un 30-40% respecto a S3. Muévete con fluidez y disfruta el movimiento.'),
            getMartes('Deload RIR 4: recuperación activa; el cuerpo consolida las adaptaciones de las 3 semanas previas.'),
            getMiercoles(),
            getJueves('Deload RIR 4: foco en la conexión músculo-mente. Calidad sobre cantidad.'),
            getViernes('Deload RIR 4: activa sin destruir. Prepara el cuerpo para el próximo ciclo.'),
            getSabado(),
        ],
    ],
];

// ─── PLAN DE ENTRENAMIENTO ─────────────────────────────────────────────────────
$planEntrenamiento = [
    'objetivo'         => 'Pérdida de grasa corporal (~5 kg en 30 días) con máxima preservación de músculo. Entrenamiento de fuerza 4 días/semana + 2 sesiones de cardio Zona 2 para maximizar el gasto calórico sin interferir con la recuperación.',
    'frecuencia'       => '6 días/semana (4 fuerza + 2 cardio)',
    'duracion_semanas' => 4,
    'semanas'          => $semanas,
];

// ─── PLAN DE NUTRICIÓN ─────────────────────────────────────────────────────────
// Basado en el perfil de Luis Eduardo: ~80-85 kg, 27% grasa corporal.
// TDEE estimado ~2500 kcal; déficit moderado de ~500 kcal/día.
// Macros: 160g proteína | 195g carbohidratos | 65g grasas = ~2005 kcal
$planNutricion = [
    'objetivo'         => 'Guía nutricional de muestra — una vista previa de lo que recibirías en el Plan Método con un plan 100% personalizado. Objetivo estimado: ~2000 kcal/día con alta proteína para tu composición corporal.',
    'calorias_diarias' => 2000,
    'proteina_g'       => 160,
    'carbohidratos_g'  => 195,
    'grasas_g'         => 65,
    'tips'             => [
        'Prioriza la proteína: apunta a ~160g diarios distribuidos en 4-5 comidas. La proteína preserva el músculo mientras pierdes grasa.',
        'Hidratación: 2.5-3 litros de agua al día. Empieza cada mañana con un vaso grande antes de desayunar.',
        'Consistencia ante todo: un día imperfecto no arruina el proceso. Vuelve al plan en la siguiente comida.',
        'Quieres un plan nutricional completo con opciones detalladas, seguimiento de macros y ajustes semanales? El Plan Método incluye nutrición personalizada con tu coach.',
    ],
    'comidas_sugeridas' => [
        [
            'nombre'   => 'Desayuno',
            'opciones' => [
                '3-4 huevos revueltos con vegetales + 60g avena cocida + 1 fruta',
            ],
        ],
        [
            'nombre'   => 'Almuerzo',
            'opciones' => [
                '200g proteína magra (pollo, res o tilapia) + 100g arroz o papa + ensalada de vegetales',
            ],
        ],
        [
            'nombre'   => 'Cena',
            'opciones' => [
                '200g proteína magra + vegetales al vapor o a la plancha + 1/2 aguacate',
            ],
        ],
    ],
];

// ─── PLAN DE HÁBITOS ──────────────────────────────────────────────────────────
$planHabitos = [
    [
        'nombre'      => 'Hidratación estratégica',
        'descripcion' => 'Bebe 3-3.5 litros de agua al día. Empieza cada mañana con 500 ml antes del desayuno. Lleva siempre una botella de 1L contigo.',
        'razon'       => 'La deshidratación del 2% reduce el rendimiento físico un 20%. El agua también transporta nutrientes, elimina toxinas metabólicas y es el mejor supresor natural del apetito.',
        'frecuencia'  => 'Todos los días',
    ],
    [
        'nombre'      => 'Sueño: 7 horas mínimo',
        'descripcion' => 'Fija una hora de acostarte y otra de levantarte (sin alarmas los fines de semana si puedes). Apaga pantallas 45 min antes. Temperatura ideal: 18-20°C.',
        'razon'       => 'Estás en 6h actualmente. El 90% de la síntesis proteica y la quema de grasa durante el sueño ocurren en fases 3-4. Agregar 1 hora = menor cortisol crónico = menos grasa visceral acumulada.',
        'frecuencia'  => 'Todas las noches',
    ],
    [
        'nombre'      => '7.000 pasos diarios (NEAT)',
        'descripcion' => 'Usa el podómetro de tu celular. Camina 10 min después de almuerzo y cena. Estaciona más lejos. Usa escaleras. No requiere esfuerzo especial.',
        'razon'       => 'El NEAT (actividad fuera del gym) puede representar hasta el 30% del gasto calórico total. Con 7.000 pasos diarios extras, quemas ~250-350 kcal más sin fatiga percibida — acelera la pérdida de grasa sin tocar el entrenamiento.',
        'frecuencia'  => 'Todos los días',
    ],
    [
        'nombre'      => 'Gestión del estrés activo',
        'descripcion' => 'Practica 5-10 min de respiración diafragmática al despertar (inhala 4s, retén 4s, exhala 6s). Identifica tu principal detonante de estrés y plan una respuesta previa en lugar de reaccionar.',
        'razon'       => 'Tu nivel de estrés es 6/10. El cortisol elevado crónicamente favorece el almacenamiento de grasa abdominal visceral y reduce la testosterona — dos enemigos directos de tu objetivo de recomposición corporal.',
        'frecuencia'  => 'Mañanas (5-10 min)',
    ],
    [
        'nombre'      => 'Meal prep dominical',
        'descripcion' => 'Dedica 1.5-2 horas cada domingo a cocinar proteínas (pollo, huevos, carne), carbohidratos (arroz, papa) y preparar vegetales. Divídelo en contenedores para 3-4 días.',
        'razon'       => 'El principal enemigo de la nutrición es la improvisación. Con comida lista, eliminas las decisiones bajo hambre, reduces el consumo de comida chatarra y garantizas tus 160g de proteína diarios.',
        'frecuencia'  => 'Domingos',
    ],
    [
        'nombre'      => 'Registro de progreso diario',
        'descripcion' => 'Cada mañana en ayunas: pésate y anótalo en el celular. Cada 2 semanas: foto de frente y lateral con la misma iluminación y ropa. Al final del día: ¿cumpliste los macros aproximados?',
        'razon'       => 'Lo que se mide, se gestiona. Las fotos bimensuales revelan cambios que la báscula no muestra (recomposición). El registro diario de macros mantiene la consciencia nutricional sin obsesión.',
        'frecuencia'  => 'Diario (peso) · Bimensual (fotos)',
    ],
];

// ─── BUSCAR EL PROGRAMA RISE ACTIVO DE LUIS EDUARDO ──────────────────────────
$riseProgram = RiseProgram::where('client_id', 16)
    ->whereIn('status', ['active', 'activo'])
    ->first();

if (! $riseProgram) {
    echo "ERROR: No se encontró un programa RISE activo para client_id=16 (Luis Eduardo Angarita).\n";
    exit(1);
}

// Preservar los datos de intake del formulario de inscripción
$existingData = $riseProgram->personalized_program ?? [];
$intake = $existingData['intake'] ?? array_filter($existingData, function ($v, $k) {
    return ! in_array($k, ['plan_entrenamiento', 'plan_nutricion', 'plan_habitos']);
}, ARRAY_FILTER_USE_BOTH);

// ─── GUARDAR ──────────────────────────────────────────────────────────────────
$riseProgram->personalized_program = [
    'plan_entrenamiento' => $planEntrenamiento,
    'plan_nutricion'     => $planNutricion,
    'plan_habitos'       => $planHabitos,
    'intake'             => $intake,
];

$riseProgram->save();

echo "✅ Programa RISE actualizado correctamente.\n";
echo "   Cliente: Luis Eduardo Angarita (client_id=16, rise_program_id={$riseProgram->id})\n";
echo "   Entrenamiento: 4 semanas · 6 días/semana (Lun–Sáb) · Progresión RIR 3→2→1→Deload\n";
echo "   Días de fuerza: Lunes (Piernas) · Martes (Empuje) · Jueves (Jalón) · Viernes (Posterior+Core)\n";
echo "   Días de cardio: Miércoles y Sábado (Zona 2)\n";
echo "   Nutrición: 2000 kcal · 160g proteína · 195g carbos · 65g grasas\n";
echo "   Hábitos: 6 hábitos diseñados para su perfil\n";
