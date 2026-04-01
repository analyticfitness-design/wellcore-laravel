<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { useApi } from '../../composables/useApi';
import AdminLayout from '../../layouts/AdminLayout.vue';

const api = useApi();

const loading = ref(true);
const error = ref(null);
const feed = ref([]);
const stats = ref({
  eventsToday: 0,
  actionsToday: 0,
  paymentsToday: 0,
  activeNow: 0,
});

const typeFilter = ref('all');
const dateFilter = ref('today');

// Module-level interval handle — NOT a ref (avoids proxy overhead)
let pollInterval = null;

const POLL_INTERVAL_MS = 10_000;

// ─── Filter options matching the Livewire blade ─────────────────────

const typeFilterOptions = [
  { value: 'all', label: 'Todos' },
  { value: 'inscriptions', label: 'Inscripciones' },
  { value: 'payments', label: 'Pagos' },
  { value: 'checkins', label: 'Check-ins' },
  { value: 'messages', label: 'Mensajes' },
  { value: 'training', label: 'Entrenamientos' },
  { value: 'photos', label: 'Fotos progreso' },
  { value: 'habits', label: 'Habitos' },
  { value: 'biometrics', label: 'Metricas' },
  { value: 'community', label: 'Comunidad' },
];

const dateFilterOptions = [
  { value: 'today', label: 'Hoy' },
  { value: 'week', label: 'Ultima semana' },
  { value: 'month', label: 'Ultimo mes' },
  { value: 'all', label: 'Todo' },
];

// ─── Color map matching the Livewire blade ──────────────────────────

const COLOR_MAP = {
  sky:     { dot: 'bg-sky-500 ring-sky-500/20',     bg: 'bg-sky-500/10',     text: 'text-sky-400' },
  emerald: { dot: 'bg-emerald-500 ring-emerald-500/20', bg: 'bg-emerald-500/10', text: 'text-emerald-400' },
  orange:  { dot: 'bg-orange-500 ring-orange-500/20',  bg: 'bg-orange-500/10',  text: 'text-orange-400' },
  violet:  { dot: 'bg-violet-500 ring-violet-500/20',  bg: 'bg-violet-500/10',  text: 'text-violet-400' },
  red:     { dot: 'bg-red-500 ring-red-500/20',     bg: 'bg-red-500/10',     text: 'text-red-400' },
  pink:    { dot: 'bg-pink-500 ring-pink-500/20',    bg: 'bg-pink-500/10',    text: 'text-pink-400' },
  yellow:  { dot: 'bg-yellow-500 ring-yellow-500/20',  bg: 'bg-yellow-500/10',  text: 'text-yellow-400' },
  cyan:    { dot: 'bg-cyan-500 ring-cyan-500/20',    bg: 'bg-cyan-500/10',    text: 'text-cyan-400' },
  amber:   { dot: 'bg-amber-500 ring-amber-500/20',   bg: 'bg-amber-500/10',   text: 'text-amber-400' },
  teal:    { dot: 'bg-teal-500 ring-teal-500/20',    bg: 'bg-teal-500/10',    text: 'text-teal-400' },
};

// Fallback color by event type when API does not send color field
const TYPE_COLOR_MAP = {
  signup:     'sky',
  inscription: 'sky',
  payment:    'emerald',
  checkin:    'orange',
  message:    'violet',
  new_client: 'red',
  community:  'pink',
  training:   'yellow',
  photo:      'cyan',
  habit:      'amber',
  biometric:  'teal',
};

function getColorClasses(event) {
  const colorKey = event.color || TYPE_COLOR_MAP[event.type] || 'sky';
  return COLOR_MAP[colorKey] || COLOR_MAP.sky;
}

// ─── Fetch ──────────────────────────────────────────────────────────

async function fetchFeed() {
  // Only show loading skeleton on first load
  if (!feed.value.length) loading.value = true;
  error.value = null;
  try {
    const params = {};
    if (typeFilter.value !== 'all') params.type = typeFilter.value;
    if (dateFilter.value !== 'today') params.date = dateFilter.value;
    else params.date = 'today';

    const response = await api.get('/api/v/admin/feed', { params });
    feed.value = response.data.feed || [];
    if (response.data.stats) stats.value = response.data.stats;
  } catch (err) {
    error.value = err.response?.data?.message || 'Error al cargar el feed';
  } finally {
    loading.value = false;
  }
}

// ─── Watchers — refetch when filters change ─────────────────────────

watch(typeFilter, () => fetchFeed());
watch(dateFilter, () => fetchFeed());

// ─── Feed count (for filter bar) ────────────────────────────────────

const feedCount = computed(() => feed.value.length);

// ─── Auto-poll lifecycle ────────────────────────────────────────────

onMounted(() => {
  fetchFeed();
  pollInterval = setInterval(fetchFeed, POLL_INTERVAL_MS);
});

onBeforeUnmount(() => {
  clearInterval(pollInterval);
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
            Auto-actualizacion cada 10s
          </span>
        </div>
      </div>

      <!-- Stats Row -->
      <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <!-- Events Today -->
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

        <!-- Actions Today -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
          <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-yellow-500/10">
              <svg class="h-5 w-5 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.048 6.75 6.75 0 009 16.5a.75.75 0 01.75-.75h4.5a.75.75 0 01.75.75 6.75 6.75 0 003.362-11.286z" />
              </svg>
            </div>
            <div>
              <p class="text-2xl font-bold font-data text-wc-text">{{ stats.actionsToday }}</p>
              <p class="text-xs text-wc-text-tertiary">Acciones hoy</p>
            </div>
          </div>
        </div>

        <!-- Payments Today -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
          <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-500/10">
              <svg class="h-5 w-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
              </svg>
            </div>
            <div>
              <p class="text-2xl font-bold font-data text-wc-text">{{ stats.paymentsToday }}</p>
              <p class="text-xs text-wc-text-tertiary">Pagos hoy</p>
            </div>
          </div>
        </div>

        <!-- Active Conversations -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
          <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-500/10">
              <svg class="h-5 w-5 text-violet-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
              </svg>
            </div>
            <div>
              <p class="text-2xl font-bold font-data text-wc-text">{{ stats.activeNow }}</p>
              <p class="text-xs text-wc-text-tertiary">Conversaciones</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Filter Bar -->
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <!-- Type Filter -->
        <div class="flex items-center gap-2">
          <label for="typeFilter" class="text-sm font-medium text-wc-text-secondary">Tipo:</label>
          <select
            id="typeFilter"
            v-model="typeFilter"
            class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
          >
            <option v-for="opt in typeFilterOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
          </select>
        </div>

        <!-- Date Filter -->
        <div class="flex items-center gap-2">
          <label for="dateFilter" class="text-sm font-medium text-wc-text-secondary">Periodo:</label>
          <select
            id="dateFilter"
            v-model="dateFilter"
            class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
          >
            <option v-for="opt in dateFilterOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
          </select>
        </div>

        <!-- Item Count -->
        <div class="sm:ml-auto">
          <span class="text-sm text-wc-text-tertiary">{{ feedCount }} eventos</span>
        </div>
      </div>

      <!-- Loading Skeleton -->
      <div v-if="loading" class="space-y-1">
        <div v-for="n in 8" :key="n" class="h-16 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-6 text-center">
        <p class="text-sm text-wc-text">{{ error }}</p>
        <button @click="fetchFeed" class="mt-3 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent/90 transition-colors">Reintentar</button>
      </div>

      <!-- Feed Timeline -->
      <div v-else-if="feed.length" class="relative">
        <!-- Vertical timeline line -->
        <div class="absolute left-5 top-0 bottom-0 w-px bg-wc-border sm:left-6"></div>

        <div class="space-y-1">
          <div
            v-for="(event, idx) in feed"
            :key="`${idx}-${event.type}-${event.timestamp || idx}`"
            class="relative flex items-start gap-4 rounded-lg py-3 pl-12 pr-4 transition-colors hover:bg-wc-bg-tertiary/50 sm:pl-14"
          >
            <!-- Colored dot on timeline -->
            <div class="absolute left-3.5 top-4 sm:left-4.5">
              <div
                class="h-3 w-3 rounded-full border-2 border-wc-bg ring-2"
                :class="getColorClasses(event).dot"
              ></div>
            </div>

            <!-- Icon circle -->
            <div class="shrink-0">
              <div
                class="flex h-9 w-9 items-center justify-center rounded-lg"
                :class="getColorClasses(event).bg"
              >
                <!-- clipboard-document-check (inscription/signup) -->
                <svg v-if="event.icon === 'clipboard-document-check' || event.type === 'signup' || event.type === 'inscription'"
                  class="h-4.5 w-4.5" :class="getColorClasses(event).text" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.25 2.25 0 012.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3l1.5 1.5 3-3.75" />
                </svg>
                <!-- banknotes (payment) -->
                <svg v-else-if="event.icon === 'banknotes' || event.type === 'payment'"
                  class="h-4.5 w-4.5" :class="getColorClasses(event).text" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                </svg>
                <!-- clipboard-document-list (checkin) -->
                <svg v-else-if="event.icon === 'clipboard-document-list' || event.type === 'checkin'"
                  class="h-4.5 w-4.5" :class="getColorClasses(event).text" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.25 2.25 0 012.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                </svg>
                <!-- chat-bubble-left-right (message) -->
                <svg v-else-if="event.icon === 'chat-bubble-left-right' || event.type === 'message'"
                  class="h-4.5 w-4.5" :class="getColorClasses(event).text" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
                </svg>
                <!-- user-plus (new_client) -->
                <svg v-else-if="event.icon === 'user-plus' || event.type === 'new_client'"
                  class="h-4.5 w-4.5" :class="getColorClasses(event).text" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
                </svg>
                <!-- chat-bubble-bottom-center-text (community) -->
                <svg v-else-if="event.icon === 'chat-bubble-bottom-center-text' || event.type === 'community'"
                  class="h-4.5 w-4.5" :class="getColorClasses(event).text" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                </svg>
                <!-- fire (training) -->
                <svg v-else-if="event.icon === 'fire' || event.type === 'training'"
                  class="h-4.5 w-4.5" :class="getColorClasses(event).text" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.048 6.75 6.75 0 009 16.5a.75.75 0 01.75-.75h4.5a.75.75 0 01.75.75 6.75 6.75 0 003.362-11.286z" />
                </svg>
                <!-- camera (photo) -->
                <svg v-else-if="event.icon === 'camera' || event.type === 'photo'"
                  class="h-4.5 w-4.5" :class="getColorClasses(event).text" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" />
                </svg>
                <!-- check-circle (habit) -->
                <svg v-else-if="event.icon === 'check-circle' || event.type === 'habit'"
                  class="h-4.5 w-4.5" :class="getColorClasses(event).text" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <!-- scale (biometric) -->
                <svg v-else-if="event.icon === 'scale' || event.type === 'biometric'"
                  class="h-4.5 w-4.5" :class="getColorClasses(event).text" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0012 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 01-2.031.352 5.988 5.988 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 01-2.031.352 5.989 5.989 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971z" />
                </svg>
                <!-- default fallback icon -->
                <svg v-else
                  class="h-4.5 w-4.5" :class="getColorClasses(event).text" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
              </div>
            </div>

            <!-- Content -->
            <div class="min-w-0 flex-1">
              <div class="flex items-center gap-2">
                <span
                  class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider"
                  :class="[getColorClasses(event).bg, getColorClasses(event).text]"
                >{{ event.title || event.type }}</span>
                <span class="text-xs text-wc-text-tertiary">{{ event.time_ago || event.time }}</span>
              </div>
              <p class="mt-1 text-sm text-wc-text-secondary truncate">{{ event.description }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="flex flex-col items-center justify-center rounded-xl border border-wc-border bg-wc-bg-tertiary py-16 px-6 text-center">
        <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-wc-bg-secondary">
          <svg class="h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
          </svg>
        </div>
        <h3 class="text-lg font-semibold text-wc-text">Sin eventos</h3>
        <p class="mt-1 text-sm text-wc-text-tertiary">No hay actividad para el periodo y filtro seleccionado.</p>
      </div>

    </div>
  </AdminLayout>
</template>
