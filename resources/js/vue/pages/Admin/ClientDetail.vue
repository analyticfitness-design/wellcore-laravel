<script setup>
import { ref, onMounted } from 'vue';
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
const activeTab = ref('info');
const editMode = ref(false);
const editForm = ref({});

const tabs = [
    { key: 'info', label: 'Info' },
    { key: 'plan', label: 'Plan' },
    { key: 'metrics', label: 'Metricas' },
    { key: 'checkins', label: 'Check-ins' },
    { key: 'payments', label: 'Pagos' },
    { key: 'activity', label: 'Actividad' },
];

async function fetchClient() {
    loading.value = true;
    error.value = null;
    try {
        const response = await api.get(`/api/v/admin/clients/${route.params.id}`);
        client.value = response.data.client || response.data;
        editForm.value = { ...(response.data.client || response.data) };
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
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al guardar';
    } finally {
        saving.value = false;
    }
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

onMounted(() => {
    fetchClient();
});
</script>

<template>
  <AdminLayout>
    <!-- Loading -->
    <div v-if="loading" class="space-y-6">
      <div class="h-8 w-48 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
      <div class="h-64 animate-pulse rounded-xl bg-wc-bg-tertiary"></div>
    </div>

    <!-- Error -->
    <div v-else-if="error && !client" class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-6 text-center">
      <p class="text-sm text-wc-text">{{ error }}</p>
      <div class="mt-4 flex justify-center gap-3">
        <button @click="fetchClient" class="rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white">Reintentar</button>
        <button @click="router.push('/admin/clients')" class="rounded-lg border border-wc-border px-4 py-2 text-sm font-medium text-wc-text">Volver</button>
      </div>
    </div>

    <!-- Client Detail -->
    <div v-else-if="client" class="space-y-6">

      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
          <button @click="router.push('/admin/clients')" class="flex h-9 w-9 items-center justify-center rounded-lg border border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:text-wc-text transition-colors" aria-label="Volver">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
            </svg>
          </button>
          <div>
            <div class="flex items-center gap-3">
              <div class="flex h-12 w-12 items-center justify-center rounded-full bg-wc-accent/10">
                <span class="text-lg font-semibold text-wc-accent">{{ (client.name || 'U').charAt(0).toUpperCase() }}</span>
              </div>
              <div>
                <h1 class="font-display text-2xl tracking-wide text-wc-text sm:text-3xl">{{ client.name }}</h1>
                <p class="text-sm text-wc-text-tertiary">{{ client.email }}</p>
              </div>
            </div>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <span class="rounded-full px-3 py-1 text-xs font-medium capitalize" :class="getStatusColor(client.status)">{{ client.status }}</span>
          <span v-if="client.plan" class="rounded-full bg-wc-bg-tertiary px-3 py-1 text-xs font-medium text-wc-text capitalize">{{ client.plan }}</span>
          <button
            v-if="!editMode"
            @click="startEdit"
            class="rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors"
          >
            Editar
          </button>
        </div>
      </div>

      <!-- Tabs -->
      <div class="border-b border-wc-border">
        <div class="flex gap-1 overflow-x-auto">
          <button
            v-for="tab in tabs"
            :key="tab.key"
            @click="activeTab = tab.key"
            :class="[
              'whitespace-nowrap border-b-2 px-4 py-3 text-sm font-medium transition-colors',
              activeTab === tab.key
                ? 'border-wc-accent text-wc-accent'
                : 'border-transparent text-wc-text-tertiary hover:text-wc-text'
            ]"
          >
            {{ tab.label }}
          </button>
        </div>
      </div>

      <!-- Tab: Info -->
      <div v-if="activeTab === 'info'" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
        <div v-if="editMode" class="space-y-4">
          <div class="grid gap-4 sm:grid-cols-2">
            <div>
              <label class="mb-1 block text-xs font-medium text-wc-text-tertiary">Nombre</label>
              <input v-model="editForm.name" type="text" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20" />
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-wc-text-tertiary">Email</label>
              <input v-model="editForm.email" type="email" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20" />
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-wc-text-tertiary">Telefono</label>
              <input v-model="editForm.phone" type="tel" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20" />
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-wc-text-tertiary">Estado</label>
              <select v-model="editForm.status" class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20">
                <option value="active">Activo</option>
                <option value="inactive">Inactivo</option>
                <option value="pending">Pendiente</option>
                <option value="suspended">Suspendido</option>
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
            <p class="text-xs font-medium text-wc-text-tertiary">Nombre</p>
            <p class="mt-1 text-sm text-wc-text">{{ client.name || '-' }}</p>
          </div>
          <div>
            <p class="text-xs font-medium text-wc-text-tertiary">Email</p>
            <p class="mt-1 text-sm text-wc-text">{{ client.email || '-' }}</p>
          </div>
          <div>
            <p class="text-xs font-medium text-wc-text-tertiary">Telefono</p>
            <p class="mt-1 text-sm text-wc-text">{{ client.phone || '-' }}</p>
          </div>
          <div>
            <p class="text-xs font-medium text-wc-text-tertiary">Pais</p>
            <p class="mt-1 text-sm text-wc-text">{{ client.country || '-' }}</p>
          </div>
          <div>
            <p class="text-xs font-medium text-wc-text-tertiary">Fecha de registro</p>
            <p class="mt-1 text-sm text-wc-text">{{ client.registeredAt || '-' }}</p>
          </div>
          <div>
            <p class="text-xs font-medium text-wc-text-tertiary">Coach asignado</p>
            <p class="mt-1 text-sm text-wc-text">{{ client.coachName || 'Sin asignar' }}</p>
          </div>
        </div>
      </div>

      <!-- Tab: Plan -->
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

      <!-- Tab: Metrics -->
      <div v-else-if="activeTab === 'metrics'" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
        <div v-if="client.metrics" class="grid gap-4 sm:grid-cols-3">
          <div class="rounded-lg bg-wc-bg-secondary p-4">
            <p class="text-xs font-medium text-wc-text-tertiary">Entrenamientos</p>
            <p class="mt-1 font-data text-2xl font-bold text-wc-text">{{ client.metrics.totalWorkouts || 0 }}</p>
          </div>
          <div class="rounded-lg bg-wc-bg-secondary p-4">
            <p class="text-xs font-medium text-wc-text-tertiary">Adherencia</p>
            <p class="mt-1 font-data text-2xl font-bold text-wc-text">{{ client.metrics.adherence || 0 }}%</p>
          </div>
          <div class="rounded-lg bg-wc-bg-secondary p-4">
            <p class="text-xs font-medium text-wc-text-tertiary">Racha actual</p>
            <p class="mt-1 font-data text-2xl font-bold text-wc-text">{{ client.metrics.streak || 0 }} dias</p>
          </div>
        </div>
        <div v-else class="py-8 text-center">
          <p class="text-sm text-wc-text-tertiary">Sin metricas disponibles</p>
        </div>
      </div>

      <!-- Tab: Check-ins -->
      <div v-else-if="activeTab === 'checkins'" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
        <div v-if="client.checkins && client.checkins.length" class="divide-y divide-wc-border">
          <div v-for="(checkin, idx) in client.checkins" :key="idx" class="py-3">
            <div class="flex items-center justify-between">
              <p class="text-sm font-medium text-wc-text">{{ checkin.date }}</p>
              <span class="rounded-full px-2 py-0.5 text-xs font-medium" :class="checkin.reviewed ? 'bg-emerald-500/10 text-emerald-500' : 'bg-amber-500/10 text-amber-500'">
                {{ checkin.reviewed ? 'Revisado' : 'Pendiente' }}
              </span>
            </div>
            <p v-if="checkin.note" class="mt-1 text-xs text-wc-text-tertiary">{{ checkin.note }}</p>
          </div>
        </div>
        <div v-else class="py-8 text-center">
          <p class="text-sm text-wc-text-tertiary">Sin check-ins</p>
        </div>
      </div>

      <!-- Tab: Payments -->
      <div v-else-if="activeTab === 'payments'" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
        <div v-if="client.payments && client.payments.length" class="divide-y divide-wc-border">
          <div v-for="(payment, idx) in client.payments" :key="idx" class="flex items-center justify-between py-3">
            <div>
              <p class="text-sm font-medium text-wc-text">{{ payment.description }}</p>
              <p class="text-xs text-wc-text-tertiary">{{ payment.date }}</p>
            </div>
            <div class="text-right">
              <p class="text-sm font-data font-bold text-wc-text">${{ payment.amount?.toLocaleString('es-CO') }}</p>
              <span class="text-xs capitalize" :class="payment.status === 'paid' ? 'text-emerald-500' : 'text-amber-500'">{{ payment.status }}</span>
            </div>
          </div>
        </div>
        <div v-else class="py-8 text-center">
          <p class="text-sm text-wc-text-tertiary">Sin pagos registrados</p>
        </div>
      </div>

      <!-- Tab: Activity -->
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

    </div>
  </AdminLayout>
</template>
