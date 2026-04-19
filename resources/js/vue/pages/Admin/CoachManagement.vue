<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import AdminLayout from '../../layouts/AdminLayout.vue';

const api = useApi();

// ─── List state ───────────────────────────────────────────────────────────────
const loading     = ref(false);
const coaches     = ref([]);
const search      = ref('');
const statusFilter = ref('all'); // all|active|inactive
const meta        = ref({ total: 0 });

let debounceTimer = null;

// ─── Toast ────────────────────────────────────────────────────────────────────
const toast = ref({ show: false, type: 'success', message: '' });
function showToast(message, type = 'success') {
  toast.value = { show: true, type, message };
  setTimeout(() => { toast.value.show = false; }, 4000);
}

// ─── Create modal ─────────────────────────────────────────────────────────────
const showCreateModal = ref(false);
const creating        = ref(false);
const createErrors    = ref({});
const createForm      = ref({ name: '', username: '', email: '', whatsapp: '', password: '' });

// ─── Edit modal ───────────────────────────────────────────────────────────────
const showEditModal = ref(false);
const editing       = ref(null);
const saving        = ref(false);
const editErrors    = ref({});
const editForm      = ref({ name: '', email: '', whatsapp: '', active: true });

// ─── Confirm modals ───────────────────────────────────────────────────────────
const confirmReset     = ref({ show: false, coach: null, loading: false, error: '' });
const confirmDelete    = ref({ show: false, coach: null, loading: false, error: '' });

// ─── Password strength ────────────────────────────────────────────────────────
const passwordStrength = computed(() => {
  const p = createForm.value.password || '';
  let score = 0;
  if (p.length >= 10) score++;
  if (/[a-z]/.test(p) && /[A-Z]/.test(p)) score++;
  if (/\d/.test(p)) score++;
  if (/[^A-Za-z0-9]/.test(p)) score++;
  return score; // 0..4
});
const strengthLabel = computed(() => ['Muy debil', 'Debil', 'Regular', 'Buena', 'Fuerte'][passwordStrength.value]);
const strengthColor = computed(() =>
  ['bg-red-500', 'bg-red-500', 'bg-amber-500', 'bg-sky-500', 'bg-emerald-500'][passwordStrength.value]
);

function generatePassword() {
  const lower = 'abcdefghijkmnpqrstuvwxyz';
  const upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
  const digits = '23456789';
  const symbols = '!@#$%&*';
  const all = lower + upper + digits + symbols;
  let p = '';
  p += lower[Math.floor(Math.random() * lower.length)];
  p += upper[Math.floor(Math.random() * upper.length)];
  p += digits[Math.floor(Math.random() * digits.length)];
  p += symbols[Math.floor(Math.random() * symbols.length)];
  for (let i = 0; i < 10; i++) p += all[Math.floor(Math.random() * all.length)];
  createForm.value.password = p.split('').sort(() => Math.random() - 0.5).join('');
}

function initial(name) {
  return (name || 'C').charAt(0).toUpperCase();
}

// ─── Fetch coaches ────────────────────────────────────────────────────────────
async function fetchCoaches() {
  loading.value = true;
  try {
    const params = new URLSearchParams();
    if (search.value) params.set('search', search.value);
    if (statusFilter.value !== 'all') params.set('status', statusFilter.value);
    const { data } = await api.get(`/api/v/admin/coaches/manage?${params}`);
    coaches.value = data.coaches ?? [];
    meta.value    = data.meta ?? { total: coaches.value.length };
  } catch {
    coaches.value = [];
  } finally {
    loading.value = false;
  }
}

watch(search, () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(fetchCoaches, 300);
});
watch(statusFilter, () => fetchCoaches());

// ─── Create coach ─────────────────────────────────────────────────────────────
function openCreate() {
  createForm.value  = { name: '', username: '', email: '', whatsapp: '', password: '' };
  createErrors.value = {};
  showCreateModal.value = true;
}

async function submitCreate() {
  creating.value = true;
  createErrors.value = {};
  try {
    const { data } = await api.post('/api/v/admin/coaches/manage', createForm.value);
    showCreateModal.value = false;
    const to = createForm.value.email || 'el nuevo coach';
    showToast(`Coach creado. Credenciales enviadas a ${to}`);
    fetchCoaches();
  } catch (err) {
    if (err.response?.status === 422) {
      createErrors.value = err.response.data.errors ?? {};
    } else {
      showToast(err.response?.data?.error || 'Error al crear coach', 'error');
    }
  } finally {
    creating.value = false;
  }
}

// ─── Edit coach ───────────────────────────────────────────────────────────────
function openEdit(coach) {
  editing.value = coach;
  editErrors.value = {};
  editForm.value = {
    name:     coach.name ?? '',
    email:    coach.email ?? '',
    whatsapp: coach.whatsapp ?? '',
    active:   !!coach.active,
  };
  showEditModal.value = true;
}

async function submitEdit() {
  if (!editing.value) return;
  saving.value = true;
  editErrors.value = {};
  try {
    await api.put(`/api/v/admin/coaches/manage/${editing.value.id}`, editForm.value);
    showEditModal.value = false;
    showToast('Cambios guardados');
    fetchCoaches();
  } catch (err) {
    if (err.response?.status === 422) {
      editErrors.value = err.response.data.errors ?? {};
    } else {
      showToast(err.response?.data?.error || 'Error al guardar', 'error');
    }
  } finally {
    saving.value = false;
  }
}

// ─── Reset password ───────────────────────────────────────────────────────────
function openResetConfirm(coach) {
  confirmReset.value = { show: true, coach, loading: false, error: '' };
}

async function doReset() {
  if (!confirmReset.value.coach) return;
  if (!confirmReset.value.coach.email) {
    confirmReset.value.error = 'El coach no tiene email configurado. Actualiza su email primero.';
    return;
  }
  confirmReset.value.loading = true;
  confirmReset.value.error = '';
  try {
    const { data } = await api.post(`/api/v/admin/coaches/manage/${confirmReset.value.coach.id}/reset-password`);
    confirmReset.value.show = false;
    showToast(`Password enviada a ${data.password_sent_to_email || confirmReset.value.coach.email}`);
  } catch (err) {
    confirmReset.value.error = err.response?.data?.error || 'Error al resetear la contrasena.';
  } finally {
    confirmReset.value.loading = false;
  }
}

// ─── Deactivate / delete ──────────────────────────────────────────────────────
function openDeleteConfirm(coach) {
  confirmDelete.value = { show: true, coach, loading: false, error: '' };
}

async function doDelete() {
  if (!confirmDelete.value.coach) return;
  confirmDelete.value.loading = true;
  confirmDelete.value.error = '';
  try {
    await api.delete(`/api/v/admin/coaches/manage/${confirmDelete.value.coach.id}`);
    confirmDelete.value.show = false;
    showToast('Coach desactivado');
    fetchCoaches();
  } catch (err) {
    if (err.response?.status === 409) {
      confirmDelete.value.error = err.response.data?.error ||
        'Este coach tiene clientes asignados. Reasignalos antes de desactivar.';
    } else {
      confirmDelete.value.error = err.response?.data?.error || 'Error al desactivar.';
    }
  } finally {
    confirmDelete.value.loading = false;
  }
}

function formatDate(iso) {
  if (!iso) return '—';
  try {
    const d = new Date(iso);
    return d.toLocaleDateString('es-MX', { year: 'numeric', month: 'short', day: 'numeric' });
  } catch {
    return '—';
  }
}

onMounted(fetchCoaches);
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">

      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="font-display text-3xl tracking-wide text-wc-text">GESTION DE COACHES</h1>
          <p class="mt-1 text-sm text-wc-text-secondary">
            {{ meta.total || coaches.length }} coach{{ (meta.total || coaches.length) === 1 ? '' : 'es' }} registrado{{ (meta.total || coaches.length) === 1 ? '' : 's' }}
          </p>
        </div>
        <button
          @click="openCreate"
          class="inline-flex items-center gap-2 rounded-lg bg-wc-accent px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:opacity-90"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          Nuevo coach
        </button>
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
            placeholder="Buscar por nombre, usuario o email..."
            class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 pl-10 pr-4 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none"
          />
        </div>
        <select
          v-model="statusFilter"
          class="rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none"
        >
          <option value="all">Todos</option>
          <option value="active">Activos</option>
          <option value="inactive">Inactivos</option>
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

      <!-- Empty -->
      <div v-else-if="!coaches.length" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center">
        <p class="text-sm text-wc-text-secondary">No hay coaches que coincidan con los filtros.</p>
      </div>

      <!-- Table -->
      <div v-else class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-tertiary">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-wc-border">
                <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Nombre</th>
                <th class="hidden px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary sm:table-cell">Usuario</th>
                <th class="hidden px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary md:table-cell">Email</th>
                <th class="hidden px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary lg:table-cell">WhatsApp</th>
                <th class="hidden px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary sm:table-cell">Clientes</th>
                <th class="hidden px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary xl:table-cell">Ultimo login</th>
                <th class="px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Estado</th>
                <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-wc-border">
              <tr
                v-for="coach in coaches"
                :key="coach.id"
                class="transition-colors hover:bg-wc-bg-secondary/40"
              >
                <td class="px-4 py-3">
                  <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-sky-500/15">
                      <span class="text-sm font-semibold text-sky-400">{{ initial(coach.name) }}</span>
                    </div>
                    <div class="min-w-0">
                      <div class="truncate text-sm font-medium text-wc-text">{{ coach.name || '—' }}</div>
                      <div class="truncate text-xs text-wc-text-tertiary sm:hidden">@{{ coach.username }}</div>
                    </div>
                  </div>
                </td>
                <td class="hidden px-4 py-3 sm:table-cell">
                  <span class="font-mono text-xs text-wc-text-secondary">@{{ coach.username }}</span>
                </td>
                <td class="hidden px-4 py-3 md:table-cell">
                  <span class="text-xs text-wc-text-secondary">{{ coach.email || '—' }}</span>
                </td>
                <td class="hidden px-4 py-3 lg:table-cell">
                  <span class="text-xs text-wc-text-secondary">{{ coach.whatsapp || '—' }}</span>
                </td>
                <td class="hidden px-4 py-3 text-center sm:table-cell">
                  <span class="font-data text-sm font-semibold text-wc-text">{{ coach.client_count ?? 0 }}</span>
                </td>
                <td class="hidden px-4 py-3 xl:table-cell">
                  <span class="text-xs text-wc-text-tertiary">{{ formatDate(coach.last_login_at) }}</span>
                </td>
                <td class="px-4 py-3 text-center">
                  <span
                    class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold"
                    :class="coach.active
                      ? 'bg-emerald-500/10 text-emerald-500'
                      : 'bg-wc-bg-secondary text-wc-text-tertiary'"
                  >
                    {{ coach.active ? 'Activo' : 'Inactivo' }}
                  </span>
                </td>
                <td class="px-4 py-3 text-right">
                  <div class="flex items-center justify-end gap-1.5">
                    <button
                      @click="openEdit(coach)"
                      title="Editar"
                      class="rounded-lg border border-wc-border bg-wc-bg-secondary px-2.5 py-1.5 text-xs text-wc-text-secondary transition-colors hover:border-wc-accent hover:text-wc-accent"
                    >
                      <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                      </svg>
                    </button>
                    <button
                      @click="openResetConfirm(coach)"
                      title="Resetear contrasena"
                      class="rounded-lg border border-wc-border bg-wc-bg-secondary px-2.5 py-1.5 text-xs text-wc-text-secondary transition-colors hover:border-amber-500 hover:text-amber-500"
                    >
                      <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" />
                      </svg>
                    </button>
                    <button
                      v-if="coach.active"
                      @click="openDeleteConfirm(coach)"
                      title="Desactivar"
                      class="rounded-lg border border-wc-border bg-wc-bg-secondary px-2.5 py-1.5 text-xs text-wc-text-secondary transition-colors hover:border-red-500 hover:text-red-400"
                    >
                      <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ==================== CREATE MODAL ==================== -->
    <Transition name="fade">
      <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showCreateModal = false"></div>
        <Transition name="slide-up">
          <div v-if="showCreateModal" class="relative z-10 max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-wc-border bg-wc-bg-secondary p-6 shadow-2xl">
            <div class="mb-5 flex items-start justify-between">
              <h2 class="font-display text-2xl tracking-wide text-wc-text">NUEVO COACH</h2>
              <button @click="showCreateModal = false" class="flex h-8 w-8 items-center justify-center rounded-lg border border-wc-border text-wc-text-secondary hover:text-wc-text">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
              </button>
            </div>

            <form @submit.prevent="submitCreate" class="space-y-4">
              <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Nombre <span class="text-wc-accent">*</span></label>
                <input v-model="createForm.name" type="text" placeholder="Nombre completo"
                       class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none"
                       :class="createErrors.name ? 'border-wc-accent' : ''" />
                <p v-if="createErrors.name" class="mt-1 text-xs text-wc-accent">{{ createErrors.name[0] }}</p>
              </div>

              <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Usuario <span class="text-wc-accent">*</span></label>
                <input v-model="createForm.username" type="text" placeholder="usuario_login" @input="createForm.username = createForm.username.replace(/\s+/g, '')"
                       class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none font-mono"
                       :class="createErrors.username ? 'border-wc-accent' : ''" />
                <p class="mt-1 text-[10px] text-wc-text-tertiary">Sin espacios. Debe ser unico.</p>
                <p v-if="createErrors.username" class="mt-1 text-xs text-wc-accent">{{ createErrors.username[0] }}</p>
              </div>

              <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Email</label>
                <input v-model="createForm.email" type="email" placeholder="coach@ejemplo.com"
                       class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none"
                       :class="createErrors.email ? 'border-wc-accent' : ''" />
                <p v-if="createErrors.email" class="mt-1 text-xs text-wc-accent">{{ createErrors.email[0] }}</p>
              </div>

              <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">WhatsApp</label>
                <input v-model="createForm.whatsapp" type="tel" placeholder="+57 300 123 4567"
                       class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none"
                       :class="createErrors.whatsapp ? 'border-wc-accent' : ''" />
                <p v-if="createErrors.whatsapp" class="mt-1 text-xs text-wc-accent">{{ createErrors.whatsapp[0] }}</p>
              </div>

              <div>
                <div class="mb-1.5 flex items-center justify-between">
                  <label class="block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Contrasena <span class="text-wc-accent">*</span></label>
                  <button type="button" @click="generatePassword" class="text-[11px] font-medium text-wc-accent hover:underline">Generar aleatoria</button>
                </div>
                <input v-model="createForm.password" type="text"
                       placeholder="Minimo 10 caracteres"
                       class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 font-mono text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none"
                       :class="createErrors.password ? 'border-wc-accent' : ''" />
                <div v-if="createForm.password" class="mt-1.5">
                  <div class="flex gap-1">
                    <div v-for="n in 4" :key="n" class="h-1 flex-1 rounded-full transition-colors"
                         :class="n <= passwordStrength ? strengthColor : 'bg-wc-border'"></div>
                  </div>
                  <p class="mt-1 text-[10px] text-wc-text-tertiary">Fortaleza: {{ strengthLabel }}</p>
                </div>
                <p v-if="createErrors.password" class="mt-1 text-xs text-wc-accent">{{ createErrors.password[0] }}</p>
              </div>

              <div class="flex gap-3 pt-1">
                <button type="button" @click="showCreateModal = false"
                        class="flex-1 rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 text-sm font-medium text-wc-text-secondary transition-colors hover:text-wc-text">
                  Cancelar
                </button>
                <button type="submit" :disabled="creating"
                        class="flex-1 rounded-lg bg-wc-accent py-2.5 text-sm font-semibold text-white transition-colors hover:opacity-90 disabled:opacity-60">
                  {{ creating ? 'Creando...' : 'Crear coach' }}
                </button>
              </div>
            </form>
          </div>
        </Transition>
      </div>
    </Transition>

    <!-- ==================== EDIT MODAL ==================== -->
    <Transition name="fade">
      <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showEditModal = false"></div>
        <Transition name="slide-up">
          <div v-if="showEditModal" class="relative z-10 max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-wc-border bg-wc-bg-secondary p-6 shadow-2xl">
            <div class="mb-5 flex items-start justify-between">
              <h2 class="font-display text-2xl tracking-wide text-wc-text">EDITAR COACH</h2>
              <button @click="showEditModal = false" class="flex h-8 w-8 items-center justify-center rounded-lg border border-wc-border text-wc-text-secondary hover:text-wc-text">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
              </button>
            </div>

            <form @submit.prevent="submitEdit" class="space-y-4">
              <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Nombre</label>
                <input v-model="editForm.name" type="text"
                       class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none"
                       :class="editErrors.name ? 'border-wc-accent' : ''" />
                <p v-if="editErrors.name" class="mt-1 text-xs text-wc-accent">{{ editErrors.name[0] }}</p>
              </div>

              <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Email</label>
                <input v-model="editForm.email" type="email"
                       class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none"
                       :class="editErrors.email ? 'border-wc-accent' : ''" />
                <p v-if="editErrors.email" class="mt-1 text-xs text-wc-accent">{{ editErrors.email[0] }}</p>
              </div>

              <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">WhatsApp</label>
                <input v-model="editForm.whatsapp" type="tel"
                       class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none"
                       :class="editErrors.whatsapp ? 'border-wc-accent' : ''" />
                <p v-if="editErrors.whatsapp" class="mt-1 text-xs text-wc-accent">{{ editErrors.whatsapp[0] }}</p>
              </div>

              <div class="flex items-center justify-between rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5">
                <div>
                  <p class="text-sm font-medium text-wc-text">Coach activo</p>
                  <p class="text-[10px] text-wc-text-tertiary">Puede iniciar sesion y gestionar clientes</p>
                </div>
                <button type="button" @click="editForm.active = !editForm.active" role="switch" :aria-checked="String(editForm.active)"
                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors"
                        :class="editForm.active ? 'bg-emerald-500' : 'bg-wc-bg-secondary'">
                  <span class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transition-transform"
                        :class="editForm.active ? 'translate-x-5' : 'translate-x-0'"></span>
                </button>
              </div>

              <div class="flex gap-3 pt-1">
                <button type="button" @click="showEditModal = false"
                        class="flex-1 rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 text-sm font-medium text-wc-text-secondary hover:text-wc-text">
                  Cancelar
                </button>
                <button type="submit" :disabled="saving"
                        class="flex-1 rounded-lg bg-wc-accent py-2.5 text-sm font-semibold text-white hover:opacity-90 disabled:opacity-60">
                  {{ saving ? 'Guardando...' : 'Guardar' }}
                </button>
              </div>
            </form>
          </div>
        </Transition>
      </div>
    </Transition>

    <!-- ==================== RESET PASSWORD CONFIRM ==================== -->
    <Transition name="fade">
      <div v-if="confirmReset.show" class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="confirmReset.show = false"></div>
        <Transition name="slide-up">
          <div v-if="confirmReset.show" class="relative z-10 w-full max-w-md rounded-2xl border border-wc-border bg-wc-bg-secondary p-6 shadow-2xl">
            <h3 class="font-display text-xl tracking-wide text-wc-text">RESETEAR CONTRASENA</h3>
            <p class="mt-2 text-sm text-wc-text-secondary">
              Se generara una nueva contrasena temporal y se enviara a
              <span class="font-semibold text-wc-text">{{ confirmReset.coach?.email || '—' }}</span>.
            </p>
            <p v-if="!confirmReset.coach?.email" class="mt-3 rounded-lg border border-amber-500/40 bg-amber-500/10 p-3 text-xs text-amber-400">
              Este coach no tiene email configurado. Agrega un email antes de resetear.
            </p>
            <p v-if="confirmReset.error" class="mt-3 rounded-lg border border-red-500/40 bg-red-500/10 p-3 text-xs text-red-400">
              {{ confirmReset.error }}
            </p>
            <div class="mt-5 flex gap-3">
              <button @click="confirmReset.show = false"
                      class="flex-1 rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 text-sm font-medium text-wc-text-secondary hover:text-wc-text">
                Cancelar
              </button>
              <button @click="doReset" :disabled="confirmReset.loading || !confirmReset.coach?.email"
                      class="flex-1 rounded-lg bg-amber-500 py-2.5 text-sm font-semibold text-black hover:opacity-90 disabled:opacity-50">
                {{ confirmReset.loading ? 'Enviando...' : 'Enviar nueva' }}
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>

    <!-- ==================== DEACTIVATE CONFIRM ==================== -->
    <Transition name="fade">
      <div v-if="confirmDelete.show" class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="confirmDelete.show = false"></div>
        <Transition name="slide-up">
          <div v-if="confirmDelete.show" class="relative z-10 w-full max-w-md rounded-2xl border border-wc-border bg-wc-bg-secondary p-6 shadow-2xl">
            <h3 class="font-display text-xl tracking-wide text-wc-text">DESACTIVAR COACH</h3>
            <p class="mt-2 text-sm text-wc-text-secondary">
              Estas por desactivar a <span class="font-semibold text-wc-text">{{ confirmDelete.coach?.name }}</span>.
              El coach perdera acceso a la plataforma y sus clientes deberan ser reasignados a otro coach.
            </p>
            <p v-if="confirmDelete.error" class="mt-3 rounded-lg border border-red-500/40 bg-red-500/10 p-3 text-xs text-red-400">
              {{ confirmDelete.error }}
            </p>
            <div class="mt-5 flex gap-3">
              <button @click="confirmDelete.show = false"
                      class="flex-1 rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 text-sm font-medium text-wc-text-secondary hover:text-wc-text">
                Cancelar
              </button>
              <button @click="doDelete" :disabled="confirmDelete.loading"
                      class="flex-1 rounded-lg bg-red-500 py-2.5 text-sm font-semibold text-white hover:opacity-90 disabled:opacity-60">
                {{ confirmDelete.loading ? 'Desactivando...' : 'Desactivar' }}
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>

    <!-- ==================== TOAST ==================== -->
    <Transition name="slide-up">
      <div v-if="toast.show" class="fixed bottom-6 left-1/2 z-[100] -translate-x-1/2 rounded-xl border px-5 py-3 text-sm font-medium shadow-xl"
           :class="toast.type === 'error'
              ? 'border-red-500/40 bg-red-500/10 text-red-400'
              : 'border-emerald-500/40 bg-emerald-500/10 text-emerald-400'">
        {{ toast.message }}
      </div>
    </Transition>
  </AdminLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.slide-up-enter-active, .slide-up-leave-active { transition: transform 0.3s ease, opacity 0.3s ease; }
.slide-up-enter-from, .slide-up-leave-to { transform: translateY(1.5rem); opacity: 0; }
</style>
