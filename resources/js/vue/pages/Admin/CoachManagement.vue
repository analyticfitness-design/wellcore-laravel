<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import AdminLayout from '../../layouts/AdminLayout.vue';

const api = useApi();

// ─── List state ──────────────────────────────────────────────────────────────
const loading     = ref(false);
const coaches     = ref([]);
const pagination  = ref({ current_page: 1, last_page: 1, total: 0 });
const stats       = ref({ total: 0, coaches: 0, with_profile: 0, clients: 0 });
const search      = ref('');
const roleFilter  = ref('all');
const sortBy      = ref('created_at');
const sortDir     = ref('desc');
const currentPage = ref(1);

// ─── Create modal ─────────────────────────────────────────────────────────────
const showCreateModal = ref(false);
const creating        = ref(false);
const createErrors    = ref({});
const createForm      = ref({ name: '', username: '', password: '', role: 'coach' });

// ─── Edit profile modal ───────────────────────────────────────────────────────
const showEditModal = ref(false);
const editingId     = ref(null);
const saving        = ref(false);
const editErrors    = ref({});
const editForm      = ref({
    bio: '', city: '', experience: '', specializations: '',
    whatsapp: '', instagram: '', referral_code: '', referral_commission: '',
    public_visible: false,
});

// ─── View detail modal ────────────────────────────────────────────────────────
const showViewModal  = ref(false);
const viewingCoach   = ref(null);
const viewLoading    = ref(false);

// ─── Delete state ─────────────────────────────────────────────────────────────
const deletingId = ref(null);

// ─── Debounce timer (module-level, not reactive) ──────────────────────────────
let debounceTimer = null;

// ─── Role badge color map ─────────────────────────────────────────────────────
const ROLE_COLORS = {
    superadmin: 'bg-red-500/10 text-red-400',
    admin:      'bg-violet-500/10 text-violet-400',
    coach:      'bg-sky-500/10 text-sky-400',
    jefe:       'bg-amber-500/10 text-amber-400',
};

const AVATAR_COLORS = {
    superadmin: 'bg-red-500/20 text-red-400',
    admin:      'bg-violet-500/20 text-violet-400',
    coach:      'bg-sky-500/20 text-sky-400',
    jefe:       'bg-amber-500/20 text-amber-400',
};

// ─── Computed ─────────────────────────────────────────────────────────────────
const hasPages = computed(() => pagination.value.last_page > 1);

function roleBadgeClass(role) {
    return ROLE_COLORS[role] ?? 'bg-wc-bg-secondary text-wc-text-secondary';
}

function avatarClass(role) {
    return AVATAR_COLORS[role] ?? 'bg-wc-bg-secondary text-wc-text-secondary';
}

function initial(name) {
    return (name || 'A').charAt(0).toUpperCase();
}

// ─── Fetch coaches list ────────────────────────────────────────────────────────
async function fetchCoaches(page = 1) {
    loading.value = true;
    try {
        const params = new URLSearchParams({
            search:   search.value,
            role:     roleFilter.value,
            sort_by:  sortBy.value,
            sort_dir: sortDir.value,
            page:     String(page),
        });
        const res = await api.get(`/api/v/admin/coaches?${params}`);
        coaches.value    = res.data.coaches ?? [];
        pagination.value = res.data.pagination ?? { current_page: 1, last_page: 1, total: 0 };
        currentPage.value = pagination.value.current_page;
    } catch {
        coaches.value = [];
    } finally {
        loading.value = false;
    }
}

async function fetchStats() {
    try {
        const res = await api.get('/api/v/admin/coaches/stats');
        stats.value = res.data;
    } catch {
        // stats are non-critical
    }
}

// ─── Sorting ──────────────────────────────────────────────────────────────────
function sortByColumn(col) {
    if (sortBy.value === col) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value  = col;
        sortDir.value = 'desc';
    }
    fetchCoaches(1);
}

// ─── Search + filter watchers ─────────────────────────────────────────────────
watch(search, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => fetchCoaches(1), 300);
});

watch(roleFilter, () => fetchCoaches(1));

// ─── Create coach ─────────────────────────────────────────────────────────────
function openCreate() {
    createForm.value  = { name: '', username: '', password: '', role: 'coach' };
    createErrors.value = {};
    showCreateModal.value = true;
}

function closeCreate() {
    showCreateModal.value = false;
}

async function submitCreate() {
    creating.value     = true;
    createErrors.value = {};
    try {
        await api.post('/api/v/admin/coaches', createForm.value);
        closeCreate();
        fetchCoaches(currentPage.value);
        fetchStats();
    } catch (err) {
        if (err.response?.status === 422) {
            createErrors.value = err.response.data.errors ?? {};
        }
    } finally {
        creating.value = false;
    }
}

// ─── Edit profile ─────────────────────────────────────────────────────────────
async function openEdit(id) {
    editingId.value    = id;
    editErrors.value   = {};
    showEditModal.value = true;

    // Fetch full detail to pre-populate form
    try {
        const res = await api.get(`/api/v/admin/coaches/${id}`);
        const c   = res.data;
        editForm.value = {
            bio:                c.bio ?? '',
            city:               c.city ?? '',
            experience:         c.experience ?? '',
            specializations:    Array.isArray(c.specializations) ? c.specializations.join(', ') : '',
            whatsapp:           c.whatsapp ?? '',
            instagram:          c.instagram ?? '',
            referral_code:      c.referral_code ?? '',
            referral_commission: c.referral_commission != null ? String(c.referral_commission) : '',
            public_visible:     !!c.public_visible,
        };
    } catch {
        // form stays blank — user can still fill it
    }
}

function closeEdit() {
    showEditModal.value = false;
    editingId.value     = null;
}

async function submitEdit() {
    saving.value      = true;
    editErrors.value  = {};
    try {
        await api.put(`/api/v/admin/coaches/${editingId.value}`, editForm.value);
        closeEdit();
        fetchCoaches(currentPage.value);
        fetchStats();
    } catch (err) {
        if (err.response?.status === 422) {
            editErrors.value = err.response.data.errors ?? {};
        }
    } finally {
        saving.value = false;
    }
}

// ─── View detail ──────────────────────────────────────────────────────────────
async function openView(id) {
    viewingCoach.value  = null;
    viewLoading.value   = true;
    showViewModal.value = true;
    try {
        const res       = await api.get(`/api/v/admin/coaches/${id}`);
        viewingCoach.value = res.data;
    } catch {
        showViewModal.value = false;
    } finally {
        viewLoading.value = false;
    }
}

function closeView() {
    showViewModal.value = false;
    viewingCoach.value  = null;
}

// ─── Toggle visibility ────────────────────────────────────────────────────────
async function toggleVisibility(coach) {
    try {
        const res = await api.patch(`/api/v/admin/coaches/${coach.id}/visibility`);
        coach.public_visible = res.data.public_visible;
    } catch {
        // noop
    }
}

// ─── Delete ───────────────────────────────────────────────────────────────────
async function deleteCoach(id, displayName) {
    if (!confirm(`¿Eliminar a ${displayName}? Esta accion no se puede deshacer.`)) return;
    deletingId.value = id;
    try {
        await api.delete(`/api/v/admin/coaches/${id}`);
        fetchCoaches(currentPage.value);
        fetchStats();
    } catch (err) {
        alert(err.response?.data?.error ?? 'Error al eliminar el coach.');
    } finally {
        deletingId.value = null;
    }
}

// ─── Pagination ───────────────────────────────────────────────────────────────
function goToPage(page) {
    if (page < 1 || page > pagination.value.last_page) return;
    fetchCoaches(page);
}

// ─── Init ─────────────────────────────────────────────────────────────────────
onMounted(() => {
    fetchCoaches(1);
    fetchStats();
});
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">

      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="font-display text-3xl tracking-wide text-wc-text">GESTION DE COACHES</h1>
          <p class="mt-1 text-sm text-wc-text-secondary">Administra coaches, perfiles y asignaciones de clientes.</p>
        </div>
        <button
          @click="openCreate"
          class="inline-flex items-center gap-2 rounded-lg bg-wc-accent px-4 py-2 text-sm font-semibold text-white transition-colors hover:opacity-90"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          Nuevo Coach
        </button>
      </div>

      <!-- Stats row -->
      <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 text-center">
          <p class="font-data text-2xl font-bold text-wc-text">{{ stats.total }}</p>
          <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Total</p>
        </div>
        <div class="rounded-xl border border-sky-500/30 bg-sky-500/5 p-4 text-center">
          <p class="font-data text-2xl font-bold text-sky-400">{{ stats.coaches }}</p>
          <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Coaches</p>
        </div>
        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/5 p-4 text-center">
          <p class="font-data text-2xl font-bold text-emerald-400">{{ stats.with_profile }}</p>
          <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Con Perfil</p>
        </div>
        <div class="rounded-xl border border-violet-500/30 bg-violet-500/5 p-4 text-center">
          <p class="font-data text-2xl font-bold text-violet-400">{{ stats.clients }}</p>
          <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Clientes Asignados</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="flex flex-wrap gap-3">
        <div class="relative min-w-48 flex-1">
          <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
          </svg>
          <input
            v-model="search"
            type="text"
            placeholder="Buscar por nombre o usuario..."
            class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 pl-10 pr-4 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none"
          />
        </div>
        <select
          v-model="roleFilter"
          class="rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none"
        >
          <option value="all">Todos los roles</option>
          <option value="superadmin">Superadmin</option>
          <option value="admin">Admin</option>
          <option value="coach">Coach</option>
          <option value="jefe">Jefe</option>
        </select>
      </div>

      <!-- Loading skeleton -->
      <template v-if="loading">
        <div class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-tertiary">
          <div v-for="n in 5" :key="n" class="flex items-center gap-4 border-b border-wc-border px-4 py-3 last:border-b-0">
            <div class="h-9 w-9 animate-pulse rounded-full bg-wc-bg-secondary"></div>
            <div class="flex-1 space-y-1.5">
              <div class="h-3 w-40 animate-pulse rounded bg-wc-bg-secondary"></div>
              <div class="h-2.5 w-24 animate-pulse rounded bg-wc-bg-secondary"></div>
            </div>
            <div class="h-5 w-16 animate-pulse rounded-full bg-wc-bg-secondary"></div>
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
                    <button
                      @click="sortByColumn('name')"
                      class="flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary hover:text-wc-text"
                    >
                      Coach
                      <svg
                        v-if="sortBy === 'name'"
                        class="h-3 w-3 transition-transform"
                        :class="sortDir === 'asc' ? '' : 'rotate-180'"
                        fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                      >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75 12 8.25l7.5 7.5" />
                      </svg>
                    </button>
                  </th>
                  <th class="hidden px-4 py-3 text-left sm:table-cell">
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Rol</span>
                  </th>
                  <th class="hidden px-4 py-3 text-left md:table-cell">
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Ciudad</span>
                  </th>
                  <th class="hidden px-4 py-3 text-center lg:table-cell">
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Clientes</span>
                  </th>
                  <th class="hidden px-4 py-3 text-left lg:table-cell">
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Especialidades</span>
                  </th>
                  <th class="hidden px-4 py-3 text-center sm:table-cell">
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Publico</span>
                  </th>
                  <th class="px-4 py-3 text-right">
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Acciones</span>
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-wc-border">
                <template v-if="coaches.length">
                  <tr
                    v-for="coach in coaches"
                    :key="coach.id"
                    class="transition-colors hover:bg-wc-bg-secondary/50"
                  >
                    <!-- Avatar + Name -->
                    <td class="px-4 py-3">
                      <div class="flex items-center gap-3">
                        <div
                          class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full"
                          :class="avatarClass(coach.role)"
                        >
                          <span class="text-sm font-semibold">{{ initial(coach.name) }}</span>
                        </div>
                        <div>
                          <div class="text-sm font-medium text-wc-text">{{ coach.name || '—' }}</div>
                          <div class="text-xs text-wc-text-tertiary">{{ coach.username }}</div>
                        </div>
                      </div>
                    </td>

                    <!-- Role badge -->
                    <td class="hidden px-4 py-3 sm:table-cell">
                      <span
                        class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold"
                        :class="roleBadgeClass(coach.role)"
                      >
                        {{ coach.role_label || coach.role }}
                      </span>
                    </td>

                    <!-- City -->
                    <td class="hidden px-4 py-3 md:table-cell">
                      <span class="text-xs text-wc-text-secondary">{{ coach.city || '—' }}</span>
                    </td>

                    <!-- Client count -->
                    <td class="hidden px-4 py-3 text-center lg:table-cell">
                      <span class="font-data text-sm font-semibold text-wc-text">{{ coach.client_count ?? 0 }}</span>
                    </td>

                    <!-- Specializations -->
                    <td class="hidden px-4 py-3 lg:table-cell">
                      <div v-if="coach.specializations && coach.specializations.length" class="flex flex-wrap gap-1">
                        <span
                          v-for="(spec, i) in coach.specializations.slice(0, 3)"
                          :key="i"
                          class="rounded-full bg-wc-bg-secondary px-2 py-0.5 text-[10px] text-wc-text-secondary"
                        >{{ spec }}</span>
                        <span
                          v-if="coach.specializations.length > 3"
                          class="rounded-full bg-wc-bg-secondary px-2 py-0.5 text-[10px] text-wc-text-tertiary"
                        >+{{ coach.specializations.length - 3 }}</span>
                      </div>
                      <span v-else class="text-xs text-wc-text-tertiary">—</span>
                    </td>

                    <!-- Public visible toggle -->
                    <td class="hidden px-4 py-3 text-center sm:table-cell">
                      <button
                        v-if="coach.has_profile"
                        @click="toggleVisibility(coach)"
                        class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold transition-colors"
                        :class="coach.public_visible
                          ? 'bg-emerald-500/10 text-emerald-400'
                          : 'bg-wc-bg-secondary text-wc-text-tertiary hover:bg-emerald-500/10 hover:text-emerald-400'"
                      >
                        {{ coach.public_visible ? 'Visible' : 'Oculto' }}
                      </button>
                      <span v-else class="text-xs text-wc-text-tertiary">—</span>
                    </td>

                    <!-- Actions -->
                    <td class="px-4 py-3 text-right">
                      <div class="flex items-center justify-end gap-1.5">
                        <!-- View detail -->
                        <button
                          @click="openView(coach.id)"
                          class="rounded-lg border border-wc-border bg-wc-bg-secondary px-2.5 py-1.5 text-xs font-medium text-wc-text-secondary transition-colors hover:border-sky-500 hover:text-sky-400"
                          title="Ver detalle"
                        >
                          <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                          </svg>
                        </button>

                        <!-- Edit profile (coach + has_profile) -->
                        <button
                          v-if="coach.has_profile || coach.role === 'coach'"
                          @click="openEdit(coach.id)"
                          class="rounded-lg border border-wc-border bg-wc-bg-secondary px-2.5 py-1.5 text-xs font-medium text-wc-text-secondary transition-colors hover:border-wc-accent hover:text-wc-accent"
                          title="Editar perfil"
                        >
                          <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                          </svg>
                        </button>

                        <!-- Delete (non-superadmin rows only) -->
                        <button
                          v-if="coach.role !== 'superadmin'"
                          @click="deleteCoach(coach.id, coach.name || coach.username)"
                          :disabled="deletingId === coach.id"
                          class="rounded-lg border border-wc-border bg-wc-bg-secondary px-2.5 py-1.5 text-xs font-medium text-wc-text-secondary transition-colors hover:border-red-500 hover:text-red-400 disabled:opacity-50"
                          title="Eliminar coach"
                        >
                          <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                          </svg>
                        </button>
                      </div>
                    </td>
                  </tr>
                </template>

                <!-- Empty state -->
                <tr v-else>
                  <td colspan="7" class="px-4 py-12 text-center text-sm text-wc-text-tertiary">
                    No se encontraron coaches con los filtros seleccionados.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="hasPages" class="flex items-center justify-between">
          <p class="text-xs text-wc-text-tertiary">
            Pagina {{ pagination.current_page }} de {{ pagination.last_page }} &mdash; {{ pagination.total }} registros
          </p>
          <div class="flex gap-2">
            <button
              @click="goToPage(pagination.current_page - 1)"
              :disabled="pagination.current_page === 1"
              class="rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-1.5 text-xs font-medium text-wc-text-secondary transition-colors hover:text-wc-text disabled:opacity-40"
            >
              Anterior
            </button>
            <button
              @click="goToPage(pagination.current_page + 1)"
              :disabled="pagination.current_page === pagination.last_page"
              class="rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-1.5 text-xs font-medium text-wc-text-secondary transition-colors hover:text-wc-text disabled:opacity-40"
            >
              Siguiente
            </button>
          </div>
        </div>
      </template>

    </div>

    <!-- ==================== CREATE MODAL ==================== -->
    <Transition name="fade">
      <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeCreate"></div>
        <Transition name="slide-up">
          <div v-if="showCreateModal" class="relative z-10 w-full max-w-lg rounded-2xl border border-wc-border bg-wc-bg-secondary p-6 shadow-2xl">
            <div class="mb-5 flex items-start justify-between">
              <h2 class="font-display text-2xl tracking-wide text-wc-text">NUEVO COACH</h2>
              <button @click="closeCreate" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-wc-border text-wc-text-secondary hover:text-wc-text">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <form @submit.prevent="submitCreate" class="space-y-4">
              <!-- Name -->
              <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                  Nombre <span class="text-wc-accent">*</span>
                </label>
                <input
                  v-model="createForm.name"
                  type="text"
                  placeholder="Nombre completo"
                  class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none"
                  :class="createErrors.name ? 'border-wc-accent' : ''"
                />
                <p v-if="createErrors.name" class="mt-1 text-xs text-wc-accent">{{ createErrors.name[0] }}</p>
              </div>

              <!-- Username -->
              <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                  Usuario <span class="text-wc-accent">*</span>
                </label>
                <input
                  v-model="createForm.username"
                  type="text"
                  placeholder="usuario_login"
                  class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none"
                  :class="createErrors.username ? 'border-wc-accent' : ''"
                />
                <p v-if="createErrors.username" class="mt-1 text-xs text-wc-accent">{{ createErrors.username[0] }}</p>
              </div>

              <!-- Password -->
              <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                  Contrasena <span class="text-wc-accent">*</span>
                </label>
                <input
                  v-model="createForm.password"
                  type="password"
                  placeholder="Minimo 8 caracteres"
                  class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none"
                  :class="createErrors.password ? 'border-wc-accent' : ''"
                />
                <p v-if="createErrors.password" class="mt-1 text-xs text-wc-accent">{{ createErrors.password[0] }}</p>
              </div>

              <!-- Role -->
              <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Rol</label>
                <select
                  v-model="createForm.role"
                  class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none"
                >
                  <option value="coach">Coach</option>
                  <option value="admin">Admin</option>
                  <option value="jefe">Jefe</option>
                  <option value="superadmin">Superadmin</option>
                </select>
              </div>

              <div class="flex gap-3 pt-1">
                <button
                  type="button"
                  @click="closeCreate"
                  class="flex-1 rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 text-sm font-medium text-wc-text-secondary transition-colors hover:text-wc-text"
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  :disabled="creating"
                  class="flex-1 rounded-lg bg-wc-accent py-2.5 text-sm font-semibold text-white transition-colors hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-70"
                >
                  <span v-if="!creating">Crear Coach</span>
                  <span v-else class="inline-flex items-center justify-center gap-2">
                    <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Creando...
                  </span>
                </button>
              </div>
            </form>
          </div>
        </Transition>
      </div>
    </Transition>

    <!-- ==================== EDIT PROFILE MODAL ==================== -->
    <Transition name="fade">
      <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeEdit"></div>
        <Transition name="slide-up">
          <div v-if="showEditModal" class="relative z-10 max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl border border-wc-border bg-wc-bg-secondary p-6 shadow-2xl">
            <div class="mb-5 flex items-start justify-between">
              <h2 class="font-display text-2xl tracking-wide text-wc-text">EDITAR PERFIL</h2>
              <button @click="closeEdit" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-wc-border text-wc-text-secondary hover:text-wc-text">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <form @submit.prevent="submitEdit" class="space-y-4">
              <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                  <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Ciudad</label>
                  <input
                    v-model="editForm.city"
                    type="text"
                    placeholder="Monterrey"
                    class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none"
                    :class="editErrors.city ? 'border-wc-accent' : ''"
                  />
                  <p v-if="editErrors.city" class="mt-1 text-xs text-wc-accent">{{ editErrors.city[0] }}</p>
                </div>
                <div>
                  <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Experiencia</label>
                  <input
                    v-model="editForm.experience"
                    type="text"
                    placeholder="5 anos, certificado NSCA"
                    class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none"
                  />
                </div>
              </div>

              <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Bio</label>
                <textarea
                  v-model="editForm.bio"
                  rows="3"
                  placeholder="Descripcion breve del coach..."
                  class="w-full resize-none rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none"
                  :class="editErrors.bio ? 'border-wc-accent' : ''"
                ></textarea>
                <p v-if="editErrors.bio" class="mt-1 text-xs text-wc-accent">{{ editErrors.bio[0] }}</p>
              </div>

              <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                  Especialidades
                  <span class="text-[10px] normal-case text-wc-text-tertiary">(separadas por coma)</span>
                </label>
                <input
                  v-model="editForm.specializations"
                  type="text"
                  placeholder="Fuerza, Hipertrofia, Nutricion deportiva"
                  class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none"
                />
              </div>

              <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                  <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">WhatsApp</label>
                  <input
                    v-model="editForm.whatsapp"
                    type="text"
                    placeholder="+52 811 234 5678"
                    class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none"
                  />
                </div>
                <div>
                  <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Instagram</label>
                  <input
                    v-model="editForm.instagram"
                    type="text"
                    placeholder="@coach_fitness"
                    class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none"
                  />
                </div>
              </div>

              <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                  <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Codigo Referido</label>
                  <input
                    v-model="editForm.referral_code"
                    type="text"
                    placeholder="COACH2026"
                    class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none"
                  />
                </div>
                <div>
                  <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Comision Referido (%)</label>
                  <input
                    v-model="editForm.referral_commission"
                    type="number"
                    step="0.01"
                    min="0"
                    max="100"
                    placeholder="15.00"
                    class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none"
                  />
                </div>
              </div>

              <!-- Public visible toggle -->
              <div class="flex items-center gap-3">
                <button
                  type="button"
                  @click="editForm.public_visible = !editForm.public_visible"
                  role="switch"
                  :aria-checked="String(editForm.public_visible)"
                  class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200"
                  :class="editForm.public_visible ? 'bg-emerald-500' : 'bg-wc-bg-tertiary'"
                >
                  <span
                    class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow ring-0 transition-transform duration-200"
                    :class="editForm.public_visible ? 'translate-x-5' : 'translate-x-0'"
                  ></span>
                </button>
                <span class="text-sm text-wc-text-secondary">Perfil publico visible</span>
              </div>

              <div class="flex gap-3 pt-1">
                <button
                  type="button"
                  @click="closeEdit"
                  class="flex-1 rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 text-sm font-medium text-wc-text-secondary transition-colors hover:text-wc-text"
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  :disabled="saving"
                  class="flex-1 rounded-lg bg-wc-accent py-2.5 text-sm font-semibold text-white transition-colors hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-70"
                >
                  <span v-if="!saving">Guardar Perfil</span>
                  <span v-else class="inline-flex items-center justify-center gap-2">
                    <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Guardando...
                  </span>
                </button>
              </div>
            </form>
          </div>
        </Transition>
      </div>
    </Transition>

    <!-- ==================== VIEW DETAIL MODAL ==================== -->
    <Transition name="fade">
      <div v-if="showViewModal" class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeView"></div>
        <Transition name="slide-up">
          <div v-if="showViewModal" class="relative z-10 max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl border border-wc-border bg-wc-bg-secondary p-6 shadow-2xl">

            <!-- Loading state -->
            <div v-if="viewLoading" class="flex items-center justify-center py-12">
              <svg class="h-8 w-8 animate-spin text-wc-accent" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
              </svg>
            </div>

            <template v-else-if="viewingCoach">
              <!-- Header -->
              <div class="mb-5 flex items-start justify-between">
                <div class="flex items-center gap-4">
                  <div
                    class="flex h-14 w-14 items-center justify-center rounded-full"
                    :class="avatarClass(viewingCoach.role)"
                  >
                    <span class="font-display text-2xl">{{ initial(viewingCoach.name) }}</span>
                  </div>
                  <div>
                    <h2 class="font-display text-2xl tracking-wide text-wc-text">
                      {{ (viewingCoach.name || 'SIN NOMBRE').toUpperCase() }}
                    </h2>
                    <div class="mt-1 flex items-center gap-2">
                      <span class="text-xs text-wc-text-tertiary">@{{ viewingCoach.username }}</span>
                      <span
                        class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold"
                        :class="roleBadgeClass(viewingCoach.role)"
                      >
                        {{ viewingCoach.role_label || viewingCoach.role }}
                      </span>
                    </div>
                  </div>
                </div>
                <button @click="closeView" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-wc-border text-wc-text-secondary hover:text-wc-text">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>

              <!-- Mini stats -->
              <div class="mb-5 grid grid-cols-3 gap-3">
                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3 text-center">
                  <p class="font-data text-lg font-bold text-wc-text">{{ viewingCoach.client_count ?? 0 }}</p>
                  <p class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">Clientes</p>
                </div>
                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3 text-center">
                  <p class="font-data text-lg font-bold text-wc-text">
                    {{ viewingCoach.referral_commission != null ? viewingCoach.referral_commission + '%' : '—' }}
                  </p>
                  <p class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">Comision</p>
                </div>
                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3 text-center">
                  <p
                    class="font-data text-lg font-bold"
                    :class="viewingCoach.public_visible ? 'text-emerald-400' : 'text-wc-text-tertiary'"
                  >
                    {{ viewingCoach.public_visible ? 'SI' : 'NO' }}
                  </p>
                  <p class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">Publico</p>
                </div>
              </div>

              <!-- Profile detail -->
              <div v-if="viewingCoach.has_profile" class="space-y-4">
                <div v-if="viewingCoach.bio" class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
                  <h4 class="mb-1.5 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Bio</h4>
                  <p class="text-sm leading-relaxed text-wc-text">{{ viewingCoach.bio }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                  <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
                    <h4 class="mb-1 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Ciudad</h4>
                    <p class="text-sm text-wc-text">{{ viewingCoach.city || '—' }}</p>
                  </div>
                  <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
                    <h4 class="mb-1 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Experiencia</h4>
                    <p class="text-sm text-wc-text">{{ viewingCoach.experience || '—' }}</p>
                  </div>
                  <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
                    <h4 class="mb-1 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">WhatsApp</h4>
                    <p class="text-sm text-wc-text">{{ viewingCoach.whatsapp || '—' }}</p>
                  </div>
                  <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
                    <h4 class="mb-1 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Instagram</h4>
                    <p class="text-sm text-wc-text">{{ viewingCoach.instagram || '—' }}</p>
                  </div>
                </div>

                <div v-if="viewingCoach.specializations && viewingCoach.specializations.length" class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
                  <h4 class="mb-2 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Especialidades</h4>
                  <div class="flex flex-wrap gap-1.5">
                    <span
                      v-for="(spec, i) in viewingCoach.specializations"
                      :key="i"
                      class="rounded-full bg-sky-500/10 px-2.5 py-1 text-xs font-medium text-sky-400"
                    >{{ spec }}</span>
                  </div>
                </div>

                <div v-if="viewingCoach.referral_code" class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
                  <h4 class="mb-1 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Codigo Referido</h4>
                  <p class="font-mono text-sm font-semibold text-wc-accent">{{ viewingCoach.referral_code }}</p>
                </div>
              </div>

              <!-- No profile -->
              <div v-else class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-8 text-center">
                <p class="text-sm text-wc-text-tertiary">Este admin no tiene perfil de coach configurado.</p>
                <button
                  v-if="viewingCoach.role === 'coach'"
                  @click="closeView(); openEdit(viewingCoach.id)"
                  class="mt-3 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white transition-colors hover:opacity-90"
                >
                  Crear Perfil
                </button>
              </div>
            </template>
          </div>
        </Transition>
      </div>
    </Transition>

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

.slide-up-enter-active,
.slide-up-leave-active {
  transition: transform 0.3s ease, opacity 0.3s ease;
}
.slide-up-enter-from,
.slide-up-leave-to {
  transform: translateY(2rem);
  opacity: 0;
}
</style>
