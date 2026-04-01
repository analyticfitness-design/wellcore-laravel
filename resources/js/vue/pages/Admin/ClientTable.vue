<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useApi } from '../../composables/useApi';
import AdminLayout from '../../layouts/AdminLayout.vue';

const api = useApi();
const router = useRouter();

const loading = ref(true);
const error = ref(null);
const clients = ref([]);
const meta = ref({ total: 0, currentPage: 1, lastPage: 1, perPage: 20 });

// Filters
const search = ref('');
const planFilter = ref('');
const statusFilter = ref('');
const sortBy = ref('name');
const sortDir = ref('asc');

// Debounced search
let searchTimeout = null;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        meta.value.currentPage = 1;
        fetchClients();
    }, 400);
});

async function fetchClients() {
    loading.value = true;
    error.value = null;
    try {
        const response = await api.get('/api/v/admin/clients', {
            params: {
                search: search.value || undefined,
                plan: planFilter.value || undefined,
                status: statusFilter.value || undefined,
                sort: sortBy.value,
                direction: sortDir.value,
                page: meta.value.currentPage,
                per_page: meta.value.perPage,
            },
        });
        clients.value = response.data.data || response.data.clients || [];
        const p = response.data.pagination;
        if (response.data.meta) {
            meta.value = { ...meta.value, ...response.data.meta };
        } else if (p) {
            meta.value.total = p.total;
            meta.value.lastPage = p.last_page ?? Math.ceil(p.total / meta.value.perPage);
        } else if (response.data.total !== undefined) {
            meta.value.total = response.data.total;
            meta.value.lastPage = response.data.last_page || Math.ceil(response.data.total / meta.value.perPage);
        }
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al cargar clientes';
    } finally {
        loading.value = false;
    }
}

function toggleSort(column) {
    if (sortBy.value === column) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = column;
        sortDir.value = 'asc';
    }
    fetchClients();
}

function goToPage(page) {
    if (page < 1 || page > meta.value.lastPage) return;
    meta.value.currentPage = page;
    fetchClients();
}

function applyFilter() {
    meta.value.currentPage = 1;
    fetchClients();
}

function viewClient(id) {
    router.push(`/v/admin/clients/${id}`);
}

function getStatusColor(status) {
    const colors = {
        active: 'bg-emerald-500/10 text-emerald-500',
        inactive: 'bg-gray-500/10 text-gray-400',
        pending: 'bg-amber-500/10 text-amber-500',
        suspended: 'bg-red-500/10 text-red-500',
    };
    return colors[status] || 'bg-gray-500/10 text-gray-400';
}

function getStatusLabel(status) {
    const labels = { active: 'Activo', inactive: 'Inactivo', pending: 'Pendiente', suspended: 'Suspendido' };
    return labels[status] || status;
}

const sortIcon = computed(() => sortDir.value === 'asc' ? 'M4.5 15.75l7.5-7.5 7.5 7.5' : 'M19.5 8.25l-7.5 7.5-7.5-7.5');

onMounted(() => {
    fetchClients();
});
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">

      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="font-display text-3xl tracking-wide text-wc-text">Clientes</h1>
          <p class="mt-1 text-sm text-wc-text-tertiary">{{ meta.total }} clientes registrados</p>
        </div>
      </div>

      <!-- Filters Row -->
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <!-- Search -->
        <div class="relative flex-1">
          <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
          </svg>
          <input
            v-model="search"
            type="text"
            placeholder="Buscar por nombre o email..."
            class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary py-2 pl-10 pr-4 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
          />
        </div>
        <!-- Plan filter -->
        <select
          v-model="planFilter"
          @change="applyFilter"
          class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
        >
          <option value="">Todos los planes</option>
          <option value="premium">Premium</option>
          <option value="basic">Basico</option>
          <option value="rise">RISE</option>
          <option value="presencial">Presencial</option>
        </select>
        <!-- Status filter -->
        <select
          v-model="statusFilter"
          @change="applyFilter"
          class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
        >
          <option value="">Todos los estados</option>
          <option value="active">Activo</option>
          <option value="inactive">Inactivo</option>
          <option value="pending">Pendiente</option>
        </select>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="space-y-2">
        <div v-for="i in 10" :key="i" class="h-14 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-6 text-center">
        <p class="text-sm text-wc-text">{{ error }}</p>
        <button @click="fetchClients" class="mt-3 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white">Reintentar</button>
      </div>

      <!-- Table -->
      <div v-else class="overflow-x-auto rounded-xl border border-wc-border">
        <table class="w-full min-w-[700px]">
          <thead class="border-b border-wc-border bg-wc-bg-tertiary">
            <tr>
              <th class="cursor-pointer px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-wc-text-tertiary" @click="toggleSort('name')">
                <div class="flex items-center gap-1">
                  Nombre
                  <svg v-if="sortBy === 'name'" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" :d="sortIcon" />
                  </svg>
                </div>
              </th>
              <th class="cursor-pointer px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-wc-text-tertiary" @click="toggleSort('plan')">
                <div class="flex items-center gap-1">
                  Plan
                  <svg v-if="sortBy === 'plan'" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" :d="sortIcon" />
                  </svg>
                </div>
              </th>
              <th class="cursor-pointer px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-wc-text-tertiary" @click="toggleSort('status')">
                <div class="flex items-center gap-1">
                  Estado
                  <svg v-if="sortBy === 'status'" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" :d="sortIcon" />
                  </svg>
                </div>
              </th>
              <th class="cursor-pointer px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-wc-text-tertiary" @click="toggleSort('last_login')">
                <div class="flex items-center gap-1">
                  Ultimo login
                  <svg v-if="sortBy === 'last_login'" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" :d="sortIcon" />
                  </svg>
                </div>
              </th>
              <th class="cursor-pointer px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-wc-text-tertiary" @click="toggleSort('adherence')">
                <div class="flex items-center gap-1">
                  Adherencia
                  <svg v-if="sortBy === 'adherence'" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" :d="sortIcon" />
                  </svg>
                </div>
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-wc-border">
            <tr
              v-for="client in clients"
              :key="client.id"
              @click="viewClient(client.id)"
              class="cursor-pointer bg-wc-bg-secondary transition-colors hover:bg-wc-bg-tertiary"
            >
              <td class="px-4 py-3">
                <div class="flex items-center gap-3">
                  <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-wc-accent/10">
                    <span class="text-xs font-semibold text-wc-accent">{{ (client.name || 'U').charAt(0).toUpperCase() }}</span>
                  </div>
                  <div class="min-w-0">
                    <p class="text-sm font-medium text-wc-text truncate">{{ client.name }}</p>
                    <p class="text-xs text-wc-text-tertiary truncate">{{ client.email }}</p>
                  </div>
                </div>
              </td>
              <td class="px-4 py-3">
                <span class="rounded-full bg-wc-bg-tertiary px-2.5 py-0.5 text-xs font-medium text-wc-text capitalize">{{ client.plan || 'Sin plan' }}</span>
              </td>
              <td class="px-4 py-3">
                <span class="rounded-full px-2.5 py-0.5 text-xs font-medium capitalize" :class="getStatusColor(client.status)">{{ getStatusLabel(client.status) }}</span>
              </td>
              <td class="px-4 py-3 text-xs text-wc-text-tertiary">{{ client.lastLogin || 'Nunca' }}</td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                  <div class="h-1.5 w-16 overflow-hidden rounded-full bg-wc-bg-tertiary">
                    <div class="h-full rounded-full bg-wc-accent transition-all" :style="{ width: `${client.adherence || 0}%` }"></div>
                  </div>
                  <span class="text-xs font-data text-wc-text-tertiary">{{ client.adherence || 0 }}%</span>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Empty state -->
        <div v-if="!clients.length" class="p-12 text-center">
          <p class="text-sm text-wc-text-tertiary">No se encontraron clientes</p>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="meta.lastPage > 1" class="flex items-center justify-between">
        <p class="text-xs text-wc-text-tertiary">
          Pagina {{ meta.currentPage }} de {{ meta.lastPage }} ({{ meta.total }} resultados)
        </p>
        <div class="flex items-center gap-1">
          <button
            @click="goToPage(meta.currentPage - 1)"
            :disabled="meta.currentPage <= 1"
            class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-1.5 text-xs font-medium text-wc-text disabled:opacity-40 hover:bg-wc-bg-tertiary transition-colors"
          >
            Anterior
          </button>
          <button
            @click="goToPage(meta.currentPage + 1)"
            :disabled="meta.currentPage >= meta.lastPage"
            class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-1.5 text-xs font-medium text-wc-text disabled:opacity-40 hover:bg-wc-bg-tertiary transition-colors"
          >
            Siguiente
          </button>
        </div>
      </div>

    </div>
  </AdminLayout>
</template>
