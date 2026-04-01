<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useApi } from '../../composables/useApi';
import ClientLayout from '../../layouts/ClientLayout.vue';

const api = useApi();
const route = useRoute();
const router = useRouter();

// State
const loading = ref(true);
const error = ref(null);
const days = ref([]);
const currentDayIndex = ref(0);
const exercises = ref([]);
const setData = ref({});
const workoutStarted = ref(false);
const sessionId = ref(null);
const weightUnit = ref(localStorage.getItem('wc_weight_unit') || 'kg');

// Timer
const elapsed = ref(0);
let timerInterval = null;

// Rest timer
const restSeconds = ref(0);
const restTotal = ref(0);
const showRestTimer = ref(false);
let restInterval = null;

// Current exercise
const exercises_ = computed(() => exercises.value);

const currentExercises = computed(() => {
  if (!days.value.length) return [];
  const day = days.value[currentDayIndex.value];
  if (!day) return [];
  return day.ejercicios || day.exercises || [];
});

const completedSetsCount = computed(() => {
  let count = 0;
  for (const key in setData.value) {
    const sets = setData.value[key] || [];
    sets.forEach(s => { if (s.completed) count++; });
  }
  return count;
});

const totalSetsCount = computed(() => {
  let count = 0;
  exercises.value.forEach(ex => {
    count += parseInt(ex.series || ex.sets || 3);
  });
  return count;
});

const progressPct = computed(() => {
  if (totalSetsCount.value === 0) return 0;
  return Math.round((completedSetsCount.value / totalSetsCount.value) * 100);
});

const completedExercisesCount = computed(() => {
  let count = 0;
  exercises.value.forEach((ex, idx) => {
    const totalSets = parseInt(ex.series || ex.sets || 3);
    const exSets = setData.value[idx] || [];
    const done = exSets.filter(s => s.completed).length;
    if (done >= totalSets) count++;
  });
  return count;
});

// Elapsed display
const elapsedDisplay = computed(() => {
  const h = Math.floor(elapsed.value / 3600);
  const m = Math.floor((elapsed.value % 3600) / 60);
  const s = elapsed.value % 60;
  const mStr = String(m).padStart(2, '0');
  const sStr = String(s).padStart(2, '0');
  return (h > 0 ? h + ':' : '') + mStr + ':' + sStr;
});

// Block type helpers
function getBlockType(exercise) {
  return (exercise.bloque || exercise.block_type || 'normal').toLowerCase();
}

function isInBlock(exercise) {
  const bt = getBlockType(exercise);
  return ['superset', 'circuito'].includes(bt);
}

function isFirstInBlock(exIndex) {
  const ex = exercises.value[exIndex];
  if (!ex) return false;
  const bt = getBlockType(ex);
  if (!['superset', 'circuito'].includes(bt)) return false;
  if (exIndex === 0) return true;
  const prev = exercises.value[exIndex - 1];
  const prevBt = getBlockType(prev);
  const prevGroupId = prev.grupo_id || prev.group_id;
  const currGroupId = ex.grupo_id || ex.group_id;
  return prevBt !== bt || prevGroupId !== currGroupId;
}

// Normalized exercise fields
function exName(ex) {
  return ex.nombre || ex.name || ex.ejercicio || 'Ejercicio';
}
function exSeries(ex) { return ex.series || ex.sets || null; }
function exReps(ex) { return ex.repeticiones || ex.reps || null; }
function exDescanso(ex) { return ex.descanso || ex.rest || ex.rest_seconds || null; }
function exRir(ex) { return ex.rir !== undefined ? ex.rir : null; }
function exNotas(ex) { return ex.notas || ex.notes || null; }
function exMuscle(ex) { return ex.musculo || ex.muscle_group || null; }

function rirClass(rir) {
  if (rir === null || rir === undefined) return '';
  if (rir >= 3) return 'bg-emerald-500/10 text-emerald-400';
  if (rir >= 2) return 'bg-amber-500/10 text-amber-400';
  return 'bg-red-500/10 text-red-400';
}

// Set management
function getSetRows(exIndex) {
  const ex = exercises.value[exIndex];
  const total = parseInt(exSeries(ex) || 3);
  if (!setData.value[exIndex]) {
    setData.value[exIndex] = [];
    for (let i = 0; i < total; i++) {
      setData.value[exIndex].push({ weight: '', reps: '', completed: false });
    }
  }
  return setData.value[exIndex];
}

function allSetsComplete(exIndex) {
  const ex = exercises.value[exIndex];
  const total = parseInt(exSeries(ex) || 3);
  const sets = setData.value[exIndex] || [];
  return sets.filter(s => s.completed).length >= total;
}

async function toggleSet(exIndex, setIndex) {
  const sets = getSetRows(exIndex);
  const set = sets[setIndex];
  set.completed = !set.completed;

  if (set.completed && workoutStarted.value) {
    try {
      await api.post('/api/v/client/workout/complete-set', {
        session_id: sessionId.value,
        exercise_index: exIndex,
        set_index: setIndex,
        weight: parseFloat(set.weight) || 0,
        reps: parseInt(set.reps) || 0,
        unit: weightUnit.value,
      });
    } catch (err) {
      // Silently handle
    }

    // Start rest timer
    const ex = exercises.value[exIndex];
    const rest = exDescanso(ex);
    if (rest) {
      const seconds = typeof rest === 'number' ? rest : parseInt(rest) || 60;
      startRestTimer(seconds);
    }
  }
}

function startRestTimer(seconds) {
  clearInterval(restInterval);
  restSeconds.value = seconds;
  restTotal.value = seconds;
  showRestTimer.value = true;
  restInterval = setInterval(() => {
    if (restSeconds.value <= 0) {
      clearInterval(restInterval);
      showRestTimer.value = false;
      beep();
      return;
    }
    restSeconds.value--;
  }, 1000);
}

function clearRestTimer() {
  clearInterval(restInterval);
  showRestTimer.value = false;
  restSeconds.value = 0;
}

function startTimer() {
  elapsed.value = 0;
  timerInterval = setInterval(() => {
    elapsed.value++;
  }, 1000);
}

function stopTimer() {
  clearInterval(timerInterval);
}

// Weight unit
function setWeightUnit(unit) {
  weightUnit.value = unit;
  localStorage.setItem('wc_weight_unit', unit);
}

// Audio
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

// Day switching
function switchDay(index) {
  if (workoutStarted.value && currentDayIndex.value !== index) return;
  currentDayIndex.value = index;
  clearRestTimer();
  loadDayExercises();
}

function loadDayExercises() {
  if (!days.value.length) return;
  const day = days.value[currentDayIndex.value];
  if (!day) return;
  exercises.value = day.ejercicios || day.exercises || [];
  setData.value = {};
  // Initialize sets
  exercises.value.forEach((ex, idx) => {
    getSetRows(idx);
  });
}

// API calls
async function fetchWorkout() {
  loading.value = true;
  error.value = null;
  try {
    const dayParam = route.params.day || '';
    const url = dayParam ? `/api/v/client/workout/${dayParam}` : '/api/v/client/workout/1';
    const response = await api.get(url);
    const d = response.data;
    days.value = d.days || [];
    currentDayIndex.value = d.currentDayIndex || 0;

    // Resume session if exists
    if (d.activeSession) {
      sessionId.value = d.activeSession.id;
      workoutStarted.value = true;
      elapsed.value = d.activeSession.elapsed || 0;
      startTimer();
      if (d.activeSession.setData) {
        setData.value = d.activeSession.setData;
      }
    }

    loadDayExercises();
  } catch (err) {
    error.value = err.response?.data?.message || 'Error al cargar el entrenamiento';
  } finally {
    loading.value = false;
  }
}

async function startWorkout() {
  workoutStarted.value = true;
  startTimer();
  try {
    const response = await api.post('/api/v/client/workout/start', {
      day_index: currentDayIndex.value,
    });
    sessionId.value = response.data.session_id || null;
  } catch (err) {
    // Keep going even if API fails
  }
  window.scrollTo(0, 0);
}

async function finishWorkout() {
  stopTimer();
  clearRestTimer();
  try {
    const response = await api.post('/api/v/client/workout/finish', {
      session_id: sessionId.value,
      elapsed: elapsed.value,
      set_data: setData.value,
    });
    const sid = response.data.session_id || sessionId.value;
    router.push({ name: 'client-workout-summary', params: { sessionId: sid } });
  } catch (err) {
    // Navigate even on error
    router.push({ name: 'client-workout-summary', params: { sessionId: sessionId.value || 'latest' } });
  }
}

onMounted(() => {
  fetchWorkout();
});

onUnmounted(() => {
  stopTimer();
  clearRestTimer();
});
</script>

<template>
  <ClientLayout>
    <div class="min-h-screen" :class="{ 'pb-[280px]': workoutStarted }">

      <!-- Loading -->
      <template v-if="loading">
        <div class="space-y-4 animate-pulse p-4">
          <div class="h-16 rounded-xl bg-wc-bg-tertiary"></div>
          <div class="h-24 rounded-xl bg-wc-bg-tertiary"></div>
          <div class="h-48 rounded-xl bg-wc-bg-tertiary"></div>
          <div class="h-48 rounded-xl bg-wc-bg-tertiary"></div>
        </div>
      </template>

      <!-- Error -->
      <div v-else-if="error" class="flex min-h-[60vh] items-center justify-center px-4">
        <div class="w-full max-w-sm rounded-2xl border border-red-500/30 bg-red-500/10 p-8 text-center">
          <svg class="mx-auto h-10 w-10 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
          <p class="mt-3 text-sm text-red-400">{{ error }}</p>
          <button @click="fetchWorkout" class="mt-4 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors">Reintentar</button>
        </div>
      </div>

      <!-- Empty state -->
      <div v-else-if="!days.length" class="flex min-h-[60vh] items-center justify-center px-4">
        <div class="w-full max-w-sm rounded-2xl border border-wc-border bg-wc-bg-tertiary p-10 text-center">
          <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-wc-bg-secondary">
            <svg class="h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" /></svg>
          </div>
          <h2 class="mt-5 font-display text-2xl tracking-wide text-wc-text">TU PLAN VIENE EN CAMINO</h2>
          <p class="mt-2 text-sm text-wc-text-secondary">Tu coach esta preparando tu plan de entrenamiento personalizado.</p>
        </div>
      </div>

      <!-- Main workout content -->
      <template v-else>
        <!-- Header: Day selector -->
        <div class="sticky top-0 z-30 bg-wc-bg/95 backdrop-blur-md border-b border-wc-border">
          <div class="px-4 py-3">
            <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none -mx-1 px-1">
              <button
                v-for="(day, index) in days"
                :key="index"
                @click="switchDay(index)"
                :class="[
                  'shrink-0 flex flex-col items-center gap-0.5 rounded-xl px-4 py-2.5 transition-all',
                  currentDayIndex === index
                    ? 'bg-wc-accent text-white shadow-lg shadow-wc-accent/25'
                    : workoutStarted
                      ? 'bg-wc-bg-tertiary border border-wc-border text-wc-text-tertiary opacity-50 cursor-not-allowed'
                      : 'bg-wc-bg-tertiary border border-wc-border text-wc-text-secondary hover:text-wc-text hover:border-wc-text-tertiary'
                ]"
                :title="currentDayIndex !== index && workoutStarted ? 'No puedes cambiar de dia con un entrenamiento en curso' : ''"
              >
                <span class="font-display text-base tracking-wider leading-none">DIA {{ index + 1 }}</span>
                <span v-if="day.grupo_muscular || day.muscle_group || day.nombre || day.name || day.dia" class="text-[9px] font-medium uppercase tracking-wider leading-none opacity-75 max-w-[80px] truncate">
                  {{ day.grupo_muscular || day.muscle_group || day.nombre || day.name || day.dia }}
                </span>
              </button>
            </div>

            <!-- Active session timer -->
            <div v-if="workoutStarted" class="mt-2 flex items-center justify-between rounded-lg bg-wc-accent/10 border border-wc-accent/20 px-3 py-2">
              <div class="flex items-center gap-2">
                <span class="relative flex h-2 w-2">
                  <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-wc-accent opacity-75"></span>
                  <span class="relative inline-flex h-2 w-2 rounded-full bg-wc-accent"></span>
                </span>
                <span class="text-xs font-semibold uppercase tracking-wider text-wc-accent">En curso</span>
              </div>
              <span class="font-data text-lg font-bold text-wc-accent tabular-nums">{{ elapsedDisplay }}</span>
            </div>
          </div>
        </div>

        <!-- Rest timer floating -->
        <Transition
          enter-active-class="transition ease-out duration-200"
          enter-from-class="opacity-0 translate-y-4"
          enter-to-class="opacity-100 translate-y-0"
          leave-active-class="transition ease-in duration-150"
          leave-from-class="opacity-100 translate-y-0"
          leave-to-class="opacity-0 translate-y-4"
        >
          <div v-if="showRestTimer" class="fixed bottom-24 left-1/2 -translate-x-1/2 z-40 rounded-2xl border border-wc-accent/30 bg-wc-bg/95 backdrop-blur-xl px-6 py-4 shadow-2xl">
            <div class="flex items-center gap-4">
              <div class="text-center">
                <p class="text-[10px] font-bold uppercase tracking-wider text-wc-text-tertiary">Descanso</p>
                <p class="font-data text-3xl font-bold tabular-nums text-wc-accent">
                  {{ String(Math.floor(restSeconds / 60)).padStart(2, '0') }}:{{ String(restSeconds % 60).padStart(2, '0') }}
                </p>
              </div>
              <button @click="clearRestTimer" class="rounded-lg border border-wc-border px-3 py-1.5 text-xs font-medium text-wc-text-secondary hover:text-wc-text transition-colors">
                Saltar
              </button>
            </div>
          </div>
        </Transition>

        <div class="mt-4 space-y-4 px-4">
          <!-- PRE-WORKOUT STATE -->
          <div v-if="!workoutStarted">
            <!-- Progress summary -->
            <div class="flex items-center justify-between rounded-xl border border-wc-border bg-wc-bg-tertiary px-4 py-3">
              <div class="flex items-center gap-2">
                <svg class="h-4 w-4 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12" /></svg>
                <span class="text-sm text-wc-text-secondary">
                  <span class="font-data font-semibold text-wc-text">{{ exercises.length }}</span> ejercicios
                  <span class="text-wc-text-tertiary mx-1">&middot;</span>
                  <span class="text-wc-text-tertiary">~{{ Math.max(exercises.length * 8, 20) }} min estimado</span>
                </span>
              </div>
            </div>

            <!-- Warmup -->
            <div v-if="days[currentDayIndex] && (days[currentDayIndex].calentamiento || days[currentDayIndex].warmup)" class="overflow-hidden rounded-2xl border border-amber-500/25 bg-gradient-to-br from-amber-500/[0.08] via-amber-400/[0.04] to-transparent">
              <div class="flex items-start gap-3 px-4 py-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/15">
                  <svg class="h-5 w-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" /></svg>
                </div>
                <div>
                  <p class="font-display text-sm tracking-wide text-amber-400">CALENTAMIENTO</p>
                  <p class="mt-1 text-sm leading-relaxed text-wc-text-secondary">{{ days[currentDayIndex].calentamiento || days[currentDayIndex].warmup }}</p>
                </div>
              </div>
            </div>

            <!-- Exercise preview cards -->
            <template v-for="(exercise, exIndex) in exercises" :key="exIndex">
              <!-- Block label -->
              <div v-if="isFirstInBlock(exIndex)" class="flex items-center gap-3">
                <span class="rounded-full border border-wc-accent/30 bg-wc-accent/10 px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-wc-accent">
                  {{ getBlockType(exercise) === 'superset' ? 'SUPERSET' : 'CIRCUITO' }}
                </span>
                <div class="h-px flex-1 bg-wc-accent/15"></div>
              </div>

              <div :class="['overflow-hidden rounded-2xl border bg-wc-bg-tertiary transition-all', isInBlock(exercise) ? 'ml-3 border-l-[3px] border-l-wc-accent border-wc-border/70' : 'border-wc-border']">
                <div class="flex items-stretch">
                  <div class="relative w-20 shrink-0 overflow-hidden bg-wc-bg-secondary">
                    <div class="flex h-full w-full min-h-[80px] flex-col items-center justify-center bg-gradient-to-b from-wc-bg-secondary to-wc-bg">
                      <svg class="h-7 w-7 text-wc-text-tertiary/40" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" /></svg>
                    </div>
                    <div class="absolute bottom-2 left-2 flex h-6 w-6 items-center justify-center rounded-lg bg-wc-bg/80 backdrop-blur-sm border border-wc-border/30">
                      <span class="font-data text-xs font-black leading-none text-wc-accent">{{ exIndex + 1 }}</span>
                    </div>
                  </div>
                  <div class="min-w-0 flex-1 p-3">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                      <h3 class="font-display text-xl tracking-wide leading-tight text-wc-text uppercase">{{ exName(exercise) }}</h3>
                      <span v-if="exMuscle(exercise)" class="shrink-0 rounded-full bg-wc-bg-secondary px-2.5 py-0.5 text-[10px] font-medium text-wc-text-tertiary">{{ exMuscle(exercise) }}</span>
                    </div>
                    <p v-if="exNotas(exercise)" class="mt-1 text-xs leading-relaxed text-wc-text-tertiary">{{ exNotas(exercise) }}</p>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                      <span v-if="exSeries(exercise) || exReps(exercise)" class="inline-flex items-center gap-1 rounded-lg bg-wc-accent/10 px-3 py-1.5">
                        <span class="font-data text-sm font-black text-wc-accent">{{ exSeries(exercise) || '?' }}</span>
                        <span class="text-xs text-wc-accent/60">&times;</span>
                        <span class="font-data text-sm font-black text-wc-accent">{{ exReps(exercise) || '?' }}</span>
                        <span class="text-[10px] text-wc-accent/60 ml-0.5">reps</span>
                      </span>
                      <span v-if="exDescanso(exercise)" class="inline-flex items-center gap-1.5 rounded-lg bg-wc-bg-secondary px-2.5 py-1.5 text-xs text-wc-text-secondary">
                        <svg class="h-3 w-3 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        {{ exDescanso(exercise) }}
                      </span>
                      <span v-if="exRir(exercise) !== null" class="rounded-lg px-2.5 py-1.5 text-[11px] font-bold" :class="rirClass(exRir(exercise))">RIR {{ exRir(exercise) }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </template>

            <!-- Weight unit selector -->
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-semibold text-wc-text">Unidad de peso</p>
                  <p class="mt-0.5 text-xs text-wc-text-tertiary">Selecciona la unidad que usa tu gimnasio</p>
                </div>
                <div class="flex rounded-lg border border-wc-border bg-wc-bg-secondary p-0.5">
                  <button
                    @click="setWeightUnit('kg')"
                    :class="weightUnit === 'kg' ? 'bg-wc-accent text-white shadow-sm' : 'text-wc-text-tertiary hover:text-wc-text'"
                    class="rounded-md px-4 py-1.5 text-sm font-bold transition-all"
                  >KG</button>
                  <button
                    @click="setWeightUnit('lbs')"
                    :class="weightUnit === 'lbs' ? 'bg-wc-accent text-white shadow-sm' : 'text-wc-text-tertiary hover:text-wc-text'"
                    class="rounded-md px-4 py-1.5 text-sm font-bold transition-all"
                  >LBS</button>
                </div>
              </div>
            </div>

            <!-- START WORKOUT CTA -->
            <div class="pt-2 pb-4">
              <button
                @click="startWorkout"
                class="w-full rounded-2xl bg-wc-accent py-4 text-center shadow-lg shadow-wc-accent/20 hover:bg-red-700 transition-colors"
              >
                <span class="font-display text-xl tracking-widest text-white">INICIAR ENTRENAMIENTO</span>
              </button>
            </div>
          </div>

          <!-- ACTIVE WORKOUT STATE -->
          <div v-if="workoutStarted">
            <!-- Progress bar -->
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-3">
              <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-medium text-wc-text-secondary">
                  <span class="font-data font-semibold text-wc-text">{{ completedExercisesCount }}</span>/{{ exercises.length }} ejercicios completados
                </span>
                <span class="font-data text-xs font-bold text-wc-accent">{{ progressPct }}%</span>
              </div>
              <div class="h-1.5 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
                <div class="h-full rounded-full bg-gradient-to-r from-wc-accent to-red-400 transition-all duration-700 ease-out" :style="{ width: progressPct + '%' }"></div>
              </div>
            </div>

            <!-- Active exercise cards -->
            <template v-for="(exercise, exIndex) in exercises" :key="'active-' + exIndex">
              <!-- Block label -->
              <div v-if="isFirstInBlock(exIndex) && isInBlock(exercise)" class="flex items-center gap-2 mt-3">
                <span class="rounded-full bg-wc-accent/15 px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-wc-accent">
                  {{ getBlockType(exercise) === 'superset' ? 'SUPERSET' : 'CIRCUITO' }}
                </span>
                <div class="h-px flex-1 bg-wc-accent/15"></div>
              </div>

              <div
                :id="'exercise-' + exIndex"
                :class="[
                  'rounded-2xl border bg-wc-bg-tertiary overflow-hidden transition-all',
                  isInBlock(exercise) ? 'ml-2' : '',
                  allSetsComplete(exIndex) ? 'border-l-[3px] border-l-emerald-500 border-emerald-500/20' : 'border-wc-border'
                ]"
              >
                <!-- Exercise header -->
                <div class="p-4 pb-3">
                  <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                      <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-wc-bg-secondary text-xs font-bold text-wc-text-tertiary font-data">{{ exIndex + 1 }}</span>
                        <h3 class="font-display text-lg tracking-wide text-wc-text uppercase truncate">{{ exName(exercise) }}</h3>
                      </div>
                      <div class="mt-2 flex flex-wrap gap-2">
                        <span v-if="exSeries(exercise) || exReps(exercise)" class="rounded-lg bg-wc-accent/10 px-2.5 py-1 text-xs font-bold text-wc-accent">
                          {{ exSeries(exercise) || '?' }} x {{ exReps(exercise) || '?' }}
                        </span>
                        <span v-if="exDescanso(exercise)" class="rounded-lg bg-wc-bg-secondary px-2.5 py-1 text-xs text-wc-text-tertiary">{{ exDescanso(exercise) }}s descanso</span>
                        <span v-if="exRir(exercise) !== null" class="rounded-lg px-2.5 py-1 text-xs font-bold" :class="rirClass(exRir(exercise))">RIR {{ exRir(exercise) }}</span>
                      </div>
                    </div>
                    <div v-if="allSetsComplete(exIndex)" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-500">
                      <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                    </div>
                  </div>
                </div>

                <!-- Sets table -->
                <div class="border-t border-wc-border">
                  <div class="grid grid-cols-[auto_1fr_1fr_auto] gap-x-3 px-4 py-2 text-[10px] font-bold uppercase tracking-wider text-wc-text-tertiary">
                    <span>Set</span>
                    <span>Peso ({{ weightUnit }})</span>
                    <span>Reps</span>
                    <span class="text-center">OK</span>
                  </div>
                  <div v-for="(set, sIdx) in getSetRows(exIndex)" :key="sIdx" class="grid grid-cols-[auto_1fr_1fr_auto] items-center gap-x-3 border-t border-wc-border/50 px-4 py-2">
                    <span class="font-data text-sm font-bold text-wc-text-tertiary w-6 text-center">{{ sIdx + 1 }}</span>
                    <input
                      v-model="set.weight"
                      type="number"
                      step="0.5"
                      min="0"
                      placeholder="0"
                      class="w-full rounded-lg border border-wc-border bg-wc-bg px-2.5 py-1.5 font-data text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none"
                      :disabled="set.completed"
                    />
                    <input
                      v-model="set.reps"
                      type="number"
                      min="0"
                      :placeholder="exReps(exercise) || '0'"
                      class="w-full rounded-lg border border-wc-border bg-wc-bg px-2.5 py-1.5 font-data text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none"
                      :disabled="set.completed"
                    />
                    <button
                      @click="toggleSet(exIndex, sIdx)"
                      :class="[
                        'flex h-8 w-8 items-center justify-center rounded-lg transition-all',
                        set.completed ? 'bg-emerald-500 text-white' : 'border border-wc-border bg-wc-bg-secondary text-wc-text-tertiary hover:border-wc-accent'
                      ]"
                      :aria-label="set.completed ? 'Desmarcar set ' + (sIdx + 1) : 'Completar set ' + (sIdx + 1)"
                    >
                      <svg v-if="set.completed" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                      <span v-else class="text-xs font-bold">{{ sIdx + 1 }}</span>
                    </button>
                  </div>
                </div>
              </div>
            </template>

            <!-- Finish workout button -->
            <div class="pt-4 pb-8">
              <button
                @click="finishWorkout"
                class="w-full rounded-2xl bg-emerald-600 py-4 text-center shadow-lg shadow-emerald-600/20 hover:bg-emerald-700 transition-colors"
              >
                <span class="font-display text-xl tracking-widest text-white">FINALIZAR ENTRENAMIENTO</span>
              </button>
            </div>
          </div>
        </div>
      </template>
    </div>
  </ClientLayout>
</template>
