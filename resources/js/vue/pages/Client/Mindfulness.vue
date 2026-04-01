<template>
  <div class="space-y-8">

    <!-- Header -->
    <div>
      <h1 class="font-display text-3xl tracking-wide text-wc-text">MINDFULNESS</h1>
      <p class="mt-1 text-sm text-wc-text-secondary">Respiración guiada y sesiones de bienestar mental para optimizar tu rendimiento.</p>
    </div>

    <!-- ═══════════════════════════════════════════════════════════
         SECTION 1: Guided Sessions
         ═══════════════════════════════════════════════════════════ -->
    <section aria-label="Sesiones guiadas">
      <h2 class="mb-4 font-display text-xl tracking-wide text-wc-text">SESIONES GUIADAS</h2>

      <Transition name="fade" mode="out-in">

        <!-- Active Session Timer -->
        <div v-if="activeSessionId" key="active" class="rounded-xl border border-wc-accent/30 bg-wc-bg-tertiary p-8">
          <div class="mb-6 flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold uppercase tracking-widest text-wc-accent">Sesión activa</p>
              <h3 class="mt-1 font-display text-2xl tracking-wide text-wc-text">
                {{ currentSession?.emoji }} {{ currentSession?.title }}
              </h3>
            </div>
            <button
              @click="endSession"
              class="rounded-lg border border-wc-border px-4 py-2 text-sm font-medium text-wc-text-secondary transition-colors hover:border-red-500/50 hover:text-red-400 focus:outline-none focus:ring-2 focus:ring-wc-accent"
            >
              Terminar sesión
            </button>
          </div>

          <!-- Circular Timer -->
          <div class="flex flex-col items-center gap-6">
            <div class="relative flex h-52 w-52 items-center justify-center">
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
                <span class="font-data text-5xl font-bold tabular-nums text-wc-text">{{ formatSessionTime(sessionRemaining) }}</span>
                <p class="mt-1 text-xs font-medium uppercase tracking-widest text-wc-text-secondary">restante</p>
              </div>
            </div>

            <!-- Completed indicator -->
            <Transition name="fade">
              <div v-if="sessionRemaining === 0 && !sessionRunning" class="rounded-xl border border-green-500/30 bg-green-500/10 px-6 py-3 text-center">
                <p class="text-sm font-semibold text-green-400">Sesión completada</p>
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
          <div class="mt-6" role="progressbar" :aria-valuenow="Math.round(sessionProgress)" aria-valuemin="0" aria-valuemax="100">
            <div class="h-1.5 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
              <div class="h-full rounded-full bg-wc-accent transition-all duration-1000" :style="'width:' + sessionProgress + '%'"></div>
            </div>
            <div class="mt-1.5 flex justify-between text-xs text-wc-text-secondary">
              <span>Inicio</span>
              <span>{{ Math.round(sessionProgress) }}%</span>
              <span>Fin</span>
            </div>
          </div>
        </div>

        <!-- Session Cards Grid -->
        <div v-else key="cards" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <button
            v-for="session in SESSIONS"
            :key="session.id"
            @click="startSession(session.id)"
            aria-label="'Iniciar sesión: ' + session.title"
            class="group relative flex flex-col items-start rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 text-left transition-all hover:border-wc-accent/50 hover:bg-wc-bg-secondary focus:outline-none focus:ring-2 focus:ring-wc-accent active:scale-[0.98]"
          >
            <span class="mb-3 text-3xl" aria-hidden="true">{{ session.emoji }}</span>
            <h3 class="font-display text-lg tracking-wide text-wc-text">{{ session.title.toUpperCase() }}</h3>
            <p class="mt-1.5 text-xs leading-relaxed text-wc-text-secondary">{{ session.description }}</p>
            <div class="mt-4 flex w-full items-center justify-between">
              <span class="inline-flex items-center gap-1 rounded-full border border-wc-border bg-wc-bg-secondary px-2.5 py-0.5 text-xs font-medium text-wc-text-secondary">
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                {{ session.duration }}
              </span>
              <span class="text-xs font-semibold text-wc-accent">{{ session.benefit }}</span>
            </div>
            <div class="absolute right-4 top-4 opacity-0 transition-opacity group-hover:opacity-100" aria-hidden="true">
              <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
              </svg>
            </div>
          </button>
        </div>

      </Transition>
    </section>

    <!-- ═══════════════════════════════════════════════════════════
         SECTION 2: Breathing Exercises
         ═══════════════════════════════════════════════════════════ -->
    <section aria-label="Ejercicios de respiración">
      <h2 class="mb-4 font-display text-xl tracking-wide text-wc-text">EJERCICIOS DE RESPIRACIÓN</h2>

      <!-- Technique Tabs -->
      <div class="flex flex-wrap gap-2" role="tablist" aria-label="Técnicas de respiración">
        <button
          v-for="t in TECHNIQUE_LIST"
          :key="t.id"
          role="tab"
          :aria-selected="technique === t.id"
          @click="selectTechnique(t.id)"
          :class="technique === t.id ? 'bg-wc-accent text-white' : 'bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-text'"
          class="rounded-lg border border-wc-border px-4 py-2 text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-wc-accent"
        >
          {{ t.label }}
        </button>
      </div>

      <div class="mx-auto mt-6 max-w-md">

        <!-- Breathing Circle -->
        <div class="relative mx-auto flex h-72 w-72 items-center justify-center sm:h-80 sm:w-80">
          <!-- Ambient glow -->
          <div
            class="absolute inset-0 rounded-full transition-all duration-1000"
            :class="running ? (phase === 'inhala' ? 'shadow-[0_0_60px_rgba(220,38,38,0.15)]' : phase === 'exhala' ? 'shadow-[0_0_60px_rgba(59,130,246,0.15)]' : 'shadow-[0_0_60px_rgba(168,85,247,0.15)]') : ''"
          ></div>

          <!-- Dashed background ring -->
          <svg class="absolute inset-0" viewBox="0 0 200 200" aria-hidden="true">
            <circle cx="100" cy="100" r="90" fill="none" stroke="currentColor" stroke-width="2" class="text-wc-border" stroke-dasharray="4 4" />
          </svg>

          <!-- Animated breathing circle (scale controlled by CSS transition via :style) -->
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
                  stop-opacity="0.15"
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
            <p v-if="!running && !paused" class="text-xs text-wc-text-secondary">Presiona iniciar</p>
          </div>
        </div>

        <!-- Config panel (visible when stopped) -->
        <div v-if="!running && !paused" class="mt-6 space-y-4 rounded-xl border border-wc-border bg-wc-bg-tertiary p-6">
          <div>
            <h3 class="text-sm font-semibold text-wc-text">{{ currentTechnique.label }}</h3>
            <p class="mt-1 text-xs text-wc-text-secondary">{{ currentTechnique.description }}</p>
          </div>
          <div class="flex flex-wrap items-center gap-2 text-xs">
            <template v-for="(p, i) in currentTechnique.phases" :key="i">
              <div class="flex items-center gap-1">
                <span class="inline-block h-2 w-2 rounded-full" :class="p.type === 'inhala' ? 'bg-wc-accent' : p.type === 'exhala' ? 'bg-blue-500' : 'bg-purple-500'"></span>
                <span class="text-wc-text-secondary">{{ p.label }} {{ p.seconds }}s</span>
                <span v-if="i < currentTechnique.phases.length - 1" class="mx-1 text-wc-text-secondary">→</span>
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
        <div class="mt-6 flex items-center justify-center gap-4">
          <button
            v-if="!running && !paused"
            @click="startBreathing"
            class="rounded-lg bg-wc-accent px-8 py-3 text-sm font-semibold text-white transition-colors hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-wc-accent"
          >
            Iniciar
          </button>
          <button
            v-if="running"
            @click="pauseBreathing"
            class="rounded-lg border border-yellow-500 px-8 py-3 text-sm font-semibold text-yellow-400 transition-colors hover:bg-yellow-500/10 focus:outline-none focus:ring-2 focus:ring-wc-accent"
          >
            Pausar
          </button>
          <button
            v-if="paused"
            @click="resumeBreathing"
            class="rounded-lg bg-wc-accent px-8 py-3 text-sm font-semibold text-white transition-colors hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-wc-accent"
          >
            Reanudar
          </button>
          <button
            v-if="running || paused"
            @click="stopBreathing"
            class="rounded-lg border border-wc-border px-8 py-3 text-sm font-semibold text-wc-text-secondary transition-colors hover:text-wc-text focus:outline-none focus:ring-2 focus:ring-wc-accent"
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
            <p class="mt-2 text-xs text-wc-text-secondary">Técnica del Dr. Andrew Weil. Inhala 4s, mantén 7s, exhala 8s. Activa el sistema nervioso parasimpático para calma profunda.</p>
          </div>
          <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
            <div class="flex items-center gap-2">
              <span class="inline-block h-2.5 w-2.5 rounded-full bg-blue-500"></span>
              <p class="text-xs font-semibold text-blue-400">Box Breathing</p>
            </div>
            <p class="mt-2 text-xs text-wc-text-secondary">Usada por Navy SEALs. 4 fases iguales de 4s. Equilibra el sistema nervioso autónomo y mejora el enfoque bajo presión.</p>
          </div>
          <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
            <div class="flex items-center gap-2">
              <span class="inline-block h-2.5 w-2.5 rounded-full bg-purple-500"></span>
              <p class="text-xs font-semibold text-purple-400">Coherente</p>
            </div>
            <p class="mt-2 text-xs text-wc-text-secondary">Frecuencia de resonancia: 5.5 respiraciones/min. Sincroniza corazón, cerebro y sistema nervioso. Ideal pre-entrenamiento.</p>
          </div>
        </div>

      </div>
    </section>

  </div>
</template>

<script setup>
import { ref, computed, onBeforeUnmount } from 'vue';

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
