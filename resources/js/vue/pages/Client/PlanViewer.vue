<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useApi } from '../../composables/useApi';
import ClientLayout from '../../layouts/ClientLayout.vue';

const api = useApi();
const router = useRouter();

// State
const loading = ref(true);
const error = ref(null);
const activeTab = ref('entrenamiento');
const currentWeek = ref(1);

// Plan data
const planData = ref(null);
const trainingPlan = ref(null);
const nutritionPlan = ref(null);
const supplementPlan = ref(null);
const habitsPlan = ref(null);
const clientPlanType = ref('basico');
const planStartDate = ref(null);

// Tabs
const tabs = [
  { key: 'entrenamiento', label: 'Entrenamiento' },
  { key: 'nutricion', label: 'Nutricion' },
  { key: 'suplementacion', label: 'Suplementos' },
  { key: 'habitos', label: 'Habitos' },
];

const canAccessNutricion = computed(() => {
  return ['esencial', 'metodo', 'elite', 'presencial', 'rise'].includes(clientPlanType.value);
});

function isTabLocked(key) {
  if (['nutricion', 'suplementacion'].includes(key) && !canAccessNutricion.value) return true;
  return false;
}

function setTab(key) {
  if (!isTabLocked(key)) {
    activeTab.value = key;
  }
}

// Training computed
const totalWeeks = computed(() => {
  if (!trainingPlan.value?.semanas) return 1;
  return trainingPlan.value.semanas.length;
});

const progressPct = computed(() => {
  if (totalWeeks.value <= 1) return 0;
  return Math.min(((currentWeek.value) / totalWeeks.value) * 100, 100);
});

const planObjetivo = computed(() => {
  if (!trainingPlan.value) return null;
  return trainingPlan.value.objetivo || trainingPlan.value.objetivo_general || null;
});

const planFrecuencia = computed(() => {
  if (!trainingPlan.value) return null;
  return trainingPlan.value.frecuencia || null;
});

const planSplit = computed(() => {
  if (!trainingPlan.value) return null;
  return trainingPlan.value.split || trainingPlan.value.metodologia || null;
});

const semanas = computed(() => {
  if (!trainingPlan.value?.semanas) return [];
  return trainingPlan.value.semanas;
});

// Week accordion state
const openWeeks = ref({});

function toggleWeek(weekNum) {
  openWeeks.value[weekNum] = !openWeeks.value[weekNum];
}

function isWeekOpen(weekNum) {
  return !!openWeeks.value[weekNum];
}

// Type badge colors
function tipoBadgeClass(tipo) {
  const t = (tipo || '').toLowerCase();
  const map = {
    empuje: 'bg-orange-500/10 text-orange-400',
    push: 'bg-orange-500/10 text-orange-400',
    jale: 'bg-blue-500/10 text-blue-400',
    pull: 'bg-blue-500/10 text-blue-400',
    piernas: 'bg-violet-500/10 text-violet-400',
    legs: 'bg-violet-500/10 text-violet-400',
    pierna: 'bg-violet-500/10 text-violet-400',
    full: 'bg-emerald-500/10 text-emerald-400',
    'full body': 'bg-emerald-500/10 text-emerald-400',
    cardio: 'bg-sky-500/10 text-sky-400',
    upper: 'bg-rose-500/10 text-rose-400',
    'tren superior': 'bg-rose-500/10 text-rose-400',
    lower: 'bg-teal-500/10 text-teal-400',
    'tren inferior': 'bg-teal-500/10 text-teal-400',
  };
  return map[t] || 'bg-wc-accent/10 text-wc-accent';
}

// RIR badge color
function rirClass(rir) {
  if (rir === null || rir === undefined) return '';
  if (rir >= 3) return 'bg-emerald-500/15 text-emerald-400';
  if (rir >= 2) return 'bg-amber-500/15 text-amber-400';
  return 'bg-red-500/15 text-red-400';
}

// Supplement timing icons
function getTimingIcon(timing) {
  const t = (timing || '').toLowerCase();
  if (t.includes('mañana') || t.includes('manana') || t.includes('morning')) return '\u{1F305}';
  if (t.includes('pre-entreno') || t.includes('pre entreno') || t.includes('pre-workout')) return '\u{26A1}';
  if (t.includes('post-entreno') || t.includes('post entreno') || t.includes('post-workout')) return '\u{1F504}';
  if (t.includes('con comida')) return '\u{1F37D}';
  if (t.includes('noche') || t.includes('night') || t.includes('antes de dormir')) return '\u{1F319}';
  return '\u{1F48A}';
}

// Fetch
async function fetchPlan() {
  loading.value = true;
  error.value = null;
  try {
    const response = await api.get('/api/v/client/plan');
    const d = response.data;
    planData.value = d;
    trainingPlan.value = d.trainingPlan || null;
    nutritionPlan.value = d.nutritionPlan || null;
    supplementPlan.value = d.supplementPlan || null;
    habitsPlan.value = d.habitsPlan || null;
    clientPlanType.value = d.clientPlanType || 'basico';
    planStartDate.value = d.planStartDate || null;
    currentWeek.value = d.currentWeek || 1;

    // Auto-open current week
    if (currentWeek.value) {
      openWeeks.value[currentWeek.value] = true;
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Error al cargar el plan';
  } finally {
    loading.value = false;
  }
}

function goToWorkout(dayIndex) {
  router.push({ name: 'client-workout', params: { day: dayIndex } });
}

onMounted(() => {
  fetchPlan();
});
</script>

<template>
  <ClientLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="font-display text-3xl tracking-wide text-wc-text">MI PLAN</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">Tu programacion personalizada, diseñada por tu coach</p>
      </div>

      <!-- Loading skeleton -->
      <template v-if="loading">
        <div class="space-y-4 animate-pulse">
          <div class="h-12 rounded-xl bg-wc-bg-tertiary"></div>
          <div class="h-48 rounded-xl bg-wc-bg-tertiary"></div>
          <div class="h-32 rounded-xl bg-wc-bg-tertiary"></div>
          <div class="h-64 rounded-xl bg-wc-bg-tertiary"></div>
        </div>
      </template>

      <!-- Error state -->
      <div v-else-if="error" class="rounded-xl border border-red-500/30 bg-red-500/10 p-6 text-center">
        <svg class="mx-auto h-10 w-10 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
        </svg>
        <p class="mt-3 text-sm text-red-400">{{ error }}</p>
        <button @click="fetchPlan" class="mt-4 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors">
          Reintentar
        </button>
      </div>

      <!-- Content -->
      <template v-else>
        <!-- Tabs -->
        <div class="mb-6 rounded-xl border border-wc-border bg-wc-bg-tertiary p-1" role="tablist" aria-label="Secciones del plan">
          <div class="flex gap-1 overflow-x-auto">
            <button
              v-for="tab in tabs"
              :key="tab.key"
              @click="setTab(tab.key)"
              role="tab"
              :aria-selected="activeTab === tab.key ? 'true' : 'false'"
              :class="[
                'shrink-0 flex-1 rounded-lg px-4 py-2.5 text-sm font-medium transition-all whitespace-nowrap focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-1',
                activeTab === tab.key
                  ? 'bg-wc-accent text-white shadow-sm'
                  : isTabLocked(tab.key)
                    ? 'cursor-not-allowed opacity-40 text-wc-text-secondary'
                    : 'text-wc-text-secondary hover:text-wc-text hover:bg-wc-bg-secondary'
              ]"
            >
              {{ tab.label }}
              <span v-if="isTabLocked(tab.key)" class="ml-1 text-xs">&#x1F512;</span>
            </button>
          </div>
        </div>

        <!-- ==================== TAB: ENTRENAMIENTO ==================== -->
        <div v-if="activeTab === 'entrenamiento'">
          <template v-if="trainingPlan">
            <!-- Program Overview Card -->
            <div class="relative mb-6 overflow-hidden rounded-xl border border-wc-accent/20 bg-gradient-to-br from-wc-accent/[0.08] via-wc-bg-tertiary to-transparent p-5 sm:p-6">
              <div class="pointer-events-none absolute -right-8 -top-8 h-32 w-32 rounded-full bg-wc-accent/[0.06]"></div>
              <div class="pointer-events-none absolute -right-3 -top-3 h-16 w-16 rounded-full bg-wc-accent/10"></div>

              <div class="relative">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                  <div class="flex-1">
                    <!-- Plan badge -->
                    <div class="flex items-center gap-2">
                      <span class="inline-flex items-center gap-1 rounded-full bg-gradient-to-r from-wc-accent/15 to-red-400/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-wc-accent">
                        <svg class="h-2.5 w-2.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        Plan {{ clientPlanType.charAt(0).toUpperCase() + clientPlanType.slice(1) }}
                      </span>
                      <span class="rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-emerald-400">Activo</span>
                    </div>

                    <p v-if="planStartDate" class="mt-2 text-sm text-wc-text-secondary">Inicio: {{ planStartDate }}</p>

                    <!-- Attributes -->
                    <div class="mt-3 flex flex-wrap gap-2">
                      <span v-if="planFrecuencia" class="inline-flex items-center gap-1 rounded-full bg-wc-accent/10 px-2.5 py-1 text-xs font-medium text-wc-accent">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                        {{ planFrecuencia }}
                      </span>
                      <span v-if="planSplit" class="inline-flex items-center gap-1 rounded-full bg-wc-bg-secondary px-2.5 py-1 text-xs font-medium text-wc-text-secondary">
                        <svg class="h-3 w-3 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 0a1.5 1.5 0 0 1-1.5-1.5v-3a1.5 1.5 0 0 1 1.5-1.5h1.5a1.5 1.5 0 0 1 1.5 1.5v3m-4.5 0a1.5 1.5 0 0 0-1.5 1.5v3a1.5 1.5 0 0 0 1.5 1.5h1.5a1.5 1.5 0 0 0 1.5-1.5v-3m12-4.5v3a1.5 1.5 0 0 1-1.5 1.5h-1.5m3 0a1.5 1.5 0 0 1-1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-1.5a1.5 1.5 0 0 1-1.5-1.5v-3m0-4.5a1.5 1.5 0 0 0-1.5-1.5h-1.5a1.5 1.5 0 0 0-1.5 1.5v3" /></svg>
                        {{ planSplit }}
                      </span>
                    </div>
                  </div>

                  <!-- Week counter -->
                  <div v-if="totalWeeks > 1" class="flex shrink-0 flex-col items-end">
                    <div class="flex items-baseline gap-1">
                      <span class="font-data text-4xl font-bold tabular-nums text-wc-accent">{{ currentWeek }}</span>
                      <span class="text-sm text-wc-text-tertiary">/ {{ totalWeeks }}</span>
                    </div>
                    <p class="text-[11px] uppercase tracking-wider text-wc-text-tertiary">Semana actual</p>
                  </div>
                </div>

                <!-- Progress bar -->
                <div v-if="totalWeeks > 1" class="mt-5">
                  <div class="flex items-center justify-between text-[11px] text-wc-text-tertiary mb-1.5">
                    <span>Progreso del programa</span>
                    <span class="font-data font-semibold text-wc-accent">{{ Math.round(progressPct) }}%</span>
                  </div>
                  <div class="h-2 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
                    <div class="h-full rounded-full bg-gradient-to-r from-wc-accent to-red-400 transition-all duration-700" :style="{ width: progressPct + '%' }"></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Objetivo banner -->
            <div v-if="planObjetivo" class="mb-5 flex items-start gap-3 rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-4">
              <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-wc-accent/15">
                <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" /></svg>
              </div>
              <div>
                <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-accent/70">Objetivo del plan</p>
                <p class="mt-0.5 text-sm leading-relaxed text-wc-text-secondary">{{ planObjetivo }}</p>
              </div>
            </div>

            <!-- Weeks accordion -->
            <div v-if="semanas.length > 0" class="space-y-3">
              <div
                v-for="(semana, sIdx) in semanas"
                :key="sIdx"
                class="overflow-hidden rounded-xl border transition-colors"
                :class="(semana.numero || sIdx + 1) === currentWeek ? 'border-wc-accent/30 bg-wc-accent/5' : 'border-wc-border bg-wc-bg-tertiary'"
              >
                <!-- Week header -->
                <button
                  @click="toggleWeek(semana.numero || sIdx + 1)"
                  class="flex w-full items-center justify-between px-4 py-4 text-left transition-colors hover:bg-black/5 dark:hover:bg-white/5 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-wc-accent"
                  :aria-expanded="isWeekOpen(semana.numero || sIdx + 1)"
                >
                  <div class="flex items-center gap-3">
                    <div
                      class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg font-data text-sm font-bold"
                      :class="(semana.numero || sIdx + 1) === currentWeek ? 'bg-wc-accent text-white' : 'bg-wc-bg-secondary text-wc-text-tertiary'"
                    >
                      {{ semana.numero || sIdx + 1 }}
                    </div>
                    <div>
                      <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-sm font-semibold text-wc-text">Semana {{ semana.numero || sIdx + 1 }}</span>
                        <span
                          v-if="(semana.numero || sIdx + 1) === currentWeek"
                          class="rounded-full bg-wc-accent px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white"
                        >Semana actual</span>
                        <span v-if="semana.fase" class="rounded-full bg-wc-bg-secondary px-2 py-0.5 text-[10px] font-medium text-wc-text-tertiary">{{ semana.fase }}</span>
                      </div>
                      <p v-if="(semana.dias || []).length > 0" class="mt-0.5 text-xs text-wc-text-tertiary">
                        {{ (semana.dias || []).length }} dia{{ (semana.dias || []).length !== 1 ? 's' : '' }} de entrenamiento
                      </p>
                    </div>
                  </div>
                  <svg
                    class="h-4 w-4 shrink-0 text-wc-text-tertiary transition-transform duration-200"
                    :class="{ 'rotate-180': isWeekOpen(semana.numero || sIdx + 1) }"
                    fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                  ><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                </button>

                <!-- Week body -->
                <div v-show="isWeekOpen(semana.numero || sIdx + 1)">
                  <div class="space-y-3 border-t border-wc-border/50 px-4 pb-4 pt-4">
                    <div
                      v-for="(dia, dIdx) in (semana.dias || [])"
                      :key="dIdx"
                      class="rounded-xl border border-wc-border bg-wc-bg-secondary"
                    >
                      <!-- Day header -->
                      <div class="flex items-center justify-between gap-3 px-4 py-3.5">
                        <div class="flex items-center gap-3 min-w-0">
                          <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-wc-accent/10">
                            <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 0a1.5 1.5 0 0 1-1.5-1.5v-3a1.5 1.5 0 0 1 1.5-1.5h1.5a1.5 1.5 0 0 1 1.5 1.5v3m-4.5 0a1.5 1.5 0 0 0-1.5 1.5v3a1.5 1.5 0 0 0 1.5 1.5h1.5a1.5 1.5 0 0 0 1.5-1.5v-3m12-4.5v3a1.5 1.5 0 0 1-1.5 1.5h-1.5m3 0a1.5 1.5 0 0 1-1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-1.5a1.5 1.5 0 0 1-1.5-1.5v-3m0-4.5a1.5 1.5 0 0 0-1.5-1.5h-1.5a1.5 1.5 0 0 0-1.5 1.5v3" /></svg>
                          </div>
                          <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-wc-text">{{ dia.nombre || dia.name || dia.dia || ('Dia ' + (dIdx + 1)) }}</p>
                            <p v-if="(dia.ejercicios || []).length > 0" class="text-xs text-wc-text-tertiary">
                              {{ (dia.ejercicios || []).length }} ejercicio{{ (dia.ejercicios || []).length !== 1 ? 's' : '' }}
                            </p>
                          </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                          <span
                            v-if="dia.tipo || dia.grupo_muscular || dia.muscle_group"
                            class="rounded-full px-2.5 py-1 text-[10px] font-semibold"
                            :class="tipoBadgeClass(dia.tipo || dia.grupo_muscular || dia.muscle_group)"
                          >{{ dia.tipo || dia.grupo_muscular || dia.muscle_group }}</span>
                        </div>
                      </div>

                      <!-- Warmup -->
                      <div v-if="dia.calentamiento || dia.warmup" class="flex items-start gap-3 border-t border-amber-500/20 bg-gradient-to-r from-amber-500/[0.08] to-transparent px-4 py-3">
                        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-amber-500/15">
                          <svg class="h-3.5 w-3.5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" /></svg>
                        </div>
                        <div>
                          <p class="text-[10px] font-bold uppercase tracking-wider text-amber-400">Calentamiento</p>
                          <p class="mt-0.5 text-xs leading-relaxed text-wc-text-secondary">{{ dia.calentamiento || dia.warmup }}</p>
                        </div>
                      </div>

                      <!-- Workout launch button -->
                      <div v-if="(dia.ejercicios || []).length > 0" class="border-t border-wc-border/40 px-4 py-2.5">
                        <button
                          @click="goToWorkout(dIdx + 1)"
                          class="flex w-full items-center justify-center gap-2 rounded-lg bg-wc-accent px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 transition-colors"
                        >
                          <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z"/></svg>
                          Entrenar este dia
                        </button>
                      </div>

                      <!-- Exercises list -->
                      <div v-if="(dia.ejercicios || []).length > 0" class="divide-y divide-wc-border/40 border-t border-wc-border/40">
                        <div v-for="(ejercicio, eIdx) in (dia.ejercicios || [])" :key="eIdx" class="flex items-start gap-3 px-4 py-3">
                          <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-wc-accent/10 font-data text-[11px] font-bold text-wc-accent">{{ eIdx + 1 }}</span>
                          <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-wc-text">{{ typeof ejercicio === 'string' ? ejercicio : (ejercicio.nombre || ejercicio.name || ejercicio.ejercicio || 'Ejercicio') }}</p>
                            <div v-if="typeof ejercicio === 'object'" class="mt-1.5 flex flex-wrap gap-1.5">
                              <span
                                v-if="(ejercicio.series || ejercicio.sets) || (ejercicio.repeticiones || ejercicio.reps)"
                                class="rounded-full bg-wc-bg-tertiary px-2 py-0.5 text-[11px] font-semibold text-wc-text-secondary"
                              >
                                <template v-if="(ejercicio.series || ejercicio.sets) && (ejercicio.repeticiones || ejercicio.reps)">
                                  {{ ejercicio.series || ejercicio.sets }} x {{ ejercicio.repeticiones || ejercicio.reps }}
                                </template>
                                <template v-else-if="ejercicio.series || ejercicio.sets">{{ ejercicio.series || ejercicio.sets }} series</template>
                                <template v-else>{{ ejercicio.repeticiones || ejercicio.reps }} reps</template>
                              </span>
                              <span
                                v-if="ejercicio.descanso || ejercicio.rest || ejercicio.rest_seconds"
                                class="inline-flex items-center gap-1 rounded-full bg-wc-bg-tertiary px-2 py-0.5 text-[11px] text-wc-text-tertiary"
                              >
                                <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                {{ typeof (ejercicio.descanso || ejercicio.rest || ejercicio.rest_seconds) === 'number' ? (ejercicio.descanso || ejercicio.rest || ejercicio.rest_seconds) + 's' : (ejercicio.descanso || ejercicio.rest || ejercicio.rest_seconds) }}
                              </span>
                              <span
                                v-if="ejercicio.rir !== undefined && ejercicio.rir !== null"
                                class="rounded-full px-2 py-0.5 text-[10px] font-black"
                                :class="rirClass(ejercicio.rir)"
                              >RIR{{ ejercicio.rir }}</span>
                            </div>
                            <p v-if="typeof ejercicio === 'object' && (ejercicio.notas || ejercicio.notes)" class="mt-1.5 text-xs italic leading-relaxed text-wc-text-tertiary">{{ ejercicio.notas || ejercicio.notes }}</p>
                          </div>
                        </div>
                      </div>

                      <!-- Cooldown -->
                      <div v-if="dia.vuelta_calma || dia.cooldown" class="flex items-start gap-3 border-t border-sky-500/20 bg-gradient-to-r from-sky-500/[0.08] to-transparent px-4 py-3">
                        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-sky-500/15">
                          <svg class="h-3.5 w-3.5 text-sky-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z" /></svg>
                        </div>
                        <div>
                          <p class="text-[10px] font-bold uppercase tracking-wider text-sky-400">Vuelta a la calma</p>
                          <p class="mt-0.5 text-xs leading-relaxed text-wc-text-secondary">{{ dia.vuelta_calma || dia.cooldown }}</p>
                        </div>
                      </div>
                    </div>

                    <div v-if="(semana.dias || []).length === 0" class="rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-6 text-center">
                      <p class="text-sm text-wc-text-tertiary">Sin dias asignados esta semana.</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </template>

          <!-- Empty training -->
          <div v-else class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-wc-bg-secondary">
              <svg class="h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" /></svg>
            </div>
            <h2 class="mt-5 font-display text-xl tracking-wide text-wc-text">PLAN EN PREPARACION</h2>
            <p class="mt-2 text-sm text-wc-text-secondary">Tu coach esta diseñando tu plan de entrenamiento.</p>
          </div>
        </div>

        <!-- ==================== TAB: NUTRICION ==================== -->
        <div v-else-if="activeTab === 'nutricion'">
          <template v-if="canAccessNutricion && nutritionPlan">
            <!-- Macros summary -->
            <div v-if="nutritionPlan.macros" class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
              <div v-for="(val, key) in nutritionPlan.macros" :key="key" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 text-center">
                <p class="text-[10px] font-bold uppercase tracking-widest text-wc-text-tertiary">{{ key }}</p>
                <p class="mt-1 font-data text-2xl font-bold text-wc-text">{{ val }}</p>
              </div>
            </div>

            <!-- Meals list -->
            <div v-if="nutritionPlan.comidas && nutritionPlan.comidas.length > 0" class="space-y-3">
              <div v-for="(meal, mIdx) in nutritionPlan.comidas" :key="mIdx" class="rounded-xl border border-wc-border bg-wc-bg-tertiary overflow-hidden">
                <div class="flex items-center gap-3 border-b border-wc-border px-4 py-3">
                  <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-wc-accent/10">
                    <span class="font-data text-sm font-bold text-wc-accent">{{ mIdx + 1 }}</span>
                  </div>
                  <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-wc-text">{{ meal.nombre || meal.name || ('Comida ' + (mIdx + 1)) }}</p>
                    <p v-if="meal.hora || meal.time" class="text-xs text-wc-text-tertiary">{{ meal.hora || meal.time }}</p>
                  </div>
                  <div v-if="meal.calorias || meal.calories" class="rounded-full bg-wc-bg-secondary px-2.5 py-0.5 text-[10px] font-semibold text-wc-text-secondary">
                    {{ meal.calorias || meal.calories }} kcal
                  </div>
                </div>
                <div v-if="(meal.alimentos || meal.foods || []).length > 0" class="divide-y divide-wc-border/40 px-4">
                  <div v-for="(alimento, aIdx) in (meal.alimentos || meal.foods || [])" :key="aIdx" class="flex items-center justify-between py-2.5">
                    <span class="text-sm text-wc-text">{{ typeof alimento === 'string' ? alimento : (alimento.nombre || alimento.name || 'Alimento') }}</span>
                    <span v-if="typeof alimento === 'object' && (alimento.cantidad || alimento.porcion)" class="text-xs text-wc-text-tertiary">{{ alimento.cantidad || alimento.porcion }}</span>
                  </div>
                </div>
              </div>
            </div>

            <div v-else class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-6 text-center">
              <p class="text-sm text-wc-text-secondary">Tu coach esta preparando tu plan de nutricion.</p>
            </div>
          </template>

          <div v-else-if="!canAccessNutricion" class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-8 text-center">
            <p class="font-display text-xl text-wc-text">Nutricion Premium</p>
            <p class="mt-2 text-sm text-wc-text-secondary">Disponible en planes Metodo y Elite.</p>
            <a href="/planes" class="mt-4 inline-block rounded-full bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white">Upgrade</a>
          </div>

          <div v-else class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-6 text-center">
            <p class="text-sm text-wc-text-secondary">Tu coach esta preparando tu plan de nutricion.</p>
          </div>
        </div>

        <!-- ==================== TAB: SUPLEMENTACION ==================== -->
        <div v-else-if="activeTab === 'suplementacion'">
          <template v-if="canAccessNutricion && supplementPlan">
            <div class="space-y-5">
              <!-- Descripcion -->
              <div v-if="supplementPlan.descripcion_protocolo || supplementPlan.descripcion" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
                <div class="flex items-start gap-3">
                  <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-wc-accent/10">
                    <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                  </div>
                  <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Protocolo</p>
                    <p class="mt-0.5 text-sm font-medium text-wc-text">{{ supplementPlan.descripcion_protocolo || supplementPlan.descripcion }}</p>
                  </div>
                </div>
              </div>

              <!-- Advertencia -->
              <div v-if="supplementPlan.advertencia" class="rounded-xl border border-amber-500/30 bg-amber-500/[0.08] p-4">
                <div class="flex items-start gap-2">
                  <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                  <p class="text-xs leading-relaxed text-amber-300">{{ supplementPlan.advertencia }}</p>
                </div>
              </div>

              <!-- Categorias or flat list -->
              <template v-if="supplementPlan.categorias && supplementPlan.categorias.length > 0">
                <div v-for="(cat, cIdx) in supplementPlan.categorias" :key="cIdx" class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-tertiary">
                  <div class="flex items-center gap-2 border-b border-wc-border px-5 py-3 bg-wc-bg-secondary">
                    <h3 class="font-display text-sm tracking-wider text-wc-text">{{ (cat.nombre || 'Suplementos').toUpperCase() }}</h3>
                    <span class="ml-auto rounded-full bg-wc-bg-tertiary px-2 py-0.5 text-[10px] font-semibold text-wc-text-tertiary">{{ (cat.suplementos || []).length }}</span>
                  </div>
                  <div class="divide-y divide-wc-border">
                    <div v-for="(sup, sIdx) in (cat.suplementos || [])" :key="sIdx" class="px-5 py-4">
                      <div class="flex flex-wrap items-start gap-x-3 gap-y-1">
                        <span class="font-semibold text-wc-text">{{ typeof sup === 'string' ? sup : (sup.nombre || sup.name || 'Suplemento') }}</span>
                        <span v-if="typeof sup === 'object' && (sup.dosis || sup.dose)" class="rounded bg-wc-bg-secondary px-2 py-0.5 font-data text-xs font-bold text-wc-accent">{{ sup.dosis || sup.dose }}</span>
                        <span v-if="typeof sup === 'object' && sup.prioridad" class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-wc-accent bg-wc-accent/10">{{ sup.prioridad }}</span>
                      </div>
                      <p v-if="typeof sup === 'object' && (sup.timing || sup.momento || sup.horario)" class="mt-1.5 inline-flex items-center gap-1 text-xs text-wc-text-secondary">
                        <span>{{ getTimingIcon(sup.timing || sup.momento || sup.horario) }}</span>
                        <span>{{ sup.timing || sup.momento || sup.horario }}</span>
                      </p>
                      <p v-if="typeof sup === 'object' && (sup.notas || sup.notes)" class="mt-1 text-xs leading-relaxed text-wc-text-tertiary">{{ sup.notas || sup.notes }}</p>
                    </div>
                  </div>
                </div>
              </template>

              <template v-else-if="(supplementPlan.suplementos || supplementPlan.supplements || supplementPlan.protocolo || []).length > 0">
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary overflow-hidden">
                  <div class="flex items-center justify-between border-b border-wc-border px-5 py-4">
                    <h3 class="font-display text-lg tracking-wide text-wc-text">SUPLEMENTOS</h3>
                    <span class="rounded-full bg-wc-accent/10 px-2.5 py-0.5 text-xs font-semibold text-wc-accent">{{ (supplementPlan.suplementos || supplementPlan.supplements || supplementPlan.protocolo || []).length }}</span>
                  </div>
                  <div class="divide-y divide-wc-border">
                    <div v-for="(sup, sIdx) in (supplementPlan.suplementos || supplementPlan.supplements || supplementPlan.protocolo || [])" :key="sIdx" class="flex items-start gap-4 px-5 py-4">
                      <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-wc-border bg-wc-bg-secondary">
                        <span class="font-data text-xs font-bold text-wc-accent">{{ sIdx + 1 }}</span>
                      </div>
                      <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-baseline gap-2">
                          <span class="font-semibold text-wc-text">{{ typeof sup === 'string' ? sup : (sup.nombre || sup.name || 'Suplemento') }}</span>
                          <span v-if="typeof sup === 'object' && (sup.dosis || sup.dose)" class="rounded bg-wc-bg-secondary px-2 py-0.5 font-data text-xs font-semibold text-wc-accent">{{ sup.dosis || sup.dose }}</span>
                        </div>
                        <p v-if="typeof sup === 'object' && (sup.momento || sup.timing || sup.horario)" class="mt-1 text-xs text-wc-text-secondary">{{ getTimingIcon(sup.momento || sup.timing || sup.horario) }} {{ sup.momento || sup.timing || sup.horario }}</p>
                        <p v-if="typeof sup === 'object' && (sup.notas || sup.notes)" class="mt-1 text-xs text-wc-text-tertiary">{{ sup.notas || sup.notes }}</p>
                      </div>
                    </div>
                  </div>
                </div>
              </template>

              <div v-else class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-6 text-center">
                <p class="text-sm text-wc-text-secondary">Tu coach esta preparando tu protocolo de suplementacion.</p>
              </div>
            </div>
          </template>

          <div v-else-if="!canAccessNutricion" class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-8 text-center">
            <p class="font-display text-xl text-wc-text">Suplementos Premium</p>
            <p class="mt-2 text-sm text-wc-text-secondary">Disponible en planes Metodo y Elite.</p>
            <a href="/planes" class="mt-4 inline-block rounded-full bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white">Upgrade</a>
          </div>
        </div>

        <!-- ==================== TAB: HABITOS ==================== -->
        <div v-else-if="activeTab === 'habitos'">
          <template v-if="habitsPlan && (habitsPlan.habitos || habitsPlan.habits || []).length > 0">
            <div class="space-y-3">
              <div
                v-for="(habit, hIdx) in (habitsPlan.habitos || habitsPlan.habits || [])"
                :key="hIdx"
                class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4"
              >
                <div class="flex items-start justify-between gap-3">
                  <div class="flex items-start gap-3 min-w-0">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-500/10">
                      <svg class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    </div>
                    <div class="min-w-0">
                      <p class="text-sm font-semibold text-wc-text">{{ typeof habit === 'string' ? habit : (habit.nombre || habit.name || habit.habito || 'Habito') }}</p>
                      <p v-if="typeof habit === 'object' && (habit.descripcion || habit.description)" class="mt-1 text-xs text-wc-text-tertiary">{{ habit.descripcion || habit.description }}</p>
                      <p v-if="typeof habit === 'object' && (habit.frecuencia || habit.frequency)" class="mt-1 text-xs text-wc-text-secondary">{{ habit.frecuencia || habit.frequency }}</p>
                    </div>
                  </div>
                  <div v-if="typeof habit === 'object' && habit.streak" class="text-right shrink-0">
                    <p class="font-data text-lg font-bold text-emerald-400">{{ habit.streak }}</p>
                    <p class="text-[10px] text-wc-text-tertiary">dias</p>
                  </div>
                </div>
              </div>
            </div>
          </template>

          <div v-else class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-wc-bg-secondary">
              <svg class="h-6 w-6 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
            </div>
            <p class="mt-3 font-display text-lg text-wc-text">HABITOS EN PREPARACION</p>
            <p class="mt-1 text-sm text-wc-text-secondary">Tu coach esta configurando tu plan de habitos.</p>
          </div>
        </div>
      </template>
    </div>
  </ClientLayout>
</template>
