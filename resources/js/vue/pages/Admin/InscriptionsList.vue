<script setup>
import { ref, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import AdminLayout from '../../layouts/AdminLayout.vue';

const api = useApi();

const loading = ref(true);
const error = ref(null);
const inscriptions = ref([]);
const filter = ref('all');
const expandedId = ref(null);
const actionLoading = ref(null);

const filterOptions = [
    { value: 'all', label: 'Todas' },
    { value: 'pending', label: 'Pendientes' },
    { value: 'approved', label: 'Aprobadas' },
    { value: 'contacted', label: 'Contactadas' },
    { value: 'rejected', label: 'Rechazadas' },
];

async function fetchInscriptions() {
    loading.value = true;
    error.value = null;
    try {
        const response = await api.get('/api/v/admin/inscriptions', {
            params: { status: filter.value !== 'all' ? filter.value : undefined },
        });
        inscriptions.value = response.data.inscriptions || response.data.data || [];
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al cargar inscripciones';
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

function toggleExpand(id) {
    expandedId.value = expandedId.value === id ? null : id;
}

function applyFilter(val) {
    filter.value = val;
    fetchInscriptions();
}

function getStatusColor(status) {
    const map = {
        pending: 'bg-amber-500/10 text-amber-500',
        approved: 'bg-emerald-500/10 text-emerald-500',
        contacted: 'bg-sky-500/10 text-sky-500',
        rejected: 'bg-red-500/10 text-red-500',
    };
    return map[status] || 'bg-gray-500/10 text-gray-400';
}

function getStatusLabel(status) {
    const map = { pending: 'Pendiente', approved: 'Aprobada', contacted: 'Contactada', rejected: 'Rechazada' };
    return map[status] || status;
}

onMounted(() => {
    fetchInscriptions();
});
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">

      <!-- Header -->
      <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text">Inscripciones</h1>
        <p class="mt-1 text-sm text-wc-text-tertiary">Gestiona las solicitudes de inscripcion</p>
      </div>

      <!-- Filters -->
      <div class="flex flex-wrap gap-2">
        <button
          v-for="opt in filterOptions"
          :key="opt.value"
          @click="applyFilter(opt.value)"
          :class="[
            'rounded-lg px-3 py-1.5 text-xs font-medium transition-colors',
            filter === opt.value ? 'bg-wc-accent text-white' : 'bg-wc-bg-tertiary text-wc-text-secondary hover:bg-wc-bg-secondary'
          ]"
        >
          {{ opt.label }}
        </button>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="space-y-3">
        <div v-for="i in 6" :key="i" class="h-20 animate-pulse rounded-xl bg-wc-bg-tertiary"></div>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-6 text-center">
        <p class="text-sm text-wc-text">{{ error }}</p>
        <button @click="fetchInscriptions" class="mt-3 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white">Reintentar</button>
      </div>

      <!-- Inscriptions List -->
      <div v-else-if="inscriptions.length" class="space-y-3">
        <div
          v-for="insc in inscriptions"
          :key="insc.id"
          class="rounded-xl border border-wc-border bg-wc-bg-tertiary overflow-hidden transition-colors"
        >
          <!-- Row -->
          <div class="flex items-center gap-4 p-4 cursor-pointer hover:bg-wc-bg-secondary transition-colors" @click="toggleExpand(insc.id)">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent/10">
              <span class="text-sm font-semibold text-wc-accent">{{ (insc.name || 'U').charAt(0).toUpperCase() }}</span>
            </div>
            <div class="min-w-0 flex-1">
              <p class="text-sm font-medium text-wc-text truncate">{{ insc.name }}</p>
              <p class="text-xs text-wc-text-tertiary truncate">{{ insc.email }} &middot; {{ insc.phone || '' }}</p>
            </div>
            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium capitalize" :class="getStatusColor(insc.status)">{{ getStatusLabel(insc.status) }}</span>
            <span class="text-xs text-wc-text-tertiary hidden sm:inline">{{ insc.date || insc.created_at }}</span>
            <svg :class="expandedId === insc.id ? 'rotate-180' : ''" class="h-4 w-4 text-wc-text-tertiary transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
          </div>

          <!-- Expanded Detail -->
          <div v-if="expandedId === insc.id" class="border-t border-wc-border bg-wc-bg-secondary p-4">
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 mb-4">
              <div>
                <p class="text-xs font-medium text-wc-text-tertiary">Plan solicitado</p>
                <p class="mt-0.5 text-sm text-wc-text capitalize">{{ insc.plan || '-' }}</p>
              </div>
              <div>
                <p class="text-xs font-medium text-wc-text-tertiary">Objetivo</p>
                <p class="mt-0.5 text-sm text-wc-text">{{ insc.goal || '-' }}</p>
              </div>
              <div>
                <p class="text-xs font-medium text-wc-text-tertiary">Experiencia</p>
                <p class="mt-0.5 text-sm text-wc-text capitalize">{{ insc.experience || '-' }}</p>
              </div>
            </div>
            <div v-if="insc.notes" class="mb-4">
              <p class="text-xs font-medium text-wc-text-tertiary">Notas</p>
              <p class="mt-0.5 text-sm text-wc-text">{{ insc.notes }}</p>
            </div>
            <!-- Actions -->
            <div class="flex flex-wrap gap-2">
              <button
                v-if="insc.status !== 'approved'"
                @click.stop="updateStatus(insc.id, 'approved')"
                :disabled="actionLoading === insc.id"
                class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-emerald-700 transition-colors disabled:opacity-50"
              >
                Aprobar
              </button>
              <button
                v-if="insc.status !== 'contacted'"
                @click.stop="updateStatus(insc.id, 'contacted')"
                :disabled="actionLoading === insc.id"
                class="rounded-lg bg-sky-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-sky-700 transition-colors disabled:opacity-50"
              >
                Contactar
              </button>
              <button
                v-if="insc.status !== 'rejected'"
                @click.stop="updateStatus(insc.id, 'rejected')"
                :disabled="actionLoading === insc.id"
                class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-700 transition-colors disabled:opacity-50"
              >
                Rechazar
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty -->
      <div v-else class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center">
        <p class="text-sm text-wc-text-tertiary">Sin inscripciones encontradas</p>
      </div>

    </div>
  </AdminLayout>
</template>
