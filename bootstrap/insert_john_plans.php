<?php
/**
 * INSERT plan John Carvajal (client_id=77)
 * Plan Esencial — Entrenamiento + Nutrición + Suplementación
 * Generado: 2026-04-25
 *
 * Ejecutar desde EasyPanel console:
 *   php /code/bootstrap/insert_john_plans.php
 */

$host = 'wellcorefitness_wellcorefitness-mysql';
$db   = 'wellcorefitness';
$user = 'wellcorefitness';
$pass = 'fYCVgn4XZ7twq34';

$pdo = new PDO(
    "mysql:host=$host;dbname=$db;charset=utf8mb4",
    $user,
    $pass,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$clientId   = 77;
$assignedBy = 7;        // Anderson Ardila
$validFrom  = '2026-04-28';
$expiresAt  = '2026-05-25';
$now        = date('Y-m-d H:i:s');

$trainJson = <<<'EOJSON'
{
  "plan_type": "entrenamiento",
  "titulo": "Plan Esencial Entrenamiento — John Carvajal",
  "programa": "Plan Esencial Entrenamiento — John Carvajal",
  "cliente": "John Carvajal",
  "plan": "Esencial",
  "objetivo": "Pérdida de grasa con preservación muscular — déficit calórico moderado y cardio diario.",
  "genero": "Masculino",
  "nivel": "Principiante",
  "metodologia": "Body Part Split 5 días · Adaptación → Hipertrofia → Fuerza → Peak",
  "frecuencia": "5 días por semana",
  "frecuencia_dias": 5,
  "duracion_sesion": "70-75 minutos",
  "equipamiento": "Gimnasio completo",
  "duracion_semanas": 4,
  "peso_cliente": "124 kg",
  "estatura": "176 cm",
  "fecha_inicio": "2026-04-28",
  "fecha_fin": "2026-05-25",
  "split": {
    "Lunes": "Pecho + Tríceps + Abdomen",
    "Martes": "Cuádriceps + Pantorrilla",
    "Miércoles": "Espalda + Bíceps + Abdomen",
    "Jueves": "Femorales + Glúteos + Pantorrilla",
    "Viernes": "Hombros + Bíceps + Tríceps + Abdomen"
  },
  "principios": {
    "sobrecarga_progresiva": "Cada semana subís carga en al menos 1 ejercicio compuesto.",
    "tecnica_primero": "Sin técnica correcta no hay resultado real. Mejor menos peso y forma perfecta.",
    "registro": "Anotá los pesos de cada sesión para saber cuándo subir."
  },
  "notas_generales": "Plan de 4 semanas de intensificación progresiva. Sem 1 Adaptación → Sem 2 Hipertrofia → Sem 3 Fuerza → Sem 4 Peak.",
  "notas_coach": "John, diseñé este plan pensando en donde estás hoy: 124 kg, principiante, con el gym disponible de lunes a viernes. No te mandé a hacer sentadillas con barra en semana 1 — empezamos con la prensa porque la técnica de la sentadilla requiere práctica, y con tu peso actual el riesgo de lesión es alto si se aprende mal. A medida que avances en el plan y tu técnica mejore, la sentadilla con barra entra como variación.\n\nEl split que tenés ataca cada grupo muscular una vez por semana con suficiente intensidad para que el cuerpo cambie. Los 50 minutos de pesas más 20 de caminadora son innegociables. La caminadora con inclinación quema grasa sin destruir músculo, que es exactamente lo que buscamos.\n\nSemana 1 es de aprendizaje — no trates de levantar el máximo. Aprende el movimiento, siente el músculo que trabajas. Semana 4 sí vas con todo. El progreso lo vas a ver en el espejo a partir de la semana 3.\n\nUna cosa: si un día te duele una articulación (no el músculo, sino la articulación), pará y me escribís. Dolor muscular al día siguiente es normal. Dolor en rodilla, hombro o espalda baja NO es normal. Me avisás.",
  "semanas": [
    {
      "semana": 1,
      "numero": 1,
      "fase": "Adaptación",
      "fase_nombre": "Semana 1 — Adaptación",
      "nombre_bloque": "Semana 1 — Adaptación",
      "nota_semana": "Semana de adaptación: prioriza la técnica sobre el peso. RIR 3 significa que te quedan 3 reps en el tanque.",
      "dias": [
        {
          "dia": 1,
          "dia_semana": "Lunes",
          "nombre": "Lunes — Pecho + Tríceps + Abdomen",
          "tipo": "empuje",
          "grupo_muscular": "Pecho, Tríceps, Core",
          "duracion_estimada": "70-75 min",
          "calentamiento": "5 min en caminadora suave + rotaciones de hombro + 2 series de 15 reps en máquina de pecho sin peso",
          "vuelta_calma": "5 min: estiramiento del grupo muscular principal del día.",
          "ejercicios": [
            {
              "nombre": "Press de pecho en máquina",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/press-de-pecho-en-maquina.gif",
              "series": 3,
              "repeticiones": "12",
              "descanso": "90s",
              "rir": "3",
              "bloque": "normal",
              "notas": "Pecho arriba, omóplatos contra el respaldo. Empuja explosivo, baja controlado en 2 segundos.",
              "variacion": {
                "nombre": "Press de banca con barra",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/press-banca-barra.gif"
              }
            },
            {
              "nombre": "Press de pecho inclinado en máquina",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/press-de-pecho-inclinado-en-maquina.gif",
              "series": 3,
              "repeticiones": "12",
              "descanso": "90s",
              "rir": "3",
              "bloque": "normal",
              "notas": "La inclinación ataca la parte alta del pecho. No arquees la espalda baja.",
              "variacion": {
                "nombre": "Press de banca inclinado con barra",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/press-banca-inclinado-con-barra.gif"
              }
            },
            {
              "nombre": "Aperturas en peck deck",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/aperturas-en-peck-deck.gif",
              "series": 3,
              "repeticiones": "12",
              "descanso": "60s",
              "rir": "3",
              "bloque": "normal",
              "notas": "Abraza un árbol imaginario. Siente el estiramiento en el pecho al abrir, contrae fuerte al cerrar.",
              "variacion": {
                "nombre": "Aperturas en polea de pie",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/aperturas-en-polea-de-pie.gif"
              }
            },
            {
              "nombre": "Extensión de tríceps en polea con cuerda",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/extension-de-triceps-en-polea-con-cuerda.gif",
              "series": 3,
              "repeticiones": "12",
              "descanso": "60s",
              "rir": "3",
              "bloque": "normal",
              "notas": "Codos pegados al cuerpo, solo mueve el antebrazo. Al bajar, separa la cuerda hacia afuera.",
              "variacion": {
                "nombre": "Extensión de tríceps en máquina",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/extension-de-triceps-en-maquina.gif"
              }
            },
            {
              "nombre": "Fondos de tríceps en máquina",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/fondos-de-triceps-en-maquina.gif",
              "series": 3,
              "repeticiones": "12",
              "descanso": "60s",
              "rir": "3",
              "bloque": "normal",
              "notas": "Mantén el torso vertical, no te inclines hacia adelante. Extiende los codos completamente.",
              "variacion": {
                "nombre": "Extensión de tríceps en polea agarre inverso",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/extension-de-triceps-en-polea-agarre-inverso.gif"
              }
            },
            {
              "nombre": "Crunch en polea arrodillado",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/crunch-en-polea-arrodillado.gif",
              "series": 3,
              "repeticiones": "15",
              "descanso": "45s",
              "rir": "1",
              "bloque": "normal",
              "notas": "Contrae el abdomen, lleva los codos hacia las rodillas. No jales con los brazos.",
              "variacion": {
                "nombre": "Crunch abdominal en máquina total",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/crunch-abdominal-en-maquina-total.gif"
              }
            },
            {
              "nombre": "Caminadora inclinada",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/caminadora-inclinada.gif",
              "is_cardio": true,
              "series": 1,
              "repeticiones": "20 min",
              "descanso": "-",
              "notas": "5-6 km/h, 10-12% de inclinación. Post-pesas. Sin agarrarte de las manijas. FC objetivo 120-140 bpm."
            }
          ]
        },
        {
          "dia": 2,
          "dia_semana": "Martes",
          "nombre": "Martes — Cuádriceps + Pantorrilla",
          "tipo": "piernas",
          "grupo_muscular": "Cuádriceps, Pantorrilla",
          "duracion_estimada": "70-75 min",
          "calentamiento": "5 min en caminadora + 2 series de 15 reps de sentadillas sin peso + movilidad de cadera",
          "vuelta_calma": "5 min: estiramiento del grupo muscular principal del día.",
          "ejercicios": [
            {
              "nombre": "Prensa de piernas abierto",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/presa-de-piernas-abierto.gif",
              "series": 3,
              "repeticiones": "12",
              "descanso": "90s",
              "rir": "3",
              "bloque": "normal",
              "notas": "Pies separados al ancho de los hombros. No bloquees las rodillas arriba. Baja hasta 90° o menos.",
              "variacion": {
                "nombre": "Sentadilla con barra",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/sentadilla-con-barra.gif"
              }
            },
            {
              "nombre": "Extensión de piernas en máquina",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/extension-de-piernas-en-maquina.gif",
              "series": 3,
              "repeticiones": "12",
              "descanso": "75s",
              "rir": "3",
              "bloque": "normal",
              "notas": "Extiende completamente y sostén 1 segundo arriba. Baja en 2-3 segundos.",
              "variacion": {
                "nombre": "Sentadilla goblet",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/sentadilla-goblet.gif"
              }
            },
            {
              "nombre": "Sentadilla Hack",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/sentadilla-hacka.gif",
              "series": 3,
              "repeticiones": "12",
              "descanso": "90s",
              "rir": "3",
              "bloque": "normal",
              "notas": "Pies juntos o separados según comodidad. Descarga el peso en los talones, no en la punta.",
              "variacion": {
                "nombre": "Sentadilla con mancuernas",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/sentadilla-con-mancuernas.gif"
              }
            },
            {
              "nombre": "Zancada inversa con mancuernas",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/zancada-inversa-con-mancuernas.gif",
              "series": 3,
              "repeticiones": "12 c/pierna",
              "descanso": "75s",
              "rir": "3",
              "bloque": "normal",
              "notas": "Da un paso atrás y baja la rodilla trasera casi al piso. El torso recto. Alternas piernas.",
              "variacion": {
                "nombre": "Zancada frontal con mancuerna",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/zancada-frontal-con-mancuerna.gif"
              }
            },
            {
              "nombre": "Elevación de talones en máquina",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/elevacion-de-talones-en-maquina.gif",
              "series": 3,
              "repeticiones": "15",
              "descanso": "45s",
              "rir": "1",
              "bloque": "normal",
              "notas": "Estira completo abajo, eleva máximo arriba y sostén 1 segundo. Sin rebote.",
              "variacion": {
                "nombre": "Elevación de talones sentado",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/elevacion-de-talones-sentado.gif"
              }
            },
            {
              "nombre": "Caminadora inclinada",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/caminadora-inclinada.gif",
              "is_cardio": true,
              "series": 1,
              "repeticiones": "20 min",
              "descanso": "-",
              "notas": "5-6 km/h, 10-12% de inclinación. Post-pesas. Sin agarrarte de las manijas. FC objetivo 120-140 bpm."
            }
          ]
        },
        {
          "dia": 3,
          "dia_semana": "Miércoles",
          "nombre": "Miércoles — Espalda + Bíceps + Abdomen",
          "tipo": "jale",
          "grupo_muscular": "Espalda, Bíceps, Core",
          "duracion_estimada": "70-75 min",
          "calentamiento": "5 min en caminadora + rotaciones de hombro + 2 series de 15 reps en jalón con peso liviano",
          "vuelta_calma": "5 min: estiramiento del grupo muscular principal del día.",
          "ejercicios": [
            {
              "nombre": "Jalón al pecho en máquina",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/jalon-al-pecho-en-maquina.gif",
              "series": 3,
              "repeticiones": "12",
              "descanso": "90s",
              "rir": "3",
              "bloque": "normal",
              "notas": "Jala llevando los codos hacia las caderas, no solo hacia abajo. Aprieta la espalda al final.",
              "variacion": {
                "nombre": "Jalón al pecho agarre supino",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/jalon-al-pecho-agarre-supino.gif"
              }
            },
            {
              "nombre": "Remo sentado en máquina",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/remo-sentado-en-maquina.gif",
              "series": 3,
              "repeticiones": "12",
              "descanso": "90s",
              "rir": "3",
              "bloque": "normal",
              "notas": "Jala hacia el abdomen. Aprieta los omóplatos juntos al final. Suelta controlado.",
              "variacion": {
                "nombre": "Remo en polea sentado",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/remo-en-polea-sentado.gif"
              }
            },
            {
              "nombre": "Pullover con mancuerna",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/pullover-con-mancuerna.gif",
              "series": 3,
              "repeticiones": "12",
              "descanso": "75s",
              "rir": "3",
              "bloque": "normal",
              "notas": "Mantén codos ligeramente doblados. Siente el estiramiento del dorsal al llevar la mancuerna atrás.",
              "variacion": {
                "nombre": "Pullover en polea con cuerda",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/pullover-en-polea-con-cuerda.gif"
              }
            },
            {
              "nombre": "Curl bíceps con barra",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/curl-biceps-con-barra.gif",
              "series": 3,
              "repeticiones": "12",
              "descanso": "60s",
              "rir": "3",
              "bloque": "normal",
              "notas": "Codos pegados al cuerpo. No balancees el torso. Baja lento en 2-3 segundos.",
              "variacion": {
                "nombre": "Curl predicador con barra EZ",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/curl-predicador-con-barra-ez.gif"
              }
            },
            {
              "nombre": "Curl martillo con mancuerna",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/curl-martillo-con-mancuerna.gif",
              "series": 3,
              "repeticiones": "12",
              "descanso": "60s",
              "rir": "3",
              "bloque": "normal",
              "notas": "Agarre neutro (como si sostuvieras un martillo). Trabajas el braquial además del bíceps.",
              "variacion": {
                "nombre": "Curl martillo en polea con cuerda",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/curl-martillo-en-polea-con-cuerda.gif"
              }
            },
            {
              "nombre": "Crunch abdominal en máquina total",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/crunch-abdominal-en-maquina-total.gif",
              "series": 3,
              "repeticiones": "15",
              "descanso": "45s",
              "rir": "1",
              "bloque": "normal",
              "notas": "Contrae fuerte en cada rep, sostén 1 segundo abajo. No uses inercia.",
              "variacion": {
                "nombre": "Crunch en polea arrodillado",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/crunch-en-polea-arrodillado.gif"
              }
            },
            {
              "nombre": "Caminadora inclinada",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/caminadora-inclinada.gif",
              "is_cardio": true,
              "series": 1,
              "repeticiones": "20 min",
              "descanso": "-",
              "notas": "5-6 km/h, 10-12% de inclinación. Post-pesas. Sin agarrarte de las manijas. FC objetivo 120-140 bpm."
            }
          ]
        },
        {
          "dia": 4,
          "dia_semana": "Jueves",
          "nombre": "Jueves — Femorales + Glúteos + Pantorrilla",
          "tipo": "piernas",
          "grupo_muscular": "Femorales, Glúteos, Pantorrilla",
          "duracion_estimada": "70-75 min",
          "calentamiento": "5 min en caminadora + movilidad de cadera + 2 series de 10 reps de buenos días sin peso",
          "vuelta_calma": "5 min: estiramiento del grupo muscular principal del día.",
          "ejercicios": [
            {
              "nombre": "Peso muerto rumano con barra",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/peso-muerto-rumano-con-barra.gif",
              "series": 3,
              "repeticiones": "12",
              "descanso": "90s",
              "rir": "3",
              "bloque": "normal",
              "notas": "Espalda recta siempre. Empuja las caderas hacia atrás y baja la barra rozando las piernas. Siente el femoral.",
              "variacion": {
                "nombre": "Peso muerto rumano con mancuerna",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/peso-muerto-rumano-con-mancuerna.gif"
              }
            },
            {
              "nombre": "Curl femoral acostado en máquina",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/curl-femoral-acostado-en-maquina.gif",
              "series": 3,
              "repeticiones": "12",
              "descanso": "75s",
              "rir": "3",
              "bloque": "normal",
              "notas": "Curva completa: extiende completamente abajo, contrae hasta el fondo arriba. Sin rebote.",
              "variacion": {
                "nombre": "Curl femoral sentado",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/curl-femoral-sentado.gif"
              }
            },
            {
              "nombre": "Hip thrust con barra",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/hipthrust-con-barra.gif",
              "series": 3,
              "repeticiones": "12",
              "descanso": "90s",
              "rir": "3",
              "bloque": "normal",
              "notas": "Empuja con los talones, no con los dedos. Aprieta el glúteo 1 segundo arriba. La barra va sobre la cadera con pad.",
              "variacion": {
                "nombre": "Puente de glúteo con barra",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/puente-de-gluteo-con-barra.gif"
              }
            },
            {
              "nombre": "Patada trasera en máquina",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/patada-trasera-en-maquina.gif",
              "series": 3,
              "repeticiones": "12 c/pierna",
              "descanso": "60s",
              "rir": "3",
              "bloque": "normal",
              "notas": "Extiende la cadera completamente atrás y aprieta el glúteo. Mantén el torso estable.",
              "variacion": {
                "nombre": "Patada trasera en polea",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/patada-trasera-en-polea.gif"
              }
            },
            {
              "nombre": "Elevación de talones sentado",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/elevacion-de-talones-sentado.gif",
              "series": 3,
              "repeticiones": "15",
              "descanso": "45s",
              "rir": "1",
              "bloque": "normal",
              "notas": "Estiramiento completo abajo, máximo arriba. Posición sentada aísla el sóleo.",
              "variacion": {
                "nombre": "Pantorrillas en prensa de pierna",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/pantorrillas-en-prensa-de-pierna.gif"
              }
            },
            {
              "nombre": "Caminadora inclinada",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/caminadora-inclinada.gif",
              "is_cardio": true,
              "series": 1,
              "repeticiones": "20 min",
              "descanso": "-",
              "notas": "5-6 km/h, 10-12% de inclinación. Post-pesas. Sin agarrarte de las manijas. FC objetivo 120-140 bpm."
            }
          ]
        },
        {
          "dia": 5,
          "dia_semana": "Viernes",
          "nombre": "Viernes — Hombros + Bíceps + Tríceps + Abdomen",
          "tipo": "empuje",
          "grupo_muscular": "Hombros, Bíceps, Tríceps, Core",
          "duracion_estimada": "70-75 min",
          "calentamiento": "5 min en caminadora + rotaciones de hombro + 2 series de 15 reps elevaciones laterales sin peso",
          "vuelta_calma": "5 min: estiramiento del grupo muscular principal del día.",
          "ejercicios": [
            {
              "nombre": "Press de hombro en máquina",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/press-de-hombro-en-maquina.gif",
              "series": 3,
              "repeticiones": "12",
              "descanso": "90s",
              "rir": "3",
              "bloque": "normal",
              "notas": "No bloquees los codos arriba. Baja hasta los 90° del codo. Torso erguido.",
              "variacion": {
                "nombre": "Press Arnold con mancuerna",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/press-arnold-con-mancuerna.gif"
              }
            },
            {
              "nombre": "Elevación lateral con mancuerna",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/elevacion-lateral-con-mancuerna.gif",
              "series": 3,
              "repeticiones": "12",
              "descanso": "60s",
              "rir": "3",
              "bloque": "normal",
              "notas": "Codos ligeramente doblados. Eleva hasta la altura del hombro, no más. Control total.",
              "variacion": {
                "nombre": "Elevaciones laterales en máquina",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/elevaciones-laterales-en-maquina.gif"
              }
            },
            {
              "nombre": "Elevaciones posteriores sobre banco inclinado",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/elevaciones-posteriores-sobre-banco-inclinado.gif",
              "series": 3,
              "repeticiones": "12",
              "descanso": "60s",
              "rir": "3",
              "bloque": "normal",
              "notas": "Pecho apoyado en el banco inclinado. Eleva los brazos hacia afuera contrayendo el deltoides posterior.",
              "variacion": {
                "nombre": "Apertura posteriores con mancuerna sentado",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/apertura-posteriores-con-mancuerna-sentado.gif"
              }
            },
            {
              "nombre": "Curl bíceps con mancuerna",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/curl-biceps-con-mancuerna.gif",
              "series": 3,
              "repeticiones": "12",
              "descanso": "60s",
              "rir": "3",
              "bloque": "normal",
              "notas": "Alterna brazos o haz los dos al tiempo. Codos fijos, sin balanceo.",
              "variacion": {
                "nombre": "Curl concentrado con mancuerna",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/curl-concentrado-con-mancuerna.gif"
              }
            },
            {
              "nombre": "Extensión de tríceps sobre cabeza en polea con cuerda",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/extension-de-triceps-sobre-cabeza-en-polea-con-cuerda.gif",
              "series": 3,
              "repeticiones": "12",
              "descanso": "60s",
              "rir": "3",
              "bloque": "normal",
              "notas": "Inclínate un poco hacia adelante. Estira el tríceps completamente hacia atrás.",
              "variacion": {
                "nombre": "Extensión de tríceps en máquina",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/extension-de-triceps-en-maquina.gif"
              }
            },
            {
              "nombre": "Plancha abdominal",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/plancha-abdominal.gif",
              "series": 3,
              "repeticiones": "20s",
              "descanso": "45s",
              "rir": "0",
              "bloque": "normal",
              "notas": "Cuerpo recto de cabeza a talones. Aprieta el abdomen, los glúteos y los hombros. No dejes caer las caderas.",
              "variacion": {
                "nombre": "Plancha de rodillas",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/plancha-de-rodillas.gif"
              }
            },
            {
              "nombre": "Caminadora inclinada",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/caminadora-inclinada.gif",
              "is_cardio": true,
              "series": 1,
              "repeticiones": "20 min",
              "descanso": "-",
              "notas": "5-6 km/h, 10-12% de inclinación. Post-pesas. Sin agarrarte de las manijas. FC objetivo 120-140 bpm."
            }
          ]
        }
      ]
    },
    {
      "semana": 2,
      "numero": 2,
      "fase": "Hipertrofia",
      "fase_nombre": "Semana 2 — Hipertrofia",
      "nombre_bloque": "Semana 2 — Hipertrofia",
      "nota_semana": "Subimos a 4 series. Aquí empieza a construirse el músculo. Sube peso si las 10 reps se sienten fáciles.",
      "dias": [
        {
          "dia": 1,
          "dia_semana": "Lunes",
          "nombre": "Lunes — Pecho + Tríceps + Abdomen",
          "tipo": "empuje",
          "grupo_muscular": "Pecho, Tríceps, Core",
          "duracion_estimada": "70-75 min",
          "calentamiento": "5 min en caminadora suave + rotaciones de hombro + 2 series de 15 reps en máquina de pecho sin peso",
          "vuelta_calma": "5 min: estiramiento del grupo muscular principal del día.",
          "ejercicios": [
            {
              "nombre": "Press de pecho en máquina",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/press-de-pecho-en-maquina.gif",
              "series": 4,
              "repeticiones": "10",
              "descanso": "90s",
              "rir": "2",
              "bloque": "normal",
              "notas": "Pecho arriba, omóplatos contra el respaldo. Empuja explosivo, baja controlado en 2 segundos.",
              "variacion": {
                "nombre": "Press de banca con barra",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/press-banca-barra.gif"
              }
            },
            {
              "nombre": "Press de pecho inclinado en máquina",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/press-de-pecho-inclinado-en-maquina.gif",
              "series": 4,
              "repeticiones": "10",
              "descanso": "90s",
              "rir": "2",
              "bloque": "normal",
              "notas": "La inclinación ataca la parte alta del pecho. No arquees la espalda baja.",
              "variacion": {
                "nombre": "Press de banca inclinado con barra",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/press-banca-inclinado-con-barra.gif"
              }
            },
            {
              "nombre": "Aperturas en peck deck",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/aperturas-en-peck-deck.gif",
              "series": 4,
              "repeticiones": "10",
              "descanso": "60s",
              "rir": "2",
              "bloque": "normal",
              "notas": "Abraza un árbol imaginario. Siente el estiramiento en el pecho al abrir, contrae fuerte al cerrar.",
              "variacion": {
                "nombre": "Aperturas en polea de pie",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/aperturas-en-polea-de-pie.gif"
              }
            },
            {
              "nombre": "Extensión de tríceps en polea con cuerda",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/extension-de-triceps-en-polea-con-cuerda.gif",
              "series": 4,
              "repeticiones": "10",
              "descanso": "60s",
              "rir": "2",
              "bloque": "normal",
              "notas": "Codos pegados al cuerpo, solo mueve el antebrazo. Al bajar, separa la cuerda hacia afuera.",
              "variacion": {
                "nombre": "Extensión de tríceps en máquina",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/extension-de-triceps-en-maquina.gif"
              }
            },
            {
              "nombre": "Fondos de tríceps en máquina",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/fondos-de-triceps-en-maquina.gif",
              "series": 4,
              "repeticiones": "10",
              "descanso": "60s",
              "rir": "2",
              "bloque": "normal",
              "notas": "Mantén el torso vertical, no te inclines hacia adelante. Extiende los codos completamente.",
              "variacion": {
                "nombre": "Extensión de tríceps en polea agarre inverso",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/extension-de-triceps-en-polea-agarre-inverso.gif"
              }
            },
            {
              "nombre": "Crunch en polea arrodillado",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/crunch-en-polea-arrodillado.gif",
              "series": 4,
              "repeticiones": "12",
              "descanso": "45s",
              "rir": "1",
              "bloque": "normal",
              "notas": "Contrae el abdomen, lleva los codos hacia las rodillas. No jales con los brazos.",
              "variacion": {
                "nombre": "Crunch abdominal en máquina total",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/crunch-abdominal-en-maquina-total.gif"
              }
            },
            {
              "nombre": "Caminadora inclinada",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/caminadora-inclinada.gif",
              "is_cardio": true,
              "series": 1,
              "repeticiones": "20 min",
              "descanso": "-",
              "notas": "5-6 km/h, 10-12% de inclinación. Post-pesas. Sin agarrarte de las manijas. FC objetivo 120-140 bpm."
            }
          ]
        },
        {
          "dia": 2,
          "dia_semana": "Martes",
          "nombre": "Martes — Cuádriceps + Pantorrilla",
          "tipo": "piernas",
          "grupo_muscular": "Cuádriceps, Pantorrilla",
          "duracion_estimada": "70-75 min",
          "calentamiento": "5 min en caminadora + 2 series de 15 reps de sentadillas sin peso + movilidad de cadera",
          "vuelta_calma": "5 min: estiramiento del grupo muscular principal del día.",
          "ejercicios": [
            {
              "nombre": "Prensa de piernas abierto",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/presa-de-piernas-abierto.gif",
              "series": 4,
              "repeticiones": "10",
              "descanso": "90s",
              "rir": "2",
              "bloque": "normal",
              "notas": "Pies separados al ancho de los hombros. No bloquees las rodillas arriba. Baja hasta 90° o menos.",
              "variacion": {
                "nombre": "Sentadilla con barra",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/sentadilla-con-barra.gif"
              }
            },
            {
              "nombre": "Extensión de piernas en máquina",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/extension-de-piernas-en-maquina.gif",
              "series": 4,
              "repeticiones": "10",
              "descanso": "75s",
              "rir": "2",
              "bloque": "normal",
              "notas": "Extiende completamente y sostén 1 segundo arriba. Baja en 2-3 segundos.",
              "variacion": {
                "nombre": "Sentadilla goblet",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/sentadilla-goblet.gif"
              }
            },
            {
              "nombre": "Sentadilla Hack",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/sentadilla-hacka.gif",
              "series": 4,
              "repeticiones": "10",
              "descanso": "90s",
              "rir": "2",
              "bloque": "normal",
              "notas": "Pies juntos o separados según comodidad. Descarga el peso en los talones, no en la punta.",
              "variacion": {
                "nombre": "Sentadilla con mancuernas",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/sentadilla-con-mancuernas.gif"
              }
            },
            {
              "nombre": "Zancada inversa con mancuernas",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/zancada-inversa-con-mancuernas.gif",
              "series": 4,
              "repeticiones": "10 c/pierna",
              "descanso": "75s",
              "rir": "2",
              "bloque": "normal",
              "notas": "Da un paso atrás y baja la rodilla trasera casi al piso. El torso recto. Alternas piernas.",
              "variacion": {
                "nombre": "Zancada frontal con mancuerna",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/zancada-frontal-con-mancuerna.gif"
              }
            },
            {
              "nombre": "Elevación de talones en máquina",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/elevacion-de-talones-en-maquina.gif",
              "series": 4,
              "repeticiones": "12",
              "descanso": "45s",
              "rir": "1",
              "bloque": "normal",
              "notas": "Estira completo abajo, eleva máximo arriba y sostén 1 segundo. Sin rebote.",
              "variacion": {
                "nombre": "Elevación de talones sentado",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/elevacion-de-talones-sentado.gif"
              }
            },
            {
              "nombre": "Caminadora inclinada",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/caminadora-inclinada.gif",
              "is_cardio": true,
              "series": 1,
              "repeticiones": "20 min",
              "descanso": "-",
              "notas": "5-6 km/h, 10-12% de inclinación. Post-pesas. Sin agarrarte de las manijas. FC objetivo 120-140 bpm."
            }
          ]
        },
        {
          "dia": 3,
          "dia_semana": "Miércoles",
          "nombre": "Miércoles — Espalda + Bíceps + Abdomen",
          "tipo": "jale",
          "grupo_muscular": "Espalda, Bíceps, Core",
          "duracion_estimada": "70-75 min",
          "calentamiento": "5 min en caminadora + rotaciones de hombro + 2 series de 15 reps en jalón con peso liviano",
          "vuelta_calma": "5 min: estiramiento del grupo muscular principal del día.",
          "ejercicios": [
            {
              "nombre": "Jalón al pecho en máquina",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/jalon-al-pecho-en-maquina.gif",
              "series": 4,
              "repeticiones": "10",
              "descanso": "90s",
              "rir": "2",
              "bloque": "normal",
              "notas": "Jala llevando los codos hacia las caderas, no solo hacia abajo. Aprieta la espalda al final.",
              "variacion": {
                "nombre": "Jalón al pecho agarre supino",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/jalon-al-pecho-agarre-supino.gif"
              }
            },
            {
              "nombre": "Remo sentado en máquina",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/remo-sentado-en-maquina.gif",
              "series": 4,
              "repeticiones": "10",
              "descanso": "90s",
              "rir": "2",
              "bloque": "normal",
              "notas": "Jala hacia el abdomen. Aprieta los omóplatos juntos al final. Suelta controlado.",
              "variacion": {
                "nombre": "Remo en polea sentado",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/remo-en-polea-sentado.gif"
              }
            },
            {
              "nombre": "Pullover con mancuerna",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/pullover-con-mancuerna.gif",
              "series": 4,
              "repeticiones": "10",
              "descanso": "75s",
              "rir": "2",
              "bloque": "normal",
              "notas": "Mantén codos ligeramente doblados. Siente el estiramiento del dorsal al llevar la mancuerna atrás.",
              "variacion": {
                "nombre": "Pullover en polea con cuerda",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/pullover-en-polea-con-cuerda.gif"
              }
            },
            {
              "nombre": "Curl bíceps con barra",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/curl-biceps-con-barra.gif",
              "series": 4,
              "repeticiones": "10",
              "descanso": "60s",
              "rir": "2",
              "bloque": "normal",
              "notas": "Codos pegados al cuerpo. No balancees el torso. Baja lento en 2-3 segundos.",
              "variacion": {
                "nombre": "Curl predicador con barra EZ",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/curl-predicador-con-barra-ez.gif"
              }
            },
            {
              "nombre": "Curl martillo con mancuerna",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/curl-martillo-con-mancuerna.gif",
              "series": 4,
              "repeticiones": "10",
              "descanso": "60s",
              "rir": "2",
              "bloque": "normal",
              "notas": "Agarre neutro (como si sostuvieras un martillo). Trabajas el braquial además del bíceps.",
              "variacion": {
                "nombre": "Curl martillo en polea con cuerda",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/curl-martillo-en-polea-con-cuerda.gif"
              }
            },
            {
              "nombre": "Crunch abdominal en máquina total",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/crunch-abdominal-en-maquina-total.gif",
              "series": 4,
              "repeticiones": "12",
              "descanso": "45s",
              "rir": "1",
              "bloque": "normal",
              "notas": "Contrae fuerte en cada rep, sostén 1 segundo abajo. No uses inercia.",
              "variacion": {
                "nombre": "Crunch en polea arrodillado",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/crunch-en-polea-arrodillado.gif"
              }
            },
            {
              "nombre": "Caminadora inclinada",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/caminadora-inclinada.gif",
              "is_cardio": true,
              "series": 1,
              "repeticiones": "20 min",
              "descanso": "-",
              "notas": "5-6 km/h, 10-12% de inclinación. Post-pesas. Sin agarrarte de las manijas. FC objetivo 120-140 bpm."
            }
          ]
        },
        {
          "dia": 4,
          "dia_semana": "Jueves",
          "nombre": "Jueves — Femorales + Glúteos + Pantorrilla",
          "tipo": "piernas",
          "grupo_muscular": "Femorales, Glúteos, Pantorrilla",
          "duracion_estimada": "70-75 min",
          "calentamiento": "5 min en caminadora + movilidad de cadera + 2 series de 10 reps de buenos días sin peso",
          "vuelta_calma": "5 min: estiramiento del grupo muscular principal del día.",
          "ejercicios": [
            {
              "nombre": "Peso muerto rumano con barra",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/peso-muerto-rumano-con-barra.gif",
              "series": 4,
              "repeticiones": "10",
              "descanso": "90s",
              "rir": "2",
              "bloque": "normal",
              "notas": "Espalda recta siempre. Empuja las caderas hacia atrás y baja la barra rozando las piernas. Siente el femoral.",
              "variacion": {
                "nombre": "Peso muerto rumano con mancuerna",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/peso-muerto-rumano-con-mancuerna.gif"
              }
            },
            {
              "nombre": "Curl femoral acostado en máquina",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/curl-femoral-acostado-en-maquina.gif",
              "series": 4,
              "repeticiones": "10",
              "descanso": "75s",
              "rir": "2",
              "bloque": "normal",
              "notas": "Curva completa: extiende completamente abajo, contrae hasta el fondo arriba. Sin rebote.",
              "variacion": {
                "nombre": "Curl femoral sentado",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/curl-femoral-sentado.gif"
              }
            },
            {
              "nombre": "Hip thrust con barra",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/hipthrust-con-barra.gif",
              "series": 4,
              "repeticiones": "10",
              "descanso": "90s",
              "rir": "2",
              "bloque": "normal",
              "notas": "Empuja con los talones, no con los dedos. Aprieta el glúteo 1 segundo arriba. La barra va sobre la cadera con pad.",
              "variacion": {
                "nombre": "Puente de glúteo con barra",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/puente-de-gluteo-con-barra.gif"
              }
            },
            {
              "nombre": "Patada trasera en máquina",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/patada-trasera-en-maquina.gif",
              "series": 4,
              "repeticiones": "10 c/pierna",
              "descanso": "60s",
              "rir": "2",
              "bloque": "normal",
              "notas": "Extiende la cadera completamente atrás y aprieta el glúteo. Mantén el torso estable.",
              "variacion": {
                "nombre": "Patada trasera en polea",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/patada-trasera-en-polea.gif"
              }
            },
            {
              "nombre": "Elevación de talones sentado",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/elevacion-de-talones-sentado.gif",
              "series": 4,
              "repeticiones": "12",
              "descanso": "45s",
              "rir": "1",
              "bloque": "normal",
              "notas": "Estiramiento completo abajo, máximo arriba. Posición sentada aísla el sóleo.",
              "variacion": {
                "nombre": "Pantorrillas en prensa de pierna",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/pantorrillas-en-prensa-de-pierna.gif"
              }
            },
            {
              "nombre": "Caminadora inclinada",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/caminadora-inclinada.gif",
              "is_cardio": true,
              "series": 1,
              "repeticiones": "20 min",
              "descanso": "-",
              "notas": "5-6 km/h, 10-12% de inclinación. Post-pesas. Sin agarrarte de las manijas. FC objetivo 120-140 bpm."
            }
          ]
        },
        {
          "dia": 5,
          "dia_semana": "Viernes",
          "nombre": "Viernes — Hombros + Bíceps + Tríceps + Abdomen",
          "tipo": "empuje",
          "grupo_muscular": "Hombros, Bíceps, Tríceps, Core",
          "duracion_estimada": "70-75 min",
          "calentamiento": "5 min en caminadora + rotaciones de hombro + 2 series de 15 reps elevaciones laterales sin peso",
          "vuelta_calma": "5 min: estiramiento del grupo muscular principal del día.",
          "ejercicios": [
            {
              "nombre": "Press de hombro en máquina",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/press-de-hombro-en-maquina.gif",
              "series": 4,
              "repeticiones": "10",
              "descanso": "90s",
              "rir": "2",
              "bloque": "normal",
              "notas": "No bloquees los codos arriba. Baja hasta los 90° del codo. Torso erguido.",
              "variacion": {
                "nombre": "Press Arnold con mancuerna",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/press-arnold-con-mancuerna.gif"
              }
            },
            {
              "nombre": "Elevación lateral con mancuerna",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/elevacion-lateral-con-mancuerna.gif",
              "series": 4,
              "repeticiones": "10",
              "descanso": "60s",
              "rir": "2",
              "bloque": "normal",
              "notas": "Codos ligeramente doblados. Eleva hasta la altura del hombro, no más. Control total.",
              "variacion": {
                "nombre": "Elevaciones laterales en máquina",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/elevaciones-laterales-en-maquina.gif"
              }
            },
            {
              "nombre": "Elevaciones posteriores sobre banco inclinado",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/elevaciones-posteriores-sobre-banco-inclinado.gif",
              "series": 4,
              "repeticiones": "10",
              "descanso": "60s",
              "rir": "2",
              "bloque": "normal",
              "notas": "Pecho apoyado en el banco inclinado. Eleva los brazos hacia afuera contrayendo el deltoides posterior.",
              "variacion": {
                "nombre": "Apertura posteriores con mancuerna sentado",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/apertura-posteriores-con-mancuerna-sentado.gif"
              }
            },
            {
              "nombre": "Curl bíceps con mancuerna",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/curl-biceps-con-mancuerna.gif",
              "series": 4,
              "repeticiones": "10",
              "descanso": "60s",
              "rir": "2",
              "bloque": "normal",
              "notas": "Alterna brazos o haz los dos al tiempo. Codos fijos, sin balanceo.",
              "variacion": {
                "nombre": "Curl concentrado con mancuerna",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/curl-concentrado-con-mancuerna.gif"
              }
            },
            {
              "nombre": "Extensión de tríceps sobre cabeza en polea con cuerda",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/extension-de-triceps-sobre-cabeza-en-polea-con-cuerda.gif",
              "series": 4,
              "repeticiones": "10",
              "descanso": "60s",
              "rir": "2",
              "bloque": "normal",
              "notas": "Inclínate un poco hacia adelante. Estira el tríceps completamente hacia atrás.",
              "variacion": {
                "nombre": "Extensión de tríceps en máquina",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/extension-de-triceps-en-maquina.gif"
              }
            },
            {
              "nombre": "Plancha abdominal",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/plancha-abdominal.gif",
              "series": 3,
              "repeticiones": "30s",
              "descanso": "45s",
              "rir": "0",
              "bloque": "normal",
              "notas": "Cuerpo recto de cabeza a talones. Aprieta el abdomen, los glúteos y los hombros. No dejes caer las caderas.",
              "variacion": {
                "nombre": "Plancha de rodillas",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/plancha-de-rodillas.gif"
              }
            },
            {
              "nombre": "Caminadora inclinada",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/caminadora-inclinada.gif",
              "is_cardio": true,
              "series": 1,
              "repeticiones": "20 min",
              "descanso": "-",
              "notas": "5-6 km/h, 10-12% de inclinación. Post-pesas. Sin agarrarte de las manijas. FC objetivo 120-140 bpm."
            }
          ]
        }
      ]
    },
    {
      "semana": 3,
      "numero": 3,
      "fase": "Fuerza",
      "fase_nombre": "Semana 3 — Fuerza",
      "nombre_bloque": "Semana 3 — Fuerza",
      "nota_semana": "4 series de 8 reps con más peso. Subí 5-10% vs semana 2. Si no puedes, mantén el peso.",
      "dias": [
        {
          "dia": 1,
          "dia_semana": "Lunes",
          "nombre": "Lunes — Pecho + Tríceps + Abdomen",
          "tipo": "empuje",
          "grupo_muscular": "Pecho, Tríceps, Core",
          "duracion_estimada": "70-75 min",
          "calentamiento": "5 min en caminadora suave + rotaciones de hombro + 2 series de 15 reps en máquina de pecho sin peso",
          "vuelta_calma": "5 min: estiramiento del grupo muscular principal del día.",
          "ejercicios": [
            {
              "nombre": "Press de pecho en máquina",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/press-de-pecho-en-maquina.gif",
              "series": 4,
              "repeticiones": "8",
              "descanso": "90s",
              "rir": "1",
              "bloque": "normal",
              "notas": "Pecho arriba, omóplatos contra el respaldo. Empuja explosivo, baja controlado en 2 segundos.",
              "variacion": {
                "nombre": "Press de banca con barra",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/press-banca-barra.gif"
              }
            },
            {
              "nombre": "Press de pecho inclinado en máquina",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/press-de-pecho-inclinado-en-maquina.gif",
              "series": 4,
              "repeticiones": "8",
              "descanso": "90s",
              "rir": "1",
              "bloque": "normal",
              "notas": "La inclinación ataca la parte alta del pecho. No arquees la espalda baja.",
              "variacion": {
                "nombre": "Press de banca inclinado con barra",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/press-banca-inclinado-con-barra.gif"
              }
            },
            {
              "nombre": "Aperturas en peck deck",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/aperturas-en-peck-deck.gif",
              "series": 4,
              "repeticiones": "8",
              "descanso": "60s",
              "rir": "1",
              "bloque": "normal",
              "notas": "Abraza un árbol imaginario. Siente el estiramiento en el pecho al abrir, contrae fuerte al cerrar.",
              "variacion": {
                "nombre": "Aperturas en polea de pie",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/aperturas-en-polea-de-pie.gif"
              }
            },
            {
              "nombre": "Extensión de tríceps en polea con cuerda",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/extension-de-triceps-en-polea-con-cuerda.gif",
              "series": 4,
              "repeticiones": "8",
              "descanso": "60s",
              "rir": "1",
              "bloque": "normal",
              "notas": "Codos pegados al cuerpo, solo mueve el antebrazo. Al bajar, separa la cuerda hacia afuera.",
              "variacion": {
                "nombre": "Extensión de tríceps en máquina",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/extension-de-triceps-en-maquina.gif"
              }
            },
            {
              "nombre": "Fondos de tríceps en máquina",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/fondos-de-triceps-en-maquina.gif",
              "series": 4,
              "repeticiones": "8",
              "descanso": "60s",
              "rir": "1",
              "bloque": "normal",
              "notas": "Mantén el torso vertical, no te inclines hacia adelante. Extiende los codos completamente.",
              "variacion": {
                "nombre": "Extensión de tríceps en polea agarre inverso",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/extension-de-triceps-en-polea-agarre-inverso.gif"
              }
            },
            {
              "nombre": "Crunch en polea arrodillado",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/crunch-en-polea-arrodillado.gif",
              "series": 4,
              "repeticiones": "12",
              "descanso": "45s",
              "rir": "1",
              "bloque": "normal",
              "notas": "Contrae el abdomen, lleva los codos hacia las rodillas. No jales con los brazos.",
              "variacion": {
                "nombre": "Crunch abdominal en máquina total",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/crunch-abdominal-en-maquina-total.gif"
              }
            },
            {
              "nombre": "Caminadora inclinada",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/caminadora-inclinada.gif",
              "is_cardio": true,
              "series": 1,
              "repeticiones": "20 min",
              "descanso": "-",
              "notas": "5-6 km/h, 10-12% de inclinación. Post-pesas. Sin agarrarte de las manijas. FC objetivo 120-140 bpm."
            }
          ]
        },
        {
          "dia": 2,
          "dia_semana": "Martes",
          "nombre": "Martes — Cuádriceps + Pantorrilla",
          "tipo": "piernas",
          "grupo_muscular": "Cuádriceps, Pantorrilla",
          "duracion_estimada": "70-75 min",
          "calentamiento": "5 min en caminadora + 2 series de 15 reps de sentadillas sin peso + movilidad de cadera",
          "vuelta_calma": "5 min: estiramiento del grupo muscular principal del día.",
          "ejercicios": [
            {
              "nombre": "Prensa de piernas abierto",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/presa-de-piernas-abierto.gif",
              "series": 4,
              "repeticiones": "8",
              "descanso": "90s",
              "rir": "1",
              "bloque": "normal",
              "notas": "Pies separados al ancho de los hombros. No bloquees las rodillas arriba. Baja hasta 90° o menos.",
              "variacion": {
                "nombre": "Sentadilla con barra",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/sentadilla-con-barra.gif"
              }
            },
            {
              "nombre": "Extensión de piernas en máquina",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/extension-de-piernas-en-maquina.gif",
              "series": 4,
              "repeticiones": "8",
              "descanso": "75s",
              "rir": "1",
              "bloque": "normal",
              "notas": "Extiende completamente y sostén 1 segundo arriba. Baja en 2-3 segundos.",
              "variacion": {
                "nombre": "Sentadilla goblet",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/sentadilla-goblet.gif"
              }
            },
            {
              "nombre": "Sentadilla Hack",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/sentadilla-hacka.gif",
              "series": 4,
              "repeticiones": "8",
              "descanso": "90s",
              "rir": "1",
              "bloque": "normal",
              "notas": "Pies juntos o separados según comodidad. Descarga el peso en los talones, no en la punta.",
              "variacion": {
                "nombre": "Sentadilla con mancuernas",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/sentadilla-con-mancuernas.gif"
              }
            },
            {
              "nombre": "Zancada inversa con mancuernas",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/zancada-inversa-con-mancuernas.gif",
              "series": 4,
              "repeticiones": "8 c/pierna",
              "descanso": "75s",
              "rir": "1",
              "bloque": "normal",
              "notas": "Da un paso atrás y baja la rodilla trasera casi al piso. El torso recto. Alternas piernas.",
              "variacion": {
                "nombre": "Zancada frontal con mancuerna",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/zancada-frontal-con-mancuerna.gif"
              }
            },
            {
              "nombre": "Elevación de talones en máquina",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/elevacion-de-talones-en-maquina.gif",
              "series": 4,
              "repeticiones": "12",
              "descanso": "45s",
              "rir": "1",
              "bloque": "normal",
              "notas": "Estira completo abajo, eleva máximo arriba y sostén 1 segundo. Sin rebote.",
              "variacion": {
                "nombre": "Elevación de talones sentado",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/elevacion-de-talones-sentado.gif"
              }
            },
            {
              "nombre": "Caminadora inclinada",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/caminadora-inclinada.gif",
              "is_cardio": true,
              "series": 1,
              "repeticiones": "20 min",
              "descanso": "-",
              "notas": "5-6 km/h, 10-12% de inclinación. Post-pesas. Sin agarrarte de las manijas. FC objetivo 120-140 bpm."
            }
          ]
        },
        {
          "dia": 3,
          "dia_semana": "Miércoles",
          "nombre": "Miércoles — Espalda + Bíceps + Abdomen",
          "tipo": "jale",
          "grupo_muscular": "Espalda, Bíceps, Core",
          "duracion_estimada": "70-75 min",
          "calentamiento": "5 min en caminadora + rotaciones de hombro + 2 series de 15 reps en jalón con peso liviano",
          "vuelta_calma": "5 min: estiramiento del grupo muscular principal del día.",
          "ejercicios": [
            {
              "nombre": "Jalón al pecho en máquina",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/jalon-al-pecho-en-maquina.gif",
              "series": 4,
              "repeticiones": "8",
              "descanso": "90s",
              "rir": "1",
              "bloque": "normal",
              "notas": "Jala llevando los codos hacia las caderas, no solo hacia abajo. Aprieta la espalda al final.",
              "variacion": {
                "nombre": "Jalón al pecho agarre supino",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/jalon-al-pecho-agarre-supino.gif"
              }
            },
            {
              "nombre": "Remo sentado en máquina",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/remo-sentado-en-maquina.gif",
              "series": 4,
              "repeticiones": "8",
              "descanso": "90s",
              "rir": "1",
              "bloque": "normal",
              "notas": "Jala hacia el abdomen. Aprieta los omóplatos juntos al final. Suelta controlado.",
              "variacion": {
                "nombre": "Remo en polea sentado",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/remo-en-polea-sentado.gif"
              }
            },
            {
              "nombre": "Pullover con mancuerna",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/pullover-con-mancuerna.gif",
              "series": 4,
              "repeticiones": "8",
              "descanso": "75s",
              "rir": "1",
              "bloque": "normal",
              "notas": "Mantén codos ligeramente doblados. Siente el estiramiento del dorsal al llevar la mancuerna atrás.",
              "variacion": {
                "nombre": "Pullover en polea con cuerda",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/pullover-en-polea-con-cuerda.gif"
              }
            },
            {
              "nombre": "Curl bíceps con barra",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/curl-biceps-con-barra.gif",
              "series": 4,
              "repeticiones": "8",
              "descanso": "60s",
              "rir": "1",
              "bloque": "normal",
              "notas": "Codos pegados al cuerpo. No balancees el torso. Baja lento en 2-3 segundos.",
              "variacion": {
                "nombre": "Curl predicador con barra EZ",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/curl-predicador-con-barra-ez.gif"
              }
            },
            {
              "nombre": "Curl martillo con mancuerna",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/curl-martillo-con-mancuerna.gif",
              "series": 4,
              "repeticiones": "8",
              "descanso": "60s",
              "rir": "1",
              "bloque": "normal",
              "notas": "Agarre neutro (como si sostuvieras un martillo). Trabajas el braquial además del bíceps.",
              "variacion": {
                "nombre": "Curl martillo en polea con cuerda",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/curl-martillo-en-polea-con-cuerda.gif"
              }
            },
            {
              "nombre": "Crunch abdominal en máquina total",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/crunch-abdominal-en-maquina-total.gif",
              "series": 4,
              "repeticiones": "12",
              "descanso": "45s",
              "rir": "1",
              "bloque": "normal",
              "notas": "Contrae fuerte en cada rep, sostén 1 segundo abajo. No uses inercia.",
              "variacion": {
                "nombre": "Crunch en polea arrodillado",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/crunch-en-polea-arrodillado.gif"
              }
            },
            {
              "nombre": "Caminadora inclinada",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/caminadora-inclinada.gif",
              "is_cardio": true,
              "series": 1,
              "repeticiones": "20 min",
              "descanso": "-",
              "notas": "5-6 km/h, 10-12% de inclinación. Post-pesas. Sin agarrarte de las manijas. FC objetivo 120-140 bpm."
            }
          ]
        },
        {
          "dia": 4,
          "dia_semana": "Jueves",
          "nombre": "Jueves — Femorales + Glúteos + Pantorrilla",
          "tipo": "piernas",
          "grupo_muscular": "Femorales, Glúteos, Pantorrilla",
          "duracion_estimada": "70-75 min",
          "calentamiento": "5 min en caminadora + movilidad de cadera + 2 series de 10 reps de buenos días sin peso",
          "vuelta_calma": "5 min: estiramiento del grupo muscular principal del día.",
          "ejercicios": [
            {
              "nombre": "Peso muerto rumano con barra",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/peso-muerto-rumano-con-barra.gif",
              "series": 4,
              "repeticiones": "8",
              "descanso": "90s",
              "rir": "1",
              "bloque": "normal",
              "notas": "Espalda recta siempre. Empuja las caderas hacia atrás y baja la barra rozando las piernas. Siente el femoral.",
              "variacion": {
                "nombre": "Peso muerto rumano con mancuerna",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/peso-muerto-rumano-con-mancuerna.gif"
              }
            },
            {
              "nombre": "Curl femoral acostado en máquina",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/curl-femoral-acostado-en-maquina.gif",
              "series": 4,
              "repeticiones": "8",
              "descanso": "75s",
              "rir": "1",
              "bloque": "normal",
              "notas": "Curva completa: extiende completamente abajo, contrae hasta el fondo arriba. Sin rebote.",
              "variacion": {
                "nombre": "Curl femoral sentado",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/curl-femoral-sentado.gif"
              }
            },
            {
              "nombre": "Hip thrust con barra",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/hipthrust-con-barra.gif",
              "series": 4,
              "repeticiones": "8",
              "descanso": "90s",
              "rir": "1",
              "bloque": "normal",
              "notas": "Empuja con los talones, no con los dedos. Aprieta el glúteo 1 segundo arriba. La barra va sobre la cadera con pad.",
              "variacion": {
                "nombre": "Puente de glúteo con barra",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/puente-de-gluteo-con-barra.gif"
              }
            },
            {
              "nombre": "Patada trasera en máquina",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/patada-trasera-en-maquina.gif",
              "series": 4,
              "repeticiones": "8 c/pierna",
              "descanso": "60s",
              "rir": "1",
              "bloque": "normal",
              "notas": "Extiende la cadera completamente atrás y aprieta el glúteo. Mantén el torso estable.",
              "variacion": {
                "nombre": "Patada trasera en polea",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/patada-trasera-en-polea.gif"
              }
            },
            {
              "nombre": "Elevación de talones sentado",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/elevacion-de-talones-sentado.gif",
              "series": 4,
              "repeticiones": "12",
              "descanso": "45s",
              "rir": "1",
              "bloque": "normal",
              "notas": "Estiramiento completo abajo, máximo arriba. Posición sentada aísla el sóleo.",
              "variacion": {
                "nombre": "Pantorrillas en prensa de pierna",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/pantorrillas-en-prensa-de-pierna.gif"
              }
            },
            {
              "nombre": "Caminadora inclinada",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/caminadora-inclinada.gif",
              "is_cardio": true,
              "series": 1,
              "repeticiones": "20 min",
              "descanso": "-",
              "notas": "5-6 km/h, 10-12% de inclinación. Post-pesas. Sin agarrarte de las manijas. FC objetivo 120-140 bpm."
            }
          ]
        },
        {
          "dia": 5,
          "dia_semana": "Viernes",
          "nombre": "Viernes — Hombros + Bíceps + Tríceps + Abdomen",
          "tipo": "empuje",
          "grupo_muscular": "Hombros, Bíceps, Tríceps, Core",
          "duracion_estimada": "70-75 min",
          "calentamiento": "5 min en caminadora + rotaciones de hombro + 2 series de 15 reps elevaciones laterales sin peso",
          "vuelta_calma": "5 min: estiramiento del grupo muscular principal del día.",
          "ejercicios": [
            {
              "nombre": "Press de hombro en máquina",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/press-de-hombro-en-maquina.gif",
              "series": 4,
              "repeticiones": "8",
              "descanso": "90s",
              "rir": "1",
              "bloque": "normal",
              "notas": "No bloquees los codos arriba. Baja hasta los 90° del codo. Torso erguido.",
              "variacion": {
                "nombre": "Press Arnold con mancuerna",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/press-arnold-con-mancuerna.gif"
              }
            },
            {
              "nombre": "Elevación lateral con mancuerna",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/elevacion-lateral-con-mancuerna.gif",
              "series": 4,
              "repeticiones": "8",
              "descanso": "60s",
              "rir": "1",
              "bloque": "normal",
              "notas": "Codos ligeramente doblados. Eleva hasta la altura del hombro, no más. Control total.",
              "variacion": {
                "nombre": "Elevaciones laterales en máquina",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/elevaciones-laterales-en-maquina.gif"
              }
            },
            {
              "nombre": "Elevaciones posteriores sobre banco inclinado",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/elevaciones-posteriores-sobre-banco-inclinado.gif",
              "series": 4,
              "repeticiones": "8",
              "descanso": "60s",
              "rir": "1",
              "bloque": "normal",
              "notas": "Pecho apoyado en el banco inclinado. Eleva los brazos hacia afuera contrayendo el deltoides posterior.",
              "variacion": {
                "nombre": "Apertura posteriores con mancuerna sentado",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/apertura-posteriores-con-mancuerna-sentado.gif"
              }
            },
            {
              "nombre": "Curl bíceps con mancuerna",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/curl-biceps-con-mancuerna.gif",
              "series": 4,
              "repeticiones": "8",
              "descanso": "60s",
              "rir": "1",
              "bloque": "normal",
              "notas": "Alterna brazos o haz los dos al tiempo. Codos fijos, sin balanceo.",
              "variacion": {
                "nombre": "Curl concentrado con mancuerna",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/curl-concentrado-con-mancuerna.gif"
              }
            },
            {
              "nombre": "Extensión de tríceps sobre cabeza en polea con cuerda",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/extension-de-triceps-sobre-cabeza-en-polea-con-cuerda.gif",
              "series": 4,
              "repeticiones": "8",
              "descanso": "60s",
              "rir": "1",
              "bloque": "normal",
              "notas": "Inclínate un poco hacia adelante. Estira el tríceps completamente hacia atrás.",
              "variacion": {
                "nombre": "Extensión de tríceps en máquina",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/extension-de-triceps-en-maquina.gif"
              }
            },
            {
              "nombre": "Plancha abdominal",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/plancha-abdominal.gif",
              "series": 3,
              "repeticiones": "30s",
              "descanso": "45s",
              "rir": "0",
              "bloque": "normal",
              "notas": "Cuerpo recto de cabeza a talones. Aprieta el abdomen, los glúteos y los hombros. No dejes caer las caderas.",
              "variacion": {
                "nombre": "Plancha de rodillas",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/plancha-de-rodillas.gif"
              }
            },
            {
              "nombre": "Caminadora inclinada",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/caminadora-inclinada.gif",
              "is_cardio": true,
              "series": 1,
              "repeticiones": "20 min",
              "descanso": "-",
              "notas": "5-6 km/h, 10-12% de inclinación. Post-pesas. Sin agarrarte de las manijas. FC objetivo 120-140 bpm."
            }
          ]
        }
      ]
    },
    {
      "semana": 4,
      "numero": 4,
      "fase": "Peak",
      "fase_nombre": "Semana 4 — Peak",
      "nombre_bloque": "Semana 4 — Peak",
      "nota_semana": "Semana de pico: 5 series pesadas. Esto es lo más duro del bloque. Dalo todo.",
      "dias": [
        {
          "dia": 1,
          "dia_semana": "Lunes",
          "nombre": "Lunes — Pecho + Tríceps + Abdomen",
          "tipo": "empuje",
          "grupo_muscular": "Pecho, Tríceps, Core",
          "duracion_estimada": "70-75 min",
          "calentamiento": "5 min en caminadora suave + rotaciones de hombro + 2 series de 15 reps en máquina de pecho sin peso",
          "vuelta_calma": "5 min: estiramiento del grupo muscular principal del día.",
          "ejercicios": [
            {
              "nombre": "Press de pecho en máquina",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/press-de-pecho-en-maquina.gif",
              "series": 5,
              "repeticiones": "6",
              "descanso": "90s",
              "rir": "0",
              "bloque": "normal",
              "notas": "Pecho arriba, omóplatos contra el respaldo. Empuja explosivo, baja controlado en 2 segundos.",
              "variacion": {
                "nombre": "Press de banca con barra",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/press-banca-barra.gif"
              }
            },
            {
              "nombre": "Press de pecho inclinado en máquina",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/press-de-pecho-inclinado-en-maquina.gif",
              "series": 5,
              "repeticiones": "6",
              "descanso": "90s",
              "rir": "0",
              "bloque": "normal",
              "notas": "La inclinación ataca la parte alta del pecho. No arquees la espalda baja.",
              "variacion": {
                "nombre": "Press de banca inclinado con barra",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/press-banca-inclinado-con-barra.gif"
              }
            },
            {
              "nombre": "Aperturas en peck deck",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/aperturas-en-peck-deck.gif",
              "series": 5,
              "repeticiones": "6",
              "descanso": "60s",
              "rir": "0",
              "bloque": "normal",
              "notas": "Abraza un árbol imaginario. Siente el estiramiento en el pecho al abrir, contrae fuerte al cerrar.",
              "variacion": {
                "nombre": "Aperturas en polea de pie",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/aperturas-en-polea-de-pie.gif"
              }
            },
            {
              "nombre": "Extensión de tríceps en polea con cuerda",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/extension-de-triceps-en-polea-con-cuerda.gif",
              "series": 5,
              "repeticiones": "6",
              "descanso": "60s",
              "rir": "0",
              "bloque": "normal",
              "notas": "Codos pegados al cuerpo, solo mueve el antebrazo. Al bajar, separa la cuerda hacia afuera.",
              "variacion": {
                "nombre": "Extensión de tríceps en máquina",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/extension-de-triceps-en-maquina.gif"
              }
            },
            {
              "nombre": "Fondos de tríceps en máquina",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/fondos-de-triceps-en-maquina.gif",
              "series": 5,
              "repeticiones": "6",
              "descanso": "60s",
              "rir": "0",
              "bloque": "normal",
              "notas": "Mantén el torso vertical, no te inclines hacia adelante. Extiende los codos completamente.",
              "variacion": {
                "nombre": "Extensión de tríceps en polea agarre inverso",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/extension-de-triceps-en-polea-agarre-inverso.gif"
              }
            },
            {
              "nombre": "Crunch en polea arrodillado",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/crunch-en-polea-arrodillado.gif",
              "series": 4,
              "repeticiones": "15",
              "descanso": "45s",
              "rir": "1",
              "bloque": "normal",
              "notas": "Contrae el abdomen, lleva los codos hacia las rodillas. No jales con los brazos.",
              "variacion": {
                "nombre": "Crunch abdominal en máquina total",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/crunch-abdominal-en-maquina-total.gif"
              }
            },
            {
              "nombre": "Caminadora inclinada",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/caminadora-inclinada.gif",
              "is_cardio": true,
              "series": 1,
              "repeticiones": "20 min",
              "descanso": "-",
              "notas": "5-6 km/h, 10-12% de inclinación. Post-pesas. Sin agarrarte de las manijas. FC objetivo 120-140 bpm."
            }
          ]
        },
        {
          "dia": 2,
          "dia_semana": "Martes",
          "nombre": "Martes — Cuádriceps + Pantorrilla",
          "tipo": "piernas",
          "grupo_muscular": "Cuádriceps, Pantorrilla",
          "duracion_estimada": "70-75 min",
          "calentamiento": "5 min en caminadora + 2 series de 15 reps de sentadillas sin peso + movilidad de cadera",
          "vuelta_calma": "5 min: estiramiento del grupo muscular principal del día.",
          "ejercicios": [
            {
              "nombre": "Prensa de piernas abierto",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/presa-de-piernas-abierto.gif",
              "series": 5,
              "repeticiones": "6",
              "descanso": "90s",
              "rir": "0",
              "bloque": "normal",
              "notas": "Pies separados al ancho de los hombros. No bloquees las rodillas arriba. Baja hasta 90° o menos.",
              "variacion": {
                "nombre": "Sentadilla con barra",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/sentadilla-con-barra.gif"
              }
            },
            {
              "nombre": "Extensión de piernas en máquina",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/extension-de-piernas-en-maquina.gif",
              "series": 5,
              "repeticiones": "6",
              "descanso": "75s",
              "rir": "0",
              "bloque": "normal",
              "notas": "Extiende completamente y sostén 1 segundo arriba. Baja en 2-3 segundos.",
              "variacion": {
                "nombre": "Sentadilla goblet",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/sentadilla-goblet.gif"
              }
            },
            {
              "nombre": "Sentadilla Hack",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/sentadilla-hacka.gif",
              "series": 5,
              "repeticiones": "6",
              "descanso": "90s",
              "rir": "0",
              "bloque": "normal",
              "notas": "Pies juntos o separados según comodidad. Descarga el peso en los talones, no en la punta.",
              "variacion": {
                "nombre": "Sentadilla con mancuernas",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/sentadilla-con-mancuernas.gif"
              }
            },
            {
              "nombre": "Zancada inversa con mancuernas",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/zancada-inversa-con-mancuernas.gif",
              "series": 5,
              "repeticiones": "6 c/pierna",
              "descanso": "75s",
              "rir": "0",
              "bloque": "normal",
              "notas": "Da un paso atrás y baja la rodilla trasera casi al piso. El torso recto. Alternas piernas.",
              "variacion": {
                "nombre": "Zancada frontal con mancuerna",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/zancada-frontal-con-mancuerna.gif"
              }
            },
            {
              "nombre": "Elevación de talones en máquina",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/elevacion-de-talones-en-maquina.gif",
              "series": 4,
              "repeticiones": "12",
              "descanso": "45s",
              "rir": "1",
              "bloque": "normal",
              "notas": "Estira completo abajo, eleva máximo arriba y sostén 1 segundo. Sin rebote.",
              "variacion": {
                "nombre": "Elevación de talones sentado",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/elevacion-de-talones-sentado.gif"
              }
            },
            {
              "nombre": "Caminadora inclinada",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/caminadora-inclinada.gif",
              "is_cardio": true,
              "series": 1,
              "repeticiones": "20 min",
              "descanso": "-",
              "notas": "5-6 km/h, 10-12% de inclinación. Post-pesas. Sin agarrarte de las manijas. FC objetivo 120-140 bpm."
            }
          ]
        },
        {
          "dia": 3,
          "dia_semana": "Miércoles",
          "nombre": "Miércoles — Espalda + Bíceps + Abdomen",
          "tipo": "jale",
          "grupo_muscular": "Espalda, Bíceps, Core",
          "duracion_estimada": "70-75 min",
          "calentamiento": "5 min en caminadora + rotaciones de hombro + 2 series de 15 reps en jalón con peso liviano",
          "vuelta_calma": "5 min: estiramiento del grupo muscular principal del día.",
          "ejercicios": [
            {
              "nombre": "Jalón al pecho en máquina",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/jalon-al-pecho-en-maquina.gif",
              "series": 5,
              "repeticiones": "6",
              "descanso": "90s",
              "rir": "0",
              "bloque": "normal",
              "notas": "Jala llevando los codos hacia las caderas, no solo hacia abajo. Aprieta la espalda al final.",
              "variacion": {
                "nombre": "Jalón al pecho agarre supino",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/jalon-al-pecho-agarre-supino.gif"
              }
            },
            {
              "nombre": "Remo sentado en máquina",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/remo-sentado-en-maquina.gif",
              "series": 5,
              "repeticiones": "6",
              "descanso": "90s",
              "rir": "0",
              "bloque": "normal",
              "notas": "Jala hacia el abdomen. Aprieta los omóplatos juntos al final. Suelta controlado.",
              "variacion": {
                "nombre": "Remo en polea sentado",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/remo-en-polea-sentado.gif"
              }
            },
            {
              "nombre": "Pullover con mancuerna",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/pullover-con-mancuerna.gif",
              "series": 5,
              "repeticiones": "6",
              "descanso": "75s",
              "rir": "0",
              "bloque": "normal",
              "notas": "Mantén codos ligeramente doblados. Siente el estiramiento del dorsal al llevar la mancuerna atrás.",
              "variacion": {
                "nombre": "Pullover en polea con cuerda",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/pullover-en-polea-con-cuerda.gif"
              }
            },
            {
              "nombre": "Curl bíceps con barra",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/curl-biceps-con-barra.gif",
              "series": 5,
              "repeticiones": "6",
              "descanso": "60s",
              "rir": "0",
              "bloque": "normal",
              "notas": "Codos pegados al cuerpo. No balancees el torso. Baja lento en 2-3 segundos.",
              "variacion": {
                "nombre": "Curl predicador con barra EZ",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/curl-predicador-con-barra-ez.gif"
              }
            },
            {
              "nombre": "Curl martillo con mancuerna",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/curl-martillo-con-mancuerna.gif",
              "series": 5,
              "repeticiones": "6",
              "descanso": "60s",
              "rir": "0",
              "bloque": "normal",
              "notas": "Agarre neutro (como si sostuvieras un martillo). Trabajas el braquial además del bíceps.",
              "variacion": {
                "nombre": "Curl martillo en polea con cuerda",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/curl-martillo-en-polea-con-cuerda.gif"
              }
            },
            {
              "nombre": "Crunch abdominal en máquina total",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/crunch-abdominal-en-maquina-total.gif",
              "series": 4,
              "repeticiones": "15",
              "descanso": "45s",
              "rir": "1",
              "bloque": "normal",
              "notas": "Contrae fuerte en cada rep, sostén 1 segundo abajo. No uses inercia.",
              "variacion": {
                "nombre": "Crunch en polea arrodillado",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/crunch-en-polea-arrodillado.gif"
              }
            },
            {
              "nombre": "Caminadora inclinada",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/caminadora-inclinada.gif",
              "is_cardio": true,
              "series": 1,
              "repeticiones": "20 min",
              "descanso": "-",
              "notas": "5-6 km/h, 10-12% de inclinación. Post-pesas. Sin agarrarte de las manijas. FC objetivo 120-140 bpm."
            }
          ]
        },
        {
          "dia": 4,
          "dia_semana": "Jueves",
          "nombre": "Jueves — Femorales + Glúteos + Pantorrilla",
          "tipo": "piernas",
          "grupo_muscular": "Femorales, Glúteos, Pantorrilla",
          "duracion_estimada": "70-75 min",
          "calentamiento": "5 min en caminadora + movilidad de cadera + 2 series de 10 reps de buenos días sin peso",
          "vuelta_calma": "5 min: estiramiento del grupo muscular principal del día.",
          "ejercicios": [
            {
              "nombre": "Peso muerto rumano con barra",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/peso-muerto-rumano-con-barra.gif",
              "series": 5,
              "repeticiones": "6",
              "descanso": "90s",
              "rir": "0",
              "bloque": "normal",
              "notas": "Espalda recta siempre. Empuja las caderas hacia atrás y baja la barra rozando las piernas. Siente el femoral.",
              "variacion": {
                "nombre": "Peso muerto rumano con mancuerna",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/peso-muerto-rumano-con-mancuerna.gif"
              }
            },
            {
              "nombre": "Curl femoral acostado en máquina",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/curl-femoral-acostado-en-maquina.gif",
              "series": 5,
              "repeticiones": "6",
              "descanso": "75s",
              "rir": "0",
              "bloque": "normal",
              "notas": "Curva completa: extiende completamente abajo, contrae hasta el fondo arriba. Sin rebote.",
              "variacion": {
                "nombre": "Curl femoral sentado",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/curl-femoral-sentado.gif"
              }
            },
            {
              "nombre": "Hip thrust con barra",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/hipthrust-con-barra.gif",
              "series": 5,
              "repeticiones": "6",
              "descanso": "90s",
              "rir": "0",
              "bloque": "normal",
              "notas": "Empuja con los talones, no con los dedos. Aprieta el glúteo 1 segundo arriba. La barra va sobre la cadera con pad.",
              "variacion": {
                "nombre": "Puente de glúteo con barra",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/puente-de-gluteo-con-barra.gif"
              }
            },
            {
              "nombre": "Patada trasera en máquina",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/patada-trasera-en-maquina.gif",
              "series": 5,
              "repeticiones": "6 c/pierna",
              "descanso": "60s",
              "rir": "0",
              "bloque": "normal",
              "notas": "Extiende la cadera completamente atrás y aprieta el glúteo. Mantén el torso estable.",
              "variacion": {
                "nombre": "Patada trasera en polea",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/patada-trasera-en-polea.gif"
              }
            },
            {
              "nombre": "Elevación de talones sentado",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/elevacion-de-talones-sentado.gif",
              "series": 4,
              "repeticiones": "12",
              "descanso": "45s",
              "rir": "1",
              "bloque": "normal",
              "notas": "Estiramiento completo abajo, máximo arriba. Posición sentada aísla el sóleo.",
              "variacion": {
                "nombre": "Pantorrillas en prensa de pierna",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/pantorrillas-en-prensa-de-pierna.gif"
              }
            },
            {
              "nombre": "Caminadora inclinada",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/caminadora-inclinada.gif",
              "is_cardio": true,
              "series": 1,
              "repeticiones": "20 min",
              "descanso": "-",
              "notas": "5-6 km/h, 10-12% de inclinación. Post-pesas. Sin agarrarte de las manijas. FC objetivo 120-140 bpm."
            }
          ]
        },
        {
          "dia": 5,
          "dia_semana": "Viernes",
          "nombre": "Viernes — Hombros + Bíceps + Tríceps + Abdomen",
          "tipo": "empuje",
          "grupo_muscular": "Hombros, Bíceps, Tríceps, Core",
          "duracion_estimada": "70-75 min",
          "calentamiento": "5 min en caminadora + rotaciones de hombro + 2 series de 15 reps elevaciones laterales sin peso",
          "vuelta_calma": "5 min: estiramiento del grupo muscular principal del día.",
          "ejercicios": [
            {
              "nombre": "Press de hombro en máquina",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/press-de-hombro-en-maquina.gif",
              "series": 5,
              "repeticiones": "6",
              "descanso": "90s",
              "rir": "0",
              "bloque": "normal",
              "notas": "No bloquees los codos arriba. Baja hasta los 90° del codo. Torso erguido.",
              "variacion": {
                "nombre": "Press Arnold con mancuerna",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/press-arnold-con-mancuerna.gif"
              }
            },
            {
              "nombre": "Elevación lateral con mancuerna",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/elevacion-lateral-con-mancuerna.gif",
              "series": 5,
              "repeticiones": "6",
              "descanso": "60s",
              "rir": "0",
              "bloque": "normal",
              "notas": "Codos ligeramente doblados. Eleva hasta la altura del hombro, no más. Control total.",
              "variacion": {
                "nombre": "Elevaciones laterales en máquina",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/elevaciones-laterales-en-maquina.gif"
              }
            },
            {
              "nombre": "Elevaciones posteriores sobre banco inclinado",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/elevaciones-posteriores-sobre-banco-inclinado.gif",
              "series": 5,
              "repeticiones": "6",
              "descanso": "60s",
              "rir": "0",
              "bloque": "normal",
              "notas": "Pecho apoyado en el banco inclinado. Eleva los brazos hacia afuera contrayendo el deltoides posterior.",
              "variacion": {
                "nombre": "Apertura posteriores con mancuerna sentado",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/apertura-posteriores-con-mancuerna-sentado.gif"
              }
            },
            {
              "nombre": "Curl bíceps con mancuerna",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/curl-biceps-con-mancuerna.gif",
              "series": 5,
              "repeticiones": "6",
              "descanso": "60s",
              "rir": "0",
              "bloque": "normal",
              "notas": "Alterna brazos o haz los dos al tiempo. Codos fijos, sin balanceo.",
              "variacion": {
                "nombre": "Curl concentrado con mancuerna",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/curl-concentrado-con-mancuerna.gif"
              }
            },
            {
              "nombre": "Extensión de tríceps sobre cabeza en polea con cuerda",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/extension-de-triceps-sobre-cabeza-en-polea-con-cuerda.gif",
              "series": 5,
              "repeticiones": "6",
              "descanso": "60s",
              "rir": "0",
              "bloque": "normal",
              "notas": "Inclínate un poco hacia adelante. Estira el tríceps completamente hacia atrás.",
              "variacion": {
                "nombre": "Extensión de tríceps en máquina",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/extension-de-triceps-en-maquina.gif"
              }
            },
            {
              "nombre": "Plancha abdominal",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/plancha-abdominal.gif",
              "series": 4,
              "repeticiones": "45s",
              "descanso": "45s",
              "rir": "0",
              "bloque": "normal",
              "notas": "Cuerpo recto de cabeza a talones. Aprieta el abdomen, los glúteos y los hombros. No dejes caer las caderas.",
              "variacion": {
                "nombre": "Plancha de rodillas",
                "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/plancha-de-rodillas.gif"
              }
            },
            {
              "nombre": "Caminadora inclinada",
              "gif_url": "https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/caminadora-inclinada.gif",
              "is_cardio": true,
              "series": 1,
              "repeticiones": "20 min",
              "descanso": "-",
              "notas": "5-6 km/h, 10-12% de inclinación. Post-pesas. Sin agarrarte de las manijas. FC objetivo 120-140 bpm."
            }
          ]
        }
      ]
    }
  ]
}
EOJSON;

$nutriJson = <<<'EOJSON'
{
  "plan_type": "nutricion",
  "titulo": "Plan Nutricional — Pérdida de Grasa | John Carvajal",
  "cliente": "John Carvajal",
  "metodologia": "Déficit calórico moderado · Alta proteína · 4 comidas diarias",
  "objetivo_calorico": 2100,
  "objetivo_cal": 2100,
  "objetivo": "Pérdida de grasa con preservación muscular — déficit de ~500 kcal sobre tu gasto real. Proteína alta (210g) para no perder músculo mientras baja la grasa.",
  "macros": {
    "calorias": 2100,
    "proteina_g": 210,
    "carbohidratos_g": 170,
    "grasas_g": 65
  },
  "hidratacion": {
    "agua_minima_litros": 4.5,
    "electrolitos": "En días de entreno intenso (sem 3 y 4), añade una pizca de sal al agua o bebida con electrolitos sin azúcar."
  },
  "fecha_inicio": "2026-04-28",
  "peso_objetivo": 100,
  "tips_nutricionales": [
    "Proteína primero: si un día no alcanzás las calorías totales pero cumpliste los 210g de proteína, el día fue productivo.",
    "Las 3 opciones de cada comida son intercambiables — los macros son equivalentes entre sí.",
    "Pesá los alimentos crudos la primera semana. Después ya calculás a ojo.",
    "Pre-entreno o post-entreno (según tu horario): consumilo dentro de los 30 min antes o después del gym.",
    "Días de descanso: mantén el plan igual pero podés bajar 100g de arroz en almuerzo y cena.",
    "4.5 L de agua al día son innegociables. Arrancá con 500 ml en ayunas antes del desayuno.",
    "Si te dan antojos de noche, un vaso de agua con proteína whey en agua (sin leche) no sale del plan."
  ],
  "tips": [
    "Proteína primero: si un día no alcanzás las calorías totales pero cumpliste los 210g de proteína, el día fue productivo.",
    "Las 3 opciones de cada comida son intercambiables — los macros son equivalentes entre sí.",
    "Pesá los alimentos crudos la primera semana. Después ya calculás a ojo.",
    "Pre-entreno o post-entreno (según tu horario): consumilo dentro de los 30 min antes o después del gym.",
    "Días de descanso: mantén el plan igual pero podés bajar 100g de arroz en almuerzo y cena.",
    "4.5 L de agua al día son innegociables. Arrancá con 500 ml en ayunas antes del desayuno.",
    "Si te dan antojos de noche, un vaso de agua con proteína whey en agua (sin leche) no sale del plan."
  ],
  "notas_coach": "John, calculé tus calorías en 2,100 kcal/día. Tu cuerpo quema ~2,628 en reposo más movimiento mínimo. El déficit de 500 kcal te lleva a perder ~0.5 kg de grasa por semana, que es el ritmo correcto para no comerte el músculo.\n\nLa proteína está alta (210g) por una razón: cuando estás en déficit, el cuerpo quiere usar músculo como energía. La proteína alta lo impide. Nunca bajes de 200g, aunque estés con poco apetito.\n\nLos alimentos que pediste no incluir (coliflor, espinaca, brócoli, manzana verde, pasta) no están en el plan. Las opciones están construidas con lo que mencionaste que sí querés usar.\n\nLa comida pre/post-entreno depende de tu horario: si entrenas entre las 4-7pm, comela antes. Si entrenas entre las 8-10am, comela después. El objetivo es que el músculo tenga nutrientes cerca del entreno.",
  "comidas": [
    {
      "nombre": "Desayuno",
      "tipo": "desayuno",
      "hora": "7:00am",
      "calorias": 520,
      "macros": {
        "proteina": 45,
        "carbohidratos": 52,
        "grasas": 14
      },
      "opcion_a": [
        "Huevos enteros revueltos (3 unidades)",
        "Tostadas integrales (2 rebanadas, 60g)",
        "Aguacate (40g)",
        "Banano (1 unidad pequeña, 80g)"
      ],
      "opcion_b": [
        "Avena en hojuelas cocida en agua (60g)",
        "Claras de huevo revueltas (4 claras)",
        "Crema de maní natural (15g)",
        "Manzana roja (1 unidad, 120g)"
      ],
      "opcion_c": [
        "Arepa integral (1 mediana, 80g)",
        "Queso cottage (150g)",
        "Huevos enteros (2 unidades)",
        "Papaya en trozos (100g)"
      ],
      "notas_comida": "Prepara los huevos sin aceite o con spray. El banano y la papaya son los carbos del desayuno — no los saltes."
    },
    {
      "nombre": "Almuerzo",
      "tipo": "almuerzo",
      "hora": "1:00pm",
      "calorias": 630,
      "macros": {
        "proteina": 60,
        "carbohidratos": 65,
        "grasas": 16
      },
      "opcion_a": [
        "Pechuga de pollo a la plancha (200g)",
        "Arroz cocido (100g)",
        "Ensalada de lechuga, tomate y pepino (1 plato)"
      ],
      "opcion_b": [
        "Carne de res magra a la plancha (180g)",
        "Papa cocida (150g)",
        "Ensalada de zanahoria, tomate y pimentón (1 plato)"
      ],
      "opcion_c": [
        "Lomo de cerdo a la plancha (180g)",
        "Arroz cocido (100g)",
        "Ensalada de lechuga y pimentón (1 plato)"
      ],
      "notas_comida": "Cocina la proteína sin frituras. Aceite de oliva en spray si es necesario. La ensalada sin aderezo o con limón."
    },
    {
      "nombre": "Pre-entreno / Post-entreno",
      "tipo": "pre-entreno",
      "hora": "Variable según horario",
      "calorias": 310,
      "macros": {
        "proteina": 35,
        "carbohidratos": 30,
        "grasas": 8
      },
      "opcion_a": [
        "Proteína whey en agua (1 scoop, 30g)",
        "Tostadas de arroz (2 unidades)",
        "Crema de maní natural (15g)"
      ],
      "opcion_b": [
        "Proteína whey en agua (1 scoop, 30g)",
        "Banano maduro (1 unidad mediana, 100g)"
      ],
      "opcion_c": [
        "Proteína whey en agua (1 scoop, 30g)",
        "Manzana roja (1 unidad, 120g)",
        "Almendras (15g)"
      ],
      "notas_comida": "⚡ IMPORTANTE: Hacé esta comida ANTES del entrenamiento si entrenas entre las 4-7pm (30-45 min antes). Hacela DESPUÉS del entrenamiento si entrenas entre las 8-10am (dentro de los 30 min). Siempre mezcla el whey en agua, no en leche."
    },
    {
      "nombre": "Cena",
      "tipo": "cena",
      "hora": "8:00pm",
      "calorias": 640,
      "macros": {
        "proteina": 70,
        "carbohidratos": 23,
        "grasas": 27
      },
      "opcion_a": [
        "Pechuga de pollo a la plancha (200g)",
        "Papa cocida (100g)",
        "Ensalada de lechuga, tomate y pepino (1 plato)"
      ],
      "opcion_b": [
        "Atún en agua escurrido (1 lata, 150g)",
        "Papa cocida (120g)",
        "Ensalada de zanahoria, pimentón y lechuga (1 plato)"
      ],
      "opcion_c": [
        "Lomo de cerdo a la plancha (180g)",
        "Papa cocida (100g)",
        "Ensalada de tomate y pepino (1 plato)"
      ],
      "notas_comida": "La cena es alta en proteína y moderada en carbos. Si llegas tarde y con poco apetito, come al menos la proteína. Es la comida más flexible del día."
    }
  ],
  "plan_dia_descanso": {
    "descripcion": "En sábado y domingo (días de descanso) bajamos ~150-200 kcal de carbos. Mantenemos la proteína igual.",
    "calorias_objetivo": 1950,
    "ajustes": [
      "Elimina la comida pre/post-entreno (no entrenas)",
      "En almuerzo y cena, baja 50g de arroz o papa",
      "Mantén el desayuno igual",
      "Hidratación: mantén los 4.5 L igual"
    ]
  }
}
EOJSON;

$supJson = <<<'EOJSON'
{
  "plan_type": "suplementacion",
  "titulo": "Stack Esencial Suplementación — John Carvajal",
  "descripcion_protocolo": "Protocolo básico para pérdida de grasa con preservación muscular. Solo lo que tiene evidencia científica real.",
  "perfil_cliente": "Hombre 31 años, 124 kg, principiante, objetivo pérdida de grasa",
  "advertencia": "Consultá con tu médico si tomás algún medicamento o tenés condiciones de salud.",
  "categorias": [
    {
      "nombre": "Recuperación",
      "suplementos": [
        {
          "nombre": "Proteína de Suero Whey Concentrada",
          "dosis": "1 scoop (30g) en agua",
          "timing": "Post-entreno o como snack",
          "prioridad": "esencial",
          "notas": "Cubre el requerimiento proteico. En agua, no en leche. Mezclá con banano si lo tomás post-entreno."
        }
      ]
    },
    {
      "nombre": "Rendimiento",
      "suplementos": [
        {
          "nombre": "Creatina Monohidrato",
          "dosis": "5g",
          "timing": "Con el desayuno (diario)",
          "prioridad": "esencial",
          "notas": "El suplemento más estudiado y efectivo. Todos los días, incluso los de descanso. No necesita ciclo ni carga."
        },
        {
          "nombre": "Cafeína anhidra",
          "dosis": "200mg",
          "timing": "Pre-entreno (30 min antes)",
          "prioridad": "recomendado",
          "notas": "Solo los días de pesas. No tomar después de las 5 PM para no afectar el sueño. Podés usar café negro (2 tazas) si preferís."
        }
      ]
    },
    {
      "nombre": "Salud",
      "suplementos": [
        {
          "nombre": "Omega 3 (EPA+DHA)",
          "dosis": "2g (2 cápsulas de 1g)",
          "timing": "Con el almuerzo",
          "prioridad": "recomendado",
          "notas": "Reduce inflamación, mejora recuperación. Tomar con comida grasa para mejor absorción."
        },
        {
          "nombre": "Multivitamínico",
          "dosis": "1 tableta",
          "timing": "Con el desayuno",
          "prioridad": "recomendado",
          "notas": "Cubre deficiencias básicas. Cualquier marca decente sirve. Busca uno con vitaminas del complejo B y Zinc."
        },
        {
          "nombre": "Vitamina D3",
          "dosis": "2000 UI",
          "timing": "Con el desayuno",
          "prioridad": "recomendado",
          "notas": "Especialmente si trabajás en interior o vivís en zona con poco sol. Crítico para testosterona y recuperación muscular."
        }
      ]
    }
  ],
  "timing_diario": [
    {
      "momento": "Con el desayuno (7:00am)",
      "suplementos": "Creatina 5g + Multivitamínico + Vitamina D3"
    },
    {
      "momento": "Pre-entreno (30 min antes)",
      "suplementos": "Cafeína 200mg (si entrenás en la mañana, no después de las 5pm)"
    },
    {
      "momento": "Con el almuerzo (1:00pm)",
      "suplementos": "Omega 3 2g"
    },
    {
      "momento": "Post-entreno o snack",
      "suplementos": "Proteína Whey 1 scoop en agua"
    }
  ],
  "sinergias": [
    {
      "titulo": "Creatina + Carbohidratos",
      "explicacion": "Tomar la creatina con el desayuno (que tiene carbos) mejora su absorción por la insulina."
    },
    {
      "titulo": "Omega 3 + Vitamina D3",
      "explicacion": "Ambos son liposolubles. Tómalos con una comida que tenga grasa (huevos, aguacate) para mejor absorción."
    }
  ],
  "notas_coach": "John, con este stack tenés lo que realmente funciona. No gastes en quemadores de grasa, BCAAs ni pre-workouts con 20 ingredientes — no hacen diferencia a tu nivel y algunos tienen efectos secundarios.\n\nEmpezá solo con los 3 esenciales las primeras 2 semanas: Whey, Creatina y Multivitamínico. Después agregás el Omega 3, la Vitamina D3 y la Cafeína. Así sabés qué te cae bien.\n\nLa creatina no te va a hacer subir de peso en grasa — retiene agua intramuscular (1-2 kg en el músculo), que se va cuando dejás de tomarla. Es normal."
}
EOJSON;

try {
    $pdo->beginTransaction();

    // 1. Desactivar TODOS los planes activos previos del cliente
    $stmt = $pdo->prepare("UPDATE assigned_plans SET active=0 WHERE client_id=? AND active=1");
    $stmt->execute([$clientId]);
    $deactivated = $stmt->rowCount();
    echo "Planes previos desactivados: {$deactivated}\n";

    // 2. Insertar los 3 nuevos planes
    $stmt = $pdo->prepare(
        "INSERT INTO assigned_plans (client_id, plan_type, content, assigned_by, valid_from, expires_at, active, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([$clientId, 'entrenamiento',  $trainJson, $assignedBy, $validFrom, $expiresAt, 1, $now]);
    $trainId = $pdo->lastInsertId();
    echo "Plan entrenamiento insertado ID: {$trainId}\n";

    $stmt->execute([$clientId, 'nutricion',      $nutriJson, $assignedBy, $validFrom, $expiresAt, 1, $now]);
    $nutriId = $pdo->lastInsertId();
    echo "Plan nutricion insertado ID: {$nutriId}\n";

    $stmt->execute([$clientId, 'suplementacion', $supJson,   $assignedBy, $validFrom, $expiresAt, 1, $now]);
    $supId = $pdo->lastInsertId();
    echo "Plan suplementacion insertado ID: {$supId}\n";

    $pdo->commit();
    echo "\nOK: 3 planes insertados para client_id={$clientId}\n";
    echo "valid_from={$validFrom} | expires_at={$expiresAt}\n";

    // 3. Invalidar caches
    // (Ejecutar manualmente via tinker si Redis está activo)
    echo "\nRecordatorio: invalidar caches manualmente via tinker:\n";
    echo "  Cache::forget('client_plan_v3_{$clientId}');\n";
    echo "  Cache::forget('wp:plan:{$clientId}');\n";
    echo "  Cache::forget('wp:weekdays:{$clientId}');\n";
    echo "  Cache::forget('dashboard:{$clientId}');\n";

} catch (Exception $e) {
    $pdo->rollBack();
    die("ERROR: " . $e->getMessage() . "\n");
}
