<script setup>
import { ref, watch, onMounted, computed } from 'vue';
import { useApi } from '../../composables/useApi';
import CoachLayout from '../../layouts/CoachLayout.vue';

const api = useApi();
const loading = ref(true);
const search = ref('');
const statusFilter = ref('');
const sortBy = ref('name');
const clients = ref([]);
const expandedClient = ref(null);
const totalClients = ref(0);

let debounceTimer = null;

const filteredClients = computed(() => {
    let list = clients.value;
    if (search.value) {
        const q = search.value.toLowerCase();
        list = list.filter(c => c.name.toLowerCase().includes(q));
    }
    if (statusFilter.value) {
        list = list.filter(c => c.status === statusFilter.value);
    }
    if (sortBy.value === 'name') {
        list = [...list].sort((a, b) => a.name.localeCompare(b.name));
    } else if (sortBy.value === 'activity') {
        list = [...list].sort((a, b) => (a.days_since_activity || 999) - (b.days_since_activity || 999));
    } else if (sortBy.value === 'adherence') {
        list = [...list].sort((a, b) => (b.adherence || 0) - (a.adherence || 0));
    }
    return list;
});

function toggleExpand(id) {
    expandedClient.value = expandedClient.value === id ? null : id;
}

async function loadClients() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/v/coach/clients');
        clients.value = data.clients || [];
        totalClients.value = data.totalClients || clients.value.length;
    } catch (e) {
        clients.value = [];
    } finally {
        loading.value = false;
    }
}

watch(search, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(loadClients, 300);
});

onMounted(loadClients);
</script>

<template>
  <CoachLayout>
    <div class="space-y-6">

      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">Mis Clientes</h1>
          <p class="mt-1 text-sm text-wc-text-tertiary">{{ totalClients }} cliente{{ totalClients !== 1 ? 's' : '' }} activo{{ totalClients !== 1 ? 's' : '' }}</p>
        </div>
        <div class="flex items-center gap-3">
          <!-- Kanban toggle -->
          <RouterLink
            to="/coach/kanban"
            class="flex h-9 w-9 items-center justify-center rounded-lg border border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:text-wc-text transition-colors"
            title="Vista Kanban"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5v15m6-15v15m-10.875 0h15.75c.621 0 1.125-.504 1.125-1.125V5.625c0-.621-.504-1.125-1.125-1.125H4.125C3.504 4.5 3 5.004 3 5.625v12.75c0 .621.504 1.125 1.125 1.125Z" />
            </svg>
          </RouterLink>

          <!-- Search -->
          <div class="relative w-full sm:w-72">
            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <input
              v-model="search"
              type="text"
              placeholder="Buscar por nombre..."
              class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary py-2 pl-10 pr-4 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
            />
          </div>

          <!-- Status filter (Vue enhancement, not in Blade) -->
          <select
            v-model="statusFilter"
            class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
          >
            <option value="">Todos</option>
            <option value="active">Activos</option>
            <option value="risk">En riesgo</option>
            <option value="inactive">Inactivos</option>
          </select>
        </div>
      </div>

      <!-- Loading skeletons -->
      <template v-if="loading">
        <div v-for="n in 4" :key="n" class="animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
          <div class="flex items-center gap-4">
            <div class="h-11 w-11 shrink-0 rounded-full bg-wc-border/50"></div>
            <div class="flex-1 space-y-2">
              <div class="h-4 w-36 rounded bg-wc-border/50"></div>
              <div class="h-3 w-48 rounded bg-wc-border/30"></div>
            </div>
            <div class="hidden sm:block h-6 w-16 rounded-full bg-wc-border/30"></div>
          </div>
        </div>
      </template>

      <!-- Client cards -->
      <div v-else-if="filteredClients.length > 0" class="space-y-3">
        <div
          v-for="client in filteredClients"
          :key="client.id"
          class="rounded-xl border border-wc-border bg-wc-bg-tertiary overflow-hidden"
        >
          <!-- Client row -->
          <button
            @click="toggleExpand(client.id)"
            class="flex w-full items-center gap-4 p-4 text-left hover:bg-wc-bg-secondary/50 transition-colors"
          >
            <!-- Avatar -->
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-wc-accent/15">
              <span class="text-base font-semibold text-wc-accent">{{ client.avatar_initial || (client.name || 'C').charAt(0) }}</span>
            </div>

            <!-- Name + plan + badges -->
            <div class="min-w-0 flex-1">
              <div class="flex items-center gap-2 flex-wrap">
                <p class="text-sm font-medium text-wc-text truncate">{{ client.name }}</p>
                <span class="inline-flex shrink-0 rounded-full bg-wc-accent/10 px-2 py-0.5 text-[10px] font-semibold text-wc-accent">
                  {{ client.plan_label || 'Sin plan' }}
                </span>
                <span
                  v-if="client.pending_checkins > 0"
                  class="inline-flex shrink-0 rounded-full bg-orange-500/10 px-2 py-0.5 text-[10px] font-semibold text-orange-500"
                >{{ client.pending_checkins }} pendiente{{ client.pending_checkins > 1 ? 's' : '' }}</span>
                <!-- Vue enhancement: risk badge -->
                <span
                  v-if="client.status === 'risk'"
                  class="inline-flex shrink-0 rounded-full bg-amber-500/10 px-2 py-0.5 text-[10px] font-semibold text-amber-500"
                >En riesgo</span>
              </div>
              <div class="mt-0.5 flex items-center gap-4 text-xs text-wc-text-tertiary">
                <span>Check-in: {{ client.last_checkin || 'Nunca' }}</span>
                <span class="hidden sm:inline">Mensaje: {{ client.last_message || 'Sin mensajes' }}</span>
              </div>
            </div>

            <!-- XP Level badge + adherence (right side) -->
            <div class="hidden sm:flex items-center gap-2">
              <!-- XP Level badge (from Blade) -->
              <div class="flex items-center gap-1 rounded-full bg-violet-500/10 px-2.5 py-1">
                <svg class="h-3 w-3 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                </svg>
                <span class="text-[11px] font-semibold text-violet-500">Nv. {{ client.xp_level || 1 }}</span>
              </div>
              <!-- Adherence (Vue enhancement) -->
              <div v-if="client.adherence !== undefined" class="flex items-center gap-1 rounded-full bg-emerald-500/10 px-2.5 py-1">
                <span class="font-data text-[11px] font-semibold text-emerald-500">{{ client.adherence }}%</span>
              </div>
            </div>

            <!-- Expand chevron -->
            <svg
              class="h-4 w-4 shrink-0 text-wc-text-tertiary transition-transform"
              :class="{ 'rotate-180': expandedClient === client.id }"
              fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
            >
              <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
          </button>

          <!-- Expanded details -->
          <div v-show="expandedClient === client.id" class="border-t border-wc-border bg-wc-bg-secondary/30 px-4 py-4">
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
              <div>
                <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">XP Total</p>
                <p class="mt-1 font-data text-sm font-semibold text-wc-text">{{ (client.xp_total || 0).toLocaleString() }}</p>
              </div>
              <div>
                <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Racha</p>
                <p class="mt-1 font-data text-sm font-semibold text-wc-text">{{ client.streak_days || 0 }} dias</p>
              </div>
              <div>
                <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Fecha inicio</p>
                <p class="mt-1 text-sm font-semibold text-wc-text">{{ client.fecha_inicio || 'N/A' }}</p>
              </div>
              <div>
                <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Ultimo check-in</p>
                <p class="mt-1 text-sm font-semibold text-wc-text">{{ client.last_checkin_date || 'N/A' }}</p>
              </div>
            </div>

            <!-- XP level badge (visible on mobile, hidden on sm+ where it shows in the row) -->
            <div class="mt-3 flex items-center gap-2 sm:hidden">
              <div class="flex items-center gap-1 rounded-full bg-violet-500/10 px-2.5 py-1">
                <svg class="h-3 w-3 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                </svg>
                <span class="text-[11px] font-semibold text-violet-500">Nv. {{ client.xp_level || 1 }}</span>
              </div>
            </div>

            <div class="mt-4 flex items-center gap-2">
              <RouterLink
                to="/coach/checkins"
                class="inline-flex items-center gap-1.5 rounded-lg bg-wc-accent px-3 py-1.5 text-xs font-medium text-white hover:bg-wc-accent-hover transition-colors"
              >
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                Ver check-ins
              </RouterLink>
              <RouterLink
                to="/coach/messages"
                class="inline-flex items-center gap-1.5 rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-1.5 text-xs font-medium text-wc-text hover:bg-wc-bg-secondary transition-colors"
              >
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                </svg>
                Enviar mensaje
              </RouterLink>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-else class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center">
        <svg class="mx-auto h-12 w-12 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
        </svg>
        <p class="mt-3 text-sm font-medium text-wc-text">No se encontraron clientes</p>
        <p class="mt-1 text-xs text-wc-text-tertiary">
          {{ search ? `No hay resultados para "${search}"` : 'No tienes clientes asignados aun' }}
        </p>
      </div>
    </div>
  </CoachLayout>
</template>
