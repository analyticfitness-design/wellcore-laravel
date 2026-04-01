<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import AdminLayout from '../../layouts/AdminLayout.vue';

const api = useApi();

// ── State ───────────────────────────────────────────────────────────────
const loading = ref(false);
const error = ref(null);
const inscriptions = ref([]);

// Filters
const search = ref('');
const statusFilter = ref('');
const planFilter = ref('');

// Pagination
const currentPage = ref(1);
const lastPage = ref(1);
const total = ref(0);

// Expanded row + action loading
const expandedId = ref(null);
const actionLoading = ref(null);

// ── Constants ───────────────────────────────────────────────────────────
const STATUS_OPTIONS = [
  { value: '', label: 'Todos los estados' },
  { value: 'pendiente', label: 'Pendiente' },
  { value: 'contactado', label: 'Contactado' },
  { value: 'convertido', label: 'Convertido' },
  { value: 'rechazado', label: 'Rechazado' },
];

const PLAN_OPTIONS = [
  { value: '', label: 'Todos los planes' },
  { value: 'esencial', label: 'Esencial' },
  { value: 'metodo', label: 'Metodo' },
  { value: 'elite', label: 'Elite' },
  { value: 'rise', label: 'Rise' },
  { value: 'presencial', label: 'Presencial' },
];

const STATUS_COLORS = {
  pendiente: 'bg-amber-500/10 text-amber-500',
  contactado: 'bg-sky-500/10 text-sky-500',
  convertido: 'bg-emerald-500/10 text-emerald-500',
  rechazado: 'bg-red-500/10 text-red-500',
  nuevo: 'bg-violet-500/10 text-violet-500',
  pagado: 'bg-emerald-500/10 text-emerald-500',
  activo: 'bg-sky-500/10 text-sky-500',
};

const PLAN_COLORS = {
  esencial: 'bg-sky-500/10 text-sky-500',
  metodo: 'bg-violet-500/10 text-violet-500',
  elite: 'bg-amber-500/10 text-amber-500',
  rise: 'bg-emerald-500/10 text-emerald-500',
  presencial: 'bg-orange-500/10 text-orange-500',
};

// ── Computed ────────────────────────────────────────────────────────────
const hasActiveFilters = computed(() =>
  search.value !== '' || statusFilter.value !== '' || planFilter.value !== ''
);

// ── API ─────────────────────────────────────────────────────────────────
async function fetchInscriptions(page = 1) {
  loading.value = true;
  error.value = null;
  try {
    const params = { page, per_page: 25 };
    if (search.value) params.search = search.value;
    if (statusFilter.value) params.status = statusFilter.value;
    if (planFilter.value) params.plan = planFilter.value;

    const response = await api.get('/api/v/admin/inscriptions', { params });
    inscriptions.value = response.data.inscriptions ?? [];
    if (response.data.pagination) {
      currentPage.value = response.data.pagination.current_page;
      lastPage.value = response.data.pagination.last_page;
      total.value = response.data.pagination.total;
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Error al cargar inscripciones';
    inscriptions.value = [];
  } finally {
    loading.value = false;
  }
}

async function updateStatus(id, status) {
  actionLoading.value = id;
  try {
    await api.put(`/api/v/admin/inscriptions/${id}`, { status });
    const idx = inscriptions.value.findIndex(i => i.id === id);
    if (idx !== -1) inscriptions.value[idx].status = status;
  } catch (err) {
    error.value = err.response?.data?.message || 'Error al actualizar';
  } finally {
    actionLoading.value = null;
  }
}

// ── Filter handlers ─────────────────────────────────────────────────────
function clearFilters() {
  search.value = '';
  statusFilter.value = '';
  planFilter.value = '';
  currentPage.value = 1;
  fetchInscriptions(1);
}

function goToPage(page) {
  if (page < 1 || page > lastPage.value) return;
  currentPage.value = page;
  fetchInscriptions(page);
}

function toggleExpand(id) {
  expandedId.value = expandedId.value === id ? null : id;
}

function getStatusColor(status) {
  return STATUS_COLORS[status] || 'bg-wc-bg-secondary text-wc-text-tertiary';
}

function getPlanColor(planRaw) {
  return PLAN_COLORS[planRaw] || 'bg-wc-bg-secondary text-wc-text-tertiary';
}

function capitalizeFirst(str) {
  if (!str) return '-';
  return str.charAt(0).toUpperCase() + str.slice(1);
}

// ── Pagination range ────────────────────────────────────────────────────
const paginationPages = computed(() => {
  const pages = [];
  const delta = 2;
  const left = Math.max(2, currentPage.value - delta);
  const right = Math.min(lastPage.value - 1, currentPage.value + delta);

  pages.push(1);
  if (left > 2) pages.push('...');
  for (let i = left; i <= right; i++) pages.push(i);
  if (right < lastPage.value - 1) pages.push('...');
  if (lastPage.value > 1) pages.push(lastPage.value);

  return pages;
});

// ── Debounced search ────────────────────────────────────────────────────
let debounceTimer = null;

watch(search, () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    currentPage.value = 1;
    fetchInscriptions(1);
  }, 300);
});

watch(statusFilter, () => {
  currentPage.value = 1;
  fetchInscriptions(1);
});

watch(planFilter, () => {
  currentPage.value = 1;
  fetchInscriptions(1);
});

// ── Init ────────────────────────────────────────────────────────────────
onMounted(() => {
  fetchInscriptions(1);
});
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">

      <!-- Header -->
      <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">INSCRIPCIONES</h1>
        <p class="mt-1 text-sm text-wc-text-tertiary">Gestiona las solicitudes de inscripcion</p>
      </div>

      <!-- Filters -->
      <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
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
              class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary py-2 pl-10 pr-4 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
            />
          </div>

          <!-- Status filter -->
          <select
            v-model="statusFilter"
            class="rounded-lg border border-wc-border bg-wc-bg-secondary py-2 pl-3 pr-8 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
          >
            <option v-for="opt in STATUS_OPTIONS" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
          </select>

          <!-- Plan filter -->
          <select
            v-model="planFilter"
            class="rounded-lg border border-wc-border bg-wc-bg-secondary py-2 pl-3 pr-8 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
          >
            <option v-for="opt in PLAN_OPTIONS" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
          </select>

          <!-- Clear filters -->
          <Transition name="fade">
            <button
              v-if="hasActiveFilters"
              @click="clearFilters"
              class="inline-flex items-center gap-1 rounded-lg px-3 py-2 text-xs font-medium text-wc-accent hover:bg-wc-accent/10 transition-colors"
            >
              <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
              </svg>
              Limpiar
            </button>
          </Transition>
        </div>
      </div>

      <!-- Loading skeleton -->
      <template v-if="loading">
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-wc-border bg-wc-bg-secondary">
                  <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Nombre</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary hidden md:table-cell">Email</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Plan</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Estado</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary hidden lg:table-cell">Ciudad</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary hidden sm:table-cell">Fecha</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-wc-border">
                <tr v-for="n in 8" :key="n">
                  <td class="px-4 py-3"><div class="h-8 w-40 animate-pulse rounded-lg bg-wc-bg-secondary"></div></td>
                  <td class="px-4 py-3 hidden md:table-cell"><div class="h-4 w-36 animate-pulse rounded bg-wc-bg-secondary"></div></td>
                  <td class="px-4 py-3"><div class="h-5 w-16 animate-pulse rounded-full bg-wc-bg-secondary"></div></td>
                  <td class="px-4 py-3"><div class="h-5 w-20 animate-pulse rounded-full bg-wc-bg-secondary"></div></td>
                  <td class="px-4 py-3 hidden lg:table-cell"><div class="h-4 w-24 animate-pulse rounded bg-wc-bg-secondary"></div></td>
                  <td class="px-4 py-3 hidden sm:table-cell"><div class="h-4 w-28 animate-pulse rounded bg-wc-bg-secondary"></div></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </template>

      <!-- Error -->
      <div v-else-if="error" class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-6 text-center">
        <p class="text-sm text-wc-text">{{ error }}</p>
        <button @click="fetchInscriptions(currentPage)" class="mt-3 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors">Reintentar</button>
      </div>

      <!-- Empty state -->
      <div v-else-if="inscriptions.length === 0" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center">
        <svg class="mx-auto h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
        </svg>
        <p class="mt-2 text-sm text-wc-text-tertiary">No se encontraron inscripciones</p>
        <button
          v-if="hasActiveFilters"
          @click="clearFilters"
          class="mt-2 text-xs font-medium text-wc-accent hover:text-red-400 transition-colors"
        >
          Limpiar filtros
        </button>
      </div>

      <!-- Table -->
      <div v-else class="rounded-xl border border-wc-border bg-wc-bg-tertiary overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-wc-border bg-wc-bg-secondary">
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Nombre</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary hidden md:table-cell">Email</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Plan</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Estado</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary hidden lg:table-cell">Ciudad</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary hidden sm:table-cell">Fecha</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary w-10"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-wc-border">
              <template v-for="insc in inscriptions" :key="insc.id">
                <!-- Main row -->
                <tr
                  class="hover:bg-wc-bg-secondary/50 transition-colors cursor-pointer"
                  @click="toggleExpand(insc.id)"
                >
                  <!-- Name + WhatsApp -->
                  <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                      <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-sky-500/10">
                        <span class="text-xs font-semibold text-sky-500">{{ insc.initial || (insc.nombre || 'I').charAt(0) }}</span>
                      </div>
                      <div class="min-w-0">
                        <p class="truncate font-medium text-wc-text">{{ insc.nombre || '-' }}</p>
                        <p v-if="insc.whatsapp" class="text-xs text-wc-text-tertiary">{{ insc.whatsapp }}</p>
                      </div>
                    </div>
                  </td>

                  <!-- Email -->
                  <td class="px-4 py-3 hidden md:table-cell">
                    <span class="truncate text-xs text-wc-text-secondary">{{ insc.email || '-' }}</span>
                  </td>

                  <!-- Plan badge -->
                  <td class="px-4 py-3">
                    <span
                      v-if="insc.plan && insc.plan !== '-'"
                      class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold"
                      :class="getPlanColor(insc.plan_raw)"
                    >
                      {{ insc.plan }}
                    </span>
                    <span v-else class="text-xs text-wc-text-tertiary">-</span>
                  </td>

                  <!-- Status badge -->
                  <td class="px-4 py-3">
                    <span
                      v-if="insc.status && insc.status !== '-'"
                      class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold"
                      :class="getStatusColor(insc.status)"
                    >
                      {{ capitalizeFirst(insc.status) }}
                    </span>
                    <span v-else class="text-xs text-wc-text-tertiary">-</span>
                  </td>

                  <!-- Ciudad -->
                  <td class="px-4 py-3 hidden lg:table-cell">
                    <span class="text-xs text-wc-text-secondary">{{ insc.ciudad || '-' }}</span>
                  </td>

                  <!-- Fecha -->
                  <td class="px-4 py-3 hidden sm:table-cell">
                    <span class="font-data text-xs text-wc-text-secondary">{{ insc.created_at || '-' }}</span>
                  </td>

                  <!-- Expand chevron -->
                  <td class="px-4 py-3">
                    <svg
                      :class="expandedId === insc.id ? 'rotate-180' : ''"
                      class="h-4 w-4 text-wc-text-tertiary transition-transform duration-200"
                      fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                    </svg>
                  </td>
                </tr>

                <!-- Expanded detail row -->
                <tr v-if="expandedId === insc.id">
                  <td :colspan="7" class="border-t border-wc-border bg-wc-bg-secondary p-0">
                    <div class="p-4">
                      <!-- Detail grid -->
                      <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 mb-4">
                        <div>
                          <p class="text-xs font-medium text-wc-text-tertiary">Email</p>
                          <p class="mt-0.5 text-sm text-wc-text">{{ insc.email || '-' }}</p>
                        </div>
                        <div>
                          <p class="text-xs font-medium text-wc-text-tertiary">WhatsApp</p>
                          <p class="mt-0.5 text-sm text-wc-text">
                            <template v-if="insc.whatsapp">
                              <a
                                :href="`https://wa.me/${insc.whatsapp.replace(/[^0-9]/g, '')}`"
                                target="_blank"
                                rel="noopener"
                                class="inline-flex items-center gap-1 text-emerald-500 hover:text-emerald-400 transition-colors"
                                @click.stop
                              >
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor">
                                  <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                                  <path d="M12 0C5.373 0 0 5.373 0 12c0 2.625.846 5.059 2.284 7.034L.789 23.492a.75.75 0 00.917.918l4.458-1.495A11.952 11.952 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-2.347 0-4.518-.802-6.237-2.148a.75.75 0 00-.593-.131l-3.22 1.079 1.079-3.22a.75.75 0 00-.131-.593A9.958 9.958 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/>
                                </svg>
                                {{ insc.whatsapp }}
                              </a>
                            </template>
                            <template v-else>-</template>
                          </p>
                        </div>
                        <div>
                          <p class="text-xs font-medium text-wc-text-tertiary">Ciudad</p>
                          <p class="mt-0.5 text-sm text-wc-text">{{ insc.ciudad || '-' }}</p>
                        </div>
                        <div>
                          <p class="text-xs font-medium text-wc-text-tertiary">Registrado</p>
                          <p class="mt-0.5 font-data text-sm text-wc-text">{{ insc.created_at || '-' }}</p>
                          <p v-if="insc.time_ago" class="text-xs text-wc-text-tertiary">{{ insc.time_ago }}</p>
                        </div>
                      </div>

                      <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 mb-4">
                        <div>
                          <p class="text-xs font-medium text-wc-text-tertiary">Plan solicitado</p>
                          <p class="mt-0.5 text-sm text-wc-text capitalize">{{ insc.plan || '-' }}</p>
                        </div>
                        <div>
                          <p class="text-xs font-medium text-wc-text-tertiary">Objetivo</p>
                          <p class="mt-0.5 text-sm text-wc-text">{{ insc.objetivo || '-' }}</p>
                        </div>
                        <div>
                          <p class="text-xs font-medium text-wc-text-tertiary">Experiencia</p>
                          <p class="mt-0.5 text-sm text-wc-text capitalize">{{ insc.experiencia || '-' }}</p>
                        </div>
                      </div>

                      <!-- Status actions -->
                      <div class="flex flex-wrap gap-2">
                        <button
                          v-if="insc.status !== 'contactado'"
                          @click.stop="updateStatus(insc.id, 'contactado')"
                          :disabled="actionLoading === insc.id"
                          class="rounded-lg bg-sky-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-sky-700 transition-colors disabled:opacity-50"
                        >
                          <span v-if="actionLoading === insc.id" class="inline-flex items-center gap-1">
                            <svg class="h-3 w-3 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"/><path d="M4 12a8 8 0 018-8" stroke="currentColor" stroke-width="4" stroke-linecap="round" class="opacity-75"/></svg>
                            ...
                          </span>
                          <span v-else>Contactado</span>
                        </button>
                        <button
                          v-if="insc.status !== 'convertido'"
                          @click.stop="updateStatus(insc.id, 'convertido')"
                          :disabled="actionLoading === insc.id"
                          class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-emerald-700 transition-colors disabled:opacity-50"
                        >
                          <span v-if="actionLoading === insc.id" class="inline-flex items-center gap-1">
                            <svg class="h-3 w-3 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"/><path d="M4 12a8 8 0 018-8" stroke="currentColor" stroke-width="4" stroke-linecap="round" class="opacity-75"/></svg>
                            ...
                          </span>
                          <span v-else>Convertido</span>
                        </button>
                        <button
                          v-if="insc.status !== 'rechazado'"
                          @click.stop="updateStatus(insc.id, 'rechazado')"
                          :disabled="actionLoading === insc.id"
                          class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-700 transition-colors disabled:opacity-50"
                        >
                          <span v-if="actionLoading === insc.id" class="inline-flex items-center gap-1">
                            <svg class="h-3 w-3 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"/><path d="M4 12a8 8 0 018-8" stroke="currentColor" stroke-width="4" stroke-linecap="round" class="opacity-75"/></svg>
                            ...
                          </span>
                          <span v-else>Rechazado</span>
                        </button>
                      </div>
                    </div>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="lastPage > 1" class="border-t border-wc-border px-4 py-3">
          <div class="flex items-center justify-between">
            <p class="text-xs text-wc-text-tertiary">
              <span class="font-data">{{ total }}</span> inscripciones en total
            </p>
            <nav class="flex items-center gap-1">
              <!-- Previous -->
              <button
                @click="goToPage(currentPage - 1)"
                :disabled="currentPage <= 1"
                class="rounded-lg p-1.5 text-wc-text-tertiary hover:bg-wc-bg-secondary transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
              >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
              </button>

              <!-- Page numbers -->
              <template v-for="(page, idx) in paginationPages" :key="idx">
                <span v-if="page === '...'" class="px-1 text-xs text-wc-text-tertiary">...</span>
                <button
                  v-else
                  @click="goToPage(page)"
                  :class="[
                    'min-w-[32px] rounded-lg px-2 py-1 font-data text-xs transition-colors',
                    page === currentPage
                      ? 'bg-wc-accent text-white'
                      : 'text-wc-text-secondary hover:bg-wc-bg-secondary'
                  ]"
                >
                  {{ page }}
                </button>
              </template>

              <!-- Next -->
              <button
                @click="goToPage(currentPage + 1)"
                :disabled="currentPage >= lastPage"
                class="rounded-lg p-1.5 text-wc-text-tertiary hover:bg-wc-bg-secondary transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
              >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
              </button>
            </nav>
          </div>
        </div>
      </div>

    </div>
  </AdminLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
