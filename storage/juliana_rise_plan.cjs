// Generate Juliana Portilla RISE plan — home with bands
const fs = require('fs');

const weekConfigs = [
  { semana: 1, nombre: 'Semana 1 — Adaptación', series: 3, reps: '12-15', descanso: '45s', nota: 'Enfócate en la técnica y conexión mente-músculo. No te apures.' },
  { semana: 2, nombre: 'Semana 2 — Volumen', series: 3, reps: '15-18', descanso: '40s', nota: 'Sube repeticiones. Si la banda se siente fácil, usa una de mayor resistencia.' },
  { semana: 3, nombre: 'Semana 3 — Intensidad', series: 4, reps: '12-15', descanso: '45s', nota: 'Agregamos una serie extra. Mantén la tensión constante en la banda.' },
  { semana: 4, nombre: 'Semana 4 — Peak', series: 4, reps: '15-20', descanso: '35s', nota: 'Semana final. Da todo. Reduce descansos, maximiza repeticiones.' },
];

const dias = [
  {
    dia: 'Lunes', nombre: 'Cuádriceps',
    ejercicios: [
      { nombre: 'Sentadilla con banda en rodillas', notas: 'Banda encima de las rodillas. Baja hasta paralela, empuja con los talones. Rodillas hacia afuera contra la banda.' },
      { nombre: 'Sentadilla sumo con banda', notas: 'Pies bien abiertos, puntas afuera. Banda en rodillas. Baja controlado, aprieta glúteos arriba.' },
      { nombre: 'Zancadas alternas con banda en rodillas', notas: 'Paso largo hacia adelante, rodilla trasera casi toca el suelo. Mantén el torso erguido.' },
      { nombre: 'Step ups en silla o escalón', notas: 'Usa una silla estable o escalón de 30-40cm. Sube con una pierna, baja controlado. Alterna piernas.' },
      { nombre: 'Extensión de rodilla sentada con banda', notas: 'Siéntate en silla, banda anclada atrás del tobillo. Extiende la pierna completamente. 2 seg arriba.' },
    ],
    cardio: '20 minutos de saltar lazo o clase de rumba'
  },
  {
    dia: 'Martes', nombre: 'Hombro, Tríceps y Abdomen',
    ejercicios: [
      { nombre: 'Press de hombros con superband', notas: 'Pisa la banda con ambos pies, empuja arriba hasta extensión completa. Baja controlado.' },
      { nombre: 'Elevaciones laterales con banda', notas: 'Pisa la banda, sube los brazos a la altura de los hombros. No balancees. Excéntrico 3 seg.' },
      { nombre: 'Extensión de tríceps con superband overhead', notas: 'Banda anclada abajo o pisada. Extiende los brazos sobre la cabeza. Codos fijos.' },
      { nombre: 'Dips en silla', notas: 'Manos en el borde de la silla, baja flexionando codos hasta 90°. Empuja arriba. Pies en el suelo.' },
      { nombre: 'Crunch abdominal', notas: 'Acuéstate, rodillas flexionadas. Eleva hombros del suelo contrayendo el abdomen. No tires del cuello.' },
      { nombre: 'Plancha lateral', notas: 'Apóyate en antebrazo, cuerpo recto. Mantén 30-45 seg cada lado. Aprieta oblicuos.' },
    ],
    cardio: '20 minutos de saltar lazo o clase de rumba'
  },
  {
    dia: 'Miércoles', nombre: 'Glúteos',
    ejercicios: [
      { nombre: 'Hip thrust en suelo con banda en rodillas', notas: 'Espalda apoyada en el suelo, pies al ancho de cadera. Banda encima de las rodillas. Empuja cadera arriba, contrae glúteos 2 seg.' },
      { nombre: 'Puente de glúteo unilateral con banda', notas: 'Igual que hip thrust pero con una pierna extendida. Empuja con el talón de la pierna apoyada.' },
      { nombre: 'Kickback de glúteo con banda', notas: 'En 4 puntos, banda en rodillas o tobillos. Extiende una pierna hacia atrás y arriba. Contrae glúteo arriba.' },
      { nombre: 'Clamshell con banda', notas: 'Acuéstate de lado, banda encima de las rodillas. Abre la rodilla superior como almeja. No rotar la cadera.' },
      { nombre: 'Fire hydrant con banda', notas: 'En 4 puntos, banda en rodillas. Abre la rodilla hacia el lado manteniendo 90°. Contrae glúteo medio.' },
      { nombre: 'Sentadilla sumo pulso con banda', notas: 'Posición sumo, baja y haz 3 pulsos abajo antes de subir. Banda en rodillas. Quema total.' },
    ],
    cardio: '20 minutos de saltar lazo o clase de rumba'
  },
  {
    dia: 'Jueves', nombre: 'Espalda, Bíceps y Abdomen',
    ejercicios: [
      { nombre: 'Remo con superband', notas: 'Banda anclada en puerta a la altura del pecho. Jala con codos hacia atrás, aprieta escápulas. Excéntrico 3 seg.' },
      { nombre: 'Pull apart con banda', notas: 'Brazos extendidos al frente con banda tensa. Abre los brazos hacia los lados. Trabaja deltoides posterior y espalda alta.' },
      { nombre: 'Remo a una mano con superband', notas: 'Pisa la banda con un pie, jala con un brazo. Codo hacia atrás y arriba. Alterna lados.' },
      { nombre: 'Curl de bíceps con superband', notas: 'Pisa la banda, agarra con ambas manos. Flexiona los codos subiendo las manos hacia los hombros. Codos fijos.' },
      { nombre: 'Curl martillo con superband', notas: 'Igual que curl normal pero con agarre neutro (palmas mirándose). Trabaja bíceps braquial.' },
      { nombre: 'Plancha abdominal isométrica', notas: 'Apóyate en antebrazos y puntas de pies. Cuerpo recto como tabla. Mantén 45-60 seg. Aprieta glúteos y abdomen.' },
    ],
    cardio: '20 minutos de saltar lazo o clase de rumba'
  },
  {
    dia: 'Viernes', nombre: 'Femoral + Glúteos',
    ejercicios: [
      { nombre: 'Peso muerto rumano con superband', notas: 'Pisa la banda, agarra con ambas manos. Bisagra de cadera hacia atrás, espalda neutra. Siente el estiramiento en femoral.' },
      { nombre: 'Curl femoral acostada con banda', notas: 'Acuéstate boca abajo, banda en tobillos anclada al frente. Flexiona rodillas llevando talones al glúteo.' },
      { nombre: 'Hip thrust con banda en rodillas', notas: 'Espalda contra sofá o silla baja. Banda en rodillas, empuja cadera arriba. Contracción máxima 2 seg.' },
      { nombre: 'Good morning con superband', notas: 'Banda detrás del cuello pisada con los pies. Inclínate hacia adelante con espalda recta. Siente femoral y glúteo.' },
      { nombre: 'Sentadilla búlgara con banda', notas: 'Pie trasero en silla, banda en rodillas. Baja hasta que la rodilla trasera casi toque el suelo. Empuja con el talón delantero.' },
      { nombre: 'Puente de glúteo con pausa', notas: 'Pies apoyados en suelo, banda en rodillas. Sube cadera y mantén 3 seg arriba. Baja lento.' },
    ],
    cardio: '20 minutos de saltar lazo o clase de rumba'
  },
  {
    dia: 'Sábado', nombre: 'Full Body',
    ejercicios: [
      { nombre: 'Sentadilla con banda en rodillas', notas: 'Calentamiento de piernas. Banda en rodillas, profundidad completa.' },
      { nombre: 'Push ups (flexiones)', notas: 'Manos al ancho de hombros. Si es difícil, hazlas en rodillas. Baja el pecho hasta casi tocar el suelo.' },
      { nombre: 'Remo con superband', notas: 'Banda anclada. Jala con ambos brazos, aprieta escápulas.' },
      { nombre: 'Press de hombros con superband', notas: 'Pisa la banda, empuja arriba. Movimiento completo.' },
      { nombre: 'Curl de bíceps con superband', notas: 'Pisa la banda, flexiona los codos.' },
      { nombre: 'Hip thrust con banda', notas: 'Banda en rodillas, empuja cadera arriba. Contrae glúteos.' },
      { nombre: 'Plancha abdominal 60 seg', notas: 'Cierra la semana con core fuerte. Mantén 60 seg sin descanso.' },
    ],
    cardio: null // No cardio on Saturday
  },
];

const semanas = weekConfigs.map(wc => ({
  semana: wc.semana,
  nombre_bloque: wc.nombre,
  nota_semana: wc.nota,
  dias: dias.map(d => ({
    dia: d.dia,
    nombre: `${d.dia} — ${d.nombre}`,
    ejercicios: d.ejercicios.map(e => ({
      nombre: e.nombre,
      series: wc.series,
      repeticiones: wc.reps,
      descanso: wc.descanso,
      notas: e.notas,
    })),
    cardio: d.cardio,
  })),
}));

const plan = {
  titulo: 'RISE 30 Días — Juliana Portilla',
  objetivo: 'Perder grasa y mejorar composición muscular',
  metodologia: 'Entrenamiento en casa con bandas elásticas + cardio',
  equipo: ['Bandas elásticas (glúteo)', 'Superband (tren superior)', 'Lazo para cardio', 'Silla estable'],
  duracion_semanas: 4,
  frecuencia_dias: 6,
  split: 'L: Cuádriceps | M: Hombro/Triceps/Abdomen | X: Glúteos | J: Espalda/Biceps/Abdomen | V: Femoral+Glúteos | S: Full Body',
  notas_generales: 'Plan diseñado para entrenamiento en casa con bandas elásticas y superband. 20 min de cardio (lazo o rumba) al final de cada sesión excepto sábado. Progresión semanal: más reps y series cada semana.',
  semanas: semanas,
};

fs.writeFileSync('storage/juliana_rise_program.json', JSON.stringify(plan, null, 2));
console.log('Plan generated:', semanas.length, 'weeks,', dias.length, 'days/week');
console.log('Total exercises per day:', dias.map(d => d.ejercicios.length).join(', '));
