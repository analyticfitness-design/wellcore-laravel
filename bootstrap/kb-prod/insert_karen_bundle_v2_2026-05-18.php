<?php

/**
 * insert_bundle.php — generado automáticamente por plan:export-bundle-prod-script
 *
 * Inserta 4 planes (bundle multi-vertical) en wellcore_fitness.assigned_plans
 * dentro de UNA SOLA transaction atómica.
 *
 * Generado:    2026-05-18T13:54:54-05:00
 * Cliente:     97
 * Coach:       9
 * Vigencia:    2026-06-01 → 2026-06-29
 * Plan types:  'entrenamiento', 'nutricion', 'suplementacion', 'habitos'
 *
 * Ejecutar en container EasyPanel:
 *   php /code/bootstrap/kb-prod/insert_bundle_*.php
 *
 * Para escribir realmente, editar este archivo:
 *   const DRY_RUN = true;  → const DRY_RUN = false;
 */

const DRY_RUN    = false;  // ✓ Motor v2 upgrade — autorizado por Daniel "hazlo todo"
const CLIENT_ID  = 97;
const COACH_ID   = 9;
const VALID_FROM = '2026-06-01';
const EXPIRES_AT = '2026-06-29';

$now = date('Y-m-d H:i:s');

// ─── Bundle: 4 planes ─────────────────────────────────────────────
$bundle = array (
  0 => 
  array (
    'composed_id' => 11,
    'plan_type' => 'entrenamiento',
    'methodology_slug' => 'upper_lower_4d',
    'plan_json' => 
    array (
      'plan_type' => 'entrenamiento',
      'titulo' => 'Plan Upper / Lower 4 días — Karen Vanessa Gómez Lagos',
      'objetivo' => 'Pérdida de grasa con preservación de masa muscular. Upper / Lower 4 días con énfasis en mantener intensidad alta mientras el déficit calórico hace el trabajo.',
      'metodologia' => 'Upper / Lower 4 días',
      'frecuencia' => '4 dias/semana',
      'frecuencia_dias' => 4,
      'duracion_semanas' => 4,
      'fecha_inicio' => '2026-06-01',
      'split' => 
      array (
        'Lunes' => 'Gluteos',
        'Martes' => 'Espalda + Triceps + Hombros + Biceps',
        'Viernes' => 'Gluteos',
        'Miércoles' => 'Piernas',
      ),
      'notas_coach' => 'Karen, este plan está armado para tu objetivo de pérdida de grasa con 4 días/semana. La metodología elegida es Upper / Lower 4 días porque encaja con tu nivel intermedio y el split que tu coach validó.

La estructura sube intensidad cada semana: empezás con RIR 3 (3 reps en reserva) y vas bajando hasta RIR 0 en la semana 4. Las series y rangos de repeticiones cambian entre fases — compounds llevan más series y menos reps, isolations lo opuesto. No es 3×12 lineal — cada ejercicio tiene su prescripción.

Las primeras 2 semanas pueden sentirse pesadas — el cuerpo está en déficit calórico. Si tenés baja energía, mové la carga +5kg cada semana solo si llegás al RIR objetivo. A partir de la semana 3 empezás a ver cambios reales en espejo y medidas.

Empezás mañana. Anotá peso y RIR de cada serie apenas terminás — sin registro no hay progresión real. Si una semana no llegás al RIR objetivo, te quedás en el peso y ajustás técnica primero. Cualquier dolor articular (no fatiga muscular), parás y me avisás. — Héctor Leonardo Pérez Torres',
      'consejos_coach' => 
      array (
        0 => 'Calentá siempre 5-10 min antes de la primera serie',
        1 => 'Compounds primero, isolations al final del día',
        2 => 'Anotá peso y RIR de cada serie apenas terminás el ejercicio',
        3 => 'Si una articulación duele (no fatiga muscular), parás y avisás',
        4 => 'Hidratate durante el entreno (mínimo 500 ml/hora)',
        5 => 'Dormí 7-9h — la recuperación es parte del plan',
        6 => 'Cardio post-pesas, no antes (no robés energía a la musculación)',
        7 => 'Si energía baja, una taza de café 30 min antes ayuda',
      ),
      'tips' => 
      array (
        0 => 'Anotá peso, reps y RIR de cada serie apenas terminás el ejercicio',
        1 => 'Hidratate durante el entreno (mínimo 500 ml por hora de gym)',
        2 => 'Dormí al menos 7 horas — la recuperación es parte del plan',
        3 => 'Si una articulación duele (no fatiga muscular), parás el ejercicio y avisás al coach',
        4 => 'Semana de descarga (volumen -40-50%, intensidad -10%) cada 4-6 semanas previene overreaching y permite supercompensar.',
        5 => '4 entrenos por semana durante 12 semanas vencen a 7 entrenos perfectos durante 3 semanas + 9 semanas perdidas.',
        6 => 'El músculo crece entre sesiones, no durante. Sueño + nutrición + descanso entre series son tan importantes como el ejercicio.',
      ),
      'principios_aplicados' => 
      array (
        0 => 'deload_semana_cada_4_6',
        1 => 'consistencia_sobre_intensidad',
        2 => 'recuperacion_es_entrenamiento',
      ),
      'semanas' => 
      array (
        0 => 
        array (
          'numero' => 1,
          'fase' => 'Adaptación · RIR 3',
          'rir_objetivo' => 3,
          'volumen_pct' => 70,
          'descripcion' => 'Semana de adaptación. RIR 3 — quedate con 3 reps en reserva, prioridad técnica sobre carga. Sentí los músculos trabajando.',
          'dias' => 
          array (
            0 => 
            array (
              'dia_semana' => 'Lunes',
              'grupo_muscular' => 'Gluteos',
              'nombre' => 'Lunes — Gluteos',
              'duracion_estimada_min' => 47,
              'calentamiento' => '5 min cardio suave + movilidad articular general. Total: 8 min.',
              'vuelta_calma' => 'Estiramiento 3-5 min de los grupos trabajados + 5 min caminata suave',
              'ejercicios' => 
              array (
                0 => 
                array (
                  'nombre' => 'Hip thrust con barra',
                  'series' => 3,
                  'repeticiones' => '12',
                  'descanso' => '90s',
                  'rir' => 3,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/hipthrust-con-barra.gif',
                  'notas' => 'Espalda media en el banco. Pies a 90° de la rodilla arriba. Apretar glúteo 1 seg arriba.',
                  'tecnica_ejecucion' => 'Espalda media en el banco. Pies a 90° de la rodilla arriba. Apretar glúteo 1 seg arriba.',
                  'errores_comunes' => 'Hiperextensión lumbar arriba · Pies muy lejos o muy cerca · No apretar glúteo arriba',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'compound',
                  'orden' => 1,
                ),
                1 => 
                array (
                  'nombre' => 'Hipthrust a una pierna con barra',
                  'series' => 3,
                  'repeticiones' => '12',
                  'descanso' => '90s',
                  'rir' => 3,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/hipthrust-a-una-pierna-con-barra.gif',
                  'notas' => 'Espalda apoyada en banco, barra acolchada en caderas. Empujá desde talones, apretá glúteo 1 seg arriba. Costillas abajo, no extender lumbar.',
                  'tecnica_ejecucion' => 'Espalda apoyada en banco, barra acolchada en caderas. Empujá desde talones, apretá glúteo 1 seg arriba. Costillas abajo, no extender lumbar.',
                  'errores_comunes' => 'Levantar la lumbar (no apretar glúteo) · Pies muy adelante · No apretar arriba',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'compound',
                  'orden' => 2,
                ),
                2 => 
                array (
                  'nombre' => 'Abduccion de cadera de pie en maquina',
                  'series' => 3,
                  'repeticiones' => '12-15',
                  'descanso' => '60s',
                  'rir' => 3,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 3,
                ),
                3 => 
                array (
                  'nombre' => 'Abduccion de cadera sentado en maquina',
                  'series' => 3,
                  'repeticiones' => '12-15',
                  'descanso' => '60s',
                  'rir' => 3,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-sentado-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 4,
                ),
                4 => 
                array (
                  'nombre' => 'Aduccion de cadera de pie en maquina',
                  'series' => 3,
                  'repeticiones' => '12-15',
                  'descanso' => '60s',
                  'rir' => 3,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/aduccion-de-cadera-de-pie-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 5,
                ),
                5 => 
                array (
                  'nombre' => 'Aduccion de cadera en polea',
                  'series' => 3,
                  'repeticiones' => '12-15',
                  'descanso' => '60s',
                  'rir' => 3,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/aduccion-de-cadera-en-polea.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 6,
                ),
                6 => 
                array (
                  'nombre' => 'Aduccion de cadera sentado en maquina',
                  'series' => 3,
                  'repeticiones' => '12-15',
                  'descanso' => '60s',
                  'rir' => 3,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/aduccion-de-cadera-sentado-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 7,
                ),
              ),
            ),
            1 => 
            array (
              'dia_semana' => 'Martes',
              'grupo_muscular' => 'Espalda + Triceps + Hombros + Biceps',
              'nombre' => 'Martes — Espalda + Triceps + Hombros + Biceps',
              'duracion_estimada_min' => 47,
              'calentamiento' => '5 min remo o caminadora + rotaciones de hombro 2×15 + 1×15 push-up rodillas + 1×10 face-pull con banda. Total: 8 min.',
              'vuelta_calma' => 'Estiramiento 3-5 min de los grupos trabajados + 5 min caminata suave',
              'ejercicios' => 
              array (
                0 => 
                array (
                  'nombre' => 'Dominadas asistidas (banda o máquina)',
                  'series' => 3,
                  'repeticiones' => '12',
                  'descanso' => '90s',
                  'rir' => 3,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/dominadas.gif',
                  'notas' => 'Reducir banda cada 2-3 semanas. Mantener mismo rango que dominada libre.',
                  'tecnica_ejecucion' => 'Reducir banda cada 2-3 semanas. Mantener mismo rango que dominada libre.',
                  'errores_comunes' => 'Confiar demasiado en la banda · No reducir asistencia progresivamente',
                  'musculo_primario' => 'Espalda',
                  'tipo' => 'compound',
                  'orden' => 1,
                ),
                1 => 
                array (
                  'nombre' => 'Fondos de triceps en maquina',
                  'series' => 3,
                  'repeticiones' => '12',
                  'descanso' => '90s',
                  'rir' => 3,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/fondos-de-triceps-en-maquina.gif',
                  'notas' => 'Escápulas retraídas y abajo. Bajá 2-3 seg controlado, empujá explosivo. Codos 45° del torso, no abiertos a 90°.',
                  'tecnica_ejecucion' => 'Escápulas retraídas y abajo. Bajá 2-3 seg controlado, empujá explosivo. Codos 45° del torso, no abiertos a 90°.',
                  'errores_comunes' => 'Codos abiertos a 90° · Rebotar la barra en el pecho · Rango parcial sin tocar pecho',
                  'musculo_primario' => 'Tríceps',
                  'tipo' => 'compound',
                  'orden' => 2,
                ),
                2 => 
                array (
                  'nombre' => 'Elevaciones laterales con mancuernas',
                  'series' => 3,
                  'repeticiones' => '12-15',
                  'descanso' => '60s',
                  'rir' => 3,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/elevacion-lateral-con-mancuerna.gif',
                  'notas' => 'Codo ligeramente flexionado y fijo. Subir hasta paralelo al piso. Control de bajada en 2 segundos.',
                  'tecnica_ejecucion' => 'Codo ligeramente flexionado y fijo. Subir hasta paralelo al piso. Control de bajada en 2 segundos.',
                  'errores_comunes' => 'Subir por encima de paralelo (involucra trapecio) · Balanceo · Mancuernas muy pesadas',
                  'musculo_primario' => 'Hombros',
                  'tipo' => 'isolation',
                  'orden' => 3,
                ),
                3 => 
                array (
                  'nombre' => 'Curl de bíceps con mancuernas',
                  'series' => 3,
                  'repeticiones' => '12-15',
                  'descanso' => '60s',
                  'rir' => 3,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/curl-biceps-con-mancuerna.gif',
                  'notas' => 'Codos pegados al torso. Supinación completa arriba. Bajada controlada.',
                  'tecnica_ejecucion' => 'Codos pegados al torso. Supinación completa arriba. Bajada controlada.',
                  'errores_comunes' => 'Balanceo (cheating) · Codos adelantados (involucra hombro) · Rango incompleto',
                  'musculo_primario' => 'Bíceps',
                  'tipo' => 'isolation',
                  'orden' => 4,
                  'variacion' => 
                  array (
                    'nombre' => 'Curl de bíceps con barra Z',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/curl-biceps-barra-ez.gif',
                    'motivo' => 'Mayor carga concentrada, menos estrés muñeca',
                  ),
                ),
                4 => 
                array (
                  'nombre' => 'Curl de bíceps con barra Z',
                  'series' => 3,
                  'repeticiones' => '12-15',
                  'descanso' => '60s',
                  'rir' => 3,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/curl-biceps-barra-ez.gif',
                  'notas' => 'Agarre supinado natural en la Z. Sin balanceo. Apretar arriba 1 segundo.',
                  'tecnica_ejecucion' => 'Agarre supinado natural en la Z. Sin balanceo. Apretar arriba 1 segundo.',
                  'errores_comunes' => 'Balanceo de cadera · Codos al frente · Rango parcial',
                  'musculo_primario' => 'Bíceps',
                  'tipo' => 'isolation',
                  'orden' => 5,
                ),
                5 => 
                array (
                  'nombre' => 'Extensión de tríceps en polea',
                  'series' => 3,
                  'repeticiones' => '12-15',
                  'descanso' => '60s',
                  'rir' => 3,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/extension-de-triceps-en-polea-con-cuerda.gif',
                  'notas' => 'Codos pegados al torso fijos. Solo se mueve antebrazo. Extensión completa abajo.',
                  'tecnica_ejecucion' => 'Codos pegados al torso fijos. Solo se mueve antebrazo. Extensión completa abajo.',
                  'errores_comunes' => 'Codos abriéndose · Movimiento desde hombro · Soltar peso arriba',
                  'musculo_primario' => 'Tríceps',
                  'tipo' => 'isolation',
                  'orden' => 6,
                ),
                6 => 
                array (
                  'nombre' => 'Elevacion fronta en polea barra',
                  'series' => 3,
                  'repeticiones' => '12-15',
                  'descanso' => '60s',
                  'rir' => 3,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/elevacion-fronta-en-polea-barra.gif',
                  'notas' => 'Codos ligeramente flexionados. Subí hasta línea de hombros, no más arriba. Bajá controlado, sin caer.',
                  'tecnica_ejecucion' => 'Codos ligeramente flexionados. Subí hasta línea de hombros, no más arriba. Bajá controlado, sin caer.',
                  'errores_comunes' => 'Subir más allá de los hombros · Codos bloqueados rectos · Usar trapecio',
                  'musculo_primario' => 'Hombros',
                  'tipo' => 'isolation',
                  'orden' => 7,
                ),
              ),
            ),
            2 => 
            array (
              'dia_semana' => 'Viernes',
              'grupo_muscular' => 'Gluteos',
              'nombre' => 'Viernes — Gluteos',
              'duracion_estimada_min' => 47,
              'calentamiento' => '5 min cardio suave + movilidad articular general. Total: 8 min.',
              'vuelta_calma' => 'Estiramiento 3-5 min de los grupos trabajados + 5 min caminata suave',
              'ejercicios' => 
              array (
                0 => 
                array (
                  'nombre' => 'Hip thrust con barra',
                  'series' => 3,
                  'repeticiones' => '12',
                  'descanso' => '90s',
                  'rir' => 3,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/hipthrust-con-barra.gif',
                  'notas' => 'Espalda media en el banco. Pies a 90° de la rodilla arriba. Apretar glúteo 1 seg arriba.',
                  'tecnica_ejecucion' => 'Espalda media en el banco. Pies a 90° de la rodilla arriba. Apretar glúteo 1 seg arriba.',
                  'errores_comunes' => 'Hiperextensión lumbar arriba · Pies muy lejos o muy cerca · No apretar glúteo arriba',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'compound',
                  'orden' => 1,
                ),
                1 => 
                array (
                  'nombre' => 'Hipthrust a una pierna con barra',
                  'series' => 3,
                  'repeticiones' => '12',
                  'descanso' => '90s',
                  'rir' => 3,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/hipthrust-a-una-pierna-con-barra.gif',
                  'notas' => 'Espalda apoyada en banco, barra acolchada en caderas. Empujá desde talones, apretá glúteo 1 seg arriba. Costillas abajo, no extender lumbar.',
                  'tecnica_ejecucion' => 'Espalda apoyada en banco, barra acolchada en caderas. Empujá desde talones, apretá glúteo 1 seg arriba. Costillas abajo, no extender lumbar.',
                  'errores_comunes' => 'Levantar la lumbar (no apretar glúteo) · Pies muy adelante · No apretar arriba',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'compound',
                  'orden' => 2,
                ),
                2 => 
                array (
                  'nombre' => 'Abduccion de cadera de pie en maquina',
                  'series' => 3,
                  'repeticiones' => '12-15',
                  'descanso' => '60s',
                  'rir' => 3,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 3,
                ),
                3 => 
                array (
                  'nombre' => 'Abduccion de cadera sentado en maquina',
                  'series' => 3,
                  'repeticiones' => '12-15',
                  'descanso' => '60s',
                  'rir' => 3,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-sentado-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 4,
                ),
                4 => 
                array (
                  'nombre' => 'Aduccion de cadera de pie en maquina',
                  'series' => 3,
                  'repeticiones' => '12-15',
                  'descanso' => '60s',
                  'rir' => 3,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/aduccion-de-cadera-de-pie-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 5,
                ),
                5 => 
                array (
                  'nombre' => 'Aduccion de cadera en polea',
                  'series' => 3,
                  'repeticiones' => '12-15',
                  'descanso' => '60s',
                  'rir' => 3,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/aduccion-de-cadera-en-polea.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 6,
                ),
                6 => 
                array (
                  'nombre' => 'Aduccion de cadera sentado en maquina',
                  'series' => 3,
                  'repeticiones' => '12-15',
                  'descanso' => '60s',
                  'rir' => 3,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/aduccion-de-cadera-sentado-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 7,
                ),
              ),
            ),
            3 => 
            array (
              'dia_semana' => 'Miércoles',
              'grupo_muscular' => 'Piernas',
              'nombre' => 'Miércoles — Piernas',
              'duracion_estimada_min' => 47,
              'calentamiento' => '5 min cardio suave + movilidad articular general. Total: 8 min.',
              'vuelta_calma' => 'Estiramiento 3-5 min de los grupos trabajados + 5 min caminata suave',
              'ejercicios' => 
              array (
                0 => 
                array (
                  'nombre' => 'Sentadilla con barra',
                  'series' => 3,
                  'repeticiones' => '12',
                  'descanso' => '90s',
                  'rir' => 3,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/sentadilla-con-barra.gif',
                  'notas' => 'Pies ancho de hombros. Empujar el piso. Sentarse hacia atrás y abajo.',
                  'tecnica_ejecucion' => 'Pies ancho de hombros. Empujar el piso. Sentarse hacia atrás y abajo. Rodillas en línea con dedos del pie.',
                  'errores_comunes' => 'Rodillas hacia adentro (valgo) · Talones se levantan · Espalda redonda · Profundidad insuficiente',
                  'musculo_primario' => 'Cuádriceps',
                  'tipo' => 'compound',
                  'orden' => 1,
                  'variacion' => 
                  array (
                    'nombre' => 'Sentadilla frontal',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/sentadilla-frontal-en-landmine.gif',
                    'motivo' => 'Énfasis cuádriceps + obliga torso vertical',
                  ),
                ),
                1 => 
                array (
                  'nombre' => 'Hip thrust con barra',
                  'series' => 3,
                  'repeticiones' => '12',
                  'descanso' => '90s',
                  'rir' => 3,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/hipthrust-con-barra.gif',
                  'notas' => 'Espalda media en el banco. Pies a 90° de la rodilla arriba. Apretar glúteo 1 seg arriba.',
                  'tecnica_ejecucion' => 'Espalda media en el banco. Pies a 90° de la rodilla arriba. Apretar glúteo 1 seg arriba.',
                  'errores_comunes' => 'Hiperextensión lumbar arriba · Pies muy lejos o muy cerca · No apretar glúteo arriba',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'compound',
                  'orden' => 2,
                ),
                2 => 
                array (
                  'nombre' => 'Extensión de cuádriceps',
                  'series' => 3,
                  'repeticiones' => '12-15',
                  'descanso' => '60s',
                  'rir' => 3,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/extension-de-piernas-en-maquina.gif',
                  'notas' => 'Espalda apoyada total. Extensión completa arriba 1 seg. Control de bajada.',
                  'tecnica_ejecucion' => 'Espalda apoyada total. Extensión completa arriba 1 seg. Control de bajada.',
                  'errores_comunes' => 'Cargar peso excesivo (estrés patelar) · Asiento mal ajustado · Soltar peso bajando',
                  'musculo_primario' => 'Cuádriceps',
                  'tipo' => 'isolation',
                  'orden' => 3,
                ),
                3 => 
                array (
                  'nombre' => 'Abduccion de cadera de pie en maquina',
                  'series' => 3,
                  'repeticiones' => '12-15',
                  'descanso' => '60s',
                  'rir' => 3,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 4,
                ),
                4 => 
                array (
                  'nombre' => 'Abduccion de cadera sentado en maquina',
                  'series' => 3,
                  'repeticiones' => '12-15',
                  'descanso' => '60s',
                  'rir' => 3,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-sentado-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 5,
                ),
                5 => 
                array (
                  'nombre' => 'Aduccion de cadera de pie en maquina',
                  'series' => 3,
                  'repeticiones' => '12-15',
                  'descanso' => '60s',
                  'rir' => 3,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/aduccion-de-cadera-de-pie-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 6,
                ),
                6 => 
                array (
                  'nombre' => 'Presa de piernas abierto',
                  'series' => 3,
                  'repeticiones' => '12-15',
                  'descanso' => '60s',
                  'rir' => 3,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/presa-de-piernas-abierto.gif',
                  'notas' => 'Codos quietos al lado del cuerpo. Movimiento solo del codo, sin balanceo. Bajá controlado 2s, subí explosivo.',
                  'tecnica_ejecucion' => 'Codos quietos al lado del cuerpo. Movimiento solo del codo, sin balanceo. Bajá controlado 2s, subí explosivo.',
                  'errores_comunes' => 'Balancear el cuerpo · Codos se mueven · Rango parcial',
                  'musculo_primario' => 'Cuádriceps',
                  'tipo' => 'isolation',
                  'orden' => 7,
                ),
              ),
            ),
          ),
        ),
        1 => 
        array (
          'numero' => 2,
          'fase' => 'Hipertrofia · RIR 2',
          'rir_objetivo' => 2,
          'volumen_pct' => 100,
          'descripcion' => 'Acumulación de volumen. RIR 2 — subí carga si la técnica está sólida, te quedan 2 reps en el tanque al terminar la serie.',
          'dias' => 
          array (
            0 => 
            array (
              'dia_semana' => 'Lunes',
              'grupo_muscular' => 'Gluteos',
              'nombre' => 'Lunes — Gluteos',
              'duracion_estimada_min' => 56,
              'calentamiento' => '5 min cardio suave + movilidad articular general. Total: 8 min.',
              'vuelta_calma' => 'Estiramiento 3-5 min de los grupos trabajados + 5 min caminata suave',
              'ejercicios' => 
              array (
                0 => 
                array (
                  'nombre' => 'Hip thrust con barra',
                  'series' => 4,
                  'repeticiones' => '10',
                  'descanso' => '90s',
                  'rir' => 2,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/hipthrust-con-barra.gif',
                  'notas' => 'Espalda media en el banco. Pies a 90° de la rodilla arriba. Apretar glúteo 1 seg arriba.',
                  'tecnica_ejecucion' => 'Espalda media en el banco. Pies a 90° de la rodilla arriba. Apretar glúteo 1 seg arriba.',
                  'errores_comunes' => 'Hiperextensión lumbar arriba · Pies muy lejos o muy cerca · No apretar glúteo arriba',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'compound',
                  'orden' => 1,
                ),
                1 => 
                array (
                  'nombre' => 'Hipthrust a una pierna con barra',
                  'series' => 4,
                  'repeticiones' => '10',
                  'descanso' => '90s',
                  'rir' => 2,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/hipthrust-a-una-pierna-con-barra.gif',
                  'notas' => 'Espalda apoyada en banco, barra acolchada en caderas. Empujá desde talones, apretá glúteo 1 seg arriba. Costillas abajo, no extender lumbar.',
                  'tecnica_ejecucion' => 'Espalda apoyada en banco, barra acolchada en caderas. Empujá desde talones, apretá glúteo 1 seg arriba. Costillas abajo, no extender lumbar.',
                  'errores_comunes' => 'Levantar la lumbar (no apretar glúteo) · Pies muy adelante · No apretar arriba',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'compound',
                  'orden' => 2,
                ),
                2 => 
                array (
                  'nombre' => 'Abduccion de cadera de pie en maquina',
                  'series' => 3,
                  'repeticiones' => '10-12',
                  'descanso' => '75s',
                  'rir' => 2,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 3,
                ),
                3 => 
                array (
                  'nombre' => 'Abduccion de cadera sentado en maquina',
                  'series' => 3,
                  'repeticiones' => '10-12',
                  'descanso' => '75s',
                  'rir' => 2,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-sentado-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 4,
                ),
                4 => 
                array (
                  'nombre' => 'Aduccion de cadera de pie en maquina',
                  'series' => 3,
                  'repeticiones' => '10-12',
                  'descanso' => '75s',
                  'rir' => 2,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/aduccion-de-cadera-de-pie-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 5,
                ),
                5 => 
                array (
                  'nombre' => 'Aduccion de cadera en polea',
                  'series' => 3,
                  'repeticiones' => '10-12',
                  'descanso' => '75s',
                  'rir' => 2,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/aduccion-de-cadera-en-polea.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 6,
                ),
                6 => 
                array (
                  'nombre' => 'Aduccion de cadera sentado en maquina',
                  'series' => 3,
                  'repeticiones' => '10-12',
                  'descanso' => '75s',
                  'rir' => 2,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/aduccion-de-cadera-sentado-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 7,
                  'tecnica_intensificacion' => 
                  array (
                    'nombre' => 'Drop Set',
                    'descripcion' => 'En la última serie: bajá ~20-30% el peso y seguí hasta el fallo técnico. Sin descanso entre la serie principal y el drop.',
                  ),
                ),
              ),
            ),
            1 => 
            array (
              'dia_semana' => 'Martes',
              'grupo_muscular' => 'Espalda + Triceps + Hombros + Biceps',
              'nombre' => 'Martes — Espalda + Triceps + Hombros + Biceps',
              'duracion_estimada_min' => 56,
              'calentamiento' => '5 min remo o caminadora + rotaciones de hombro 2×15 + 1×15 push-up rodillas + 1×10 face-pull con banda. Total: 8 min.',
              'vuelta_calma' => 'Estiramiento 3-5 min de los grupos trabajados + 5 min caminata suave',
              'ejercicios' => 
              array (
                0 => 
                array (
                  'nombre' => 'Dominadas asistidas (banda o máquina)',
                  'series' => 4,
                  'repeticiones' => '10',
                  'descanso' => '90s',
                  'rir' => 2,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/dominadas.gif',
                  'notas' => 'Reducir banda cada 2-3 semanas. Mantener mismo rango que dominada libre.',
                  'tecnica_ejecucion' => 'Reducir banda cada 2-3 semanas. Mantener mismo rango que dominada libre.',
                  'errores_comunes' => 'Confiar demasiado en la banda · No reducir asistencia progresivamente',
                  'musculo_primario' => 'Espalda',
                  'tipo' => 'compound',
                  'orden' => 1,
                ),
                1 => 
                array (
                  'nombre' => 'Fondos de triceps en maquina',
                  'series' => 4,
                  'repeticiones' => '10',
                  'descanso' => '90s',
                  'rir' => 2,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/fondos-de-triceps-en-maquina.gif',
                  'notas' => 'Escápulas retraídas y abajo. Bajá 2-3 seg controlado, empujá explosivo. Codos 45° del torso, no abiertos a 90°.',
                  'tecnica_ejecucion' => 'Escápulas retraídas y abajo. Bajá 2-3 seg controlado, empujá explosivo. Codos 45° del torso, no abiertos a 90°.',
                  'errores_comunes' => 'Codos abiertos a 90° · Rebotar la barra en el pecho · Rango parcial sin tocar pecho',
                  'musculo_primario' => 'Tríceps',
                  'tipo' => 'compound',
                  'orden' => 2,
                ),
                2 => 
                array (
                  'nombre' => 'Elevaciones laterales con mancuernas',
                  'series' => 3,
                  'repeticiones' => '10-12',
                  'descanso' => '75s',
                  'rir' => 2,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/elevacion-lateral-con-mancuerna.gif',
                  'notas' => 'Codo ligeramente flexionado y fijo. Subir hasta paralelo al piso. Control de bajada en 2 segundos.',
                  'tecnica_ejecucion' => 'Codo ligeramente flexionado y fijo. Subir hasta paralelo al piso. Control de bajada en 2 segundos.',
                  'errores_comunes' => 'Subir por encima de paralelo (involucra trapecio) · Balanceo · Mancuernas muy pesadas',
                  'musculo_primario' => 'Hombros',
                  'tipo' => 'isolation',
                  'orden' => 3,
                ),
                3 => 
                array (
                  'nombre' => 'Curl de bíceps con mancuernas',
                  'series' => 3,
                  'repeticiones' => '10-12',
                  'descanso' => '75s',
                  'rir' => 2,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/curl-biceps-con-mancuerna.gif',
                  'notas' => 'Codos pegados al torso. Supinación completa arriba. Bajada controlada.',
                  'tecnica_ejecucion' => 'Codos pegados al torso. Supinación completa arriba. Bajada controlada.',
                  'errores_comunes' => 'Balanceo (cheating) · Codos adelantados (involucra hombro) · Rango incompleto',
                  'musculo_primario' => 'Bíceps',
                  'tipo' => 'isolation',
                  'orden' => 4,
                  'variacion' => 
                  array (
                    'nombre' => 'Curl de bíceps con barra Z',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/curl-biceps-barra-ez.gif',
                    'motivo' => 'Mayor carga concentrada, menos estrés muñeca',
                  ),
                ),
                4 => 
                array (
                  'nombre' => 'Curl de bíceps con barra Z',
                  'series' => 3,
                  'repeticiones' => '10-12',
                  'descanso' => '75s',
                  'rir' => 2,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/curl-biceps-barra-ez.gif',
                  'notas' => 'Agarre supinado natural en la Z. Sin balanceo. Apretar arriba 1 segundo.',
                  'tecnica_ejecucion' => 'Agarre supinado natural en la Z. Sin balanceo. Apretar arriba 1 segundo.',
                  'errores_comunes' => 'Balanceo de cadera · Codos al frente · Rango parcial',
                  'musculo_primario' => 'Bíceps',
                  'tipo' => 'isolation',
                  'orden' => 5,
                ),
                5 => 
                array (
                  'nombre' => 'Extensión de tríceps en polea',
                  'series' => 3,
                  'repeticiones' => '10-12',
                  'descanso' => '75s',
                  'rir' => 2,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/extension-de-triceps-en-polea-con-cuerda.gif',
                  'notas' => 'Codos pegados al torso fijos. Solo se mueve antebrazo. Extensión completa abajo.',
                  'tecnica_ejecucion' => 'Codos pegados al torso fijos. Solo se mueve antebrazo. Extensión completa abajo.',
                  'errores_comunes' => 'Codos abriéndose · Movimiento desde hombro · Soltar peso arriba',
                  'musculo_primario' => 'Tríceps',
                  'tipo' => 'isolation',
                  'orden' => 6,
                ),
                6 => 
                array (
                  'nombre' => 'Elevacion fronta en polea barra',
                  'series' => 3,
                  'repeticiones' => '10-12',
                  'descanso' => '75s',
                  'rir' => 2,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/elevacion-fronta-en-polea-barra.gif',
                  'notas' => 'Codos ligeramente flexionados. Subí hasta línea de hombros, no más arriba. Bajá controlado, sin caer.',
                  'tecnica_ejecucion' => 'Codos ligeramente flexionados. Subí hasta línea de hombros, no más arriba. Bajá controlado, sin caer.',
                  'errores_comunes' => 'Subir más allá de los hombros · Codos bloqueados rectos · Usar trapecio',
                  'musculo_primario' => 'Hombros',
                  'tipo' => 'isolation',
                  'orden' => 7,
                  'tecnica_intensificacion' => 
                  array (
                    'nombre' => 'Drop Set',
                    'descripcion' => 'En la última serie: bajá ~20-30% el peso y seguí hasta el fallo técnico. Sin descanso entre la serie principal y el drop.',
                  ),
                ),
              ),
            ),
            2 => 
            array (
              'dia_semana' => 'Viernes',
              'grupo_muscular' => 'Gluteos',
              'nombre' => 'Viernes — Gluteos',
              'duracion_estimada_min' => 56,
              'calentamiento' => '5 min cardio suave + movilidad articular general. Total: 8 min.',
              'vuelta_calma' => 'Estiramiento 3-5 min de los grupos trabajados + 5 min caminata suave',
              'ejercicios' => 
              array (
                0 => 
                array (
                  'nombre' => 'Hip thrust con barra',
                  'series' => 4,
                  'repeticiones' => '10',
                  'descanso' => '90s',
                  'rir' => 2,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/hipthrust-con-barra.gif',
                  'notas' => 'Espalda media en el banco. Pies a 90° de la rodilla arriba. Apretar glúteo 1 seg arriba.',
                  'tecnica_ejecucion' => 'Espalda media en el banco. Pies a 90° de la rodilla arriba. Apretar glúteo 1 seg arriba.',
                  'errores_comunes' => 'Hiperextensión lumbar arriba · Pies muy lejos o muy cerca · No apretar glúteo arriba',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'compound',
                  'orden' => 1,
                ),
                1 => 
                array (
                  'nombre' => 'Hipthrust a una pierna con barra',
                  'series' => 4,
                  'repeticiones' => '10',
                  'descanso' => '90s',
                  'rir' => 2,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/hipthrust-a-una-pierna-con-barra.gif',
                  'notas' => 'Espalda apoyada en banco, barra acolchada en caderas. Empujá desde talones, apretá glúteo 1 seg arriba. Costillas abajo, no extender lumbar.',
                  'tecnica_ejecucion' => 'Espalda apoyada en banco, barra acolchada en caderas. Empujá desde talones, apretá glúteo 1 seg arriba. Costillas abajo, no extender lumbar.',
                  'errores_comunes' => 'Levantar la lumbar (no apretar glúteo) · Pies muy adelante · No apretar arriba',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'compound',
                  'orden' => 2,
                ),
                2 => 
                array (
                  'nombre' => 'Abduccion de cadera de pie en maquina',
                  'series' => 3,
                  'repeticiones' => '10-12',
                  'descanso' => '75s',
                  'rir' => 2,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 3,
                ),
                3 => 
                array (
                  'nombre' => 'Abduccion de cadera sentado en maquina',
                  'series' => 3,
                  'repeticiones' => '10-12',
                  'descanso' => '75s',
                  'rir' => 2,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-sentado-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 4,
                ),
                4 => 
                array (
                  'nombre' => 'Aduccion de cadera de pie en maquina',
                  'series' => 3,
                  'repeticiones' => '10-12',
                  'descanso' => '75s',
                  'rir' => 2,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/aduccion-de-cadera-de-pie-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 5,
                ),
                5 => 
                array (
                  'nombre' => 'Aduccion de cadera en polea',
                  'series' => 3,
                  'repeticiones' => '10-12',
                  'descanso' => '75s',
                  'rir' => 2,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/aduccion-de-cadera-en-polea.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 6,
                ),
                6 => 
                array (
                  'nombre' => 'Aduccion de cadera sentado en maquina',
                  'series' => 3,
                  'repeticiones' => '10-12',
                  'descanso' => '75s',
                  'rir' => 2,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/aduccion-de-cadera-sentado-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 7,
                  'tecnica_intensificacion' => 
                  array (
                    'nombre' => 'Drop Set',
                    'descripcion' => 'En la última serie: bajá ~20-30% el peso y seguí hasta el fallo técnico. Sin descanso entre la serie principal y el drop.',
                  ),
                ),
              ),
            ),
            3 => 
            array (
              'dia_semana' => 'Miércoles',
              'grupo_muscular' => 'Piernas',
              'nombre' => 'Miércoles — Piernas',
              'duracion_estimada_min' => 56,
              'calentamiento' => '5 min cardio suave + movilidad articular general. Total: 8 min.',
              'vuelta_calma' => 'Estiramiento 3-5 min de los grupos trabajados + 5 min caminata suave',
              'ejercicios' => 
              array (
                0 => 
                array (
                  'nombre' => 'Sentadilla con barra',
                  'series' => 4,
                  'repeticiones' => '10',
                  'descanso' => '90s',
                  'rir' => 2,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/sentadilla-con-barra.gif',
                  'notas' => 'Pies ancho de hombros. Empujar el piso. Sentarse hacia atrás y abajo.',
                  'tecnica_ejecucion' => 'Pies ancho de hombros. Empujar el piso. Sentarse hacia atrás y abajo. Rodillas en línea con dedos del pie.',
                  'errores_comunes' => 'Rodillas hacia adentro (valgo) · Talones se levantan · Espalda redonda · Profundidad insuficiente',
                  'musculo_primario' => 'Cuádriceps',
                  'tipo' => 'compound',
                  'orden' => 1,
                  'variacion' => 
                  array (
                    'nombre' => 'Sentadilla frontal',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/sentadilla-frontal-en-landmine.gif',
                    'motivo' => 'Énfasis cuádriceps + obliga torso vertical',
                  ),
                ),
                1 => 
                array (
                  'nombre' => 'Hip thrust con barra',
                  'series' => 4,
                  'repeticiones' => '10',
                  'descanso' => '90s',
                  'rir' => 2,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/hipthrust-con-barra.gif',
                  'notas' => 'Espalda media en el banco. Pies a 90° de la rodilla arriba. Apretar glúteo 1 seg arriba.',
                  'tecnica_ejecucion' => 'Espalda media en el banco. Pies a 90° de la rodilla arriba. Apretar glúteo 1 seg arriba.',
                  'errores_comunes' => 'Hiperextensión lumbar arriba · Pies muy lejos o muy cerca · No apretar glúteo arriba',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'compound',
                  'orden' => 2,
                ),
                2 => 
                array (
                  'nombre' => 'Extensión de cuádriceps',
                  'series' => 3,
                  'repeticiones' => '10-12',
                  'descanso' => '75s',
                  'rir' => 2,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/extension-de-piernas-en-maquina.gif',
                  'notas' => 'Espalda apoyada total. Extensión completa arriba 1 seg. Control de bajada.',
                  'tecnica_ejecucion' => 'Espalda apoyada total. Extensión completa arriba 1 seg. Control de bajada.',
                  'errores_comunes' => 'Cargar peso excesivo (estrés patelar) · Asiento mal ajustado · Soltar peso bajando',
                  'musculo_primario' => 'Cuádriceps',
                  'tipo' => 'isolation',
                  'orden' => 3,
                ),
                3 => 
                array (
                  'nombre' => 'Abduccion de cadera de pie en maquina',
                  'series' => 3,
                  'repeticiones' => '10-12',
                  'descanso' => '75s',
                  'rir' => 2,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 4,
                ),
                4 => 
                array (
                  'nombre' => 'Abduccion de cadera sentado en maquina',
                  'series' => 3,
                  'repeticiones' => '10-12',
                  'descanso' => '75s',
                  'rir' => 2,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-sentado-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 5,
                ),
                5 => 
                array (
                  'nombre' => 'Aduccion de cadera de pie en maquina',
                  'series' => 3,
                  'repeticiones' => '10-12',
                  'descanso' => '75s',
                  'rir' => 2,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/aduccion-de-cadera-de-pie-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 6,
                ),
                6 => 
                array (
                  'nombre' => 'Presa de piernas abierto',
                  'series' => 3,
                  'repeticiones' => '10-12',
                  'descanso' => '75s',
                  'rir' => 2,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/presa-de-piernas-abierto.gif',
                  'notas' => 'Codos quietos al lado del cuerpo. Movimiento solo del codo, sin balanceo. Bajá controlado 2s, subí explosivo.',
                  'tecnica_ejecucion' => 'Codos quietos al lado del cuerpo. Movimiento solo del codo, sin balanceo. Bajá controlado 2s, subí explosivo.',
                  'errores_comunes' => 'Balancear el cuerpo · Codos se mueven · Rango parcial',
                  'musculo_primario' => 'Cuádriceps',
                  'tipo' => 'isolation',
                  'orden' => 7,
                  'tecnica_intensificacion' => 
                  array (
                    'nombre' => 'Drop Set',
                    'descripcion' => 'En la última serie: bajá ~20-30% el peso y seguí hasta el fallo técnico. Sin descanso entre la serie principal y el drop.',
                  ),
                ),
              ),
            ),
          ),
        ),
        2 => 
        array (
          'numero' => 3,
          'fase' => 'Fuerza · RIR 1',
          'rir_objetivo' => 1,
          'volumen_pct' => 90,
          'descripcion' => 'Intensificación. RIR 1 — pesos cercanos al máximo, descanso completo entre series. Acá viene la mayor sobrecarga del bloque.',
          'dias' => 
          array (
            0 => 
            array (
              'dia_semana' => 'Lunes',
              'grupo_muscular' => 'Gluteos',
              'nombre' => 'Lunes — Gluteos',
              'duracion_estimada_min' => 69,
              'calentamiento' => '5 min cardio suave + movilidad articular general. Total: 8 min.',
              'vuelta_calma' => 'Estiramiento 3-5 min de los grupos trabajados + 5 min caminata suave',
              'ejercicios' => 
              array (
                0 => 
                array (
                  'nombre' => 'Hip thrust con barra',
                  'series' => 4,
                  'repeticiones' => '6-8',
                  'descanso' => '150s',
                  'rir' => 1,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/hipthrust-con-barra.gif',
                  'notas' => 'Espalda media en el banco. Pies a 90° de la rodilla arriba. Apretar glúteo 1 seg arriba.',
                  'tecnica_ejecucion' => 'Espalda media en el banco. Pies a 90° de la rodilla arriba. Apretar glúteo 1 seg arriba.',
                  'errores_comunes' => 'Hiperextensión lumbar arriba · Pies muy lejos o muy cerca · No apretar glúteo arriba',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'compound',
                  'orden' => 1,
                ),
                1 => 
                array (
                  'nombre' => 'Hipthrust a una pierna con barra',
                  'series' => 4,
                  'repeticiones' => '6-8',
                  'descanso' => '150s',
                  'rir' => 1,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/hipthrust-a-una-pierna-con-barra.gif',
                  'notas' => 'Espalda apoyada en banco, barra acolchada en caderas. Empujá desde talones, apretá glúteo 1 seg arriba. Costillas abajo, no extender lumbar.',
                  'tecnica_ejecucion' => 'Espalda apoyada en banco, barra acolchada en caderas. Empujá desde talones, apretá glúteo 1 seg arriba. Costillas abajo, no extender lumbar.',
                  'errores_comunes' => 'Levantar la lumbar (no apretar glúteo) · Pies muy adelante · No apretar arriba',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'compound',
                  'orden' => 2,
                ),
                2 => 
                array (
                  'nombre' => 'Abduccion de cadera de pie en maquina',
                  'series' => 3,
                  'repeticiones' => '8-10',
                  'descanso' => '90s',
                  'rir' => 1,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 3,
                ),
                3 => 
                array (
                  'nombre' => 'Abduccion de cadera sentado en maquina',
                  'series' => 3,
                  'repeticiones' => '8-10',
                  'descanso' => '90s',
                  'rir' => 1,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-sentado-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 4,
                ),
                4 => 
                array (
                  'nombre' => 'Aduccion de cadera de pie en maquina',
                  'series' => 3,
                  'repeticiones' => '8-10',
                  'descanso' => '90s',
                  'rir' => 1,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/aduccion-de-cadera-de-pie-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 5,
                ),
                5 => 
                array (
                  'nombre' => 'Aduccion de cadera en polea',
                  'series' => 3,
                  'repeticiones' => '8-10',
                  'descanso' => '90s',
                  'rir' => 1,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/aduccion-de-cadera-en-polea.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 6,
                ),
                6 => 
                array (
                  'nombre' => 'Aduccion de cadera sentado en maquina',
                  'series' => 3,
                  'repeticiones' => '8-10',
                  'descanso' => '90s',
                  'rir' => 1,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/aduccion-de-cadera-sentado-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 7,
                  'tecnica_intensificacion' => 
                  array (
                    'nombre' => 'Cluster Set',
                    'descripcion' => 'Última serie: hacé 2 reps, descansá 20s, 2 reps más, 20s, 2 reps más. Carga alta con técnica controlada.',
                  ),
                ),
              ),
            ),
            1 => 
            array (
              'dia_semana' => 'Martes',
              'grupo_muscular' => 'Espalda + Triceps + Hombros + Biceps',
              'nombre' => 'Martes — Espalda + Triceps + Hombros + Biceps',
              'duracion_estimada_min' => 69,
              'calentamiento' => '5 min remo o caminadora + rotaciones de hombro 2×15 + 1×15 push-up rodillas + 1×10 face-pull con banda. Total: 8 min.',
              'vuelta_calma' => 'Estiramiento 3-5 min de los grupos trabajados + 5 min caminata suave',
              'ejercicios' => 
              array (
                0 => 
                array (
                  'nombre' => 'Dominadas asistidas (banda o máquina)',
                  'series' => 4,
                  'repeticiones' => '6-8',
                  'descanso' => '150s',
                  'rir' => 1,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/dominadas.gif',
                  'notas' => 'Reducir banda cada 2-3 semanas. Mantener mismo rango que dominada libre.',
                  'tecnica_ejecucion' => 'Reducir banda cada 2-3 semanas. Mantener mismo rango que dominada libre.',
                  'errores_comunes' => 'Confiar demasiado en la banda · No reducir asistencia progresivamente',
                  'musculo_primario' => 'Espalda',
                  'tipo' => 'compound',
                  'orden' => 1,
                ),
                1 => 
                array (
                  'nombre' => 'Fondos de triceps en maquina',
                  'series' => 4,
                  'repeticiones' => '6-8',
                  'descanso' => '150s',
                  'rir' => 1,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/fondos-de-triceps-en-maquina.gif',
                  'notas' => 'Escápulas retraídas y abajo. Bajá 2-3 seg controlado, empujá explosivo. Codos 45° del torso, no abiertos a 90°.',
                  'tecnica_ejecucion' => 'Escápulas retraídas y abajo. Bajá 2-3 seg controlado, empujá explosivo. Codos 45° del torso, no abiertos a 90°.',
                  'errores_comunes' => 'Codos abiertos a 90° · Rebotar la barra en el pecho · Rango parcial sin tocar pecho',
                  'musculo_primario' => 'Tríceps',
                  'tipo' => 'compound',
                  'orden' => 2,
                ),
                2 => 
                array (
                  'nombre' => 'Elevaciones laterales con mancuernas',
                  'series' => 3,
                  'repeticiones' => '8-10',
                  'descanso' => '90s',
                  'rir' => 1,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/elevacion-lateral-con-mancuerna.gif',
                  'notas' => 'Codo ligeramente flexionado y fijo. Subir hasta paralelo al piso. Control de bajada en 2 segundos.',
                  'tecnica_ejecucion' => 'Codo ligeramente flexionado y fijo. Subir hasta paralelo al piso. Control de bajada en 2 segundos.',
                  'errores_comunes' => 'Subir por encima de paralelo (involucra trapecio) · Balanceo · Mancuernas muy pesadas',
                  'musculo_primario' => 'Hombros',
                  'tipo' => 'isolation',
                  'orden' => 3,
                ),
                3 => 
                array (
                  'nombre' => 'Curl de bíceps con mancuernas',
                  'series' => 3,
                  'repeticiones' => '8-10',
                  'descanso' => '90s',
                  'rir' => 1,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/curl-biceps-con-mancuerna.gif',
                  'notas' => 'Codos pegados al torso. Supinación completa arriba. Bajada controlada.',
                  'tecnica_ejecucion' => 'Codos pegados al torso. Supinación completa arriba. Bajada controlada.',
                  'errores_comunes' => 'Balanceo (cheating) · Codos adelantados (involucra hombro) · Rango incompleto',
                  'musculo_primario' => 'Bíceps',
                  'tipo' => 'isolation',
                  'orden' => 4,
                  'variacion' => 
                  array (
                    'nombre' => 'Curl de bíceps con barra Z',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/curl-biceps-barra-ez.gif',
                    'motivo' => 'Mayor carga concentrada, menos estrés muñeca',
                  ),
                ),
                4 => 
                array (
                  'nombre' => 'Curl de bíceps con barra Z',
                  'series' => 3,
                  'repeticiones' => '8-10',
                  'descanso' => '90s',
                  'rir' => 1,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/curl-biceps-barra-ez.gif',
                  'notas' => 'Agarre supinado natural en la Z. Sin balanceo. Apretar arriba 1 segundo.',
                  'tecnica_ejecucion' => 'Agarre supinado natural en la Z. Sin balanceo. Apretar arriba 1 segundo.',
                  'errores_comunes' => 'Balanceo de cadera · Codos al frente · Rango parcial',
                  'musculo_primario' => 'Bíceps',
                  'tipo' => 'isolation',
                  'orden' => 5,
                ),
                5 => 
                array (
                  'nombre' => 'Extensión de tríceps en polea',
                  'series' => 3,
                  'repeticiones' => '8-10',
                  'descanso' => '90s',
                  'rir' => 1,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/extension-de-triceps-en-polea-con-cuerda.gif',
                  'notas' => 'Codos pegados al torso fijos. Solo se mueve antebrazo. Extensión completa abajo.',
                  'tecnica_ejecucion' => 'Codos pegados al torso fijos. Solo se mueve antebrazo. Extensión completa abajo.',
                  'errores_comunes' => 'Codos abriéndose · Movimiento desde hombro · Soltar peso arriba',
                  'musculo_primario' => 'Tríceps',
                  'tipo' => 'isolation',
                  'orden' => 6,
                ),
                6 => 
                array (
                  'nombre' => 'Elevacion fronta en polea barra',
                  'series' => 3,
                  'repeticiones' => '8-10',
                  'descanso' => '90s',
                  'rir' => 1,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/elevacion-fronta-en-polea-barra.gif',
                  'notas' => 'Codos ligeramente flexionados. Subí hasta línea de hombros, no más arriba. Bajá controlado, sin caer.',
                  'tecnica_ejecucion' => 'Codos ligeramente flexionados. Subí hasta línea de hombros, no más arriba. Bajá controlado, sin caer.',
                  'errores_comunes' => 'Subir más allá de los hombros · Codos bloqueados rectos · Usar trapecio',
                  'musculo_primario' => 'Hombros',
                  'tipo' => 'isolation',
                  'orden' => 7,
                  'tecnica_intensificacion' => 
                  array (
                    'nombre' => 'Cluster Set',
                    'descripcion' => 'Última serie: hacé 2 reps, descansá 20s, 2 reps más, 20s, 2 reps más. Carga alta con técnica controlada.',
                  ),
                ),
              ),
            ),
            2 => 
            array (
              'dia_semana' => 'Viernes',
              'grupo_muscular' => 'Gluteos',
              'nombre' => 'Viernes — Gluteos',
              'duracion_estimada_min' => 69,
              'calentamiento' => '5 min cardio suave + movilidad articular general. Total: 8 min.',
              'vuelta_calma' => 'Estiramiento 3-5 min de los grupos trabajados + 5 min caminata suave',
              'ejercicios' => 
              array (
                0 => 
                array (
                  'nombre' => 'Hip thrust con barra',
                  'series' => 4,
                  'repeticiones' => '6-8',
                  'descanso' => '150s',
                  'rir' => 1,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/hipthrust-con-barra.gif',
                  'notas' => 'Espalda media en el banco. Pies a 90° de la rodilla arriba. Apretar glúteo 1 seg arriba.',
                  'tecnica_ejecucion' => 'Espalda media en el banco. Pies a 90° de la rodilla arriba. Apretar glúteo 1 seg arriba.',
                  'errores_comunes' => 'Hiperextensión lumbar arriba · Pies muy lejos o muy cerca · No apretar glúteo arriba',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'compound',
                  'orden' => 1,
                ),
                1 => 
                array (
                  'nombre' => 'Hipthrust a una pierna con barra',
                  'series' => 4,
                  'repeticiones' => '6-8',
                  'descanso' => '150s',
                  'rir' => 1,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/hipthrust-a-una-pierna-con-barra.gif',
                  'notas' => 'Espalda apoyada en banco, barra acolchada en caderas. Empujá desde talones, apretá glúteo 1 seg arriba. Costillas abajo, no extender lumbar.',
                  'tecnica_ejecucion' => 'Espalda apoyada en banco, barra acolchada en caderas. Empujá desde talones, apretá glúteo 1 seg arriba. Costillas abajo, no extender lumbar.',
                  'errores_comunes' => 'Levantar la lumbar (no apretar glúteo) · Pies muy adelante · No apretar arriba',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'compound',
                  'orden' => 2,
                ),
                2 => 
                array (
                  'nombre' => 'Abduccion de cadera de pie en maquina',
                  'series' => 3,
                  'repeticiones' => '8-10',
                  'descanso' => '90s',
                  'rir' => 1,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 3,
                ),
                3 => 
                array (
                  'nombre' => 'Abduccion de cadera sentado en maquina',
                  'series' => 3,
                  'repeticiones' => '8-10',
                  'descanso' => '90s',
                  'rir' => 1,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-sentado-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 4,
                ),
                4 => 
                array (
                  'nombre' => 'Aduccion de cadera de pie en maquina',
                  'series' => 3,
                  'repeticiones' => '8-10',
                  'descanso' => '90s',
                  'rir' => 1,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/aduccion-de-cadera-de-pie-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 5,
                ),
                5 => 
                array (
                  'nombre' => 'Aduccion de cadera en polea',
                  'series' => 3,
                  'repeticiones' => '8-10',
                  'descanso' => '90s',
                  'rir' => 1,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/aduccion-de-cadera-en-polea.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 6,
                ),
                6 => 
                array (
                  'nombre' => 'Aduccion de cadera sentado en maquina',
                  'series' => 3,
                  'repeticiones' => '8-10',
                  'descanso' => '90s',
                  'rir' => 1,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/aduccion-de-cadera-sentado-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 7,
                  'tecnica_intensificacion' => 
                  array (
                    'nombre' => 'Cluster Set',
                    'descripcion' => 'Última serie: hacé 2 reps, descansá 20s, 2 reps más, 20s, 2 reps más. Carga alta con técnica controlada.',
                  ),
                ),
              ),
            ),
            3 => 
            array (
              'dia_semana' => 'Miércoles',
              'grupo_muscular' => 'Piernas',
              'nombre' => 'Miércoles — Piernas',
              'duracion_estimada_min' => 69,
              'calentamiento' => '5 min cardio suave + movilidad articular general. Total: 8 min.',
              'vuelta_calma' => 'Estiramiento 3-5 min de los grupos trabajados + 5 min caminata suave',
              'ejercicios' => 
              array (
                0 => 
                array (
                  'nombre' => 'Sentadilla con barra',
                  'series' => 4,
                  'repeticiones' => '6-8',
                  'descanso' => '150s',
                  'rir' => 1,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/sentadilla-con-barra.gif',
                  'notas' => 'Pies ancho de hombros. Empujar el piso. Sentarse hacia atrás y abajo.',
                  'tecnica_ejecucion' => 'Pies ancho de hombros. Empujar el piso. Sentarse hacia atrás y abajo. Rodillas en línea con dedos del pie.',
                  'errores_comunes' => 'Rodillas hacia adentro (valgo) · Talones se levantan · Espalda redonda · Profundidad insuficiente',
                  'musculo_primario' => 'Cuádriceps',
                  'tipo' => 'compound',
                  'orden' => 1,
                  'variacion' => 
                  array (
                    'nombre' => 'Sentadilla frontal',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/sentadilla-frontal-en-landmine.gif',
                    'motivo' => 'Énfasis cuádriceps + obliga torso vertical',
                  ),
                ),
                1 => 
                array (
                  'nombre' => 'Hip thrust con barra',
                  'series' => 4,
                  'repeticiones' => '6-8',
                  'descanso' => '150s',
                  'rir' => 1,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/hipthrust-con-barra.gif',
                  'notas' => 'Espalda media en el banco. Pies a 90° de la rodilla arriba. Apretar glúteo 1 seg arriba.',
                  'tecnica_ejecucion' => 'Espalda media en el banco. Pies a 90° de la rodilla arriba. Apretar glúteo 1 seg arriba.',
                  'errores_comunes' => 'Hiperextensión lumbar arriba · Pies muy lejos o muy cerca · No apretar glúteo arriba',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'compound',
                  'orden' => 2,
                ),
                2 => 
                array (
                  'nombre' => 'Extensión de cuádriceps',
                  'series' => 3,
                  'repeticiones' => '8-10',
                  'descanso' => '90s',
                  'rir' => 1,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/extension-de-piernas-en-maquina.gif',
                  'notas' => 'Espalda apoyada total. Extensión completa arriba 1 seg. Control de bajada.',
                  'tecnica_ejecucion' => 'Espalda apoyada total. Extensión completa arriba 1 seg. Control de bajada.',
                  'errores_comunes' => 'Cargar peso excesivo (estrés patelar) · Asiento mal ajustado · Soltar peso bajando',
                  'musculo_primario' => 'Cuádriceps',
                  'tipo' => 'isolation',
                  'orden' => 3,
                ),
                3 => 
                array (
                  'nombre' => 'Abduccion de cadera de pie en maquina',
                  'series' => 3,
                  'repeticiones' => '8-10',
                  'descanso' => '90s',
                  'rir' => 1,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 4,
                ),
                4 => 
                array (
                  'nombre' => 'Abduccion de cadera sentado en maquina',
                  'series' => 3,
                  'repeticiones' => '8-10',
                  'descanso' => '90s',
                  'rir' => 1,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-sentado-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 5,
                ),
                5 => 
                array (
                  'nombre' => 'Aduccion de cadera de pie en maquina',
                  'series' => 3,
                  'repeticiones' => '8-10',
                  'descanso' => '90s',
                  'rir' => 1,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/aduccion-de-cadera-de-pie-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 6,
                ),
                6 => 
                array (
                  'nombre' => 'Presa de piernas abierto',
                  'series' => 3,
                  'repeticiones' => '8-10',
                  'descanso' => '90s',
                  'rir' => 1,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/presa-de-piernas-abierto.gif',
                  'notas' => 'Codos quietos al lado del cuerpo. Movimiento solo del codo, sin balanceo. Bajá controlado 2s, subí explosivo.',
                  'tecnica_ejecucion' => 'Codos quietos al lado del cuerpo. Movimiento solo del codo, sin balanceo. Bajá controlado 2s, subí explosivo.',
                  'errores_comunes' => 'Balancear el cuerpo · Codos se mueven · Rango parcial',
                  'musculo_primario' => 'Cuádriceps',
                  'tipo' => 'isolation',
                  'orden' => 7,
                  'tecnica_intensificacion' => 
                  array (
                    'nombre' => 'Cluster Set',
                    'descripcion' => 'Última serie: hacé 2 reps, descansá 20s, 2 reps más, 20s, 2 reps más. Carga alta con técnica controlada.',
                  ),
                ),
              ),
            ),
          ),
        ),
        3 => 
        array (
          'numero' => 4,
          'fase' => 'Peak · RIR 0',
          'rir_objetivo' => 0,
          'volumen_pct' => 75,
          'descripcion' => 'Peak del bloque. RIR 0 — máximo esfuerzo controlado. Si no llegás al RIR objetivo, mantené peso y mejorá ejecución.',
          'dias' => 
          array (
            0 => 
            array (
              'dia_semana' => 'Lunes',
              'grupo_muscular' => 'Gluteos',
              'nombre' => 'Lunes — Gluteos',
              'duracion_estimada_min' => 86,
              'calentamiento' => '5 min cardio suave + movilidad articular general. Total: 8 min.',
              'vuelta_calma' => 'Estiramiento 3-5 min de los grupos trabajados + 5 min caminata suave',
              'ejercicios' => 
              array (
                0 => 
                array (
                  'nombre' => 'Hip thrust con barra',
                  'series' => 5,
                  'repeticiones' => '3-5',
                  'descanso' => '180s',
                  'rir' => 0,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/hipthrust-con-barra.gif',
                  'notas' => 'Espalda media en el banco. Pies a 90° de la rodilla arriba. Apretar glúteo 1 seg arriba.',
                  'tecnica_ejecucion' => 'Espalda media en el banco. Pies a 90° de la rodilla arriba. Apretar glúteo 1 seg arriba.',
                  'errores_comunes' => 'Hiperextensión lumbar arriba · Pies muy lejos o muy cerca · No apretar glúteo arriba',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'compound',
                  'orden' => 1,
                ),
                1 => 
                array (
                  'nombre' => 'Hipthrust a una pierna con barra',
                  'series' => 5,
                  'repeticiones' => '3-5',
                  'descanso' => '180s',
                  'rir' => 0,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/hipthrust-a-una-pierna-con-barra.gif',
                  'notas' => 'Espalda apoyada en banco, barra acolchada en caderas. Empujá desde talones, apretá glúteo 1 seg arriba. Costillas abajo, no extender lumbar.',
                  'tecnica_ejecucion' => 'Espalda apoyada en banco, barra acolchada en caderas. Empujá desde talones, apretá glúteo 1 seg arriba. Costillas abajo, no extender lumbar.',
                  'errores_comunes' => 'Levantar la lumbar (no apretar glúteo) · Pies muy adelante · No apretar arriba',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'compound',
                  'orden' => 2,
                ),
                2 => 
                array (
                  'nombre' => 'Abduccion de cadera de pie en maquina',
                  'series' => 4,
                  'repeticiones' => '8-10',
                  'descanso' => '75s',
                  'rir' => 0,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 3,
                ),
                3 => 
                array (
                  'nombre' => 'Abduccion de cadera sentado en maquina',
                  'series' => 4,
                  'repeticiones' => '8-10',
                  'descanso' => '75s',
                  'rir' => 0,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-sentado-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 4,
                ),
                4 => 
                array (
                  'nombre' => 'Aduccion de cadera de pie en maquina',
                  'series' => 4,
                  'repeticiones' => '8-10',
                  'descanso' => '75s',
                  'rir' => 0,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/aduccion-de-cadera-de-pie-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 5,
                ),
                5 => 
                array (
                  'nombre' => 'Aduccion de cadera en polea',
                  'series' => 4,
                  'repeticiones' => '8-10',
                  'descanso' => '75s',
                  'rir' => 0,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/aduccion-de-cadera-en-polea.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 6,
                ),
                6 => 
                array (
                  'nombre' => 'Aduccion de cadera sentado en maquina',
                  'series' => 4,
                  'repeticiones' => '8-10',
                  'descanso' => '75s',
                  'rir' => 0,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/aduccion-de-cadera-sentado-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 7,
                  'tecnica_intensificacion' => 
                  array (
                    'nombre' => 'Rest-Pause',
                    'descripcion' => 'Última serie al RIR 0. Descansá 15s y hacé 3-5 reps más. Repetí 2× total. Acumulás reps sin agregar más series.',
                  ),
                ),
              ),
            ),
            1 => 
            array (
              'dia_semana' => 'Martes',
              'grupo_muscular' => 'Espalda + Triceps + Hombros + Biceps',
              'nombre' => 'Martes — Espalda + Triceps + Hombros + Biceps',
              'duracion_estimada_min' => 86,
              'calentamiento' => '5 min remo o caminadora + rotaciones de hombro 2×15 + 1×15 push-up rodillas + 1×10 face-pull con banda. Total: 8 min.',
              'vuelta_calma' => 'Estiramiento 3-5 min de los grupos trabajados + 5 min caminata suave',
              'ejercicios' => 
              array (
                0 => 
                array (
                  'nombre' => 'Dominadas asistidas (banda o máquina)',
                  'series' => 5,
                  'repeticiones' => '3-5',
                  'descanso' => '180s',
                  'rir' => 0,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/dominadas.gif',
                  'notas' => 'Reducir banda cada 2-3 semanas. Mantener mismo rango que dominada libre.',
                  'tecnica_ejecucion' => 'Reducir banda cada 2-3 semanas. Mantener mismo rango que dominada libre.',
                  'errores_comunes' => 'Confiar demasiado en la banda · No reducir asistencia progresivamente',
                  'musculo_primario' => 'Espalda',
                  'tipo' => 'compound',
                  'orden' => 1,
                ),
                1 => 
                array (
                  'nombre' => 'Fondos de triceps en maquina',
                  'series' => 5,
                  'repeticiones' => '3-5',
                  'descanso' => '180s',
                  'rir' => 0,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/fondos-de-triceps-en-maquina.gif',
                  'notas' => 'Escápulas retraídas y abajo. Bajá 2-3 seg controlado, empujá explosivo. Codos 45° del torso, no abiertos a 90°.',
                  'tecnica_ejecucion' => 'Escápulas retraídas y abajo. Bajá 2-3 seg controlado, empujá explosivo. Codos 45° del torso, no abiertos a 90°.',
                  'errores_comunes' => 'Codos abiertos a 90° · Rebotar la barra en el pecho · Rango parcial sin tocar pecho',
                  'musculo_primario' => 'Tríceps',
                  'tipo' => 'compound',
                  'orden' => 2,
                ),
                2 => 
                array (
                  'nombre' => 'Elevaciones laterales con mancuernas',
                  'series' => 4,
                  'repeticiones' => '8-10',
                  'descanso' => '75s',
                  'rir' => 0,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/elevacion-lateral-con-mancuerna.gif',
                  'notas' => 'Codo ligeramente flexionado y fijo. Subir hasta paralelo al piso. Control de bajada en 2 segundos.',
                  'tecnica_ejecucion' => 'Codo ligeramente flexionado y fijo. Subir hasta paralelo al piso. Control de bajada en 2 segundos.',
                  'errores_comunes' => 'Subir por encima de paralelo (involucra trapecio) · Balanceo · Mancuernas muy pesadas',
                  'musculo_primario' => 'Hombros',
                  'tipo' => 'isolation',
                  'orden' => 3,
                ),
                3 => 
                array (
                  'nombre' => 'Curl de bíceps con mancuernas',
                  'series' => 4,
                  'repeticiones' => '8-10',
                  'descanso' => '75s',
                  'rir' => 0,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/curl-biceps-con-mancuerna.gif',
                  'notas' => 'Codos pegados al torso. Supinación completa arriba. Bajada controlada.',
                  'tecnica_ejecucion' => 'Codos pegados al torso. Supinación completa arriba. Bajada controlada.',
                  'errores_comunes' => 'Balanceo (cheating) · Codos adelantados (involucra hombro) · Rango incompleto',
                  'musculo_primario' => 'Bíceps',
                  'tipo' => 'isolation',
                  'orden' => 4,
                  'variacion' => 
                  array (
                    'nombre' => 'Curl de bíceps con barra Z',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/curl-biceps-barra-ez.gif',
                    'motivo' => 'Mayor carga concentrada, menos estrés muñeca',
                  ),
                ),
                4 => 
                array (
                  'nombre' => 'Curl de bíceps con barra Z',
                  'series' => 4,
                  'repeticiones' => '8-10',
                  'descanso' => '75s',
                  'rir' => 0,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/curl-biceps-barra-ez.gif',
                  'notas' => 'Agarre supinado natural en la Z. Sin balanceo. Apretar arriba 1 segundo.',
                  'tecnica_ejecucion' => 'Agarre supinado natural en la Z. Sin balanceo. Apretar arriba 1 segundo.',
                  'errores_comunes' => 'Balanceo de cadera · Codos al frente · Rango parcial',
                  'musculo_primario' => 'Bíceps',
                  'tipo' => 'isolation',
                  'orden' => 5,
                ),
                5 => 
                array (
                  'nombre' => 'Extensión de tríceps en polea',
                  'series' => 4,
                  'repeticiones' => '8-10',
                  'descanso' => '75s',
                  'rir' => 0,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/extension-de-triceps-en-polea-con-cuerda.gif',
                  'notas' => 'Codos pegados al torso fijos. Solo se mueve antebrazo. Extensión completa abajo.',
                  'tecnica_ejecucion' => 'Codos pegados al torso fijos. Solo se mueve antebrazo. Extensión completa abajo.',
                  'errores_comunes' => 'Codos abriéndose · Movimiento desde hombro · Soltar peso arriba',
                  'musculo_primario' => 'Tríceps',
                  'tipo' => 'isolation',
                  'orden' => 6,
                ),
                6 => 
                array (
                  'nombre' => 'Elevacion fronta en polea barra',
                  'series' => 4,
                  'repeticiones' => '8-10',
                  'descanso' => '75s',
                  'rir' => 0,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/elevacion-fronta-en-polea-barra.gif',
                  'notas' => 'Codos ligeramente flexionados. Subí hasta línea de hombros, no más arriba. Bajá controlado, sin caer.',
                  'tecnica_ejecucion' => 'Codos ligeramente flexionados. Subí hasta línea de hombros, no más arriba. Bajá controlado, sin caer.',
                  'errores_comunes' => 'Subir más allá de los hombros · Codos bloqueados rectos · Usar trapecio',
                  'musculo_primario' => 'Hombros',
                  'tipo' => 'isolation',
                  'orden' => 7,
                  'tecnica_intensificacion' => 
                  array (
                    'nombre' => 'Rest-Pause',
                    'descripcion' => 'Última serie al RIR 0. Descansá 15s y hacé 3-5 reps más. Repetí 2× total. Acumulás reps sin agregar más series.',
                  ),
                ),
              ),
            ),
            2 => 
            array (
              'dia_semana' => 'Viernes',
              'grupo_muscular' => 'Gluteos',
              'nombre' => 'Viernes — Gluteos',
              'duracion_estimada_min' => 86,
              'calentamiento' => '5 min cardio suave + movilidad articular general. Total: 8 min.',
              'vuelta_calma' => 'Estiramiento 3-5 min de los grupos trabajados + 5 min caminata suave',
              'ejercicios' => 
              array (
                0 => 
                array (
                  'nombre' => 'Hip thrust con barra',
                  'series' => 5,
                  'repeticiones' => '3-5',
                  'descanso' => '180s',
                  'rir' => 0,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/hipthrust-con-barra.gif',
                  'notas' => 'Espalda media en el banco. Pies a 90° de la rodilla arriba. Apretar glúteo 1 seg arriba.',
                  'tecnica_ejecucion' => 'Espalda media en el banco. Pies a 90° de la rodilla arriba. Apretar glúteo 1 seg arriba.',
                  'errores_comunes' => 'Hiperextensión lumbar arriba · Pies muy lejos o muy cerca · No apretar glúteo arriba',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'compound',
                  'orden' => 1,
                ),
                1 => 
                array (
                  'nombre' => 'Hipthrust a una pierna con barra',
                  'series' => 5,
                  'repeticiones' => '3-5',
                  'descanso' => '180s',
                  'rir' => 0,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/hipthrust-a-una-pierna-con-barra.gif',
                  'notas' => 'Espalda apoyada en banco, barra acolchada en caderas. Empujá desde talones, apretá glúteo 1 seg arriba. Costillas abajo, no extender lumbar.',
                  'tecnica_ejecucion' => 'Espalda apoyada en banco, barra acolchada en caderas. Empujá desde talones, apretá glúteo 1 seg arriba. Costillas abajo, no extender lumbar.',
                  'errores_comunes' => 'Levantar la lumbar (no apretar glúteo) · Pies muy adelante · No apretar arriba',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'compound',
                  'orden' => 2,
                ),
                2 => 
                array (
                  'nombre' => 'Abduccion de cadera de pie en maquina',
                  'series' => 4,
                  'repeticiones' => '8-10',
                  'descanso' => '75s',
                  'rir' => 0,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 3,
                ),
                3 => 
                array (
                  'nombre' => 'Abduccion de cadera sentado en maquina',
                  'series' => 4,
                  'repeticiones' => '8-10',
                  'descanso' => '75s',
                  'rir' => 0,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-sentado-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 4,
                ),
                4 => 
                array (
                  'nombre' => 'Aduccion de cadera de pie en maquina',
                  'series' => 4,
                  'repeticiones' => '8-10',
                  'descanso' => '75s',
                  'rir' => 0,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/aduccion-de-cadera-de-pie-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 5,
                ),
                5 => 
                array (
                  'nombre' => 'Aduccion de cadera en polea',
                  'series' => 4,
                  'repeticiones' => '8-10',
                  'descanso' => '75s',
                  'rir' => 0,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/aduccion-de-cadera-en-polea.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 6,
                ),
                6 => 
                array (
                  'nombre' => 'Aduccion de cadera sentado en maquina',
                  'series' => 4,
                  'repeticiones' => '8-10',
                  'descanso' => '75s',
                  'rir' => 0,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/aduccion-de-cadera-sentado-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 7,
                  'tecnica_intensificacion' => 
                  array (
                    'nombre' => 'Rest-Pause',
                    'descripcion' => 'Última serie al RIR 0. Descansá 15s y hacé 3-5 reps más. Repetí 2× total. Acumulás reps sin agregar más series.',
                  ),
                ),
              ),
            ),
            3 => 
            array (
              'dia_semana' => 'Miércoles',
              'grupo_muscular' => 'Piernas',
              'nombre' => 'Miércoles — Piernas',
              'duracion_estimada_min' => 86,
              'calentamiento' => '5 min cardio suave + movilidad articular general. Total: 8 min.',
              'vuelta_calma' => 'Estiramiento 3-5 min de los grupos trabajados + 5 min caminata suave',
              'ejercicios' => 
              array (
                0 => 
                array (
                  'nombre' => 'Sentadilla con barra',
                  'series' => 5,
                  'repeticiones' => '3-5',
                  'descanso' => '180s',
                  'rir' => 0,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/sentadilla-con-barra.gif',
                  'notas' => 'Pies ancho de hombros. Empujar el piso. Sentarse hacia atrás y abajo.',
                  'tecnica_ejecucion' => 'Pies ancho de hombros. Empujar el piso. Sentarse hacia atrás y abajo. Rodillas en línea con dedos del pie.',
                  'errores_comunes' => 'Rodillas hacia adentro (valgo) · Talones se levantan · Espalda redonda · Profundidad insuficiente',
                  'musculo_primario' => 'Cuádriceps',
                  'tipo' => 'compound',
                  'orden' => 1,
                  'variacion' => 
                  array (
                    'nombre' => 'Sentadilla frontal',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/sentadilla-frontal-en-landmine.gif',
                    'motivo' => 'Énfasis cuádriceps + obliga torso vertical',
                  ),
                ),
                1 => 
                array (
                  'nombre' => 'Hip thrust con barra',
                  'series' => 5,
                  'repeticiones' => '3-5',
                  'descanso' => '180s',
                  'rir' => 0,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/hipthrust-con-barra.gif',
                  'notas' => 'Espalda media en el banco. Pies a 90° de la rodilla arriba. Apretar glúteo 1 seg arriba.',
                  'tecnica_ejecucion' => 'Espalda media en el banco. Pies a 90° de la rodilla arriba. Apretar glúteo 1 seg arriba.',
                  'errores_comunes' => 'Hiperextensión lumbar arriba · Pies muy lejos o muy cerca · No apretar glúteo arriba',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'compound',
                  'orden' => 2,
                ),
                2 => 
                array (
                  'nombre' => 'Extensión de cuádriceps',
                  'series' => 4,
                  'repeticiones' => '8-10',
                  'descanso' => '75s',
                  'rir' => 0,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/extension-de-piernas-en-maquina.gif',
                  'notas' => 'Espalda apoyada total. Extensión completa arriba 1 seg. Control de bajada.',
                  'tecnica_ejecucion' => 'Espalda apoyada total. Extensión completa arriba 1 seg. Control de bajada.',
                  'errores_comunes' => 'Cargar peso excesivo (estrés patelar) · Asiento mal ajustado · Soltar peso bajando',
                  'musculo_primario' => 'Cuádriceps',
                  'tipo' => 'isolation',
                  'orden' => 3,
                ),
                3 => 
                array (
                  'nombre' => 'Abduccion de cadera de pie en maquina',
                  'series' => 4,
                  'repeticiones' => '8-10',
                  'descanso' => '75s',
                  'rir' => 0,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 4,
                ),
                4 => 
                array (
                  'nombre' => 'Abduccion de cadera sentado en maquina',
                  'series' => 4,
                  'repeticiones' => '8-10',
                  'descanso' => '75s',
                  'rir' => 0,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-sentado-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 5,
                ),
                5 => 
                array (
                  'nombre' => 'Aduccion de cadera de pie en maquina',
                  'series' => 4,
                  'repeticiones' => '8-10',
                  'descanso' => '75s',
                  'rir' => 0,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/aduccion-de-cadera-de-pie-en-maquina.gif',
                  'notas' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'tecnica_ejecucion' => 'Cadera estable, no rotes el tronco. Apretá el glúteo 1 segundo en el pico del movimiento. Movimiento controlado, sin balanceo ni momentum.',
                  'errores_comunes' => 'Balancear el tronco · No apretar arriba · Rango parcial · Hiperextender lumbar',
                  'musculo_primario' => 'Glúteo',
                  'tipo' => 'isolation',
                  'orden' => 6,
                ),
                6 => 
                array (
                  'nombre' => 'Presa de piernas abierto',
                  'series' => 4,
                  'repeticiones' => '8-10',
                  'descanso' => '75s',
                  'rir' => 0,
                  'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/presa-de-piernas-abierto.gif',
                  'notas' => 'Codos quietos al lado del cuerpo. Movimiento solo del codo, sin balanceo. Bajá controlado 2s, subí explosivo.',
                  'tecnica_ejecucion' => 'Codos quietos al lado del cuerpo. Movimiento solo del codo, sin balanceo. Bajá controlado 2s, subí explosivo.',
                  'errores_comunes' => 'Balancear el cuerpo · Codos se mueven · Rango parcial',
                  'musculo_primario' => 'Cuádriceps',
                  'tipo' => 'isolation',
                  'orden' => 7,
                  'tecnica_intensificacion' => 
                  array (
                    'nombre' => 'Rest-Pause',
                    'descripcion' => 'Última serie al RIR 0. Descansá 15s y hacé 3-5 reps más. Repetí 2× total. Acumulás reps sin agregar más series.',
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    ),
  ),
  1 => 
  array (
    'composed_id' => 12,
    'plan_type' => 'nutricion',
    'methodology_slug' => 'iifym_deficit',
    'plan_json' => 
    array (
      'plan_type' => 'nutricion',
      'titulo' => 'Plan IIFYM con déficit calórico moderado — Karen Vanessa Gómez Lagos',
      'objetivo' => 'Pérdida de grasa con preservación muscular — déficit moderado de 400 kcal sobre tu TDEE (2194 kcal/día). Vas a comer 1794 kcal/día con proteína alta (2.4 g/kg = 166g) para mantener masa magra. Meta: bajar 0.5-1 kg/semana después de la semana 2.',
      'metodologia' => 'IIFYM con déficit calórico moderado',
      'duracion_semanas' => 4,
      'fecha_inicio' => '2026-06-01',
      'objetivo_cal' => 1794,
      'macros' => 
      array (
        'proteina_g' => 166,
        'carbohidratos_g' => 143,
        'grasas_g' => 62,
      ),
      'tdee_calculado' => 2194,
      'bmr_calculado' => 1415,
      'hidratacion' => 
      array (
        'agua_minima_litros' => 2.4,
        'agua_total_dia_entreno_litros' => 2.9,
        'electrolitos' => 'En déficit, sumá una pizca de sal en agua si entrenás >45 min o tomás cardio extra. Ayuda a evitar bajón energético.',
        'notas' => 'Tu peso × 0.035 = 2.4 L mínimo diario. Sumá 500 ml extra los días de entreno.',
      ),
      'notas_coach' => 'Karen, tu plan nutricional tiene 1794 kcal/día con 166g de proteína (2.4 g/kg). Esto es un déficit de 400 kcal sobre tu TDEE calculado (2194 kcal) — pensado para pérdida de grasa.

El reparto en 4 comidas mantiene proteína constante todo el día. Cada comida tiene 3 opciones equivalentes en macros (±5%) para que no te aburras y puedas cambiar según lo que tengas en casa. Pesá la primera semana en crudo — después calculás a ojo.

Las primeras 2 semanas vas a sentir hambre — es normal, tu cuerpo se ajusta al nuevo aporte calórico. Tomá agua + masticá despacio (20 masticadas por bocado) ayuda. Esperá perder 0.5-1 kg/semana después de la semana 2. Si bajás más rápido, es agua o músculo — avisame y ajustamos.

Empezás mañana. Si llegás tarde a una comida, no te la saltes — sumá su proteína a la siguiente. Hidratate bien (mínimo 35 ml × peso en kg/día). Cualquier antojo nocturno se cubre con té + canela o agua caliente con miel. — Héctor Leonardo Pérez Torres',
      'consejos_coach' => 
      array (
        0 => 'Proteína primero: si cumplís el target diario, el día sigue siendo productivo',
        1 => 'Las 3 opciones por comida son intercambiables — macros equivalentes',
        2 => 'Cocina sin aceite (plancha, horno, vapor). Spray o sartén antiadherente',
        3 => 'Verduras de ensalada = libres. Llenate el plato',
        4 => 'Batch cooking dominical te salva la semana',
        5 => '2.4 L de agua mínimo. El hambre muchas veces es sed',
        6 => 'Café y té libres, sin azúcar',
        7 => 'Antojo nocturno: té con canela o agua caliente con miel (1 cdita)',
        8 => 'En días de entreno: 50 kcal extra carbos. Descanso: -50 kcal',
        9 => 'Cheat meal: 1 comida/semana (no día entero)',
        10 => 'Si el peso no baja 2 semanas seguidas, avisame — ajustamos',
      ),
      'tips' => 
      array (
        0 => 'Tomá mínimo 2.4 L de agua al día (35 ml/kg)',
        1 => 'Si entrenás, tomá la comida pre-entreno 60-90 min antes',
        2 => 'Anotá el peso 1 vez por semana en ayunas, no diario (mucha varianza)',
        3 => 'Las gramaturas son crudo/seco — pesá antes de cocinar',
        4 => 'Si te saltás una comida, sumá su proteína (~33g) a la siguiente',
        5 => 'Saturar MPS (síntesis proteica muscular) con 25-40 g proteína completa. Ventana real: 2-4 h, no 30 min.',
        6 => 'Adultos LATAM consumen 12-15 g de fibra/día. El target 25-35 g mejora saciedad, glucemia y microbiota intestinal.',
        7 => '1 copa de vino = 130 kcal. Una noche social puede sumar 800+ kcal "invisibles" que tumban la semana en déficit.',
      ),
      'principios_aplicados' => 
      array (
        0 => 'post_entreno_30g_proteina',
        1 => 'fibra_25_35g_diaria',
        2 => 'alcohol_kcal_invisible',
      ),
      'comidas' => 
      array (
        0 => 
        array (
          'nombre' => 'Desayuno',
          'hora' => '05:00',
          'subtitulo' => 'Proteína completa + carbo medio + 1 fruta',
          'macros' => 
          array (
            'proteina' => 42,
            'carbohidratos' => 36,
            'grasas' => 16,
          ),
          'kcal_objetivo' => 449,
          'notas' => 'La proteína del desayuno la cocinás sin aceite — usá sartén antiadherente o spray. El carbo y la fruta te dan energía para arrancar el día y entrenar 2-3 horas después.',
          'opcion_a' => 
          array (
            0 => 'Proteína whey concentrada (58g)',
            1 => 'Arroz blanco (crudo) (39g)',
            2 => 'Almendras (26g)',
            3 => 'Papaya (120g)',
          ),
          'opcion_b' => 
          array (
            0 => 'Kumis natural sin azúcar (400g)',
            1 => 'Pan integral (42g)',
            2 => 'Maní (cacahuate) (20g)',
            3 => 'Manzana (120g)',
          ),
          'opcion_c' => 
          array (
            0 => 'Proteína vegetal (arveja/arroz) (56g)',
            1 => 'Avena en hojuelas (48g)',
            2 => 'Aguacate (75g)',
            3 => 'Pera (120g)',
          ),
        ),
        1 => 
        array (
          'nombre' => 'Snack AM',
          'hora' => '10:00',
          'subtitulo' => 'Proteína magra + grasa saludable o fruta',
          'macros' => 
          array (
            'proteina' => 17,
            'carbohidratos' => 14,
            'grasas' => 6,
          ),
          'kcal_objetivo' => 179,
          'notas' => 'Snack pequeño para mantener proteína constante. Si no tenés hambre, sumá esta proteína al desayuno o almuerzo — no te la saltés del todo.',
          'opcion_a' => 
          array (
            0 => 'Queso campesino bajo en grasa (113g)',
            1 => 'Banano (48g)',
          ),
          'opcion_b' => 
          array (
            0 => 'Lentejas cocidas (189g)',
            1 => 'Aceite de coco (20g)',
          ),
          'opcion_c' => 
          array (
            0 => 'Leche deslactosada (entera) (400g)',
          ),
        ),
        2 => 
        array (
          'nombre' => 'Almuerzo',
          'hora' => '13:00',
          'subtitulo' => 'Comida principal — proteína + carbo + verdura',
          'macros' => 
          array (
            'proteina' => 58,
            'carbohidratos' => 50,
            'grasas' => 22,
          ),
          'kcal_objetivo' => 628,
          'notas' => 'Comida más grande del día. Usá aceite de oliva en frío (1 cda) para la ensalada o aguacate, no para freír. La proteína a la plancha, horno o hervida — sin frituras.',
          'opcion_a' => 
          array (
            0 => 'Carne de res molida magra (5% grasa) (271g)',
            1 => 'Galletas de arroz (61g)',
            2 => 'Maní (cacahuate) (20g)',
            3 => 'Coliflor (100g)',
          ),
          'opcion_b' => 
          array (
            0 => 'Carne de res magra — lomo (264g)',
            1 => 'Yuca (cruda) (131g)',
            2 => 'Nueces (20g)',
            3 => 'Espinaca (100g)',
          ),
          'opcion_c' => 
          array (
            0 => 'Atún en agua (lata) (223g)',
            1 => 'Arroz integral (crudo) (65g)',
            2 => 'Aceite de coco (20g)',
            3 => 'Lechuga (100g)',
          ),
        ),
        3 => 
        array (
          'nombre' => 'Cena',
          'hora' => '16:00',
          'subtitulo' => 'Proteína + verdura · ligero · 2-3h antes de dormir',
          'macros' => 
          array (
            'proteina' => 50,
            'carbohidratos' => 43,
            'grasas' => 19,
          ),
          'kcal_objetivo' => 538,
          'notas' => 'Si llegás tarde y con sueño, come solo proteína + verdura — la cena es la comida más flexible. Evitá carbos pesados si dormís dentro de 2h.',
          'opcion_a' => 
          array (
            0 => 'Tofu firme (400g)',
            1 => 'Tostada integral (47g)',
            2 => 'Pepino (100g)',
          ),
          'opcion_b' => 
          array (
            0 => 'Carne de res magra — lomo (227g)',
            1 => 'Pasta integral (cruda) (61g)',
            2 => 'Semillas de chía (20g)',
            3 => 'Champiñones (100g)',
          ),
          'opcion_c' => 
          array (
            0 => 'Claras de huevo (400g)',
            1 => 'Quinoa (cocida) (188g)',
            2 => 'Chocolate negro >85% cacao (33g)',
            3 => 'Calabacín (zucchini) (100g)',
          ),
        ),
      ),
      'plan_dia_descanso' => 
      array (
        'descripcion' => 'En días de descanso (sin entreno) ajustás levemente las calorías porque no quemás extra. Mantenés proteína y bajás un poco los carbos.',
        'calorias_objetivo' => 1644,
        'ajustes' => 
        array (
          0 => 'Reducí ~30g de arroz o pasta en el almuerzo (cambia ~120 kcal)',
          1 => 'Mantené la proteína igual — el músculo se construye también en descanso',
          2 => 'Si tenés snack pre-entreno, sáltalo (esa comida es para tener energía de gym)',
          3 => 'Hidratate igual: el descanso es cuando el cuerpo procesa todo lo trabajado',
        ),
      ),
    ),
  ),
  2 => 
  array (
    'composed_id' => 13,
    'plan_type' => 'suplementacion',
    'methodology_slug' => 'stack_basico',
    'plan_json' => 
    array (
      'plan_type' => 'suplementacion',
      'titulo' => 'Stack de suplementación — Pérdida de grasa femenina intermedia — Karen Vanessa Gómez Lagos',
      'objetivo' => 'Pérdida de grasa con preservación de masa magra para mujer intermedia en déficit moderado/agresivo. Soporte energético pre-entreno + manejo de sueño/cólicos + base proteica fuerte.',
      'metodologia' => 'Stack Básico WellCore',
      'duracion_semanas' => 4,
      'fecha_inicio' => '2026-06-01',
      'stack_info' => 
      array (
        'stack_slug' => 'stack-perdida-grasa-femenina-intermedia',
        'stack_nombre' => 'Pérdida de grasa femenina intermedia',
        'costo_mensual_estimado_cop' => 280000,
        'costo_mensual_rango_cop' => '230000-360000',
      ),
      'notas_coach' => 'El stack está pensado para tu objetivo y nivel actual. Tomá los suplementos en el momento y frecuencia indicados — la consistencia importa más que la dosis exacta. El costo mensual aproximado es de COP $280.000. — Héctor Leonardo Pérez Torres',
      'tips' => 
      array (
        0 => 'La creatina NO necesita fase de carga — 5g diarios desde el día 1 son suficientes',
        1 => 'La proteína whey es opcional si llegás a tu target diario con alimentos enteros',
        2 => 'Si te saltás un día de creatina, no pasa nada — el efecto es por saturación acumulada',
        3 => 'NO mezcles más de un pre-entreno con cafeína al día (riesgo cardiovascular)',
        4 => 'Suspendé cualquier suplemento si aparecen síntomas adversos y avisá al coach',
        5 => 'Vida media cafeína ≈5h. Última dosis ≥8h antes de dormir o el sueño profundo se reduce aunque te duermas igual.',
        6 => 'Ningún suplemento compensa mal sueño, dieta basura o entrenamiento mal hecho. Son la cereza, no el pastel.',
        7 => 'Deficiencia de D3 es endémica en LATAM urbano. Suplementar 2000-5000 IU/día con grasa mejora performance, ánimo y recovery.',
      ),
      'principios_aplicados' => 
      array (
        0 => 'cafeina_timing_no_8h_pre_sueno',
        1 => 'suplemento_completa_no_sustituye',
        2 => 'vitamina_d3_dosis_efectiva',
      ),
      'suplementos' => 
      array (
        0 => 
        array (
          'nombre' => 'Aminoácidos',
          'slug' => 'aminoacidos',
          'dosis' => 'Consultá dosis con tu coach',
          'momento' => 'Consultá momento con tu coach',
          'frecuencia' => 'Según prescripción del coach',
          'notas' => 'Suplemento prescrito por el coach, fuera del stack evidence-based canónico. Confirmá dosis y respaldo con el coach antes de comprar.',
        ),
        1 => 
        array (
          'nombre' => 'Omega 3 epa dha',
          'slug' => 'omega-3-epa-dha',
          'dosis' => '1-2g',
          'momento' => 'Con almuerzo',
          'frecuencia' => 'Diaria',
          'notas' => 'Antiinflamatorio + recovery. En déficit hay inflamación crónica leve elevada. Evidencia moderate para recovery.',
        ),
        2 => 
        array (
          'nombre' => 'Vitamina d3',
          'slug' => 'vitamina-d3',
          'dosis' => '1000-2000 UI',
          'momento' => 'Con desayuno',
          'frecuencia' => 'Diaria',
          'notas' => 'Insurance contra déficit común en LATAM urbano. NO es driver de body composition, pero corrige déficit si existe.',
        ),
      ),
      'advertencia_legal' => 'Stack diseñado para mujer adulta sana intermedia/avanzada en déficit calórico. Consulta médico antes si tienes condición médica preexistente, estás embarazada/lactando, o tomas medicamentos crónicos. Suplementos NO sustituyen alimentación equilibrada.',
    ),
  ),
  3 => 
  array (
    'composed_id' => 14,
    'plan_type' => 'habitos',
    'methodology_slug' => 'habitos_sueno_hidratacion_basico',
    'plan_json' => 
    array (
      'plan_type' => 'habitos',
      'titulo' => 'Plan de hábitos — Hábitos básicos: sueño + hidratación — Karen Vanessa Gómez Lagos',
      'objetivo' => 'Consolidar pilares de recuperación y consistencia (sueño, hidratación, registro). Los hábitos básicos sostenidos valen más que cualquier suplemento o táctica avanzada.',
      'metodologia' => 'Hábitos básicos: sueño + hidratación',
      'duracion_semanas' => 4,
      'fecha_inicio' => '2026-06-01',
      'habitos' => 
      array (
        0 => 
        array (
          'nombre' => 'Sueño 7-9h consistente',
          'categoria' => 'sueño',
          'objetivo' => '7.5 horas promedio semanal · mismo horario ±30 min entre semana y fin de semana',
          'tracking_method' => 'app WellCore — campo horas_sueño (registrar cada mañana)',
          'por_que_importa' => 'El pico de hormona de crecimiento (GH) y la recuperación muscular ocurren en sueño profundo. Dormir <6h reduce ~50% de las ganancias de entrenamiento.',
          'tips' => 
          array (
            0 => 'Sin pantallas 1 hora antes de dormir',
            1 => 'Habitación fresca (18-20°C) y oscura',
            2 => 'Si trabajás de noche, hablalo con el coach para ajustar el plan',
          ),
        ),
        1 => 
        array (
          'nombre' => 'Hidratación mínima 2.4 L/día',
          'categoria' => 'hidratacion',
          'objetivo' => '2.4 L diarios base + 500 ml por hora de entrenamiento',
          'tracking_method' => 'Botella de 1L visible — meta de N botellas/día',
          'por_que_importa' => 'Deshidratación leve (-2% peso corporal en líquidos) reduce fuerza, resistencia y enfoque cognitivo. Cuando sentís sed ya estás deshidratado.',
          'tips' => 
          array (
            0 => 'Tu mínimo: peso × 0.035 = 2.4 L (peso aproximado 69 kg)',
            1 => 'Sumá 500 ml extra los días de entreno (durante + post)',
            2 => 'Café y té cuentan parcialmente (60%), bebidas con azúcar no',
          ),
        ),
        2 => 
        array (
          'nombre' => 'Registro de entrenamiento',
          'categoria' => 'registro',
          'objetivo' => 'Anotar peso, reps y RIR de cada serie ANTES de salir del gym',
          'tracking_method' => 'app WellCore — registro post-ejercicio en tiempo real',
          'por_que_importa' => 'Sin registro, no hay sobrecarga progresiva real, solo recuerdo selectivo. El que anota sabe exactamente cuándo subir, cuándo está estancado, cuándo deload.',
          'tips' => 
          array (
            0 => 'Anotá en el momento, no al final del día',
            1 => '80% de adherencia sostenida vale más que 100% del primer mes',
            2 => 'Si la app falla, libreta funciona igual — lo importante es la consistencia',
          ),
        ),
        3 => 
        array (
          'nombre' => 'Check-in semanal',
          'categoria' => 'tracking',
          'objetivo' => 'Peso (ayunas, 1× semana) + medidas (cintura/cadera, 1× semana) + 2 fotos (frente/lateral)',
          'tracking_method' => 'app WellCore — sección Check-in los domingos en la mañana',
          'por_que_importa' => 'El peso diario tiene mucha varianza (agua, comida). El promedio semanal es la métrica real. Las fotos detectan cambios que el peso no.',
          'tips' => 
          array (
            0 => 'Mismo día y hora cada semana (domingos en ayunas funciona)',
            1 => 'Mismas condiciones para las fotos (luz, ángulo, ropa)',
            2 => 'No mires el peso de la balanza diariamente — eso aumenta ansiedad sin info útil',
          ),
        ),
        4 => 
        array (
          'nombre' => 'Tracking del ciclo menstrual',
          'categoria' => 'ciclo',
          'objetivo' => 'Registrar día 1 del ciclo + duración promedio + síntomas relevantes',
          'tracking_method' => 'app WellCore — sección Ciclo o app dedicada (Flo, Clue)',
          'por_que_importa' => 'El ciclo modula recuperación, fuerza y respuesta a déficit calórico. Conocer en qué fase estás permite ajustar el entreno (más volumen folicular, más recuperación lútea).',
          'tips' => 
          array (
            0 => 'No es para "explicar" malos días — es info para ajustar carga',
            1 => 'Si el ciclo se interrumpe o cambia drásticamente, avisá al coach',
            2 => 'La fase lútea tardía puede pedir más calorías; está bien',
          ),
        ),
      ),
      'notas_coach' => 'Los hábitos son la base de todo. El plan de entreno y nutrición rinde 3× cuando estos pilares están sostenidos. No tenés que cumplir el 100% — apuntá a 80% sostenido durante las 4 semanas y el resultado se nota. — Héctor Leonardo Pérez Torres',
      'tips' => 
      array (
        0 => 'Empezá por el hábito que más te cueste sostener — ese es el que más valor agrega',
        1 => 'Si fallás un día, retomá al siguiente — no compenses con extra esfuerzo (eso desgasta)',
        2 => '4 semanas es suficiente para que el hábito automatice — no necesitás motivación constante',
        3 => 'Si un hábito no te encaja, hablalo con el coach antes de abandonarlo',
        4 => 'Cocinar tu propia comida es el control de calidad más subestimado en composición corporal. Mínimo 3 batch-cooks semanales.',
        5 => 'Cortisol crónico bloquea recomposición. 10 min/día de respiración consciente bajan cortisol salival ~20%.',
        6 => 'Luz azul + dopamina de redes destruye latencia del sueño. Sin pantallas 90 min antes de dormir, dormitorio sin TV.',
      ),
      'principios_aplicados' => 
      array (
        0 => 'cocinar_propio_3x_semana',
        1 => 'gestion_estres_cortisol',
        2 => 'digital_detox_bedroom',
      ),
    ),
  ),
);

echo "═══ insert_bundle (4 planes) ═══\n";
echo "DRY_RUN:    " . (DRY_RUN ? 'true (no escribe)' : 'false (ESCRIBE)') . "\n";
echo "client_id:  " . CLIENT_ID . "\n";
echo "coach_id:   " . COACH_ID . "\n";
echo "valid_from: " . VALID_FROM . "\n";
echo "expires_at: " . EXPIRES_AT . "\n";
echo "planes:\n";
foreach ($bundle as $p) {
    echo "  · {$p['plan_type']} (composed_id={$p['composed_id']}, methodology={$p['methodology_slug']}, " . strlen(json_encode($p['plan_json'])) . " bytes)\n";
}
echo "\n";

if (DRY_RUN) {
    echo "[DRY-RUN] Verificando sin escribir...\n";
}

$pdo = new PDO(
    'mysql:host=wellcorefitness_wellcorefitness-mysql;dbname=wellcorefitness;charset=utf8mb4',
    'wellcorefitness',
    'fYCVgn4XZ7twq34',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// ─── 1. Verificar cliente ───────────────────────────────────────────────────
$stmt = $pdo->prepare("SELECT id, name, email FROM clients WHERE id = ? LIMIT 1");
$stmt->execute([CLIENT_ID]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);
if (! $client) {
    fwrite(STDERR, "✗ ERROR: cliente CLIENT_ID=" . CLIENT_ID . " no existe.\n");
    exit(1);
}
echo "✓ Cliente: #{$client['id']} {$client['name']} <{$client['email']}>\n";

// ─── 2. Verificar coach (en tabla admins, role=coach|jefe|admin) ────────────
$stmt = $pdo->prepare("SELECT id, name, role FROM admins WHERE id = ? LIMIT 1");
$stmt->execute([COACH_ID]);
$coach = $stmt->fetch(PDO::FETCH_ASSOC);
if (! $coach) {
    fwrite(STDERR, "✗ ERROR: coach COACH_ID=" . COACH_ID . " no existe en admins.\n");
    exit(1);
}
echo "✓ Coach: #{$coach['id']} {$coach['name']} (role: {$coach['role']})\n";

// ─── 3. Planes activos previos por plan_type ───────────────────────────────
$planTypes = array_column($bundle, 'plan_type');
$placeholders = implode(',', array_fill(0, count($planTypes), '?'));
$stmt = $pdo->prepare(
    "SELECT id, plan_type, valid_from, expires_at FROM assigned_plans
     WHERE client_id = ? AND plan_type IN ($placeholders) AND active = 1
     ORDER BY plan_type, id DESC"
);
$stmt->execute([CLIENT_ID, ...$planTypes]);
$prev = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "→ Planes activos previos por plan_type:\n";
foreach ($prev as $p) {
    echo "   #{$p['id']} {$p['plan_type']} ({$p['valid_from']} → {$p['expires_at']})\n";
}

if (DRY_RUN) {
    echo "\n[DRY-RUN] OK. Si todo se ve bien, editar este archivo y poner DRY_RUN=false.\n";
    exit(0);
}

// ─── 4. WRITE: 1 sola transaction (desactivar todos + insertar todos) ──────
try {
    $pdo->beginTransaction();

    // 4a. Desactivar planes previos activos del cliente para los plan_types del bundle
    $stmtDeact = $pdo->prepare(
        "UPDATE assigned_plans SET active = 0
         WHERE client_id = ? AND plan_type IN ($placeholders) AND active = 1"
    );
    $stmtDeact->execute([CLIENT_ID, ...$planTypes]);
    $desactivados = $stmtDeact->rowCount();

    // 4b. Insertar todos los nuevos
    $stmtIns = $pdo->prepare(
        "INSERT INTO assigned_plans
         (client_id, plan_type, content, assigned_by, valid_from, expires_at, active, created_at)
         VALUES (?, ?, ?, ?, ?, ?, 1, ?)"
    );

    $insertedIds = [];
    foreach ($bundle as $p) {
        $stmtIns->execute([
            CLIENT_ID, $p['plan_type'],
            json_encode($p['plan_json'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            COACH_ID, VALID_FROM, EXPIRES_AT, $now,
        ]);
        $insertedIds[$p['plan_type']] = $pdo->lastInsertId();
    }

    $pdo->commit();

    echo "\n✓ OK — Bundle insertado en wellcore_fitness.assigned_plans\n";
    echo "   · Planes previos desactivados: $desactivados\n";
    foreach ($insertedIds as $type => $id) {
        echo "   · $type → assigned_plan_id=$id\n";
    }
    echo "\nSiguiente paso (invalidar cache del cliente):\n";
    echo "   php artisan tinker --execute=\"\\Cache::forget('client_plan_v3_" . CLIENT_ID . "'); \\Cache::forget('wp:plan:" . CLIENT_ID . "'); \\Cache::forget('wp:weekdays:" . CLIENT_ID . "'); \\Cache::forget('dashboard:" . CLIENT_ID . "'); echo 'cache invalidated';\"\n";
} catch (Exception $e) {
    $pdo->rollBack();
    fwrite(STDERR, "✗ ERROR (rollback aplicado): " . $e->getMessage() . "\n");
    fwrite(STDERR, $e->getTraceAsString() . "\n");
    exit(1);
}
