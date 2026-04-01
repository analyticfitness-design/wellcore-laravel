<script setup>
import { ref, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import AdminLayout from '../../layouts/AdminLayout.vue';

const api = useApi();

const loading = ref(true);
const error = ref(null);
const data = ref(null);
const payments = ref([]);
const filter = ref('all');
const page = ref(1);

const filterOptions = [
    { value: 'all', label: 'Todos' },
    { value: 'paid', label: 'Pagados' },
    { value: 'pending', label: 'Pendientes' },
    { value: 'failed', label: 'Fallidos' },
    { value: 'refunded', label: 'Reembolsados' },
];

async function fetchPayments() {
    loading.value = true;
    error.value = null;
    try {
        const response = await api.get('/api/v/admin/payments', {
            params: { status: filter.value !== 'all' ? filter.value : undefined, page: page.value },
        });
        data.value = response.data;
        payments.value = response.data.payments || response.data.data || [];
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al cargar pagos';
    } finally {
        loading.value = false;
    }
}

function formatCurrency(value) {
    if (!value && value !== 0) return '$0';
    const num = typeof value === 'string' ? parseFloat(value.replace(/\./g, '').replace(',', '.')) : value;
    if (isNaN(num)) return '$0';
    return new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(num);
}

function applyFilter(val) {
    filter.value = val;
    page.value = 1;
    fetchPayments();
}

function getStatusColor(status) {
    const map = {
        paid: 'bg-emerald-500/10 text-emerald-500',
        approved: 'bg-emerald-500/10 text-emerald-500',
        pending: 'bg-amber-500/10 text-amber-500',
        failed: 'bg-red-500/10 text-red-500',
        refunded: 'bg-sky-500/10 text-sky-500',
    };
    return map[status] || 'bg-gray-500/10 text-gray-400';
}

function getStatusLabel(status) {
    const map = { paid: 'Pagado', approved: 'Aprobado', pending: 'Pendiente', failed: 'Fallido', refunded: 'Reembolsado' };
    return map[status] || status;
}

onMounted(() => {
    fetchPayments();
});
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">

      <!-- Header -->
      <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text">Pagos</h1>
        <p class="mt-1 text-sm text-wc-text-tertiary">Administracion de pagos y facturacion</p>
      </div>

      <!-- Stats Cards -->
      <div v-if="data" class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
          <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Ingresos totales</span>
          <p class="mt-2 font-data text-2xl font-bold text-wc-text">{{ formatCurrency(data.stats?.totalRevenue) }}</p>
        </div>
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
          <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Este mes</span>
          <p class="mt-2 font-data text-2xl font-bold text-emerald-500">{{ formatCurrency(data.stats?.monthRevenue) }}</p>
        </div>
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
          <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Pendientes</span>
          <p class="mt-2 font-data text-2xl font-bold text-amber-500">{{ data.stats?.pendingPayments ?? 0 }}</p>
        </div>
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
          <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Total pagos</span>
          <p class="mt-2 font-data text-2xl font-bold text-wc-text">{{ data.pagination?.total || 0 }}</p>
        </div>
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
      <div v-if="loading" class="space-y-2">
        <div v-for="i in 8" :key="i" class="h-14 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-6 text-center">
        <p class="text-sm text-wc-text">{{ error }}</p>
        <button @click="fetchPayments" class="mt-3 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white">Reintentar</button>
      </div>

      <!-- Payments Table -->
      <div v-else class="overflow-x-auto rounded-xl border border-wc-border">
        <table class="w-full min-w-[600px]">
          <thead class="border-b border-wc-border bg-wc-bg-tertiary">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Cliente</th>
              <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Concepto</th>
              <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Monto</th>
              <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Estado</th>
              <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Fecha</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-wc-border">
            <tr v-for="(payment, idx) in payments" :key="idx" class="bg-wc-bg-secondary transition-colors hover:bg-wc-bg-tertiary">
              <td class="px-4 py-3 text-sm text-wc-text">{{ payment.buyer_name || payment.client_name || '-' }}</td>
              <td class="px-4 py-3 text-sm text-wc-text-secondary">{{ payment.plan || '-' }}</td>
              <td class="px-4 py-3 font-data text-sm font-bold text-wc-text">{{ formatCurrency(payment.amount) }}</td>
              <td class="px-4 py-3">
                <span class="rounded-full px-2.5 py-0.5 text-xs font-medium capitalize" :class="getStatusColor(payment.status)">{{ getStatusLabel(payment.status) }}</span>
              </td>
              <td class="px-4 py-3 text-xs text-wc-text-tertiary">{{ payment.date || payment.created_at || '-' }}</td>
            </tr>
          </tbody>
        </table>
        <div v-if="!payments.length" class="p-12 text-center">
          <p class="text-sm text-wc-text-tertiary">Sin pagos encontrados</p>
        </div>
      </div>

      <!-- Revenue Chart -->
      <div v-if="data && data.revenueChart && data.revenueChart.length" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
        <h3 class="mb-4 font-display text-lg tracking-wide text-wc-text">Ingresos por Mes</h3>
        <div class="space-y-2">
          <div v-for="(item, idx) in data.revenueChart" :key="idx" class="flex items-center gap-3">
            <span class="w-16 text-xs font-medium text-wc-text-tertiary">{{ item.month }}</span>
            <div class="flex-1 rounded-full bg-wc-bg-secondary h-4 overflow-hidden">
              <div class="h-full rounded-full bg-gradient-to-r from-violet-600 to-violet-500 transition-all duration-500" :style="{ width: `${(item.amount / (data.revenueChart.reduce((max, i) => Math.max(max, i.amount), 1))) * 100}%` }"></div>
            </div>
            <span class="w-28 text-right text-xs font-data font-medium text-wc-text">{{ formatCurrency(item.amount) }}</span>
          </div>
        </div>
      </div>

    </div>
  </AdminLayout>
</template>
