<?php

/**
 * insert_bundle.php — generado automáticamente por plan:export-bundle-prod-script
 *
 * Inserta 4 planes (bundle multi-vertical) en wellcore_fitness.assigned_plans
 * dentro de UNA SOLA transaction atómica.
 *
 * Generado:    2026-05-18T14:34:12-05:00
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

const DRY_RUN    = false;  // ✓ Motor v2 upgrade v3 — voz limpia + LATAM + variations + anti-IA
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
      'objetivo' => 'Vas a bajar grasa preservando músculo. Upper / Lower 4 días con intensidad sostenida — el déficit calórico hace el trabajo de bajar grasa; el gym hace el trabajo de mantener el músculo.',
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
      'notas_coach' => 'Karen, te armé este plan pensando en lo que estamos buscando: bajar grasa entrenando 4 días por semana. Vamos con Upper / Lower 4 días porque para tu nivel intermedio es lo que mejor te va a funcionar.

Vamos a subir intensidad semana a semana: arrancás con RIR 3 (te tienen que quedar 3 reps en el tanque al terminar la serie) y vamos bajando hasta RIR 0 en la semana 4. En los ejercicios grandes (sentadilla, peso muerto, press) hacés más series con menos reps; en los de aislación es al revés. Cada ejercicio tiene su propia cuenta — no es el típico 3×12 para todo.

Las primeras dos semanas las vas a sentir pesadas — estás comiendo menos de lo que gastás, es normal. Si te baja la energía, no fuerces — quedate con el mismo peso. Recién subís +5kg cuando llegás al RIR que te puse. A partir de la tercera semana ya empezás a notar cambios en el espejo y en las medidas.

Arrancás mañana. Anotá peso y RIR de cada serie apenas la terminás — si no anotás, no sabemos qué subir la próxima semana. Si una semana no llegás al RIR que te pongo, te quedás con el mismo peso y limpiá técnica primero. Cualquier dolor articular (no fatiga normal, dolor que pincha), parás y me escribís de una. — Héctor',
      'consejos_coach' => 
      array (
        0 => 'Calentá siempre 5-10 min antes de la primera serie',
        1 => 'Empezá por los ejercicios grandes (sentadilla, peso muerto, press) y dejá los de aislación al final',
        2 => 'Anotá peso y RIR de cada serie apenas terminás el ejercicio',
        3 => 'Si te duele una articulación (no fatiga muscular, dolor que pincha), parás y me escribís',
        4 => 'Tomá agua durante el entreno — mínimo 500 ml por hora',
        5 => 'Dormí entre 7 y 9 horas. El músculo se construye durmiendo, no en el gym',
        6 => 'Si hacés cardio, va después de las pesas, no antes — no robés energía al trabajo de fuerza',
        7 => 'Si te baja la energía, una taza de café 30 min antes ayuda',
      ),
      'tips' => 
      array (
        0 => 'Anotá peso, reps y RIR de cada serie apenas terminás el ejercicio',
        1 => 'Tomá agua durante el entreno (mínimo 500 ml por hora)',
        2 => 'Dormí al menos 7 horas. El músculo se construye durmiendo, no en el gym',
        3 => 'Si te duele una articulación (no fatiga muscular, dolor que pincha), parás y me escribís',
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
          'descripcion' => 'Semana de adaptación. RIR 3 — quedate con 3 reps en el tanque al terminar cada serie. Esta semana la técnica manda sobre el peso. Andá sintiendo cómo trabaja cada músculo.',
          'dias' => 
          array (
            0 => 
            array (
              'dia_semana' => 'Lunes',
              'grupo_muscular' => 'Gluteos',
              'nombre' => 'Lunes — Gluteos',
              'duracion_estimada_min' => 47,
              'calentamiento' => '5 min cardio suave + movilidad articular general. Total: 8 min.',
              'vuelta_calma' => 'Estirá 3-5 min los grupos que trabajaste y cerrá con 5 min de caminata suave. Bajá pulsaciones antes de irte.',
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
                  'variacion' => 
                  array (
                    'nombre' => 'Hipthrust a una pierna con barra',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/hipthrust-a-una-pierna-con-barra.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Hip thrust con barra',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/hipthrust-con-barra.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera sentado en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-sentado-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera de pie en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera de pie en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera de pie en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera de pie en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
              'vuelta_calma' => 'Estirá 3-5 min los grupos que trabajaste y cerrá con 5 min de caminata suave. Bajá pulsaciones antes de irte.',
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
                  'variacion' => 
                  array (
                    'nombre' => 'Jalón en polea alta',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/jalon-en-polea.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Fondos de triceps',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/fondos-de-triceps.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Elevacion fronta en polea barra',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/elevacion-fronta-en-polea-barra.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Curl de bíceps con mancuernas',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/curl-biceps-con-mancuerna.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Empuje de triceps en polea unilateral',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/empuje-de-triceps-en-polea-unilateral.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Elevaciones laterales con mancuernas',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/elevacion-lateral-con-mancuerna.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
              'vuelta_calma' => 'Estirá 3-5 min los grupos que trabajaste y cerrá con 5 min de caminata suave. Bajá pulsaciones antes de irte.',
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
                  'variacion' => 
                  array (
                    'nombre' => 'Hipthrust a una pierna con barra',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/hipthrust-a-una-pierna-con-barra.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Hip thrust con barra',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/hipthrust-con-barra.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera sentado en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-sentado-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera de pie en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera de pie en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera de pie en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera de pie en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
              'vuelta_calma' => 'Estirá 3-5 min los grupos que trabajaste y cerrá con 5 min de caminata suave. Bajá pulsaciones antes de irte.',
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
                  'variacion' => 
                  array (
                    'nombre' => 'Hipthrust a una pierna con barra',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/hipthrust-a-una-pierna-con-barra.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Presa de piernas abierto',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/presa-de-piernas-abierto.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera sentado en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-sentado-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera de pie en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera de pie en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Extensión de cuádriceps',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/extension-de-piernas-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
          'descripcion' => 'Acá ya estás más enchufado. Si la técnica te sale sólida, subí peso — todavía te tienen que quedar 2 reps en el tanque al terminar la serie.',
          'dias' => 
          array (
            0 => 
            array (
              'dia_semana' => 'Lunes',
              'grupo_muscular' => 'Gluteos',
              'nombre' => 'Lunes — Gluteos',
              'duracion_estimada_min' => 56,
              'calentamiento' => '5 min cardio suave + movilidad articular general. Total: 8 min.',
              'vuelta_calma' => 'Estirá 3-5 min los grupos que trabajaste y cerrá con 5 min de caminata suave. Bajá pulsaciones antes de irte.',
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
                  'variacion' => 
                  array (
                    'nombre' => 'Hipthrust a una pierna con barra',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/hipthrust-a-una-pierna-con-barra.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Hip thrust con barra',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/hipthrust-con-barra.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera sentado en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-sentado-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera de pie en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera de pie en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera de pie en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera de pie en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
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
              'vuelta_calma' => 'Estirá 3-5 min los grupos que trabajaste y cerrá con 5 min de caminata suave. Bajá pulsaciones antes de irte.',
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
                  'variacion' => 
                  array (
                    'nombre' => 'Jalón en polea alta',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/jalon-en-polea.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Fondos de triceps',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/fondos-de-triceps.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Elevacion fronta en polea barra',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/elevacion-fronta-en-polea-barra.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Curl de bíceps con mancuernas',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/curl-biceps-con-mancuerna.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Empuje de triceps en polea unilateral',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/empuje-de-triceps-en-polea-unilateral.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Elevaciones laterales con mancuernas',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/elevacion-lateral-con-mancuerna.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
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
              'vuelta_calma' => 'Estirá 3-5 min los grupos que trabajaste y cerrá con 5 min de caminata suave. Bajá pulsaciones antes de irte.',
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
                  'variacion' => 
                  array (
                    'nombre' => 'Hipthrust a una pierna con barra',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/hipthrust-a-una-pierna-con-barra.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Hip thrust con barra',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/hipthrust-con-barra.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera sentado en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-sentado-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera de pie en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera de pie en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera de pie en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera de pie en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
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
              'vuelta_calma' => 'Estirá 3-5 min los grupos que trabajaste y cerrá con 5 min de caminata suave. Bajá pulsaciones antes de irte.',
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
                  'variacion' => 
                  array (
                    'nombre' => 'Hipthrust a una pierna con barra',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/hipthrust-a-una-pierna-con-barra.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Presa de piernas abierto',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/presa-de-piernas-abierto.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera sentado en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-sentado-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera de pie en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera de pie en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Extensión de cuádriceps',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/extension-de-piernas-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
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
          'descripcion' => 'Esta semana le metés en serio. Pesos cercanos al tope, descanso completo entre series. RIR 1. Es la semana más dura del mes — vas a sentirlo.',
          'dias' => 
          array (
            0 => 
            array (
              'dia_semana' => 'Lunes',
              'grupo_muscular' => 'Gluteos',
              'nombre' => 'Lunes — Gluteos',
              'duracion_estimada_min' => 69,
              'calentamiento' => '5 min cardio suave + movilidad articular general. Total: 8 min.',
              'vuelta_calma' => 'Estirá 3-5 min los grupos que trabajaste y cerrá con 5 min de caminata suave. Bajá pulsaciones antes de irte.',
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
                  'variacion' => 
                  array (
                    'nombre' => 'Hipthrust a una pierna con barra',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/hipthrust-a-una-pierna-con-barra.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Hip thrust con barra',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/hipthrust-con-barra.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera sentado en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-sentado-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera de pie en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera de pie en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera de pie en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera de pie en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
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
              'vuelta_calma' => 'Estirá 3-5 min los grupos que trabajaste y cerrá con 5 min de caminata suave. Bajá pulsaciones antes de irte.',
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
                  'variacion' => 
                  array (
                    'nombre' => 'Jalón en polea alta',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/jalon-en-polea.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Fondos de triceps',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/fondos-de-triceps.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Elevacion fronta en polea barra',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/elevacion-fronta-en-polea-barra.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Curl de bíceps con mancuernas',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/curl-biceps-con-mancuerna.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Empuje de triceps en polea unilateral',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/empuje-de-triceps-en-polea-unilateral.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Elevaciones laterales con mancuernas',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/elevacion-lateral-con-mancuerna.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
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
              'vuelta_calma' => 'Estirá 3-5 min los grupos que trabajaste y cerrá con 5 min de caminata suave. Bajá pulsaciones antes de irte.',
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
                  'variacion' => 
                  array (
                    'nombre' => 'Hipthrust a una pierna con barra',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/hipthrust-a-una-pierna-con-barra.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Hip thrust con barra',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/hipthrust-con-barra.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera sentado en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-sentado-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera de pie en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera de pie en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera de pie en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera de pie en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
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
              'vuelta_calma' => 'Estirá 3-5 min los grupos que trabajaste y cerrá con 5 min de caminata suave. Bajá pulsaciones antes de irte.',
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
                  'variacion' => 
                  array (
                    'nombre' => 'Hipthrust a una pierna con barra',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/hipthrust-a-una-pierna-con-barra.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Presa de piernas abierto',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/presa-de-piernas-abierto.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera sentado en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-sentado-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera de pie en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera de pie en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Extensión de cuádriceps',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/extension-de-piernas-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
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
          'descripcion' => 'Última semana del bloque, dale todo lo que tenés con técnica intacta. RIR 0. Si no llegás, mantené el peso y mejorá la ejecución.',
          'dias' => 
          array (
            0 => 
            array (
              'dia_semana' => 'Lunes',
              'grupo_muscular' => 'Gluteos',
              'nombre' => 'Lunes — Gluteos',
              'duracion_estimada_min' => 86,
              'calentamiento' => '5 min cardio suave + movilidad articular general. Total: 8 min.',
              'vuelta_calma' => 'Estirá 3-5 min los grupos que trabajaste y cerrá con 5 min de caminata suave. Bajá pulsaciones antes de irte.',
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
                  'variacion' => 
                  array (
                    'nombre' => 'Hipthrust a una pierna con barra',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/hipthrust-a-una-pierna-con-barra.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Hip thrust con barra',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/hipthrust-con-barra.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera sentado en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-sentado-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera de pie en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera de pie en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera de pie en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera de pie en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
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
              'vuelta_calma' => 'Estirá 3-5 min los grupos que trabajaste y cerrá con 5 min de caminata suave. Bajá pulsaciones antes de irte.',
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
                  'variacion' => 
                  array (
                    'nombre' => 'Jalón en polea alta',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/jalon-en-polea.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Fondos de triceps',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/fondos-de-triceps.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Elevacion fronta en polea barra',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/elevacion-fronta-en-polea-barra.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Curl de bíceps con mancuernas',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/curl-biceps-con-mancuerna.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Empuje de triceps en polea unilateral',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/empuje-de-triceps-en-polea-unilateral.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Elevaciones laterales con mancuernas',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/elevacion-lateral-con-mancuerna.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
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
              'vuelta_calma' => 'Estirá 3-5 min los grupos que trabajaste y cerrá con 5 min de caminata suave. Bajá pulsaciones antes de irte.',
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
                  'variacion' => 
                  array (
                    'nombre' => 'Hipthrust a una pierna con barra',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/hipthrust-a-una-pierna-con-barra.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Hip thrust con barra',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/hipthrust-con-barra.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera sentado en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-sentado-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera de pie en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera de pie en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera de pie en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera de pie en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
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
              'vuelta_calma' => 'Estirá 3-5 min los grupos que trabajaste y cerrá con 5 min de caminata suave. Bajá pulsaciones antes de irte.',
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
                  'variacion' => 
                  array (
                    'nombre' => 'Hipthrust a una pierna con barra',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/hipthrust-a-una-pierna-con-barra.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Presa de piernas abierto',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/presa-de-piernas-abierto.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera sentado en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-sentado-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera de pie en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Abduccion de cadera de pie en maquina',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/abduccion-de-cadera-de-pie-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
                  ),
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
                  'variacion' => 
                  array (
                    'nombre' => 'Extensión de cuádriceps',
                    'gif_url' => 'https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs-v2/main/extension-de-piernas-en-maquina.gif',
                    'motivo' => 'Misma mecánica, distinto ejercicio — para variar estímulo o por preferencia',
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
      'objetivo' => 'Vas a bajar grasa sin perder músculo. Comés 1794 kcal por día con proteína alta (2.4 g por cada kilo tuyo = 166g total) para que el cuerpo conserve el músculo. Meta: bajar entre medio y un kilo por semana, de la semana 2 en adelante.',
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
        'electrolitos' => 'Si estás comiendo menos calorías y entrenás más de 45 min o sumás cardio, agregale una pizca de sal al agua. Te evita el bajón.',
        'notas' => 'Tu peso en kilos × 35 ml te da el mínimo diario: 2.4 L. Sumá 500 ml extra los días que entrenás.',
      ),
      'notas_coach' => 'Karen, te dejo 1794 kcal por día con 166g de proteína (2.4 g por cada kilo tuyo). Vamos a estar comiendo 400 kcal menos de lo que tu cuerpo gasta normalmente — esto es lo que necesitás para bajar grasa.

Te lo partí en 4 comidas para que tengas proteína repartida todo el día. Cada comida te dejé 3 opciones que cumplen lo mismo — elegís según lo que tengas en la cocina o lo que se te antoje. La primera semana pesá los alimentos en crudo; ya después le agarrás el ojo.

Las primeras dos semanas vas a tener hambre — es normal, tu cuerpo se está acomodando. Tomá más agua, masticá despacio (intentá 20 masticadas por bocado), y se te va apagando. De la semana 2 en adelante esperá bajar entre medio y un kilo por semana. Si bajás más rápido, no es grasa — escribime y ajustamos.

Arrancás mañana. Si llegás tarde a una comida, no te la saltés — sumale la proteína a la próxima. Tomá agua (mínimo 35 ml por cada kilo tuyo al día). Si te ataca el antojo de noche, agua caliente con miel o un té con canela lo apagan. — Héctor',
      'consejos_coach' => 
      array (
        0 => 'La proteína no se negocia: si llegás al total del día, el día está hecho',
        1 => 'Las 3 opciones de cada comida valen lo mismo — cambialas como quieras',
        2 => 'Cociná sin aceite — plancha, horno o vapor. Si necesitás, spray en aerosol o sartén antiadherente',
        3 => 'Verduras de ensalada = libres. Llenate el plato sin contar gramos',
        4 => 'Cocinar para varios días el domingo te salva la semana',
        5 => '2.4 L de agua mínimo al día. El hambre muchas veces es sed',
        6 => 'Café y té libres, sin azúcar',
        7 => 'Si te ataca el antojo de noche: té con canela o agua caliente con una cucharadita de miel',
        8 => 'Días que entrenás, sumá 50 kcal de carbohidratos. Días sin entreno, restalas',
        9 => 'Una comida libre por semana — UNA comida, no todo el día',
        10 => 'Si el peso no baja 2 semanas seguidas, escribime y ajustamos',
      ),
      'tips' => 
      array (
        0 => 'Tomá mínimo 2.4 L de agua al día (tu peso en kilos × 35 ml = tu mínimo)',
        1 => 'Si entrenás, comé la comida pre-entreno entre 60 y 90 min antes',
        2 => 'Pesate 1 vez por semana en ayunas. Diario te vuelve loca con números que no significan nada — varía mucho por agua y comida',
        3 => 'Los gramos que te pongo son del alimento crudo o seco — pesalo antes de cocinarlo',
        4 => 'Si te saltás una comida, sumale la proteína (~33g) a la siguiente',
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
          'subtitulo' => 'Proteína + carbohidrato + fruta',
          'macros' => 
          array (
            'proteina' => 42,
            'carbohidratos' => 36,
            'grasas' => 16,
          ),
          'kcal_objetivo' => 449,
          'notas' => 'La proteína la cocinás sin aceite — usá sartén antiadherente o spray en aerosol. El carbohidrato y la fruta te dan la energía para arrancar el día y entrenar 2-3 horas después.',
          'opcion_a' => 
          array (
            0 => 'Claras de huevo (270g)',
            1 => 'Pan integral (79g)',
            2 => 'Almendras (24g)',
            3 => 'Papaya (120g)',
          ),
          'opcion_b' => 
          array (
            0 => 'Huevo entero (333g)',
            1 => 'Avena en hojuelas (51g)',
            2 => 'Manzana (120g)',
          ),
          'opcion_c' => 
          array (
            0 => 'Claras de huevo (270g)',
            1 => 'Arepa de maíz blanco (97g)',
            2 => 'Aguacate (95g)',
            3 => 'Pera (120g)',
          ),
        ),
        1 => 
        array (
          'nombre' => 'Snack AM',
          'hora' => '10:00',
          'subtitulo' => 'Algo proteico ligero con grasa buena o fruta',
          'macros' => 
          array (
            'proteina' => 17,
            'carbohidratos' => 14,
            'grasas' => 6,
          ),
          'kcal_objetivo' => 179,
          'notas' => 'Snack pequeño para mantener proteína repartida. Si no tenés hambre, sumá esta proteína al desayuno o almuerzo — no te la saltés del todo.',
          'opcion_a' => 
          array (
            0 => 'Claras de huevo (156g)',
            1 => 'Banano (57g)',
          ),
          'opcion_b' => 
          array (
            0 => 'Huevo entero (135g)',
            1 => 'Pera (86g)',
          ),
          'opcion_c' => 
          array (
            0 => 'Claras de huevo (156g)',
            1 => 'Papaya (118g)',
          ),
        ),
        2 => 
        array (
          'nombre' => 'Almuerzo',
          'hora' => '13:00',
          'subtitulo' => 'Tu comida más fuerte del día: proteína, carbohidrato y verdura',
          'macros' => 
          array (
            'proteina' => 58,
            'carbohidratos' => 50,
            'grasas' => 22,
          ),
          'kcal_objetivo' => 628,
          'notas' => 'Es la comida más grande del día. El aceite de oliva en frío (una cucharada) va para la ensalada o el aguacate, no para freír. La proteína la hacés a la plancha, al horno o hervida — nada de frito.',
          'opcion_a' => 
          array (
            0 => 'Pechuga de pavo (220g)',
            1 => 'Galletas de arroz (61g)',
            2 => 'Maní (cacahuate) (37g)',
            3 => 'Coliflor (100g)',
          ),
          'opcion_b' => 
          array (
            0 => 'Pechuga de pollo (187g)',
            1 => 'Yuca (cruda) (131g)',
            2 => 'Nueces (23g)',
            3 => 'Espinaca (100g)',
          ),
          'opcion_c' => 
          array (
            0 => 'Pechuga de pavo (220g)',
            1 => 'Arroz integral (65g)',
            2 => 'Aceite de coco (20g)',
            3 => 'Lechuga (100g)',
          ),
        ),
        3 => 
        array (
          'nombre' => 'Cena',
          'hora' => '16:00',
          'subtitulo' => 'Proteína con verdura, ligero, 2-3h antes de dormir',
          'macros' => 
          array (
            'proteina' => 50,
            'carbohidratos' => 43,
            'grasas' => 19,
          ),
          'kcal_objetivo' => 538,
          'notas' => 'Si llegás tarde y con sueño, comé solo proteína + verdura — la cena es la comida más flexible. Evitá carbohidratos pesados si dormís dentro de 2 horas.',
          'opcion_a' => 
          array (
            0 => 'Pechuga de pavo (211g)',
            1 => 'Tostada integral (57g)',
            2 => 'Aceite de coco (20g)',
            3 => 'Pepino (100g)',
          ),
          'opcion_b' => 
          array (
            0 => 'Pechuga de pollo (161g)',
            1 => 'Pasta integral (cruda) (61g)',
            2 => 'Semillas de chía (35g)',
            3 => 'Champiñones (100g)',
          ),
          'opcion_c' => 
          array (
            0 => 'Pechuga de pavo (211g)',
            1 => 'Quinoa (200g)',
            2 => 'Chocolate negro >85% cacao (30g)',
            3 => 'Calabacín (zucchini) (100g)',
          ),
        ),
      ),
      'plan_dia_descanso' => 
      array (
        'descripcion' => 'Los días que no entrenás bajás un poco las calorías porque no quemás extra. La proteína se mantiene igual; bajás un poco los carbohidratos.',
        'calorias_objetivo' => 1644,
        'ajustes' => 
        array (
          0 => 'Reducí ~30g de arroz o pasta en el almuerzo (te queda ~120 kcal menos)',
          1 => 'Mantené la proteína igual — el músculo también se construye en descanso',
          2 => 'Si tenés snack pre-entreno, saltátelo (esa comida es para tener energía en el gym)',
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
      'notas_coach' => 'Tomá cada suplemento en el momento que te marco — el cuándo importa tanto como el qué. Y constancia: mejor que los tomes el 80% del mes y no que los tomes a tope la primera semana y los abandones.

Si tenés algo de riñones, hígado, presión, o estás embarazada, parame ahí y hablamos antes de que compres nada.

Si no te alcanza para todos este mes, arrancá por los que te marqué como esenciales — los demás los sumás cuando puedas. El costo mensual aproximado es de COP $280.000 (referencial, varía 2-3× por marca y país). — Héctor',
      'tips' => 
      array (
        0 => 'La creatina no necesita "fase de carga" como dicen por ahí — 5g diarios desde el día 1 alcanzan',
        1 => 'La proteína whey es opcional si ya llegás a la proteína del día comiendo alimentos enteros',
        2 => 'Si te saltás un día de creatina, no pasa nada — el efecto se construye con el tiempo, no con una sola toma',
        3 => 'No mezclés más de un pre-entreno con cafeína al día — te puede tirar el corazón',
        4 => 'Si algún suplemento te cae mal o te sentís raro, parate y escribime',
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
          'notas' => 'Te lo recomendé personalmente. Confirmame dosis y marca antes de comprarlo, así nos aseguramos.',
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
      'objetivo' => 'Vamos a fijar los hábitos que sostienen todo lo demás: dormir bien, tomar agua, anotar lo que hacés en el gym. Sin esto, el plan de entreno y nutrición no rinde.',
      'metodologia' => 'Hábitos básicos: sueño + hidratación',
      'duracion_semanas' => 4,
      'fecha_inicio' => '2026-06-01',
      'habitos' => 
      array (
        0 => 
        array (
          'id' => 'sueno',
          'nombre' => 'Dormir entre 7 y 9 horas',
          'categoria' => 'sueño',
          'objetivo_diario' => '7.5 horas promedio · mismo horario entre semana y fin de semana (±30 min)',
          'objetivo' => '7.5 horas promedio · mismo horario entre semana y fin de semana (±30 min)',
          'tracking_method' => 'Registrá tus horas de sueño cada mañana en la app',
          'por_que_importa' => 'Mientras dormís profundo, el cuerpo libera la hormona que reconstruye el músculo. Dormís menos de 6 horas, perdés hasta la mitad de lo que ganaste en el gym.',
          'tips' => 
          array (
            0 => 'Apagá las pantallas una hora antes de dormir',
            1 => 'Habitación fresca (entre 18 y 20°C) y oscura',
            2 => 'Si trabajás de noche, escribime y vemos cómo ajustamos esto',
          ),
        ),
        1 => 
        array (
          'id' => 'agua',
          'nombre' => 'Tomar 2.4 L de agua al día',
          'categoria' => 'hidratacion',
          'objetivo_diario' => '2.4 L diarios + 500 ml extra por hora de entreno',
          'objetivo' => '2.4 L diarios + 500 ml extra por hora de entreno',
          'tracking_method' => 'Una botella de 1L a la vista — apuntá a vaciarla N veces al día',
          'por_que_importa' => 'Si perdés apenas 2% de tu peso en agua, ya te baja la fuerza, el aguante y la concentración. Cuando sentís sed, ya estás corto.',
          'tips' => 
          array (
            0 => 'Tu mínimo te lo calculo así: tu peso (69 kg) × 0.035 = 2.4 L',
            1 => 'Los días que entrenás, sumá 500 ml extra (durante y después)',
            2 => 'Café y té cuentan parcialmente (60%); bebidas con azúcar no cuentan',
          ),
        ),
        2 => 
        array (
          'id' => 'entrenamiento',
          'nombre' => 'Anotar cada serie del entreno',
          'categoria' => 'registro',
          'objetivo_diario' => 'Peso, reps y RIR de cada serie, antes de salir del gym',
          'objetivo' => 'Peso, reps y RIR de cada serie, antes de salir del gym',
          'tracking_method' => 'Registralo en la app apenas terminás cada ejercicio',
          'por_que_importa' => 'Si no anotás, no podés saber cuándo subir peso, cuándo te estancaste o cuándo te toca una semana más liviana. La memoria juega malas pasadas — lo escrito gana.',
          'tips' => 
          array (
            0 => 'Anotá en el momento, no al final del día',
            1 => 'Es mejor que lo hagas el 80% siempre y no el 100% solo los primeros días',
            2 => 'Si la app falla, libreta de papel funciona igual — lo importante es que anotes, no en qué',
          ),
        ),
        3 => 
        array (
          'id' => 'nutricion',
          'nombre' => 'Check-in semanal',
          'categoria' => 'tracking',
          'objetivo_diario' => 'Pesate una vez por semana en ayunas + medidas cintura/cadera + 2 fotos (frente y costado)',
          'objetivo' => 'Pesate una vez por semana en ayunas + medidas cintura/cadera + 2 fotos (frente y costado)',
          'tracking_method' => 'En la app, sección Check-in. Domingos en la mañana funciona bien',
          'por_que_importa' => 'El peso diario varía mucho (agua, comida, hora del día). Lo que importa es el promedio de la semana. Las fotos te muestran cambios que la balanza esconde.',
          'tips' => 
          array (
            0 => 'Mismo día y hora cada semana (domingos en ayunas funciona bien)',
            1 => 'Mismas condiciones para las fotos (luz, ángulo, ropa)',
            2 => 'No te peses todos los días — solo te genera ansiedad sin darte información que sirva',
          ),
        ),
        4 => 
        array (
          'id' => 'suplementos',
          'nombre' => 'Tracking del ciclo menstrual',
          'categoria' => 'ciclo',
          'objetivo_diario' => 'Día 1 del ciclo + duración promedio + síntomas relevantes',
          'objetivo' => 'Día 1 del ciclo + duración promedio + síntomas relevantes',
          'tracking_method' => 'En la app sección Ciclo, o en una app dedicada como Flo o Clue',
          'por_que_importa' => 'Tu ciclo afecta cómo te recuperás, qué tan fuerte estás y cómo respondés a comer menos. Saber en qué fase estás te ayuda a ajustar: en la primera mitad del ciclo podés meterle más; en la segunda, priorizá recuperación.',
          'tips' => 
          array (
            0 => 'No es para justificar días malos — es información para acomodar tu entreno',
            1 => 'Si el ciclo se interrumpe o cambia mucho, escribime',
            2 => 'Los últimos 5-7 días antes del periodo, tu cuerpo te pide más calorías — eso es normal',
          ),
        ),
      ),
      'notas_coach' => 'Los hábitos son la base de todo. Con esto firme, el resto del plan rinde el triple. Sin esto, el mejor entreno y la mejor nutrición no sirven.

No te pongas la meta del 100% — apuntá al 80% todas las semanas y vas a ver el cambio. Si fallás un día, retomá al siguiente. No compensés con esfuerzo extra (eso desgasta).

Si algo no te encaja con tus tiempos o tu situación, escribime y lo ajustamos. — Héctor',
      'tips' => 
      array (
        0 => 'Arrancá por el hábito que más te cuesta — ese es el que más te va a mover la aguja',
        1 => 'Si fallás un día, retomá al siguiente. No compensés con esfuerzo extra (eso desgasta)',
        2 => 'En 4 semanas el hábito se te vuelve automático y ya no necesitás estar motivada todos los días',
        3 => 'Si un hábito no te encaja, escribime antes de abandonarlo',
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
