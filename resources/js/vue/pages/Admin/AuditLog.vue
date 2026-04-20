<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import AdminLayout from '../../layouts/AdminLayout.vue';

const logs = ref([]);
const loading = ref(false);
const error = ref('');
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });

const filters = ref({
  action: '',
  actor_type: '',
  actor_id: '',
  from: '',
  to: '',
  page: 1,
});

const token = computed(() => localStorage.getItem('wc_token') || '');

async function load() {
  loading.value = true;
  error.value = '';
  try {
    const params = { ...filters.value };
    Object.keys(params).forEach((k) => {
      if (params[k] === '' || params[k] === null) delete params[k];
    });
    const resp = await axios.get('/api/v/admin/audit-logs', {
      headers: { Authorization: `Bearer ${token.value}` },
      params,
    });
    logs.value = resp.data.logs || [];
    pagination.value = resp.data.pagination || { current_page: 1, last_page: 1, total: 0 };
  } catch (e) {
    error.value = e?.response?.data?.message || 'No se pudo cargar el audit log.';
  } finally {
    loading.value = false;
  }
}

function applyFilters() {
  filters.value.page = 1;
  load();
}

function resetFilters() {
  filters.value = { action: '', actor_type: '', actor_id: '', from: '', to: '', page: 1 };
  load();
}

function nextPage() {
  if (pagination.value.current_page < pagination.value.last_page) {
    filters.value.page = pagination.value.current_page + 1;
    load();
  }
}
function prevPage() {
  if (pagination.value.current_page > 1) {
    filters.value.page = pagination.value.current_page - 1;
    load();
  }
}

function formatDate(iso) {
  if (!iso) return '';
  const d = new Date(iso);
  return d.toLocaleString('es-MX', { dateStyle: 'short', timeStyle: 'short' });
}

function formatDiff(diff) {
  if (!diff) return '';
  try {
    return JSON.stringify(typeof diff === 'string' ? JSON.parse(diff) : diff, null, 0);
  } catch {
    return String(diff);
  }
}

onMounted(load);
</script>

<template>
  <AdminLayout>
    <div class="p-4 md:p-6 space-y-6">
      <header class="space-y-1">
        <h1 class="text-2xl md:text-3xl font-display text-wc-text">Audit Log</h1>
        <p class="text-sm text-wc-text-secondary">
          Registro inmutable de acciones criticas (solo superadmin).
        </p>
      </header>

      <section class="rounded-xl border border-wc-border bg-wc-bg-secondary p-4 space-y-3">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
          <input
            v-model="filters.action"
            type="text"
            placeholder="action (ej. coach.create)"
            class="px-3 py-2 rounded-lg bg-wc-bg border border-wc-border text-sm text-wc-text"
          />
          <select
            v-model="filters.actor_type"
            class="px-3 py-2 rounded-lg bg-wc-bg border border-wc-border text-sm text-wc-text"
          >
            <option value="">actor_type (todos)</option>
            <option value="admin">admin</option>
            <option value="client">client</option>
          </select>
          <input
            v-model="filters.actor_id"
            type="number"
            placeholder="actor_id"
            class="px-3 py-2 rounded-lg bg-wc-bg border border-wc-border text-sm text-wc-text"
          />
          <input
            v-model="filters.from"
            type="date"
            class="px-3 py-2 rounded-lg bg-wc-bg border border-wc-border text-sm text-wc-text"
          />
          <input
            v-model="filters.to"
            type="date"
            class="px-3 py-2 rounded-lg bg-wc-bg border border-wc-border text-sm text-wc-text"
          />
        </div>
        <div class="flex gap-2">
          <button
            type="button"
            class="px-4 py-2 rounded-lg bg-wc-accent text-white text-sm font-semibold hover:opacity-90"
            @click="applyFilters"
          >
            Filtrar
          </button>
          <button
            type="button"
            class="px-4 py-2 rounded-lg bg-wc-bg border border-wc-border text-wc-text text-sm"
            @click="resetFilters"
          >
            Reset
          </button>
        </div>
      </section>

      <section v-if="error" class="p-3 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 text-sm">
        {{ error }}
      </section>

      <section class="rounded-xl border border-wc-border bg-wc-bg-secondary overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-wc-bg-tertiary text-wc-text-secondary">
              <tr>
                <th class="px-3 py-2 text-left font-medium">Fecha</th>
                <th class="px-3 py-2 text-left font-medium">Actor</th>
                <th class="px-3 py-2 text-left font-medium">Accion</th>
                <th class="px-3 py-2 text-left font-medium">Target</th>
                <th class="px-3 py-2 text-left font-medium">IP</th>
                <th class="px-3 py-2 text-left font-medium">Diff</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="6" class="px-3 py-4 text-center text-wc-text-secondary">Cargando...</td>
              </tr>
              <tr v-else-if="!logs.length">
                <td colspan="6" class="px-3 py-4 text-center text-wc-text-secondary">Sin registros.</td>
              </tr>
              <tr
                v-for="log in logs"
                :key="log.id"
                class="border-t border-wc-border hover:bg-wc-bg-tertiary/50"
              >
                <td class="px-3 py-2 text-wc-text whitespace-nowrap">{{ formatDate(log.created_at) }}</td>
                <td class="px-3 py-2 text-wc-text">
                  <span class="text-wc-text-secondary">{{ log.actor_type }}#{{ log.actor_id }}</span>
                  <div class="text-xs">{{ log.actor_name }}</div>
                </td>
                <td class="px-3 py-2 text-wc-accent font-mono text-xs">{{ log.action }}</td>
                <td class="px-3 py-2 text-wc-text">
                  <span class="text-wc-text-secondary">{{ log.target_type }}#{{ log.target_id }}</span>
                  <div class="text-xs">{{ log.target_label }}</div>
                </td>
                <td class="px-3 py-2 text-wc-text-secondary font-mono text-xs">{{ log.ip }}</td>
                <td class="px-3 py-2 text-wc-text-secondary font-mono text-xs max-w-sm truncate">
                  {{ formatDiff(log.diff) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="flex items-center justify-between px-3 py-2 border-t border-wc-border text-sm">
          <span class="text-wc-text-secondary">
            Pagina {{ pagination.current_page }} / {{ pagination.last_page }} ({{ pagination.total }} total)
          </span>
          <div class="flex gap-2">
            <button
              type="button"
              class="px-3 py-1 rounded bg-wc-bg border border-wc-border disabled:opacity-40"
              :disabled="pagination.current_page <= 1"
              @click="prevPage"
            >
              Anterior
            </button>
            <button
              type="button"
              class="px-3 py-1 rounded bg-wc-bg border border-wc-border disabled:opacity-40"
              :disabled="pagination.current_page >= pagination.last_page"
              @click="nextPage"
            >
              Siguiente
            </button>
          </div>
        </div>
      </section>
    </div>
  </AdminLayout>
</template>
