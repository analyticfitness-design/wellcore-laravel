<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useApi } from '../../composables/useApi';
import AdminLayout from '../../layouts/AdminLayout.vue';

const api = useApi();
const route = useRoute();
const router = useRouter();

const loading = ref(true);
const saving = ref(false);
const error = ref(null);
const client = ref(null);
const coaches = ref([]);
const plans = ref([]);
const statusOptions = ref([]);
const planOptions = ref([]);
const activeTab = ref('info');
const editMode = ref(false);
const editForm = ref({});
const successMessage = ref('');
const showCoachModal = ref(false);
const selectedCoachId = ref(0);
const assignPlanType = ref('entrenamiento');
const assigningCoach = ref(false);

// Quick-action forms
const editStatus = ref('');
const editPlan = ref('');
const savingStatus = ref(false);
const savingPlan = ref(false);

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

const tabs = [
  { key: 'info', label: 'Informacion' },
  { key: 'plans', label: 'Planes' },
  { key: 'checkins', label: 'Check-ins' },
  { key: 'payments', label: 'Pagos' },
  { key: 'metrics', label: 'Metricas' },
  { key: 'activity', label: 'Actividad' },
];

// Plan color map
const PLAN_COLORS = {
  esencial: 'bg-sky-500/10 text-sky-500',
  metodo: 'bg-violet-500/10 text-violet-500',
  elite: 'bg-amber-500/10 text-amber-500',
  rise: 'bg-emerald-500/10 text-emerald-500',
  presencial: 'bg-orange-500/10 text-orange-500',
};

function getPlanColor(plan) {
  return PLAN_COLORS[plan] || 'bg-wc-bg-secondary text-wc-text-tertiary';
}

function getStatusColor(status) {
  const colors = {
    activo: 'bg-emerald-500/10 text-emerald-500',
    active: 'bg-emerald-500/10 text-emerald-500',
    inactivo: 'bg-zinc-500/10 text-zinc-400',
    inactive: 'bg-zinc-500/10 text-zinc-400',
    suspendido: 'bg-red-500/10 text-red-500',
    suspended: 'bg-red-500/10 text-red-500',
    pendiente: 'bg-amber-500/10 text-amber-500',
    pending: 'bg-amber-500/10 text-amber-500',
    congelado: 'bg-sky-500/10 text-sky-500',
  };
  return colors[status] || 'bg-zinc-500/10 text-zinc-400';
}

function getPaymentStatusColor(status) {
  const colors = {
    approved: 'bg-emerald-500/10 text-emerald-500',
    pending: 'bg-amber-500/10 text-amber-500',
    declined: 'bg-red-500/10 text-red-500',
    rejected: 'bg-red-500/10 text-red-500',
    cancelled: 'bg-red-500/10 text-red-500',
    voided: 'bg-zinc-500/10 text-zinc-400',
    error: 'bg-red-500/10 text-red-500',
  };
  return colors[status] || 'bg-wc-bg-secondary text-wc-text-tertiary';
}

async function fetchClient() {
  loading.value = true;
  error.value = null;
  try {
    const response = await api.get(`/api/v/admin/clients/${route.params.id}`);
    const data = response.data;
    client.value = data.client || data;
    coaches.value = data.coaches || [];
    plans.value = data.plans || [];
    statusOptions.value = data.statusOptions || [];
    planOptions.value = data.planOptions || [];
    editForm.value = { ...(data.client || data) };
    editStatus.value = client.value.status || '';
    editPlan.value = client.value.plan || '';
  } catch (err) {
    error.value = err.response?.data?.message || 'Error al cargar el cliente';
  } finally {
    loading.value = false;
  }
}

function startEdit() {
  editForm.value = { ...client.value };
  editMode.value = true;
}

function cancelEdit() {
  editMode.value = false;
  editForm.value = { ...client.value };
}

async function saveClient() {
  saving.value = true;
  try {
    const response = await api.put(`/api/v/admin/clients/${route.params.id}`, editForm.value);
    client.value = response.data.client || response.data;
    editMode.value = false;
    showSuccess('Cliente actualizado correctamente');
  } catch (err) {
    error.value = err.response?.data?.message || 'Error al guardar';
  } finally {
    saving.value = false;
  }
}

async function updateStatus() {
  savingStatus.value = true;
  try {
    const response = await api.put(`/api/v/admin/clients/${route.params.id}`, { status: editStatus.value });
    client.value = response.data.client || { ...client.value, status: editStatus.value };
    const label = statusOptions.value.find(s => s.value === editStatus.value)?.label || editStatus.value;
    showSuccess(`Estado actualizado a ${label}`);
  } catch (err) {
    error.value = err.response?.data?.message || 'Error al actualizar estado';
  } finally {
    savingStatus.value = false;
  }
}

async function updatePlan() {
  savingPlan.value = true;
  try {
    const response = await api.put(`/api/v/admin/clients/${route.params.id}`, { plan: editPlan.value });
    client.value = response.data.client || { ...client.value, plan: editPlan.value };
    const label = planOptions.value.find(p => p.value === editPlan.value)?.label || editPlan.value;
    showSuccess(`Plan actualizado a ${label}`);
  } catch (err) {
    error.value = err.response?.data?.message || 'Error al actualizar plan';
  } finally {
    savingPlan.value = false;
  }
}

function openCoachModal() {
  selectedCoachId.value = 0;
  assignPlanType.value = 'entrenamiento';
  showCoachModal.value = true;
}

async function assignCoach() {
  if (!selectedCoachId.value || selectedCoachId.value === 0) return;
  assigningCoach.value = true;
  try {
    await api.put(`/api/v/admin/clients/${route.params.id}`, {
      coach_id: selectedCoachId.value,
      assign_plan_type: assignPlanType.value,
    });
    const coachName = coaches.value.find(c => c.id === Number(selectedCoachId.value))?.name || 'Coach';
    showSuccess(`Coach ${coachName} asignado correctamente al plan de ${assignPlanType.value}`);
    showCoachModal.value = false;
    await fetchClient();
  } catch (err) {
    error.value = err.response?.data?.message || err.response?.data?.error || 'Error al asignar coach';
  } finally {
    assigningCoach.value = false;
  }
}

let successTimer = null;
function showSuccess(msg) {
  successMessage.value = msg;
  clearTimeout(successTimer);
  successTimer = setTimeout(() => { successMessage.value = ''; }, 5000);
}

function dismissSuccess() {
  successMessage.value = '';
  clearTimeout(successTimer);
}

onMounted(() => {
  fetchClient();
});
</script>

<template>
  <AdminLayout>
    <!-- Loading -->
    <div v-if="loading" class="space-y-6">
      <div class="flex items-center gap-4">
        <div class="h-9 w-9 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
        <div class="flex items-center gap-4">
          <div class="h-14 w-14 animate-pulse rounded-full bg-wc-bg-tertiary"></div>
          <div class="space-y-2">
            <div class="h-8 w-56 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
            <div class="h-4 w-40 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
          </div>
        </div>
      </div>
      <div class="h-12 animate-pulse rounded-xl bg-wc-bg-tertiary"></div>
      <div class="h-64 animate-pulse rounded-xl bg-wc-bg-tertiary"></div>
    </div>

    <!-- Error (no client loaded) -->
    <div v-else-if="error && !client" class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-6 text-center">
      <p class="text-sm text-wc-text">{{ error }}</p>
      <div class="mt-4 flex justify-center gap-3">
        <button @click="fetchClient" class="rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white">Reintentar</button>
        <button @click="router.push('/admin/clients')" class="rounded-lg border border-wc-border px-4 py-2 text-sm font-medium text-wc-text">Volver</button>
      </div>
    </div>

    <!-- Client Detail -->
    <div v-else-if="client" class="space-y-6">

      <!-- Success message -->
      <Transition name="fade">
        <div v-if="successMessage" class="flex items-center justify-between rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3">
          <div class="flex items-center gap-2">
            <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <span class="text-sm font-medium text-emerald-500">{{ successMessage }}</span>
          </div>
          <button @click="dismissSuccess" class="text-emerald-500 hover:text-emerald-400">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </Transition>

      <!-- Inline error -->
      <Transition name="fade">
        <div v-if="error && client" class="flex items-center justify-between rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3">
          <span class="text-sm font-medium text-red-400">{{ error }}</span>
          <button @click="error = null" class="text-red-400 hover:text-red-300">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </Transition>

      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex items-start gap-4">
          <button @click="router.push('/admin/clients')" class="mt-1 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-wc-border bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-text transition-colors" aria-label="Volver">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
          </button>
          <div class="flex items-center gap-4">
            <!-- Avatar -->
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-red-500/10">
              <img v-if="client.avatar_url" :src="client.avatar_url" :alt="client.name" class="h-14 w-14 rounded-full object-cover" />
              <span v-else class="font-display text-2xl text-red-500">{{ (client.name || 'C').charAt(0).toUpperCase() }}</span>
            </div>
            <div>
              <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ client.name }}</h1>
              <div class="mt-1 flex flex-wrap items-center gap-2">
                <span class="font-data text-sm text-wc-text-tertiary">{{ client.client_code || 'Sin codigo' }}</span>
                <span class="text-wc-text-tertiary">|</span>
                <span class="text-sm text-wc-text-tertiary">{{ client.email }}</span>
                <span v-if="client.plan" class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold" :class="getPlanColor(client.plan)">
                  {{ client.plan_label || client.plan }}
                </span>
                <span v-if="client.status" class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold" :class="getStatusColor(client.status)">
                  {{ client.status_label || client.status }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Action buttons -->
        <div class="flex items-center gap-2">
          <!-- Ver Portal (impersonate) -->
          <form method="POST" :action="`/admin/impersonate/${client.id}`">
            <input type="hidden" name="_token" :value="csrfToken" />
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2 text-sm font-medium text-wc-text-secondary hover:border-wc-accent/50 hover:text-wc-text transition-colors focus:outline-none focus:ring-2 focus:ring-wc-accent" aria-label="Ver portal del cliente">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
              </svg>
              Ver Portal
            </button>
          </form>

          <!-- Asignar Coach -->
          <button @click="openCoachModal" class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
            </svg>
            Asignar Coach
          </button>

          <!-- Editar -->
          <button v-if="!editMode" @click="startEdit" class="rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors">
            Editar
          </button>
        </div>
      </div>

      <!-- Current Coach card -->
      <div v-if="client.coachName" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
        <div class="flex items-center gap-3">
          <div class="flex h-9 w-9 items-center justify-center rounded-full bg-violet-500/10">
            <svg class="h-4 w-4 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
            </svg>
          </div>
          <div>
            <p class="text-xs text-wc-text-tertiary">Coach asignado</p>
            <p class="text-sm font-medium text-wc-text">{{ client.coachName }}</p>
          </div>
        </div>
      </div>

      <!-- Tab Navigation -->
      <div class="flex gap-1 overflow-x-auto rounded-xl border border-wc-border bg-wc-bg-tertiary p-1">
        <button
          v-for="tab in tabs"
          :key="tab.key"
          @click="activeTab = tab.key"
          :class="[
            'flex-1 whitespace-nowrap rounded-lg px-4 py-2 text-sm font-medium transition-colors',
            activeTab === tab.key
              ? 'bg-red-500/10 text-wc-text border-l-2 border-red-500'
              : 'text-wc-text-secondary hover:text-wc-text hover:bg-wc-bg-secondary'
          ]"
        >
          {{ tab.label }}
        </button>
      </div>

      <!-- ============== TAB: INFO ============== -->
      <div v-if="activeTab === 'info'" class="grid gap-6 lg:grid-cols-2">

        <!-- Client Info Card -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 space-y-4">
          <h2 class="font-display text-xl tracking-wide text-wc-text">Datos del Cliente</h2>

          <div v-if="editMode" class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-2">
              <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Nombre</label>
                <input v-model="editForm.name" type="text" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20" />
              </div>
              <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Email</label>
                <input v-model="editForm.email" type="email" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20" />
              </div>
              <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Telefono</label>
                <input v-model="editForm.phone" type="tel" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20" />
              </div>
              <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Estado</label>
                <select v-model="editForm.status" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20">
                  <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                </select>
              </div>
            </div>
            <div class="flex gap-3">
              <button @click="saveClient" :disabled="saving" class="rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors disabled:opacity-50">
                {{ saving ? 'Guardando...' : 'Guardar cambios' }}
              </button>
              <button @click="cancelEdit" class="rounded-lg border border-wc-border px-4 py-2 text-sm font-medium text-wc-text hover:bg-wc-bg-secondary transition-colors">
                Cancelar
              </button>
            </div>
          </div>

          <div v-else class="grid gap-4 sm:grid-cols-2">
            <div>
              <label class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Nombre</label>
              <p class="mt-1 text-sm text-wc-text">{{ client.name || '-' }}</p>
            </div>
            <div>
              <label class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Email</label>
              <p class="mt-1 text-sm text-wc-text">{{ client.email || '-' }}</p>
            </div>
            <div>
              <label class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Codigo</label>
              <p class="mt-1 font-data text-sm text-wc-text">{{ client.client_code || '-' }}</p>
            </div>
            <div>
              <label class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Telefono</label>
              <p class="mt-1 text-sm text-wc-text">{{ client.phone || '-' }}</p>
            </div>
            <div>
              <label class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Ciudad</label>
              <p class="mt-1 text-sm text-wc-text">{{ client.city || client.country || '-' }}</p>
            </div>
            <div>
              <label class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Fecha Nacimiento</label>
              <p class="mt-1 font-data text-sm text-wc-text">{{ client.birth_date || '-' }}</p>
            </div>
            <div>
              <label class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Fecha Inicio</label>
              <p class="mt-1 font-data text-sm text-wc-text">{{ client.fecha_inicio || client.registeredAt || '-' }}</p>
            </div>
            <div>
              <label class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Coach asignado</label>
              <p class="mt-1 text-sm text-wc-text">{{ client.coachName || 'Sin asignar' }}</p>
            </div>
            <div>
              <label class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Referral Code</label>
              <p class="mt-1 font-data text-sm text-wc-text">{{ client.referral_code || '-' }}</p>
            </div>
            <div>
              <label class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Referido por</label>
              <p class="mt-1 font-data text-sm text-wc-text">{{ client.referred_by || '-' }}</p>
            </div>
          </div>

          <div v-if="!editMode && client.bio">
            <label class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Bio</label>
            <p class="mt-1 text-sm text-wc-text-secondary">{{ client.bio }}</p>
          </div>
        </div>

        <!-- Quick Actions column -->
        <div class="space-y-6">

          <!-- Change Status -->
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 space-y-4">
            <h2 class="font-display text-xl tracking-wide text-wc-text">Cambiar Estado</h2>
            <div class="flex items-end gap-3">
              <div class="flex-1">
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Estado</label>
                <select v-model="editStatus" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary py-2 pl-3 pr-8 text-sm text-wc-text focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500">
                  <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                </select>
              </div>
              <button @click="updateStatus" :disabled="savingStatus" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors disabled:opacity-50">
                {{ savingStatus ? 'Guardando...' : 'Guardar' }}
              </button>
            </div>
          </div>

          <!-- Change Plan -->
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 space-y-4">
            <h2 class="font-display text-xl tracking-wide text-wc-text">Cambiar Plan</h2>
            <div class="flex items-end gap-3">
              <div class="flex-1">
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Plan</label>
                <select v-model="editPlan" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary py-2 pl-3 pr-8 text-sm text-wc-text focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500">
                  <option v-for="opt in planOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                </select>
              </div>
              <button @click="updatePlan" :disabled="savingPlan" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors disabled:opacity-50">
                {{ savingPlan ? 'Guardando...' : 'Guardar' }}
              </button>
            </div>
          </div>

          <!-- Summary Stats (2x2 grid) -->
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
            <h2 class="font-display text-xl tracking-wide text-wc-text mb-4">Resumen</h2>
            <div class="grid grid-cols-2 gap-4">
              <div class="rounded-lg bg-wc-bg-secondary p-3 text-center">
                <p class="font-data text-2xl font-bold text-wc-text">{{ client.stats?.checkins_count ?? 0 }}</p>
                <p class="text-xs text-wc-text-tertiary">Check-ins</p>
              </div>
              <div class="rounded-lg bg-wc-bg-secondary p-3 text-center">
                <p class="font-data text-2xl font-bold text-wc-text">{{ client.stats?.approved_payments ?? 0 }}</p>
                <p class="text-xs text-wc-text-tertiary">Pagos aprobados</p>
              </div>
              <div class="rounded-lg bg-wc-bg-secondary p-3 text-center">
                <p class="font-data text-2xl font-bold text-wc-text">{{ client.stats?.active_plans ?? 0 }}</p>
                <p class="text-xs text-wc-text-tertiary">Planes activos</p>
              </div>
              <div class="rounded-lg bg-wc-bg-secondary p-3 text-center">
                <p class="font-data text-2xl font-bold text-wc-text">{{ client.stats?.progress_photos ?? 0 }}</p>
                <p class="text-xs text-wc-text-tertiary">Fotos progreso</p>
              </div>
            </div>
          </div>

        </div>
      </div>

      <!-- ============== TAB: PLANS ============== -->
      <div v-else-if="activeTab === 'plans'" class="rounded-xl border border-wc-border bg-wc-bg-tertiary overflow-hidden">
        <div class="border-b border-wc-border bg-wc-bg-secondary px-5 py-3 flex items-center justify-between">
          <h2 class="font-display text-xl tracking-wide text-wc-text">Planes Asignados</h2>
          <span class="font-data text-sm text-wc-text-tertiary">{{ plans.length }} total</span>
        </div>

        <div v-if="!plans.length" class="px-5 py-12 text-center">
          <svg class="mx-auto h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
          </svg>
          <p class="mt-2 text-sm text-wc-text-tertiary">No hay planes asignados</p>
          <button @click="openCoachModal" class="mt-3 text-sm font-medium text-red-500 hover:text-red-400 transition-colors">Asignar coach</button>
        </div>

        <div v-else class="divide-y divide-wc-border">
          <div v-for="plan in plans" :key="plan.id" class="px-5 py-4 flex items-start justify-between gap-4">
            <div class="min-w-0 flex-1">
              <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-wc-text capitalize">{{ plan.plan_type }}</span>
                <span v-if="plan.active" class="inline-flex rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold text-emerald-500">Activo</span>
                <span v-else class="inline-flex rounded-full bg-zinc-500/10 px-2 py-0.5 text-[10px] font-semibold text-zinc-400">Inactivo</span>
                <span class="font-data text-xs text-wc-text-tertiary">v{{ plan.version }}</span>
              </div>
              <div class="mt-1 flex items-center gap-3 text-xs text-wc-text-tertiary">
                <span>Creado: {{ plan.created_at || '-' }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ============== TAB: PLAN (legacy — kept for backward compat) ============== -->
      <div v-else-if="activeTab === 'plan'" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
        <div v-if="client.planDetails" class="space-y-4">
          <div class="grid gap-4 sm:grid-cols-2">
            <div>
              <p class="text-xs font-medium text-wc-text-tertiary">Plan actual</p>
              <p class="mt-1 text-sm font-medium text-wc-text capitalize">{{ client.planDetails.name || client.plan }}</p>
            </div>
            <div>
              <p class="text-xs font-medium text-wc-text-tertiary">Fecha de inicio</p>
              <p class="mt-1 text-sm text-wc-text">{{ client.planDetails.startDate || '-' }}</p>
            </div>
            <div>
              <p class="text-xs font-medium text-wc-text-tertiary">Semana actual</p>
              <p class="mt-1 text-sm text-wc-text">{{ client.planDetails.currentWeek || '-' }}</p>
            </div>
            <div>
              <p class="text-xs font-medium text-wc-text-tertiary">Total semanas</p>
              <p class="mt-1 text-sm text-wc-text">{{ client.planDetails.totalWeeks || '-' }}</p>
            </div>
          </div>
        </div>
        <div v-else class="py-8 text-center">
          <p class="text-sm text-wc-text-tertiary">Sin plan asignado</p>
        </div>
      </div>

      <!-- ============== TAB: CHECKINS ============== -->
      <div v-else-if="activeTab === 'checkins'" class="rounded-xl border border-wc-border bg-wc-bg-tertiary overflow-hidden">
        <div class="border-b border-wc-border bg-wc-bg-secondary px-5 py-3 flex items-center justify-between">
          <h2 class="font-display text-xl tracking-wide text-wc-text">Check-ins</h2>
          <span class="font-data text-sm text-wc-text-tertiary">{{ client.checkins?.length ?? 0 }} recientes</span>
        </div>

        <div v-if="!client.checkins || client.checkins.length === 0" class="px-5 py-12 text-center">
          <svg class="mx-auto h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
          </svg>
          <p class="mt-2 text-sm text-wc-text-tertiary">No hay check-ins registrados</p>
        </div>

        <div v-else class="divide-y divide-wc-border">
          <div v-for="(checkin, idx) in client.checkins" :key="idx" class="px-5 py-3">
            <div class="flex items-center justify-between">
              <p class="font-data text-sm font-medium text-wc-text">{{ checkin.date }}</p>
              <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold" :class="checkin.reviewed ? 'bg-emerald-500/10 text-emerald-500' : 'bg-amber-500/10 text-amber-500'">
                {{ checkin.reviewed ? 'Respondido' : 'Pendiente' }}
              </span>
            </div>
            <p v-if="checkin.note" class="mt-1 text-xs text-wc-text-tertiary">{{ checkin.note }}</p>
          </div>
        </div>
      </div>

      <!-- ============== TAB: PAYMENTS ============== -->
      <div v-else-if="activeTab === 'payments'" class="rounded-xl border border-wc-border bg-wc-bg-tertiary overflow-hidden">
        <div class="border-b border-wc-border bg-wc-bg-secondary px-5 py-3 flex items-center justify-between">
          <h2 class="font-display text-xl tracking-wide text-wc-text">Pagos</h2>
          <span class="font-data text-sm text-wc-text-tertiary">{{ client.payments?.length ?? 0 }} recientes</span>
        </div>

        <div v-if="!client.payments || client.payments.length === 0" class="px-5 py-12 text-center">
          <svg class="mx-auto h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
          </svg>
          <p class="mt-2 text-sm text-wc-text-tertiary">No hay pagos registrados</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-wc-border bg-wc-bg-secondary">
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Fecha</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Plan</th>
                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Monto</th>
                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Estado</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-wc-border">
              <tr v-for="(payment, idx) in client.payments" :key="idx" class="hover:bg-wc-bg-secondary/50 transition-colors">
                <td class="px-4 py-3 font-data text-wc-text-secondary">{{ payment.date || '-' }}</td>
                <td class="px-4 py-3 text-sm text-wc-text capitalize">{{ payment.description || '-' }}</td>
                <td class="px-4 py-3 text-right font-data font-semibold text-wc-text">
                  ${{ payment.amount ? Number(payment.amount).toLocaleString('es-CO') : '0' }}
                  <span class="text-xs text-wc-text-tertiary">{{ payment.currency || 'COP' }}</span>
                </td>
                <td class="px-4 py-3 text-center">
                  <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold" :class="getPaymentStatusColor(payment.status)">
                    {{ payment.status || '-' }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ============== TAB: METRICS ============== -->
      <div v-else-if="activeTab === 'metrics'" class="space-y-6">
        <!-- Workout metrics -->
        <div v-if="client.metrics" class="grid gap-4 sm:grid-cols-3">
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
            <p class="text-xs font-medium text-wc-text-tertiary">Entrenamientos</p>
            <p class="mt-1 font-data text-2xl font-bold text-wc-text">{{ client.metrics.totalWorkouts || 0 }}</p>
          </div>
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
            <p class="text-xs font-medium text-wc-text-tertiary">Adherencia</p>
            <p class="mt-1 font-data text-2xl font-bold text-wc-text">{{ client.metrics.adherence || 0 }}%</p>
          </div>
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
            <p class="text-xs font-medium text-wc-text-tertiary">Racha actual</p>
            <p class="mt-1 font-data text-2xl font-bold text-wc-text">{{ client.metrics.streak || 0 }} dias</p>
          </div>
        </div>
        <div v-else class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 text-center">
          <p class="text-sm text-wc-text-tertiary">Sin metricas disponibles</p>
        </div>
      </div>

      <!-- ============== TAB: ACTIVITY ============== -->
      <div v-else-if="activeTab === 'activity'" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
        <div v-if="client.activity && client.activity.length" class="divide-y divide-wc-border">
          <div v-for="(entry, idx) in client.activity" :key="idx" class="flex items-center gap-3 py-3">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-wc-bg-secondary">
              <svg class="h-4 w-4 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
              </svg>
            </div>
            <div class="min-w-0 flex-1">
              <p class="text-sm text-wc-text truncate">{{ entry.description }}</p>
              <p class="text-xs text-wc-text-tertiary">{{ entry.time }}</p>
            </div>
          </div>
        </div>
        <div v-else class="py-8 text-center">
          <p class="text-sm text-wc-text-tertiary">Sin actividad registrada</p>
        </div>
      </div>

      <!-- ============== ASSIGN COACH MODAL ============== -->
      <Transition name="fade">
        <div v-if="showCoachModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <!-- Backdrop -->
          <div class="absolute inset-0 bg-black/60" @click="showCoachModal = false"></div>

          <!-- Modal -->
          <Transition name="scale">
            <div v-if="showCoachModal" class="relative w-full max-w-md rounded-xl border border-wc-border bg-wc-bg-secondary p-6 shadow-xl">
              <div class="flex items-center justify-between mb-5">
                <h3 class="font-display text-2xl tracking-wide text-wc-text">Asignar Coach</h3>
                <button @click="showCoachModal = false" class="text-wc-text-tertiary hover:text-wc-text transition-colors">
                  <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>

              <div class="space-y-4">
                <!-- Coach select -->
                <div>
                  <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Coach</label>
                  <select v-model="selectedCoachId" class="w-full rounded-lg border border-wc-border bg-wc-bg py-2 pl-3 pr-8 text-sm text-wc-text focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500">
                    <option :value="0">Seleccionar coach...</option>
                    <option v-for="coach in coaches" :key="coach.id" :value="coach.id">{{ coach.name }}</option>
                  </select>
                </div>

                <!-- Plan type select -->
                <div>
                  <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Tipo de Plan</label>
                  <select v-model="assignPlanType" class="w-full rounded-lg border border-wc-border bg-wc-bg py-2 pl-3 pr-8 text-sm text-wc-text focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500">
                    <option value="entrenamiento">Entrenamiento</option>
                    <option value="nutricion">Nutricion</option>
                    <option value="habitos">Habitos</option>
                  </select>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end gap-3 pt-2">
                  <button @click="showCoachModal = false" class="rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2 text-sm font-medium text-wc-text hover:bg-wc-bg-secondary transition-colors">
                    Cancelar
                  </button>
                  <button @click="assignCoach" :disabled="assigningCoach || !selectedCoachId" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors disabled:opacity-50">
                    {{ assigningCoach ? 'Asignando...' : 'Asignar Coach' }}
                  </button>
                </div>
              </div>
            </div>
          </Transition>
        </div>
      </Transition>

    </div>
  </AdminLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.scale-enter-active, .scale-leave-active { transition: opacity 0.2s ease, transform 0.2s ease; }
.scale-enter-from, .scale-leave-to { opacity: 0; transform: scale(0.95); }
</style>
