<template>
  <ClientLayout>
  <div class="space-y-8 px-4 sm:px-0 sm:space-y-10">

    <!-- ═══ HERO HEADER ═══════════════════════════════════════════════════════ -->
    <div class="relative overflow-hidden rounded-2xl border border-wc-border bg-wc-bg-secondary px-6 py-8 sm:px-10 sm:py-10">
      <!-- Decorative moon icon (low opacity background) -->
      <svg
        class="pointer-events-none absolute -right-6 -top-6 h-48 w-48 opacity-[0.04] text-wc-accent"
        viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"
      >
        <path d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75 9.75 9.75 0 0 1 8.25 6c0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 12c0 5.385 4.365 9.75 9.75 9.75 4.282 0 7.867-2.634 9.002-6.248Z" />
      </svg>

      <!-- Subtle gradient overlay -->
      <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-wc-accent/5 via-transparent to-transparent rounded-2xl" aria-hidden="true"></div>

      <div class="relative">
        <!-- Badge -->
        <span class="mb-3 inline-flex items-center gap-1.5 rounded-full border border-wc-accent/30 bg-wc-accent/10 px-3 py-1 text-[10px] font-semibold uppercase tracking-widest text-wc-accent">
          <span class="inline-block h-1.5 w-1.5 rounded-full bg-wc-accent"></span>
          Salud Mental · Rendimiento
        </span>

        <h1 class="font-display text-4xl tracking-wide text-wc-text sm:text-5xl">
          BIENESTAR <span class="text-wc-accent">MENTAL</span>
        </h1>
        <p class="mt-2 max-w-lg text-sm text-wc-text-secondary">
          Respiración guiada y sesiones de atención plena para optimizar tu recuperación, foco y rendimiento deportivo.
        </p>
      </div>
    </div>

    <!-- ═══ SECTION 1: Sesiones Guiadas ══════════════════════════════════════ -->
    <section aria-label="Sesiones guiadas">
      <h2 class="mb-5 font-display text-xl tracking-wide text-wc-text">SESIONES GUIADAS</h2>

      <Transition name="fade" mode="out-in">

        <!-- ── Active Session Timer ────────────────────────────────────────── -->
        <div
          v-if="activeSessionId"
          key="active"
          class="relative overflow-hidden rounded-2xl border border-wc-accent/30 bg-wc-bg-tertiary p-6 sm:p-8"
        >
          <!-- Glow background -->
          <div class="pointer-events-none absolute inset-0 rounded-2xl bg-[radial-gradient(ellipse_at_top_right,rgba(220,38,38,0.08),transparent_60%)]" aria-hidden="true"></div>

          <!-- Header -->
          <div class="relative mb-6 flex flex-wrap items-start justify-between gap-4">
            <div>
              <!-- Pulsing badge -->
              <span class="inline-flex items-center gap-1.5 rounded-full bg-wc-accent/15 px-3 py-1 text-[10px] font-semibold uppercase tracking-widest text-wc-accent">
                <span class="inline-block h-2 w-2 animate-pulse rounded-full bg-wc-accent"></span>
                Sesión activa
              </span>
              <h3 class="mt-2 font-display text-2xl tracking-wide text-wc-text">
                {{ currentSession?.emoji }} {{ currentSession?.title }}
              </h3>
            </div>
            <button
              @click="endSession"
              class="rounded-lg border border-wc-accent/50 px-4 py-2 text-sm font-semibold text-wc-accent transition-colors hover:bg-wc-accent/10 focus:outline-none focus:ring-2 focus:ring-wc-accent"
            >
              Terminar sesión
            </button>
          </div>

          <!-- Circular Timer -->
          <div class="relative flex flex-col items-center gap-6">
            <div class="relative flex h-44 w-44 items-center justify-center sm:h-52 sm:w-52">
              <!-- Outer glow ring -->
              <div class="absolute inset-0 rounded-full shadow-[0_0_40px_rgba(220,38,38,0.12)]"></div>
              <svg class="-rotate-90 absolute inset-0 h-full w-full" viewBox="0 0 200 200" aria-hidden="true">
                <circle cx="100" cy="100" r="88" fill="none" stroke="currentColor" stroke-width="4" class="text-wc-border" />
                <circle
                  cx="100" cy="100" r="88"
                  fill="none" stroke="#DC2626" stroke-width="4" stroke-linecap="round"
                  :stroke-dasharray="SESSION_CIRC"
                  :stroke-dashoffset="sessionProgressOffset"
                  class="transition-all duration-1000"
                />
              </svg>
              <div class="relative text-center" aria-live="polite" aria-atomic="true">
                <span class="font-data text-4xl font-bold tabular-nums text-wc-text sm:text-5xl">{{ formatSessionTime(sessionRemaining) }}</span>
                <p class="mt-1 text-[10px] font-semibold uppercase tracking-widest text-wc-text-tertiary">restante</p>
              </div>
            </div>

            <!-- Completed indicator -->
            <Transition name="fade">
              <div v-if="sessionRemaining === 0 && !sessionRunning" class="rounded-xl border border-green-500/30 bg-green-500/10 px-6 py-3 text-center">
                <p class="text-sm font-semibold text-green-400">Sesion completada</p>
                <p class="mt-1 text-xs text-wc-text-secondary">Buen trabajo. Tu mente y cuerpo lo agradecen.</p>
              </div>
            </Transition>

            <!-- Pause / Resume controls -->
            <div class="flex items-center gap-3">
              <button
                v-if="sessionRunning"
                @click="pauseSession"
                class="rounded-lg border border-yellow-500/60 px-6 py-2.5 text-sm font-semibold text-yellow-400 transition-colors hover:bg-yellow-500/10 focus:outline-none focus:ring-2 focus:ring-wc-accent"
              >
                Pausar
              </button>
              <button
                v-if="!sessionRunning && sessionRemaining > 0"
                @click="resumeSession"
                class="rounded-lg bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-wc-accent"
              >
                Reanudar
              </button>
            </div>
          </div>

          <!-- Linear progress bar -->
          <div class="relative mt-6" role="progressbar" :aria-valuenow="Math.round(sessionProgress)" aria-valuemin="0" aria-valuemax="100">
            <div class="h-1.5 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
              <div class="h-full rounded-full bg-wc-accent transition-all duration-1000" :style="'width:' + sessionProgress + '%'"></div>
            </div>
            <div class="mt-1.5 flex justify-between text-xs text-wc-text-tertiary">
              <span>Inicio</span>
              <span>{{ Math.round(sessionProgress) }}%</span>
              <span>Fin</span>
            </div>
          </div>
        </div>

        <!-- ── Session Cards Grid ──────────────────────────────────────────── -->
        <div v-else key="cards" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
          <button
            v-for="session in SESSIONS"
            :key="session.id"
            @click="startSession(session.id)"
            :aria-label="'Iniciar sesión: ' + session.title"
            class="group relative overflow-hidden rounded-xl border border-wc-border bg-wc-bg-tertiary text-left transition-all hover:border-wc-accent/50 hover:bg-wc-bg-secondary focus:outline-none focus:ring-2 focus:ring-wc-accent active:scale-[0.98]"
          >
            <!-- Top color stripe -->
            <div
              class="h-[3px] w-full transition-all duration-300"
              :class="{
                'bg-wc-accent': session.id === 'breathing',
                'bg-blue-500': session.id === 'meditation',
                'bg-purple-500': session.id === 'body-scan',
                'bg-amber-500': session.id === 'visualization',
              }"
            ></div>

            <!-- Card body — horizontal on mobile, vertical on sm+ -->
            <div class="flex flex-row items-center gap-4 p-4 sm:flex-col sm:items-start sm:p-5">
              <!-- Emoji block -->
              <span
                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-wc-bg-secondary text-2xl sm:h-10 sm:w-10 sm:text-xl"
                aria-hidden="true"
              >{{ session.emoji }}</span>

              <!-- Text block -->
              <div class="min-w-0 flex-1 sm:mt-2">
                <h3 class="font-display text-base tracking-wide text-wc-text leading-tight sm:text-lg">
                  {{ session.title.toUpperCase() }}
                </h3>
                <p class="mt-1 text-xs leading-relaxed text-wc-text-secondary line-clamp-2 sm:line-clamp-none">
                  {{ session.description }}
                </p>

                <!-- Footer meta -->
                <div class="mt-3 flex flex-wrap items-center gap-2">
                  <span class="inline-flex items-center gap-1 rounded-full border border-wc-border bg-wc-bg-secondary px-2 py-0.5 text-xs font-medium text-wc-text-secondary">
                    <svg class="h-3 w-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <span class="font-data text-xs">{{ session.duration }}</span>
                  </span>
                  <span class="text-[10px] font-semibold text-wc-accent">{{ session.benefit }}</span>
                </div>
              </div>
            </div>

            <!-- Hover arrow -->
            <div class="absolute right-3 top-5 opacity-0 transition-opacity group-hover:opacity-100" aria-hidden="true">
              <svg class="h-3.5 w-3.5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
              </svg>
            </div>
          </button>
        </div>

      </Transition>
    </section>

    <!-- ═══ SECTION 2: Respiración ════════════════════════════════════════════ -->
    <section aria-label="Ejercicios de respiración">
      <h2 class="mb-5 font-display text-xl tracking-wide text-wc-text">EJERCICIOS DE RESPIRACIÓN</h2>

      <!-- Technique Tabs — horizontal scroll on mobile -->
      <div class="overflow-x-auto pb-1" role="tablist" aria-label="Técnicas de respiración">
        <div class="flex min-w-max gap-2 sm:min-w-0 sm:flex-wrap">
          <button
            v-for="t in TECHNIQUE_LIST"
            :key="t.id"
            role="tab"
            :aria-selected="technique === t.id"
            @click="selectTechnique(t.id)"
            :class="technique === t.id ? 'bg-wc-accent text-white border-wc-accent' : 'bg-wc-bg-tertiary text-wc-text-secondary border-wc-border hover:text-wc-text'"
            class="rounded-lg border px-4 py-2 text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-wc-accent whitespace-nowrap"
          >
            {{ t.label }}
          </button>
        </div>
      </div>

      <div class="mx-auto mt-6 max-w-md">

        <!-- Breathing Circle -->
        <div class="relative mx-auto flex h-64 w-64 items-center justify-center sm:h-72 sm:w-72">
          <!-- Ambient glow -->
          <div
            class="absolute inset-0 rounded-full transition-all duration-1000"
            :class="running
              ? (phase === 'inhala'
                  ? 'shadow-[0_0_80px_rgba(220,38,38,0.22)]'
                  : phase === 'exhala'
                    ? 'shadow-[0_0_80px_rgba(59,130,246,0.22)]'
                    : 'shadow-[0_0_80px_rgba(168,85,247,0.22)]')
              : ''"
          ></div>

          <!-- Dashed background ring -->
          <svg class="absolute inset-0" viewBox="0 0 200 200" aria-hidden="true">
            <circle cx="100" cy="100" r="90" fill="none" stroke="currentColor" stroke-width="2" class="text-wc-border" stroke-dasharray="4 4" />
          </svg>

          <!-- Animated breathing circle -->
          <svg
            class="absolute inset-0"
            viewBox="0 0 200 200"
            :style="{ transform: `scale(${breathScale})`, transformOrigin: 'center', transition: `transform ${phaseDuration}s ease-in-out` }"
            aria-hidden="true"
          >
            <defs>
              <radialGradient id="breathGrad" cx="50%" cy="50%" r="50%">
                <stop offset="0%"
                  :stop-color="phase === 'inhala' ? '#DC2626' : phase === 'exhala' ? '#3B82F6' : '#A855F7'"
                  stop-opacity="0.18"
                />
                <stop offset="100%"
                  :stop-color="phase === 'inhala' ? '#DC2626' : phase === 'exhala' ? '#3B82F6' : '#A855F7'"
                  stop-opacity="0.02"
                />
              </radialGradient>
            </defs>
            <circle cx="100" cy="100" r="70" fill="url(#breathGrad)" />
            <circle
              cx="100" cy="100" r="70"
              fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"
              :class="phase === 'inhala' ? 'text-wc-accent' : phase === 'exhala' ? 'text-blue-500' : 'text-purple-500'"
              class="transition-colors duration-500"
            />
          </svg>

          <!-- Progress ring -->
          <svg class="absolute inset-0 -rotate-90" viewBox="0 0 200 200" aria-hidden="true">
            <circle
              cx="100" cy="100" r="92"
              fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
              class="text-wc-accent/40 transition-all duration-1000"
              :stroke-dasharray="BREATH_CIRC"
              :stroke-dashoffset="progressOffset"
            />
          </svg>

          <!-- Center text -->
          <div class="relative text-center" aria-live="polite" aria-atomic="true">
            <p class="font-data text-5xl font-bold tabular-nums text-wc-text sm:text-6xl">{{ phaseCountdown }}</p>
            <p
              v-if="running || paused"
              class="mt-2 text-sm font-semibold uppercase tracking-widest transition-colors duration-500"
              :class="phase === 'inhala' ? 'text-wc-accent' : phase === 'exhala' ? 'text-blue-500' : 'text-purple-500'"
            >{{ phaseLabel }}</p>
            <p v-if="running || paused" class="mt-1 text-xs text-wc-text-secondary">
              Ciclo {{ currentCycle }}/{{ totalCycles }}
            </p>
            <p v-if="!running && !paused" class="text-xs text-wc-text-tertiary">Presiona iniciar</p>
          </div>
        </div>

        <!-- Config panel (visible when stopped) -->
        <div v-if="!running && !paused" class="mt-6 space-y-4 rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-6">
          <div>
            <h3 class="text-sm font-semibold text-wc-text">{{ currentTechnique.label }}</h3>
            <p class="mt-1 text-xs text-wc-text-secondary">{{ currentTechnique.description }}</p>
          </div>
          <div class="flex flex-wrap items-center gap-2 text-xs">
            <template v-for="(p, i) in currentTechnique.phases" :key="i">
              <div class="flex items-center gap-1">
                <span class="inline-block h-2 w-2 rounded-full" :class="p.type === 'inhala' ? 'bg-wc-accent' : p.type === 'exhala' ? 'bg-blue-500' : 'bg-purple-500'"></span>
                <span class="text-wc-text-secondary">{{ p.label }} {{ p.seconds }}s</span>
                <span v-if="i < currentTechnique.phases.length - 1" class="mx-1 text-wc-text-tertiary">→</span>
              </div>
            </template>
          </div>
          <div>
            <label class="block text-xs font-medium text-wc-text-secondary">Ciclos</label>
            <input
              type="number"
              v-model.number="totalCycles"
              min="1" max="20"
              aria-label="Número de ciclos"
              class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
            />
            <p class="mt-1 text-xs text-wc-text-secondary">
              Duración total: {{ formatBreathTime(totalCycles * currentTechnique.cycleDuration) }}
            </p>
          </div>
          <label class="flex cursor-pointer items-center gap-3">
            <div class="relative">
              <input type="checkbox" v-model="soundEnabled" class="sr-only peer" aria-label="Sonido ambiental" />
              <div class="h-5 w-9 rounded-full bg-wc-border transition-colors peer-checked:bg-wc-accent"></div>
              <div class="absolute left-0.5 top-0.5 h-4 w-4 rounded-full bg-white transition-transform peer-checked:translate-x-4"></div>
            </div>
            <span class="text-xs text-wc-text-secondary">Sonido ambiental guía</span>
          </label>
        </div>

        <!-- Controls -->
        <div class="mt-6 flex flex-col items-stretch gap-3 sm:flex-row sm:items-center sm:justify-center sm:gap-4">
          <button
            v-if="!running && !paused"
            @click="startBreathing"
            class="w-full rounded-lg bg-wc-accent px-8 py-3 text-sm font-semibold text-white transition-colors hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-wc-accent sm:w-auto"
          >
            Iniciar
          </button>
          <button
            v-if="running"
            @click="pauseBreathing"
            class="w-full rounded-lg border border-yellow-500 px-8 py-3 text-sm font-semibold text-yellow-400 transition-colors hover:bg-yellow-500/10 focus:outline-none focus:ring-2 focus:ring-wc-accent sm:w-auto"
          >
            Pausar
          </button>
          <button
            v-if="paused"
            @click="resumeBreathing"
            class="w-full rounded-lg bg-wc-accent px-8 py-3 text-sm font-semibold text-white transition-colors hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-wc-accent sm:w-auto"
          >
            Reanudar
          </button>
          <button
            v-if="running || paused"
            @click="stopBreathing"
            class="w-full rounded-lg border border-wc-border px-8 py-3 text-sm font-semibold text-wc-text-secondary transition-colors hover:text-wc-text focus:outline-none focus:ring-2 focus:ring-wc-accent sm:w-auto"
          >
            Detener
          </button>
        </div>

        <!-- Session complete banner -->
        <Transition name="fade">
          <div v-if="completed" class="mt-6 rounded-xl border border-green-500/30 bg-green-500/10 p-6 text-center" role="status">
            <svg class="mx-auto h-12 w-12 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <p class="mt-3 text-sm font-semibold text-green-400">Sesión completada</p>
            <p class="mt-1 text-xs text-wc-text-secondary">
              {{ currentTechnique.label }} · {{ totalCycles }} ciclos · {{ formatBreathTime(totalCycles * currentTechnique.cycleDuration) }}
            </p>
            <button
              @click="completed = false"
              class="mt-4 rounded-lg border border-wc-border px-6 py-2 text-sm font-medium text-wc-text-secondary transition-colors hover:text-wc-text focus:outline-none focus:ring-2 focus:ring-wc-accent"
            >
              Cerrar
            </button>
          </div>
        </Transition>

        <!-- Technique info cards -->
        <div class="mt-8 grid grid-cols-1 gap-3 sm:grid-cols-3">
          <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
            <div class="flex items-center gap-2">
              <span class="inline-block h-2.5 w-2.5 rounded-full bg-wc-accent"></span>
              <p class="text-xs font-semibold text-wc-accent">4-7-8</p>
            </div>
            <p class="mt-2 text-xs leading-relaxed text-wc-text-secondary">Técnica del Dr. Andrew Weil. Inhala 4s, mantén 7s, exhala 8s. Activa el sistema nervioso parasimpático para calma profunda.</p>
          </div>
          <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
            <div class="flex items-center gap-2">
              <span class="inline-block h-2.5 w-2.5 rounded-full bg-blue-500"></span>
              <p class="text-xs font-semibold text-blue-400">Box Breathing</p>
            </div>
            <p class="mt-2 text-xs leading-relaxed text-wc-text-secondary">Usada por Navy SEALs. 4 fases iguales de 4s. Equilibra el sistema nervioso autónomo y mejora el enfoque bajo presión.</p>
          </div>
          <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
            <div class="flex items-center gap-2">
              <span class="inline-block h-2.5 w-2.5 rounded-full bg-purple-500"></span>
              <p class="text-xs font-semibold text-purple-400">Coherente</p>
            </div>
            <p class="mt-2 text-xs leading-relaxed text-wc-text-secondary">Frecuencia de resonancia: 5.5 respiraciones/min. Sincroniza corazón, cerebro y sistema nervioso. Ideal pre-entrenamiento.</p>
          </div>
        </div>

      </div>
    </section>

  </div>
  </ClientLayout>
</template>

<script setup>
import { ref, computed, onBeforeUnmount } from 'vue';
import ClientLayout from '../../layouts/ClientLayout.vue';

// ─── Constants ────────────────────────────────────────────────────────────────
const SESSION_CIRC = 2 * Math.PI * 88;
const BREATH_CIRC  = 2 * Math.PI * 92;

const SESSIONS = [
  { id: 'breathing',     title: 'Respiración 4-7-8',            description: 'Inhala 4s, retén 7s, exhala 8s. Reduce cortisol y activa el nervio vago.',                            duration: '5 min',  emoji: '🌬️', benefit: 'Reduce estrés',       seconds: 300 },
  { id: 'meditation',    title: 'Meditación de Atención Plena',  description: 'Observa tus pensamientos sin juzgar. Mejora el foco y la recuperación.',                              duration: '10 min', emoji: '🧘', benefit: 'Mejora foco',          seconds: 600 },
  { id: 'body-scan',     title: 'Body Scan',                     description: 'Recorre cada parte de tu cuerpo con atención. Detecta tensión muscular.',                             duration: '15 min', emoji: '🔍', benefit: 'Recuperación activa',  seconds: 900 },
  { id: 'visualization', title: 'Visualización de Rendimiento',  description: 'Visualiza tu próximo entrenamiento o competición. Técnica usada por atletas de élite.',              duration: '8 min',  emoji: '🏆', benefit: 'Mejora rendimiento',    seconds: 480 },
];

const TECHNIQUES = {
  '478': {
    id: '478', label: 'Respiración 4-7-8',
    description: 'Técnica del Dr. Andrew Weil. Reduce ansiedad, mejora el sueño y activa la respuesta de relajación.',
    cycleDuration: 19,
    phases: [
      { type: 'inhala', label: 'Inhala', seconds: 4 },
      { type: 'manten', label: 'Mantén', seconds: 7 },
      { type: 'exhala', label: 'Exhala', seconds: 8 },
    ],
  },
  'box': {
    id: 'box', label: 'Box Breathing',
    description: 'Técnica Navy SEAL. 4 fases iguales para control total del sistema nervioso bajo presión.',
    cycleDuration: 16,
    phases: [
      { type: 'inhala', label: 'Inhala', seconds: 4 },
      { type: 'manten', label: 'Mantén', seconds: 4 },
      { type: 'exhala', label: 'Exhala', seconds: 4 },
      { type: 'manten', label: 'Mantén', seconds: 4 },
    ],
  },
  'coherent': {
    id: 'coherent', label: 'Respiración Coherente',
    description: 'Frecuencia de resonancia a 5.5 respiraciones/min. Sincroniza ritmo cardíaco y sistema nervioso.',
    cycleDuration: 11,
    phases: [
      { type: 'inhala', label: 'Inhala', seconds: 5.5 },
      { type: 'exhala', label: 'Exhala', seconds: 5.5 },
    ],
  },
};

const TECHNIQUE_LIST = [
  { id: '478',      label: '4-7-8' },
  { id: 'box',      label: 'Box Breathing' },
  { id: 'coherent', label: 'Coherente' },
];

// ─── Section 1: Guided Sessions ───────────────────────────────────────────────
const activeSessionId  = ref('');
const sessionDuration  = ref(0);
const sessionRemaining = ref(0);
const sessionRunning   = ref(false);

let sessionTimer = null;

const currentSession = computed(() => SESSIONS.find(s => s.id === activeSessionId.value) ?? null);

const sessionProgress = computed(() =>
  sessionDuration.value > 0
    ? ((sessionDuration.value - sessionRemaining.value) / sessionDuration.value) * 100
    : 0
);

const sessionProgressOffset = computed(() => SESSION_CIRC * (1 - sessionProgress.value / 100));

function formatSessionTime(secs) {
  const m = Math.floor(secs / 60);
  const s = secs % 60;
  return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
}

function startSession(type) {
  const session = SESSIONS.find(s => s.id === type);
  if (!session) return;
  clearInterval(sessionTimer);
  activeSessionId.value  = type;
  sessionDuration.value  = session.seconds;
  sessionRemaining.value = session.seconds;
  sessionRunning.value   = true;
  runSessionTick();
}

function runSessionTick() {
  sessionTimer = setInterval(() => {
    if (sessionRemaining.value > 0) {
      sessionRemaining.value--;
    } else {
      sessionRunning.value = false;
      clearInterval(sessionTimer);
    }
  }, 1000);
}

function pauseSession() {
  sessionRunning.value = false;
  clearInterval(sessionTimer);
}

function resumeSession() {
  if (sessionRemaining.value <= 0) return;
  sessionRunning.value = true;
  runSessionTick();
}

function endSession() {
  clearInterval(sessionTimer);
  sessionRunning.value   = false;
  activeSessionId.value  = '';
  sessionDuration.value  = 0;
  sessionRemaining.value = 0;
}

// ─── Section 2: Breathing Exercises ──────────────────────────────────────────
const technique     = ref('478');
const totalCycles   = ref(4);
const currentCycle  = ref(1);
const running       = ref(false);
const paused        = ref(false);
const completed     = ref(false);
const phase         = ref('inhala');
const phaseIndex    = ref(0);
const phaseCountdown = ref(0);
const phaseDuration = ref(1);
const breathScale   = ref(0.6);
const elapsed       = ref(0);
const progressOffset = ref(BREATH_CIRC);
const soundEnabled  = ref(true);

// Module-level mutable — not reactive (no need for proxy on audio handles)
let breathTimer = null;
let audioCtx    = null;
let oscillator  = null;
let gainNode    = null;

const currentTechnique = computed(() => TECHNIQUES[technique.value]);

const phaseLabel = computed(() => {
  const labels = { inhala: 'INHALA', manten: 'MANTÉN', exhala: 'EXHALA' };
  return labels[phase.value] || '';
});

function formatBreathTime(secs) {
  const m = Math.floor(secs / 60);
  const s = Math.round(secs % 60);
  return m > 0 ? m + 'min ' + s + 's' : s + 's';
}

function selectTechnique(id) {
  stopBreathing();
  technique.value = id;
}

function startBreathing() {
  completed.value    = false;
  currentCycle.value = 1;
  phaseIndex.value   = 0;
  elapsed.value      = 0;
  running.value      = true;
  paused.value       = false;
  startPhase();
  breathTick();
}

function startPhase() {
  const p = currentTechnique.value.phases[phaseIndex.value];
  phase.value         = p.type;
  phaseDuration.value = p.seconds;
  phaseCountdown.value = Math.ceil(p.seconds);

  if (p.type === 'inhala') breathScale.value = 1.0;
  else if (p.type === 'exhala') breathScale.value = 0.6;

  if (soundEnabled.value) playTone(p.type, p.seconds);
}

function breathTick() {
  const totalDuration = totalCycles.value * currentTechnique.value.cycleDuration;
  breathTimer = setInterval(() => {
    elapsed.value++;
    phaseCountdown.value = Math.max(0, phaseCountdown.value - 1);
    progressOffset.value = BREATH_CIRC * (1 - Math.min(elapsed.value / totalDuration, 1));
    if (phaseCountdown.value <= 0) nextBreathPhase();
  }, 1000);
}

function nextBreathPhase() {
  const phases = currentTechnique.value.phases;
  phaseIndex.value++;

  if (phaseIndex.value >= phases.length) {
    if (currentCycle.value >= totalCycles.value) {
      completeBreathing();
      return;
    }
    currentCycle.value++;
    phaseIndex.value = 0;
  }

  startPhase();
}

function completeBreathing() {
  stopAudio();
  clearInterval(breathTimer);
  running.value      = false;
  completed.value    = true;
  breathScale.value  = 0.6;
  progressOffset.value = 0;
  if (soundEnabled.value) playChime();
}

function pauseBreathing() {
  clearInterval(breathTimer);
  running.value = false;
  paused.value  = true;
  stopAudio();
}

function resumeBreathing() {
  running.value = true;
  paused.value  = false;
  breathTick();
  if (soundEnabled.value) {
    const p = currentTechnique.value.phases[phaseIndex.value];
    playTone(p.type, phaseCountdown.value);
  }
}

function stopBreathing() {
  clearInterval(breathTimer);
  stopAudio();
  running.value        = false;
  paused.value         = false;
  completed.value      = false;
  phaseCountdown.value = 0;
  breathScale.value    = 0.6;
  progressOffset.value = BREATH_CIRC;
  elapsed.value        = 0;
  phase.value          = 'inhala';
  phaseIndex.value     = 0;
  currentCycle.value   = 1;
}

// ─── Web Audio ───────────────────────────────────────────────────────────────
function initAudio() {
  if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
  if (audioCtx.state === 'suspended') audioCtx.resume();
}

function playTone(type, duration) {
  try {
    stopAudio();
    initAudio();
    const ctx = audioCtx;
    oscillator = ctx.createOscillator();
    gainNode   = ctx.createGain();
    const freqs = { inhala: 174, manten: 285, exhala: 136 };
    oscillator.type = 'sine';
    oscillator.frequency.setValueAtTime(freqs[type] || 174, ctx.currentTime);
    gainNode.gain.setValueAtTime(0, ctx.currentTime);
    gainNode.gain.linearRampToValueAtTime(0.08, ctx.currentTime + 0.5);
    gainNode.gain.linearRampToValueAtTime(0.06, ctx.currentTime + duration * 0.5);
    gainNode.gain.linearRampToValueAtTime(0, ctx.currentTime + duration);
    oscillator.connect(gainNode);
    gainNode.connect(ctx.destination);
    oscillator.start(ctx.currentTime);
    oscillator.stop(ctx.currentTime + duration);
  } catch (e) {}
}

function stopAudio() {
  try {
    if (oscillator) { oscillator.stop(); oscillator.disconnect(); oscillator = null; }
    if (gainNode)   { gainNode.disconnect(); gainNode = null; }
  } catch (e) {}
}

function playChime() {
  try {
    initAudio();
    const ctx = audioCtx;
    [523.25, 659.25, 783.99].forEach((freq, i) => {
      const osc  = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.type = 'sine';
      osc.frequency.value = freq;
      gain.gain.setValueAtTime(0, ctx.currentTime + i * 0.2);
      gain.gain.linearRampToValueAtTime(0.12, ctx.currentTime + i * 0.2 + 0.1);
      gain.gain.linearRampToValueAtTime(0, ctx.currentTime + i * 0.2 + 1.5);
      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.start(ctx.currentTime + i * 0.2);
      osc.stop(ctx.currentTime + i * 0.2 + 1.5);
    });
  } catch (e) {}
}

onBeforeUnmount(() => {
  clearInterval(sessionTimer);
  clearInterval(breathTimer);
  stopAudio();
  if (audioCtx) { audioCtx.close().catch(() => {}); audioCtx = null; }
});
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
