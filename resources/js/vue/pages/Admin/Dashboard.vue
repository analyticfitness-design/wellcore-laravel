<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { RouterLink } from 'vue-router';
import { useApi } from '../../composables/useApi';
import AdminLayout from '../../layouts/AdminLayout.vue';

const api = useApi();
const loading = ref(true);
const error = ref(null);
const data = ref(null);
const lastRefresh = ref('');

let refreshInterval = null;

function formatCOP(value) {
  const n = Number(value || 0);
  return '$' + new Intl.NumberFormat('es-CO', { maximumFractionDigits: 0 }).format(n);
}
function formatNumber(value) {
  return new Intl.NumberFormat('es-CO').format(Number(value || 0));
}

const mrrDelta = computed(() => {
  const d = Number(data.value?.financial?.mrr_delta_pct ?? 0);
  return { value: d, positive: d >= 0 };
});

async function fetchDashboard() {
  loading.value = !data.value;
  error.value = null;
  try {
    const { data: r } = await api.get('/api/v/admin/dashboard');
    data.value = r;
    lastRefresh.value = new Date().toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
  } catch (err) {
    error.value = err.response?.data?.message || 'Error al cargar el dashboard';
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  fetchDashboard();
  refreshInterval = setInterval(fetchDashboard, 30000);
});

onBeforeUnmount(() => {
  if (refreshInterval) clearInterval(refreshInterval);
});

function alertClasses(type) {
  const map = {
    error: 'border-red-500/40 bg-red-500/10 text-red-400',
    warning: 'border-yellow-500/40 bg-yellow-500/10 text-yellow-400',
    info: 'border-blue-500/40 bg-blue-500/10 text-blue-400',
  };
  return map[type] || map.info;
}
</script>

<template>
  <AdminLayout>

    <!-- Loading -->
    <div v-if="loading" class="space-y-6">
      <div class="h-12 w-96 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
      <div class="grid gap-4 lg:grid-cols-3">
        <div v-for="i in 3" :key="i" class="h-80 animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary"></div>
      </div>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-6 text-center">
      <p class="text-sm font-medium text-wc-text">{{ error }}</p>
      <button @click="fetchDashboard" class="mt-4 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors">
        Reintentar
      </button>
    </div>

    <div v-else-if="data" class="space-y-8">

      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
          <h1 class="font-display text-4xl tracking-wide text-wc-text sm:text-5xl">{{ data.greeting || 'Panel de Administracion' }}</h1>
          <div class="mt-2 flex items-center gap-3">
            <div class="flex items-center gap-1.5 text-xs text-wc-text-tertiary">
              <span class="relative flex h-2 w-2">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
              </span>
              <span>En vivo</span>
              <template v-if="lastRefresh"><span class="text-wc-text-tertiary/60">&middot; {{ lastRefresh }}</span></template>
            </div>
          </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <RouterLink
            to="/admin/plan-tickets?status=pendiente"
            class="inline-flex items-center gap-2 rounded-lg bg-wc-accent px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700 transition-colors"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6M5 8h14M5 4h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z" />
            </svg>
            Tickets pendientes
            <span
              v-if="data.production?.plan_tickets_pendientes"
              class="rounded-full bg-white/20 px-1.5 text-[11px] font-bold"
            >{{ data.production.plan_tickets_pendientes }}</span>
          </RouterLink>
          <RouterLink
            to="/admin/plan-tickets?status=en_revision"
            class="inline-flex items-center gap-2 rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm font-semibold text-wc-text hover:bg-wc-bg-secondary transition-colors"
          >
            En revision
            <span
              v-if="data.production?.plan_tickets_en_revision"
              class="rounded-full bg-blue-500/20 px-1.5 text-[11px] font-bold text-blue-500"
            >{{ data.production.plan_tickets_en_revision }}</span>
          </RouterLink>
        </div>
      </div>

      <!-- Alerts -->
      <div v-if="data.alerts && data.alerts.length" class="space-y-2">
        <div
          v-for="(alert, idx) in data.alerts"
          :key="idx"
          class="rounded-xl border p-4"
          :class="alertClasses(alert.type)"
        >
          <div class="flex items-start gap-3">
            <svg class="h-5 w-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold">{{ alert.title }}</p>
              <p v-if="alert.body" class="mt-0.5 text-xs opacity-80">{{ alert.body }}</p>
            </div>
            <RouterLink
              v-if="alert.link"
              :to="alert.link"
              class="shrink-0 text-xs font-semibold underline hover:opacity-80"
            >Ver &rarr;</RouterLink>
          </div>
        </div>
      </div>

      <!-- 3-col KPI grid -->
      <div class="grid gap-6 lg:grid-cols-3">

        <!-- PRODUCCION -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 transition hover:border-wc-accent/30 hover:-translate-y-0.5 hover:shadow-lg">
          <div class="flex items-center justify-between mb-4">
            <h2 class="font-display text-lg tracking-widest text-wc-text uppercase">Produccion</h2>
            <span class="rounded-full bg-wc-accent/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-wc-accent">Hoy</span>
          </div>
          <div class="space-y-3">
            <div class="flex items-baseline justify-between rounded-lg bg-wc-bg-secondary px-3 py-2.5">
              <span class="text-xs uppercase tracking-wide text-wc-text-tertiary">Tickets pendientes</span>
              <span
                class="font-data text-2xl font-bold"
                :class="data.production?.plan_tickets_pendientes > 0 ? 'text-wc-accent' : 'text-wc-text'"
              >{{ formatNumber(data.production?.plan_tickets_pendientes) }}</span>
            </div>
            <div class="flex items-baseline justify-between rounded-lg bg-wc-bg-secondary px-3 py-2.5">
              <span class="text-xs uppercase tracking-wide text-wc-text-tertiary">En revision</span>
              <span class="font-data text-2xl font-bold text-yellow-400">{{ formatNumber(data.production?.plan_tickets_en_revision) }}</span>
            </div>
            <div class="flex items-baseline justify-between rounded-lg bg-wc-bg-secondary px-3 py-2.5">
              <span class="text-xs uppercase tracking-wide text-wc-text-tertiary">Completados mes</span>
              <span class="font-data text-2xl font-bold text-emerald-400">{{ formatNumber(data.production?.plan_tickets_completados_este_mes) }}</span>
            </div>
            <div class="flex items-baseline justify-between rounded-lg bg-wc-bg-secondary px-3 py-2.5">
              <span class="text-xs uppercase tracking-wide text-wc-text-tertiary">Rechazados mes</span>
              <span class="font-data text-xl font-bold text-wc-text">{{ formatNumber(data.production?.plan_tickets_rechazados_este_mes) }}</span>
            </div>
            <div class="flex items-baseline justify-between rounded-lg bg-wc-bg-secondary px-3 py-2.5">
              <span class="text-xs uppercase tracking-wide text-wc-text-tertiary">Tickets overdue</span>
              <span
                class="font-data text-xl font-bold"
                :class="data.production?.plan_tickets_overdue > 0 ? 'text-wc-accent' : 'text-wc-text'"
              >{{ formatNumber(data.production?.plan_tickets_overdue) }}</span>
            </div>
            <div class="flex items-baseline justify-between rounded-lg bg-wc-bg-secondary px-3 py-2.5">
              <span class="text-xs uppercase tracking-wide text-wc-text-tertiary">Check-ins sin responder</span>
              <span class="font-data text-xl font-bold text-wc-text">{{ formatNumber(data.production?.checkins_sin_responder_global) }}</span>
            </div>
            <div class="flex items-baseline justify-between rounded-lg bg-wc-bg-secondary px-3 py-2.5">
              <span class="text-xs uppercase tracking-wide text-wc-text-tertiary">Support tickets</span>
              <span class="font-data text-xl font-bold text-wc-text">{{ formatNumber(data.production?.support_tickets_abiertos) }}</span>
            </div>
          </div>
        </div>

        <!-- FINANZAS -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 transition hover:border-emerald-500/30 hover:-translate-y-0.5 hover:shadow-lg">
          <div class="flex items-center justify-between mb-4">
            <h2 class="font-display text-lg tracking-widest text-wc-text uppercase">Finanzas</h2>
            <span class="rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-emerald-400">COP</span>
          </div>

          <div class="rounded-lg bg-wc-bg-secondary p-4 mb-3">
            <p class="text-xs uppercase tracking-wider text-wc-text-tertiary">MRR actual</p>
            <div class="mt-1 flex items-baseline gap-2">
              <span class="font-data text-3xl font-bold text-wc-text">{{ formatCOP(data.financial?.mrr_actual_cop) }}</span>
            </div>
            <div class="mt-1 flex items-center gap-1.5 text-xs">
              <svg class="h-3.5 w-3.5" :class="mrrDelta.positive ? 'text-emerald-400' : 'text-red-400 rotate-180'" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" />
              </svg>
              <span :class="mrrDelta.positive ? 'text-emerald-400' : 'text-red-400'" class="font-semibold">
                {{ mrrDelta.positive ? '+' : '' }}{{ mrrDelta.value.toFixed(1) }}%
              </span>
              <span class="text-wc-text-tertiary">vs. mes anterior ({{ formatCOP(data.financial?.mrr_mes_anterior_cop) }})</span>
            </div>
          </div>

          <div class="space-y-3">
            <div class="flex items-baseline justify-between rounded-lg bg-wc-bg-secondary px-3 py-2.5">
              <span class="text-xs uppercase tracking-wide text-wc-text-tertiary">Pagos pendientes</span>
              <span class="font-data text-xl font-bold text-yellow-400">{{ formatCOP(data.financial?.pagos_pendientes_cop) }}</span>
            </div>
            <div class="flex items-baseline justify-between rounded-lg bg-wc-bg-secondary px-3 py-2.5">
              <span class="text-xs uppercase tracking-wide text-wc-text-tertiary">Nuevas inscripciones</span>
              <span class="font-data text-xl font-bold text-wc-text">{{ formatNumber(data.financial?.nuevas_inscripciones_este_mes) }}</span>
            </div>
          </div>
        </div>

        <!-- OPERACION -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 transition hover:border-blue-500/30 hover:-translate-y-0.5 hover:shadow-lg">
          <div class="flex items-center justify-between mb-4">
            <h2 class="font-display text-lg tracking-widest text-wc-text uppercase">Operacion</h2>
            <span class="rounded-full bg-blue-500/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-blue-400">Mes</span>
          </div>
          <div class="space-y-3">
            <div class="rounded-lg bg-wc-bg-secondary p-4">
              <p class="text-xs uppercase tracking-wider text-wc-text-tertiary">Clientes activos</p>
              <p class="font-data text-3xl font-bold text-wc-text mt-1">{{ formatNumber(data.operational?.clientes_activos) }}</p>
            </div>
            <div class="flex items-baseline justify-between rounded-lg bg-wc-bg-secondary px-3 py-2.5">
              <span class="text-xs uppercase tracking-wide text-wc-text-tertiary">Clientes nuevos</span>
              <span class="font-data text-xl font-bold text-emerald-400">+{{ formatNumber(data.operational?.clientes_nuevos_mes) }}</span>
            </div>
            <div class="flex items-baseline justify-between rounded-lg bg-wc-bg-secondary px-3 py-2.5">
              <span class="text-xs uppercase tracking-wide text-wc-text-tertiary">Coaches activos</span>
              <span class="font-data text-xl font-bold text-wc-text">{{ formatNumber(data.operational?.coaches_activos) }}</span>
            </div>
            <div class="flex items-baseline justify-between rounded-lg bg-wc-bg-secondary px-3 py-2.5">
              <span class="text-xs uppercase tracking-wide text-wc-text-tertiary">Retencion mes</span>
              <span
                class="font-data text-xl font-bold"
                :class="(data.operational?.tasa_retencion_mes_pct ?? 0) >= 80 ? 'text-emerald-400' : 'text-yellow-400'"
              >{{ Number(data.operational?.tasa_retencion_mes_pct ?? 0).toFixed(1) }}%</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Top coaches -->
      <div v-if="data.top_coaches_month && data.top_coaches_month.length" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-6">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="font-display text-xl tracking-widest text-wc-text uppercase">Top Coaches del Mes</h2>
            <p class="mt-1 text-xs text-wc-text-tertiary">Clasificados por tickets completados</p>
          </div>
          <RouterLink to="/admin/coaches" class="text-xs font-semibold text-wc-accent hover:text-red-400">Ver todos &rarr;</RouterLink>
        </div>
        <ul class="space-y-2">
          <li
            v-for="(coach, idx) in data.top_coaches_month.slice(0, 5)"
            :key="coach.coach_id"
            class="flex items-center gap-4 rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 transition hover:border-wc-accent/30"
          >
            <div
              class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full font-data font-bold"
              :class="[
                idx === 0 ? 'bg-yellow-500/20 text-yellow-400' :
                idx === 1 ? 'bg-zinc-400/20 text-zinc-300' :
                idx === 2 ? 'bg-orange-500/20 text-orange-400' :
                'bg-wc-bg-tertiary text-wc-text-secondary'
              ]"
            >#{{ idx + 1 }}</div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-wc-text truncate">{{ coach.name }}</p>
              <p class="text-xs text-wc-text-tertiary">{{ formatNumber(coach.clients) }} clientes</p>
            </div>
            <div class="text-right shrink-0">
              <p class="font-data text-2xl font-bold text-emerald-400">{{ formatNumber(coach.tickets_completados) }}</p>
              <p class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">tickets</p>
            </div>
          </li>
        </ul>
      </div>

    </div>
  </AdminLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
