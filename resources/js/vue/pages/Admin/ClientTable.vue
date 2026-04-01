<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { useRouter } from 'vue-router';
import { useApi } from '../../composables/useApi';
import AdminLayout from '../../layouts/AdminLayout.vue';

const api = useApi();
const router = useRouter();

// ─── State ──────────────────────────────────────────────────────────────
const loading = ref(true);
const error = ref(null);
const clients = ref([]);
const isSuperadmin = ref(false);
const meta = ref({ total: 0, currentPage: 1, lastPage: 1, perPage: 25 });

// Filters
const search = ref('');
const planFilter = ref('');
const statusFilter = ref('');
const sortBy = ref('created_at');
const sortDir = ref('desc');

// Deactivate modal
const showDeactivateModal = ref(false);
const deactivateClientId = ref(null);
const deactivateClientName = ref('');
const deactivating = ref(false);

// Delete modal
const showDeleteModal = ref(false);
const deleteClientId = ref(null);
const deleteClientName = ref('');
const deleting = ref(false);

// Toast
const toast = ref(null);
let toastTimer = null;

// ─── Plan / Status maps ────────────────────────────────────────────────
const PLAN_COLORS = {
  esencial: 'bg-sky-500/10 text-sky-500',
  metodo: 'bg-violet-500/10 text-violet-500',
  elite: 'bg-amber-500/10 text-amber-500',
  rise: 'bg-emerald-500/10 text-emerald-500',
  presencial: 'bg-orange-500/10 text-orange-500',
  trial: 'bg-pink-500/10 text-pink-500',
};

const PLAN_LABELS = {
  esencial: 'Esencial',
  metodo: 'Metodo',
  elite: 'Elite',
  rise: 'Rise',
  presencial: 'Presencial',
  trial: 'Trial',
};

const STATUS_COLORS = {
  activo: 'bg-emerald-500/10 text-emerald-500',
  inactivo: 'bg-zinc-500/10 text-zinc-400',
  suspendido: 'bg-red-500/10 text-red-500',
  pendiente: 'bg-amber-500/10 text-amber-500',
  congelado: 'bg-sky-500/10 text-sky-500',
};

const STATUS_LABELS = {
  activo: 'Activo',
  inactivo: 'Inactivo',
  suspendido: 'Suspendido',
  pendiente: 'Pendiente',
  congelado: 'Congelado',
};

// ─── Debounced search (300ms standard) ─────────────────────────────────
let debounceTimer = null;
watch(search, () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    meta.value.currentPage = 1;
    fetchClients();
  }, 300);
});

// ─── Fetch clients ─────────────────────────────────────────────────────
async function fetchClients() {
  loading.value = true;
  error.value = null;
  try {
    const response = await api.get('/api/v/admin/clients', {
      params: {
        search: search.value || undefined,
        plan: planFilter.value || undefined,
        status: statusFilter.value || undefined,
        sort_by: sortBy.value,
        sort_dir: sortDir.value,
        page: meta.value.currentPage,
        per_page: meta.value.perPage,
      },
    });
    clients.value = response.data.clients || response.data.data || [];
    isSuperadmin.value = response.data.isSuperadmin ?? false;
    const p = response.data.pagination;
    if (p) {
      meta.value.total = p.total;
      meta.value.currentPage = p.current_page;
      meta.value.lastPage = p.last_page;
      meta.value.perPage = p.per_page;
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Error al cargar clientes';
    clients.value = [];
  } finally {
    loading.value = false;
  }
}

// ─── Sorting ────────────────────────────────────────────────────────────
function toggleSort(column) {
  if (sortBy.value === column) {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortBy.value = column;
    sortDir.value = 'asc';
  }
  fetchClients();
}

// ─── Filters ────────────────────────────────────────────────────────────
function applyFilter() {
  meta.value.currentPage = 1;
  fetchClients();
}

function clearFilters() {
  search.value = '';
  planFilter.value = '';
  statusFilter.value = '';
  meta.value.currentPage = 1;
  fetchClients();
}

const hasActiveFilters = computed(() => {
  return search.value !== '' || planFilter.value !== '' || statusFilter.value !== '';
});

// ─── Pagination ─────────────────────────────────────────────────────────
function goToPage(page) {
  if (page < 1 || page > meta.value.lastPage) return;
  meta.value.currentPage = page;
  fetchClients();
}

const paginationRange = computed(() => {
  const current = meta.value.currentPage;
  const last = meta.value.lastPage;
  const delta = 2;
  const range = [];
  const rangeWithDots = [];

  for (let i = 1; i <= last; i++) {
    if (i === 1 || i === last || (i >= current - delta && i <= current + delta)) {
      range.push(i);
    }
  }

  let prev = null;
  for (const i of range) {
    if (prev !== null) {
      if (i - prev > 1) {
        rangeWithDots.push('...');
      }
    }
    rangeWithDots.push(i);
    prev = i;
  }

  return rangeWithDots;
});

const paginationFrom = computed(() => {
  if (meta.value.total === 0) return 0;
  return (meta.value.currentPage - 1) * meta.value.perPage + 1;
});

const paginationTo = computed(() => {
  return Math.min(meta.value.currentPage * meta.value.perPage, meta.value.total);
});

// ─── Navigation ─────────────────────────────────────────────────────────
function viewClient(id) {
  router.push(`/admin/clients/${id}`);
}

// ─── Online indicator ───────────────────────────────────────────────────
function getOnlineStatus(lastLoginAt) {
  if (!lastLoginAt) return { dot: 'bg-wc-text-tertiary/30', text: '-', label: '', isOnline: false };

  const now = new Date();
  const login = new Date(lastLoginAt);
  const diffMs = now - login;
  const diffMin = diffMs / 60000;
  const diffHours = diffMs / 3600000;

  if (diffMin < 15) {
    return { dot: 'bg-emerald-400 shadow-sm shadow-emerald-400/50', text: 'Online', label: 'text-emerald-400 font-semibold', isOnline: true };
  }

  if (diffHours < 24) {
    const hrs = Math.floor(diffHours);
    const mins = Math.floor(diffMin);
    const text = hrs > 0 ? `hace ${hrs}h` : `hace ${mins}m`;
    return { dot: 'bg-amber-400', text, label: 'text-wc-text-tertiary', isOnline: false };
  }

  const diffDays = Math.floor(diffMs / 86400000);
  if (diffDays < 30) {
    return { dot: 'bg-wc-text-tertiary/30', text: `hace ${diffDays}d`, label: 'text-wc-text-tertiary', isOnline: false };
  }

  const diffMonths = Math.floor(diffDays / 30);
  return { dot: 'bg-wc-text-tertiary/30', text: `hace ${diffMonths} mes${diffMonths > 1 ? 'es' : ''}`, label: 'text-wc-text-tertiary', isOnline: false };
}

// ─── Plan / Status helpers ──────────────────────────────────────────────
function getPlanColor(plan) {
  const key = typeof plan === 'string' ? plan : plan?.value || plan;
  return PLAN_COLORS[key] || 'bg-wc-bg-secondary text-wc-text-tertiary';
}

function getPlanLabel(plan) {
  const key = typeof plan === 'string' ? plan : plan?.value || plan;
  return PLAN_LABELS[key] || plan || '-';
}

function getStatusColor(status) {
  const key = typeof status === 'string' ? status : status?.value || status;
  return STATUS_COLORS[key] || 'bg-wc-bg-secondary text-wc-text-tertiary';
}

function getStatusLabel(status) {
  const key = typeof status === 'string' ? status : status?.value || status;
  return STATUS_LABELS[key] || status || '-';
}

function formatDate(dateStr) {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  if (isNaN(d.getTime())) return '-';
  return d.toLocaleDateString('es-CO', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

// ─── CSV Export (frontend-generated) ────────────────────────────────────
function exportCsv() {
  const headers = ['Nombre', 'Email', 'Codigo', 'Plan', 'Estado', 'Fecha inicio'];
  const rows = clients.value.map(c => [
    c.name || '',
    c.email || '',
    c.client_code || '',
    getPlanLabel(c.plan),
    getStatusLabel(c.status),
    formatDate(c.fecha_inicio),
  ]);

  const csvContent = [headers, ...rows]
    .map(row => row.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join(','))
    .join('\n');

  const blob = new Blob(['\uFEFF' + csvContent], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.download = `clientes_wellcore_${new Date().toISOString().slice(0, 10)}.csv`;
  link.click();
  URL.revokeObjectURL(url);
}

// ─── Deactivate client ──────────────────────────────────────────────────
function confirmDeactivate(client) {
  deactivateClientId.value = client.id;
  deactivateClientName.value = client.name || 'Cliente';
  showDeactivateModal.value = true;
}

function cancelDeactivate() {
  showDeactivateModal.value = false;
  deactivateClientId.value = null;
  deactivateClientName.value = '';
}

async function deactivateClient() {
  if (!deactivateClientId.value) return;
  deactivating.value = true;
  try {
    await api.put(`/api/v/admin/clients/${deactivateClientId.value}`, {
      status: 'inactivo',
    });
    showToast('success', `Cliente "${deactivateClientName.value}" marcado como inactivo.`);
    cancelDeactivate();
    fetchClients();
  } catch (err) {
    showToast('error', err.response?.data?.message || 'Error al desactivar cliente.');
  } finally {
    deactivating.value = false;
  }
}

// ─── Delete client ──────────────────────────────────────────────────────
function confirmDelete(client) {
  deleteClientId.value = client.id;
  deleteClientName.value = client.name || 'Cliente';
  showDeleteModal.value = true;
}

function cancelDelete() {
  showDeleteModal.value = false;
  deleteClientId.value = null;
  deleteClientName.value = '';
}

async function deleteClient() {
  if (!deleteClientId.value) return;
  deleting.value = true;
  try {
    await api.delete(`/api/v/admin/clients/${deleteClientId.value}`);
    showToast('success', `Cliente "${deleteClientName.value}" eliminado permanentemente.`);
    cancelDelete();
    fetchClients();
  } catch (err) {
    showToast('error', err.response?.data?.message || 'Error al eliminar cliente.');
  } finally {
    deleting.value = false;
  }
}

// ─── Toast ──────────────────────────────────────────────────────────────
function showToast(type, message) {
  clearTimeout(toastTimer);
  toast.value = { type, message };
  toastTimer = setTimeout(() => { toast.value = null; }, 4000);
}

// ─── Cleanup ────────────────────────────────────────────────────────────
onBeforeUnmount(() => {
  clearTimeout(debounceTimer);
  clearTimeout(toastTimer);
});

onMounted(fetchClients);
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">

      <!-- Toast notification -->
      <Transition name="slide-down">
        <div
          v-if="toast"
          class="fixed top-4 right-4 z-[60] flex items-center gap-2 rounded-lg border px-4 py-3 shadow-lg"
          :class="toast.type === 'success'
            ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400'
            : 'border-red-500/20 bg-red-500/10 text-red-400'"
        >
          <svg v-if="toast.type === 'success'" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
          </svg>
          <svg v-else class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
          </svg>
          <span class="text-sm font-medium">{{ toast.message }}</span>
          <button @click="toast = null" class="ml-2 opacity-60 hover:opacity-100">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </Transition>

      <!-- Deactivate confirmation modal -->
      <Transition name="fade">
        <div
          v-if="showDeactivateModal"
          class="fixed inset-0 z-50 flex items-center justify-center p-4"
          role="dialog"
          aria-modal="true"
          @keydown.escape="cancelDeactivate"
        >
          <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="cancelDeactivate"></div>
          <Transition name="scale">
            <div v-if="showDeactivateModal" class="relative z-10 w-full max-w-md rounded-2xl border border-wc-border bg-wc-bg-tertiary p-6 shadow-2xl">
              <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-500/10">
                <svg class="h-6 w-6 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
              </div>
              <h2 class="mb-2 text-center font-display text-xl tracking-wide text-wc-text">Desactivar cliente</h2>
              <p class="mb-1 text-center text-sm text-wc-text-secondary">
                Estas a punto de marcar como <span class="font-semibold text-wc-text">inactivo</span> al cliente:
              </p>
              <p class="mb-6 text-center text-base font-semibold text-wc-accent">{{ deactivateClientName }}</p>
              <p class="mb-6 text-center text-xs text-wc-text-tertiary">
                El cliente no podra iniciar sesion pero sus datos se conservaran intactos.
                Esta accion puede revertirse cambiando el estado manualmente.
              </p>
              <div class="flex gap-3">
                <button
                  @click="cancelDeactivate"
                  class="flex-1 rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2.5 text-sm font-medium text-wc-text transition-colors hover:bg-wc-bg focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-2 focus:ring-offset-wc-bg-tertiary"
                >
                  Cancelar
                </button>
                <button
                  @click="deactivateClient"
                  :disabled="deactivating"
                  class="flex-1 rounded-lg bg-wc-accent px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-2 focus:ring-offset-wc-bg-tertiary disabled:opacity-60 disabled:cursor-not-allowed"
                >
                  {{ deactivating ? 'Procesando...' : 'Desactivar' }}
                </button>
              </div>
            </div>
          </Transition>
        </div>
      </Transition>

      <!-- Delete confirmation modal -->
      <Transition name="fade">
        <div
          v-if="showDeleteModal"
          class="fixed inset-0 z-50 flex items-center justify-center p-4"
          role="dialog"
          aria-modal="true"
          @keydown.escape="cancelDelete"
        >
          <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="cancelDelete"></div>
          <Transition name="scale">
            <div v-if="showDeleteModal" class="relative z-10 w-full max-w-md rounded-2xl border border-wc-border bg-wc-bg-secondary p-6 shadow-2xl">
              <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-500/20">
                <svg class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                </svg>
              </div>
              <h2 class="mb-2 text-center font-display text-xl tracking-wide text-wc-text">Eliminar cliente</h2>
              <p class="mb-1 text-center text-sm text-wc-text-secondary">
                Estas a punto de <span class="font-semibold text-red-400">eliminar permanentemente</span> al cliente:
              </p>
              <p class="mb-4 text-center text-base font-semibold text-wc-accent">{{ deleteClientName }}</p>
              <div class="mb-6 rounded-lg border border-red-500/20 bg-red-500/5 p-3">
                <p class="text-center text-xs text-red-400">Esta accion eliminara su cuenta, perfil, y todos los datos asociados. No se puede deshacer.</p>
              </div>
              <div class="flex gap-3">
                <button
                  @click="cancelDelete"
                  class="flex-1 rounded-lg border border-wc-border px-4 py-2.5 text-sm font-medium text-wc-text-secondary transition-colors hover:bg-wc-bg-tertiary"
                >
                  Cancelar
                </button>
                <button
                  @click="deleteClient"
                  :disabled="deleting"
                  class="flex-1 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-red-700 disabled:opacity-60 disabled:cursor-not-allowed"
                >
                  {{ deleting ? 'Eliminando...' : 'Eliminar' }}
                </button>
              </div>
            </div>
          </Transition>
        </div>
      </Transition>

      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">Clientes</h1>
          <p class="mt-1 text-sm text-wc-text-tertiary">Gestion de clientes de WellCore</p>
        </div>
        <div class="flex items-center gap-2">
          <button
            @click="exportCsv"
            class="inline-flex items-center gap-2 rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2 text-sm font-medium text-wc-text transition-colors hover:bg-wc-bg-secondary"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            Exportar CSV
          </button>
        </div>
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
              placeholder="Buscar por nombre, email o codigo..."
              class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary py-2 pl-10 pr-4 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"
            />
          </div>

          <!-- Plan filter -->
          <select
            v-model="planFilter"
            @change="applyFilter"
            class="rounded-lg border border-wc-border bg-wc-bg-secondary py-2 pl-3 pr-8 text-sm text-wc-text focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"
          >
            <option value="">Todos los planes</option>
            <option value="esencial">Esencial</option>
            <option value="metodo">Metodo</option>
            <option value="elite">Elite</option>
            <option value="rise">Rise</option>
            <option value="presencial">Presencial</option>
          </select>

          <!-- Status filter -->
          <select
            v-model="statusFilter"
            @change="applyFilter"
            class="rounded-lg border border-wc-border bg-wc-bg-secondary py-2 pl-3 pr-8 text-sm text-wc-text focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"
          >
            <option value="">Todos los estados</option>
            <option value="activo">Activo</option>
            <option value="inactivo">Inactivo</option>
            <option value="suspendido">Suspendido</option>
            <option value="pendiente">Pendiente</option>
          </select>

          <!-- Clear filters -->
          <button
            v-if="hasActiveFilters"
            @click="clearFilters"
            class="inline-flex items-center gap-1 rounded-lg px-3 py-2 text-xs font-medium text-red-500 transition-colors hover:bg-red-500/10"
          >
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
            Limpiar
          </button>
        </div>
      </div>

      <!-- Loading skeleton -->
      <template v-if="loading">
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary overflow-hidden">
          <div class="border-b border-wc-border bg-wc-bg-secondary px-4 py-3">
            <div class="h-4 w-48 animate-pulse rounded bg-wc-bg-tertiary"></div>
          </div>
          <div class="divide-y divide-wc-border">
            <div v-for="n in 10" :key="n" class="flex items-center gap-4 px-4 py-3">
              <div class="h-8 w-8 animate-pulse rounded-full bg-wc-bg-secondary"></div>
              <div class="flex-1 space-y-1.5">
                <div class="h-3.5 w-32 animate-pulse rounded bg-wc-bg-secondary"></div>
                <div class="h-3 w-48 animate-pulse rounded bg-wc-bg-secondary"></div>
              </div>
              <div class="h-5 w-16 animate-pulse rounded-full bg-wc-bg-secondary"></div>
              <div class="h-5 w-14 animate-pulse rounded-full bg-wc-bg-secondary"></div>
            </div>
          </div>
        </div>
      </template>

      <!-- Error -->
      <div v-else-if="error" class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-8 text-center">
        <svg class="mx-auto h-8 w-8 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
        </svg>
        <p class="mt-2 text-sm text-wc-text">{{ error }}</p>
        <button @click="fetchClients" class="mt-3 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700">
          Reintentar
        </button>
      </div>

      <!-- Table -->
      <template v-else>
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-wc-border bg-wc-bg-secondary">
                  <!-- Cliente -->
                  <th class="px-4 py-3 text-left">
                    <button
                      @click="toggleSort('name')"
                      class="flex items-center gap-1 text-xs font-semibold uppercase tracking-wider transition-colors"
                      :class="sortBy === 'name' ? 'text-wc-text' : 'text-wc-text-tertiary hover:text-wc-text'"
                    >
                      Cliente
                      <svg v-if="sortBy === 'name'" class="h-3 w-3 transition-transform" :class="sortDir === 'desc' ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" />
                      </svg>
                    </button>
                  </th>

                  <!-- Codigo -->
                  <th class="px-4 py-3 text-left">
                    <button
                      @click="toggleSort('client_code')"
                      class="flex items-center gap-1 text-xs font-semibold uppercase tracking-wider transition-colors"
                      :class="sortBy === 'client_code' ? 'text-wc-text' : 'text-wc-text-tertiary hover:text-wc-text'"
                    >
                      Codigo
                      <svg v-if="sortBy === 'client_code'" class="h-3 w-3 transition-transform" :class="sortDir === 'desc' ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" />
                      </svg>
                    </button>
                  </th>

                  <!-- Plan -->
                  <th class="px-4 py-3 text-left">
                    <span class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Plan</span>
                  </th>

                  <!-- Estado -->
                  <th class="px-4 py-3 text-left">
                    <span class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Estado</span>
                  </th>

                  <!-- Fecha inicio -->
                  <th class="px-4 py-3 text-left">
                    <button
                      @click="toggleSort('fecha_inicio')"
                      class="flex items-center gap-1 text-xs font-semibold uppercase tracking-wider transition-colors"
                      :class="sortBy === 'fecha_inicio' ? 'text-wc-text' : 'text-wc-text-tertiary hover:text-wc-text'"
                    >
                      Fecha inicio
                      <svg v-if="sortBy === 'fecha_inicio'" class="h-3 w-3 transition-transform" :class="sortDir === 'desc' ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" />
                      </svg>
                    </button>
                  </th>

                  <!-- Ultima sesion -->
                  <th class="px-4 py-3 text-left">
                    <span class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Ultima sesion</span>
                  </th>

                  <!-- Acciones -->
                  <th class="px-4 py-3 text-right">
                    <span class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Acciones</span>
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-wc-border">
                <tr
                  v-for="client in clients"
                  :key="client.id"
                  class="transition-colors hover:bg-wc-bg-secondary/50"
                >
                  <!-- Name + email -->
                  <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                      <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-red-500/10">
                        <span class="text-xs font-semibold text-red-500">{{ (client.name || 'C').charAt(0).toUpperCase() }}</span>
                      </div>
                      <div class="min-w-0">
                        <p class="truncate font-medium text-wc-text">{{ client.name }}</p>
                        <p class="truncate text-xs text-wc-text-tertiary">{{ client.email }}</p>
                      </div>
                    </div>
                  </td>

                  <!-- Code -->
                  <td class="px-4 py-3">
                    <span class="font-data text-xs text-wc-text-secondary">{{ client.client_code || '-' }}</span>
                  </td>

                  <!-- Plan badge -->
                  <td class="px-4 py-3">
                    <span
                      v-if="client.plan"
                      class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold"
                      :class="getPlanColor(client.plan)"
                    >
                      {{ getPlanLabel(client.plan) }}
                    </span>
                    <span v-else class="text-xs text-wc-text-tertiary">-</span>
                  </td>

                  <!-- Status badge -->
                  <td class="px-4 py-3">
                    <span
                      v-if="client.status"
                      class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold"
                      :class="getStatusColor(client.status)"
                    >
                      {{ getStatusLabel(client.status) }}
                    </span>
                    <span v-else class="text-xs text-wc-text-tertiary">-</span>
                  </td>

                  <!-- Fecha inicio -->
                  <td class="px-4 py-3">
                    <span class="font-data text-xs text-wc-text-secondary">{{ formatDate(client.fecha_inicio) }}</span>
                  </td>

                  <!-- Ultima sesion (online indicator) -->
                  <td class="px-4 py-3">
                    <template v-if="client.last_login_at">
                      <div class="flex items-center gap-1.5">
                        <span class="h-2 w-2 shrink-0 rounded-full" :class="getOnlineStatus(client.last_login_at).dot"></span>
                        <span class="font-data text-[11px]" :class="getOnlineStatus(client.last_login_at).label">
                          {{ getOnlineStatus(client.last_login_at).text }}
                        </span>
                      </div>
                    </template>
                    <span v-else class="text-xs text-wc-text-tertiary">-</span>
                  </td>

                  <!-- Actions -->
                  <td class="px-4 py-3 text-right">
                    <div class="inline-flex items-center gap-1">
                      <!-- View detail -->
                      <button
                        @click="viewClient(client.id)"
                        class="inline-flex h-7 w-7 items-center justify-center rounded-lg text-wc-text-tertiary transition-colors hover:bg-wc-bg-secondary hover:text-wc-text focus:outline-none focus:ring-2 focus:ring-wc-accent"
                        :aria-label="'Ver detalle de ' + client.name"
                        title="Ver detalle"
                      >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                          <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                      </button>

                      <!-- Deactivate (superadmin only, not already inactive) -->
                      <button
                        v-if="isSuperadmin && (client.status !== 'inactivo' && client.status?.value !== 'inactivo')"
                        @click.stop="confirmDeactivate(client)"
                        class="inline-flex h-7 w-7 items-center justify-center rounded-lg text-wc-text-tertiary transition-colors hover:bg-amber-500/10 hover:text-amber-500 focus:outline-none focus:ring-2 focus:ring-wc-accent"
                        title="Desactivar"
                        :aria-label="'Desactivar cliente ' + client.name"
                      >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25v13.5m-7.5-13.5v13.5" />
                        </svg>
                      </button>

                      <!-- Delete (superadmin only) -->
                      <button
                        v-if="isSuperadmin"
                        @click.stop="confirmDelete(client)"
                        class="inline-flex h-7 w-7 items-center justify-center rounded-lg text-wc-text-tertiary transition-colors hover:bg-red-500/10 hover:text-red-500 focus:outline-none focus:ring-2 focus:ring-wc-accent"
                        title="Eliminar"
                        :aria-label="'Eliminar cliente ' + client.name"
                      >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Empty state -->
          <div v-if="!clients.length" class="px-4 py-12 text-center">
            <svg class="mx-auto h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
            </svg>
            <p class="mt-2 text-sm text-wc-text-tertiary">No se encontraron clientes</p>
            <button
              v-if="hasActiveFilters"
              @click="clearFilters"
              class="mt-2 text-xs font-medium text-red-500 transition-colors hover:text-red-400"
            >
              Limpiar filtros
            </button>
          </div>

          <!-- Pagination -->
          <div v-if="meta.lastPage > 1" class="flex flex-col items-center justify-between gap-3 border-t border-wc-border px-4 py-3 sm:flex-row">
            <p class="text-xs text-wc-text-tertiary">
              Mostrando <span class="font-data font-semibold text-wc-text-secondary">{{ paginationFrom }}</span>-<span class="font-data font-semibold text-wc-text-secondary">{{ paginationTo }}</span> de <span class="font-data font-semibold text-wc-text-secondary">{{ meta.total }}</span> resultados
            </p>
            <div class="flex items-center gap-1">
              <!-- Previous -->
              <button
                @click="goToPage(meta.currentPage - 1)"
                :disabled="meta.currentPage <= 1"
                class="rounded-lg border border-wc-border bg-wc-bg-secondary px-2.5 py-1.5 text-xs font-medium text-wc-text transition-colors hover:bg-wc-bg-tertiary disabled:opacity-40 disabled:cursor-not-allowed"
              >
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
              </button>

              <!-- Page numbers -->
              <template v-for="(page, idx) in paginationRange" :key="idx">
                <span v-if="page === '...'" class="px-1 text-xs text-wc-text-tertiary">...</span>
                <button
                  v-else
                  @click="goToPage(page)"
                  class="min-w-[28px] rounded-lg px-2 py-1.5 text-xs font-medium transition-colors"
                  :class="page === meta.currentPage
                    ? 'bg-wc-accent text-white'
                    : 'border border-wc-border bg-wc-bg-secondary text-wc-text hover:bg-wc-bg-tertiary'"
                >
                  {{ page }}
                </button>
              </template>

              <!-- Next -->
              <button
                @click="goToPage(meta.currentPage + 1)"
                :disabled="meta.currentPage >= meta.lastPage"
                class="rounded-lg border border-wc-border bg-wc-bg-secondary px-2.5 py-1.5 text-xs font-medium text-wc-text transition-colors hover:bg-wc-bg-tertiary disabled:opacity-40 disabled:cursor-not-allowed"
              >
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </template>

    </div>
  </AdminLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.scale-enter-active { transition: transform 0.2s ease, opacity 0.2s ease; }
.scale-leave-active { transition: transform 0.15s ease, opacity 0.15s ease; }
.scale-enter-from, .scale-leave-to { transform: scale(0.95); opacity: 0; }

.slide-down-enter-active, .slide-down-leave-active { transition: transform 0.3s ease, opacity 0.3s ease; }
.slide-down-enter-from, .slide-down-leave-to { transform: translateY(-1rem); opacity: 0; }
</style>
