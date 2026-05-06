<script setup>
/**
 * WorkoutPlayerV2.vue — Redesign visual de la sesión de entrenamiento.
 *
 * Misma lógica funcional que WorkoutPlayer.vue (legacy), pero el template
 * está descompuesto en sub-componentes (WorkoutHero, ExerciseCard, SetRow,
 * RestTimerCard, etc.). Mantiene 100% de la API y comportamiento del legacy.
 *
 * Activación: feature flag `workout_player_v2` (ver useFeatureFlag.js).
 */
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useApi } from '../../composables/useApi';
import { useToast } from '../../composables/useToast';
import { usePlanLock } from '../../composables/usePlanLock';
import { useVoiceLogger } from '../../composables/useVoiceLogger.js';
import { useWorkoutProgress } from '../../composables/useWorkoutProgress';

import ClientLayout from '../../layouts/ClientLayout.vue';
import LockOverlay from '../../components/LockOverlay.vue';
import ExerciseMediaModal from '../../components/workout/ExerciseMediaModal.vue';
import DayPickerStrip from '../../components/workout/DayPickerStrip.vue';
import WorkoutHero from '../../components/workout/WorkoutHero.vue';
import ExerciseCard from '../../components/workout/ExerciseCard.vue';
import RestTimerCard from '../../components/workout/RestTimerCard.vue';
import WorkoutBottomBar from '../../components/workout/WorkoutBottomBar.vue';

const api = useApi();
const toast = useToast();
const route = useRoute();
const router = useRouter();
const { isLocked } = usePlanLock();

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
const starting = ref(false);

// ── Voice logger ──
const {
    engine: voiceEngine,
    listening: voiceListening,
    isProcessing: voiceIsProcessing,
    confirmation: voiceConfirmation,
    error: voiceError,
    startListening: voiceStartListeningFn,
    stopListening: voiceStopListening,
    cancel: voiceCancel,
} = useVoiceLogger();
const voiceExIndex = ref(null);

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

// UI state per exercise
const expandedNotes = ref({});
const showActiveMedia = ref({});
const activeVariations = ref({});

// Exercise media modal
const mediaModal = ref({ show: false, exercise: null });
function openMedia(ex) { mediaModal.value = { show: true, exercise: ex }; }
function closeMedia()  { mediaModal.value = { show: false, exercise: null }; }

// ── Mobile detection (reactive) ──
const isMobile = ref(false);
function checkMobile() {
    isMobile.value = typeof window !== 'undefined' && window.matchMedia('(max-width: 1023px)').matches;
}

// ── Timers ──
let timerInterval = null;
let restInterval = null;
let audioCtx = null;

const elapsed = ref(0);
const restSeconds = ref(0);
const restTotal = ref(0);
const showRestTimer = ref(false);
const restPaused = ref(false);
let restPausedAt = 0;
let restPausedDuration = 0;

// ── Computed ──
const currentDay = computed(() => days.value[currentDayIndex.value] || null);

const dayWarmup = computed(() => {
    const d = currentDay.value;
    return d?.calentamiento || d?.warmup || null;
});

const dayPriority = computed(() => {
    const d = currentDay.value;
    if (!d) return null;
    const raw = d.prioridad || d.priority || d.objetivo || d.tipo || d.focus || d.estimulo || null;
    if (!raw || typeof raw !== 'string') return null;
    return raw.trim() || null;
});

// Progress aggregates via composable
const {
    progressPct,
    completedSetsCount,
    totalSetsCount,
    completedExercisesCount,
    totalRepsAll,
    maxWeightAll,
    totalVolumeKg,
    currentExerciseIndex,
    exerciseStates,
} = useWorkoutProgress(exercises, setData);

const elapsedDisplay = computed(() => {
    const total = Math.max(0, elapsed.value);
    const h = Math.floor(total / 3600);
    const m = Math.floor((total % 3600) / 60);
    const s = total % 60;
    const mStr = String(m).padStart(2, '0');
    const sStr = String(s).padStart(2, '0');
    return (h > 0 ? `${h}:` : '') + `${mStr}:${sStr}`;
});

const restCard = computed(() => {
    if (!showRestTimer.value) return null;
    const next = exercises.value[currentExerciseIndex.value];
    if (!next) return null;
    const sets = getSetRows(currentExerciseIndex.value);
    const nextSetIdx = sets.findIndex(s => !s.completed);
    const nextSet = nextSetIdx >= 0 ? sets[nextSetIdx] : null;
    const nextSetTarget = nextSet
        ? `Objetivo: ${nextSet.target_weight ? nextSet.target_weight + weightUnit.value + ' × ' : ''}${nextSet.target_reps || exReps(next) || ''}${exRir(next) !== null ? ' · RIR ' + exRir(next) : ''}`
        : '';
    return {
        nextExercise: exName(next),
        nextSetNumber: nextSetIdx >= 0 ? nextSetIdx + 1 : 0,
        nextSetTarget,
    };
});

// ── Block type helpers ──
function getBlockType(ex) {
    return (ex?.bloque || ex?.block_type || 'normal').toLowerCase();
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

// ── Field extractors ──
function exName(ex)      { return ex?.nombre || ex?.name || ex?.ejercicio || 'Ejercicio'; }
function exSeries(ex)    { return ex?.series || ex?.sets || null; }
function exReps(ex)      { return ex?.repeticiones || ex?.reps || null; }
function exDescanso(ex)  { return ex?.descanso || ex?.rest || ex?.rest_seconds || null; }
function exRir(ex)       { return (ex?.rir !== undefined && ex?.rir !== null) ? ex.rir : null; }
function exImageUrl(ex)  { return ex?.image_url || ex?.gif_url || ex?.imagen || ex?.thumbnail_url || null; }
function exVideoUrl(ex)  { return ex?.video_url || ex?.video || null; }
function exIsCardio(ex)  { return !!ex?.is_cardio; }

function parseRestSeconds(rest) {
    if (!rest) return 90;
    const str = String(rest).trim().toLowerCase();
    let m = str.match(/^(\d+)\s*s(eg)?$/i); if (m) return parseInt(m[1]);
    m = str.match(/^(\d+)\s*min$/i);         if (m) return parseInt(m[1]) * 60;
    m = str.match(/^(\d+):(\d{2})$/);         if (m) return parseInt(m[1]) * 60 + parseInt(m[2]);
    if (!isNaN(str)) return parseInt(str);
    m = str.match(/(\d+)/);
    if (m) {
        const n = parseInt(m[1]);
        return str.includes('min') ? n * 60 : n;
    }
    return 90;
}

function parseInitialReps(repsStr) {
    if (!repsStr) return 10;
    const m = String(repsStr).match(/\d+/);
    return m ? parseInt(m[0]) : 10;
}

function weightStep() {
    return weightUnit.value === 'lbs' ? 5 : 2.5;
}
function weightToKg(weight) {
    if (weightUnit.value === 'lbs') return +(weight / 2.205).toFixed(2);
    return weight;
}

// ── Set management ──
function getSetRows(exIndex) {
    const ex = exercises.value[exIndex];
    if (!ex) return [];
    const total = parseInt(exSeries(ex) || 3) || 3;
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

// ── Variation toggle ──
function toggleVariation(exIndex) {
    activeVariations.value[exIndex] = !activeVariations.value[exIndex];
}
function isVariationActive(exIndex) {
    return !!activeVariations.value[exIndex];
}

// ── Notes / media toggles ──
function toggleNotes(exIndex) {
    expandedNotes.value[exIndex] = !expandedNotes.value[exIndex];
}
function toggleActiveMedia(exIndex) {
    showActiveMedia.value[exIndex] = !showActiveMedia.value[exIndex];
}

// ── Set complete / uncomplete ──
async function toggleSet(exIndex, setIndex) {
    const sets = getSetRows(exIndex);
    const set = sets[setIndex];
    const prevCompleted = set.completed;
    set.completed = !prevCompleted;

    if (set.completed && workoutStarted.value) {
        if (!sessionId.value) {
            set.completed = prevCompleted;
            toast.warn('Espera un momento, iniciando entrenamiento...');
            return;
        }
        if (set._saving) {
            set.completed = prevCompleted;
            return;
        }
        set._saving = true;

        const reps = parseInt(set.reps) || 0;
        if (reps <= 0) {
            set.completed = false;
            set._saving = false;
            toast.warn('Ingresa las repeticiones antes de marcar.');
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
                reps,
                unit: weightUnit.value,
            });
            if (response.data?.is_pr) set.is_pr = true;
        } catch (err) {
            set.completed = prevCompleted;
            toast.apiError(err, 'No pudimos guardar ese set. Verifica tu conexion.');
            return;
        } finally {
            set._saving = false;
        }

        const ex = exercises.value[exIndex];
        const rest = exDescanso(ex);
        if (rest) startRestTimer(parseRestSeconds(rest));
    } else if (!set.completed && workoutStarted.value) {
        try {
            await api.post('/api/v/client/workout/uncomplete-set', {
                session_id: sessionId.value,
                exercise_index: exIndex,
                exercise_name: exName(exercises.value[exIndex]),
                set_index: setIndex,
                set_number: setIndex + 1,
            });
        } catch (err) {
            set.completed = prevCompleted;
            toast.apiError(err, 'No pudimos deshacer el set. Intenta de nuevo.');
        }
    }
}

async function completeCardioSet({ exIndex, sIdx }) {
    const sets = getSetRows(exIndex);
    const set = sets[sIdx];
    const duration = parseInt(set.duration) || 0;
    const speed = parseFloat(set.speed) || 0;
    const incline = parseFloat(set.incline) || 0;

    if (duration <= 0) {
        toast.warn('Ingresa la duración antes de marcar.');
        return;
    }

    set.completed = true;
    if (navigator.vibrate) navigator.vibrate(50);
    set.reps = duration;

    if (workoutStarted.value) {
        try {
            await api.post('/api/v/client/workout/complete-set', {
                session_id: sessionId.value,
                exercise_index: exIndex,
                set_number: sIdx + 1,
                exercise_name: exName(exercises.value[exIndex]),
                is_cardio: true,
                duration_minutes: duration,
                speed_kmh: speed,
                incline_percent: incline,
                reps: duration,
            });
        } catch (err) {
            set.completed = false;
            toast.apiError(err, 'No pudimos guardar ese set.');
        }
    }
}

async function uncompleteSet({ exIndex, sIdx }) {
    const sets = getSetRows(exIndex);
    const set = sets[sIdx];
    set.completed = false;
    if (workoutStarted.value) {
        try {
            await api.post('/api/v/client/workout/uncomplete-set', {
                session_id: sessionId.value,
                exercise_index: exIndex,
                exercise_name: exName(exercises.value[exIndex]),
                set_index: sIdx,
                set_number: sIdx + 1,
            });
        } catch (err) {
            set.completed = true;
            toast.apiError(err, 'No pudimos deshacer ese set.');
        }
    }
}

// ── Set field update from SetRow ──
function onUpdateSetField({ exIndex, sIdx, field, value }) {
    const sets = getSetRows(exIndex);
    if (!sets[sIdx]) return;
    sets[sIdx][field] = value;
}

// ── Voice logger handlers ──
function voiceStart(exIndex) {
    voiceExIndex.value = exIndex;
    voiceStartListeningFn();
}
function voiceStop() {
    voiceStopListening();
}
async function voiceConfirm() {
    if (!voiceConfirmation.value || voiceIsProcessing.value) return;
    const { intent, weight, reps, unit } = voiceConfirmation.value;

    if (intent === 'complete_only') {
        voiceConfirmation.value = null;
        return;
    }

    const sets = getSetRows(voiceExIndex.value);
    const setIdx = sets.findIndex(s => !s.completed);
    if (setIdx === -1) { voiceCancel(); return; }

    const saveWeight = unit === 'lbs' ? +(weight / 2.205).toFixed(2) : weight;
    sets[setIdx].weight = String(saveWeight);
    sets[setIdx].reps = String(reps);

    voiceIsProcessing.value = true;
    try {
        await toggleSet(voiceExIndex.value, setIdx);
        voiceConfirmation.value = null;
        voiceExIndex.value = null;
    } finally {
        voiceIsProcessing.value = false;
    }
}
function voiceOnCancel() {
    voiceCancel();
}

// ── Rest timer ──
let restStartedAt = 0;
let restDurationTotal = 0;
function startRestTimer(seconds) {
    clearInterval(restInterval);
    restDurationTotal = seconds;
    restStartedAt = Date.now();
    restPausedDuration = 0;
    restPaused.value = false;
    restTotal.value = seconds;
    restSeconds.value = seconds;
    showRestTimer.value = true;
    playBeep(440, 0.10);
    restInterval = setInterval(() => {
        if (restPaused.value) return;
        const elapsedRest = Math.floor((Date.now() - restStartedAt - restPausedDuration) / 1000);
        const remaining = Math.max(0, restDurationTotal - elapsedRest);
        restSeconds.value = remaining;
        if (remaining <= 0) {
            clearInterval(restInterval);
            showRestTimer.value = false;
            playBeep(880, 0.15);
            setTimeout(() => playBeep(880, 0.15), 200);
            return;
        }
        if (remaining <= 3 && remaining > 0) playBeep(660, 0.08);
    }, 500);
}
function clearRestTimer() {
    clearInterval(restInterval);
    showRestTimer.value = false;
    restSeconds.value = 0;
    restPaused.value = false;
}
function pauseRestTimer() {
    if (!showRestTimer.value || restPaused.value) return;
    restPaused.value = true;
    restPausedAt = Date.now();
}
function resumeRestTimer() {
    if (!showRestTimer.value || !restPaused.value) return;
    restPausedDuration += Date.now() - restPausedAt;
    restPaused.value = false;
}
function adjustRest(delta) {
    if (!showRestTimer.value) return;
    restDurationTotal = Math.max(1, restDurationTotal + delta);
    restTotal.value = restDurationTotal;
    restSeconds.value = Math.max(1, restSeconds.value + delta);
}

// ── Workout session timer ──
let workoutStartTimestamp = 0;
function startTimer() {
    workoutStartTimestamp = Date.now();
    elapsed.value = 0;
    clearInterval(timerInterval);
    timerInterval = setInterval(() => {
        elapsed.value = Math.floor((Date.now() - workoutStartTimestamp) / 1000);
    }, 1000);
}
function resumeTimer(startTime) {
    workoutStartTimestamp = new Date(startTime).getTime();
    elapsed.value = Math.max(0, Math.floor((Date.now() - workoutStartTimestamp) / 1000));
    clearInterval(timerInterval);
    timerInterval = setInterval(() => {
        elapsed.value = Math.floor((Date.now() - workoutStartTimestamp) / 1000);
    }, 1000);
}
function stopTimer() { clearInterval(timerInterval); }

function handleVisibilityChange() {
    if (document.visibilityState === 'visible') {
        if (workoutStarted.value && workoutStartTimestamp > 0) {
            elapsed.value = Math.floor((Date.now() - workoutStartTimestamp) / 1000);
        }
        if (showRestTimer.value && restStartedAt > 0 && !restPaused.value) {
            const elapsedRest = Math.floor((Date.now() - restStartedAt - restPausedDuration) / 1000);
            const remaining = Math.max(0, restDurationTotal - elapsedRest);
            restSeconds.value = remaining;
            if (remaining <= 0) {
                clearInterval(restInterval);
                showRestTimer.value = false;
            }
        }
    }
}

// ── Weight unit ──
function setWeightUnit(unit) {
    weightUnit.value = unit;
    localStorage.setItem('wc_weight_unit', unit);
}

// ── Audio ──
function playBeep(freq = 880, duration = 0.15) {
    try {
        if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
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

// ── Day / week switching ──
function switchDay(index) {
    if (workoutStarted.value && currentDayIndex.value !== index) return;
    currentDayIndex.value = index;
    clearRestTimer();
    loadDayExercises();
}
async function switchWeek(week) {
    if (workoutStarted.value || !hasProgressions.value) return;
    if (week < 1 || week > totalWeeks.value) return;
    currentWeek.value = week;
    loading.value = true;
    try {
        const response = await api.get(`/api/v/client/workout/1?week=${week}`);
        const d = response.data;
        days.value = d.days || [];
        currentDayIndex.value = 0;
        loadDayExercises();
    } catch (err) {
        toast.apiError(err, 'No se pudo cargar esa semana.');
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
    exercises.value.forEach((_, idx) => { getSetRows(idx); });
}

// ── Tutorial ──
function dismissTutorial() {
    showTutorial.value = false;
    try { api.post('/api/v/client/workout/dismiss-tutorial'); }
    catch { /* silent */ }
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

        if (d.hasProgressions) {
            hasProgressions.value = true;
            currentWeek.value = d.currentWeek || 1;
            totalWeeks.value = d.totalWeeks || 1;
        }
        if (d.showTutorial) showTutorial.value = true;

        loadDayExercises();

        if (d.activeSession) {
            sessionId.value = d.activeSession.id;
            workoutStarted.value = true;
            if (d.activeSession.startTime) resumeTimer(d.activeSession.startTime);
            else {
                elapsed.value = d.activeSession.elapsed || 0;
                startTimer();
            }
            if (d.activeSession.setData) setData.value = d.activeSession.setData;
        }
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al cargar el entrenamiento';
    } finally {
        loading.value = false;
    }
}

async function startWorkout() {
    if (starting.value || workoutStarted.value) return;
    starting.value = true;
    startTimer();
    try {
        const response = await api.post('/api/v/client/workout/start', {
            day_index: currentDayIndex.value,
            week: hasProgressions.value ? currentWeek.value : null,
        });
        sessionId.value = response.data.session_id || null;
        workoutStarted.value = true;
        if (response.data.setData) setData.value = response.data.setData;
    } catch (err) {
        workoutStarted.value = false;
        stopTimer();
        toast.apiError(err, 'No pudimos iniciar tu entrenamiento.');
    } finally {
        starting.value = false;
    }
    await nextTick();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function finishWorkout() {
    if (saving.value) return;
    if (navigator.vibrate) navigator.vibrate([50, 30, 100]);
    saving.value = true;
    try {
        const response = await api.post('/api/v/client/workout/finish', {
            session_id: sessionId.value,
            elapsed: elapsed.value,
            set_data: setData.value,
        });
        const sid = response.data.session_id || sessionId.value;
        stopTimer();
        clearRestTimer();
        router.push({ name: 'client-workout-summary', params: { sessionId: sid } });
    } catch (err) {
        toast.apiError(err, 'No pudimos finalizar tu entrenamiento.');
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
        await api.post('/api/v/client/workout/abandon', { session_id: sessionId.value });
        toast.info('Entrenamiento abandonado.');
    } catch (err) {
        toast.apiError(err, 'No pudimos registrar el abandono.');
    } finally {
        abandoning.value = false;
    }
    workoutStarted.value = false;
    sessionId.value = null;
    setData.value = {};
    elapsed.value = 0;
    loadDayExercises();
}

function goBack() {
    router.push({ name: 'client-dashboard' }).catch(() => {
        router.push('/client').catch(() => {});
    });
}

// ── Lifecycle ──
onMounted(() => {
    fetchWorkout();
    checkMobile();
    if (typeof document !== 'undefined') {
        document.addEventListener('visibilitychange', handleVisibilityChange);
    }
    if (typeof window !== 'undefined') {
        window.addEventListener('resize', checkMobile);
    }
});

onBeforeUnmount(() => {
    stopTimer();
    clearRestTimer();
    voiceStopListening();
    if (typeof document !== 'undefined') {
        document.removeEventListener('visibilitychange', handleVisibilityChange);
    }
    if (typeof window !== 'undefined') {
        window.removeEventListener('resize', checkMobile);
    }
    if (audioCtx) {
        audioCtx.close().catch(() => {});
        audioCtx = null;
    }
});
</script>

<template>
  <ClientLayout>
    <div class="wcv2-shell" data-component-version="v2">
      <div class="relative">
        <LockOverlay v-if="isLocked" />
        <div :class="isLocked ? 'pointer-events-none blur-sm select-none' : ''" :aria-hidden="isLocked ? 'true' : undefined">

          <!-- LOADING -->
          <template v-if="loading">
            <div class="space-y-4 animate-pulse p-4">
              <div class="h-16 rounded-xl bg-wc-bg-tertiary"></div>
              <div class="h-24 rounded-xl bg-wc-bg-tertiary"></div>
              <div class="h-48 rounded-xl bg-wc-bg-tertiary"></div>
              <div class="h-48 rounded-xl bg-wc-bg-tertiary"></div>
            </div>
          </template>

          <!-- ERROR -->
          <div v-else-if="error" class="flex min-h-[60vh] items-center justify-center px-4">
            <div class="w-full max-w-sm rounded-2xl border border-red-500/30 bg-red-500/10 p-8 text-center">
              <p class="text-sm text-red-400">{{ error }}</p>
              <button @click="fetchWorkout" class="mt-4 inline-flex items-center justify-center rounded-xl bg-wc-accent px-5 py-2.5 text-sm font-semibold text-white">
                Reintentar
              </button>
            </div>
          </div>

          <!-- EMPTY -->
          <div v-else-if="!days.length" class="flex min-h-[60vh] items-center justify-center px-4">
            <div class="w-full max-w-sm rounded-2xl border border-wc-border bg-wc-bg-tertiary p-10 text-center">
              <h2 class="font-display text-2xl tracking-wide text-wc-text">TU PLAN VIENE EN CAMINO</h2>
              <p class="mt-3 text-sm text-wc-text-secondary">Tu coach está preparando tu plan personalizado.</p>
            </div>
          </div>

          <!-- MAIN -->
          <template v-else>
            <div class="page">
              <DayPickerStrip
                :days="days"
                :current-day-index="currentDayIndex"
                :workout-started="workoutStarted"
                :has-progressions="hasProgressions"
                :current-week="currentWeek"
                :total-weeks="totalWeeks"
                @change-day="switchDay"
                @change-week="switchWeek"
                @back="goBack"
              />

              <!-- HERO en sesión activa -->
              <WorkoutHero
                v-if="workoutStarted"
                :elapsed-display="elapsedDisplay"
                :progress-pct="progressPct"
                :completed-exercises="completedExercisesCount"
                :total-exercises="exercises.length"
                :phase-label="dayPriority || ''"
                :exercises="exercises"
                :current-exercise-index="currentExerciseIndex"
                :exercise-states="exerciseStates"
              />

              <!-- PRE-WORKOUT — info bar + warmup + start CTA -->
              <div v-if="!workoutStarted" class="pre-workout space-y-4 mt-4">
                <div class="info-bar rounded-xl border border-wc-border bg-wc-bg-tertiary px-4 py-3">
                  <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm">
                    <span class="inline-flex items-center gap-2 text-wc-text-secondary">
                      <strong class="font-data text-wc-text">{{ exercises.length }}</strong> ejercicios
                    </span>
                    <span class="meta-sep" aria-hidden="true"></span>
                    <span class="inline-flex items-center gap-2 text-wc-text-secondary">
                      <strong class="font-data text-wc-text">{{ Math.max(exercises.length * 8, 20) }}</strong> min estimado
                    </span>
                    <template v-if="dayPriority">
                      <span class="meta-sep" aria-hidden="true"></span>
                      <span class="inline-flex items-center gap-2 text-wc-text-secondary">
                        Prioridad: <strong class="font-semibold text-wc-text">{{ dayPriority }}</strong>
                      </span>
                    </template>
                  </div>
                </div>

                <!-- Warmup card -->
                <div v-if="dayWarmup" class="warmup-card rounded-2xl border border-amber-500/25 px-4 py-4">
                  <h3 class="font-display text-sm tracking-widest text-amber-400 uppercase mb-2">Calentamiento</h3>
                  <p class="text-sm text-wc-text-secondary leading-relaxed">{{ dayWarmup }}</p>
                </div>

                <!-- Weight unit toggle -->
                <div class="flex items-center justify-end gap-2">
                  <span class="text-xs text-wc-text-tertiary uppercase tracking-wider font-display">Unidad</span>
                  <div class="inline-flex rounded-full border border-wc-border overflow-hidden">
                    <button
                      type="button"
                      class="px-4 py-1.5 text-xs font-display tracking-wider"
                      :class="weightUnit === 'kg' ? 'bg-wc-accent text-white' : 'text-wc-text-secondary hover:text-wc-text'"
                      @click="setWeightUnit('kg')"
                    >KG</button>
                    <button
                      type="button"
                      class="px-4 py-1.5 text-xs font-display tracking-wider"
                      :class="weightUnit === 'lbs' ? 'bg-wc-accent text-white' : 'text-wc-text-secondary hover:text-wc-text'"
                      @click="setWeightUnit('lbs')"
                    >LBS</button>
                  </div>
                </div>

                <!-- Exercise preview cards (compact) -->
                <div class="space-y-3">
                  <ExerciseCard
                    v-for="(ex, idx) in exercises"
                    :key="`pre-${idx}`"
                    :exercise-index="idx"
                    :exercise="ex"
                    :sets="getSetRows(idx)"
                    :weight-unit="weightUnit"
                    state="upcoming"
                    :block-type="getBlockType(ex)"
                    :is-first-in-block="isFirstInBlock(idx)"
                    @open-media="openMedia(ex)"
                  />
                </div>

                <!-- Start CTA -->
                <button
                  type="button"
                  class="start-cta w-full rounded-2xl bg-wc-accent text-white font-display font-bold uppercase tracking-widest py-5 text-base shadow-lg shadow-wc-accent/30"
                  :disabled="starting"
                  @click="startWorkout"
                >
                  <span v-if="!starting">Iniciar entrenamiento</span>
                  <span v-else>Iniciando…</span>
                </button>
              </div>

              <!-- ACTIVE STATE — Content grid -->
              <div v-else class="content mt-4">
                <div class="col-main space-y-4">
                  <!-- Rest timer card inline (solo desktop; mobile usa floating) -->
                  <RestTimerCard
                    v-if="showRestTimer && restCard && !isMobile"
                    :seconds-remaining="restSeconds"
                    :total-seconds="restTotal"
                    :next-exercise="restCard.nextExercise"
                    :next-set-number="restCard.nextSetNumber"
                    :next-set-target="restCard.nextSetTarget"
                    :is-paused="restPaused"
                    @skip="clearRestTimer"
                    @pause="pauseRestTimer"
                    @resume="resumeRestTimer"
                    @add-15="adjustRest(15)"
                    @subtract-15="adjustRest(-15)"
                  />

                  <ExerciseCard
                    v-for="(ex, idx) in exercises"
                    :key="`active-${idx}`"
                    :exercise-index="idx"
                    :exercise="ex"
                    :sets="getSetRows(idx)"
                    :weight-unit="weightUnit"
                    :state="exerciseStates[idx]"
                    :block-type="getBlockType(ex)"
                    :is-first-in-block="isFirstInBlock(idx)"
                    :is-variation-active="isVariationActive(idx)"
                    :notes-expanded="!!expandedNotes[idx]"
                    :show-active-media="!!showActiveMedia[idx]"
                    :voice-engine="!!voiceEngine"
                    :voice-listening="voiceListening"
                    :voice-is-processing="voiceIsProcessing"
                    :voice-confirmation="voiceConfirmation"
                    :voice-error="voiceError"
                    :voice-ex-index="voiceExIndex"
                    @toggle-set="(p) => toggleSet(p.exIndex, p.sIdx)"
                    @complete-cardio-set="completeCardioSet"
                    @uncomplete-set="uncompleteSet"
                    @update-set-field="onUpdateSetField"
                    @open-media="openMedia(ex)"
                    @toggle-active-media="toggleActiveMedia(idx)"
                    @start-rest="startRestTimer"
                    @voice-start="voiceStart"
                    @voice-stop="voiceStop"
                    @voice-confirm="voiceConfirm"
                    @voice-cancel="voiceOnCancel"
                    @notes-toggle="toggleNotes(idx)"
                    @variation-toggle="toggleVariation(idx)"
                  />
                </div>
              </div>
            </div>

            <!-- REST TIMER FLOATING (mobile) — widget compacto siempre visible -->
            <Transition name="rest-float">
              <div
                v-if="showRestTimer && restCard && isMobile"
                class="rest-floating"
              >
                <RestTimerCard
                  compact
                  :seconds-remaining="restSeconds"
                  :total-seconds="restTotal"
                  :next-exercise="restCard.nextExercise"
                  :next-set-number="restCard.nextSetNumber"
                  :next-set-target="restCard.nextSetTarget"
                  :is-paused="restPaused"
                  @skip="clearRestTimer"
                  @pause="pauseRestTimer"
                  @resume="resumeRestTimer"
                  @add-15="adjustRest(15)"
                  @subtract-15="adjustRest(-15)"
                />
              </div>
            </Transition>

            <!-- BOTTOM BAR -->
            <WorkoutBottomBar
              v-if="workoutStarted"
              :elapsed-display="elapsedDisplay"
              :total-volume="totalVolumeKg"
              :completed-sets="completedSetsCount"
              :total-sets="totalSetsCount"
              :progress-pct="progressPct"
              :can-finish="completedSetsCount > 0"
              :saving="saving"
              :weight-unit="weightUnit"
              @abandon="confirmAbandon = true"
              @finish="finishWorkout"
            />

            <!-- ABANDON DIALOG -->
            <Transition name="fade">
              <div
                v-if="confirmAbandon"
                class="fixed inset-0 z-[60] flex items-end justify-center bg-black/60 px-4 pb-8 backdrop-blur-sm"
                role="dialog"
                aria-modal="true"
                aria-labelledby="abandon-dialog-title"
                @keydown.escape="confirmAbandon = false"
              >
                <div class="w-full max-w-sm rounded-2xl border border-wc-border bg-wc-bg p-6 shadow-2xl">
                  <h3 id="abandon-dialog-title" class="font-display text-xl tracking-wide text-wc-text">ABANDONAR SESIÓN</h3>
                  <p class="mt-2 text-sm text-wc-text-secondary">Tu progreso parcial se conservará. Los sets completados ya están guardados.</p>
                  <div class="mt-5 flex gap-3">
                    <button
                      type="button"
                      class="flex-1 rounded-xl border border-wc-border bg-wc-bg-secondary py-3 text-sm font-medium text-wc-text-secondary hover:text-wc-text"
                      @click="confirmAbandon = false"
                    >Cancelar</button>
                    <button
                      type="button"
                      class="flex-1 rounded-xl border border-red-600/60 bg-red-600/10 py-3 text-sm font-semibold text-red-400 hover:bg-red-600/20"
                      :disabled="abandoning"
                      @click="abandonWorkout"
                    >
                      {{ abandoning ? 'Saliendo…' : 'Sí, abandonar' }}
                    </button>
                  </div>
                </div>
              </div>
            </Transition>

            <!-- TUTORIAL -->
            <Transition name="fade">
              <div
                v-if="showTutorial"
                class="fixed inset-0 z-[80] flex items-end justify-center bg-black/70 px-4 pb-6"
                @keydown.escape="dismissTutorial"
              >
                <div class="w-full max-w-sm rounded-2xl border border-wc-border bg-wc-bg p-6 shadow-2xl">
                  <div class="flex items-center justify-between mb-4">
                    <h3 class="font-display text-lg tracking-widest text-wc-text">CÓMO ENTRENAR</h3>
                    <button @click="dismissTutorial" class="text-wc-text-tertiary hover:text-wc-text" aria-label="Cerrar tutorial">✕</button>
                  </div>

                  <div v-show="tutorialStep === 1">
                    <div class="flex items-start gap-4">
                      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent text-white font-bold text-sm">1</div>
                      <div>
                        <p class="font-semibold text-wc-text text-sm">Ajusta peso y reps</p>
                        <p class="mt-1 text-xs text-wc-text-secondary leading-relaxed">Usa los botones <span class="font-mono text-wc-accent">−</span> <span class="font-mono text-wc-accent">+</span> para ajustar el peso y reps de cada serie.</p>
                      </div>
                    </div>
                  </div>
                  <div v-show="tutorialStep === 2">
                    <div class="flex items-start gap-4">
                      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent text-white font-bold text-sm">2</div>
                      <div>
                        <p class="font-semibold text-wc-text text-sm">Marca cada serie</p>
                        <p class="mt-1 text-xs text-wc-text-secondary leading-relaxed">Cuando termines una serie, toca el botón ✓ para registrarla.</p>
                      </div>
                    </div>
                  </div>
                  <div v-show="tutorialStep === 3">
                    <div class="flex items-start gap-4">
                      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent text-white font-bold text-sm">3</div>
                      <div>
                        <p class="font-semibold text-wc-text text-sm">Completa la sesión</p>
                        <p class="mt-1 text-xs text-wc-text-secondary leading-relaxed">Al terminar (o si debes irte), toca <strong class="text-wc-text">Finalizar</strong>.</p>
                      </div>
                    </div>
                  </div>

                  <div class="mt-4 flex justify-center gap-1.5">
                    <div
                      v-for="i in TUTORIAL_TOTAL"
                      :key="`tdot-${i}`"
                      class="h-1.5 rounded-full transition-all"
                      :class="i === tutorialStep ? 'bg-wc-accent w-4' : 'bg-wc-bg-tertiary w-1.5'"
                    ></div>
                  </div>
                  <div class="mt-5 flex gap-3">
                    <button v-if="tutorialStep > 1" @click="tutorialStep--" class="flex-1 rounded-xl border border-wc-border bg-wc-bg-secondary py-2.5 text-sm font-medium text-wc-text-secondary hover:text-wc-text">Atrás</button>
                    <button v-if="tutorialStep < TUTORIAL_TOTAL" @click="tutorialStep++" class="flex-1 rounded-xl bg-wc-accent text-white py-2.5 text-sm font-semibold uppercase tracking-wider">Siguiente</button>
                    <button v-if="tutorialStep === TUTORIAL_TOTAL" @click="dismissTutorial" class="flex-1 rounded-xl bg-wc-accent text-white py-2.5 text-sm font-semibold uppercase tracking-wider">¡Listo!</button>
                  </div>
                </div>
              </div>
            </Transition>
          </template>
        </div>
      </div>

      <!-- Modal de media del ejercicio -->
      <ExerciseMediaModal
        :exercise="mediaModal.exercise"
        :show="mediaModal.show"
        @close="closeMedia"
      />
    </div>
  </ClientLayout>
</template>

<style scoped>
/* Wrapper local sin cascada wc-shell */
.wcv2-shell { position: relative; }

.page {
  width: 100%;
  max-width: 100%;
  padding: 16px 16px 240px;
  position: relative;
}
@media (min-width: 1024px) {
  .page { padding: 24px 32px 140px; max-width: 1200px; margin: 0 auto; }
}

/* Rest timer floating mobile — widget compacto sobre bottom-bar */
.rest-floating {
  position: fixed;
  left: 10px;
  right: 10px;
  bottom: calc(92px + env(safe-area-inset-bottom));
  z-index: 49;
}
@media (min-width: 1024px) {
  .rest-floating { display: none; }
}

.rest-float-enter-active, .rest-float-leave-active {
  transition: opacity 0.25s var(--ease-out), transform 0.3s var(--ease-out);
}
.rest-float-enter-from, .rest-float-leave-to {
  opacity: 0;
  transform: translateY(24px) scale(0.96);
}

@media (prefers-reduced-motion: reduce) {
  .rest-float-enter-active, .rest-float-leave-active { transition: none; }
}

.meta-sep {
  width: 3px; height: 3px;
  border-radius: 999px;
  background: var(--color-wc-text-tertiary);
}

.warmup-card {
  background: linear-gradient(135deg, rgba(245,158,11,0.08), rgba(245,158,11,0.02));
}

.start-cta {
  border: none;
  cursor: pointer;
  letter-spacing: 0.12em;
  transition: transform 0.15s var(--ease-out), box-shadow 0.15s var(--ease-out);
}
.start-cta:hover { transform: translateY(-1px); box-shadow: 0 12px 32px -8px rgba(220,38,38,0.5); }
.start-cta:active { transform: translateY(0); }
.start-cta:disabled { opacity: 0.6; cursor: not-allowed; }

.content {
  display: flex;
  flex-direction: column;
  gap: 14px;
}
@media (min-width: 1024px) {
  .content {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    gap: 20px;
    align-items: start;
  }
}

.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

@media (prefers-reduced-motion: reduce) {
  .start-cta { transition: none; }
}
</style>
