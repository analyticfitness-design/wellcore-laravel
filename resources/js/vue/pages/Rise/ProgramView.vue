<script setup>
import { ref, computed, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import RiseLayout from '../../layouts/RiseLayout.vue';

const api = useApi();

const loading = ref(true);
const error = ref(null);
const data = ref(null);
const activeTab = ref('plan');
const expandedWeek = ref(null);

const tabs = [
    { key: 'plan', label: 'Plan' },
    { key: 'entrenamiento', label: 'Entrenamiento' },
    { key: 'habitos', label: 'Habitos' },
];

async function fetchProgram() {
    loading.value = true;
    error.value = null;
    try {
        const response = await api.get('/api/v/rise/program');
        data.value = response.data;
        if (response.data.currentWeek) {
            expandedWeek.value = response.data.currentWeek;
        }
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al cargar el programa';
    } finally {
        loading.value = false;
    }
}

function toggleWeek(weekNum) {
    expandedWeek.value = expandedWeek.value === weekNum ? null : weekNum;
}

onMounted(() => {
    fetchProgram();
});
</script>

<template>
  <RiseLayout>
    <!-- Loading -->
    <div v-if="loading" class="space-y-6">
      <div class="h-10 w-48 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
      <div class="h-40 animate-pulse rounded-xl bg-wc-bg-tertiary"></div>
      <div class="h-64 animate-pulse rounded-xl bg-wc-bg-tertiary"></div>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-6 text-center">
      <p class="text-sm font-medium text-wc-text">{{ error }}</p>
      <button @click="fetchProgram" class="mt-4 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors">
        Reintentar
      </button>
    </div>

    <!-- Program content -->
    <div v-else-if="data" class="space-y-6">

      <!-- Page header -->
      <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">MI PROGRAMA</h1>
        <p class="mt-1 text-sm text-wc-text-tertiary">Tu plan RISE personalizado de {{ data.totalWeeks || 12 }} semanas.</p>
      </div>

      <!-- Program overview card -->
      <div v-if="data.hasProgram" class="relative overflow-hidden rounded-xl border border-wc-accent/20 bg-gradient-to-br from-wc-accent/[0.08] via-amber-400/[0.04] to-transparent p-5 sm:p-6">
        <div class="pointer-events-none absolute -right-8 -top-8 h-32 w-32 rounded-full bg-wc-accent/[0.06]"></div>
        <div class="pointer-events-none absolute -right-3 -top-3 h-16 w-16 rounded-full bg-wc-accent/10"></div>

        <div class="relative">
          <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex-1">
              <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1 rounded-full bg-gradient-to-r from-wc-accent/15 to-amber-400/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-wc-accent">
                  <svg class="h-2.5 w-2.5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                  </svg>
                  Programa RISE
                </span>
                <span v-if="data.status" class="rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-emerald-400">Activo</span>
              </div>

              <p class="mt-2 text-sm text-wc-text-secondary">
                <template v-if="data.startDate && data.endDate">{{ data.startDate }} -- {{ data.endDate }}</template>
                <template v-else>Plan en curso</template>
              </p>

              <!-- Program attributes -->
              <div class="mt-3 flex flex-wrap gap-2">
                <span v-if="data.experienceLevel" class="inline-flex items-center gap-1 rounded-full bg-wc-bg-secondary px-2.5 py-1 text-xs font-medium text-wc-text-secondary">
                  {{ data.experienceLevel }}
                </span>
                <span v-if="data.trainingLocation" class="inline-flex items-center gap-1 rounded-full bg-wc-bg-secondary px-2.5 py-1 text-xs font-medium text-wc-text-secondary">
                  {{ data.trainingLocation }}
                </span>
                <span v-if="data.frequency" class="inline-flex items-center gap-1 rounded-full bg-wc-accent/10 px-2.5 py-1 text-xs font-medium text-wc-accent">
                  {{ data.frequency }}
                </span>
              </div>
            </div>

            <!-- Week counter -->
            <div class="flex shrink-0 flex-col items-end">
              <div class="flex items-baseline gap-1">
                <span class="font-data text-4xl font-bold tabular-nums text-wc-accent">{{ data.currentWeek }}</span>
                <span class="text-sm text-wc-text-tertiary">/ {{ data.totalWeeks }}</span>
              </div>
              <p class="text-[11px] uppercase tracking-wider text-wc-text-tertiary">Semana actual</p>
            </div>
          </div>

          <!-- Progress bar -->
          <div class="mt-5">
            <div class="flex items-center justify-between text-[11px] text-wc-text-tertiary mb-1.5">
              <span>Progreso del programa</span>
              <span class="font-data font-semibold text-wc-accent">{{ Math.round(data.progressPct || 0) }}%</span>
            </div>
            <div class="h-2 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
              <div class="h-full rounded-full bg-gradient-to-r from-wc-accent to-amber-400 transition-all duration-700" :style="{ width: (data.progressPct || 0) + '%' }"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="flex gap-1 rounded-lg bg-wc-bg-secondary p-1">
        <button
          v-for="tab in tabs"
          :key="tab.key"
          @click="activeTab = tab.key"
          :class="[
            'flex-1 rounded-md px-4 py-2 text-sm font-medium transition-colors',
            activeTab === tab.key
              ? 'bg-wc-accent text-white shadow-sm'
              : 'text-wc-text-secondary hover:text-wc-text'
          ]"
        >
          {{ tab.label }}
        </button>
      </div>

      <!-- Tab: Plan -->
      <div v-if="activeTab === 'plan'" class="space-y-3">
        <div v-for="week in (data.weeks || [])" :key="week.number" class="rounded-xl border border-wc-border bg-wc-bg-tertiary overflow-hidden">
          <button
            @click="toggleWeek(week.number)"
            class="flex w-full items-center justify-between px-5 py-4 text-left transition-colors hover:bg-wc-bg-secondary/50"
          >
            <div class="flex items-center gap-3">
              <div :class="['flex h-9 w-9 items-center justify-center rounded-lg text-sm font-bold', week.number === data.currentWeek ? 'bg-wc-accent/15 text-wc-accent' : 'bg-wc-bg-secondary text-wc-text-secondary']">
                {{ week.number }}
              </div>
              <div>
                <p class="text-sm font-medium text-wc-text">Semana {{ week.number }}</p>
                <p class="text-xs text-wc-text-tertiary">{{ week.focus || 'Entrenamiento general' }}</p>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <span v-if="week.number === data.currentWeek" class="rounded-full bg-wc-accent/10 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-wc-accent">Actual</span>
              <svg :class="['h-5 w-5 text-wc-text-tertiary transition-transform', expandedWeek === week.number ? 'rotate-180' : '']" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
              </svg>
            </div>
          </button>

          <div v-if="expandedWeek === week.number" class="border-t border-wc-border px-5 py-4 space-y-3">
            <div v-for="(day, idx) in (week.days || [])" :key="idx" class="rounded-lg border border-wc-border bg-wc-bg-secondary p-3">
              <p class="text-sm font-medium text-wc-text">{{ day.name || ('Dia ' + (idx + 1)) }}</p>
              <p v-if="day.focus" class="mt-0.5 text-xs text-wc-text-tertiary">{{ day.focus }}</p>
              <div v-if="day.exercises" class="mt-2 space-y-1">
                <p v-for="(ex, eidx) in day.exercises" :key="eidx" class="text-xs text-wc-text-secondary">
                  {{ ex.name || ex.nombre }} - {{ ex.sets || ex.series }}x{{ ex.reps || ex.repeticiones }}
                  <span v-if="ex.rest || ex.descanso" class="text-wc-text-tertiary">({{ ex.rest || ex.descanso }}s)</span>
                </p>
              </div>
            </div>
            <p v-if="!week.days || week.days.length === 0" class="text-sm text-wc-text-tertiary text-center py-4">
              Sin dias configurados para esta semana.
            </p>
          </div>
        </div>

        <p v-if="!data.weeks || data.weeks.length === 0" class="text-center text-sm text-wc-text-tertiary py-8">
          Programa sin contenido definido aun.
        </p>
      </div>

      <!-- Tab: Entrenamiento -->
      <div v-else-if="activeTab === 'entrenamiento'" class="space-y-4">
        <div v-if="data.trainingPlan" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
          <h3 class="font-display text-lg tracking-wide text-wc-text">Plan de entrenamiento</h3>
          <p v-if="data.trainingPlan.objetivo" class="mt-1 text-sm text-wc-text-tertiary">Objetivo: {{ data.trainingPlan.objetivo }}</p>
          <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
            <div v-if="data.trainingPlan.frecuencia" class="rounded-lg border border-wc-border bg-wc-bg-secondary p-3">
              <p class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">Frecuencia</p>
              <p class="text-sm font-medium text-wc-text">{{ data.trainingPlan.frecuencia }}</p>
            </div>
            <div v-if="data.trainingPlan.duracion" class="rounded-lg border border-wc-border bg-wc-bg-secondary p-3">
              <p class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">Duracion</p>
              <p class="text-sm font-medium text-wc-text">{{ data.trainingPlan.duracion }}</p>
            </div>
            <div v-if="data.trainingPlan.tipo" class="rounded-lg border border-wc-border bg-wc-bg-secondary p-3">
              <p class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">Tipo</p>
              <p class="text-sm font-medium text-wc-text">{{ data.trainingPlan.tipo }}</p>
            </div>
          </div>
        </div>
        <div v-else class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 text-center">
          <p class="text-sm text-wc-text-tertiary">Sin plan de entrenamiento definido.</p>
        </div>
      </div>

      <!-- Tab: Habitos -->
      <div v-else-if="activeTab === 'habitos'" class="space-y-4">
        <div v-if="data.habitsPlan && data.habitsPlan.length > 0" class="grid grid-cols-1 gap-3 sm:grid-cols-2">
          <div v-for="(habit, idx) in data.habitsPlan" :key="idx" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
            <div class="flex items-center gap-3">
              <div :class="['flex h-9 w-9 items-center justify-center rounded-lg', idx % 3 === 0 ? 'bg-emerald-500/15' : idx % 3 === 1 ? 'bg-wc-accent/15' : 'bg-violet-500/15']">
                <svg :class="['h-4 w-4', idx % 3 === 0 ? 'text-emerald-500' : idx % 3 === 1 ? 'text-wc-accent' : 'text-violet-500']" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
              </div>
              <div>
                <p class="text-sm font-medium text-wc-text">{{ habit.nombre || habit.name }}</p>
                <p v-if="habit.frecuencia || habit.frequency" class="text-xs text-wc-text-tertiary">{{ habit.frecuencia || habit.frequency }}</p>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 text-center">
          <p class="text-sm text-wc-text-tertiary">Sin habitos definidos en tu programa aun.</p>
        </div>
      </div>

      <!-- No program fallback -->
      <div v-if="!data.hasProgram" class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-6 text-center">
        <p class="text-sm font-medium text-wc-text">No tienes un programa RISE activo.</p>
        <p class="mt-1 text-xs text-wc-text-tertiary">Contacta a tu coach para activar tu programa.</p>
      </div>
    </div>
  </RiseLayout>
</template>
