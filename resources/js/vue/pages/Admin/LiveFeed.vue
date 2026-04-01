<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useApi } from '../../composables/useApi';
import AdminLayout from '../../layouts/AdminLayout.vue';

const api = useApi();

const loading = ref(true);
const error = ref(null);
const feed = ref([]);
const stats = ref({ eventsToday: 0, actionsToday: 0, paymentsToday: 0, activeNow: 0 });
const filter = ref('all');
let refreshInterval = null;

const filterOptions = [
    { value: 'all', label: 'Todos' },
    { value: 'training', label: 'Entrenamiento' },
    { value: 'checkin', label: 'Check-ins' },
    { value: 'payment', label: 'Pagos' },
    { value: 'signup', label: 'Registros' },
    { value: 'habits', label: 'Habitos' },
    { value: 'photos', label: 'Fotos' },
];

async function fetchFeed() {
    if (!feed.value.length) loading.value = true;
    error.value = null;
    try {
        const response = await api.get('/api/v/admin/feed', { params: { type: filter.value !== 'all' ? filter.value : undefined } });
        feed.value = response.data.feed || [];
        if (response.data.stats) stats.value = response.data.stats;
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al cargar el feed';
    } finally {
        loading.value = false;
    }
}

function getEventColor(type) {
    const colors = {
        training: 'bg-emerald-500/10 text-emerald-500',
        checkin: 'bg-sky-500/10 text-sky-500',
        payment: 'bg-violet-500/10 text-violet-500',
        signup: 'bg-amber-500/10 text-amber-500',
        habits: 'bg-orange-500/10 text-orange-500',
        photos: 'bg-pink-500/10 text-pink-500',
    };
    return colors[type] || 'bg-wc-bg-secondary text-wc-text-tertiary';
}

function getEventIcon(type) {
    return type; // Used in template v-if
}

function setFilter(value) {
    filter.value = value;
    fetchFeed();
}

onMounted(() => {
    fetchFeed();
    refreshInterval = setInterval(fetchFeed, 30000);
});

onUnmounted(() => {
    if (refreshInterval) clearInterval(refreshInterval);
});
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">

      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
          <h1 class="font-display text-3xl tracking-wide text-wc-text">LIVE FEED</h1>
          <span class="flex items-center gap-2 rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-medium text-emerald-400">
            <span class="relative flex h-2 w-2">
              <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
              <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
            </span>
            Auto-refresh 30s
          </span>
        </div>
      </div>

      <!-- Stats Row -->
      <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
          <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-sky-500/10">
              <svg class="h-5 w-5 text-sky-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
              </svg>
            </div>
            <div>
              <p class="text-2xl font-bold font-data text-wc-text">{{ stats.eventsToday }}</p>
              <p class="text-xs text-wc-text-tertiary">Eventos hoy</p>
            </div>
          </div>
        </div>
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
          <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-yellow-500/10">
              <svg class="h-5 w-5 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" />
              </svg>
            </div>
            <div>
              <p class="text-2xl font-bold font-data text-wc-text">{{ stats.actionsToday }}</p>
              <p class="text-xs text-wc-text-tertiary">Acciones hoy</p>
            </div>
          </div>
        </div>
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
          <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-500/10">
              <svg class="h-5 w-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
              </svg>
            </div>
            <div>
              <p class="text-2xl font-bold font-data text-wc-text">{{ stats.paymentsToday }}</p>
              <p class="text-xs text-wc-text-tertiary">Pagos hoy</p>
            </div>
          </div>
        </div>
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
          <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-500/10">
              <svg class="h-5 w-5 text-violet-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
              </svg>
            </div>
            <div>
              <p class="text-2xl font-bold font-data text-wc-text">{{ stats.activeNow }}</p>
              <p class="text-xs text-wc-text-tertiary">Activos ahora</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="flex flex-wrap gap-2">
        <button
          v-for="opt in filterOptions"
          :key="opt.value"
          @click="setFilter(opt.value)"
          :class="[
            'rounded-lg px-3 py-1.5 text-xs font-medium transition-colors',
            filter === opt.value
              ? 'bg-wc-accent text-white'
              : 'bg-wc-bg-tertiary text-wc-text-secondary hover:bg-wc-bg-secondary hover:text-wc-text'
          ]"
        >
          {{ opt.label }}
        </button>
      </div>

      <!-- Feed List -->
      <div v-if="loading" class="space-y-3">
        <div v-for="i in 8" :key="i" class="h-16 animate-pulse rounded-xl bg-wc-bg-tertiary"></div>
      </div>

      <div v-else-if="error" class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-6 text-center">
        <p class="text-sm text-wc-text">{{ error }}</p>
        <button @click="fetchFeed" class="mt-3 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors">Reintentar</button>
      </div>

      <div v-else-if="feed.length" class="space-y-2">
        <div
          v-for="(event, idx) in feed"
          :key="idx"
          class="flex items-center gap-4 rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 transition-colors hover:bg-wc-bg-secondary"
        >
          <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full" :class="getEventColor(event.type)">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path v-if="event.type === 'training'" stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
              <path v-else-if="event.type === 'payment'" stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
              <path v-else-if="event.type === 'signup'" stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
              <path v-else-if="event.type === 'checkin'" stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
          </div>
          <div class="min-w-0 flex-1">
            <p class="text-sm font-medium text-wc-text truncate">{{ event.clientName || 'Usuario' }}</p>
            <p class="text-xs text-wc-text-tertiary truncate">{{ event.description }}</p>
          </div>
          <div class="text-right shrink-0">
            <p class="text-xs font-medium text-wc-text-tertiary">{{ event.time }}</p>
            <span class="mt-1 inline-block rounded-full px-2 py-0.5 text-[10px] font-medium capitalize" :class="getEventColor(event.type)">{{ event.type }}</span>
          </div>
        </div>
      </div>

      <div v-else class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center">
        <svg class="mx-auto h-10 w-10 text-wc-text-tertiary/40" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
        </svg>
        <p class="mt-3 text-sm text-wc-text-tertiary">Sin eventos recientes</p>
      </div>

    </div>
  </AdminLayout>
</template>
