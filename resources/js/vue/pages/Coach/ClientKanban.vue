<script setup>
import { ref, watch, onMounted, onBeforeUnmount, computed } from 'vue';
import { useApi } from '../../composables/useApi';
import { useRouter } from 'vue-router';
import CoachLayout from '../../layouts/CoachLayout.vue';

const api = useApi();
const router = useRouter();
const loading = ref(true);
const search = ref('');
const dragOverColumn = ref(null);
const draggedClientId = ref(null);

// Detail modal state
const showDetail = ref(false);
const detailClient = ref(null);
const detailLoading = ref(false);

const columns = ref({
  nuevo:    { title: 'Nuevos',     color: 'blue',    clients: [] },
  activo:   { title: 'Activos',    color: 'emerald', clients: [] },
  riesgo:   { title: 'En Riesgo',  color: 'amber',   clients: [] },
  inactivo: { title: 'Inactivos',  color: 'red',     clients: [] },
});

const totalClients = computed(() => {
  return Object.values(columns.value).reduce((sum, col) => sum + col.clients.length, 0);
});

// Static color map (module-level constant)
const colorMap = {
  blue:    { border: 'border-t-blue-500',    bg: 'bg-blue-500/10 text-blue-500 border-blue-500/20',       headerBg: 'bg-blue-500/10 text-blue-600 dark:text-blue-400',    badge: 'bg-blue-500 text-white',    dot: 'bg-blue-500' },
  emerald: { border: 'border-t-emerald-500', bg: 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20', headerBg: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400', badge: 'bg-emerald-500 text-white', dot: 'bg-emerald-500' },
  amber:   { border: 'border-t-amber-500',   bg: 'bg-amber-500/10 text-amber-500 border-amber-500/20',     headerBg: 'bg-amber-500/10 text-amber-600 dark:text-amber-400',   badge: 'bg-amber-500 text-white',   dot: 'bg-amber-500' },
  red:     { border: 'border-t-red-500',     bg: 'bg-red-500/10 text-red-500 border-red-500/20',           headerBg: 'bg-red-500/10 text-red-600 dark:text-red-400',       badge: 'bg-red-500 text-white',     dot: 'bg-red-500' },
};

// ── Debounced search (300ms) ──────────────────────────────────────────
let debounceTimer = null;
watch(search, () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(loadBoard, 300);
});

// ── Drag & Drop ───────────────────────────────────────────────────────
function onDragStart(event, clientId) {
  draggedClientId.value = clientId;
  event.dataTransfer.effectAllowed = 'move';
  event.dataTransfer.setData('text/plain', clientId.toString());
  requestAnimationFrame(() => {
    event.target.style.opacity = '0.5';
    event.target.style.transform = 'rotate(2deg)';
  });
}

function onDragEnd(event) {
  event.target.style.opacity = '1';
  event.target.style.transform = 'rotate(0deg)';
  dragOverColumn.value = null;
  draggedClientId.value = null;
}

function onDragOver(event, colKey) {
  event.preventDefault();
  event.dataTransfer.dropEffect = 'move';
  dragOverColumn.value = colKey;
}

function onDragLeave(event) {
  const rect = event.currentTarget.getBoundingClientRect();
  if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) {
    dragOverColumn.value = null;
  }
}

async function onDrop(event, colKey) {
  event.preventDefault();
  const clientId = parseInt(event.dataTransfer.getData('text/plain'));
  dragOverColumn.value = null;
  if (!clientId || isNaN(clientId)) return;

  // Move client locally (optimistic)
  let movedClient = null;
  for (const key in columns.value) {
    const idx = columns.value[key].clients.findIndex(c => c.id === clientId);
    if (idx !== -1) {
      movedClient = columns.value[key].clients.splice(idx, 1)[0];
      break;
    }
  }
  if (movedClient) {
    columns.value[colKey].clients.push(movedClient);
  }

  try {
    await api.post('/api/v/coach/kanban/move', { client_id: clientId, target_column: colKey });
  } catch (e) {
    loadBoard();
  }
}

// ── Data Fetching ─────────────────────────────────────────────────────
async function loadBoard() {
  loading.value = true;
  try {
    const params = {};
    if (search.value.trim()) params.search = search.value.trim();
    const { data } = await api.get('/api/v/coach/kanban', { params });
    if (data.columns) {
      columns.value = data.columns;
    }
  } catch (e) {
    // silent
  } finally {
    loading.value = false;
  }
}

// ── Detail Modal ──────────────────────────────────────────────────────
async function openDetail(clientId) {
  detailLoading.value = true;
  showDetail.value = true;
  try {
    const { data } = await api.get(`/api/v/coach/kanban/detail/${clientId}`);
    detailClient.value = data.client;
  } catch (e) {
    detailClient.value = null;
  } finally {
    detailLoading.value = false;
  }
}

function closeDetail() {
  showDetail.value = false;
  detailClient.value = null;
}

function formatNumber(n) {
  return n != null ? n.toLocaleString('es-CO') : '0';
}

// Escape key handler
function onEscapeKey(e) {
  if (e.key === 'Escape' && showDetail.value) {
    closeDetail();
  }
}

// Activity color helper
function activityColorClass(days) {
  if (days === null || days === undefined) return 'text-red-500';
  if (days > 14) return 'text-red-500 font-semibold';
  if (days > 7) return 'text-amber-500 font-semibold';
  return '';
}

function activityLabel(days) {
  if (days === null || days === undefined) return '--';
  if (days === 0) return 'Hoy';
  return days + 'd';
}

onMounted(() => {
  loadBoard();
  document.addEventListener('keydown', onEscapeKey);
});

onBeforeUnmount(() => {
  clearTimeout(debounceTimer);
  document.removeEventListener('keydown', onEscapeKey);
});
</script>

<template>
  <CoachLayout>
    <div class="space-y-6">

      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">KANBAN CLIENTES</h1>
          <p class="mt-1 text-sm text-wc-text-tertiary">{{ totalClients }} cliente{{ totalClients !== 1 ? 's' : '' }} &middot; Vista por actividad</p>
        </div>
        <div class="flex items-center gap-3">
          <!-- Search -->
          <div class="relative w-full sm:w-64">
            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <input
              v-model="search"
              type="text"
              placeholder="Buscar cliente..."
              class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary py-2 pl-10 pr-4 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
            />
          </div>

          <!-- Link back to list view -->
          <RouterLink
            to="/coach/clients"
            class="flex h-9 w-9 items-center justify-center rounded-lg border border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:text-wc-text transition-colors"
            title="Vista lista"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
            </svg>
          </RouterLink>

          <!-- Refresh -->
          <button
            @click="loadBoard"
            class="flex h-9 w-9 items-center justify-center rounded-lg border border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:text-wc-text transition-colors"
            title="Actualizar"
          >
            <svg class="h-4 w-4" :class="{ 'animate-spin': loading }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.992 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Summary stats -->
      <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div v-for="(col, key) in columns" :key="key" class="flex items-center gap-3 rounded-lg border p-3" :class="colorMap[col.color]?.bg">
          <span class="text-2xl font-bold font-data">{{ col.clients.length }}</span>
          <span class="text-xs font-medium">{{ col.title }}</span>
        </div>
      </div>

      <!-- Loading skeleton -->
      <template v-if="loading">
        <div class="flex gap-4 overflow-hidden">
          <div v-for="n in 4" :key="n" class="w-72 shrink-0 space-y-3 rounded-xl border border-wc-border bg-wc-bg-secondary p-3 sm:w-[280px]">
            <div class="h-8 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
            <div v-for="m in 3" :key="m" class="h-24 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
          </div>
        </div>
      </template>

      <!-- Kanban Board -->
      <div v-else class="kanban-scroll -mx-4 flex gap-4 overflow-x-auto px-4 pb-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8" style="scroll-snap-type: x mandatory;">
        <div
          v-for="(col, colKey) in columns"
          :key="colKey"
          class="kanban-column flex w-72 shrink-0 flex-col rounded-xl border border-t-[3px] border-wc-border bg-wc-bg-secondary sm:w-[280px]"
          :class="[colorMap[col.color]?.border, dragOverColumn === colKey ? 'ring-2 ring-wc-accent/40 bg-wc-accent/5' : '']"
          style="scroll-snap-align: start; min-height: 420px;"
          @dragover.prevent="onDragOver($event, colKey)"
          @dragleave="onDragLeave($event)"
          @drop.prevent="onDrop($event, colKey)"
        >
          <!-- Column header -->
          <div class="flex items-center justify-between border-b border-wc-border px-4 py-3">
            <div class="flex items-center gap-2">
              <span class="h-2 w-2 rounded-full" :class="colorMap[col.color]?.dot"></span>
              <h3 class="text-sm font-semibold text-wc-text">{{ col.title }}</h3>
            </div>
            <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full px-1.5 text-[10px] font-bold" :class="colorMap[col.color]?.badge">
              {{ col.clients.length }}
            </span>
          </div>

          <!-- Column body -->
          <div class="flex-1 space-y-2.5 overflow-y-auto p-3" style="max-height: 65vh;">
            <template v-if="col.clients.length > 0">
              <div
                v-for="client in col.clients"
                :key="client.id"
                class="kanban-card group relative cursor-grab rounded-lg border border-wc-border bg-wc-bg-tertiary p-3 shadow-sm transition-all hover:shadow-md hover:border-wc-accent/30 active:cursor-grabbing active:shadow-lg"
                draggable="true"
                @dragstart="onDragStart($event, client.id)"
                @dragend="onDragEnd($event)"
              >
                <!-- Card top: avatar + name -->
                <div class="flex items-start gap-2.5">
                  <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-wc-accent/15">
                    <span class="text-sm font-semibold text-wc-accent">{{ client.avatar_initial || (client.name || 'C').charAt(0).toUpperCase() }}</span>
                  </div>
                  <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-wc-text truncate leading-tight">{{ client.name }}</p>
                    <span class="mt-0.5 inline-block rounded-full bg-wc-accent/10 px-1.5 py-0.5 text-[10px] font-semibold text-wc-accent leading-none">
                      {{ client.plan_label }}
                    </span>
                  </div>
                </div>

                <!-- Activity info (matching blade: days_since_activity, last_checkin_date, last_training_date) -->
                <div class="mt-2.5 flex items-center gap-3 text-[11px] text-wc-text-tertiary">
                  <!-- Days since activity -->
                  <div class="flex items-center gap-1" title="Ultima actividad">
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <span :class="activityColorClass(client.days_since_activity)">
                      {{ activityLabel(client.days_since_activity) }}
                    </span>
                  </div>

                  <!-- Last check-in -->
                  <div v-if="client.last_checkin_date" class="flex items-center gap-1" title="Ultimo check-in">
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    {{ client.last_checkin_date }}
                  </div>

                  <!-- Last training -->
                  <div v-if="client.last_training_date" class="flex items-center gap-1" title="Ultimo entrenamiento">
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" />
                    </svg>
                    {{ client.last_training_date }}
                  </div>
                </div>

                <!-- Badges row (with icons matching blade) -->
                <div v-if="client.pending_checkins > 0 || client.unread_messages > 0" class="mt-2 flex items-center gap-1.5">
                  <span v-if="client.pending_checkins > 0" class="inline-flex items-center gap-0.5 rounded-full bg-orange-500/10 px-1.5 py-0.5 text-[10px] font-semibold text-orange-500">
                    <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>
                    {{ client.pending_checkins }} check-in{{ client.pending_checkins > 1 ? 's' : '' }}
                  </span>
                  <span v-if="client.unread_messages > 0" class="inline-flex items-center gap-0.5 rounded-full bg-violet-500/10 px-1.5 py-0.5 text-[10px] font-semibold text-violet-500">
                    <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                    </svg>
                    {{ client.unread_messages }}
                  </span>
                </div>

                <!-- Detail button (visible on hover, matching blade) -->
                <button
                  @click.stop="openDetail(client.id)"
                  class="mt-2 flex w-full items-center justify-center gap-1 rounded-md border border-wc-border bg-wc-bg-secondary/50 py-1 text-[11px] font-medium text-wc-text-secondary opacity-0 transition-all group-hover:opacity-100 hover:bg-wc-bg-secondary hover:text-wc-text"
                >
                  <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                  </svg>
                  Ver detalle
                </button>
              </div>
            </template>

            <!-- Empty column -->
            <div v-else class="flex flex-col items-center justify-center rounded-lg border border-dashed border-wc-border/50 py-8 text-center">
              <svg class="h-8 w-8 text-wc-text-tertiary/50" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
              </svg>
              <p class="mt-2 text-xs text-wc-text-tertiary/70">Sin clientes</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Drag hint (with icon, matching blade) -->
      <p class="hidden text-center text-[11px] text-wc-text-tertiary sm:block">
        <svg class="mr-1 inline h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
        </svg>
        Arrastra las tarjetas entre columnas para reclasificar clientes
      </p>

      <!-- ═══ Client Detail Modal ═══ -->
      <Transition name="fade">
        <div
          v-if="showDetail"
          class="fixed inset-0 z-50 flex items-center justify-center p-4"
        >
          <!-- Backdrop -->
          <div class="absolute inset-0 bg-black/60" @click="closeDetail"></div>

          <!-- Modal content -->
          <Transition name="modal-scale" appear>
            <div class="relative w-full max-w-md rounded-xl border border-wc-border bg-wc-bg-tertiary shadow-2xl">
              <!-- Close button -->
              <button
                @click="closeDetail"
                class="absolute right-3 top-3 z-10 flex h-7 w-7 items-center justify-center rounded-lg text-wc-text-tertiary hover:bg-wc-bg-secondary hover:text-wc-text transition-colors"
              >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
              </button>

              <!-- Loading state -->
              <div v-if="detailLoading" class="flex items-center justify-center p-12">
                <svg class="h-8 w-8 animate-spin text-wc-accent" viewBox="0 0 24 24" fill="none">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
              </div>

              <!-- Error state -->
              <div v-else-if="!detailClient" class="p-12 text-center">
                <p class="text-sm text-wc-text-secondary">No se pudo cargar el detalle del cliente.</p>
                <button @click="closeDetail" class="mt-3 text-xs text-wc-accent hover:underline">Cerrar</button>
              </div>

              <!-- Detail content -->
              <div v-else class="p-6">
                <!-- Client header -->
                <div class="flex items-center gap-4">
                  <div class="flex h-14 w-14 items-center justify-center rounded-full bg-wc-accent/15">
                    <span class="text-xl font-bold text-wc-accent">{{ detailClient.avatar_initial }}</span>
                  </div>
                  <div>
                    <h3 class="text-lg font-semibold text-wc-text">{{ detailClient.name }}</h3>
                    <p class="text-xs text-wc-text-tertiary">{{ detailClient.email }}</p>
                    <div class="mt-1 flex items-center gap-2">
                      <span class="rounded-full bg-wc-accent/10 px-2 py-0.5 text-[10px] font-semibold text-wc-accent">{{ detailClient.plan_label }}</span>
                      <span class="rounded-full bg-wc-bg-secondary px-2 py-0.5 text-[10px] font-medium text-wc-text-secondary">{{ detailClient.status_label }}</span>
                    </div>
                  </div>
                </div>

                <!-- Stats grid -->
                <div class="mt-5 grid grid-cols-3 gap-3">
                  <div class="rounded-lg bg-wc-bg-secondary p-3 text-center">
                    <p class="text-lg font-bold font-data text-wc-text">Nv. {{ detailClient.xp_level }}</p>
                    <p class="text-[10px] text-wc-text-tertiary">Nivel XP</p>
                  </div>
                  <div class="rounded-lg bg-wc-bg-secondary p-3 text-center">
                    <p class="text-lg font-bold font-data text-wc-text">{{ formatNumber(detailClient.xp_total) }}</p>
                    <p class="text-[10px] text-wc-text-tertiary">XP Total</p>
                  </div>
                  <div class="rounded-lg bg-wc-bg-secondary p-3 text-center">
                    <p class="text-lg font-bold font-data text-wc-text">{{ detailClient.streak_days }}</p>
                    <p class="text-[10px] text-wc-text-tertiary">Racha (dias)</p>
                  </div>
                </div>

                <!-- Info rows -->
                <div class="mt-4 space-y-2">
                  <div class="flex items-center justify-between rounded-lg bg-wc-bg-secondary/50 px-3 py-2">
                    <span class="text-xs text-wc-text-tertiary">Fecha inicio</span>
                    <span class="text-xs font-medium text-wc-text">{{ detailClient.fecha_inicio }}</span>
                  </div>
                  <div class="flex items-center justify-between rounded-lg bg-wc-bg-secondary/50 px-3 py-2">
                    <span class="text-xs text-wc-text-tertiary">Ultimo check-in</span>
                    <span class="text-xs font-medium text-wc-text">{{ detailClient.last_checkin }}</span>
                  </div>
                  <div v-if="detailClient.last_checkin_bienestar" class="flex items-center justify-between rounded-lg bg-wc-bg-secondary/50 px-3 py-2">
                    <span class="text-xs text-wc-text-tertiary">Bienestar</span>
                    <span class="text-xs font-medium text-wc-text">{{ detailClient.last_checkin_bienestar }}/10</span>
                  </div>
                  <div class="flex items-center justify-between rounded-lg bg-wc-bg-secondary/50 px-3 py-2">
                    <span class="text-xs text-wc-text-tertiary">Plan activo</span>
                    <span class="text-xs font-medium text-wc-text capitalize">{{ detailClient.active_plan_type }}</span>
                  </div>
                </div>

                <!-- Recent notes -->
                <div v-if="detailClient.recent_notes && detailClient.recent_notes.length > 0" class="mt-4">
                  <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary mb-2">Notas recientes</p>
                  <div class="space-y-1.5">
                    <div v-for="(note, idx) in detailClient.recent_notes" :key="idx" class="rounded-lg border border-wc-border/50 bg-wc-bg-secondary/30 px-3 py-2">
                      <p class="text-xs text-wc-text">{{ note.note }}</p>
                      <p class="mt-0.5 text-[10px] text-wc-text-tertiary">{{ note.date }} &middot; {{ note.type }}</p>
                    </div>
                  </div>
                </div>

                <!-- Actions -->
                <div class="mt-5 flex items-center gap-2">
                  <RouterLink
                    to="/coach/checkins"
                    class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg bg-wc-accent px-3 py-2 text-xs font-medium text-white hover:bg-red-700 transition-colors"
                  >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    Check-ins
                  </RouterLink>
                  <RouterLink
                    to="/coach/messages"
                    class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-xs font-medium text-wc-text hover:bg-wc-bg-tertiary transition-colors"
                  >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                    </svg>
                    Mensajes
                  </RouterLink>
                  <RouterLink
                    to="/coach/notes"
                    class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-xs font-medium text-wc-text hover:bg-wc-bg-tertiary transition-colors"
                  >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                    </svg>
                    Notas
                  </RouterLink>
                </div>
              </div>
            </div>
          </Transition>
        </div>
      </Transition>

    </div>
  </CoachLayout>
</template>

<style scoped>
/* ── Transitions ────────────────────────────────────────────────────── */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.modal-scale-enter-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.modal-scale-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}
.modal-scale-enter-from {
  opacity: 0;
  transform: scale(0.95);
}
.modal-scale-leave-to {
  opacity: 0;
  transform: scale(0.95);
}

/* ── Custom scrollbar for kanban ────────────────────────────────────── */
.kanban-scroll {
  scrollbar-width: thin;
  scrollbar-color: var(--color-wc-border) transparent;
}
.kanban-scroll::-webkit-scrollbar {
  height: 6px;
}
.kanban-scroll::-webkit-scrollbar-track {
  background: transparent;
}
.kanban-scroll::-webkit-scrollbar-thumb {
  background-color: var(--color-wc-border);
  border-radius: 3px;
}

/* ── Kanban card drag & hover transforms ────────────────────────────── */
.kanban-card {
  transition: transform 0.15s ease, box-shadow 0.15s ease, opacity 0.15s ease;
}
.kanban-card:hover {
  transform: translateY(-1px);
}
.kanban-card[draggable="true"]:active {
  transform: rotate(2deg) scale(1.02);
}

/* ── Mobile snap scrolling ──────────────────────────────────────────── */
@media (max-width: 640px) {
  .kanban-scroll {
    scroll-snap-type: x mandatory;
    -webkit-overflow-scrolling: touch;
  }
  .kanban-column {
    scroll-snap-align: center;
  }
}
</style>
