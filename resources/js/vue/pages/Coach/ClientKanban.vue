<script setup>
import { ref, watch, onMounted, onBeforeUnmount, computed } from 'vue';
import { useApi } from '../../composables/useApi';
import { useRouter } from 'vue-router';
import CoachLayout from '../../layouts/CoachLayout.vue';
import WcPageHeader from '../../components/WcPageHeader.vue';
import AvatarConic from '../../components/coach/ios/AvatarConic.vue';
import EmptyState from '../../components/coach/ios/EmptyState.vue';

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

// ─── Impersonation ────────────────────────────────────────────────────────────
const impersonating = ref(false);
async function impersonateClient(client) {
  if (impersonating.value || !client?.id) return;
  impersonating.value = true;
  try {
    const { data } = await api.post(`/api/v/coach/clients/${client.id}/impersonate`);
    // Backup actual sesión coach.
    localStorage.setItem('wc_token_backup',     localStorage.getItem('wc_token') || '');
    localStorage.setItem('wc_user_type_backup', localStorage.getItem('wc_user_type') || '');
    localStorage.setItem('wc_user_id_backup',   localStorage.getItem('wc_user_id') || '');
    localStorage.setItem('wc_user_name_backup', localStorage.getItem('wc_user_name') || '');
    localStorage.setItem('wc_user_portal_backup', localStorage.getItem('wc_user_portal') || '/coach');
    // Swap a sesión cliente.
    localStorage.setItem('wc_token',      data.token);
    localStorage.setItem('wc_user_type',  'client');
    localStorage.setItem('wc_user_id',    String(data.client_id));
    localStorage.setItem('wc_user_name',  data.client_name || 'Cliente');
    localStorage.setItem('wc_user_portal', '/client');
    localStorage.setItem('wc_impersonating_by_coach', '1');
    localStorage.setItem('wc_impersonating_token_key', data.token);
    localStorage.setItem('wc_impersonation_client_id', String(data.client_id));
    if (data.expires_at) {
      const expiresMs = new Date(data.expires_at).getTime();
      if (!isNaN(expiresMs)) {
        localStorage.setItem('wc_impersonation_expires_at', String(expiresMs));
      }
    }
    window.location.href = data.redirect_url || '/client';
  } catch (err) {
    impersonating.value = false;
  }
}

const columns = ref({
  nuevo:    { title: 'Nuevos',    clients: [] },
  activo:   { title: 'Activos',   clients: [] },
  riesgo:   { title: 'En Riesgo', clients: [] },
  inactivo: { title: 'Inactivos', clients: [] },
});

const totalClients = computed(() => {
  return Object.values(columns.value).reduce((sum, col) => sum + col.clients.length, 0);
});

// Full hardcoded class strings — Tailwind JIT requires no dynamic concatenation
const columnStyles = {
  nuevo:   { statTop: 'border-t-2 border-t-sky-400',     cardExtra: 'border-l-2 border-l-sky-400/60',    badge: 'bg-sky-500/10 text-sky-400',              dot: 'bg-sky-400'     },
  activo:  { statTop: 'border-t-2 border-t-emerald-400', cardExtra: '',                                   badge: 'bg-emerald-500/10 text-emerald-400',       dot: 'bg-emerald-400' },
  riesgo:  { statTop: 'border-t-2 border-t-amber-400',   cardExtra: 'border-l-4 border-l-amber-400/70',  badge: 'bg-amber-500/10 text-amber-400',           dot: 'bg-amber-400'   },
  inactivo:{ statTop: '',                                 cardExtra: 'opacity-75',                         badge: 'bg-wc-bg-secondary text-wc-text-tertiary', dot: 'bg-zinc-500'    },
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

function activityColorClass(days) {
  if (days === null || days === undefined) return 'text-wc-accent';
  if (days > 14) return 'text-wc-accent font-semibold';
  if (days > 7)  return 'text-amber-400 font-semibold';
  return 'text-emerald-400';
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

      <WcPageHeader contextLabel="MIS CLIENTES" title="KANBAN" :subtitle="`${totalClients} cliente${totalClients !== 1 ? 's' : ''} · Vista por actividad`">
        <template #actions>
          <div class="flex items-center gap-2">
            <div class="relative w-56">
              <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
              </svg>
              <input
                v-model="search"
                type="text"
                placeholder="Buscar cliente..."
                class="w-full rounded-button border border-wc-border bg-wc-bg-secondary py-2 pl-10 pr-4 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
              />
            </div>
            <RouterLink
              to="/coach/clients"
              class="flex h-9 w-9 items-center justify-center rounded-button border border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:text-wc-text transition-colors"
              title="Vista lista"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
              </svg>
            </RouterLink>
            <button
              @click="loadBoard"
              class="flex h-9 w-9 items-center justify-center rounded-button border border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:text-wc-text transition-colors"
              title="Actualizar"
            >
              <svg class="h-4 w-4" :class="{ 'animate-spin': loading }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.992 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" />
              </svg>
            </button>
          </div>
        </template>
      </WcPageHeader>

      <!-- Summary stats -->
      <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 anim-entry anim-entry-2">
        <div
          v-for="(col, key) in columns"
          :key="key"
          class="flex items-center gap-3 rounded-[14px] border border-[var(--b1)] p-3"
          style="background: var(--s2); box-shadow: var(--shadow-card-ios);"
          :class="columnStyles[key]?.statTop"
        >
          <span class="h-2 w-2 rounded-full shrink-0" :class="columnStyles[key]?.dot"></span>
          <span class="text-2xl font-bold font-data text-wc-text">{{ col.clients.length }}</span>
          <span class="text-xs font-medium text-wc-text-secondary">{{ col.title }}</span>
        </div>
      </div>

      <!-- Loading skeleton -->
      <template v-if="loading">
        <div class="flex gap-4 overflow-hidden">
          <div v-for="n in 4" :key="n" class="w-72 shrink-0 space-y-3 rounded-card border border-wc-border bg-wc-bg-secondary p-3 sm:w-[280px]">
            <div class="h-8 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
            <div v-for="m in 3" :key="m" class="h-24 animate-pulse rounded-button bg-wc-bg-tertiary"></div>
          </div>
        </div>
      </template>

      <!-- Kanban Board -->
      <div v-else class="kanban-scroll -mx-4 flex gap-4 overflow-x-auto px-4 pb-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 anim-entry anim-entry-3" style="scroll-snap-type: x mandatory;">
        <div
          v-for="(col, colKey) in columns"
          :key="colKey"
          class="kanban-column flex w-72 shrink-0 flex-col rounded-[14px] border border-[var(--b1)] sm:w-[280px]"
          :class="dragOverColumn === colKey ? 'ring-2 ring-wc-accent/40 bg-wc-accent/5' : ''"
          style="scroll-snap-align: start; min-height: 420px; background: var(--s2); box-shadow: var(--shadow-card-ios);"
          @dragover.prevent="onDragOver($event, colKey)"
          @dragleave="onDragLeave($event)"
          @drop.prevent="onDrop($event, colKey)"
        >
          <!-- Column header -->
          <div class="flex items-center justify-between border-b border-wc-border px-4 py-3">
            <div class="flex items-center gap-2">
              <span class="h-2 w-2 rounded-full" :class="columnStyles[colKey]?.dot"></span>
              <h3 class="text-sm font-semibold text-wc-text">{{ col.title }}</h3>
            </div>
            <span
              class="inline-flex h-5 min-w-5 items-center justify-center rounded-full px-1.5 text-[10px] font-bold"
              :class="columnStyles[colKey]?.badge"
            >
              {{ col.clients.length }}
            </span>
          </div>

          <!-- Column body -->
          <div class="flex-1 space-y-2.5 overflow-y-auto p-3" style="max-height: 65vh;">
            <template v-if="col.clients.length > 0">
              <div
                v-for="client in col.clients"
                :key="client.id"
                class="kanban-card group relative cursor-grab rounded-card border border-wc-border bg-wc-bg-tertiary p-3 shadow-sm transition-all hover:shadow-md hover:border-wc-accent/30 active:cursor-grabbing active:shadow-lg"
                :class="columnStyles[colKey]?.cardExtra"
                draggable="true"
                @dragstart="onDragStart($event, client.id)"
                @dragend="onDragEnd($event)"
              >
                <!-- Card top: avatar + name -->
                <div class="flex items-start gap-2.5">
                  <AvatarConic
                    :initial="client.avatar_initial || (client.name || 'C').charAt(0).toUpperCase()"
                    :image-url="client.photo_url || ''"
                    tone="accent"
                    size="sm"
                  />
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
                  <span v-if="client.pending_checkins > 0" class="inline-flex items-center gap-0.5 rounded-full bg-wc-accent/10 px-1.5 py-0.5 text-[10px] font-semibold text-wc-accent">
                    <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>
                    {{ client.pending_checkins }} check-in{{ client.pending_checkins > 1 ? 's' : '' }}
                  </span>
                  <span v-if="client.unread_messages > 0" class="inline-flex items-center gap-0.5 rounded-full bg-wc-accent/15 px-1.5 py-0.5 text-[10px] font-semibold text-wc-accent">
                    <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                    </svg>
                    {{ client.unread_messages }}
                  </span>
                </div>

                <!-- Detail button (visible on hover, matching blade) -->
                <button
                  @click.stop="openDetail(client.id)"
                  class="mt-2 flex w-full items-center justify-center gap-1 rounded-button border border-wc-border bg-wc-bg-secondary/50 py-1 text-[11px] font-medium text-wc-text-secondary opacity-0 transition-all group-hover:opacity-100 hover:bg-wc-bg-secondary hover:text-wc-text"
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
            <EmptyState v-else kind="activity" title="Sin clientes" subtitle="Arrastra tarjetas aquí" />

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
            <div class="relative w-full max-w-md rounded-card border border-wc-border bg-wc-bg-tertiary shadow-2xl">
              <!-- Close button -->
              <button
                @click="closeDetail"
                class="absolute right-3 top-3 z-10 flex h-7 w-7 items-center justify-center rounded-button text-wc-text-tertiary hover:bg-wc-bg-secondary hover:text-wc-text transition-colors"
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
                  <AvatarConic
                    :initial="detailClient.avatar_initial || (detailClient.name || 'C').charAt(0).toUpperCase()"
                    :image-url="detailClient.photo_url || ''"
                    tone="accent"
                    size="lg"
                  />
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
                  <div class="rounded-button bg-wc-bg-secondary p-3 text-center">
                    <p class="text-lg font-bold font-data text-wc-text">Nv. {{ detailClient.xp_level }}</p>
                    <p class="text-[10px] text-wc-text-tertiary">Nivel XP</p>
                  </div>
                  <div class="rounded-button bg-wc-bg-secondary p-3 text-center">
                    <p class="text-lg font-bold font-data text-wc-text">{{ formatNumber(detailClient.xp_total) }}</p>
                    <p class="text-[10px] text-wc-text-tertiary">XP Total</p>
                  </div>
                  <div class="rounded-button bg-wc-bg-secondary p-3 text-center">
                    <p class="text-lg font-bold font-data text-wc-text">{{ detailClient.streak_days }}</p>
                    <p class="text-[10px] text-wc-text-tertiary">Racha (dias)</p>
                  </div>
                </div>

                <!-- Info rows -->
                <div class="mt-4 space-y-2">
                  <div class="flex items-center justify-between rounded-button bg-wc-bg-secondary/50 px-3 py-2">
                    <span class="text-xs text-wc-text-tertiary">Fecha inicio</span>
                    <span class="text-xs font-medium text-wc-text">{{ detailClient.fecha_inicio }}</span>
                  </div>
                  <div class="flex items-center justify-between rounded-button bg-wc-bg-secondary/50 px-3 py-2">
                    <span class="text-xs text-wc-text-tertiary">Ultimo check-in</span>
                    <span class="text-xs font-medium text-wc-text">{{ detailClient.last_checkin }}</span>
                  </div>
                  <div v-if="detailClient.last_checkin_bienestar" class="flex items-center justify-between rounded-lg bg-wc-bg-secondary/50 px-3 py-2">
                    <span class="text-xs text-wc-text-tertiary">Bienestar</span>
                    <span class="text-xs font-medium text-wc-text">{{ detailClient.last_checkin_bienestar }}/10</span>
                  </div>
                  <div class="flex items-center justify-between rounded-button bg-wc-bg-secondary/50 px-3 py-2">
                    <span class="text-xs text-wc-text-tertiary">Plan activo</span>
                    <span class="text-xs font-medium text-wc-text capitalize">{{ detailClient.active_plan_type }}</span>
                  </div>
                </div>

                <!-- Recent notes -->
                <div v-if="detailClient.recent_notes && detailClient.recent_notes.length > 0" class="mt-4">
                  <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary mb-2">Notas recientes</p>
                  <div class="space-y-1.5">
                    <div v-for="(note, idx) in detailClient.recent_notes" :key="idx" class="rounded-button border border-wc-border/50 bg-wc-bg-secondary/30 px-3 py-2">
                      <p class="text-xs text-wc-text">{{ note.note }}</p>
                      <p class="mt-0.5 text-[10px] text-wc-text-tertiary">{{ note.date }} &middot; {{ note.type }}</p>
                    </div>
                  </div>
                </div>

                <!-- Actions -->
                <div class="mt-5 flex items-center gap-2">
                  <RouterLink
                    to="/coach/checkins"
                    class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-button bg-wc-accent px-3 py-2 text-xs font-medium text-white hover:bg-wc-accent-hover transition-colors"
                  >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    Check-ins
                  </RouterLink>
                  <RouterLink
                    to="/coach/messages"
                    class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-button border border-wc-border bg-wc-bg-secondary px-3 py-2 text-xs font-medium text-wc-text hover:bg-wc-bg-tertiary transition-colors"
                  >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                    </svg>
                    Mensajes
                  </RouterLink>
                  <RouterLink
                    to="/coach/notes"
                    class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-button border border-wc-border bg-wc-bg-secondary px-3 py-2 text-xs font-medium text-wc-text hover:bg-wc-bg-tertiary transition-colors"
                  >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                    </svg>
                    Notas
                  </RouterLink>
                </div>

                <!-- Impersonate (ver portal del cliente) -->
                <button
                  type="button"
                  @click="impersonateClient(detailClient)"
                  :disabled="impersonating"
                  class="mt-3 inline-flex w-full items-center justify-center gap-1.5 rounded-button border border-wc-border bg-wc-bg-secondary px-3 py-2 text-xs font-medium text-wc-text hover:border-wc-accent hover:text-wc-accent transition-colors disabled:opacity-50"
                >
                  <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                  </svg>
                  {{ impersonating ? 'Entrando…' : 'Ver portal como cliente' }}
                </button>
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
