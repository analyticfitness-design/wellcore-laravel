<script setup>
import { ref, computed, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import RiseLayout from '../../layouts/RiseLayout.vue';

const api = useApi();

const loading = ref(true);
const error = ref(null);
const data = ref(null);

async function fetchProfile() {
    loading.value = true;
    error.value = null;
    try {
        const response = await api.get('/api/v/rise/profile');
        data.value = response.data;
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al cargar perfil';
    } finally {
        loading.value = false;
    }
}

const statusConfig = computed(() => {
    if (!data.value?.status) return { color: 'bg-wc-bg-secondary text-wc-text-tertiary', label: '' };
    const map = {
        active: { color: 'bg-emerald-500/10 text-emerald-500', label: 'Activo' },
        completed: { color: 'bg-sky-500/10 text-sky-500', label: 'Completado' },
        paused: { color: 'bg-wc-accent/10 text-wc-accent', label: 'Pausado' },
    };
    return map[data.value.status] || { color: 'bg-wc-bg-secondary text-wc-text-tertiary', label: data.value.status };
});

onMounted(() => {
    fetchProfile();
});
</script>

<template>
  <RiseLayout>
    <!-- Loading -->
    <div v-if="loading" class="space-y-6">
      <div class="h-10 w-48 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
      <div class="h-32 animate-pulse rounded-xl bg-wc-bg-tertiary"></div>
      <div class="h-24 animate-pulse rounded-xl bg-wc-bg-tertiary"></div>
      <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div v-for="i in 4" :key="i" class="h-24 animate-pulse rounded-xl bg-wc-bg-tertiary"></div>
      </div>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-6 text-center">
      <p class="text-sm font-medium text-wc-text">{{ error }}</p>
      <button @click="fetchProfile" class="mt-4 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors">
        Reintentar
      </button>
    </div>

    <div v-else-if="data" class="space-y-6">

      <!-- Page header -->
      <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">MI PERFIL RISE</h1>
        <p class="mt-1 text-sm text-wc-text-tertiary">Tu informacion y progreso en el programa RISE.</p>
      </div>

      <!-- Profile card -->
      <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 sm:p-6">
        <div class="flex items-center gap-4">
          <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-wc-accent/30 to-wc-accent/20">
            <span class="font-display text-2xl text-wc-accent">{{ data.initial }}</span>
          </div>
          <div class="flex-1 min-w-0">
            <h2 class="text-lg font-semibold text-wc-text truncate">{{ data.name }}</h2>
            <p class="text-sm text-wc-text-tertiary truncate">{{ data.email }}</p>
            <span v-if="data.status" :class="['mt-1 inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium', statusConfig.color]">
              <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
              {{ statusConfig.label }}
            </span>
          </div>
        </div>
      </div>

      <!-- Progress bar -->
      <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
        <div class="flex items-center justify-between">
          <h2 class="font-display text-lg tracking-wide text-wc-text">Progreso del programa</h2>
          <span class="font-data text-2xl font-bold text-wc-accent">{{ data.progressPercent || 0 }}%</span>
        </div>
        <div class="mt-3 h-3 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
          <div class="h-full rounded-full bg-gradient-to-r from-wc-accent to-amber-400 transition-all duration-500"
            :style="{ width: (data.progressPercent || 0) + '%' }"></div>
        </div>
        <div class="mt-2 flex items-center justify-between text-xs text-wc-text-tertiary">
          <span>Dia {{ data.daysInProgram || 0 }} de {{ data.totalDays || 84 }}</span>
          <span>{{ (data.totalDays || 84) - (data.daysInProgram || 0) > 0 ? ((data.totalDays || 84) - (data.daysInProgram || 0)) + ' dias restantes' : 'Programa finalizado' }}</span>
        </div>
      </div>

      <!-- Program info card -->
      <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
        <h2 class="font-display text-lg tracking-wide text-wc-text">Informacion del programa</h2>

        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div class="flex items-center gap-3 rounded-lg border border-wc-border bg-wc-bg-secondary p-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-500/15">
              <svg class="h-[18px] w-[18px] text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
              </svg>
            </div>
            <div>
              <p class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">Fecha inicio</p>
              <p class="text-sm font-medium text-wc-text">{{ data.startDate || 'No definida' }}</p>
            </div>
          </div>

          <div class="flex items-center gap-3 rounded-lg border border-wc-border bg-wc-bg-secondary p-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-wc-accent/15">
              <svg class="h-[18px] w-[18px] text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
              </svg>
            </div>
            <div>
              <p class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">Fecha fin</p>
              <p class="text-sm font-medium text-wc-text">{{ data.endDate || 'No definida' }}</p>
            </div>
          </div>

          <div class="flex items-center gap-3 rounded-lg border border-wc-border bg-wc-bg-secondary p-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-wc-accent/15">
              <svg class="h-[18px] w-[18px] text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
              </svg>
            </div>
            <div>
              <p class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">Nivel</p>
              <p class="text-sm font-medium text-wc-text">{{ data.experienceLevel || 'No definido' }}</p>
            </div>
          </div>

          <div class="flex items-center gap-3 rounded-lg border border-wc-border bg-wc-bg-secondary p-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-sky-500/15">
              <svg class="h-[18px] w-[18px] text-sky-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
              </svg>
            </div>
            <div>
              <p class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">Ubicacion entrenamiento</p>
              <p class="text-sm font-medium text-wc-text">{{ data.trainingLocation || 'No definida' }}</p>
            </div>
          </div>

          <div v-if="data.gender" class="flex items-center gap-3 rounded-lg border border-wc-border bg-wc-bg-secondary p-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-violet-500/15">
              <svg class="h-[18px] w-[18px] text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
              </svg>
            </div>
            <div>
              <p class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">Genero</p>
              <p class="text-sm font-medium text-wc-text">{{ data.gender }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Stats grid -->
      <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 text-center">
          <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-wc-accent/15">
            <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
            </svg>
          </div>
          <p class="mt-2 font-data text-2xl font-bold text-wc-text">{{ data.daysInProgram || 0 }}</p>
          <p class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">Dias en programa</p>
        </div>

        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 text-center">
          <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-sky-500/15">
            <svg class="h-5 w-5 text-sky-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25Z" />
            </svg>
          </div>
          <p class="mt-2 font-data text-2xl font-bold text-wc-text">{{ data.measurementCount || 0 }}</p>
          <p class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">Mediciones</p>
        </div>

        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 text-center">
          <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-emerald-500/15">
            <svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
          </div>
          <p class="mt-2 font-data text-2xl font-bold text-wc-text">{{ data.checkinsCount || 0 }}</p>
          <p class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">Check-ins</p>
        </div>

        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 text-center">
          <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-violet-500/15">
            <svg class="h-5 w-5 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" />
            </svg>
          </div>
          <p class="mt-2 font-data text-2xl font-bold text-wc-text">{{ data.adherence || 0 }}%</p>
          <p class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">Adherencia</p>
        </div>
      </div>

      <!-- Quick links -->
      <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
        <h2 class="font-display text-lg tracking-wide text-wc-text">Acciones rapidas</h2>
        <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
          <RouterLink to="/v/rise/tracking"
            class="flex items-center gap-3 rounded-lg border border-wc-border bg-wc-bg-secondary p-3 transition-colors hover:border-wc-accent/30 hover:bg-wc-accent/5">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-500/15">
              <svg class="h-[18px] w-[18px] text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
              </svg>
            </div>
            <div>
              <p class="text-sm font-medium text-wc-text">Tracking diario</p>
              <p class="text-xs text-wc-text-tertiary">Registrar progreso</p>
            </div>
          </RouterLink>

          <RouterLink to="/v/rise/habits"
            class="flex items-center gap-3 rounded-lg border border-wc-border bg-wc-bg-secondary p-3 transition-colors hover:border-wc-accent/30 hover:bg-wc-accent/5">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-wc-accent/15">
              <svg class="h-[18px] w-[18px] text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" />
              </svg>
            </div>
            <div>
              <p class="text-sm font-medium text-wc-text">Habitos diarios</p>
              <p class="text-xs text-wc-text-tertiary">Registrar habitos</p>
            </div>
          </RouterLink>

          <RouterLink to="/v/rise/measurements"
            class="flex items-center gap-3 rounded-lg border border-wc-border bg-wc-bg-secondary p-3 transition-colors hover:border-wc-accent/30 hover:bg-wc-accent/5">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-sky-500/15">
              <svg class="h-[18px] w-[18px] text-sky-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25Z" />
              </svg>
            </div>
            <div>
              <p class="text-sm font-medium text-wc-text">Mediciones</p>
              <p class="text-xs text-wc-text-tertiary">Ver mediciones</p>
            </div>
          </RouterLink>
        </div>
      </div>
    </div>
  </RiseLayout>
</template>
