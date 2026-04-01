<template>
  <div class="space-y-6">
    <!-- Header -->
    <div>
      <h1 class="font-display text-3xl tracking-wide text-wc-text">EVIDENCE-BASED HACKS</h1>
      <p class="mt-1 text-sm text-wc-text-secondary">
        Estrategias respaldadas por ciencia para optimizar tu rendimiento, recuperacion y bienestar.
      </p>
    </div>

    <!-- Search + filters -->
    <div class="space-y-3">
      <!-- Search -->
      <div class="relative">
        <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Buscar hacks, autores, revistas..."
          class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 pl-10 pr-4 text-sm text-wc-text placeholder:text-wc-text-tertiary focus:border-wc-accent/60 focus:outline-none focus:ring-1 focus:ring-wc-accent/30 transition-colors"
        />
      </div>

      <!-- Category tabs -->
      <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-hide">
        <button
          type="button"
          @click="selectedCategory = null"
          :class="[
            'flex-shrink-0 rounded-full border px-4 py-1.5 text-xs font-semibold transition-colors',
            selectedCategory === null
              ? 'border-wc-accent bg-wc-accent text-white'
              : 'border-wc-border bg-wc-bg-tertiary text-wc-text-secondary hover:border-wc-accent/40 hover:text-wc-text'
          ]"
        >
          Todos
        </button>
        <button
          v-for="cat in categories"
          :key="cat.id"
          type="button"
          @click="selectedCategory = selectedCategory === cat.id ? null : cat.id"
          :class="[
            'flex-shrink-0 rounded-full border px-4 py-1.5 text-xs font-semibold transition-colors',
            selectedCategory === cat.id
              ? `${categoryColors[cat.id].bg} ${categoryColors[cat.id].text} ${categoryColors[cat.id].border} border`
              : 'border-wc-border bg-wc-bg-tertiary text-wc-text-secondary hover:border-wc-accent/40 hover:text-wc-text'
          ]"
        >
          {{ cat.icon }} {{ cat.label }}
        </button>
      </div>
    </div>

    <!-- Results counter -->
    <p class="text-xs text-wc-text-tertiary">
      {{ filteredHacks.length }} hack{{ filteredHacks.length !== 1 ? 's' : '' }} encontrado{{ filteredHacks.length !== 1 ? 's' : '' }}
    </p>

    <!-- Empty state -->
    <div
      v-if="filteredHacks.length === 0"
      class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center"
    >
      <p class="text-2xl mb-2">🔍</p>
      <p class="text-sm font-medium text-wc-text">Sin resultados</p>
      <p class="mt-1 text-xs text-wc-text-tertiary">
        Intenta con otros terminos o cambia la categoria.
      </p>
    </div>

    <!-- Grid of cards -->
    <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <button
        v-for="hack in filteredHacks"
        :key="hack.id"
        type="button"
        @click="openModal(hack)"
        class="group rounded-xl border border-wc-border bg-wc-bg-secondary p-5 text-left transition-all duration-200 hover:border-wc-accent/40 hover:shadow-lg hover:shadow-black/10"
      >
        <!-- Category badge + icon -->
        <div class="mb-3 flex items-center justify-between">
          <span
            :class="[
              'rounded-full border px-2 py-0.5 text-[10px] font-semibold',
              categoryColors[hack.category].bg,
              categoryColors[hack.category].text,
              categoryColors[hack.category].border,
            ]"
          >
            {{ getCategoryLabel(hack.category) }}
          </span>
          <span class="text-xl">{{ hack.icon }}</span>
        </div>

        <!-- Title -->
        <h3 class="mb-2 font-display text-base tracking-wide text-wc-text group-hover:text-wc-accent transition-colors">
          {{ hack.title }}
        </h3>

        <!-- Description preview -->
        <p class="line-clamp-2 text-xs text-wc-text-secondary">{{ hack.description }}</p>

        <!-- Source -->
        <p class="mt-3 truncate text-[10px] text-wc-text-tertiary">
          {{ hack.source.author }} — {{ hack.source.journal }}, {{ hack.source.year }}
        </p>
      </button>
    </div>

    <!-- Detail modal -->
    <Transition name="modal-fade">
      <div
        v-if="selectedHack"
        class="fixed inset-0 z-50 flex items-end justify-center sm:items-center p-0 sm:p-4"
        @click.self="closeModal"
      >
        <!-- Overlay -->
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeModal" />

        <!-- Modal panel -->
        <div class="relative z-10 w-full max-w-lg rounded-t-2xl sm:rounded-2xl border border-wc-border bg-wc-bg-secondary shadow-2xl max-h-[90vh] overflow-y-auto">
          <!-- Drag handle (mobile) -->
          <div class="flex justify-center pt-3 sm:hidden">
            <div class="h-1 w-12 rounded-full bg-wc-border" />
          </div>

          <!-- Modal header -->
          <div class="flex items-start justify-between p-6 pb-0">
            <div class="flex items-center gap-3">
              <span class="text-3xl">{{ selectedHack.icon }}</span>
              <span
                :class="[
                  'rounded-full border px-2 py-0.5 text-[10px] font-semibold',
                  categoryColors[selectedHack.category].bg,
                  categoryColors[selectedHack.category].text,
                  categoryColors[selectedHack.category].border,
                ]"
              >
                {{ getCategoryLabel(selectedHack.category) }}
              </span>
            </div>
            <button
              type="button"
              @click="closeModal"
              class="flex h-8 w-8 items-center justify-center rounded-lg text-wc-text-tertiary hover:bg-wc-bg-tertiary hover:text-wc-text transition-colors"
            >
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Modal body -->
          <div class="p-6 space-y-5">
            <!-- Title -->
            <h2 class="font-display text-2xl tracking-wide text-wc-text">{{ selectedHack.title }}</h2>

            <!-- Que hacer -->
            <div>
              <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Que hacer</p>
              <p class="text-sm text-wc-text-secondary leading-relaxed">{{ selectedHack.description }}</p>
            </div>

            <!-- Por que funciona -->
            <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
              <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Por que funciona</p>
              <p class="text-sm text-wc-text-secondary leading-relaxed">{{ selectedHack.explanation }}</p>
            </div>

            <!-- Source reference -->
            <div class="flex items-start gap-3 rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
              <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                />
              </svg>
              <div>
                <p class="text-xs font-semibold text-wc-text">{{ selectedHack.source.author }}</p>
                <p class="text-[11px] text-wc-text-tertiary">
                  {{ selectedHack.source.journal }}, {{ selectedHack.source.year }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';

// -------------------------------------------------------------------
// Types
// -------------------------------------------------------------------
interface HackSource {
  author: string;
  journal: string;
  year: number;
}

interface Hack {
  id: number;
  category: string;
  icon: string;
  title: string;
  description: string;
  explanation: string;
  source: HackSource;
}

// -------------------------------------------------------------------
// Static data — module-level constants (not reactive)
// -------------------------------------------------------------------
const categories = [
  { id: 'sueno', label: 'Sueno', icon: '🌙' },
  { id: 'nutricion', label: 'Nutricion', icon: '🥗' },
  { id: 'training', label: 'Training', icon: '🏋️' },
  { id: 'recovery', label: 'Recovery', icon: '💆' },
  { id: 'stress', label: 'Stress', icon: '🧘' },
];

const categoryColors: Record<string, { bg: string; text: string; border: string }> = {
  sueno: { bg: 'bg-indigo-500/10', text: 'text-indigo-400', border: 'border-indigo-500/20' },
  nutricion: { bg: 'bg-green-500/10', text: 'text-green-400', border: 'border-green-500/20' },
  training: { bg: 'bg-orange-500/10', text: 'text-orange-400', border: 'border-orange-500/20' },
  recovery: { bg: 'bg-blue-500/10', text: 'text-blue-400', border: 'border-blue-500/20' },
  stress: { bg: 'bg-purple-500/10', text: 'text-purple-400', border: 'border-purple-500/20' },
};

const allHacks: Hack[] = [
  // ---------------------------------------------------------------
  // SUENO
  // ---------------------------------------------------------------
  {
    id: 1,
    category: 'sueno',
    icon: '☀️',
    title: 'Luz solar en los primeros 30 min',
    description:
      'Sal afuera o asómate a una ventana durante 5-10 minutos dentro de los primeros 30 minutos después de despertar. En dias nublados extiende el tiempo a 20-30 min. No uses gafas de sol durante este periodo.',
    explanation:
      'La luz solar de la mañana activa las células ganglionares de la retina intrínsecamente fotosensibles (ipRGCs) que contienen melanopsina. Esto dispara el pulso de cortisol matutino saludable (necesario para alerta y energía) y ancla tu reloj circadiano, lo que mejora la calidad del sueño esa noche.',
    source: { author: 'Huberman, A.D.', journal: 'Neuron, Cell Press', year: 2021 },
  },
  {
    id: 2,
    category: 'sueno',
    icon: '🌡️',
    title: 'Cuarto a 18-19°C para dormir',
    description:
      'Programa tu termostato entre 18-19°C (65-67°F) antes de dormir. Si no tienes control de temperatura, usa calcetines y deja los brazos descubiertos para facilitar la disipación de calor corporal.',
    explanation:
      'El cuerpo necesita reducir su temperatura central ~1-2°C para iniciar y mantener el sueño profundo. Un ambiente frío facilita esta caída térmica, aumentando el tiempo en sueño de ondas lentas (SWS) donde ocurre la mayor parte de la recuperación física y consolidación de memoria.',
    source: { author: 'Walker, M.', journal: 'Why We Sleep, Penguin Books', year: 2017 },
  },
  {
    id: 3,
    category: 'sueno',
    icon: '📱',
    title: 'No pantallas 1h antes de dormir',
    description:
      'Evita smartphones, tablets y computadores durante la hora previa a acostarte. Usa modo nocturno desde las 8 PM si no puedes evitarlo. Opta por lectura en papel, meditación o conversación.',
    explanation:
      'La luz azul (450-490 nm) de pantallas LED suprime la secreción de melatonina hasta en un 50% según estudios de Harvard. Esto retrasa el inicio del sueño, reduce la duración del sueño REM y altera el ritmo circadiano, comprometiendo la recuperación cognitiva y física.',
    source: { author: 'Chang, A.M. et al.', journal: 'PNAS', year: 2015 },
  },
  {
    id: 4,
    category: 'sueno',
    icon: '💊',
    title: 'Magnesio glicinato antes de dormir',
    description:
      'Toma 200-400 mg de magnesio glicinato 30-60 minutos antes de acostarte. Esta forma quelada tiene mayor biodisponibilidad y no causa el efecto laxante del óxido de magnesio.',
    explanation:
      'El magnesio actúa como agonista del receptor GABA-A e inhibe el receptor NMDA, reduciendo la actividad neuronal excitadora. Estudios clínicos demuestran mejoras significativas en la calidad del sueño, reducción del tiempo de inicio y aumento del tiempo total de sueño en adultos con deficiencia.',
    source: { author: 'Abbasi, B. et al.', journal: 'J Res Med Sci', year: 2012 },
  },
  {
    id: 5,
    category: 'sueno',
    icon: '⏰',
    title: 'Protocolo 10-3-2-1',
    description:
      '10h antes: última cafeína. 3h antes: última comida grande o alcohol. 2h antes: detén trabajo de pantalla intensa. 1h antes: nada de pantallas. Aplícalo como rutina fija cada noche.',
    explanation:
      'Este protocolo sistematiza las variables de higiene del sueño con mayor evidencia. La cafeína tiene vida media de 5-7 horas; el alcohol fragmenta el sueño REM; las comidas tardías elevan la temperatura corporal; el trabajo mental activa el sistema nervioso simpático dificultando la transición al sueño.',
    source: { author: 'Irish, L.A. et al.', journal: 'Sleep Med Rev', year: 2015 },
  },

  // ---------------------------------------------------------------
  // NUTRICION
  // ---------------------------------------------------------------
  {
    id: 6,
    category: 'nutricion',
    icon: '🥩',
    title: 'Proteina en cada comida 20-40g',
    description:
      'Distribuye tu ingesta de proteina en 3-5 comidas de 20-40g cada una (pollo, res, huevos, pescado, proteina en polvo). Prioriza fuentes completas con todos los aminoácidos esenciales.',
    explanation:
      'La síntesis proteica muscular (MPS) requiere superar el umbral de leucina (~2-3g por comida). Distribuir la proteina maximiza los pulsos de MPS durante el dia, superando la estrategia de concentrar proteina en pocas comidas. El efecto "techo" de ~40g por comida aplica para la mayoria de adultos.',
    source: { author: 'Schoenfeld & Aragon', journal: 'JISSN', year: 2018 },
  },
  {
    id: 7,
    category: 'nutricion',
    icon: '💧',
    title: 'Agua fria al despertar 500ml',
    description:
      'Bebe 500ml de agua fria inmediatamente al despertar, antes de cafe o comida. Agrega una pizca de sal de mar y unas gotas de jugo de limon para mejorar la absorcion de electrolitos.',
    explanation:
      'Despues de 7-9 horas de ayuno, el cuerpo presenta deshidratacion leve (1-2%) que deteriora el rendimiento cognitivo y fisico. El agua fria activa el sistema simpatico y aumenta temporalmente la termogenesis en ~30% segun investigaciones del JCEM, mejorando el estado de alerta matutino.',
    source: { author: 'Boschmann et al.', journal: 'JCEM', year: 2003 },
  },
  {
    id: 8,
    category: 'nutricion',
    icon: '🫘',
    title: 'Creatina 3-5g diarios',
    description:
      'Toma 3-5g de creatina monohidrato diariamente, en cualquier momento del dia (con o sin comida). No necesitas fase de carga. La consistencia diaria es lo mas importante.',
    explanation:
      'La creatina satura los depositos musculares de fosfocreatina (PCr), el sistema energetico primario en esfuerzos de alta intensidad (1-10 segundos). Aumenta el rendimiento en esfuerzos repetidos en 5-15%, reduce el dano muscular y tiene beneficios cognitivos emergentes. Es el suplemento mas estudiado en deporte.',
    source: { author: 'Kreider et al.', journal: 'JISSN', year: 2017 },
  },
  {
    id: 9,
    category: 'nutricion',
    icon: '☕',
    title: 'Cafeina pre-entreno 3-6mg/kg',
    description:
      'Consume 3-6 mg de cafeina por kg de peso corporal, 45-60 minutos antes del entrenamiento. Para 70kg: 210-420mg (2-4 tazas de cafe). Evita en las ultimas 6-8 horas antes de dormir.',
    explanation:
      'La cafeina bloquea competitivamente los receptores de adenosina (A1 y A2A), reduciendo la percepcion de esfuerzo (RPE) en 5-6% y aumentando la potencia maxima. El efecto es ergogenico tanto para fuerza como para resistencia, con alta consistencia en la literatura cientifica.',
    source: { author: 'Goldstein et al.', journal: 'JISSN', year: 2010 },
  },
  {
    id: 10,
    category: 'nutricion',
    icon: '🥜',
    title: 'Masticar 25-40 veces por bocado',
    description:
      'Practica masticar cada bocado al menos 25-40 veces antes de tragar. Come sin distracciones (sin telefono o television) y deja los cubiertos sobre la mesa entre bocado y bocado.',
    explanation:
      'La masticacion prolongada estimula la liberacion de GLP-1 y PYY, hormonas intestinales de saciedad, antes de que el alimento llegue al estomago. Estudios demuestran reduccion de hasta 12% en la ingesta calórica total sin esfuerzo consciente. El tiempo adicional permite que las senales de saciedad alcancen el hipotalamo.',
    source: { author: 'Li, J. et al.', journal: 'AJCN', year: 2011 },
  },

  // ---------------------------------------------------------------
  // TRAINING
  // ---------------------------------------------------------------
  {
    id: 11,
    category: 'training',
    icon: '📊',
    title: 'Sobrecarga progresiva semanal',
    description:
      'Aumenta el volumen o la intensidad de tu entrenamiento entre 2-5% por semana. Lleva un diario de entreno y planifica incrementos de carga antes de cada sesion. Si no puedes mejorar en 2 semanas consecutivas, reduce el volumen 40% (deload).',
    explanation:
      'El principio de sobrecarga progresiva es el fundamento del entrenamiento de fuerza e hipertrofia. Sin estimulo de adaptacion creciente, el cuerpo alcanza homeostasis y deja de responder. La planificacion sistematica previene el estancamiento y permite adaptaciones continuas a largo plazo.',
    source: { author: 'Kraemer & Ratamess', journal: 'MSSE', year: 2004 },
  },
  {
    id: 12,
    category: 'training',
    icon: '⏱️',
    title: 'Tempo eccentrico 3-4 segundos',
    description:
      'En ejercicios compuestos (sentadilla, press, remo), baja la fase excentrica en 3-4 segundos contados. Mantén control total del peso en todo momento. Aplica especialmente en las ultimas series de cada ejercicio.',
    explanation:
      'La fase excentrica genera mayor tension mecanica con menos activacion metabolica, lo que constituye un estimulo potente para hipertrofia. El musculo puede soportar 20-40% mas carga en la fase excentrica que en la concentrica. Estudios comparativos muestran mayor sintesis proteica con temporizacion excentrica controlada.',
    source: { author: 'Schoenfeld et al.', journal: 'EJSS', year: 2017 },
  },
  {
    id: 13,
    category: 'training',
    icon: '⏸️',
    title: 'Descanso 2-3 min para fuerza',
    description:
      'Para series de fuerza maxima (1-5 reps) y hipertrofia (6-12 reps), descansa 2-3 minutos entre series. Para resistencia muscular (15+ reps) puedes reducir a 60-90 segundos.',
    explanation:
      'La fosfocreatina (PCr) se resinteriza en un 85-95% despues de 2-3 minutos de descanso. Descansos mas cortos comprometen el rendimiento en series subsecuentes, reduciendo el volumen total efectivo. Estudios directos de Schoenfeld muestran que 3 minutos producen mayor hipertrofia que 1 minuto en igual volumen.',
    source: { author: 'Schoenfeld et al.', journal: 'JSCR', year: 2016 },
  },
  {
    id: 14,
    category: 'training',
    icon: '🎯',
    title: 'No todas las series al fallo (1-3 RIR)',
    description:
      'Entrena a 1-3 repeticiones del fallo muscular (RIR: Reps In Reserve) en la mayoria de tus series. Reserva las series al fallo para la ultima serie de cada ejercicio, no todas.',
    explanation:
      'Las series al fallo muscular generan mayor fatiga neuromuscular y acumulacion de metabolitos sin producir hipertrofia significativamente mayor que series cercanas al fallo. Mantener 1-3 RIR permite mayor volumen total de entrenamiento por sesion y mejor recuperacion intersesion, factores clave para progresar a largo plazo.',
    source: { author: 'Moran-Navarro et al.', journal: 'JSCR', year: 2012 },
  },
  {
    id: 15,
    category: 'training',
    icon: '📈',
    title: 'Volumen semanal 10-20 series por musculo',
    description:
      'Distribuye 10-20 series semanales por grupo muscular en 2-4 sesiones. Comienza en el limite inferior si eres principiante y progresa gradualmente. Asegura al menos 48h de recuperacion entre sesiones del mismo grupo.',
    explanation:
      'La relacion dosis-respuesta entre volumen de entrenamiento e hipertrofia sigue una curva en U invertida. Menos de 10 series semanales produce adaptaciones suboptimas; mas de 20 series puede exceder la capacidad de recuperacion. La distribucion en multiples sesiones (frecuencia 2x) supera a la sesion unica por igual volumen.',
    source: { author: 'Schoenfeld et al.', journal: 'JSS', year: 2017 },
  },

  // ---------------------------------------------------------------
  // RECOVERY
  // ---------------------------------------------------------------
  {
    id: 16,
    category: 'recovery',
    icon: '😴',
    title: '7-9 horas de sueno para anabolismo',
    description:
      'Prioriza 7-9 horas de sueno ininterrumpido en horarios regulares. Establece una hora fija de despertar incluso en fines de semana. El sueno no es negociable si el objetivo es composicion corporal y rendimiento.',
    explanation:
      'El 95% de la hormona de crecimiento (GH) se libera durante el sueno de ondas lentas (etapas 3-4). La privacion de sueno reduce la testosterona 10-15% por noche, eleva el cortisol, reduce la sintesis proteica y aumenta la degradacion muscular. El deficit de sueno es el mayor saboteador invisible del progreso fisico.',
    source: { author: 'Dattilo et al.', journal: 'Med Hyp', year: 2011 },
  },
  {
    id: 17,
    category: 'recovery',
    icon: '🧊',
    title: 'Contraste frio-calor post-entreno',
    description:
      'Alterna 1 minuto de agua fria (12-15°C) con 2 minutos de agua caliente (38-40°C), repitiendo 3-5 ciclos al finalizar el entrenamiento. Termina siempre con agua fria.',
    explanation:
      'La alternancia termica crea una "bomba vascular" que dilata y contrae los vasos sanguineos, mejorando el flujo linfatico y la eliminacion de metabolitos de desecho (lactato, H+). Estudios reportan reduccion de lactato sanguineo en ~30% y menor percepcion de fatiga muscular en las 24h post-ejercicio.',
    source: { author: 'Versey et al.', journal: 'Sports Med', year: 2013 },
  },
  {
    id: 18,
    category: 'recovery',
    icon: '🧹',
    title: 'Foam rolling 5-10 min post-entreno',
    description:
      'Realiza foam rolling en los grupos musculares trabajados durante 5-10 minutos al finalizar el entrenamiento. Aplica presion moderada en zonas de mayor tension, deteniendote 20-30 segundos en los puntos mas sensibles.',
    explanation:
      'El foam rolling actua sobre los mecanorreceptores fasciales y reduce la actividad del sistema nervioso simpatico en la zona tratada. Meta-analisis muestran reduccion del DOMS (dolor muscular tardio) de hasta 30% en las primeras 72 horas, mayor rango de movimiento y mejor percepcion de recuperacion sin comprometer el rendimiento posterior.',
    source: { author: 'Pearcey et al.', journal: 'JAT', year: 2015 },
  },
  {
    id: 19,
    category: 'recovery',
    icon: '🍌',
    title: 'Proteina + carbs en ventana post-entreno',
    description:
      'Consume 20-40g de proteina + 0.5-1g/kg de carbohidratos dentro de los 30-60 minutos post-entrenamiento. No es obligatorio si tu ingesta diaria total es adecuada, pero es optimo si entrenas en ayunas o entre comidas.',
    explanation:
      'El ejercicio de resistencia aumenta la sensibilidad a la insulina y el transporte de glucosa (GLUT4) durante 30-60 minutos post-entreno. La combinacion proteina + carbohidrato eleva la insulina 30-40% mas que la proteina sola, optimizando la captacion de aminoácidos por el musculo y acelerando la resintesis de glucogeno.',
    source: { author: 'Aragon & Schoenfeld', journal: 'JISSN', year: 2013 },
  },
  {
    id: 20,
    category: 'recovery',
    icon: '🚶',
    title: 'Descanso activo entre dias de entreno',
    description:
      'En tus dias de descanso, realiza 20-40 minutos de actividad de baja intensidad: caminar, natacion suave, yoga, ciclismo recreativo a <60% FCmax. Evita el reposo total completo.',
    explanation:
      'El descanso activo mantiene el flujo sanguineo muscular sin generar estres de entrenamiento adicional. Esto acelera la eliminacion de metabolitos, reduce la rigidez fascial y disminuye el DOMS en 20-25% comparado con el reposo total. La actividad suave tambien preserva el estado psicologico de adherencia al programa.',
    source: { author: 'Bishop et al.', journal: 'Sports Med', year: 2008 },
  },

  // ---------------------------------------------------------------
  // STRESS
  // ---------------------------------------------------------------
  {
    id: 21,
    category: 'stress',
    icon: '🫁',
    title: 'Respiracion 4-7-8 para cortisol',
    description:
      'Inhala por nariz 4 segundos, retén 7 segundos, exhala por boca 8 segundos. Repite 4-8 ciclos. Aplica ante situaciones de estres agudo, antes de reuniones importantes o para facilitar el sueno.',
    explanation:
      'La exhalacion prolongada activa el nervio vago, aumentando la actividad parasimpatica (descanso y digestion) y reduciendo el tono simpatico (lucha o huida). Esto baja el cortisol circulante rapidamente y produce un "reset" del sistema autonomo. La variabilidad de frecuencia cardiaca (HRV) mejora significativamente en sesiones de respiracion controlada.',
    source: { author: 'Ma, X. et al.', journal: 'Front Psych', year: 2017 },
  },
  {
    id: 22,
    category: 'stress',
    icon: '🌿',
    title: 'Caminar 20 min en naturaleza',
    description:
      'Camina 20-30 minutos en un parque, bosque o cualquier entorno natural al menos 3 veces por semana. Deja el telefono en silencio y practica atencion plena a los estimulos sensoriales del entorno.',
    explanation:
      'Exposicion a entornos naturales reduce el cortisol salival en 12.4% y aumenta las celulas NK (Natural Killer) del sistema inmune en 50% segun estudios de "banos de bosque" japoneses (Shinrin-yoku). La atencion involuntaria (fascinacion suave) restaura la capacidad de atencion dirigida, reduciendo la fatiga mental.',
    source: { author: 'Hunter et al.', journal: 'Front Psych', year: 2019 },
  },
  {
    id: 23,
    category: 'stress',
    icon: '📝',
    title: 'Journaling 15 minutos diarios',
    description:
      'Escribe libremente durante 15 minutos cada dia sobre tus experiencias, emociones y pensamientos. No edites ni corrijas — deja fluir. Puedes incluir gratitud, reflexion sobre el dia o metas futuras.',
    explanation:
      'El "etiquetado afectivo" (poner emociones en palabras) reduce la actividad de la amigdala hasta en 30% segun estudios de neuroimagen. El journaling expresivo de Pennebaker mejora indicadores inmunologicos, reduce la presion arterial y esta asociado con menor ansiedad y depresion en multiples meta-analisis.',
    source: { author: 'Smyth et al.', journal: 'JAMA', year: 1999 },
  },
  {
    id: 24,
    category: 'stress',
    icon: '🚿',
    title: 'Duchas frias 2-3 minutos',
    description:
      'Termina tu ducha con 2-3 minutos de agua fria (15-20°C). Comienza con 30 segundos e incrementa progresivamente. Respira profundamente durante la exposicion al frio.',
    explanation:
      'La exposicion aguda al frio aumenta la norepinefrina plasmatica en 530% y la dopamina en ~250%, produciendo mejora sostenida del estado de animo y enfoque. Activa el tejido adiposo marron (BAT) que quema calorias para generar calor. Tambien eleva la expresion de genes antioxidantes y antiinflamatorios.',
    source: { author: 'Shevchuk, N.A.', journal: 'Med Hyp', year: 2008 },
  },
  {
    id: 25,
    category: 'stress',
    icon: '🧠',
    title: 'Meditacion 10 min reduce ansiedad',
    description:
      'Practica meditacion de atencion plena (mindfulness) durante 10-20 minutos diarios usando apps como Headspace, Calm o simplemente contando respiraciones. La consistencia diaria supera a sesiones largas ocasionales.',
    explanation:
      'Meta-analisis de 47 ensayos clinicos (Goyal et al., 2014) demuestran que la meditacion reduce sintomas de ansiedad con tamano de efecto moderado (0.38-0.40). Estudios de neuroimagen muestran reduccion de la densidad de materia gris en amigdala y aumento en corteza prefrontal (PFC) — el opuesto del patron de estres cronico.',
    source: { author: 'Goyal et al.', journal: 'JAMA IM', year: 2014 },
  },
];

// -------------------------------------------------------------------
// Reactive state
// -------------------------------------------------------------------
const searchQuery = ref('');
const selectedCategory = ref<string | null>(null);
const selectedHack = ref<Hack | null>(null);

// -------------------------------------------------------------------
// Computed
// -------------------------------------------------------------------
const filteredHacks = computed<Hack[]>(() => {
  let result = allHacks;

  if (selectedCategory.value) {
    result = result.filter((h) => h.category === selectedCategory.value);
  }

  const q = searchQuery.value.trim().toLowerCase();
  if (q) {
    result = result.filter(
      (h) =>
        h.title.toLowerCase().includes(q) ||
        h.description.toLowerCase().includes(q) ||
        h.source.author.toLowerCase().includes(q) ||
        h.source.journal.toLowerCase().includes(q),
    );
  }

  return result;
});

// -------------------------------------------------------------------
// Helpers
// -------------------------------------------------------------------
function getCategoryLabel(categoryId: string): string {
  return categories.find((c) => c.id === categoryId)?.label ?? categoryId;
}

// -------------------------------------------------------------------
// Modal
// -------------------------------------------------------------------
function openModal(hack: Hack) {
  selectedHack.value = hack;
}

function closeModal() {
  selectedHack.value = null;
}
</script>

<style scoped>
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.2s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

.scrollbar-hide {
  scrollbar-width: none;
  -ms-overflow-style: none;
}
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
