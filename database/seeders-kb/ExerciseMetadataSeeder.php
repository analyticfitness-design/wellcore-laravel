<?php

declare(strict_types=1);

namespace Database\SeedersKb;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 30 ejercicios curados — subset del catálogo de 265 GIFs.
 *
 * Selección: los compuestos esenciales + aislamientos más usados en planes WellCore.
 * El catálogo completo se enriquece progresivamente en Sprints 2+.
 *
 * Distribución:
 *   - 5 pecho · 6 espalda · 8 piernas (cuad/glúteo/fem)
 *   - 3 hombros · 3 brazos
 *   - 2 femoral/cuádriceps específicos · 2 core · 1 cardio
 *
 * Aliases verificados contra el repo público:
 * https://github.com/analyticfitness-design/wellcore-exercise-gifs
 *
 * Idempotente por alias. Ver docs/wellcore-engine-v2/03-knowledge-base-schema.md §3.3
 */
final class ExerciseMetadataSeeder extends Seeder
{
    public function run(): void
    {
        $now = now()->toDateTimeString();
        $exercises = $this->definitions();

        $rows = array_map(function (array $e) use ($now): array {
            return [
                'alias' => $e['alias'],
                'name_canonical' => $e['name'],
                'muscle_primary' => $e['muscle_primary'],
                'muscle_secondary' => $e['muscle_secondary'] ?? null,
                'equipment_required' => json_encode($e['equipment_required'] ?? []),
                'equipment_substitutes' => json_encode($e['equipment_substitutes'] ?? []),
                'level_min' => $e['level_min'] ?? 'principiante',
                'compound_isolation' => $e['compound_isolation'],
                'movement_pattern' => $e['movement_pattern'] ?? null,
                'contraindications' => json_encode($e['contraindications'] ?? []),
                'common_mistakes' => $e['common_mistakes'] ?? null,
                'coaching_cues' => json_encode($e['coaching_cues'] ?? []),
                'variations' => json_encode($e['variations'] ?? []),
                'gif_url_verified_at' => null,
                'gif_url_status' => 'unknown', // se actualiza con `php artisan kb:verify-gifs`
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $exercises);

        DB::connection('kb')
            ->table('exercise_metadata')
            ->upsert($rows, ['alias'], [
                'name_canonical', 'muscle_primary', 'muscle_secondary',
                'equipment_required', 'equipment_substitutes',
                'level_min', 'compound_isolation', 'movement_pattern',
                'contraindications', 'common_mistakes', 'coaching_cues',
                'variations', 'updated_at',
            ]);

        $this->command?->info('Seeded ' . count($rows) . ' exercise metadata entries.');
    }

    /**
     * Definiciones curadas. Cada array literal es el subset de info que el motor v2
     * necesita para sustituir por equipo/lesión y armar notas técnicas.
     */
    private function definitions(): array
    {
        return [
            // ─── PECHO (5) ──────────────────────────────────────────────────────
            [
                'alias' => 'press-banca-barra',
                'name' => 'Press de banca con barra',
                'muscle_primary' => 'Pecho',
                'muscle_secondary' => 'Tríceps, Hombro anterior',
                'equipment_required' => ['barra', 'banco_plano', 'rack'],
                'equipment_substitutes' => ['press-banca-mancuernas', 'press-banca-maquina'],
                'level_min' => 'principiante',
                'compound_isolation' => 'compound',
                'movement_pattern' => 'push_horizontal',
                'contraindications' => ['lesion_hombro_anterior', 'lesion_pectoral'],
                'common_mistakes' => "Levantar las nalgas del banco · Codos abriéndose a 90° (debe ser 45-75°) · Rebotar la barra en el pecho · Rango parcial sin tocar pecho",
                'coaching_cues' => ['Escápulas retraídas y bajas', 'Pies plantados firmes', 'Bajar en 2 segundos', 'Empujar explosivo sin bloquear codos'],
                'variations' => [
                    ['alias' => 'press-banca-mancuernas', 'reason' => 'Mayor rango y libertad articular'],
                    ['alias' => 'press-banca-maquina', 'reason' => 'Más seguro sin spotter'],
                ],
            ],
            [
                'alias' => 'press-banca-mancuernas',
                'name' => 'Press de banca con mancuernas',
                'muscle_primary' => 'Pecho',
                'muscle_secondary' => 'Tríceps, Hombro anterior',
                'equipment_required' => ['mancuernas', 'banco_plano'],
                'equipment_substitutes' => ['press-banca-barra', 'press-banca-maquina', 'flexiones'],
                'level_min' => 'principiante',
                'compound_isolation' => 'compound',
                'movement_pattern' => 'push_horizontal',
                'contraindications' => ['lesion_hombro_anterior'],
                'common_mistakes' => "Mancuernas se chocan arriba (perdida tensión) · Bajada descontrolada · Codos pegados al torso",
                'coaching_cues' => ['Mancuernas a la altura del pecho', 'Codos 45° del torso', 'Activar pecho antes de empujar'],
                'variations' => [
                    ['alias' => 'press-banca-barra', 'reason' => 'Carga mayor concentrada'],
                    ['alias' => 'press-inclinado-mancuernas', 'reason' => 'Énfasis en pecho superior'],
                ],
            ],
            [
                'alias' => 'press-banca-maquina',
                'name' => 'Press de banca en máquina',
                'muscle_primary' => 'Pecho',
                'muscle_secondary' => 'Tríceps',
                'equipment_required' => ['maquina_press_horizontal'],
                'equipment_substitutes' => ['press-banca-barra', 'press-banca-mancuernas'],
                'level_min' => 'principiante',
                'compound_isolation' => 'compound',
                'movement_pattern' => 'push_horizontal',
                'contraindications' => [],
                'common_mistakes' => "Asiento mal ajustado (manijas no a altura de pecho) · Codos hiperextendidos arriba · Empuje con piernas",
                'coaching_cues' => ['Manijas a altura de pecho medio', 'Espalda apoyada totalmente', 'Empujar parejo con ambos brazos'],
                'variations' => [
                    ['alias' => 'press-banca-mancuernas', 'reason' => 'Más libertad de plano de movimiento'],
                ],
            ],
            [
                'alias' => 'press-inclinado-mancuernas',
                'name' => 'Press inclinado con mancuernas',
                'muscle_primary' => 'Pecho',
                'muscle_secondary' => 'Hombro anterior, Tríceps',
                'equipment_required' => ['mancuernas', 'banco_inclinado'],
                'equipment_substitutes' => ['press-banca-mancuernas'],
                'level_min' => 'principiante',
                'compound_isolation' => 'compound',
                'movement_pattern' => 'push_horizontal',
                'contraindications' => ['lesion_hombro_anterior'],
                'common_mistakes' => "Inclinación demasiado alta (>45° hace press de hombro) · Cargar mucho hombro al inicio",
                'coaching_cues' => ['Banco 30-45°', 'Mancuernas paralelas al torso', 'Pecho alto, escápulas retraídas'],
                'variations' => [
                    ['alias' => 'press-banca-mancuernas', 'reason' => 'Pecho medio'],
                ],
            ],
            [
                'alias' => 'fondos-paralelas',
                'name' => 'Fondos en paralelas',
                'muscle_primary' => 'Pecho',
                'muscle_secondary' => 'Tríceps, Hombro anterior',
                'equipment_required' => ['paralelas_o_dip_bar'],
                'equipment_substitutes' => ['fondos-banco', 'press-banca-mancuernas'],
                'level_min' => 'intermedio',
                'compound_isolation' => 'compound',
                'movement_pattern' => 'push_vertical',
                'contraindications' => ['lesion_hombro_anterior', 'inestabilidad_hombro'],
                'common_mistakes' => "Bajar demasiado (estrés hombro) · Codos flared · Inclinación variable",
                'coaching_cues' => ['Torso inclinado 15-30° hacia adelante para pecho', 'Bajar hasta paralelo brazo-suelo', 'Sin balanceo'],
                'variations' => [
                    ['alias' => 'fondos-banco', 'reason' => 'Variación principiante / accesible'],
                ],
            ],

            // ─── ESPALDA (6) ────────────────────────────────────────────────────
            [
                'alias' => 'dominadas',
                'name' => 'Dominadas',
                'muscle_primary' => 'Espalda',
                'muscle_secondary' => 'Bíceps, Antebrazos',
                'equipment_required' => ['barra_dominadas'],
                'equipment_substitutes' => ['dominadas-asistidas', 'jalon-polea-alta'],
                'level_min' => 'intermedio',
                'compound_isolation' => 'compound',
                'movement_pattern' => 'pull_vertical',
                'contraindications' => ['lesion_hombro_grave', 'lesion_codo_aguda'],
                'common_mistakes' => "Rango incompleto (no llegar al mentón sobre barra) · Balanceo (kipping) · Solo bajar sin tirar",
                'coaching_cues' => ['Escápulas hacia abajo antes de tirar', 'Codos hacia abajo y atrás', 'Pecho a la barra'],
                'variations' => [
                    ['alias' => 'dominadas-asistidas', 'reason' => 'Reducir peso corporal con banda o máquina'],
                    ['alias' => 'jalon-polea-alta', 'reason' => 'Para principiantes que no completan 1 dominada'],
                ],
            ],
            [
                'alias' => 'dominadas-asistidas',
                'name' => 'Dominadas asistidas (banda o máquina)',
                'muscle_primary' => 'Espalda',
                'muscle_secondary' => 'Bíceps',
                'equipment_required' => ['barra_dominadas', 'banda_resistencia_o_maquina_asistida'],
                'equipment_substitutes' => ['jalon-polea-alta'],
                'level_min' => 'principiante',
                'compound_isolation' => 'compound',
                'movement_pattern' => 'pull_vertical',
                'contraindications' => ['lesion_hombro_grave'],
                'common_mistakes' => "Confiar demasiado en la banda · No reducir asistencia progresivamente",
                'coaching_cues' => ['Reducir banda cada 2-3 semanas', 'Mantener mismo rango que dominada libre'],
                'variations' => [
                    ['alias' => 'dominadas', 'reason' => 'Progresión natural cuando puedas 5+ reps asistidas'],
                ],
            ],
            [
                'alias' => 'jalon-polea-alta',
                'name' => 'Jalón en polea alta',
                'muscle_primary' => 'Espalda',
                'muscle_secondary' => 'Bíceps',
                'equipment_required' => ['maquina_polea_alta', 'barra'],
                'equipment_substitutes' => ['dominadas', 'dominadas-asistidas'],
                'level_min' => 'principiante',
                'compound_isolation' => 'compound',
                'movement_pattern' => 'pull_vertical',
                'contraindications' => [],
                'common_mistakes' => "Balancearse hacia atrás · Tirar con bíceps en lugar de espalda · Soltar peso en bajada",
                'coaching_cues' => ['Pecho arriba', 'Codos hacia caderas', 'Tirar el codo, no la mano'],
                'variations' => [
                    ['alias' => 'dominadas', 'reason' => 'Progresión a bodyweight'],
                ],
            ],
            [
                'alias' => 'remo-barra',
                'name' => 'Remo con barra',
                'muscle_primary' => 'Espalda',
                'muscle_secondary' => 'Bíceps, Erector espinal',
                'equipment_required' => ['barra'],
                'equipment_substitutes' => ['remo-mancuerna-una-mano', 'remo-sentado-maquina'],
                'level_min' => 'intermedio',
                'compound_isolation' => 'compound',
                'movement_pattern' => 'pull_horizontal',
                'contraindications' => ['lesion_lumbar_aguda', 'hernia_lumbar'],
                'common_mistakes' => "Espalda redonda · Tirar con brazos en lugar de retraer escápulas · Cadera muy alta",
                'coaching_cues' => ['Torso 45°', 'Barra hacia ombligo', 'Apretar escápulas al final'],
                'variations' => [
                    ['alias' => 'remo-mancuerna-una-mano', 'reason' => 'Menos estrés lumbar'],
                ],
            ],
            [
                'alias' => 'remo-mancuerna-una-mano',
                'name' => 'Remo con mancuerna a una mano',
                'muscle_primary' => 'Espalda',
                'muscle_secondary' => 'Bíceps',
                'equipment_required' => ['mancuerna', 'banco_plano'],
                'equipment_substitutes' => ['remo-sentado-maquina', 'remo-barra'],
                'level_min' => 'principiante',
                'compound_isolation' => 'compound',
                'movement_pattern' => 'pull_horizontal',
                'contraindications' => [],
                'common_mistakes' => "Rotar torso (cheating) · Tirar hacia hombro (debería ser hacia cadera) · Soltar arriba",
                'coaching_cues' => ['Espalda paralela al piso', 'Mancuerna hacia cadera', 'Apretar dorsal arriba'],
                'variations' => [
                    ['alias' => 'remo-barra', 'reason' => 'Cuando dominas el patrón, mayor carga'],
                ],
            ],
            [
                'alias' => 'remo-sentado-maquina',
                'name' => 'Remo sentado en máquina',
                'muscle_primary' => 'Espalda',
                'muscle_secondary' => 'Bíceps',
                'equipment_required' => ['maquina_remo_sentado'],
                'equipment_substitutes' => ['remo-mancuerna-una-mano'],
                'level_min' => 'principiante',
                'compound_isolation' => 'compound',
                'movement_pattern' => 'pull_horizontal',
                'contraindications' => [],
                'common_mistakes' => "Tirar con espalda baja · Pecho separado del pad · Soltar peso muy rápido",
                'coaching_cues' => ['Pecho contra el pad', 'Jalar hacia abdomen', 'Apretar escápulas 1 segundo'],
                'variations' => [],
            ],

            // ─── PIERNAS (8) ────────────────────────────────────────────────────
            [
                'alias' => 'sentadilla-barra',
                'name' => 'Sentadilla con barra',
                'muscle_primary' => 'Cuádriceps',
                'muscle_secondary' => 'Glúteo, Isquiotibiales, Erector espinal',
                'equipment_required' => ['barra', 'rack'],
                'equipment_substitutes' => ['sentadilla-frontal', 'prensa-piernas'],
                'level_min' => 'intermedio',
                'compound_isolation' => 'compound',
                'movement_pattern' => 'squat',
                'contraindications' => ['lesion_rodilla_grave', 'hernia_lumbar', 'lesion_hombro_movilidad'],
                'common_mistakes' => "Rodillas hacia adentro (valgo) · Talones se levantan · Espalda redonda · Profundidad insuficiente",
                'coaching_cues' => ['Pies ancho de hombros', 'Empujar el piso', 'Sentarse hacia atrás y abajo', 'Rodillas en línea con dedos del pie'],
                'variations' => [
                    ['alias' => 'sentadilla-frontal', 'reason' => 'Énfasis cuádriceps + obliga torso vertical'],
                    ['alias' => 'sentadilla-bulgara', 'reason' => 'Unilateral, menos carga lumbar'],
                ],
            ],
            [
                'alias' => 'sentadilla-frontal',
                'name' => 'Sentadilla frontal',
                'muscle_primary' => 'Cuádriceps',
                'muscle_secondary' => 'Glúteo, Core',
                'equipment_required' => ['barra', 'rack'],
                'equipment_substitutes' => ['sentadilla-barra'],
                'level_min' => 'avanzado',
                'compound_isolation' => 'compound',
                'movement_pattern' => 'squat',
                'contraindications' => ['lesion_muñeca', 'movilidad_torax_limitada'],
                'common_mistakes' => "Codos bajan (barra cae) · Torso inclinado al frente · Carga excesiva",
                'coaching_cues' => ['Codos altos durante todo el movimiento', 'Torso vertical', 'Rango completo'],
                'variations' => [
                    ['alias' => 'sentadilla-barra', 'reason' => 'Cuando agarre frontal es limitante'],
                ],
            ],
            [
                'alias' => 'sentadilla-bulgara',
                'name' => 'Sentadilla búlgara',
                'muscle_primary' => 'Cuádriceps',
                'muscle_secondary' => 'Glúteo, Isquiotibiales',
                'equipment_required' => ['mancuernas', 'banco_plano'],
                'equipment_substitutes' => ['zancadas-mancuernas'],
                'level_min' => 'intermedio',
                'compound_isolation' => 'compound',
                'movement_pattern' => 'lunge',
                'contraindications' => ['lesion_rodilla_anterior_aguda'],
                'common_mistakes' => "Pierna adelantada muy cerca del banco · Rodilla trasera no toca · Inclinación lateral",
                'coaching_cues' => ['Pierna adelantada 1 metro del banco', 'Empuje con talón pierna adelantada', 'Torso recto'],
                'variations' => [
                    ['alias' => 'zancadas-mancuernas', 'reason' => 'Cuando no hay banco disponible'],
                ],
            ],
            [
                'alias' => 'peso-muerto-convencional',
                'name' => 'Peso muerto convencional',
                'muscle_primary' => 'Cadena posterior',
                'muscle_secondary' => 'Glúteo, Isquiotibiales, Espalda baja, Trapecio',
                'equipment_required' => ['barra', 'discos'],
                'equipment_substitutes' => ['peso-muerto-rumano', 'hip-thrust'],
                'level_min' => 'intermedio',
                'compound_isolation' => 'compound',
                'movement_pattern' => 'hinge',
                'contraindications' => ['hernia_lumbar', 'lesion_lumbar_aguda'],
                'common_mistakes' => "Espalda redonda · Tirar con brazos · Caderas suben antes que pecho · Hiperextender arriba",
                'coaching_cues' => ['Barra pegada al cuerpo todo el rango', 'Pecho alto', 'Cadera y rodillas se extienden juntas'],
                'variations' => [
                    ['alias' => 'peso-muerto-rumano', 'reason' => 'Menos rango, más énfasis isquiotibial'],
                ],
            ],
            [
                'alias' => 'peso-muerto-rumano',
                'name' => 'Peso muerto rumano',
                'muscle_primary' => 'Isquiotibiales',
                'muscle_secondary' => 'Glúteo, Erector espinal',
                'equipment_required' => ['barra'],
                'equipment_substitutes' => ['peso-muerto-convencional', 'extension-femoral-acostado'],
                'level_min' => 'intermedio',
                'compound_isolation' => 'compound',
                'movement_pattern' => 'hinge',
                'contraindications' => ['hernia_lumbar'],
                'common_mistakes' => "Flexionar rodillas (se vuelve convencional) · Bajar sin sentir estiramiento posterior · Espalda redonda",
                'coaching_cues' => ['Rodillas semi-flexionadas pero fijas', 'Empujar caderas atrás', 'Sentir estiramiento en isquiotibiales'],
                'variations' => [
                    ['alias' => 'peso-muerto-convencional', 'reason' => 'Rango completo + más cadena posterior'],
                ],
            ],
            [
                'alias' => 'prensa-piernas',
                'name' => 'Prensa de piernas',
                'muscle_primary' => 'Cuádriceps',
                'muscle_secondary' => 'Glúteo, Isquiotibiales',
                'equipment_required' => ['maquina_prensa_45'],
                'equipment_substitutes' => ['sentadilla-barra', 'sentadilla-bulgara'],
                'level_min' => 'principiante',
                'compound_isolation' => 'compound',
                'movement_pattern' => 'squat',
                'contraindications' => ['lesion_rodilla_grave'],
                'common_mistakes' => "Bloquear rodillas arriba · Bajar muy poco · Levantar nalgas del asiento · Pies muy altos (cambia músculo)",
                'coaching_cues' => ['Pies medio del platform', 'Bajar hasta 90°', 'Sin bloquear rodillas arriba'],
                'variations' => [],
            ],
            [
                'alias' => 'hip-thrust',
                'name' => 'Hip thrust con barra',
                'muscle_primary' => 'Glúteo',
                'muscle_secondary' => 'Isquiotibiales, Core',
                'equipment_required' => ['barra', 'banco_plano', 'almohadilla_barra'],
                'equipment_substitutes' => ['hip-thrust-maquina'],
                'level_min' => 'principiante',
                'compound_isolation' => 'compound',
                'movement_pattern' => 'hinge',
                'contraindications' => ['hernia_lumbar_aguda'],
                'common_mistakes' => "Hiperextensión lumbar arriba · Pies muy lejos o muy cerca · No apretar glúteo arriba",
                'coaching_cues' => ['Espalda media en el banco', 'Pies a 90° de la rodilla arriba', 'Apretar glúteo 1 seg arriba'],
                'variations' => [],
            ],
            [
                'alias' => 'zancadas-mancuernas',
                'name' => 'Zancadas con mancuernas',
                'muscle_primary' => 'Cuádriceps',
                'muscle_secondary' => 'Glúteo, Isquiotibiales',
                'equipment_required' => ['mancuernas'],
                'equipment_substitutes' => ['sentadilla-bulgara'],
                'level_min' => 'principiante',
                'compound_isolation' => 'compound',
                'movement_pattern' => 'lunge',
                'contraindications' => ['lesion_rodilla_aguda'],
                'common_mistakes' => "Paso muy corto (rodilla cruza pie) · Inclinación lateral · Rebote arriba",
                'coaching_cues' => ['Paso largo', 'Rodilla trasera casi al piso', 'Empujar con talón adelantado'],
                'variations' => [],
            ],

            // ─── HOMBROS (3) ────────────────────────────────────────────────────
            [
                'alias' => 'press-militar-barra',
                'name' => 'Press militar con barra',
                'muscle_primary' => 'Hombros',
                'muscle_secondary' => 'Tríceps, Core',
                'equipment_required' => ['barra', 'rack'],
                'equipment_substitutes' => ['press-militar-mancuernas'],
                'level_min' => 'intermedio',
                'compound_isolation' => 'compound',
                'movement_pattern' => 'push_vertical',
                'contraindications' => ['lesion_hombro_anterior', 'inestabilidad_hombro'],
                'common_mistakes' => "Hiperextender lumbar · Empuje con piernas (push press accidental) · Barra muy adelante",
                'coaching_cues' => ['Glúteos apretados', 'Core firme', 'Barra sube en línea vertical'],
                'variations' => [
                    ['alias' => 'press-militar-mancuernas', 'reason' => 'Menos estrés hombro, más rango'],
                ],
            ],
            [
                'alias' => 'press-militar-mancuernas',
                'name' => 'Press militar con mancuernas (sentado)',
                'muscle_primary' => 'Hombros',
                'muscle_secondary' => 'Tríceps',
                'equipment_required' => ['mancuernas', 'banco_con_respaldo'],
                'equipment_substitutes' => ['press-militar-barra'],
                'level_min' => 'principiante',
                'compound_isolation' => 'compound',
                'movement_pattern' => 'push_vertical',
                'contraindications' => ['lesion_hombro_anterior'],
                'common_mistakes' => "Chocar mancuernas arriba · Bajada descontrolada · Codos hacia atrás (afecta rotador)",
                'coaching_cues' => ['Banco a 90°', 'Mancuernas a altura de orejas abajo', 'Codos ligeramente adelantados'],
                'variations' => [],
            ],
            [
                'alias' => 'elevaciones-laterales-mancuerna',
                'name' => 'Elevaciones laterales con mancuernas',
                'muscle_primary' => 'Hombros',
                'muscle_secondary' => 'Trapecio',
                'equipment_required' => ['mancuernas'],
                'equipment_substitutes' => ['elevaciones-laterales-polea'],
                'level_min' => 'principiante',
                'compound_isolation' => 'isolation',
                'movement_pattern' => 'push_vertical',
                'contraindications' => [],
                'common_mistakes' => "Subir por encima de paralelo (involucra trapecio) · Balanceo · Mancuernas muy pesadas",
                'coaching_cues' => ['Codo ligeramente flexionado y fijo', 'Subir hasta paralelo al piso', 'Control de bajada en 2 segundos'],
                'variations' => [],
            ],

            // ─── BRAZOS (3) ─────────────────────────────────────────────────────
            [
                'alias' => 'curl-biceps-mancuerna',
                'name' => 'Curl de bíceps con mancuernas',
                'muscle_primary' => 'Bíceps',
                'muscle_secondary' => 'Antebrazos',
                'equipment_required' => ['mancuernas'],
                'equipment_substitutes' => ['curl-biceps-barra-z'],
                'level_min' => 'principiante',
                'compound_isolation' => 'isolation',
                'movement_pattern' => 'pull_horizontal',
                'contraindications' => [],
                'common_mistakes' => "Balanceo (cheating) · Codos adelantados (involucra hombro) · Rango incompleto",
                'coaching_cues' => ['Codos pegados al torso', 'Supinación completa arriba', 'Bajada controlada'],
                'variations' => [
                    ['alias' => 'curl-biceps-barra-z', 'reason' => 'Mayor carga concentrada, menos estrés muñeca'],
                ],
            ],
            [
                'alias' => 'curl-biceps-barra-z',
                'name' => 'Curl de bíceps con barra Z',
                'muscle_primary' => 'Bíceps',
                'muscle_secondary' => 'Antebrazos',
                'equipment_required' => ['barra_z'],
                'equipment_substitutes' => ['curl-biceps-mancuerna'],
                'level_min' => 'principiante',
                'compound_isolation' => 'isolation',
                'movement_pattern' => 'pull_horizontal',
                'contraindications' => [],
                'common_mistakes' => "Balanceo de cadera · Codos al frente · Rango parcial",
                'coaching_cues' => ['Agarre supinado natural en la Z', 'Sin balanceo', 'Apretar arriba 1 segundo'],
                'variations' => [],
            ],
            [
                'alias' => 'extension-triceps-polea',
                'name' => 'Extensión de tríceps en polea',
                'muscle_primary' => 'Tríceps',
                'muscle_secondary' => '',
                'equipment_required' => ['maquina_polea_alta', 'cuerda_o_barra'],
                'equipment_substitutes' => ['extension-triceps-mancuerna'],
                'level_min' => 'principiante',
                'compound_isolation' => 'isolation',
                'movement_pattern' => 'push_horizontal',
                'contraindications' => ['lesion_codo_aguda'],
                'common_mistakes' => "Codos abriéndose · Movimiento desde hombro · Soltar peso arriba",
                'coaching_cues' => ['Codos pegados al torso fijos', 'Solo se mueve antebrazo', 'Extensión completa abajo'],
                'variations' => [],
            ],

            // ─── POSTERIOR / CUADRÍCEPS específicos (2) ─────────────────────────
            [
                'alias' => 'extension-femoral-acostado',
                'name' => 'Extensión femoral acostado',
                'muscle_primary' => 'Isquiotibiales',
                'muscle_secondary' => '',
                'equipment_required' => ['maquina_femoral_acostado'],
                'equipment_substitutes' => ['extension-femoral-sentado', 'peso-muerto-rumano'],
                'level_min' => 'principiante',
                'compound_isolation' => 'isolation',
                'movement_pattern' => 'hinge',
                'contraindications' => ['lesion_rodilla_aguda'],
                'common_mistakes' => "Levantar caderas del pad · Rango incompleto · Movimiento explosivo bajando",
                'coaching_cues' => ['Caderas pegadas al pad', 'Subir hasta tocar glúteos', 'Bajar en 2 segundos'],
                'variations' => [],
            ],
            [
                'alias' => 'extension-cuadriceps',
                'name' => 'Extensión de cuádriceps',
                'muscle_primary' => 'Cuádriceps',
                'muscle_secondary' => '',
                'equipment_required' => ['maquina_extension_cuadriceps'],
                'equipment_substitutes' => [],
                'level_min' => 'principiante',
                'compound_isolation' => 'isolation',
                'movement_pattern' => 'squat',
                'contraindications' => ['lesion_rodilla_aguda', 'condromalacia_rotuliana'],
                'common_mistakes' => "Cargar peso excesivo (estrés patelar) · Asiento mal ajustado · Soltar peso bajando",
                'coaching_cues' => ['Espalda apoyada total', 'Extensión completa arriba 1 seg', 'Control de bajada'],
                'variations' => [],
            ],

            // ─── CARDIO (1) ─────────────────────────────────────────────────────
            [
                'alias' => 'caminadora-inclinada',
                'name' => 'Caminadora inclinada',
                'muscle_primary' => 'Cardiovascular',
                'muscle_secondary' => 'Glúteo, Pantorrilla',
                'equipment_required' => ['caminadora'],
                'equipment_substitutes' => ['escaladora'],
                'level_min' => 'principiante',
                'compound_isolation' => 'compound',
                'movement_pattern' => 'cardio_steady',
                'contraindications' => ['lesion_rodilla_aguda'],
                'common_mistakes' => "Agarrarse de las manijas (anula trabajo) · Inclinación muy baja (no estímulo) · Velocidad muy alta sin propósito",
                'coaching_cues' => ['Sin agarrarse', 'Velocidad 5-6 km/h, inclinación 10-12%', 'Postura erecta'],
                'variations' => [],
            ],

            // ─── CORE (2) ───────────────────────────────────────────────────────
            [
                'alias' => 'plancha-frontal',
                'name' => 'Plancha frontal isométrica',
                'muscle_primary' => 'Core',
                'muscle_secondary' => 'Hombros, Glúteo',
                'equipment_required' => [],
                'equipment_substitutes' => [],
                'level_min' => 'principiante',
                'compound_isolation' => 'isolation',
                'movement_pattern' => 'core',
                'contraindications' => ['lesion_hombro_aguda', 'lesion_lumbar_aguda'],
                'common_mistakes' => "Cadera muy alta o muy baja · Hombros encogidos · Aguantar respiración",
                'coaching_cues' => ['Línea recta cabeza-talones', 'Codos bajo hombros', 'Apretar glúteo + core'],
                'variations' => [],
            ],
            [
                'alias' => 'abdominales-rueda',
                'name' => 'Abdominales con rueda (rollout)',
                'muscle_primary' => 'Core',
                'muscle_secondary' => 'Hombros, Dorsal',
                'equipment_required' => ['rueda_abdominal'],
                'equipment_substitutes' => ['plancha-frontal'],
                'level_min' => 'intermedio',
                'compound_isolation' => 'compound',
                'movement_pattern' => 'core',
                'contraindications' => ['hernia_lumbar', 'lesion_hombro'],
                'common_mistakes' => "Espalda baja arqueada (hiperextensión) · Extensión incompleta · Volver muy rápido",
                'coaching_cues' => ['Core firme todo el rango', 'Cadera + hombros ruedan juntos', 'Sin tocar el piso con vientre'],
                'variations' => [
                    ['alias' => 'plancha-frontal', 'reason' => 'Variación principiante / sin equipo'],
                ],
            ],
        ];
    }
}
