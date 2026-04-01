<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useApi } from '../../composables/useApi';
import RiseLayout from '../../layouts/RiseLayout.vue';

const api = useApi();
const router = useRouter();

const loading = ref(true);
const error = ref(null);
const data = ref(null);

const greeting = computed(() => {
    const h = new Date().getHours();
    return h < 12 ? 'Buenos dias' : h < 18 ? 'Buenas tardes' : 'Buenas noches';
});

async function fetchDashboard() {
    loading.value = true;
    error.value = null;
    try {
        const response = await api.get('/api/v/rise/dashboard');
        data.value = response.data;
        if (response.data.clientName) {
            localStorage.setItem('wc_user_name', response.data.clientName);
        }
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al cargar el dashboard';
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    fetchDashboard();
});
</script>

<template>
  <RiseLayout>
    <!-- Loading state -->
    <div v-if="loading" class="space-y-6">
      <div class="h-10 w-64 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
      <div class="h-32 animate-pulse rounded-xl bg-wc-bg-tertiary"></div>
      <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div v-for="i in 4" :key="i" class="h-28 animate-pulse rounded-xl bg-wc-bg-tertiary"></div>
      </div>
    </div>

    <!-- Error state -->
    <div v-else-if="error" class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-6 text-center">
      <svg class="mx-auto h-10 w-10 text-wc-accent/50" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
      </svg>
      <p class="mt-3 text-sm font-medium text-wc-text">{{ error }}</p>
      <button @click="fetchDashboard" class="mt-4 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors">
        Reintentar
      </button>
    </div>

    <!-- Dashboard content -->
    <div v-else-if="data" class="space-y-6">

      <!-- Greeting section -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">
            {{ greeting }}, {{ data.clientName }}
          </h1>
          <div class="mt-2 flex items-center gap-2">
            <span class="inline-flex items-center gap-1 rounded-full bg-gradient-to-r from-wc-accent/15 to-amber-400/10 px-3 py-1 text-xs font-bold uppercase tracking-wider text-wc-accent">
              <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
              </svg>
              Programa RISE
            </span>
            <span v-if="data.hasProgram" class="text-xs text-wc-text-tertiary">
              Semana {{ data.currentWeek }} de {{ data.totalWeeks }}
            </span>
          </div>
        </div>

        <!-- Quick actions (desktop) -->
        <div class="hidden sm:flex items-center gap-2">
          <RouterLink
            to="/v/rise/tracking"
            class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-wc-accent to-wc-accent px-4 py-2 text-sm font-medium text-white hover:from-wc-accent hover:to-amber-700 transition-all"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Registrar hoy
          </RouterLink>
          <RouterLink
            to="/v/rise/measurements"
            class="inline-flex items-center gap-2 rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2 text-sm font-medium text-wc-text hover:bg-wc-bg-secondary transition-colors"
          >
            Nueva medicion
          </RouterLink>
        </div>
      </div>

      <!-- Program progress banner -->
      <div v-if="data.hasProgram" class="relative overflow-hidden rounded-xl border border-wc-accent/20 bg-gradient-to-r from-wc-accent/5 via-amber-400/5 to-transparent p-5 sm:p-6">
        <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-wc-accent/5"></div>
        <div class="absolute -right-2 -top-2 h-12 w-12 rounded-full bg-wc-accent/10"></div>

        <div class="relative">
          <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h2 class="font-display text-xl tracking-wide text-wc-text">Progreso del Programa</h2>
              <p class="mt-1 text-sm text-wc-text-tertiary">
                {{ data.startDate }} -- {{ data.endDate }}
              </p>
            </div>
            <div class="flex items-center gap-4">
              <div class="text-right">
                <p class="font-data text-3xl font-bold text-wc-accent">{{ Math.round(data.progressPct || 0) }}%</p>
                <p class="text-xs text-wc-text-tertiary">completado</p>
              </div>
            </div>
          </div>

          <!-- Progress bar -->
          <div class="mt-4">
            <div class="h-2.5 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
              <div
                class="h-full rounded-full bg-gradient-to-r from-wc-accent to-amber-400 transition-all duration-500"
                :style="{ width: (data.progressPct || 0) + '%' }"
              ></div>
            </div>
            <div class="mt-2 flex items-center justify-between text-xs text-wc-text-tertiary">
              <span>Dia {{ data.daysElapsed }} de {{ data.totalDays }}</span>
              <span>{{ data.daysRemaining }} dias restantes</span>
            </div>
          </div>
        </div>
      </div>

      <!-- No program state -->
      <div v-else class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-6 text-center">
        <svg class="mx-auto h-10 w-10 text-wc-accent/50" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
        </svg>
        <p class="mt-3 text-sm font-medium text-wc-text">No tienes un programa RISE activo</p>
        <p class="mt-1 text-xs text-wc-text-tertiary">Contacta a tu coach para activar tu programa RISE.</p>
      </div>

      <!-- Stats cards -->
      <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        <!-- Current Streak -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
          <div class="flex items-center justify-between">
            <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Racha</span>
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-wc-accent/10">
              <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" />
              </svg>
            </div>
          </div>
          <p class="mt-3 font-data text-3xl font-bold text-wc-text">{{ data.currentStreak ?? 0 }}</p>
          <p class="mt-0.5 text-xs text-wc-text-tertiary">dias consecutivos</p>
        </div>

        <!-- Workouts this week -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
          <div class="flex items-center justify-between">
            <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Entrenos</span>
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/10">
              <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
              </svg>
            </div>
          </div>
          <p class="mt-3 font-data text-3xl font-bold text-wc-text">{{ data.workoutsThisWeek ?? 0 }}</p>
          <p class="mt-0.5 text-xs text-wc-text-tertiary">esta semana</p>
        </div>

        <!-- Adherence -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
          <div class="flex items-center justify-between">
            <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Adherencia</span>
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-500/10">
              <svg class="h-4 w-4 text-sky-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
              </svg>
            </div>
          </div>
          <p class="mt-3 font-data text-3xl font-bold text-wc-text">{{ data.overallAdherence ?? 0 }}%</p>
          <p class="mt-0.5 text-xs text-wc-text-tertiary">general</p>
        </div>

        <!-- Weight -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
          <div class="flex items-center justify-between">
            <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Peso</span>
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-500/10">
              <svg class="h-4 w-4 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0 0 12 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 0 1-2.031.352 5.988 5.988 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971Zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 0 1-2.031.352 5.989 5.989 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971Z" />
              </svg>
            </div>
          </div>
          <template v-if="data.latestWeight">
            <p class="mt-3 font-data text-3xl font-bold text-wc-text">{{ data.latestWeight }}</p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">
              kg
              <span v-if="data.weightChange !== null" :class="data.weightChange < 0 ? 'text-emerald-500' : data.weightChange > 0 ? 'text-wc-accent' : ''">
                ({{ data.weightChange > 0 ? '+' : '' }}{{ data.weightChange }} kg)
              </span>
            </p>
          </template>
          <template v-else>
            <p class="mt-3 font-data text-3xl font-bold text-wc-text-tertiary">--</p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">sin registro</p>
          </template>
        </div>
      </div>

      <!-- Weekly grid + Summary -->
      <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

        <!-- Weekly tracking grid -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 lg:col-span-2">
          <h2 class="font-display text-lg tracking-wide text-wc-text">Semana actual</h2>
          <p class="mt-1 text-xs text-wc-text-tertiary">Tracking diario</p>

          <div class="mt-5 flex items-center justify-between gap-2 sm:justify-start sm:gap-4">
            <div v-for="day in (data.weekDays || [])" :key="day.label" class="flex flex-col items-center gap-2">
              <span :class="['text-[11px] font-medium text-wc-text-tertiary', day.isToday ? '!text-wc-accent font-semibold' : '']">
                {{ day.label }}
              </span>
              <div v-if="day.trainingDone" class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-500/15 sm:h-12 sm:w-12">
                <svg class="h-5 w-5 text-emerald-500 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
              </div>
              <div v-else :class="['flex h-10 w-10 items-center justify-center rounded-full border-2 border-wc-border sm:h-12 sm:w-12', day.isToday ? '!border-wc-accent/40' : '']">
                <div v-if="day.isToday" class="h-2 w-2 rounded-full bg-wc-accent"></div>
              </div>
              <div :class="['h-1.5 w-1.5 rounded-full', day.nutritionDone ? 'bg-amber-400' : 'bg-wc-border']"></div>
            </div>
          </div>

          <div class="mt-5 flex items-center gap-4 text-xs text-wc-text-tertiary">
            <div class="flex items-center gap-1.5">
              <div class="h-2.5 w-2.5 rounded-full bg-emerald-500/40"></div>
              Entrenamiento
            </div>
            <div class="flex items-center gap-1.5">
              <div class="h-2.5 w-2.5 rounded-full bg-amber-400"></div>
              Nutricion
            </div>
            <div class="flex items-center gap-1.5">
              <div class="h-2.5 w-2.5 rounded-full border border-wc-border"></div>
              Pendiente
            </div>
            <div class="flex items-center gap-1.5">
              <div class="h-2.5 w-2.5 rounded-full bg-wc-accent"></div>
              Hoy
            </div>
          </div>
        </div>

        <!-- Weekly summary card -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
          <h2 class="font-display text-lg tracking-wide text-wc-text">Resumen semanal</h2>

          <div class="mt-4 space-y-4">
            <!-- Workout adherence -->
            <div>
              <div class="flex items-center justify-between">
                <span class="text-sm text-wc-text-secondary">Entrenamientos</span>
                <span class="font-data text-sm font-semibold text-wc-text">{{ data.workoutsThisWeek ?? 0 }}/7</span>
              </div>
              <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
                <div class="h-full rounded-full bg-emerald-500 transition-all" :style="{ width: Math.min(100, ((data.workoutsThisWeek || 0) / 7) * 100) + '%' }"></div>
              </div>
            </div>

            <!-- Nutrition adherence -->
            <div>
              <div class="flex items-center justify-between">
                <span class="text-sm text-wc-text-secondary">Nutricion</span>
                <span class="font-data text-sm font-semibold text-wc-text">{{ data.nutritionDaysThisWeek ?? 0 }}/7</span>
              </div>
              <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
                <div class="h-full rounded-full bg-wc-accent transition-all" :style="{ width: Math.min(100, ((data.nutritionDaysThisWeek || 0) / 7) * 100) + '%' }"></div>
              </div>
            </div>

            <!-- Habits -->
            <div>
              <div class="flex items-center justify-between">
                <span class="text-sm text-wc-text-secondary">Habitos</span>
                <span class="font-data text-sm font-semibold text-wc-text">{{ data.habitsCompletedThisWeek ?? 0 }}/7</span>
              </div>
              <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
                <div class="h-full rounded-full bg-sky-500 transition-all" :style="{ width: Math.min(100, ((data.habitsCompletedThisWeek || 0) / 7) * 100) + '%' }"></div>
              </div>
            </div>

            <!-- Tracking total -->
            <div class="mt-4 border-t border-wc-border pt-4">
              <div class="flex items-center justify-between">
                <span class="text-xs text-wc-text-tertiary">Dias registrados (total)</span>
                <span class="font-data text-sm font-semibold text-wc-accent">{{ data.totalTrackingDays ?? 0 }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick actions (mobile) -->
      <div class="grid grid-cols-1 gap-3 sm:hidden">
        <RouterLink
          to="/v/rise/tracking"
          class="flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-wc-accent to-wc-accent px-4 py-3 text-sm font-medium text-white hover:from-wc-accent hover:to-amber-700 transition-all"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          Registrar hoy
        </RouterLink>
        <RouterLink
          to="/v/rise/measurements"
          class="flex items-center justify-center gap-2 rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-3 text-sm font-medium text-wc-text hover:bg-wc-bg-secondary transition-colors"
        >
          Nueva medicion
        </RouterLink>
        <RouterLink
          to="/v/rise/program"
          class="flex items-center justify-center gap-2 rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-3 text-sm font-medium text-wc-text hover:bg-wc-bg-secondary transition-colors"
        >
          Ver mi programa
        </RouterLink>
      </div>
    </div>
  </RiseLayout>
</template>
