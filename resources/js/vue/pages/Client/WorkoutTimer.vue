<script setup>
import { ref, computed, onUnmounted } from 'vue';
import ClientLayout from '../../layouts/ClientLayout.vue';

// Timer modes
const modes = [
  { id: 'timer', label: 'Timer' },
  { id: 'tabata', label: 'Tabata' },
  { id: 'amrap', label: 'AMRAP' },
  { id: 'emom', label: 'EMOM' },
];

const mode = ref('timer');
const config = ref({ minutes: 5, rounds: 8, workSec: 40 });
const running = ref(false);
const paused = ref(false);
const seconds = ref(0);
const totalSeconds = ref(0);
const currentRound = ref(1);
const totalRounds = ref(1);
const isWork = ref(true);
const phase = ref('');
const isFullscreen = ref(false);
let interval = null;

// SVG circle
const circumference = 2 * Math.PI * 90;
const dashOffset = computed(() => {
  if (totalSeconds.value === 0) return 0;
  return circumference * (1 - seconds.value / totalSeconds.value);
});

// Display
const display = computed(() => {
  const m = Math.floor(seconds.value / 60);
  const s = seconds.value % 60;
  return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
});

// Tabata total time
const tabataTotal = computed(() => {
  const t = config.value.rounds * 30;
  return t + ' segundos (' + (t / 60).toFixed(1) + ' min)';
});

// EMOM rest
const emomRest = computed(() => {
  return (60 - config.value.workSec) + 's por ronda \u00B7 ' + config.value.minutes + ' rondas';
});

function selectMode(m) {
  stop();
  mode.value = m;
}

function start() {
  if (mode.value === 'timer') {
    totalSeconds.value = config.value.minutes * 60;
    seconds.value = totalSeconds.value;
    totalRounds.value = 1;
    currentRound.value = 1;
    phase.value = 'Timer';
    isWork.value = true;
  } else if (mode.value === 'tabata') {
    totalRounds.value = config.value.rounds;
    currentRound.value = 1;
    isWork.value = true;
    seconds.value = 20;
    totalSeconds.value = 20;
    phase.value = 'TRABAJO';
  } else if (mode.value === 'amrap') {
    totalSeconds.value = config.value.minutes * 60;
    seconds.value = totalSeconds.value;
    totalRounds.value = 1;
    currentRound.value = 1;
    phase.value = 'AMRAP';
    isWork.value = true;
  } else if (mode.value === 'emom') {
    totalRounds.value = config.value.minutes;
    currentRound.value = 1;
    isWork.value = true;
    seconds.value = config.value.workSec;
    totalSeconds.value = config.value.workSec;
    phase.value = 'TRABAJO';
  }
  running.value = true;
  paused.value = false;
  tick();
}

function tick() {
  interval = setInterval(() => {
    if (seconds.value <= 0) {
      nextPhase();
      return;
    }
    seconds.value--;
  }, 1000);
}

function nextPhase() {
  if (mode.value === 'timer' || mode.value === 'amrap') {
    beep();
    stop();
    return;
  }
  if (mode.value === 'tabata') {
    if (isWork.value) {
      isWork.value = false;
      phase.value = 'DESCANSO';
      seconds.value = 10;
      totalSeconds.value = 10;
      beep();
    } else {
      if (currentRound.value >= totalRounds.value) {
        beep();
        stop();
        return;
      }
      currentRound.value++;
      isWork.value = true;
      phase.value = 'TRABAJO';
      seconds.value = 20;
      totalSeconds.value = 20;
      beep();
    }
  }
  if (mode.value === 'emom') {
    if (isWork.value) {
      isWork.value = false;
      phase.value = 'DESCANSO';
      const rest = 60 - config.value.workSec;
      seconds.value = rest;
      totalSeconds.value = rest;
      beep();
    } else {
      if (currentRound.value >= totalRounds.value) {
        beep();
        stop();
        return;
      }
      currentRound.value++;
      isWork.value = true;
      phase.value = 'TRABAJO';
      seconds.value = config.value.workSec;
      totalSeconds.value = config.value.workSec;
      beep();
    }
  }
}

function pause() {
  clearInterval(interval);
  running.value = false;
  paused.value = true;
}

function resume() {
  running.value = true;
  paused.value = false;
  tick();
}

function stop() {
  clearInterval(interval);
  running.value = false;
  paused.value = false;
  seconds.value = 0;
  totalSeconds.value = 0;
  phase.value = '';
  currentRound.value = 1;
}

function beep() {
  try {
    const ctx = new (window.AudioContext || window.webkitAudioContext)();
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.connect(gain);
    gain.connect(ctx.destination);
    osc.frequency.value = 880;
    gain.gain.value = 0.3;
    osc.start();
    osc.stop(ctx.currentTime + 0.15);
  } catch (e) { /* silent */ }
}

function toggleFullscreen() {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen?.();
    isFullscreen.value = true;
  } else {
    document.exitFullscreen?.();
    isFullscreen.value = false;
  }
}

onUnmounted(() => {
  clearInterval(interval);
});
</script>

<template>
  <ClientLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="font-display text-3xl tracking-wide text-wc-text">TIMER</h1>
          <p class="mt-1 text-sm text-wc-text-secondary">4 modos de entrenamiento con temporizador visual.</p>
        </div>
        <button
          @click="toggleFullscreen"
          class="flex h-9 w-9 items-center justify-center rounded-lg border border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:text-wc-text transition-colors"
          :aria-label="isFullscreen ? 'Salir de pantalla completa' : 'Pantalla completa'"
        >
          <svg v-if="!isFullscreen" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
          </svg>
          <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 9V4.5M9 9H4.5M9 9 3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5m0-4.5 5.25 5.25" />
          </svg>
        </button>
      </div>

      <!-- Mode Tabs -->
      <div class="flex flex-wrap gap-2">
        <button
          v-for="m in modes"
          :key="m.id"
          @click="selectMode(m.id)"
          :class="mode === m.id ? 'bg-wc-accent text-white' : 'bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-text'"
          class="rounded-lg border border-wc-border px-4 py-2 text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-wc-accent"
        >{{ m.label }}</button>
      </div>

      <div class="mx-auto max-w-md">
        <!-- SVG Ring Timer -->
        <div class="relative mx-auto flex h-64 w-64 items-center justify-center sm:h-80 sm:w-80">
          <svg class="absolute inset-0 -rotate-90" viewBox="0 0 200 200">
            <circle cx="100" cy="100" r="90" fill="none" stroke="currentColor" stroke-width="4" class="text-wc-border" />
            <circle
              cx="100" cy="100" r="90" fill="none" stroke="currentColor" stroke-width="6" stroke-linecap="round"
              class="text-wc-accent transition-all duration-1000"
              :stroke-dasharray="circumference"
              :stroke-dashoffset="dashOffset"
            />
          </svg>
          <div class="text-center">
            <p class="font-data text-5xl font-bold text-wc-text sm:text-6xl">{{ display }}</p>
            <p
              v-if="running || paused"
              class="mt-2 text-sm font-medium"
              :class="isWork ? 'text-wc-accent' : 'text-green-500'"
            >{{ phase }}</p>
            <p v-if="running || paused" class="mt-1 text-xs text-wc-text-tertiary">
              Ronda {{ currentRound }}/{{ totalRounds }}
            </p>
          </div>
        </div>

        <!-- Config (visible when stopped) -->
        <div v-if="!running && !paused" class="mt-6 rounded-xl border border-wc-border bg-wc-bg-tertiary p-6">
          <!-- Timer mode -->
          <div v-if="mode === 'timer'" class="space-y-4">
            <h3 class="text-sm font-semibold text-wc-text">Temporizador Simple</h3>
            <div>
              <label class="block text-xs font-medium text-wc-text-tertiary">Minutos</label>
              <input
                v-model.number="config.minutes"
                type="number" min="1" max="60"
                class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none"
                placeholder="5"
              />
            </div>
          </div>

          <!-- Tabata mode -->
          <div v-else-if="mode === 'tabata'" class="space-y-4">
            <h3 class="text-sm font-semibold text-wc-text">Tabata (20s trabajo / 10s descanso)</h3>
            <div>
              <label class="block text-xs font-medium text-wc-text-tertiary">Rondas</label>
              <input
                v-model.number="config.rounds"
                type="number" min="1" max="20"
                class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none"
                placeholder="8"
              />
            </div>
            <p class="text-xs text-wc-text-tertiary">Total: {{ tabataTotal }}</p>
          </div>

          <!-- AMRAP mode -->
          <div v-else-if="mode === 'amrap'" class="space-y-4">
            <h3 class="text-sm font-semibold text-wc-text">AMRAP (As Many Reps As Possible)</h3>
            <div>
              <label class="block text-xs font-medium text-wc-text-tertiary">Minutos</label>
              <input
                v-model.number="config.minutes"
                type="number" min="1" max="30"
                class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none"
                placeholder="12"
              />
            </div>
          </div>

          <!-- EMOM mode -->
          <div v-else-if="mode === 'emom'" class="space-y-4">
            <h3 class="text-sm font-semibold text-wc-text">EMOM (Every Minute On the Minute)</h3>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-medium text-wc-text-tertiary">Minutos totales</label>
                <input
                  v-model.number="config.minutes"
                  type="number" min="1" max="30"
                  class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none"
                  placeholder="10"
                />
              </div>
              <div>
                <label class="block text-xs font-medium text-wc-text-tertiary">Trabajo (seg)</label>
                <input
                  v-model.number="config.workSec"
                  type="number" min="10" max="50"
                  class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none"
                  placeholder="40"
                />
              </div>
            </div>
            <p class="text-xs text-wc-text-tertiary">Descanso: {{ emomRest }}</p>
          </div>
        </div>

        <!-- Controls -->
        <div class="mt-6 flex items-center justify-center gap-4">
          <button
            v-if="!running && !paused"
            @click="start"
            class="rounded-lg bg-wc-accent px-8 py-3 text-sm font-semibold text-white hover:bg-red-700 transition-colors focus:outline-none focus:ring-2 focus:ring-wc-accent"
          >Iniciar</button>
          <button
            v-if="running"
            @click="pause"
            class="rounded-lg border border-yellow-500 px-8 py-3 text-sm font-semibold text-yellow-500 hover:bg-yellow-500/10 transition-colors"
          >Pausar</button>
          <button
            v-if="paused"
            @click="resume"
            class="rounded-lg bg-wc-accent px-8 py-3 text-sm font-semibold text-white hover:bg-red-700 transition-colors"
          >Reanudar</button>
          <button
            v-if="running || paused"
            @click="stop"
            class="rounded-lg border border-wc-border px-8 py-3 text-sm font-semibold text-wc-text-secondary hover:text-wc-text transition-colors"
          >Detener</button>
        </div>

        <!-- Mode descriptions -->
        <div class="mt-8 grid grid-cols-2 gap-3">
          <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
            <p class="text-xs font-semibold text-wc-accent">Timer</p>
            <p class="mt-1 text-xs text-wc-text-tertiary">Cuenta regresiva simple. Ideal para descansos entre series.</p>
          </div>
          <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
            <p class="text-xs font-semibold text-wc-accent">Tabata</p>
            <p class="mt-1 text-xs text-wc-text-tertiary">20s trabajo / 10s descanso. Protocolo HIIT clasico de 4 minutos.</p>
          </div>
          <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
            <p class="text-xs font-semibold text-wc-accent">AMRAP</p>
            <p class="mt-1 text-xs text-wc-text-tertiary">Maximo reps en tiempo fijo. Mide capacidad de trabajo.</p>
          </div>
          <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
            <p class="text-xs font-semibold text-wc-accent">EMOM</p>
            <p class="mt-1 text-xs text-wc-text-tertiary">Trabajo al inicio de cada minuto. Descanso = lo que sobre.</p>
          </div>
        </div>
      </div>
    </div>
  </ClientLayout>
</template>
