<?php

declare(strict_types=1);

namespace Database\SeedersKb;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 15 principios iniciales de coaching reutilizables.
 * COMPOSE stage los inyecta en notas_coach, tips[], notas de ejercicio.
 *
 * Distribución MVP:
 *   - 7 entrenamiento
 *   - 4 nutrición
 *   - 1 suplementación
 *   - 3 hábitos
 *
 * Ver docs/wellcore-engine-v2/03-knowledge-base-schema.md §3.4
 * Idempotente por slug.
 */
final class PrinciplesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now()->toDateTimeString();

        $rows = [
            // ─── ENTRENAMIENTO (7) ─────────────────────────────────────────────
            [
                'slug' => 'sobrecarga_progresiva',
                'name' => 'Sobrecarga progresiva',
                'vertical' => 'entrenamiento',
                'description_short' => 'Aumentar progresivamente la demanda (peso, reps, tempo, densidad) para forzar adaptación.',
                'description_long' => "La sobrecarga progresiva es el motor de cualquier ganancia muscular o de fuerza. No basta con entrenar duro — hay que entrenar progresivamente más duro semana a semana.\n\nVariables progresables (en orden de prioridad para hipertrofia): 1) número de series por grupo muscular, 2) reps por serie, 3) carga, 4) tiempo bajo tensión, 5) densidad (reducir descanso).\n\nNo todas las semanas se progresa todo. Lo común es subir 1 variable a la vez y consolidar.",
                'when_to_apply' => 'En todos los planes de entrenamiento. Especialmente importante mencionar en semana 2 cuando el cliente empieza a calibrar y entender que el plan crece con él.',
                'example_usage' => 'Notas del coach semana 2: "Si la semana 1 completaste todas las series con la técnica perfecta, esta semana subí 2.5-5kg en los compuestos. Si te quedaste corto, repetí pesos pero ajustá la técnica primero."',
                'tags' => json_encode(['hipertrofia', 'fuerza', 'fundamental']),
                'status' => 'active',
                'created_by' => 'seed-mvp-sprint-0',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'tecnica_primero',
                'name' => 'Técnica antes que carga',
                'vertical' => 'entrenamiento',
                'description_short' => 'Ningún peso vale más que la articulación. Si la técnica se rompe, bajar peso o terminar la serie.',
                'description_long' => "La técnica precede a la carga. Levantar mucho con técnica mala acumula daño articular invisible que aparece años después (hombro, lumbar, rodilla).\n\nReglas operativas: 1) calentamiento específico con barra vacía o pesos muy bajos para repasar patrón, 2) si en una serie de trabajo la última rep pierde rango o forma, NO contar reps adicionales, 3) ante duda de técnica, grabar video y revisar.",
                'when_to_apply' => 'Principiantes siempre. Intermedios cuando empiezan ejercicio nuevo. Avanzados en deload o post-lesión.',
                'example_usage' => 'Notas del coach: "El primer mes no me interesa cuánto cargás. Me interesa que la sentadilla baje hasta romper paralela con la espalda en neutro. El peso viene después."',
                'tags' => json_encode(['fundamental', 'principiante', 'prevencion_lesiones']),
                'status' => 'active',
                'created_by' => 'seed-mvp-sprint-0',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'variacion_estimulos',
                'name' => 'Variación de estímulos sin caos',
                'vertical' => 'entrenamiento',
                'description_short' => 'Rotar metodologías (series rectas, drop, superset) cada 4-6 semanas para evitar estancamiento sin perder progresión.',
                'description_long' => "El músculo se adapta al estímulo repetido. Después de 4-6 semanas con la misma metodología, las ganancias se reducen.\n\nVariar NO significa cambiar de plan cada semana. Significa rotar técnicas avanzadas (drop sets, supersets, pirámides) en los ejercicios accesorios, manteniendo los compuestos principales con series rectas y progresión lineal.",
                'when_to_apply' => 'Intermedios+ después de 4 semanas en el mismo bloque. Para principiantes NO aplica — ellos progresan con series rectas largas (3-6 meses).',
                'example_usage' => 'Bloque 2 semana 1: "Esta semana introducimos un drop set al final del press de banca. Es la única técnica avanzada del día — no la metas en los demás ejercicios."',
                'tags' => json_encode(['hipertrofia', 'intermedio', 'avanzado']),
                'status' => 'active',
                'created_by' => 'seed-mvp-sprint-0',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'recuperacion_es_entrenamiento',
                'name' => 'La recuperación es parte del entrenamiento',
                'vertical' => 'entrenamiento',
                'description_short' => 'El músculo crece entre sesiones, no durante. Sueño + nutrición + descanso entre series son tan importantes como el ejercicio.',
                'description_long' => "El estímulo de entrenamiento daña fibras musculares. La adaptación (hipertrofia, fuerza) ocurre en la recuperación. Si no se recupera, no se progresa.\n\nFactores de recuperación: 1) sueño 7-9h consistentes, 2) proteína distribuida a lo largo del día, 3) descanso entre series suficiente para mantener carga (90-180s compuestos), 4) descanso entre sesiones del mismo grupo (48-72h).",
                'when_to_apply' => 'Cuando el cliente reporta fatiga acumulada, dolor articular persistente, o pérdida de fuerza inesperada. Recomendar deload antes que insistir.',
                'example_usage' => 'Si el cliente dice "estoy cansado pero quiero entrenar igual": "Te entiendo. Hagamos esto: bajá 30% el volumen hoy. Si mañana te sentís bien, retomamos. Forzar sobre fatiga te lleva a lesión."',
                'tags' => json_encode(['fundamental', 'recuperacion', 'prevencion_lesiones']),
                'status' => 'active',
                'created_by' => 'seed-mvp-sprint-0',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'consistencia_sobre_intensidad',
                'name' => 'Consistencia gana a intensidad',
                'vertical' => 'entrenamiento',
                'description_short' => '4 entrenos por semana durante 12 semanas vencen a 7 entrenos perfectos durante 3 semanas + 9 semanas perdidas.',
                'description_long' => "La adaptación muscular y de fuerza requiere ESTÍMULO REPETIDO en el tiempo. Un cliente que entrena 4 días consistentes durante 6 meses obtiene mejores resultados que uno que entrena 6 días extremos durante 3 semanas y se quema.\n\nMejor adaptar el plan a la vida real del cliente que pretender que la vida se adapte al plan ideal.",
                'when_to_apply' => 'En la primera sesión / intake. Cuando un cliente con vida ocupada pide planes muy ambiciosos. Cuando reaparece tras una pausa.',
                'example_usage' => 'En notas_coach iniciales: "El plan está diseñado para 4 días — sostener 4 días durante 12 semanas seguidas vale más que cualquier intento de hacer 6 días los primeros 15 y desaparecer."',
                'tags' => json_encode(['fundamental', 'adherencia', 'realismo']),
                'status' => 'active',
                'created_by' => 'seed-mvp-sprint-0',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'deload_planificado',
                'name' => 'Deload planificado cada 4-6 semanas',
                'vertical' => 'entrenamiento',
                'description_short' => 'Semana ligera con -30% volumen y -70% intensidad cada 4-6 semanas previene overtraining y consolida ganancias.',
                'description_long' => "El deload no es debilidad ni vagancia — es necesidad biológica. Cada 4-6 semanas de carga progresiva, una semana de reducción permite que el sistema nervioso, articulaciones y tejido conectivo se actualicen al nuevo nivel.\n\nProtocolo estándar: mantener los ejercicios y la frecuencia, reducir volumen a 60-70% y carga a 70-80% del trabajo. Sensación: salir del gym 'queriendo entrenar más'.",
                'when_to_apply' => 'En todo bloque de 4+ semanas. Especialmente importante mencionarlo en el plan antes de la semana 4 para que el cliente no piense que es regresión.',
                'example_usage' => 'Notas semana 4 (peak): "La próxima semana es DELOAD — vas a entrenar igual, pero con menos peso y menos series. No es vagancia, es ciencia. Salí del gym con energía. Después arrancamos bloque nuevo."',
                'tags' => json_encode(['intermedio', 'avanzado', 'recuperacion']),
                'status' => 'active',
                'created_by' => 'seed-mvp-sprint-0',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'carga_progresiva_post_lesion',
                'name' => 'Carga progresiva post-lesión',
                'vertical' => 'entrenamiento',
                'description_short' => 'Volver del 0%. Empezar con 40-50% del peso anterior y subir 5-10% por semana solo si no hay dolor.',
                'description_long' => "Post-lesión (incluso lesiones menores como tendinitis o esguinces grado 1), el músculo se atrofia y la propiocepción se altera. Volver con el peso de antes garantiza recaída.\n\nProtocolo: 1) confirmar alta médica si hubo lesión moderada+, 2) primera semana 40-50% del peso pre-lesión, 3) +5-10% semanal solo si NO hay dolor durante ni después, 4) si reaparece dolor, parar y bajar.",
                'when_to_apply' => 'Intake del cliente menciona lesión activa o reciente (<6 meses). Sustituir ejercicios contraindicados por alternativas.',
                'example_usage' => 'Notas del ejercicio donde aplica: "Por el hombro que te molestaba, en lugar de press militar parado vamos con press sentado en máquina. Arrancamos en 60% de tu peso usual. Subimos cuando lleves 2 semanas sin molestia."',
                'tags' => json_encode(['lesion', 'rehabilitacion', 'prevencion']),
                'status' => 'active',
                'created_by' => 'seed-mvp-sprint-0',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ─── NUTRICIÓN (4) ─────────────────────────────────────────────────
            [
                'slug' => 'proteina_primero',
                'name' => 'Proteína primero',
                'vertical' => 'nutricion',
                'description_short' => 'Si no llegás a las calorías del día pero cumpliste tu proteína, el día sigue siendo productivo.',
                'description_long' => "La proteína es el macronutriente más crítico para preservar (y construir) músculo. Distribución típica: 1.8-2.4 g/kg de peso corporal para personas entrenando con peso.\n\nPrioridad operativa: si el día se complicó y solo podés controlar UNA cosa, asegurate de cumplir tu objetivo de proteína. Los carbos y grasas tienen más flexibilidad.",
                'when_to_apply' => 'En todo plan nutricional. Especialmente importante para clientes en déficit (perdida_grasa) o que vienen de dietas muy bajas en proteína.',
                'example_usage' => 'tips_nutricionales: "Si te quedaste corto de calorías pero cumpliste tus 180g de proteína, el día sigue contando. La proteína es la única no-negociable."',
                'tags' => json_encode(['fundamental', 'macros']),
                'status' => 'active',
                'created_by' => 'seed-mvp-sprint-0',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'distribucion_de_proteina',
                'name' => 'Distribución de proteína en 4-5 comidas',
                'vertical' => 'nutricion',
                'description_short' => 'Distribuir la proteína en 4-5 comidas con 30-40g cada una maximiza síntesis proteica vs concentrarla en 1-2 comidas grandes.',
                'description_long' => "La síntesis proteica muscular tiene un techo por comida (~0.4 g/kg). Comer 100g de proteína en 1 comida no es 2.5× más anabólico que 40g — el excedente se metaboliza para energía o se almacena.\n\nDistribución óptima: 4-5 comidas con 30-45g de proteína cada una, espaciadas 3-4h. Incluye fuentes variadas: animal completa (huevo, pollo, pescado, lácteos) + vegetal complementaria.",
                'when_to_apply' => 'Al armar el meal plan. Si el cliente prefiere ayuno intermitente, ajustar a 3 comidas más grandes pero no menos.',
                'example_usage' => 'Cada comida del plan tiene su objetivo de proteína específico (ej. desayuno 30g, almuerzo 40g, pre-entreno 25g, cena 40g, snack PM 25g = 160g).',
                'tags' => json_encode(['macros', 'timing']),
                'status' => 'active',
                'created_by' => 'seed-mvp-sprint-0',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'hidratacion_minima',
                'name' => 'Hidratación mínima 35 ml/kg/día',
                'vertical' => 'nutricion',
                'description_short' => 'Mínimo 35 ml de agua por kg de peso corporal al día, +500 ml por hora de entrenamiento intenso.',
                'description_long' => "La deshidratación leve (-2% del peso corporal en líquidos) reduce fuerza, resistencia y enfoque cognitivo significativamente. La sed NO es indicador temprano — cuando sentís sed ya estás deshidratado.\n\nCálculo: peso × 0.035 = L base diarios. Más 0.5L por cada hora de entrenamiento. En climas cálidos o altitud, sumar 20-30%.",
                'when_to_apply' => 'En todo plan nutricional. Especialmente para clientes en Colombia/LATAM costeros (clima cálido) o ciudades de altura (Bogotá +2600m).',
                'example_usage' => 'Sección hidratación del JSON: "Tu mínimo diario son 3.2 L de agua (90kg × 35ml). Los días que entrenás, sumá 500ml extra durante la sesión y otros 500ml en la hora siguiente."',
                'tags' => json_encode(['fundamental', 'hidratacion']),
                'status' => 'active',
                'created_by' => 'seed-mvp-sprint-0',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'timing_pre_entreno',
                'name' => 'Pre-entreno 60-90 min antes',
                'vertical' => 'nutricion',
                'description_short' => 'Comida pre-entreno con carbo + proteína 60-90 min antes da combustible sin pesadez digestiva.',
                'description_long' => "El pre-entreno ideal es una comida moderada (300-500 kcal) con 30-50g de carbo y 20-30g de proteína, consumida 60-90 min antes del gym. Da glucógeno disponible y aminoácidos circulantes sin causar pesadez.\n\nEjemplos: arroz + pollo + verduras, avena + claras + banano, batata + atún. Evitar grasas altas y fibra excesiva (retrasan digestión).",
                'when_to_apply' => 'Cuando el cliente entrena en horario fijo. Si entrena en ayunas, ajustar (BCAAs o proteína líquida) y mover macros a la primera comida post.',
                'example_usage' => 'Si el cliente entrena 18:00, pre-entreno es 16:30-17:00. Cena fuerte después del entreno (19:30-20:00) con proteína + carbos para recuperación.',
                'tags' => json_encode(['timing', 'rendimiento']),
                'status' => 'active',
                'created_by' => 'seed-mvp-sprint-0',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ─── SUPLEMENTACIÓN (1) ────────────────────────────────────────────
            [
                'slug' => 'creatina_basal',
                'name' => 'Creatina monohidrato 5g/día (basal)',
                'vertical' => 'suplementacion',
                'description_short' => 'El suplemento más estudiado y efectivo que existe. 5g diarios constantes — sin fases de carga ni descansos.',
                'description_long' => "Creatina monohidrato (no HCl, no Kre-Alkalyn — el monohidrato es el que tiene 100+ estudios) aumenta fuerza ~5-10% y volumen muscular ~1-2 kg en intermedios+ tras 4-8 semanas.\n\nProtocolo simple: 5g todos los días (con o sin entreno, con o sin comida). Las fases de carga (20g × 5 días) son innecesarias — el efecto es el mismo al mes 1 con 5g/día. No requiere descansos cíclicos.",
                'when_to_apply' => 'Todo plan de suplementación. Excepción: clientes con problemas renales preexistentes (consultar médico).',
                'example_usage' => 'Lista de suplementos del plan: "Creatina Monohidrato 5g — Cualquier momento del día, todos los días incluyendo descansos. Suplemento basal, no opcional."',
                'tags' => json_encode(['basal', 'evidencia_alta']),
                'status' => 'active',
                'created_by' => 'seed-mvp-sprint-0',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ─── HÁBITOS (3) ────────────────────────────────────────────────────
            [
                'slug' => 'sueno_es_anabolico',
                'name' => 'Sueño es anabólico',
                'vertical' => 'habitos',
                'description_short' => 'Dormir 7-9h consistentes vale más que cualquier suplemento. La GH y la recuperación muscular pico ocurren en sueño profundo.',
                'description_long' => "Las horas de sueño son cuando ocurre la mayoría del trabajo de recuperación muscular: pico de hormona de crecimiento (GH), reparación de fibras, consolidación neurológica del patrón motor entrenado.\n\nDormir 5-6h consistentes anula la mitad de las ganancias de entrenamiento — está documentado en múltiples estudios. Prioridad: horario fijo (mismo ±30 min entre semana y fin de semana), oscuridad total, temperatura fresca, sin pantallas 1h antes.",
                'when_to_apply' => 'En todo plan, especialmente cuando el cliente reporta progreso lento sin causa clara o fatiga sostenida.',
                'example_usage' => 'Pilar del plan de hábitos: "Sueño es no-negociable. 7.5h promedio semanal. Si trabajás de noche, hablalo con el coach y armamos un plan ajustado."',
                'tags' => json_encode(['fundamental', 'recuperacion']),
                'status' => 'active',
                'created_by' => 'seed-mvp-sprint-0',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'registro_es_clave',
                'name' => 'Registro es clave (anotás o no progresás)',
                'vertical' => 'habitos',
                'description_short' => 'Anotar peso, reps y RIR de cada serie es lo que permite progresión consciente vs entrenar de memoria.',
                'description_long' => "Sin registro, no hay sobrecarga progresiva real — solo recuerdo selectivo de la última sesión. El cliente que anota peso, reps y RIR sabe exactamente cuándo subir carga, cuándo está estancado, y cuándo necesita deload.\n\nMétodos: app WellCore (preferido), libreta, notas en celular. Lo importante es CONSISTENCIA. Registrar 80% del tiempo es 100× mejor que registrar 100% del primer mes y nada después.",
                'when_to_apply' => 'En la primera sesión / intake. Si el cliente entrena hace tiempo pero no registra, mencionar como cambio importante.',
                'example_usage' => 'Tip del plan: "Anotá peso, reps y RIR de cada serie en la app WellCore después de cada ejercicio. No al final del día — en el momento."',
                'tags' => json_encode(['fundamental', 'progresion']),
                'status' => 'active',
                'created_by' => 'seed-mvp-sprint-0',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'adherencia_sobre_perfeccion',
                'name' => 'Adherencia 80% sostenida > perfección 100% efímera',
                'vertical' => 'habitos',
                'description_short' => 'Cumplir el plan al 80% durante 12 semanas vence a cumplirlo al 100% durante 2 y abandonar.',
                'description_long' => "El error clásico: plan perfecto en papel, ejecutado al 100% por 2 semanas, abandonado por agotamiento o vida real. Resultado neto: cero ganancias y frustración.\n\nDiseñar planes para 80% de adherencia: días flexibles, opciones de comida realistas para la vida del cliente, margen para imprevistos. El 20% de imperfección es feature, no bug.",
                'when_to_apply' => 'Cuando el cliente reporta culpa por días saltados o comidas off-plan. Recordar el principio antes de que abandone por sentir que ya falló.',
                'example_usage' => 'En notas_coach iniciales: "Vas a tener semanas perfectas y semanas raras. Si cumplís 80% sostenido, ganamos. No me importa la semana perfecta seguida de tres ausentes — me importa la semana de 4/5 sostenida 12 semanas."',
                'tags' => json_encode(['fundamental', 'adherencia', 'realismo']),
                'status' => 'active',
                'created_by' => 'seed-mvp-sprint-0',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::connection('kb')
            ->table('principles')
            ->upsert($rows, ['slug'], [
                'name', 'vertical', 'description_short', 'description_long',
                'when_to_apply', 'example_usage', 'tags',
                'status', 'created_by', 'updated_at',
            ]);

        $this->command?->info('Seeded ' . count($rows) . ' principles.');
    }
}
