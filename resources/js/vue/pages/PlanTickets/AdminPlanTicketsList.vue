<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useApi } from '../../composables/useApi';
import AdminLayout from '../../layouts/AdminLayout.vue';

const api = useApi();
const router = useRouter();

const loading = ref(true);
const tickets = ref([]);
const counts = ref({ pendiente: 0, en_revision: 0, completado: 0, rechazado: 0, total: 0 });
const coaches = ref([]);

const filters = ref({
  status: '',
  coach_id: '',
  plan_type: '',
  search: '',
});

const PLAN_TYPE_META = {
  esencial: { label: 'Esencial', bg: 'bg-blue-500/10', text: 'text-blue-500' },
  metodo: { label: 'Metodo', bg: 'bg-orange-500/10', text: 'text-orange-500' },
  elite: { label: 'Elite', bg: 'bg-wc-accent/10', text: 'text-wc-accent' },
};
const STATUS_META = {
  borrador: { label: 'Borrador', bg: 'bg-wc-bg-secondary', text: 'text-wc-text-tertiary' },
  pendiente: { label: 'Pendiente', bg: 'bg-yellow-500/10', text: 'text-yellow-500' },
  en_revision: { label: 'En revision', bg: 'bg-blue-500/10', text: 'text-blue-500' },
  completado: { label: 'Completado', bg: 'bg-emerald-500/10', text: 'text-emerald-500' },
  rechazado: { label: 'Rechazado', bg: 'bg-red-500/10', text: 'text-red-400' },
};

const COUNT_CARDS = [
  { key: 'pendiente', label: 'Pendientes', text: 'text-yellow-500' },
  { key: 'en_revision', label: 'En revision', text: 'text-blue-500' },
  { key: 'completado', label: 'Completados', text: 'text-emerald-500' },
  { key: 'rechazado', label: 'Rechazados', text: 'text-red-400' },
  { key: 'total', label: 'Total', text: 'text-wc-text' },
];

async function fetchTickets() {
  loading.value = true;
  try {
    const params = {};
    if (filters.value.status) params.status = filters.value.status;
    if (filters.value.coach_id) params.coach_id = filters.value.coach_id;
    if (filters.value.plan_type) params.plan_type = filters.value.plan_type;
    if (filters.value.search) params.search = filters.value.search;
    const { data } = await api.get('/api/v/admin/plan-tickets', { params });
    tickets.value = data.tickets || [];
    counts.value = data.counts || counts.value;
  } catch (e) {
    tickets.value = [];
  } finally {
    loading.value = false;
  }
}

async function fetchCoaches() {
  try {
    const { data } = await api.get('/api/v/admin/coaches');
    coaches.value = data.coaches || data.items || [];
  } catch (e) {
    coaches.value = [];
  }
}

// Debounced search
let searchTimer = null;
watch(() => filters.value.search, () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(fetchTickets, 300);
});
watch(() => [filters.value.status, filters.value.coach_id, filters.value.plan_type], fetchTickets);

function filterByStatus(status) {
  filters.value.status = filters.value.status === status ? '' : status;
}

function planTypeMeta(t) { return PLAN_TYPE_META[t] || PLAN_TYPE_META.esencial; }
function statusMeta(s) { return STATUS_META[s] || STATUS_META.borrador; }

function formatDate(d) {
  if (!d) return '-';
  try {
    return new Date(d).toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
  } catch { return d; }
}

function goToDetail(id) {
  router.push(`/admin/plan-tickets/${id}`);
}

function humanLabel(s) {
  if (!s) return '';
  return String(s).replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
}

onMounted(() => {
  fetchCoaches();
  fetchTickets();
});
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">

      <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">TICKETS DE PLAN DE COACHES</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">Briefs estructurados enviados por el equipo de coaches.</p>
      </div>

      <!-- Count cards -->
      <div class="grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-5">
        <button
          v-for="c in COUNT_CARDS"
          :key="c.key"
          @click="filterByStatus(c.key === 'total' ? '' : c.key)"
          class="rounded-xl border bg-wc-bg-tertiary p-4 text-left transition"
          :class="(c.key === 'total' ? !filters.status : filters.status === c.key) ? 'border-wc-accent' : 'border-wc-border hover:border-wc-accent/40'"
        >
          <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">{{ c.label }}</p>
          <p class="mt-1 font-data text-2xl font-bold" :class="c.text">{{ counts[c.key] ?? 0 }}</p>
        </button>
      </div>

      <!-- Filters -->
      <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
        <div class="relative sm:col-span-2">
          <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
          </svg>
          <input
            v-model="filters.search"
            type="text"
            placeholder="Buscar por cliente o coach..."
            class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary py-2 pl-10 pr-4 text-sm text-wc-text placeholder:text-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
          />
        </div>
        <select v-model="filters.coach_id" class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
          <option value="">Todos los coaches</option>
          <option v-for="c in coaches" :key="c.id" :value="c.id">{{ c.name || c.full_name || c.email }}</option>
        </select>
        <select v-model="filters.plan_type" class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
          <option value="">Todos los planes</option>
          <option value="esencial">Esencial</option>
          <option value="metodo">Metodo</option>
          <option value="elite">Elite</option>
        </select>
      </div>

      <!-- Loading -->
      <template v-if="loading">
        <div v-for="n in 4" :key="n" class="animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary h-20"></div>
      </template>

      <!-- Empty -->
      <div v-else-if="tickets.length === 0" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center">
        <p class="text-sm text-wc-text-secondary">No hay tickets con estos filtros.</p>
      </div>

      <!-- Table / list -->
      <div v-else class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-tertiary">
        <table class="w-full">
          <thead class="border-b border-wc-border bg-wc-bg-secondary text-left text-xs uppercase tracking-wider text-wc-text-tertiary">
            <tr>
              <th class="px-4 py-3 font-semibold">Cliente</th>
              <th class="px-4 py-3 font-semibold hidden sm:table-cell">Coach</th>
              <th class="px-4 py-3 font-semibold">Plan</th>
              <th class="px-4 py-3 font-semibold">Status</th>
              <th class="px-4 py-3 font-semibold hidden md:table-cell">Enviado</th>
              <th class="px-4 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-wc-border">
            <tr
              v-for="t in tickets"
              :key="t.id"
              class="hover:bg-wc-bg-secondary/40 transition cursor-pointer"
              @click="goToDetail(t.id)"
            >
              <td class="px-4 py-3">
                <p class="text-sm font-medium text-wc-text">{{ t.client_name || '-' }}</p>
              </td>
              <td class="px-4 py-3 hidden sm:table-cell">
                <p class="text-sm text-wc-text-secondary">{{ t.coach_name || '-' }}</p>
              </td>
              <td class="px-4 py-3">
                <span class="rounded-full px-2.5 py-0.5 text-xs font-medium" :class="[planTypeMeta(t.plan_type).bg, planTypeMeta(t.plan_type).text]">
                  {{ planTypeMeta(t.plan_type).label }}
                </span>
              </td>
              <td class="px-4 py-3">
                <span class="rounded-full px-2.5 py-0.5 text-xs font-medium" :class="[statusMeta(t.status).bg, statusMeta(t.status).text]">
                  {{ statusMeta(t.status).label }}
                </span>
              </td>
              <td class="px-4 py-3 hidden md:table-cell text-xs text-wc-text-tertiary">
                {{ formatDate(t.submitted_at) }}
              </td>
              <td class="px-4 py-3 text-right">
                <span class="text-xs font-semibold text-wc-accent">Ver →</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AdminLayout>
</template>
