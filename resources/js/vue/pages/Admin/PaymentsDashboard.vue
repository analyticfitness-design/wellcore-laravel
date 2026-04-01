<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import AdminLayout from '../../layouts/AdminLayout.vue';

const api = useApi();

const loading = ref(true);
const error = ref(null);
const stats = ref(null);
const payments = ref([]);
const pagination = ref(null);

// Filters
const statusFilter = ref('');
const dateFrom = ref('');
const dateTo = ref('');
const page = ref(1);

const STATUS_OPTIONS = [
  { value: '', label: 'Todos los estados' },
  { value: 'approved', label: 'Aprobado' },
  { value: 'pending', label: 'Pendiente' },
  { value: 'declined', label: 'Rechazado' },
  { value: 'voided', label: 'Anulado' },
  { value: 'error', label: 'Error' },
];

const hasActiveFilters = computed(() => {
  return statusFilter.value !== '' || dateFrom.value !== '' || dateTo.value !== '';
});

// Debounced filter watchers (300ms)
let debounceTimer = null;

watch([statusFilter, dateFrom, dateTo], () => {
  page.value = 1;
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(fetchPayments, 300);
});

async function fetchPayments() {
  loading.value = true;
  error.value = null;
  try {
    const params = { page: page.value };
    if (statusFilter.value !== '') params.status = statusFilter.value;
    if (dateFrom.value !== '') params.date_from = dateFrom.value;
    if (dateTo.value !== '') params.date_to = dateTo.value;

    const response = await api.get('/api/v/admin/payments', { params });
    stats.value = response.data.stats ?? null;
    payments.value = response.data.payments ?? [];
    pagination.value = response.data.pagination ?? null;
  } catch (err) {
    error.value = err.response?.data?.message || 'Error al cargar pagos';
    payments.value = [];
  } finally {
    loading.value = false;
  }
}

function clearFilters() {
  statusFilter.value = '';
  dateFrom.value = '';
  dateTo.value = '';
  page.value = 1;
  fetchPayments();
}

function goToPage(p) {
  if (p < 1 || (pagination.value && p > pagination.value.last_page)) return;
  page.value = p;
  fetchPayments();
}

// ─── Helpers ──────────────────────────────────────────────────────────

function formatCurrency(value) {
  if (!value && value !== 0) return '$0';
  if (typeof value === 'string') {
    // Already formatted from API (e.g. "1.250.000")
    return '$' + value;
  }
  return new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  }).format(value);
}

function getStatusColor(status) {
  const map = {
    approved: 'bg-emerald-500/10 text-emerald-500',
    pending: 'bg-amber-500/10 text-amber-500',
    declined: 'bg-red-500/10 text-red-500',
    voided: 'bg-zinc-500/10 text-zinc-400',
    error: 'bg-red-500/10 text-red-500',
  };
  return map[status] || 'bg-wc-bg-secondary text-wc-text-tertiary';
}

function getStatusLabel(status) {
  const map = {
    approved: 'Aprobado',
    pending: 'Pendiente',
    declined: 'Rechazado',
    voided: 'Anulado',
    error: 'Error',
  };
  return map[status] || status || '-';
}

function getPlanColor(plan) {
  if (!plan || plan === '-') return 'bg-wc-bg-secondary text-wc-text-tertiary';
  const lower = plan.toLowerCase();
  if (lower.includes('esencial')) return 'bg-sky-500/10 text-sky-500';
  if (lower.includes('metodo') || lower.includes('método')) return 'bg-violet-500/10 text-violet-500';
  if (lower.includes('elite')) return 'bg-amber-500/10 text-amber-500';
  if (lower.includes('rise')) return 'bg-emerald-500/10 text-emerald-500';
  if (lower.includes('presencial')) return 'bg-orange-500/10 text-orange-500';
  return 'bg-wc-bg-secondary text-wc-text-tertiary';
}

// ─── CSV Export (frontend-generated) ──────────────────────────────────

function exportCsv() {
  if (!payments.value.length) return;

  const headers = ['Cliente', 'Plan', 'Monto', 'Estado', 'Metodo de pago', 'Fecha'];
  const rows = payments.value.map(p => [
    p.buyer_name || p.client_name || '-',
    p.plan || '-',
    p.amount_fmt || p.amount || 0,
    getStatusLabel(p.status),
    p.payment_method || '-',
    p.created_at || '-',
  ]);

  const csvContent = [headers, ...rows]
    .map(row => row.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join(','))
    .join('\n');

  const blob = new Blob(['\uFEFF' + csvContent], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.download = `pagos_wellcore_${new Date().toISOString().slice(0, 10)}.csv`;
  link.click();
  URL.revokeObjectURL(url);
}

// ─── Pagination helpers ───────────────────────────────────────────────

const paginationPages = computed(() => {
  if (!pagination.value) return [];
  const total = pagination.value.last_page;
  const current = pagination.value.current_page;
  const pages = [];

  if (total <= 7) {
    for (let i = 1; i <= total; i++) pages.push(i);
    return pages;
  }

  pages.push(1);
  if (current > 3) pages.push('...');

  const start = Math.max(2, current - 1);
  const end = Math.min(total - 1, current + 1);
  for (let i = start; i <= end; i++) pages.push(i);

  if (current < total - 2) pages.push('...');
  pages.push(total);

  return pages;
});

// ─── Current month name ───────────────────────────────────────────────

const currentMonthName = new Date().toLocaleDateString('es-CO', { month: 'long' });

onMounted(fetchPayments);
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">

      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">PAGOS</h1>
          <p class="mt-1 text-sm text-wc-text-tertiary">Resumen financiero y listado de pagos</p>
        </div>
        <div class="flex items-center gap-2">
          <button
            @click="exportCsv"
            :disabled="!payments.length"
            class="inline-flex items-center gap-2 rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2 text-sm font-medium text-wc-text transition-colors hover:bg-wc-bg-secondary disabled:cursor-not-allowed disabled:opacity-40"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            Exportar CSV
          </button>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        <!-- Total revenue -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
          <div class="flex items-center justify-between">
            <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Ingresos totales</span>
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/10">
              <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
              </svg>
            </div>
          </div>
          <template v-if="loading && !stats">
            <div class="mt-3 h-8 w-32 animate-pulse rounded bg-wc-bg-secondary"></div>
          </template>
          <template v-else>
            <p class="mt-3 font-data text-3xl font-bold text-wc-text">${{ stats?.totalRevenue ?? '0' }}</p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">COP historico</p>
          </template>
        </div>

        <!-- This month -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
          <div class="flex items-center justify-between">
            <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Este mes</span>
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-500/10">
              <svg class="h-4 w-4 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
              </svg>
            </div>
          </div>
          <template v-if="loading && !stats">
            <div class="mt-3 h-8 w-28 animate-pulse rounded bg-wc-bg-secondary"></div>
          </template>
          <template v-else>
            <p class="mt-3 font-data text-3xl font-bold text-wc-text">${{ stats?.monthRevenue ?? '0' }}</p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary capitalize">COP {{ currentMonthName }}</p>
          </template>
        </div>

        <!-- Pending -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
          <div class="flex items-center justify-between">
            <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Pendientes</span>
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-500/10">
              <svg class="h-4 w-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
              </svg>
            </div>
          </div>
          <template v-if="loading && !stats">
            <div class="mt-3 h-8 w-16 animate-pulse rounded bg-wc-bg-secondary"></div>
          </template>
          <template v-else>
            <p class="mt-3 font-data text-3xl font-bold text-wc-text">{{ stats?.pendingPayments ?? 0 }}</p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">por confirmar</p>
          </template>
        </div>

        <!-- Avg per client -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
          <div class="flex items-center justify-between">
            <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Promedio/cliente</span>
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-500/10">
              <svg class="h-4 w-4 text-sky-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
              </svg>
            </div>
          </div>
          <template v-if="loading && !stats">
            <div class="mt-3 h-8 w-24 animate-pulse rounded bg-wc-bg-secondary"></div>
          </template>
          <template v-else>
            <p class="mt-3 font-data text-3xl font-bold text-wc-text">${{ stats?.avgPerClient ?? '0' }}</p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">COP promedio</p>
          </template>
        </div>
      </div>

      <!-- Filters -->
      <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
          <!-- Status filter -->
          <select
            v-model="statusFilter"
            class="rounded-lg border border-wc-border bg-wc-bg-secondary py-2 pl-3 pr-8 text-sm text-wc-text focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"
          >
            <option v-for="opt in STATUS_OPTIONS" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
          </select>

          <!-- Date from -->
          <div class="flex items-center gap-2">
            <label class="shrink-0 text-xs text-wc-text-tertiary">Desde</label>
            <input
              type="date"
              v-model="dateFrom"
              class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"
            />
          </div>

          <!-- Date to -->
          <div class="flex items-center gap-2">
            <label class="shrink-0 text-xs text-wc-text-tertiary">Hasta</label>
            <input
              type="date"
              v-model="dateTo"
              class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"
            />
          </div>

          <!-- Clear filters -->
          <Transition name="fade">
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
          </Transition>
        </div>
      </div>

      <!-- Loading skeleton -->
      <template v-if="loading">
        <div class="overflow-hidden rounded-xl border border-wc-border">
          <div class="border-b border-wc-border bg-wc-bg-secondary px-4 py-3">
            <div class="flex gap-8">
              <div v-for="n in 6" :key="n" class="h-4 w-20 animate-pulse rounded bg-wc-bg-tertiary"></div>
            </div>
          </div>
          <div v-for="n in 8" :key="n" class="border-b border-wc-border px-4 py-4 last:border-0">
            <div class="flex items-center gap-8">
              <div class="h-4 w-32 animate-pulse rounded bg-wc-bg-tertiary"></div>
              <div class="h-4 w-16 animate-pulse rounded bg-wc-bg-tertiary"></div>
              <div class="h-4 w-24 animate-pulse rounded bg-wc-bg-tertiary"></div>
              <div class="h-4 w-16 animate-pulse rounded bg-wc-bg-tertiary"></div>
              <div class="h-4 w-20 animate-pulse rounded bg-wc-bg-tertiary"></div>
              <div class="h-4 w-28 animate-pulse rounded bg-wc-bg-tertiary"></div>
            </div>
          </div>
        </div>
      </template>

      <!-- Error state -->
      <div v-else-if="error" class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-8 text-center">
        <svg class="mx-auto h-8 w-8 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
        </svg>
        <p class="mt-2 text-sm text-wc-text">{{ error }}</p>
        <button @click="fetchPayments" class="mt-3 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700">
          Reintentar
        </button>
      </div>

      <!-- Payments table -->
      <div v-else class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-tertiary">
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-wc-border bg-wc-bg-secondary">
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Cliente</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Plan</th>
                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Monto</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Estado</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Metodo</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Fecha</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-wc-border">
              <tr
                v-for="payment in payments"
                :key="payment.id"
                class="transition-colors hover:bg-wc-bg-secondary/50"
              >
                <!-- Client -->
                <td class="px-4 py-3">
                  <div class="min-w-0">
                    <p class="truncate font-medium text-wc-text">{{ payment.buyer_name || payment.client_name || '-' }}</p>
                    <p v-if="payment.email" class="truncate text-xs text-wc-text-tertiary">{{ payment.email }}</p>
                  </div>
                </td>

                <!-- Plan -->
                <td class="px-4 py-3">
                  <span
                    v-if="payment.plan && payment.plan !== '-'"
                    class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold"
                    :class="getPlanColor(payment.plan)"
                  >
                    {{ payment.plan }}
                  </span>
                  <span v-else class="text-xs text-wc-text-tertiary">-</span>
                </td>

                <!-- Amount -->
                <td class="px-4 py-3 text-right">
                  <span class="font-data text-sm font-semibold text-wc-text">${{ payment.amount_fmt || payment.amount || '0' }}</span>
                  <p class="text-[10px] text-wc-text-tertiary">COP</p>
                </td>

                <!-- Status -->
                <td class="px-4 py-3">
                  <span
                    class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold"
                    :class="getStatusColor(payment.status)"
                  >
                    {{ getStatusLabel(payment.status) }}
                  </span>
                </td>

                <!-- Payment method -->
                <td class="px-4 py-3">
                  <span class="text-xs text-wc-text-secondary">{{ payment.payment_method || '-' }}</span>
                </td>

                <!-- Date -->
                <td class="px-4 py-3">
                  <span class="font-data text-xs text-wc-text-secondary">{{ payment.created_at || '-' }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Empty state -->
        <div v-if="!payments.length" class="px-4 py-12 text-center">
          <svg class="mx-auto h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
          </svg>
          <p class="mt-2 text-sm text-wc-text-tertiary">No se encontraron pagos</p>
          <button
            v-if="hasActiveFilters"
            @click="clearFilters"
            class="mt-2 text-xs font-medium text-red-500 transition-colors hover:text-red-400"
          >
            Limpiar filtros
          </button>
        </div>

        <!-- Pagination -->
        <div v-if="pagination && pagination.last_page > 1" class="border-t border-wc-border px-4 py-3">
          <div class="flex items-center justify-between">
            <p class="text-xs text-wc-text-tertiary">
              Mostrando {{ ((pagination.current_page - 1) * pagination.per_page) + 1 }}-{{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }} de {{ pagination.total }}
            </p>
            <div class="flex items-center gap-1">
              <!-- Prev -->
              <button
                @click="goToPage(pagination.current_page - 1)"
                :disabled="pagination.current_page <= 1"
                class="rounded-lg p-1.5 text-wc-text-tertiary transition-colors hover:bg-wc-bg-secondary disabled:cursor-not-allowed disabled:opacity-30"
              >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
              </button>

              <!-- Page numbers -->
              <template v-for="(pg, idx) in paginationPages" :key="idx">
                <span v-if="pg === '...'" class="px-1 text-xs text-wc-text-tertiary">...</span>
                <button
                  v-else
                  @click="goToPage(pg)"
                  :class="[
                    'min-w-[28px] rounded-lg px-2 py-1 text-xs font-medium transition-colors',
                    pg === pagination.current_page
                      ? 'bg-wc-accent text-white'
                      : 'text-wc-text-secondary hover:bg-wc-bg-secondary'
                  ]"
                >
                  {{ pg }}
                </button>
              </template>

              <!-- Next -->
              <button
                @click="goToPage(pagination.current_page + 1)"
                :disabled="pagination.current_page >= pagination.last_page"
                class="rounded-lg p-1.5 text-wc-text-tertiary transition-colors hover:bg-wc-bg-secondary disabled:cursor-not-allowed disabled:opacity-30"
              >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
              </button>
            </div>
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
