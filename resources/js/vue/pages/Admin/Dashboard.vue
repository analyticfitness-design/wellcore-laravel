<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useApi } from '../../composables/useApi';
import AdminLayout from '../../layouts/AdminLayout.vue';

const api = useApi();
const router = useRouter();

const loading = ref(true);
const error = ref(null);
const data = ref(null);
let refreshInterval = null;

async function fetchDashboard() {
    loading.value = !data.value;
    error.value = null;
    try {
        const response = await api.get('/api/v/admin/dashboard');
        const r = response.data;
        const growthArr = r.clientGrowthData || [];
        const growthRate = growthArr.length >= 2
            ? ((growthArr.at(-1).count - growthArr.at(-2).count) / Math.max(growthArr.at(-2).count, 1) * 100)
            : 0;
        data.value = {
            activeClients: r.stats?.activeClients ?? 0,
            mrr: r.stats?.monthlyRevenue ?? 0,
            churnRate: 0,
            growthRate,
            revenueChart: (r.revenueChartData || []).map(x => ({ month: x.month, amount: x.total })),
            clientGrowth: growthArr,
            recentActivity: [
                ...(r.recentInscriptions || []).map(i => ({ type: 'signup', description: `${i.nombre} — plan ${i.plan}`, time: i.timeAgo })),
                ...(r.recentPayments || []).map(p => ({ type: 'payment', description: `${p.buyerName} pagó ${p.plan} (${p.method})`, time: p.timeAgo })),
            ],
        };
    } catch (err) {
        error.value = err.response?.data?.message || 'Error al cargar el dashboard';
    } finally {
        loading.value = false;
    }
}

function formatCurrency(value) {
    if (!value && value !== 0) return '$0';
    return new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(value);
}

function formatNumber(value) {
    if (!value && value !== 0) return '0';
    return new Intl.NumberFormat('es-CO').format(value);
}

function formatPercent(value) {
    if (!value && value !== 0) return '0%';
    return `${Number(value).toFixed(1)}%`;
}

onMounted(() => {
    fetchDashboard();
    refreshInterval = setInterval(fetchDashboard, 30000);
});

onUnmounted(() => {
    if (refreshInterval) clearInterval(refreshInterval);
});
</script>

<template>
  <AdminLayout>
    <!-- Loading state -->
    <div v-if="loading" class="space-y-6">
      <div class="h-10 w-72 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
      <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div v-for="i in 4" :key="i" class="h-32 animate-pulse rounded-xl bg-wc-bg-tertiary"></div>
      </div>
      <div class="grid gap-6 lg:grid-cols-2">
        <div class="h-64 animate-pulse rounded-xl bg-wc-bg-tertiary"></div>
        <div class="h-64 animate-pulse rounded-xl bg-wc-bg-tertiary"></div>
      </div>
    </div>

    <!-- Error state -->
    <div v-else-if="error" class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-6 text-center">
      <svg class="mx-auto h-10 w-10 text-wc-accent/50" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
      </svg>
      <p class="mt-3 text-sm font-medium text-wc-text">{{ error }}</p>
      <button @click="fetchDashboard" class="mt-4 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors">
        Reintentar
      </button>
    </div>

    <!-- Dashboard content -->
    <div v-else-if="data" class="space-y-6">

      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">Panel de Administracion</h1>
          <div class="mt-1 flex items-center gap-3">
            <p class="text-sm text-wc-text-tertiary">Resumen general de WellCore Fitness</p>
            <div class="flex items-center gap-1.5 text-xs text-wc-text-tertiary">
              <span class="relative flex h-2 w-2">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
              </span>
              <span>En vivo</span>
            </div>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <RouterLink to="/admin/clients" class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
            </svg>
            Ver clientes
          </RouterLink>
          <RouterLink to="/admin/payments" class="inline-flex items-center gap-2 rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2 text-sm font-medium text-wc-text hover:bg-wc-bg-secondary transition-colors">
            Ver pagos
          </RouterLink>
        </div>
      </div>

      <!-- KPI Cards -->
      <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        <!-- Active Clients -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
          <div class="flex items-center justify-between">
            <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Clientes activos</span>
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/10">
              <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
              </svg>
            </div>
          </div>
          <p class="mt-3 font-data text-3xl font-bold text-wc-text">{{ formatNumber(data.activeClients) }}</p>
          <p class="mt-0.5 text-xs text-wc-text-tertiary">en total</p>
        </div>

        <!-- Monthly Revenue (MRR) -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
          <div class="flex items-center justify-between">
            <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">MRR</span>
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-500/10">
              <svg class="h-4 w-4 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
              </svg>
            </div>
          </div>
          <p class="mt-3 font-data text-3xl font-bold text-wc-text">{{ formatCurrency(data.mrr) }}</p>
          <p class="mt-0.5 text-xs text-wc-text-tertiary">COP este mes</p>
        </div>

        <!-- Churn Rate -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
          <div class="flex items-center justify-between">
            <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Churn rate</span>
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-500/10">
              <svg class="h-4 w-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6 9 12.75l4.286-4.286a11.948 11.948 0 0 1 4.306 6.43l.776 2.898m0 0 3.182-5.511m-3.182 5.51-5.511-3.181" />
              </svg>
            </div>
          </div>
          <p class="mt-3 font-data text-3xl font-bold text-wc-text">{{ formatPercent(data.churnRate) }}</p>
          <p class="mt-0.5 text-xs text-wc-text-tertiary">este mes</p>
        </div>

        <!-- Growth -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
          <div class="flex items-center justify-between">
            <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Crecimiento</span>
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-500/10">
              <svg class="h-4 w-4 text-sky-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" />
              </svg>
            </div>
          </div>
          <p class="mt-3 font-data text-3xl font-bold text-wc-text">
            <span v-if="data.growthRate > 0" class="text-emerald-500">+</span>{{ formatPercent(data.growthRate) }}
          </p>
          <p class="mt-0.5 text-xs text-wc-text-tertiary">vs. mes anterior</p>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="grid gap-6 lg:grid-cols-2">
        <!-- Revenue Chart Placeholder -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
          <h3 class="mb-4 font-display text-lg tracking-wide text-wc-text">Ingresos Mensuales</h3>
          <div v-if="data.revenueChart && data.revenueChart.length" class="space-y-2">
            <div v-for="(item, idx) in data.revenueChart" :key="idx" class="flex items-center gap-3">
              <span class="w-16 text-xs font-medium text-wc-text-tertiary">{{ item.month }}</span>
              <div class="flex-1 rounded-full bg-wc-bg-secondary h-4 overflow-hidden">
                <div class="h-full rounded-full bg-gradient-to-r from-red-600 to-red-500 transition-all duration-500" :style="{ width: `${(item.amount / (data.revenueChart.reduce((max, i) => Math.max(max, i.amount), 1))) * 100}%` }"></div>
              </div>
              <span class="w-24 text-right text-xs font-data font-medium text-wc-text">{{ formatCurrency(item.amount) }}</span>
            </div>
          </div>
          <div v-else class="flex h-40 items-center justify-center">
            <p class="text-sm text-wc-text-tertiary">Sin datos de ingresos</p>
          </div>
        </div>

        <!-- Client Growth Placeholder -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
          <h3 class="mb-4 font-display text-lg tracking-wide text-wc-text">Crecimiento de Clientes</h3>
          <div v-if="data.clientGrowth && data.clientGrowth.length" class="space-y-2">
            <div v-for="(item, idx) in data.clientGrowth" :key="idx" class="flex items-center gap-3">
              <span class="w-16 text-xs font-medium text-wc-text-tertiary">{{ item.month }}</span>
              <div class="flex-1 rounded-full bg-wc-bg-secondary h-4 overflow-hidden">
                <div class="h-full rounded-full bg-gradient-to-r from-emerald-600 to-emerald-500 transition-all duration-500" :style="{ width: `${(item.count / (data.clientGrowth.reduce((max, i) => Math.max(max, i.count), 1))) * 100}%` }"></div>
              </div>
              <span class="w-12 text-right text-xs font-data font-medium text-wc-text">{{ item.count }}</span>
            </div>
          </div>
          <div v-else class="flex h-40 items-center justify-center">
            <p class="text-sm text-wc-text-tertiary">Sin datos de crecimiento</p>
          </div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
        <div class="mb-4 flex items-center justify-between">
          <h3 class="font-display text-lg tracking-wide text-wc-text">Actividad Reciente</h3>
          <RouterLink to="/admin/feed" class="text-sm font-medium text-wc-accent hover:underline">Ver todo</RouterLink>
        </div>
        <div v-if="data.recentActivity && data.recentActivity.length" class="divide-y divide-wc-border">
          <div v-for="(activity, idx) in data.recentActivity.slice(0, 10)" :key="idx" class="flex items-center gap-3 py-3">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full" :class="{
              'bg-emerald-500/10 text-emerald-500': activity.type === 'training',
              'bg-violet-500/10 text-violet-500': activity.type === 'payment',
              'bg-sky-500/10 text-sky-500': activity.type === 'signup',
              'bg-orange-500/10 text-orange-500': activity.type === 'checkin',
              'bg-wc-bg-secondary text-wc-text-tertiary': !['training','payment','signup','checkin'].includes(activity.type),
            }">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path v-if="activity.type === 'training'" stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75Z" />
                <path v-else-if="activity.type === 'payment'" stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                <path v-else-if="activity.type === 'signup'" stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                <path v-else stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
              </svg>
            </div>
            <div class="min-w-0 flex-1">
              <p class="text-sm text-wc-text truncate">{{ activity.description }}</p>
              <p class="text-xs text-wc-text-tertiary">{{ activity.time }}</p>
            </div>
          </div>
        </div>
        <div v-else class="py-8 text-center">
          <p class="text-sm text-wc-text-tertiary">Sin actividad reciente</p>
        </div>
      </div>

    </div>
  </AdminLayout>
</template>
