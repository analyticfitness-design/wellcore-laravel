<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useApi } from '../../composables/useApi';
import RiseLayout from '../../layouts/RiseLayout.vue';

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

const elapsedDisplay = computed(() => {
    const h = Math.floor(elapsed.value / 3600);
    const m = Math.floor((elapsed.value % 3600) / 60);
    const s = elapsed.value % 60;
    const mStr = String(m).padStart(2, '0');
    const sStr = String(s).padStart(2, '0');
    return h > 0 ? `${h}:${mStr}:${sStr}` : `${mStr}:${sStr}`;
});

const restDisplay = computed(() => {
    const m = Math.floor(restSeconds.value / 60);
    const s = restSeconds.value % 60;
    return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
});

async function fetchWorkout() {
    loading.value = true;
    error.value = null;
    try {
        const day = route.params.day || null;
        const url = day ? `/api/v/rise/workout?day=${day}` : '/api/v/rise/workout';
        const response = await api.get(url);
        days.value = response.data.days || [];
        if (response.data.currentDayIndex !== undefined) {
            currentDayIndex.value = response.data.currentDayIndex;
        }
        initExercises();
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al cargar entrenamiento';
    } finally {
        loading.value = false;
    }
}

function initExercises() {
    const currentDay = days.value[currentDayIndex.value];
    if (!currentDay) return;
    exercises.value = currentDay.ejercicios || currentDay.exercises || [];
    // Initialize set data
    const newSetData = {};
    exercises.value.forEach((ex, idx) => {
        const numSets = parseInt(ex.series || ex.sets || 3);
        newSetData[idx] = Array.from({ length: numSets }, () => ({
            weight: '',
            reps: ex.repeticiones || ex.reps || '',
            completed: false,
        }));
    });
    setData.value = newSetData;
}

function selectDay(index) {
    currentDayIndex.value = index;
    initExercises();
}

function startWorkout() {
    workoutStarted.value = true;
    elapsed.value = 0;
    timerInterval = setInterval(() => { elapsed.value++; }, 1000);
}

function completeSet(exIdx, setIdx) {
    if (!setData.value[exIdx]) return;
    setData.value[exIdx][setIdx].completed = !setData.value[exIdx][setIdx].completed;

    if (setData.value[exIdx][setIdx].completed) {
        const ex = exercises.value[exIdx];
        const restTime = parseInt(ex.descanso || ex.rest || 90);
        startRestTimer(restTime);
    }
}

function startRestTimer(seconds) {
    if (restInterval) clearInterval(restInterval);
    restSeconds.value = seconds;
    restTotal.value = seconds;
    showRestTimer.value = true;
    restInterval = setInterval(() => {
        restSeconds.value--;
        if (restSeconds.value <= 0) {
            clearInterval(restInterval);
            restInterval = null;
            showRestTimer.value = false;
        }
    }, 1000);
}

function dismissRestTimer() {
    if (restInterval) clearInterval(restInterval);
    restInterval = null;
    showRestTimer.value = false;
}

async function finishWorkout() {
    if (timerInterval) clearInterval(timerInterval);

    try {
        const response = await api.post('/api/v/rise/workout', {
            day_index: currentDayIndex.value,
            day_name: days.value[currentDayIndex.value]?.nombre || days.value[currentDayIndex.value]?.name || '',
            duration_seconds: elapsed.value,
            sets: setData.value,
            weight_unit: weightUnit.value,
        });
        sessionId.value = response.data.sessionId;
        router.push(`/v/rise/workout-summary/${response.data.sessionId}`);
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al guardar entrenamiento';
    }
}

function convertWeight(kg) {
    if (weightUnit.value === 'lbs') return Math.round(kg * 2.20462);
    return kg;
}

function toggleUnit() {
    weightUnit.value = weightUnit.value === 'kg' ? 'lbs' : 'kg';
    localStorage.setItem('wc_weight_unit', weightUnit.value);
}

onMounted(() => {
    fetchWorkout();
});

onUnmounted(() => {
    if (timerInterval) clearInterval(timerInterval);
    if (restInterval) clearInterval(restInterval);
});
</script>

<template>
  <RiseLayout>
    <!-- Loading -->
    <div v-if="loading" class="space-y-6">
      <div class="h-10 w-56 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
      <div class="h-16 animate-pulse rounded-xl bg-wc-bg-tertiary"></div>
      <div v-for="i in 4" :key="i" class="h-24 animate-pulse rounded-xl bg-wc-bg-tertiary"></div>
    </div>

    <!-- Error -->
    <div v-else-if="error && !exercises.length" class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-6 text-center">
      <p class="text-sm font-medium text-wc-text">{{ error }}</p>
      <button @click="fetchWorkout" class="mt-4 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors">
        Reintentar
      </button>
    </div>

    <div v-else class="space-y-4">
      <!-- Header with timer -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="font-display text-2xl tracking-wide text-wc-text sm:text-3xl">
            {{ days[currentDayIndex]?.nombre || days[currentDayIndex]?.name || 'Entrenamiento RISE' }}
          </h1>
          <p class="mt-0.5 text-xs text-wc-text-tertiary">{{ exercises.length }} ejercicios</p>
        </div>
        <div class="flex items-center gap-2">
          <button @click="toggleUnit"
            class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-1.5 text-xs font-medium text-wc-text-secondary hover:text-wc-text transition-colors">
            {{ weightUnit.toUpperCase() }}
          </button>
        </div>
      </div>

      <!-- Day selector (if multiple days) -->
      <div v-if="days.length > 1" class="flex gap-2 overflow-x-auto pb-2">
        <button
          v-for="(day, idx) in days" :key="idx"
          @click="selectDay(idx)"
          :class="[
            'shrink-0 rounded-lg px-4 py-2 text-sm font-medium transition-colors',
            idx === currentDayIndex ? 'bg-wc-accent text-white' : 'border border-wc-border bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-text'
          ]"
        >
          {{ day.nombre || day.name || ('Dia ' + (idx + 1)) }}
        </button>
      </div>

      <!-- Timer bar -->
      <div v-if="workoutStarted" class="sticky top-16 z-20 flex items-center justify-between rounded-xl border border-wc-border bg-wc-bg-tertiary px-4 py-3">
        <div class="flex items-center gap-3">
          <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-wc-accent/15">
            <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
          </div>
          <span class="font-data text-xl font-bold tabular-nums text-wc-text">{{ elapsedDisplay }}</span>
        </div>
        <div class="flex items-center gap-3">
          <div class="text-right">
            <p class="font-data text-sm font-bold text-wc-accent">{{ progressPct }}%</p>
            <p class="text-[10px] text-wc-text-tertiary">{{ completedSetsCount }}/{{ totalSetsCount }} sets</p>
          </div>
          <button @click="finishWorkout"
            class="rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors">
            Finalizar
          </button>
        </div>
      </div>

      <!-- Start workout button -->
      <button v-if="!workoutStarted && exercises.length > 0" @click="startWorkout"
        class="w-full rounded-xl bg-gradient-to-r from-wc-accent to-wc-accent px-6 py-4 font-display text-lg tracking-wider text-white shadow-lg shadow-wc-accent/20 hover:from-wc-accent hover:to-amber-700 transition-all">
        COMENZAR ENTRENAMIENTO
      </button>

      <!-- Rest timer overlay -->
      <Teleport to="body">
        <Transition
          enter-active-class="transition ease-out duration-200"
          enter-from-class="opacity-0 translate-y-4"
          enter-to-class="opacity-100 translate-y-0"
          leave-active-class="transition ease-in duration-150"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0"
        >
          <div v-if="showRestTimer" class="fixed bottom-20 left-1/2 z-50 -translate-x-1/2 lg:bottom-8">
            <div class="flex items-center gap-4 rounded-2xl border border-wc-border bg-wc-bg-secondary px-6 py-4 shadow-2xl">
              <div class="text-center">
                <p class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">Descanso</p>
                <p class="font-data text-3xl font-bold tabular-nums text-wc-accent">{{ restDisplay }}</p>
              </div>
              <div class="h-10 w-10 rounded-full" :style="{ background: `conic-gradient(#DC2626 ${((restTotal - restSeconds) / restTotal) * 360}deg, transparent 0)` }">
              </div>
              <button @click="dismissRestTimer" class="rounded-lg border border-wc-border px-3 py-1.5 text-xs font-medium text-wc-text-secondary hover:text-wc-text transition-colors">
                Saltar
              </button>
            </div>
          </div>
        </Transition>
      </Teleport>

      <!-- Exercise list -->
      <div class="space-y-3">
        <div v-for="(ex, exIdx) in exercises" :key="exIdx" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
          <!-- Exercise header -->
          <div class="flex items-start justify-between">
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-wc-text">{{ ex.nombre || ex.name }}</p>
              <div class="mt-1 flex items-center gap-3 text-xs text-wc-text-tertiary">
                <span>{{ ex.series || ex.sets || 3 }} series</span>
                <span>{{ ex.repeticiones || ex.reps }} reps</span>
                <span v-if="ex.descanso || ex.rest">{{ ex.descanso || ex.rest }}s descanso</span>
              </div>
              <p v-if="ex.nota || ex.notes" class="mt-1 text-xs text-wc-text-tertiary italic">{{ ex.nota || ex.notes }}</p>
            </div>
          </div>

          <!-- Sets table -->
          <div v-if="workoutStarted && setData[exIdx]" class="mt-3">
            <div class="grid grid-cols-[auto_1fr_1fr_auto] gap-2 text-xs">
              <span class="text-wc-text-tertiary font-medium px-1">Set</span>
              <span class="text-wc-text-tertiary font-medium">Peso ({{ weightUnit }})</span>
              <span class="text-wc-text-tertiary font-medium">Reps</span>
              <span class="text-wc-text-tertiary font-medium px-1"></span>

              <template v-for="(set, setIdx) in setData[exIdx]" :key="setIdx">
                <span :class="['flex items-center justify-center rounded px-1 py-1.5 font-data font-bold', set.completed ? 'text-emerald-500' : 'text-wc-text-secondary']">
                  {{ setIdx + 1 }}
                </span>
                <input
                  type="number" step="0.5" min="0"
                  v-model="setData[exIdx][setIdx].weight"
                  placeholder="--"
                  :class="['rounded-lg border bg-wc-bg-secondary px-2 py-1.5 text-sm text-wc-text font-data', set.completed ? 'border-emerald-500/30' : 'border-wc-border']"
                >
                <input
                  type="number" min="0"
                  v-model="setData[exIdx][setIdx].reps"
                  :placeholder="String(ex.repeticiones || ex.reps || '')"
                  :class="['rounded-lg border bg-wc-bg-secondary px-2 py-1.5 text-sm text-wc-text font-data', set.completed ? 'border-emerald-500/30' : 'border-wc-border']"
                >
                <button
                  @click="completeSet(exIdx, setIdx)"
                  :class="['flex h-8 w-8 items-center justify-center rounded-lg transition-colors', set.completed ? 'bg-emerald-500/15 text-emerald-500' : 'bg-wc-bg-secondary text-wc-text-tertiary hover:text-wc-text']"
                  :aria-label="set.completed ? 'Desmarcar set' : 'Completar set'"
                >
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                  </svg>
                </button>
              </template>
            </div>
          </div>
        </div>
      </div>

      <!-- No exercises -->
      <div v-if="exercises.length === 0" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 text-center">
        <svg class="mx-auto h-10 w-10 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
        </svg>
        <p class="mt-3 text-sm font-medium text-wc-text">Sin ejercicios configurados</p>
        <p class="mt-1 text-xs text-wc-text-tertiary">Tu coach definira tu rutina pronto.</p>
      </div>
    </div>
  </RiseLayout>
</template>
