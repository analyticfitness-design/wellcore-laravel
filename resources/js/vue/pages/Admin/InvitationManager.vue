<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import AdminLayout from '../../layouts/AdminLayout.vue';

const api = useApi();

// ── State ──────────────────────────────────────────────────────────────
const loading = ref(true);
const invitations = ref([]);
const stats = ref({ total: 0, pending: 0, used: 0, expired: 0 });
const pagination = ref({ current_page: 1, last_page: 1, total: 0, per_page: 20 });

// Filters & sort
const search = ref('');
const statusFilter = ref('all');
const sortBy = ref('created_at');
const sortDir = ref('desc');

// Create modal
const showCreateModal = ref(false);
const creating = ref(false);
const createdCode = ref(null);
const createdIntakeUrl = ref(null);
const newPlan = ref('esencial');
const newEmailHint = ref('');
const newNote = ref('');
const newExpiresAt = ref('');
const formErrors = ref({});

// Copy feedback
const copiedCodeId = ref(null);
const copiedLinkId = ref(null);
const copiedModal = ref(false);

// ── Plan & status config (module-level constants) ──────────────────────
const PLAN_COLORS = {
  esencial:   'bg-sky-500/10 text-sky-400',
  metodo:     'bg-violet-500/10 text-violet-400',
  elite:      'bg-amber-500/10 text-amber-400',
  presencial: 'bg-orange-500/10 text-orange-400',
  rise:       'bg-red-500/10 text-red-400',
};

const STATUS_COLORS = {
  pending: 'bg-amber-500/10 text-amber-400',
  used:    'bg-emerald-500/10 text-emerald-400',
  expired: 'bg-zinc-500/10 text-zinc-400',
};

const STATUS_LABELS = {
  pending: 'Pendiente',
  used:    'Usada',
  expired: 'Expirada',
};

const PLAN_OPTIONS = [
  { value: 'rise',       label: 'Rise' },
  { value: 'esencial',   label: 'Esencial' },
  { value: 'metodo',     label: 'Metodo' },
  { value: 'elite',      label: 'Elite' },
  { value: 'presencial', label: 'Presencial' },
];

// ── Computed ───────────────────────────────────────────────────────────
const hasFilters = computed(() => search.value !== '' || statusFilter.value !== 'all');

// ── Debounced search ──────────────────────────────────────────────────
let debounceTimer = null;
watch(search, () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    pagination.value.current_page = 1;
    fetchInvitations();
  }, 300);
});

watch(statusFilter, () => {
  pagination.value.current_page = 1;
  fetchInvitations();
});

// ── API calls ─────────────────────────────────────────────────────────
async function fetchInvitations() {
  loading.value = true;
  try {
    const params = new URLSearchParams();
    params.set('page', pagination.value.current_page);
    params.set('sort_by', sortBy.value);
    params.set('sort_dir', sortDir.value);
    if (search.value) params.set('search', search.value);
    if (statusFilter.value !== 'all') params.set('status', statusFilter.value);

    const response = await api.get(`/api/v/admin/invitations?${params.toString()}`);
    invitations.value = response.data.invitations ?? [];
    stats.value = response.data.stats ?? { total: 0, pending: 0, used: 0, expired: 0 };
    pagination.value = response.data.pagination ?? pagination.value;
  } catch (e) {
    invitations.value = [];
  } finally {
    loading.value = false;
  }
}

function sortByColumn(column) {
  if (sortBy.value === column) {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortBy.value = column;
    sortDir.value = 'desc';
  }
  fetchInvitations();
}

function clearFilters() {
  search.value = '';
  statusFilter.value = 'all';
  pagination.value.current_page = 1;
  fetchInvitations();
}

function goToPage(page) {
  if (page < 1 || page > pagination.value.last_page) return;
  pagination.value.current_page = page;
  fetchInvitations();
}

// ── Create modal ──────────────────────────────────────────────────────
function openCreateModal() {
  newPlan.value = 'esencial';
  newEmailHint.value = '';
  newNote.value = '';
  newExpiresAt.value = '';
  formErrors.value = {};
  createdCode.value = null;
  createdIntakeUrl.value = null;
  showCreateModal.value = true;
}

function closeCreateModal() {
  showCreateModal.value = false;
  createdCode.value = null;
  createdIntakeUrl.value = null;
  formErrors.value = {};
}

async function createInvitation() {
  creating.value = true;
  formErrors.value = {};
  try {
    const payload = {
      plan: newPlan.value,
      email_hint: newEmailHint.value || null,
      note: newNote.value || null,
      expires_at: newExpiresAt.value || null,
    };
    const response = await api.post('/api/v/admin/invitations', payload);
    createdCode.value = response.data.code;
    createdIntakeUrl.value = response.data.intake_url;
    // Reset form fields but keep modal open to show link
    newPlan.value = 'esencial';
    newEmailHint.value = '';
    newNote.value = '';
    newExpiresAt.value = '';
    // Refresh list
    fetchInvitations();
  } catch (err) {
    if (err.response?.status === 422) {
      formErrors.value = err.response.data.errors || {};
    }
  } finally {
    creating.value = false;
  }
}

// ── Delete ────────────────────────────────────────────────────────────
async function deleteInvitation(id) {
  if (!confirm('Eliminar esta invitacion pendiente?')) return;
  try {
    await api.delete(`/api/v/admin/invitations/${id}`);
    fetchInvitations();
  } catch (e) {
    // silent
  }
}

// ── Clipboard ─────────────────────────────────────────────────────────
function copyCode(inv) {
  navigator.clipboard.writeText(inv.code).then(() => {
    copiedCodeId.value = inv.id;
    setTimeout(() => { copiedCodeId.value = null; }, 2000);
  });
}

function copyLink(inv) {
  if (!inv.intake_url) return;
  navigator.clipboard.writeText(inv.intake_url).then(() => {
    copiedLinkId.value = inv.id;
    setTimeout(() => { copiedLinkId.value = null; }, 2500);
  });
}

function copyModalLink() {
  if (!createdIntakeUrl.value) return;
  navigator.clipboard.writeText(createdIntakeUrl.value).then(() => {
    copiedModal.value = true;
    setTimeout(() => { copiedModal.value = false; }, 2500);
  });
}

// ── Init ──────────────────────────────────────────────────────────────
onMounted(fetchInvitations);
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">

      <!-- Page header -->
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="font-display text-3xl tracking-wide text-wc-text">INVITACIONES</h1>
          <p class="mt-1 text-sm text-wc-text-secondary">Genera y gestiona codigos de invitacion para nuevos clientes.</p>
        </div>
        <button
          @click="openCreateModal"
          class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700 transition-colors"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          Nueva Invitacion
        </button>
      </div>

      <!-- Stats row -->
      <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 text-center">
          <p class="font-data text-2xl font-bold text-wc-text">{{ stats.total }}</p>
          <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Total</p>
        </div>
        <div class="rounded-xl border border-amber-500/30 bg-amber-500/5 p-4 text-center">
          <p class="font-data text-2xl font-bold text-amber-400">{{ stats.pending }}</p>
          <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Pendientes</p>
        </div>
        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/5 p-4 text-center">
          <p class="font-data text-2xl font-bold text-emerald-400">{{ stats.used }}</p>
          <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Usadas</p>
        </div>
        <div class="rounded-xl border border-zinc-500/30 bg-zinc-500/5 p-4 text-center">
          <p class="font-data text-2xl font-bold text-zinc-400">{{ stats.expired }}</p>
          <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Expiradas</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="flex flex-wrap items-center gap-3">
        <!-- Search -->
        <div class="relative min-w-48 flex-1">
          <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
          </svg>
          <input
            v-model="search"
            type="text"
            placeholder="Buscar codigo, email..."
            class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary py-2 pl-10 pr-4 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none"
          />
        </div>

        <!-- Status filter -->
        <select
          v-model="statusFilter"
          class="rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none"
        >
          <option value="all">Todos los estados</option>
          <option value="pending">Pendientes</option>
          <option value="used">Usadas</option>
          <option value="expired">Expiradas</option>
        </select>

        <!-- Clear filters -->
        <button
          v-if="hasFilters"
          @click="clearFilters"
          class="rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2 text-sm text-wc-text-secondary hover:text-wc-text transition-colors"
        >
          Limpiar
        </button>
      </div>

      <!-- Loading skeleton -->
      <template v-if="loading">
        <div class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-tertiary">
          <div class="space-y-0 divide-y divide-wc-border">
            <div v-for="n in 5" :key="n" class="flex items-center gap-4 px-4 py-4">
              <div class="h-4 w-24 animate-pulse rounded bg-wc-bg-secondary"></div>
              <div class="h-5 w-16 animate-pulse rounded-full bg-wc-bg-secondary"></div>
              <div class="hidden h-4 w-32 animate-pulse rounded bg-wc-bg-secondary md:block"></div>
              <div class="h-5 w-16 animate-pulse rounded-full bg-wc-bg-secondary"></div>
              <div class="ml-auto h-4 w-20 animate-pulse rounded bg-wc-bg-secondary"></div>
            </div>
          </div>
        </div>
      </template>

      <!-- Table -->
      <template v-else>
        <div class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-tertiary">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="border-b border-wc-border">
                  <th class="px-4 py-3 text-left">
                    <button @click="sortByColumn('code')" class="flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary hover:text-wc-text">
                      Codigo
                      <svg v-if="sortBy === 'code'" class="h-3 w-3" :class="{ 'rotate-180': sortDir === 'desc' }" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75 12 8.25l7.5 7.5"/>
                      </svg>
                    </button>
                  </th>
                  <th class="px-4 py-3 text-left">
                    <button @click="sortByColumn('plan')" class="flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary hover:text-wc-text">
                      Plan
                      <svg v-if="sortBy === 'plan'" class="h-3 w-3" :class="{ 'rotate-180': sortDir === 'desc' }" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75 12 8.25l7.5 7.5"/>
                      </svg>
                    </button>
                  </th>
                  <th class="hidden px-4 py-3 text-left md:table-cell">
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Email Hint</span>
                  </th>
                  <th class="px-4 py-3 text-left">
                    <button @click="sortByColumn('status')" class="flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary hover:text-wc-text">
                      Estado
                      <svg v-if="sortBy === 'status'" class="h-3 w-3" :class="{ 'rotate-180': sortDir === 'desc' }" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75 12 8.25l7.5 7.5"/>
                      </svg>
                    </button>
                  </th>
                  <th class="hidden px-4 py-3 text-left lg:table-cell">
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Creado por</span>
                  </th>
                  <th class="hidden px-4 py-3 text-left lg:table-cell">
                    <button @click="sortByColumn('created_at')" class="flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary hover:text-wc-text">
                      Creado
                      <svg v-if="sortBy === 'created_at'" class="h-3 w-3" :class="{ 'rotate-180': sortDir === 'desc' }" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75 12 8.25l7.5 7.5"/>
                      </svg>
                    </button>
                  </th>
                  <th class="hidden px-4 py-3 text-left xl:table-cell">
                    <button @click="sortByColumn('expires_at')" class="flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary hover:text-wc-text">
                      Expira
                      <svg v-if="sortBy === 'expires_at'" class="h-3 w-3" :class="{ 'rotate-180': sortDir === 'desc' }" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75 12 8.25l7.5 7.5"/>
                      </svg>
                    </button>
                  </th>
                  <th class="hidden px-4 py-3 text-left xl:table-cell">
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Usado por</span>
                  </th>
                  <th class="hidden px-4 py-3 text-left xl:table-cell">
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Link de registro</span>
                  </th>
                  <th class="px-4 py-3 text-right">
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Acciones</span>
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-wc-border">
                <tr
                  v-for="inv in invitations"
                  :key="inv.id"
                  class="transition-colors hover:bg-wc-bg-secondary/50"
                >
                  <!-- Code -->
                  <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                      <span class="font-data text-sm font-semibold tracking-wider text-wc-text">{{ inv.code }}</span>
                      <button
                        @click="copyCode(inv)"
                        class="flex h-6 w-6 shrink-0 items-center justify-center rounded border border-wc-border text-wc-text-tertiary hover:text-wc-text transition-colors"
                        title="Copiar codigo"
                      >
                        <!-- Clipboard icon -->
                        <svg v-if="copiedCodeId !== inv.id" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9.75a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" />
                        </svg>
                        <!-- Check icon -->
                        <svg v-else class="h-3.5 w-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                      </button>
                    </div>
                  </td>

                  <!-- Plan -->
                  <td class="px-4 py-3">
                    <span
                      class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider"
                      :class="PLAN_COLORS[inv.plan] || 'bg-wc-bg-secondary text-wc-text-secondary'"
                    >
                      {{ inv.plan_label || inv.plan }}
                    </span>
                  </td>

                  <!-- Email hint -->
                  <td class="hidden px-4 py-3 md:table-cell">
                    <span class="text-xs text-wc-text-secondary">{{ inv.email_hint || '\u2014' }}</span>
                  </td>

                  <!-- Status -->
                  <td class="px-4 py-3">
                    <span
                      class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider"
                      :class="STATUS_COLORS[inv.status] || 'bg-wc-bg-secondary text-wc-text-secondary'"
                    >
                      {{ STATUS_LABELS[inv.status] || inv.status }}
                    </span>
                  </td>

                  <!-- Created by -->
                  <td class="hidden px-4 py-3 lg:table-cell">
                    <span class="text-xs text-wc-text-secondary">{{ inv.created_by_name || '\u2014' }}</span>
                  </td>

                  <!-- Created at -->
                  <td class="hidden px-4 py-3 lg:table-cell">
                    <span class="text-xs text-wc-text-tertiary">{{ inv.created_ago || '\u2014' }}</span>
                  </td>

                  <!-- Expires at -->
                  <td class="hidden px-4 py-3 xl:table-cell">
                    <template v-if="inv.expires_at">
                      <span class="text-xs" :class="inv.expires_past ? 'text-red-400' : 'text-wc-text-tertiary'">
                        {{ inv.expires_at }}
                      </span>
                    </template>
                    <span v-else class="text-xs text-wc-text-tertiary">Sin limite</span>
                  </td>

                  <!-- Used by -->
                  <td class="hidden px-4 py-3 xl:table-cell">
                    <span v-if="inv.used_by_name" class="text-xs text-emerald-400">{{ inv.used_by_name }}</span>
                    <span v-else class="text-xs text-wc-text-tertiary">&mdash;</span>
                  </td>

                  <!-- Link de registro -->
                  <td class="hidden px-4 py-3 xl:table-cell">
                    <template v-if="inv.status === 'pending' && inv.intake_url">
                      <div class="flex items-center gap-1.5">
                        <span class="max-w-[180px] truncate font-mono text-[10px] text-wc-text-tertiary" :title="inv.intake_url">
                          /unirse/{{ inv.code }}
                        </span>
                        <button
                          @click="copyLink(inv)"
                          class="flex h-6 w-6 shrink-0 items-center justify-center rounded border border-wc-border text-wc-text-tertiary hover:border-wc-accent hover:text-wc-accent transition-colors"
                          title="Copiar link de registro"
                        >
                          <svg v-if="copiedLinkId !== inv.id" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                          </svg>
                          <svg v-else class="h-3 w-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                          </svg>
                        </button>
                      </div>
                    </template>
                    <span v-else class="text-xs text-wc-text-tertiary">&mdash;</span>
                  </td>

                  <!-- Actions -->
                  <td class="px-4 py-3 text-right">
                    <button
                      v-if="inv.status === 'pending'"
                      @click="deleteInvitation(inv.id)"
                      class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-1.5 text-xs font-medium text-red-400 hover:border-red-500 hover:bg-red-500/10 transition-colors"
                    >
                      Eliminar
                    </button>
                    <span v-else-if="inv.status === 'used'" class="text-xs text-wc-text-tertiary">
                      {{ inv.used_at }}
                    </span>
                    <span v-else class="text-xs text-wc-text-tertiary">&mdash;</span>
                  </td>
                </tr>

                <!-- Empty state -->
                <tr v-if="!loading && invitations.length === 0">
                  <td colspan="10" class="px-4 py-16 text-center">
                    <div class="flex flex-col items-center gap-3">
                      <svg class="h-10 w-10 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                      </svg>
                      <p class="text-sm text-wc-text-tertiary">No hay invitaciones con los filtros seleccionados.</p>
                      <button
                        @click="openCreateModal"
                        class="mt-1 inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-700 transition-colors"
                      >
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Crear primera invitacion
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </template>

      <!-- Pagination -->
      <div v-if="!loading && pagination.last_page > 1" class="flex justify-center gap-1">
        <button
          @click="goToPage(pagination.current_page - 1)"
          :disabled="pagination.current_page === 1"
          class="rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-1.5 text-xs font-medium text-wc-text-secondary hover:text-wc-text transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
        >
          Anterior
        </button>
        <template v-for="page in pagination.last_page" :key="page">
          <button
            v-if="page === 1 || page === pagination.last_page || Math.abs(page - pagination.current_page) <= 2"
            @click="goToPage(page)"
            class="min-w-[32px] rounded-lg border px-2 py-1.5 text-xs font-medium transition-colors"
            :class="page === pagination.current_page
              ? 'border-wc-accent bg-wc-accent text-white'
              : 'border-wc-border bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-text'"
          >
            {{ page }}
          </button>
          <span
            v-else-if="page === 2 && pagination.current_page > 4"
            class="px-1 text-xs text-wc-text-tertiary"
          >...</span>
          <span
            v-else-if="page === pagination.last_page - 1 && pagination.current_page < pagination.last_page - 3"
            class="px-1 text-xs text-wc-text-tertiary"
          >...</span>
        </template>
        <button
          @click="goToPage(pagination.current_page + 1)"
          :disabled="pagination.current_page === pagination.last_page"
          class="rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-1.5 text-xs font-medium text-wc-text-secondary hover:text-wc-text transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
        >
          Siguiente
        </button>
      </div>

    </div>

    <!-- Create Modal -->
    <Transition name="fade">
      <div
        v-if="showCreateModal"
        class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center"
      >
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeCreateModal"></div>

        <!-- Modal panel -->
        <div class="relative z-10 w-full max-w-lg rounded-2xl border border-wc-border bg-wc-bg-secondary p-6 shadow-2xl">
          <div class="mb-5 flex items-start justify-between gap-4">
            <div>
              <h2 class="font-display text-2xl tracking-wide text-wc-text">NUEVA INVITACION</h2>
              <p class="mt-1 text-sm text-wc-text-secondary">Se generara un codigo unico de 12 caracteres.</p>
            </div>
            <button
              @click="closeCreateModal"
              class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-wc-border text-wc-text-secondary hover:text-wc-text"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Created success: show link -->
          <div v-if="createdCode" class="mb-5 rounded-xl border border-emerald-500/30 bg-emerald-500/8 p-4">
            <div class="mb-2 flex items-center gap-2">
              <svg class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
              </svg>
              <span class="text-sm font-semibold text-emerald-300">Invitacion creada &mdash; codigo: <span class="font-mono">{{ createdCode }}</span></span>
            </div>
            <p class="mb-3 text-xs text-wc-text-secondary">Comparte este link con el cliente para que complete su registro:</p>
            <div class="flex items-center gap-2 rounded-lg border border-emerald-500/20 bg-wc-bg-tertiary px-3 py-2.5">
              <span class="flex-1 truncate font-mono text-xs text-wc-text">{{ createdIntakeUrl }}</span>
              <button
                type="button"
                @click="copyModalLink"
                class="flex shrink-0 items-center gap-1.5 rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-1 text-xs font-medium text-wc-text-secondary hover:border-wc-accent hover:text-wc-accent transition-colors"
              >
                <template v-if="!copiedModal">
                  <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9.75a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" />
                  </svg>
                  Copiar
                </template>
                <template v-else>
                  <svg class="h-3 w-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                  </svg>
                  <span class="text-emerald-400">Copiado!</span>
                </template>
              </button>
            </div>
            <div class="mt-3">
              <button
                type="button"
                @click="closeCreateModal"
                class="w-full rounded-lg bg-emerald-600 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors"
              >
                Listo, cerrar
              </button>
            </div>
          </div>

          <!-- Create form (hidden when code was generated) -->
          <form v-if="!createdCode" @submit.prevent="createInvitation" class="space-y-4">
            <!-- Plan -->
            <div>
              <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                Plan <span class="text-wc-accent">*</span>
              </label>
              <select
                v-model="newPlan"
                class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none"
              >
                <option v-for="p in PLAN_OPTIONS" :key="p.value" :value="p.value">{{ p.label }}</option>
              </select>
              <p v-if="formErrors.plan" class="mt-1 text-xs text-wc-accent">{{ formErrors.plan[0] }}</p>
            </div>

            <!-- Email hint -->
            <div>
              <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                Email / Referencia
              </label>
              <input
                v-model="newEmailHint"
                type="text"
                placeholder="email@ejemplo.com o nombre del referido"
                class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none"
              />
              <p v-if="formErrors.email_hint" class="mt-1 text-xs text-wc-accent">{{ formErrors.email_hint[0] }}</p>
            </div>

            <!-- Note -->
            <div>
              <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                Nota interna
              </label>
              <textarea
                v-model="newNote"
                rows="3"
                placeholder="Nota opcional sobre esta invitacion..."
                class="w-full resize-none rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none"
              ></textarea>
              <p v-if="formErrors.note" class="mt-1 text-xs text-wc-accent">{{ formErrors.note[0] }}</p>
            </div>

            <!-- Expires at -->
            <div>
              <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                Fecha de expiracion
              </label>
              <input
                v-model="newExpiresAt"
                type="date"
                class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none"
              />
              <p class="mt-1 text-[10px] text-wc-text-tertiary">Dejar vacio para que no expire.</p>
              <p v-if="formErrors.expires_at" class="mt-1 text-xs text-wc-accent">{{ formErrors.expires_at[0] }}</p>
            </div>

            <!-- Actions -->
            <div class="flex gap-3 pt-1">
              <button
                type="button"
                @click="closeCreateModal"
                class="flex-1 rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 text-sm font-medium text-wc-text-secondary hover:text-wc-text transition-colors"
              >
                Cancelar
              </button>
              <button
                type="submit"
                :disabled="creating"
                class="flex-1 rounded-lg bg-red-600 py-2.5 text-sm font-semibold text-white hover:bg-red-700 transition-colors disabled:opacity-70 disabled:cursor-not-allowed"
              >
                <span v-if="!creating">Crear Invitacion</span>
                <span v-else class="inline-flex items-center justify-center gap-2">
                  <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                  </svg>
                  Creando...
                </span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>
  </AdminLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
