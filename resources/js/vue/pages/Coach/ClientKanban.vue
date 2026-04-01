<script setup>
import { ref, onMounted, computed } from 'vue';
import { useApi } from '../../composables/useApi';
import CoachLayout from '../../layouts/CoachLayout.vue';

const api = useApi();
const loading = ref(true);
const search = ref('');
const dragOverColumn = ref(null);
const draggedClientId = ref(null);

const columns = ref({
    nuevo: { title: 'Nuevo', color: 'blue', clients: [] },
    activo: { title: 'Activo', color: 'emerald', clients: [] },
    riesgo: { title: 'Riesgo', color: 'amber', clients: [] },
    inactivo: { title: 'Inactivo', color: 'red', clients: [] },
});

const totalClients = computed(() => {
    return Object.values(columns.value).reduce((sum, col) => sum + col.clients.length, 0);
});

const colorMap = {
    blue: { border: 'border-t-blue-500', bg: 'bg-blue-500/10 text-blue-500 border-blue-500/20', badge: 'bg-blue-500 text-white', dot: 'bg-blue-500', ring: 'ring-blue-500/40' },
    emerald: { border: 'border-t-emerald-500', bg: 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20', badge: 'bg-emerald-500 text-white', dot: 'bg-emerald-500', ring: 'ring-emerald-500/40' },
    amber: { border: 'border-t-amber-500', bg: 'bg-amber-500/10 text-amber-500 border-amber-500/20', badge: 'bg-amber-500 text-white', dot: 'bg-amber-500', ring: 'ring-amber-500/40' },
    red: { border: 'border-t-red-500', bg: 'bg-red-500/10 text-red-500 border-red-500/20', badge: 'bg-red-500 text-white', dot: 'bg-red-500', ring: 'ring-red-500/40' },
};

function onDragStart(event, clientId) {
    draggedClientId.value = clientId;
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('text/plain', clientId.toString());
    requestAnimationFrame(() => {
        event.target.style.opacity = '0.5';
    });
}

function onDragEnd(event) {
    event.target.style.opacity = '1';
    dragOverColumn.value = null;
    draggedClientId.value = null;
}

function onDragOver(event, colKey) {
    event.preventDefault();
    event.dataTransfer.dropEffect = 'move';
    dragOverColumn.value = colKey;
}

function onDragLeave(event, colKey) {
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

    // Move client locally
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
        await api.post('/api/v/coach/kanban/move', { client_id: clientId, column: colKey });
    } catch (e) {
        // Reload on failure
        loadBoard();
    }
}

// Simple move buttons for mobile
async function moveClient(clientId, targetCol) {
    let movedClient = null;
    for (const key in columns.value) {
        const idx = columns.value[key].clients.findIndex(c => c.id === clientId);
        if (idx !== -1) {
            movedClient = columns.value[key].clients.splice(idx, 1)[0];
            break;
        }
    }
    if (movedClient) {
        columns.value[targetCol].clients.push(movedClient);
    }
    try {
        await api.post('/api/v/coach/kanban/move', { client_id: clientId, column: targetCol });
    } catch (e) {
        loadBoard();
    }
}

async function loadBoard() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/v/coach/kanban');
        if (data.columns) {
            columns.value = data.columns;
        }
    } catch (e) {
        // silent
    } finally {
        loading.value = false;
    }
}

onMounted(loadBoard);
</script>

<template>
  <CoachLayout>
    <div class="space-y-6">

      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">Kanban Clientes</h1>
          <p class="mt-1 text-sm text-wc-text-tertiary">{{ totalClients }} cliente{{ totalClients !== 1 ? 's' : '' }} -- Vista por actividad</p>
        </div>
        <div class="flex items-center gap-3">
          <div class="relative w-full sm:w-64">
            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <input v-model="search" type="text" placeholder="Buscar cliente..." class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary py-2 pl-10 pr-4 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent" />
          </div>
          <RouterLink to="/coach/clients" class="flex h-9 w-9 items-center justify-center rounded-lg border border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:text-wc-text transition-colors" title="Vista lista">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
            </svg>
          </RouterLink>
          <button @click="loadBoard" class="flex h-9 w-9 items-center justify-center rounded-lg border border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:text-wc-text transition-colors" title="Actualizar">
            <svg class="h-4 w-4" :class="{ 'animate-spin': loading }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.992 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Summary stats -->
      <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div v-for="(col, key) in columns" :key="key" class="flex items-center gap-3 rounded-lg border p-3" :class="colorMap[col.color].bg">
          <span class="text-2xl font-bold font-data">{{ col.clients.length }}</span>
          <span class="text-xs font-medium">{{ col.title }}</span>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex items-center justify-center py-12">
        <svg class="h-8 w-8 animate-spin text-wc-accent" viewBox="0 0 24 24" fill="none">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
      </div>

      <!-- Kanban Board -->
      <div v-else class="-mx-4 flex gap-4 overflow-x-auto px-4 pb-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8" style="scroll-snap-type: x mandatory;">
        <div
          v-for="(col, colKey) in columns"
          :key="colKey"
          class="flex w-72 shrink-0 flex-col rounded-xl border border-t-[3px] border-wc-border bg-wc-bg-secondary sm:w-[280px]"
          :class="[colorMap[col.color].border, dragOverColumn === colKey ? 'ring-2 ring-wc-accent/40 bg-wc-accent/5' : '']"
          style="scroll-snap-align: start; min-height: 420px;"
          @dragover.prevent="onDragOver($event, colKey)"
          @dragleave="onDragLeave($event, colKey)"
          @drop.prevent="onDrop($event, colKey)"
        >
          <!-- Column header -->
          <div class="flex items-center justify-between border-b border-wc-border px-4 py-3">
            <div class="flex items-center gap-2">
              <span class="h-2 w-2 rounded-full" :class="colorMap[col.color].dot"></span>
              <h3 class="text-sm font-semibold text-wc-text">{{ col.title }}</h3>
            </div>
            <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full px-1.5 text-[10px] font-bold" :class="colorMap[col.color].badge">
              {{ col.clients.length }}
            </span>
          </div>

          <!-- Column body -->
          <div class="flex-1 space-y-2.5 overflow-y-auto p-3" style="max-height: 65vh;">
            <div
              v-for="client in col.clients"
              :key="client.id"
              class="group relative cursor-grab rounded-lg border border-wc-border bg-wc-bg-tertiary p-3 shadow-sm transition-all hover:shadow-md hover:border-wc-accent/30 active:cursor-grabbing"
              draggable="true"
              @dragstart="onDragStart($event, client.id)"
              @dragend="onDragEnd($event)"
            >
              <div class="flex items-start gap-2.5">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-wc-accent/15">
                  <span class="text-sm font-semibold text-wc-accent">{{ (client.name || 'C').charAt(0) }}</span>
                </div>
                <div class="min-w-0 flex-1">
                  <p class="text-sm font-medium text-wc-text truncate leading-tight">{{ client.name }}</p>
                  <span class="mt-0.5 inline-block rounded-full bg-wc-accent/10 px-1.5 py-0.5 text-[10px] font-semibold text-wc-accent leading-none">
                    {{ client.plan_label }}
                  </span>
                </div>
              </div>
              <div class="mt-2.5 flex items-center gap-3 text-[11px] text-wc-text-tertiary">
                <div v-if="client.adherence !== undefined" class="flex items-center gap-1" title="Adherencia">
                  <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75Z" />
                  </svg>
                  <span>{{ client.adherence }}%</span>
                </div>
                <div v-if="client.bienestar" class="flex items-center gap-1" title="Bienestar">
                  <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Z" />
                  </svg>
                  <span>{{ client.bienestar }}/10</span>
                </div>
              </div>
              <div v-if="client.pending_checkins > 0 || client.unread_messages > 0" class="mt-2 flex items-center gap-1.5">
                <span v-if="client.pending_checkins > 0" class="inline-flex items-center rounded-full bg-orange-500/10 px-1.5 py-0.5 text-[10px] font-semibold text-orange-500">
                  {{ client.pending_checkins }} check-in{{ client.pending_checkins > 1 ? 's' : '' }}
                </span>
                <span v-if="client.unread_messages > 0" class="inline-flex items-center rounded-full bg-violet-500/10 px-1.5 py-0.5 text-[10px] font-semibold text-violet-500">
                  {{ client.unread_messages }} msg
                </span>
              </div>
            </div>

            <!-- Empty column -->
            <div v-if="col.clients.length === 0" class="flex flex-col items-center justify-center rounded-lg border border-dashed border-wc-border/50 py-8 text-center">
              <svg class="h-8 w-8 text-wc-text-tertiary/50" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
              </svg>
              <p class="mt-2 text-xs text-wc-text-tertiary/70">Sin clientes</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Drag hint -->
      <p class="hidden text-center text-[11px] text-wc-text-tertiary sm:block">
        Arrastra las tarjetas entre columnas para reclasificar clientes
      </p>
    </div>
  </CoachLayout>
</template>
