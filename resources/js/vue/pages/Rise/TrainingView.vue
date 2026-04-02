<script setup>
import { ref, computed, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import RiseLayout from '../../layouts/RiseLayout.vue';

const api = useApi();

// State
const loading = ref(true);
const error = ref(null);
const week = ref(1);
const year = ref(new Date().getFullYear());
const days = ref([]);
const completedCount = ref(0);
const monthSessions = ref(0);
const isCurrentWeek = ref(true);
const toggling = ref(false);

// Completion percentage
const completionPct = computed(() => {
  if (completedCount.value <= 0) return 0;
  return Math.round((completedCount.value / 7) * 100);
});

// Fetch
async function fetchTraining() {
  loading.value = true;
  error.value = null;
  try {
    const response = await api.get('/api/v/client/training', {
      params: { week: week.value, year: year.value },
    });
    const d = response.data;
    days.value = d.days || [];
    completedCount.value = d.completedCount || 0;
    monthSessions.value = d.monthSessions || 0;
    isCurrentWeek.value = d.isCurrentWeek !== undefined ? d.isCurrentWeek : true;
    week.value = d.week || week.value;
    year.value = d.year || year.value;
  } catch (err) {
    error.value = err.response?.data?.message || 'Error al cargar el entrenamiento';
  } finally {
    loading.value = false;
  }
}

async function toggleDay(date) {
  if (toggling.value) return;
  toggling.value = true;
  try {
    await api.post('/api/v/client/training/toggle', { date });
    // Optimistic update
    const day = days.value.find(d => d.date === date);
    if (day) {
      day.completed = !day.completed;
      completedCount.value = days.value.filter(d => d.completed).length;
    }
  } catch (err) {
    // Refetch on error
    await fetchTraining();
  } finally {
    toggling.value = false;
  }
}

function previousWeek() {
  week.value--;
  if (week.value < 1) {
    week.value = 52;
    year.value--;
  }
  fetchTraining();
}

function nextWeek() {
  if (isCurrentWeek.value) return;
  week.value++;
  fetchTraining();
}

function goToCurrentWeek() {
  const now = new Date();
  // ISO week calculation
  const d = new Date(Date.UTC(now.getFullYear(), now.getMonth(), now.getDate()));
  const dayNum = d.getUTCDay() || 7;
  d.setUTCDate(d.getUTCDate() + 4 - dayNum);
  const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1));
  week.value = Math.ceil(((d - yearStart) / 86400000 + 1) / 7);
  year.value = now.getFullYear();
  fetchTraining();
}

function isFuture(dateStr) {
  const today = new Date().toISOString().split('T')[0];
  return dateStr > today;
}

onMounted(() => {
  goToCurrentWeek();
});
</script>

<template>
  <RiseLayout>
    <div class="space-y-6">
      <!-- Title -->
      <div class="flex items-center justify-between">
        <h1 class="font-display text-3xl tracking-wide text-wc-text">MI ENTRENAMIENTO</h1>
      </div>

      <!-- Loading -->
      <template v-if="loading">
        <div class="space-y-4 animate-pulse">
          <div class="h-16 rounded-xl bg-wc-bg-tertiary"></div>
          <div class="grid grid-cols-7 gap-2">
            <div v-for="n in 7" :key="n" class="h-32 rounded-xl bg-wc-bg-tertiary"></div>
          </div>
          <div class="h-32 rounded-xl bg-wc-bg-tertiary"></div>
        </div>
      </template>

      <!-- Error -->
      <div v-else-if="error" class="rounded-xl border border-red-500/30 bg-red-500/10 p-6 text-center">
        <p class="text-sm text-red-400">{{ error }}</p>
        <button @click="fetchTraining" class="mt-4 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors">Reintentar</button>
      </div>

      <template v-else>
        <!-- Week Navigation -->
        <div class="flex items-center justify-between rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
          <button
            @click="previousWeek"
            class="flex h-10 w-10 items-center justify-center rounded-lg border border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:text-wc-text transition-colors"
            aria-label="Semana anterior"
          >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
          </button>

          <div class="text-center">
            <span class="font-data text-lg font-semibold text-wc-text">Semana {{ week }}</span>
            <span class="ml-2 text-sm text-wc-text-secondary">{{ year }}</span>
            <button
              v-if="!isCurrentWeek"
              @click="goToCurrentWeek"
              class="ml-3 rounded-full bg-wc-accent/10 px-3 py-1 text-xs font-medium text-wc-accent hover:bg-wc-accent/20 transition-colors"
            >Hoy</button>
          </div>

          <button
            @click="nextWeek"
            :disabled="isCurrentWeek"
            :class="['flex h-10 w-10 items-center justify-center rounded-lg border border-wc-border bg-wc-bg-secondary text-wc-text-secondary transition-colors', isCurrentWeek ? 'opacity-30 cursor-not-allowed' : 'hover:text-wc-text']"
            aria-label="Semana siguiente"
            :title="isCurrentWeek ? 'Ya estas en la semana actual' : ''"
          >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
          </button>
        </div>

        <!-- Weekly Grid -->
        <div class="grid grid-cols-7 gap-2 sm:gap-3">
          <button
            v-for="day in days"
            :key="day.date"
            @click="!isFuture(day.date) && toggleDay(day.date)"
            :disabled="isFuture(day.date)"
            :class="[
              'group flex flex-col items-center gap-2 rounded-xl border p-3 sm:p-4 transition-all',
              isFuture(day.date)
                ? 'border-wc-border bg-wc-bg-tertiary opacity-40 cursor-not-allowed'
                : day.isToday
                  ? 'border-wc-accent/50 bg-wc-accent/5'
                  : 'border-wc-border bg-wc-bg-tertiary hover:border-wc-text-tertiary'
            ]"
            :title="isFuture(day.date) ? 'No puedes marcar dias futuros' : ''"
            :aria-disabled="isFuture(day.date)"
          >
            <!-- Day Name -->
            <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">{{ day.dayName }}</span>
            <!-- Day Number -->
            <span :class="['font-data text-lg font-semibold', day.isToday ? 'text-wc-accent' : 'text-wc-text']">{{ day.dayNumber }}</span>
            <!-- Toggle Circle -->
            <div :class="[
              'flex h-10 w-10 items-center justify-center rounded-full transition-all',
              day.completed
                ? 'bg-emerald-500 text-white'
                : 'border-2 border-wc-border text-wc-text-tertiary ' + (isFuture(day.date) ? '' : 'group-hover:border-wc-text-secondary')
            ]">
              <svg v-if="day.completed" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
              <svg v-else class="h-5 w-5 opacity-40" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" /></svg>
            </div>
          </button>
        </div>

        <!-- Stats Row -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
          <div class="mb-3 flex items-center justify-between">
            <span class="text-sm font-medium text-wc-text-secondary">
              <span class="font-data text-lg font-semibold text-wc-text">{{ completedCount }}</span>
              de 7 dias completados
            </span>
            <span class="font-data text-sm font-semibold text-wc-accent">{{ completionPct }}%</span>
          </div>
          <div class="h-2 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
            <div class="h-full rounded-full bg-wc-accent transition-all duration-500" :style="{ width: completionPct + '%' }"></div>
          </div>
          <div class="mt-4 grid grid-cols-2 gap-4 border-t border-wc-border pt-4">
            <div>
              <p class="text-xs text-wc-text-tertiary">Sesiones esta semana</p>
              <p class="font-data text-2xl font-semibold text-wc-text">{{ completedCount }}</p>
            </div>
            <div>
              <p class="text-xs text-wc-text-tertiary">Sesiones este mes</p>
              <p class="font-data text-2xl font-semibold text-wc-text">{{ monthSessions }}</p>
            </div>
          </div>
        </div>
      </template>
    </div>
  </RiseLayout>
</template>
