<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useApi } from '../../composables/useApi';
import ClientLayout from '../../layouts/ClientLayout.vue';
import ExerciseMediaModal from '../../components/workout/ExerciseMediaModal.vue';
import { getEmbedUrl } from '../../composables/useExerciseMedia';

const api = useApi();
const route = useRoute();
const router = useRouter();

// ── State ──
const loading = ref(true);
const error = ref(null);
const days = ref([]);
const currentDayIndex = ref(0);
const exercises = ref([]);
const setData = ref({});
const workoutStarted = ref(false);
const sessionId = ref(null);
const weightUnit = ref(localStorage.getItem('wc_weight_unit') || 'kg');
const saving = ref(false);
const abandoning = ref(false);

// Elite plan week support
const hasProgressions = ref(false);
const currentWeek = ref(1);
const totalWeeks = ref(1);

// Tutorial
const showTutorial = ref(false);
const tutorialStep = ref(1);
const TUTORIAL_TOTAL = 3;

// Abandon dialog
const confirmAbandon = ref(false);

// Expanded coach notes (per exercise index)
const expandedNotes = ref({});

// Exercise media modal
const mediaModal = ref({ show: false, exercise: null });
function openMedia(ex) { mediaModal.value = { show: true, exercise: ex }; }
function closeMedia() { mediaModal.value = { show: false, exercise: null }; }

// Toggle inline media in active workout view
const showActiveMedia = ref({});

// ── Timers (module-level, NOT reactive) ──
let timerInterval = null;
let restInterval = null;
let audioCtx = null;

// ── Timer state ──
const elapsed = ref(0);
const restSeconds = ref(0);
const restTotal = ref(0);
const showRestTimer = ref(false);

// ── Computed ──
const currentDay = computed(() => days.value[currentDayIndex.value] || null);
const dayWarmup = computed(() => {
  const d = currentDay.value;
  if (!d) return null;
  return d.calentamiento || d.warmup || null;
});

const completedSetsCount = computed(() => {
  let count = 0;
  for (const key in setData.value) {
    const sets = setData.value[key] || [];
    if (Array.isArray(sets)) {
      sets.forEach(s => { if (s.completed) count++; });
    } else {
      Object.values(sets).forEach(s => { if (s.completed) count++; });
    }
  }
  return count;
});

const totalSetsCount = computed(() => {
  let count = 0;
  exercises.value.forEach(ex => {
    count += parseInt(exSeries(ex) || 3);
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
    const totalSets = parseInt(exSeries(ex) || 3);
    const exSets = setData.value[idx] || [];
    let done = 0;
    if (Array.isArray(exSets)) {
      done = exSets.filter(s => s.completed).length;
    } else {
      done = Object.values(exSets).filter(s => s.completed).length;
    }
    if (done >= totalSets) count++;
  });
  return count;
});

const totalRepsAll = computed(() => {
  let total = 0;
  for (const key in setData.value) {
    const sets = setData.value[key];
    const arr = Array.isArray(sets) ? sets : Object.values(sets);
    arr.forEach(s => {
      if (s.completed) total += parseInt(s.reps) || 0;
    });
  }
  return total;
});

const maxWeightAll = computed(() => {
  let max = 0;
  for (const key in setData.value) {
    const sets = setData.value[key];
    const arr = Array.isArray(sets) ? sets : Object.values(sets);
    arr.forEach(s => {
      if (s.completed) {
        const w = parseFloat(s.weight) || 0;
        if (w > max) max = w;
      }
    });
  }
  return max;
});

const elapsedDisplay = computed(() => {
  const h = Math.floor(elapsed.value / 3600);
  const m = Math.floor((elapsed.value % 3600) / 60);
  const s = elapsed.value % 60;
  const mStr = String(m).padStart(2, '0');
  const sStr = String(s).padStart(2, '0');
  return (h > 0 ? h + ':' : '') + mStr + ':' + sStr;
});

const restDisplay = computed(() => {
  const m = Math.floor(restSeconds.value / 60);
  const s = restSeconds.value % 60;
  return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
});

const restProgressPct = computed(() => {
  if (restTotal.value === 0) return 0;
  return ((restTotal.value - restSeconds.value) / restTotal.value) * 100;
});

// ── Block type helpers ──
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

// ── Normalized exercise field extractors ──
function exName(ex) {
  return ex.nombre || ex.name || ex.ejercicio || 'Ejercicio';
}
function exSeries(ex) { return ex.series || ex.sets || null; }
function exReps(ex) { return ex.repeticiones || ex.reps || null; }
function exDescanso(ex) { return ex.descanso || ex.rest || ex.rest_seconds || null; }
function exRir(ex) { return ex.rir !== undefined && ex.rir !== null ? ex.rir : null; }
function exNotas(ex) { return ex.notas || ex.notes || null; }
function exMuscle(ex) { return ex.musculo || ex.muscle_group || null; }
function exEquip(ex) { return ex.equipo || ex.equipment || null; }
function exIsCardio(ex) { return !!ex.is_cardio; }

function exVideoUrl(ex) { return ex.video_url || ex.video || null; }
function exImageUrl(ex) { return ex.image_url || ex.gif_url || ex.imagen || ex.thumbnail_url || null; }

function exThumbnail(ex) {
  const img = exImageUrl(ex);
  if (img) return img;
  const video = exVideoUrl(ex);
  if (video) {
    const m = video.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|\/embed\/)([a-zA-Z0-9_-]{11})/);
    if (m) return 'https://img.youtube.com/vi/' + m[1] + '/mqdefault.jpg';
  }
  return null;
}

function rirClass(rir) {
  if (rir === null || rir === undefined) return '';
  if (rir >= 3) return 'bg-emerald-500/10 text-emerald-400';
  if (rir >= 2) return 'bg-amber-500/10 text-amber-400';
  return 'bg-red-500/10 text-red-400';
}

// Parse rest text to seconds
function parseRestSeconds(rest) {
  if (!rest) return 90;
  const str = String(rest).trim().toLowerCase();
  // "90s" or "90 seg"
  let m = str.match(/^(\d+)\s*s(eg)?$/i);
  if (m) return parseInt(m[1]);
  // "2min" or "2 min"
  m = str.match(/^(\d+)\s*min$/i);
  if (m) return parseInt(m[1]) * 60;
  // "1:30"
  m = str.match(/^(\d+):(\d{2})$/);
  if (m) return parseInt(m[1]) * 60 + parseInt(m[2]);
  // Plain number
  if (!isNaN(str)) return parseInt(str);
  // Extract first number
  m = str.match(/(\d+)/);
  if (m) {
    const n = parseInt(m[1]);
    return str.includes('min') ? n * 60 : n;
  }
  return 90;
}

// ── Set management ──
function getSetRows(exIndex) {
  const ex = exercises.value[exIndex];
  const total = parseInt(exSeries(ex) || 3);
  if (!setData.value[exIndex]) {
    setData.value[exIndex] = [];
    for (let i = 0; i < total; i++) {
      setData.value[exIndex].push({
        set_number: i + 1,
        target_reps: exReps(ex) || '',
        target_weight: null,
        weight: '',
        reps: '',
        completed: false,
        is_pr: false,
      });
    }
  }
  return Array.isArray(setData.value[exIndex])
    ? setData.value[exIndex]
    : Object.values(setData.value[exIndex]);
}

function allSetsComplete(exIndex) {
  const ex = exercises.value[exIndex];
  const total = parseInt(exSeries(ex) || 3);
  const sets = setData.value[exIndex];
  if (!sets) return false;
  const arr = Array.isArray(sets) ? sets : Object.values(sets);
  return arr.filter(s => s.completed).length >= total;
}

// Weight step based on unit
function weightStep() {
  return weightUnit.value === 'lbs' ? 5 : 2.5;
}

// Convert weight for storage (lbs -> kg)
function weightToKg(weight) {
  if (weightUnit.value === 'lbs') {
    return +(weight / 2.205).toFixed(2);
  }
  return weight;
}

// Parse initial reps from target (e.g. "8-10" -> 8)
function parseInitialReps(repsStr) {
  if (!repsStr) return 10;
  const m = String(repsStr).match(/\d+/);
  return m ? parseInt(m[0]) : 10;
}

async function toggleSet(exIndex, setIndex) {
  const sets = getSetRows(exIndex);
  const set = sets[setIndex];
  set.completed = !set.completed;

  if (set.completed && workoutStarted.value) {
    // Validate reps > 0
    const reps = parseInt(set.reps) || 0;
    if (reps <= 0) {
      set.completed = false;
      return;
    }
    if (navigator.vibrate) navigator.vibrate(50);

    const saveWeight = weightToKg(parseFloat(set.weight) || 0);

    try {
      const response = await api.post('/api/v/client/workout/complete-set', {
        session_id: sessionId.value,
        exercise_index: exIndex,
        set_index: setIndex,
        set_number: setIndex + 1,
        exercise_name: exName(exercises.value[exIndex]),
        weight: saveWeight,
        reps: reps,
        unit: weightUnit.value,
      });
      // Check for PR
      if (response.data?.is_pr) {
        set.is_pr = true;
      }
    } catch (err) {
      // Silently handle
    }

    // Start rest timer for this exercise
    const ex = exercises.value[exIndex];
    const rest = exDescanso(ex);
    if (rest) {
      const seconds = parseRestSeconds(rest);
      startRestTimer(seconds);
    }
  } else if (!set.completed && workoutStarted.value) {
    // Uncomplete set
    try {
      await api.post('/api/v/client/workout/uncomplete-set', {
        session_id: sessionId.value,
        exercise_index: exIndex,
        set_index: setIndex,
        set_number: setIndex + 1,
      });
    } catch (err) {
      // Silently handle
    }
  }
}

// Cardio set completion
async function completeCardioSet(exIndex, setIndex, duration, speed, incline) {
  const sets = getSetRows(exIndex);
  const set = sets[setIndex];
  if (duration <= 0) return;

  set.completed = true;
  if (navigator.vibrate) navigator.vibrate(50);
  set.reps = duration;

  if (workoutStarted.value) {
    try {
      await api.post('/api/v/client/workout/complete-set', {
        session_id: sessionId.value,
        exercise_index: exIndex,
        set_number: setIndex + 1,
        exercise_name: exName(exercises.value[exIndex]),
        is_cardio: true,
        duration_minutes: duration,
        speed_kmh: speed,
        incline_percent: incline,
        reps: duration,
      });
    } catch (err) {
      // Silently handle
    }
  }
}

async function uncompleteCardioSet(exIndex, setIndex) {
  const sets = getSetRows(exIndex);
  const set = sets[setIndex];
  set.completed = false;

  if (workoutStarted.value) {
    try {
      await api.post('/api/v/client/workout/uncomplete-set', {
        session_id: sessionId.value,
        exercise_index: exIndex,
        set_index: setIndex,
        set_number: setIndex + 1,
      });
    } catch (err) {
      // Silently handle
    }
  }
}

// ── Rest timer ──
function startRestTimer(seconds) {
  clearInterval(restInterval);
  restSeconds.value = seconds;
  restTotal.value = seconds;
  showRestTimer.value = true;
  playBeep(440, 0.1); // Short beep on start
  restInterval = setInterval(() => {
    if (restSeconds.value <= 0) {
      clearInterval(restInterval);
      showRestTimer.value = false;
      playBeep(880, 0.15);
      setTimeout(() => playBeep(880, 0.15), 200);
      return;
    }
    restSeconds.value--;
    // Beep at 3, 2, 1
    if (restSeconds.value <= 3 && restSeconds.value > 0) {
      playBeep(660, 0.08);
    }
  }, 1000);
}

function clearRestTimer() {
  clearInterval(restInterval);
  showRestTimer.value = false;
  restSeconds.value = 0;
}

function startTimer() {
  elapsed.value = 0;
  clearInterval(timerInterval);
  timerInterval = setInterval(() => {
    elapsed.value++;
  }, 1000);
}

function resumeTimer(startTime) {
  const start = new Date(startTime).getTime();
  elapsed.value = Math.max(0, Math.floor((Date.now() - start) / 1000));
  clearInterval(timerInterval);
  timerInterval = setInterval(() => {
    elapsed.value++;
  }, 1000);
}

function stopTimer() {
  clearInterval(timerInterval);
}

// ── Weight unit ──
function setWeightUnit(unit) {
  weightUnit.value = unit;
  localStorage.setItem('wc_weight_unit', unit);
}

// ── Audio ──
function playBeep(freq = 880, duration = 0.15) {
  try {
    if (!audioCtx) {
      audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    }
    const osc = audioCtx.createOscillator();
    const gain = audioCtx.createGain();
    osc.connect(gain);
    gain.connect(audioCtx.destination);
    osc.frequency.value = freq;
    gain.gain.value = 0.3;
    osc.start();
    osc.stop(audioCtx.currentTime + duration);
  } catch (e) { /* silent */ }
}

// ── Day switching ──
function switchDay(index) {
  if (workoutStarted.value && currentDayIndex.value !== index) return;
  currentDayIndex.value = index;
  clearRestTimer();
  loadDayExercises();
}

// ── Week switching (Elite plans) ──
async function switchWeek(week) {
  if (workoutStarted.value || !hasProgressions.value) return;
  if (week < 1 || week > totalWeeks.value) return;
  currentWeek.value = week;
  // Re-fetch workout data for the new week
  loading.value = true;
  try {
    const response = await api.get(`/api/v/client/workout/1?week=${week}`);
    const d = response.data;
    days.value = d.days || [];
    currentDayIndex.value = 0;
    loadDayExercises();
  } catch (err) {
    // Keep current data on error
  } finally {
    loading.value = false;
  }
}

function loadDayExercises() {
  if (!days.value.length) return;
  const day = days.value[currentDayIndex.value];
  if (!day) return;
  exercises.value = day.ejercicios || day.exercises || [];
  setData.value = {};
  exercises.value.forEach((ex, idx) => {
    getSetRows(idx);
  });
}

// ── Notes toggle ──
function toggleNotes(exIndex) {
  expandedNotes.value[exIndex] = !expandedNotes.value[exIndex];
}

// ── Tutorial ──
function dismissTutorial() {
  showTutorial.value = false;
  try {
    api.post('/api/v/client/workout/dismiss-tutorial');
  } catch (err) { /* silent */ }
}

// ── API calls ──
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

    // Elite plan progressions
    if (d.hasProgressions) {
      hasProgressions.value = true;
      currentWeek.value = d.currentWeek || 1;
      totalWeeks.value = d.totalWeeks || 1;
    }

    // Tutorial for first-time users
    if (d.showTutorial) {
      showTutorial.value = true;
    }

    // Resume session if exists
    if (d.activeSession) {
      sessionId.value = d.activeSession.id;
      workoutStarted.value = true;
      if (d.activeSession.startTime) {
        resumeTimer(d.activeSession.startTime);
      } else {
        elapsed.value = d.activeSession.elapsed || 0;
        startTimer();
      }
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
      week: hasProgressions.value ? currentWeek.value : null,
    });
    sessionId.value = response.data.session_id || null;
    // Re-populate setData with previous weights if returned
    if (response.data.setData) {
      setData.value = response.data.setData;
    }
  } catch (err) {
    // Keep going even if API fails
  }
  await nextTick();
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function finishWorkout() {
  if (saving.value) return;
  // Haptic feedback
  if (navigator.vibrate) navigator.vibrate([50, 30, 100]);
  saving.value = true;
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
    router.push({ name: 'client-workout-summary', params: { sessionId: sessionId.value || 'latest' } });
  } finally {
    saving.value = false;
  }
}

async function abandonWorkout() {
  if (abandoning.value) return;
  abandoning.value = true;
  confirmAbandon.value = false;
  stopTimer();
  clearRestTimer();
  try {
    await api.post('/api/v/client/workout/abandon', {
      session_id: sessionId.value,
    });
  } catch (err) { /* silent */ }
  finally {
    abandoning.value = false;
  }
  workoutStarted.value = false;
  sessionId.value = null;
  setData.value = {};
  elapsed.value = 0;
  loadDayExercises();
}

onMounted(() => {
  fetchWorkout();
});

onBeforeUnmount(() => {
  stopTimer();
  clearRestTimer();
  if (audioCtx) {
    audioCtx.close().catch(() => {});
    audioCtx = null;
  }
});
</script>

<template>
  <ClientLayout>
    <div class="min-h-screen" :class="{ 'pb-[320px]': workoutStarted }">

      <!-- ════════════════════════════════════════════════ -->
      <!-- LOADING                                          -->
      <!-- ════════════════════════════════════════════════ -->
      <template v-if="loading">
        <div class="space-y-4 animate-pulse p-4">
          <div class="h-16 rounded-xl bg-wc-bg-tertiary"></div>
          <div class="h-24 rounded-xl bg-wc-bg-tertiary"></div>
          <div class="h-48 rounded-xl bg-wc-bg-tertiary"></div>
          <div class="h-48 rounded-xl bg-wc-bg-tertiary"></div>
        </div>
      </template>

      <!-- ════════════════════════════════════════════════ -->
      <!-- ERROR                                            -->
      <!-- ════════════════════════════════════════════════ -->
      <div v-else-if="error" class="flex min-h-[60vh] items-center justify-center px-4">
        <div class="w-full max-w-sm rounded-2xl border border-red-500/30 bg-red-500/10 p-8 text-center">
          <svg class="mx-auto h-10 w-10 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
          <p class="mt-3 text-sm text-red-400">{{ error }}</p>
          <button @click="fetchWorkout" class="mt-4 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors">Reintentar</button>
        </div>
      </div>

      <!-- ════════════════════════════════════════════════ -->
      <!-- EMPTY STATE — No plan assigned                   -->
      <!-- ════════════════════════════════════════════════ -->
      <div v-else-if="!days.length" class="flex min-h-[60vh] items-center justify-center px-4">
        <div class="w-full max-w-sm rounded-2xl border border-wc-border bg-wc-bg-tertiary p-10 text-center">
          <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-wc-bg-secondary">
            <svg class="h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" /></svg>
          </div>
          <h2 class="mt-5 font-display text-2xl tracking-wide text-wc-text">TU PLAN VIENE EN CAMINO</h2>
          <p class="mt-2 text-sm text-wc-text-secondary">Tu coach esta preparando tu plan de entrenamiento personalizado.</p>
          <p class="mt-4 text-xs text-wc-text-tertiary">Cuando tu plan este listo, lo veras aqui.</p>
          <div class="mt-6 h-1 w-16 mx-auto rounded-full bg-gradient-to-r from-wc-accent/40 to-wc-accent/10"></div>
        </div>
      </div>

      <!-- ════════════════════════════════════════════════ -->
      <!-- MAIN WORKOUT CONTENT                             -->
      <!-- ════════════════════════════════════════════════ -->
      <template v-else>

        <!-- ──────────────────────────────────────────── -->
        <!-- HEADER — Day selector pills + Week selector  -->
        <!-- ──────────────────────────────────────────── -->
        <div class="sticky top-0 z-30 bg-wc-bg/95 backdrop-blur-md border-b border-wc-border">
          <div class="px-4 py-3">
            <!-- Week selector (Elite plans) -->
            <div v-if="hasProgressions && totalWeeks > 1" class="mb-2 flex items-center gap-2 overflow-x-auto scrollbar-none">
              <span class="text-[10px] font-bold uppercase tracking-wider text-wc-text-tertiary shrink-0">Semana:</span>
              <button
                v-for="w in totalWeeks"
                :key="'week-' + w"
                @click="switchWeek(w)"
                :class="[
                  'shrink-0 rounded-lg px-3 py-1 text-xs font-bold transition-all',
                  currentWeek === w
                    ? 'bg-wc-accent text-white'
                    : workoutStarted
                      ? 'bg-wc-bg-tertiary text-wc-text-tertiary opacity-50 cursor-not-allowed'
                      : 'bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-text'
                ]"
              >{{ w }}</button>
            </div>

            <!-- Day pills -->
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
                <span
                  v-if="day.grupo_muscular || day.muscle_group || day.nombre || day.name || day.dia"
                  class="text-[9px] font-medium uppercase tracking-wider leading-none opacity-75 max-w-[80px] truncate"
                >
                  {{ day.grupo_muscular || day.muscle_group || day.nombre || day.name || day.dia }}
                </span>
              </button>
            </div>

            <!-- Active session timer -->
            <div v-if="workoutStarted" class="mt-2 flex items-center justify-between rounded-lg bg-wc-accent/10 border border-wc-accent/20 px-3 py-2 wc-grain">
              <div class="flex items-center gap-2">
                <span class="relative flex h-2 w-2">
                  <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-wc-accent opacity-75"></span>
                  <span class="relative inline-flex h-2 w-2 rounded-full bg-wc-accent"></span>
                </span>
                <span class="text-xs font-semibold uppercase tracking-wider text-wc-accent">En curso</span>
              </div>
              <span class="font-data text-lg font-bold text-wc-accent tabular-nums wc-timer-glow">{{ elapsedDisplay }}</span>
            </div>
          </div>
        </div>

        <!-- ──────────────────────────────────────────── -->
        <!-- REST TIMER FLOATING OVERLAY                  -->
        <!-- ──────────────────────────────────────────── -->
        <Transition
          enter-active-class="transition ease-out duration-200"
          enter-from-class="opacity-0 translate-y-4"
          enter-to-class="opacity-100 translate-y-0"
          leave-active-class="transition ease-in duration-150"
          leave-from-class="opacity-100 translate-y-0"
          leave-to-class="opacity-0 translate-y-4"
        >
          <div v-if="showRestTimer" class="fixed bottom-24 left-1/2 -translate-x-1/2 z-40 rounded-2xl border border-wc-accent/30 bg-wc-bg/95 backdrop-blur-xl px-6 py-4 shadow-2xl min-w-[200px] wc-grain">
            <div class="flex items-center gap-4">
              <!-- Rest progress ring -->
              <div class="relative h-14 w-14 shrink-0">
                <svg class="-rotate-90 h-full w-full" viewBox="0 0 56 56">
                  <circle cx="28" cy="28" r="24" fill="none" stroke="currentColor" stroke-width="3" class="text-wc-border" />
                  <circle cx="28" cy="28" r="24" fill="none" stroke="#DC2626" stroke-width="3" stroke-linecap="round"
                    :stroke-dasharray="150.8"
                    :stroke-dashoffset="150.8 * (1 - restProgressPct / 100)"
                    class="transition-all duration-1000"
                  />
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                  <span class="font-data text-xs font-bold tabular-nums text-wc-text">{{ restSeconds }}</span>
                </div>
              </div>
              <div class="text-center">
                <p class="text-[10px] font-bold uppercase tracking-wider text-wc-text-tertiary">Descanso</p>
                <p class="font-data text-3xl font-bold tabular-nums text-wc-accent">{{ restDisplay }}</p>
              </div>
              <button @click="clearRestTimer" class="rounded-lg border border-wc-border px-3 py-1.5 text-xs font-medium text-wc-text-secondary hover:text-wc-text transition-colors">
                Saltar
              </button>
            </div>
          </div>
        </Transition>

        <div class="mt-4 space-y-4 px-4">
          <!-- ════════════════════════════════════════ -->
          <!-- PRE-WORKOUT STATE                        -->
          <!-- ════════════════════════════════════════ -->
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

            <!-- Warmup card -->
            <div v-if="dayWarmup" class="overflow-hidden rounded-2xl border border-amber-500/25 bg-gradient-to-br from-amber-500/[0.08] via-amber-400/[0.04] to-transparent">
              <div class="flex items-start gap-3 px-4 py-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/15">
                  <svg class="h-5 w-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" /></svg>
                </div>
                <div>
                  <p class="font-display text-sm tracking-wide text-amber-400">CALENTAMIENTO</p>
                  <p class="mt-1 text-sm leading-relaxed text-wc-text-secondary">{{ dayWarmup }}</p>
                </div>
              </div>
            </div>

            <!-- Exercise preview cards -->
            <template v-for="(exercise, exIndex) in exercises" :key="'preview-' + exIndex">
              <!-- Block label (superset/circuit) -->
              <div v-if="isFirstInBlock(exIndex)" class="flex items-center gap-3">
                <span class="rounded-full border border-wc-accent/30 bg-wc-accent/10 px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-wc-accent">
                  {{ getBlockType(exercise) === 'superset' ? 'SUPERSET' : 'CIRCUITO' }}
                </span>
                <div class="h-px flex-1 bg-wc-accent/15"></div>
              </div>

              <!-- Exercise card -->
              <div
                :class="[
                  'overflow-hidden rounded-2xl border bg-wc-bg-tertiary transition-all wc-glass wc-lift wc-stagger-enter',
                  isInBlock(exercise) ? 'ml-3 border-l-[3px] border-l-wc-accent border-wc-border/70' : 'border-wc-border'
                ]"
                :style="{ animationDelay: (exIndex * 80) + 'ms' }"
              >
                <div class="flex items-stretch">
                  <!-- Thumbnail column — clickable to open media modal -->
                  <div
                    class="relative w-20 shrink-0 overflow-hidden bg-wc-bg-secondary cursor-pointer"
                    @click="openMedia(exercise)"
                  >
                    <!-- GIF animado o thumbnail de YouTube -->
                    <img
                      v-if="exImageUrl(exercise)"
                      :src="exImageUrl(exercise)"
                      :alt="exName(exercise)"
                      class="h-full w-full object-cover opacity-90"
                    />
                    <!-- Fallback trueno si no hay imagen -->
                    <div v-else class="flex h-full w-full min-h-[80px] flex-col items-center justify-center bg-gradient-to-b from-wc-bg-secondary to-wc-bg">
                      <svg class="h-7 w-7 text-wc-text-tertiary/40" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" /></svg>
                    </div>
                    <!-- Numero del ejercicio -->
                    <div class="absolute bottom-2 left-2 flex h-6 w-6 items-center justify-center rounded-lg bg-wc-accent backdrop-blur-sm border border-wc-border/30">
                      <span class="font-display text-xs font-black leading-none text-white">{{ exIndex + 1 }}</span>
                    </div>
                    <!-- Badge play si tiene video -->
                    <div v-if="exVideoUrl(exercise)" class="absolute top-2 right-2 flex h-5 w-5 items-center justify-center rounded-full bg-red-600/90 backdrop-blur-sm">
                      <svg class="h-3 w-3 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    </div>
                  </div>

                  <!-- Content -->
                  <div class="min-w-0 flex-1 p-3">
                    <!-- Name + muscle group -->
                    <div class="flex flex-wrap items-start justify-between gap-2">
                      <h3 class="font-display text-xl tracking-wide leading-tight text-wc-text uppercase">{{ exName(exercise) }}</h3>
                      <span v-if="exMuscle(exercise)" class="shrink-0 rounded-full bg-wc-bg-secondary px-2.5 py-0.5 text-[10px] font-medium text-wc-text-tertiary">
                        {{ exMuscle(exercise) }}
                      </span>
                    </div>

                    <!-- Coach notes -->
                    <p v-if="exNotas(exercise)" class="mt-1 text-xs leading-relaxed text-wc-text-tertiary">{{ exNotas(exercise) }}</p>

                    <!-- Chips row -->
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                      <!-- Cardio chips -->
                      <template v-if="exIsCardio(exercise)">
                        <span v-if="exReps(exercise)" class="inline-flex items-center gap-1.5 rounded-lg bg-sky-500/10 px-3 py-1.5">
                          <svg class="h-3.5 w-3.5 text-sky-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                          <span class="font-data text-sm font-black text-sky-400">{{ exReps(exercise) }}</span>
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-lg bg-sky-500/10 px-2.5 py-1.5 text-[10px] font-semibold text-sky-400 uppercase tracking-wider">Cardio</span>
                      </template>
                      <!-- Strength chips -->
                      <template v-else>
                        <span v-if="exSeries(exercise) || exReps(exercise)" class="inline-flex items-center gap-1 rounded-lg bg-wc-accent/10 px-3 py-1.5">
                          <span class="font-data text-sm font-black text-wc-accent">{{ exSeries(exercise) || '?' }}</span>
                          <span class="text-xs text-wc-accent/60">&times;</span>
                          <span class="font-data text-sm font-black text-wc-accent">{{ exReps(exercise) || '?' }}</span>
                          <span class="text-[10px] text-wc-accent/60 ml-0.5">reps</span>
                        </span>
                      </template>

                      <!-- Rest -->
                      <span v-if="exDescanso(exercise)" class="inline-flex items-center gap-1.5 rounded-lg bg-wc-bg-secondary px-2.5 py-1.5 text-xs text-wc-text-secondary">
                        <svg class="h-3 w-3 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        {{ exDescanso(exercise) }}
                      </span>

                      <!-- RIR -->
                      <span v-if="exRir(exercise) !== null" class="rounded-lg px-2.5 py-1.5 text-[11px] font-bold" :class="rirClass(exRir(exercise))">
                        RIR {{ exRir(exercise) }}
                      </span>

                      <!-- Equipment -->
                      <span v-if="exEquip(exercise)" class="rounded-full bg-wc-bg-secondary px-2.5 py-0.5 text-[10px] text-wc-text-tertiary">
                        {{ exEquip(exercise) }}
                      </span>
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
                class="wc-btn-energy btn-ripple w-full rounded-2xl py-4 text-center shadow-lg shadow-wc-accent/20 hover:bg-red-700 transition-colors"
              >
                <span class="font-display text-xl tracking-widest text-white">INICIAR ENTRENAMIENTO</span>
              </button>
            </div>
          </div>

          <!-- ════════════════════════════════════════ -->
          <!-- ACTIVE WORKOUT STATE                     -->
          <!-- ════════════════════════════════════════ -->
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
                <div class="h-full rounded-full wc-progress-bar transition-all duration-700 ease-out" :style="{ width: progressPct + '%' }"></div>
              </div>
            </div>

            <!-- Warmup reminder (during workout) -->
            <div v-if="dayWarmup" class="rounded-xl border border-amber-500/20 bg-amber-500/5 px-4 py-3">
              <div class="flex items-center gap-2">
                <svg class="h-4 w-4 text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" /></svg>
                <span class="text-xs font-semibold text-amber-400">Calentamiento:</span>
                <span class="text-xs text-wc-text-secondary">{{ dayWarmup }}</span>
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
                  allSetsComplete(exIndex)
                    ? 'border-l-[3px] border-l-emerald-500 border-emerald-500/20'
                    : 'border-wc-border'
                ]"
              >
                <!-- Exercise header -->
                <div class="p-4 pb-3">
                  <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                      <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-wc-bg-secondary text-xs font-bold text-wc-text-tertiary font-data">{{ exIndex + 1 }}</span>
                        <h3 class="font-display text-lg tracking-wide leading-tight text-wc-text uppercase">{{ exName(exercise) }}</h3>
                      </div>

                      <!-- Badges row -->
                      <div class="mt-2 flex flex-wrap items-center gap-1.5">
                        <!-- Muscle group -->
                        <span v-if="exMuscle(exercise)" class="inline-flex items-center rounded-full bg-wc-bg-secondary px-2.5 py-0.5 text-[10px] font-medium text-wc-text-tertiary">
                          {{ exMuscle(exercise) }}
                        </span>
                        <!-- Equipment -->
                        <span v-if="exEquip(exercise)" class="inline-flex items-center rounded-full bg-wc-bg-secondary px-2.5 py-0.5 text-[10px] font-medium text-wc-text-tertiary">
                          {{ exEquip(exercise) }}
                        </span>
                        <!-- RIR -->
                        <span v-if="exRir(exercise) !== null" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold" :class="rirClass(exRir(exercise))">
                          RIR {{ exRir(exercise) }}
                        </span>
                        <!-- Rest -->
                        <span v-if="exDescanso(exercise)" class="inline-flex items-center gap-1 rounded-full bg-wc-bg-secondary px-2.5 py-0.5 text-[10px] font-medium text-wc-text-tertiary">
                          <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                          {{ typeof exDescanso(exercise) === 'number' ? exDescanso(exercise) + 's' : exDescanso(exercise) }} descanso
                        </span>
                        <!-- Completed badge -->
                        <span v-if="allSetsComplete(exIndex)" class="inline-flex items-center gap-1 rounded-full bg-emerald-500/15 px-2.5 py-0.5 text-[10px] font-bold text-emerald-400">
                          <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                          Completado
                        </span>
                      </div>

                      <!-- Last weight used -->
                      <div v-if="exercise.last_weight && exercise.last_reps" class="mt-2 flex items-center gap-1.5">
                        <svg class="h-3 w-3 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        <span class="text-[11px] text-wc-text-tertiary">
                          Ultimo: <span class="font-data font-semibold text-wc-text-secondary">{{ exercise.last_weight }} kg</span>
                          <span class="text-wc-text-tertiary">&times;</span>
                          <span class="font-data font-semibold text-wc-text-secondary">{{ exercise.last_reps }}</span>
                        </span>
                      </div>
                    </div>

                    <!-- Completed icon -->
                    <div v-if="allSetsComplete(exIndex)" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-500">
                      <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                    </div>
                  </div>

                  <!-- Coach notes (collapsible) -->
                  <div v-if="exNotas(exercise)" class="mt-3">
                    <button
                      @click="toggleNotes(exIndex)"
                      class="flex items-center gap-1 text-[11px] font-medium text-wc-text-tertiary hover:text-wc-text-secondary transition-colors"
                    >
                      <svg class="h-3 w-3 transition-transform" :class="expandedNotes[exIndex] && 'rotate-90'" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                      Notas del coach
                    </button>
                    <Transition
                      enter-active-class="transition ease-out duration-200"
                      enter-from-class="opacity-0 -translate-y-1"
                      enter-to-class="opacity-100 translate-y-0"
                      leave-active-class="transition ease-in duration-150"
                      leave-from-class="opacity-100"
                      leave-to-class="opacity-0 -translate-y-1"
                    >
                      <div v-if="expandedNotes[exIndex]" class="mt-1.5 rounded-lg bg-wc-bg-secondary px-3 py-2 text-xs leading-relaxed text-wc-text-tertiary">
                        {{ exNotas(exercise) }}
                      </div>
                    </Transition>
                  </div>

                  <!-- Video/GIF del ejercicio activo (colapsable) -->
                  <div v-if="exVideoUrl(exercise) || exImageUrl(exercise)" class="mt-3">
                    <button
                      @click="showActiveMedia[exIndex] = !showActiveMedia[exIndex]"
                      class="flex items-center gap-2 text-xs text-wc-text-tertiary hover:text-wc-text transition-colors"
                    >
                      <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                      <span>{{ showActiveMedia[exIndex] ? 'Ocultar video' : 'Ver ejercicio' }}</span>
                    </button>
                    <div v-show="showActiveMedia[exIndex]" class="mt-2 overflow-hidden rounded-xl border border-wc-border"
                         :data-playing="showActiveMedia[exIndex + '_playing']"
                    >
                      <!-- YouTube embed (shown after clicking play on GIF) -->
                      <div v-if="showActiveMedia[exIndex + '_playing'] && getEmbedUrl(exVideoUrl(exercise))" class="aspect-video w-full">
                        <iframe
                          :src="getEmbedUrl(exVideoUrl(exercise)) + '&autoplay=1'"
                          class="h-full w-full"
                          frameborder="0"
                          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                          referrerpolicy="strict-origin-when-cross-origin"
                          allowfullscreen
                        ></iframe>
                      </div>
                      <!-- GIF with play overlay (default) -->
                      <div v-else class="relative cursor-pointer group" @click="getEmbedUrl(exVideoUrl(exercise)) ? (showActiveMedia[exIndex + '_playing'] = true) : null">
                        <img v-if="exImageUrl(exercise)" :src="exImageUrl(exercise)" :alt="exName(exercise)" class="w-full object-contain max-h-64 bg-wc-bg" />
                        <!-- Play button overlay -->
                        <div v-if="getEmbedUrl(exVideoUrl(exercise))" class="absolute inset-0 flex items-center justify-center bg-black/20 group-hover:bg-black/30 transition-colors">
                          <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-600 shadow-lg group-hover:scale-110 transition-transform">
                            <svg class="h-5 w-5 text-white ml-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- ═══ CARDIO TABLE ═══ -->
                <div v-if="exIsCardio(exercise)" class="border-t border-wc-border overflow-x-auto scrollbar-none">
                  <!-- Cardio header -->
                  <div class="grid gap-1 px-3 py-2 bg-sky-500/5" style="grid-template-columns: 32px 1fr 1fr 1fr 36px">
                    <span class="text-center text-[9px] font-bold uppercase tracking-widest text-sky-400">Set</span>
                    <span class="text-center text-[9px] font-bold uppercase tracking-widest text-sky-400">Min</span>
                    <span class="text-center text-[9px] font-bold uppercase tracking-widest text-sky-400">Vel</span>
                    <span class="text-center text-[9px] font-bold uppercase tracking-widest text-sky-400">Incl %</span>
                    <span class="sr-only">Completar</span>
                  </div>
                  <!-- Cardio set rows -->
                  <div
                    v-for="(set, sIdx) in getSetRows(exIndex)"
                    :key="'cardio-' + sIdx"
                    class="grid gap-1 items-center px-3 py-2.5 transition-colors"
                    :class="set.completed ? 'bg-sky-500/5' : ''"
                    style="grid-template-columns: 32px 1fr 1fr 1fr 36px"
                  >
                    <!-- Set number -->
                    <div class="flex items-center justify-center">
                      <span class="font-data text-sm font-bold" :class="set.completed ? 'text-sky-400' : 'text-wc-text-tertiary'">{{ sIdx + 1 }}</span>
                    </div>
                    <!-- Duration -->
                    <div class="flex items-center justify-center gap-px">
                      <button @click="set.duration = Math.max(1, (set.duration || 30) - 5)" :disabled="set.completed" :class="set.completed && 'opacity-30 pointer-events-none'" class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-wc-bg-secondary text-wc-text-tertiary hover:text-wc-text transition-colors">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" /></svg>
                      </button>
                      <input type="number" min="1" v-model.number="set.duration" :disabled="set.completed" placeholder="30" class="h-7 w-10 rounded-lg border border-sky-500/30 bg-wc-bg px-1 text-center font-data text-xs font-semibold text-wc-text focus:border-sky-400 focus:outline-none tabular-nums" :class="set.completed && 'opacity-60'" />
                      <button @click="set.duration = (set.duration || 30) + 5" :disabled="set.completed" :class="set.completed && 'opacity-30 pointer-events-none'" class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-wc-bg-secondary text-wc-text-tertiary hover:text-wc-text transition-colors">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                      </button>
                    </div>
                    <!-- Speed -->
                    <div class="flex items-center justify-center gap-px">
                      <button @click="set.speed = Math.max(0, +((set.speed || 5.5) - 0.5).toFixed(1))" :disabled="set.completed" :class="set.completed && 'opacity-30 pointer-events-none'" class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-wc-bg-secondary text-wc-text-tertiary hover:text-wc-text transition-colors">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" /></svg>
                      </button>
                      <input type="number" step="0.5" min="0" v-model.number="set.speed" :disabled="set.completed" placeholder="5.5" class="h-7 w-10 rounded-lg border border-sky-500/30 bg-wc-bg px-1 text-center font-data text-xs font-semibold text-wc-text focus:border-sky-400 focus:outline-none tabular-nums" :class="set.completed && 'opacity-60'" />
                      <button @click="set.speed = +((set.speed || 5.5) + 0.5).toFixed(1)" :disabled="set.completed" :class="set.completed && 'opacity-30 pointer-events-none'" class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-wc-bg-secondary text-wc-text-tertiary hover:text-wc-text transition-colors">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                      </button>
                    </div>
                    <!-- Incline -->
                    <div class="flex items-center justify-center gap-px">
                      <button @click="set.incline = Math.max(0, (set.incline || 3) - 1)" :disabled="set.completed" :class="set.completed && 'opacity-30 pointer-events-none'" class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-wc-bg-secondary text-wc-text-tertiary hover:text-wc-text transition-colors">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" /></svg>
                      </button>
                      <input type="number" min="0" max="15" v-model.number="set.incline" :disabled="set.completed" placeholder="3" class="h-7 w-9 rounded-lg border border-sky-500/30 bg-wc-bg px-1 text-center font-data text-xs font-semibold text-wc-text focus:border-sky-400 focus:outline-none tabular-nums" :class="set.completed && 'opacity-60'" />
                      <button @click="set.incline = Math.min(15, (set.incline || 3) + 1)" :disabled="set.completed" :class="set.completed && 'opacity-30 pointer-events-none'" class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-wc-bg-secondary text-wc-text-tertiary hover:text-wc-text transition-colors">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                      </button>
                    </div>
                    <!-- Complete cardio set button -->
                    <div class="flex items-center justify-center">
                      <button
                        v-if="!set.completed"
                        @click="completeCardioSet(exIndex, sIdx, set.duration || 30, set.speed || 5.5, set.incline || 3)"
                        class="flex h-8 w-8 items-center justify-center rounded-xl border-2 border-sky-500/30 text-sky-400 hover:border-sky-400 hover:bg-sky-500/10 transition-all"
                        :class="(set.duration || 30) <= 0 && 'opacity-30 cursor-not-allowed'"
                      >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                      </button>
                      <button
                        v-else
                        @click="uncompleteCardioSet(exIndex, sIdx)"
                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-sky-500 text-white hover:bg-orange-500/80 hover:scale-90 transition-all"
                        title="Toca para desmarcar"
                      >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                      </button>
                    </div>
                  </div>
                </div>

                <!-- ═══ STRENGTH TABLE ═══ -->
                <div v-else class="border-t border-wc-border overflow-x-auto scrollbar-none">
                  <!-- Strength header -->
                  <div class="wc-topline grid gap-1 px-3 py-2 bg-wc-bg-secondary/50" style="grid-template-columns: 32px 50px 1fr 1fr 36px">
                    <span class="text-center text-[9px] font-bold uppercase tracking-widest text-wc-text-tertiary">Set</span>
                    <span class="text-center text-[9px] font-bold uppercase tracking-widest text-wc-text-tertiary">Anterior</span>
                    <span class="text-center text-[9px] font-bold uppercase tracking-widest text-wc-text-tertiary">{{ weightUnit === 'lbs' ? 'Peso (lbs)' : 'Peso (kg)' }}</span>
                    <span class="text-center text-[9px] font-bold uppercase tracking-widest text-wc-text-tertiary">Reps</span>
                    <span class="sr-only">Completar</span>
                  </div>

                  <!-- Strength set rows -->
                  <div
                    v-for="(set, sIdx) in getSetRows(exIndex)"
                    :key="'str-' + sIdx"
                    class="wc-zebra grid gap-1 items-center px-3 py-2 transition-colors"
                    :class="[
                      set.completed ? 'wc-set-done' : '',
                      sIdx < getSetRows(exIndex).length - 1 ? 'border-b border-wc-border/50' : ''
                    ]"
                    style="grid-template-columns: 32px 50px 1fr 1fr 36px"
                  >
                    <!-- Set number + PR badge -->
                    <div class="flex flex-col items-center justify-center gap-0.5">
                      <span class="font-data text-sm font-bold" :class="set.completed ? 'text-emerald-400' : 'text-wc-text-tertiary'">{{ sIdx + 1 }}</span>
                      <span v-if="set.is_pr" class="pr-badge wc-pr-badge inline-flex items-center gap-0.5 rounded-md px-1 py-0.5 text-[8px] font-black leading-none text-black">PR</span>
                    </div>

                    <!-- Anterior (target from last session) -->
                    <div class="flex items-center justify-center">
                      <span v-if="set.target_weight || set.target_reps" class="text-center font-data text-[11px] font-medium text-wc-text/30 tabular-nums leading-tight">
                        {{ set.target_weight ? Number(set.target_weight).toFixed(1) + 'kg' : '—' }}<br>
                        <span class="text-[9px]">&times; {{ set.target_reps || '?' }}</span>
                      </span>
                      <span v-else class="font-data text-[11px] text-wc-text/20">—</span>
                    </div>

                    <!-- Weight input with +/- -->
                    <div class="flex flex-col items-center gap-0.5">
                      <div class="flex items-center justify-center gap-px">
                        <button
                          @click="set.weight = Math.max(0, (parseFloat(set.weight) || 0) - weightStep())"
                          class="btn-press flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-wc-bg-secondary text-wc-text-tertiary hover:text-wc-text active:bg-wc-bg transition-colors"
                          :disabled="set.completed"
                          :class="set.completed && 'opacity-30 pointer-events-none'"
                          aria-label="Reducir peso"
                        >
                          <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" /></svg>
                        </button>
                        <input
                          type="number"
                          step="0.5"
                          min="0"
                          v-model.number="set.weight"
                          class="wc-input-focus h-7 w-10 rounded-lg border border-wc-border bg-wc-bg px-1 text-center font-data text-xs font-semibold text-wc-text focus:border-wc-accent focus:outline-none tabular-nums"
                          :class="set.completed && 'opacity-60'"
                          :disabled="set.completed"
                          placeholder="0"
                        />
                        <button
                          @click="set.weight = (parseFloat(set.weight) || 0) + weightStep()"
                          class="btn-press flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-wc-bg-secondary text-wc-text-tertiary hover:text-wc-text active:bg-wc-bg transition-colors"
                          :disabled="set.completed"
                          :class="set.completed && 'opacity-30 pointer-events-none'"
                          aria-label="Aumentar peso"
                        >
                          <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        </button>
                      </div>
                      <!-- Peso sesión anterior — solo primer set, solo si hay dato -->
                      <span
                        v-if="sIdx === 0 && exercise.last_weight != null"
                        class="font-data text-[9px] leading-none tabular-nums text-wc-text-tertiary/60"
                      >Últ: {{ weightUnit === 'lbs' ? (exercise.last_weight * 2.205).toFixed(1) : exercise.last_weight.toFixed(1) }}{{ weightUnit }}<span v-if="exercise.last_reps"> &times; {{ exercise.last_reps }}</span></span>
                    </div>

                    <!-- Reps input with +/- -->
                    <div class="flex items-center justify-center gap-px">
                      <button
                        @click="set.reps = Math.max(0, (parseInt(set.reps) || 0) - 1)"
                        class="btn-press flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-wc-bg-secondary text-wc-text-tertiary hover:text-wc-text active:bg-wc-bg transition-colors"
                        :disabled="set.completed"
                        :class="set.completed && 'opacity-30 pointer-events-none'"
                        aria-label="Reducir reps"
                      >
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" /></svg>
                      </button>
                      <input
                        type="number"
                        min="0"
                        v-model.number="set.reps"
                        class="wc-input-focus h-7 w-9 rounded-lg border border-wc-border bg-wc-bg px-1 text-center font-data text-xs font-semibold text-wc-text focus:border-wc-accent focus:outline-none tabular-nums"
                        :class="set.completed && 'opacity-60'"
                        :disabled="set.completed"
                        placeholder="0"
                      />
                      <button
                        @click="set.reps = (parseInt(set.reps) || 0) + 1"
                        class="btn-press flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-wc-bg-secondary text-wc-text-tertiary hover:text-wc-text active:bg-wc-bg transition-colors"
                        :disabled="set.completed"
                        :class="set.completed && 'opacity-30 pointer-events-none'"
                        aria-label="Aumentar reps"
                      >
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                      </button>
                    </div>

                    <!-- Complete set button -->
                    <div class="flex items-center justify-center">
                      <Transition
                        enter-active-class="transition ease-out duration-300"
                        enter-from-class="scale-50 opacity-0"
                        enter-to-class="scale-100 opacity-100"
                        leave-active-class="transition ease-in duration-150"
                        leave-from-class="scale-100 opacity-100"
                        leave-to-class="scale-50 opacity-0"
                        mode="out-in"
                      >
                        <button
                          v-if="!set.completed"
                          :key="'uncomplete-' + sIdx"
                          @click="toggleSet(exIndex, sIdx)"
                          class="flex h-8 w-8 items-center justify-center rounded-xl border-2 border-wc-border text-wc-text-tertiary hover:border-emerald-500 hover:text-emerald-400 transition-all"
                          :class="(parseInt(set.reps) || 0) <= 0 && 'opacity-30 cursor-not-allowed'"
                          aria-label="Completar serie"
                        >
                          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        </button>
                        <button
                          v-else
                          :key="'complete-' + sIdx"
                          @click="toggleSet(exIndex, sIdx)"
                          class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-500 text-white hover:bg-orange-500/80 hover:scale-90 transition-all"
                          title="Toca para desmarcar"
                        >
                          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        </button>
                      </Transition>
                    </div>
                  </div>
                </div>

                <!-- Manual rest timer button -->
                <div v-if="exDescanso(exercise)" class="border-t border-wc-border/30 px-3 py-2">
                  <button
                    @click="startRestTimer(parseRestSeconds(exDescanso(exercise)))"
                    class="flex w-full items-center justify-center gap-2 rounded-xl border border-wc-border bg-wc-bg-secondary py-2.5 text-xs font-medium text-wc-text-secondary hover:border-wc-accent/40 hover:text-wc-accent transition-all"
                  >
                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    <span>Descanso &middot; {{ exDescanso(exercise) }}{{ typeof exDescanso(exercise) === 'number' ? 's' : '' }}</span>
                  </button>
                </div>
              </div>
            </template>
          </div>
        </div>

        <!-- ════════════════════════════════════════════════ -->
        <!-- STICKY BOTTOM BAR (active workout)              -->
        <!-- ════════════════════════════════════════════════ -->
        <Transition
          enter-active-class="transition ease-out duration-300"
          enter-from-class="translate-y-full"
          enter-to-class="translate-y-0"
          leave-active-class="transition ease-in duration-200"
          leave-from-class="translate-y-0"
          leave-to-class="translate-y-full"
        >
          <div v-if="workoutStarted" class="fixed bottom-0 inset-x-0 z-[55] border-t border-wc-border bg-wc-bg/95 backdrop-blur-md" style="padding-bottom: max(env(safe-area-inset-bottom, 0px), 16px);">
            <div class="px-4 pt-3 pb-2">
              <!-- Session stats -->
              <div class="mb-2.5 flex items-center justify-center gap-4 text-center">
                <div>
                  <span class="font-data text-sm font-bold text-wc-text">{{ completedSetsCount }}</span>
                  <span class="text-[10px] text-wc-text-tertiary ml-0.5">sets</span>
                </div>
                <div class="h-3 w-px bg-wc-border"></div>
                <div>
                  <span class="font-data text-sm font-bold text-wc-text">{{ totalRepsAll }}</span>
                  <span class="text-[10px] text-wc-text-tertiary ml-0.5">reps</span>
                </div>
                <div class="h-3 w-px bg-wc-border"></div>
                <div>
                  <span class="font-data text-sm font-bold text-amber-400">{{ maxWeightAll > 0 ? maxWeightAll.toFixed(1) : '—' }}</span>
                  <span class="text-[10px] text-wc-text-tertiary ml-0.5">kg max</span>
                </div>
              </div>

              <!-- Action row: Abandon + Complete -->
              <div class="flex items-center gap-2">
                <!-- Abandon button -->
                <button
                  @click="confirmAbandon = true"
                  class="shrink-0 flex items-center gap-1.5 rounded-xl border border-red-600/40 px-4 py-3 text-sm font-medium text-wc-text-secondary hover:border-red-600/70 hover:text-red-400 transition-all focus:outline-none focus:ring-2 focus:ring-wc-accent"
                  aria-label="Abandonar sesion"
                >
                  <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                  Abandonar
                </button>

                <!-- Complete session button -->
                <button
                  @click="finishWorkout"
                  :disabled="completedSetsCount <= 0 || saving"
                  :class="[
                    'btn-ripple btn-press flex-1 rounded-2xl py-3.5 text-center font-display text-lg tracking-widest transition-all',
                    completedSetsCount > 0
                      ? 'bg-wc-accent text-white shadow-lg shadow-wc-accent/20 hover:bg-red-700'
                      : 'bg-wc-bg-secondary text-wc-text-tertiary cursor-not-allowed'
                  ]"
                >
                  <span v-if="!saving">COMPLETAR SESION</span>
                  <span v-else class="inline-flex items-center gap-2">
                    <svg class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    GUARDANDO...
                  </span>
                </button>
              </div>
            </div>
          </div>
        </Transition>

        <!-- ════════════════════════════════════════════════ -->
        <!-- ABANDON CONFIRMATION DIALOG                     -->
        <!-- ════════════════════════════════════════════════ -->
        <Transition
          enter-active-class="transition ease-out duration-200"
          enter-from-class="opacity-0"
          enter-to-class="opacity-100"
          leave-active-class="transition ease-in duration-150"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0"
        >
          <div
            v-if="confirmAbandon"
            class="fixed inset-0 z-[60] flex items-end justify-center bg-black/60 px-4 pb-8 backdrop-blur-sm"
            @keydown.escape="confirmAbandon = false"
            role="dialog"
            aria-modal="true"
            aria-labelledby="abandon-dialog-title"
          >
            <Transition
              enter-active-class="transition ease-out duration-200"
              enter-from-class="opacity-0 scale-95"
              enter-to-class="opacity-100 scale-100"
              leave-active-class="transition ease-in duration-150"
              leave-from-class="opacity-100 scale-100"
              leave-to-class="opacity-0 scale-95"
            >
              <div v-if="confirmAbandon" class="w-full max-w-sm rounded-2xl border border-wc-border bg-wc-bg p-6 shadow-2xl">
                <h3 id="abandon-dialog-title" class="font-display text-xl tracking-wide text-wc-text">ABANDONAR SESION</h3>
                <p class="mt-2 text-sm text-wc-text-secondary">Tu progreso parcial se conservara. Los sets completados ya estan guardados.</p>
                <div class="mt-5 flex gap-3">
                  <button
                    @click="confirmAbandon = false"
                    class="flex-1 rounded-xl border border-wc-border bg-wc-bg-secondary py-3 text-sm font-medium text-wc-text-secondary hover:text-wc-text transition-colors focus:outline-none focus:ring-2 focus:ring-wc-accent"
                  >
                    Cancelar
                  </button>
                  <button
                    @click="abandonWorkout"
                    :disabled="abandoning"
                    class="flex-1 rounded-xl border border-red-600/60 bg-red-600/10 py-3 text-sm font-semibold text-red-400 hover:bg-red-600/20 hover:border-red-600/80 transition-all focus:outline-none focus:ring-2 focus:ring-red-500"
                  >
                    <span v-if="!abandoning">Si, abandonar</span>
                    <span v-else class="inline-flex items-center gap-2">
                      <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                      Saliendo...
                    </span>
                  </button>
                </div>
              </div>
            </Transition>
          </div>
        </Transition>

      </template>

      <!-- ════════════════════════════════════════════════ -->
      <!-- ONBOARDING TUTORIAL                              -->
      <!-- ════════════════════════════════════════════════ -->
      <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div
          v-if="showTutorial"
          class="fixed inset-0 z-[80] flex items-end justify-center bg-black/70 px-4 pb-6"
          @keydown.escape="dismissTutorial"
        >
          <div class="w-full max-w-sm rounded-2xl border border-wc-border bg-wc-bg p-6 shadow-2xl">
            <!-- Header -->
            <div class="flex items-center justify-between mb-4">
              <h3 class="font-display text-lg tracking-widest text-wc-text">COMO ENTRENAR</h3>
              <button @click="dismissTutorial" class="text-wc-text-tertiary hover:text-wc-text transition-colors" aria-label="Cerrar tutorial">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
              </button>
            </div>

            <!-- Step 1 -->
            <div v-show="tutorialStep === 1">
              <div class="flex items-start gap-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent text-white font-bold text-sm">1</div>
                <div>
                  <p class="font-semibold text-wc-text text-sm">Ajusta peso y reps</p>
                  <p class="mt-1 text-xs text-wc-text-secondary leading-relaxed">Usa los botones <span class="font-mono text-wc-accent">-</span> <span class="font-mono text-wc-accent">+</span> para ajustar el peso y las repeticiones de cada serie antes de ejecutarla.</p>
                </div>
              </div>
            </div>

            <!-- Step 2 -->
            <div v-show="tutorialStep === 2">
              <div class="flex items-start gap-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent text-white font-bold text-sm">2</div>
                <div>
                  <p class="font-semibold text-wc-text text-sm">Marca cada serie</p>
                  <p class="mt-1 text-xs text-wc-text-secondary leading-relaxed">Cuando termines una serie, toca el boton para registrarla. Se guarda automaticamente con tu peso y reps.</p>
                </div>
              </div>
            </div>

            <!-- Step 3 -->
            <div v-show="tutorialStep === 3">
              <div class="flex items-start gap-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent text-white font-bold text-sm">3</div>
                <div>
                  <p class="font-semibold text-wc-text text-sm">Completa la sesion</p>
                  <p class="mt-1 text-xs text-wc-text-secondary leading-relaxed">Al terminar (o si debes irte), toca <strong class="text-wc-text">COMPLETAR SESION</strong>. Con solo una serie registrada puedes guardar tu progreso.</p>
                </div>
              </div>
            </div>

            <!-- Progress dots -->
            <div class="mt-4 flex justify-center gap-1.5">
              <div
                v-for="i in TUTORIAL_TOTAL"
                :key="'dot-' + i"
                class="h-1.5 rounded-full transition-all"
                :class="i === tutorialStep ? 'bg-wc-accent w-4' : 'bg-wc-bg-tertiary w-1.5'"
              ></div>
            </div>

            <!-- Navigation -->
            <div class="mt-5 flex gap-3">
              <button
                v-if="tutorialStep > 1"
                @click="tutorialStep--"
                class="flex-1 rounded-xl border border-wc-border bg-wc-bg-secondary py-2.5 text-sm font-medium text-wc-text-secondary hover:text-wc-text transition-colors"
              >Atras</button>
              <button
                v-if="tutorialStep < TUTORIAL_TOTAL"
                @click="tutorialStep++"
                class="flex-1 rounded-xl bg-wc-accent py-2.5 text-sm font-semibold text-white hover:bg-red-700 transition-colors"
              >Siguiente</button>
              <button
                v-if="tutorialStep === TUTORIAL_TOTAL"
                @click="dismissTutorial"
                class="flex-1 rounded-xl bg-wc-accent py-2.5 text-sm font-semibold text-white hover:bg-red-700 transition-colors"
              >Listo, a entrenar!</button>
            </div>
          </div>
        </div>
      </Transition>

    </div>

    <!-- Modal de media del ejercicio -->
    <ExerciseMediaModal
      :exercise="mediaModal.exercise"
      :show="mediaModal.show"
      @close="closeMedia"
    />
  </ClientLayout>
</template>

<style scoped>
/* Hide number input spinners */
input[type="number"]::-webkit-outer-spin-button,
input[type="number"]::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
input[type="number"] {
  -moz-appearance: textfield;
}

/* Hide scrollbar for day pills */
.scrollbar-none::-webkit-scrollbar { display: none; }
.scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }

/* Safe area padding for iOS bottom bar */
.safe-area-pb { padding-bottom: env(safe-area-inset-bottom, 0px); }

/* PR badge golden gradient */
.pr-badge {
  background: linear-gradient(135deg, #facc15, #f59e0b);
  animation: prPulse 1s ease-out;
}

@keyframes prPulse {
  0%   { transform: scale(1); box-shadow: 0 0 0 0 rgba(250,204,21,0.5); }
  50%  { transform: scale(1.15); box-shadow: 0 0 0 4px rgba(250,204,21,0); }
  100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(250,204,21,0); }
}
</style>
