<script setup>
import { ref, computed, onMounted } from 'vue';
import { useApi } from '../../composables/useApi';
import AdminLayout from '../../layouts/AdminLayout.vue';

const api = useApi();
const loading = ref(true);
const stats = ref(null);

async function fetchStats() {
  loading.value = true;
  try {
    const { data } = await api.get('/api/v/admin/plan-tickets/stats');
    stats.value = data || null;
  } catch (e) {
    stats.value = null;
  } finally {
    loading.value = false;
  }
}

const totals = computed(() => stats.value?.totals || {});
const perCoach = computed(() => {
  const list = stats.value?.per_coach || [];
  return [...list].sort((a, b) => (b.submitted || 0) - (a.submitted || 0));
});
const perPlanType = computed(() => stats.value?.per_plan_type || {});
const trend30d = computed(() => stats.value?.trend_30d || []);

const maxPlanType = computed(() => {
  const vals = Object.values(perPlanType.value).map(v => Number(v) || 0);
  return Math.max(1, ...vals);
});
const maxTrend = computed(() => {
  const all = trend30d.value.flatMap(d => [d.submitted || 0, d.completed || 0, d.rejected || 0]);
  return Math.max(1, ...all);
});

const PLAN_TYPE_LABEL = { esencial: 'Esencial', metodo: 'Metodo', elite: 'Elite' };

function num(v) { return typeof v === 'number' ? v : (Number(v) || 0); }

function rejectionRate(coach) {
  const sub = num(coach.submitted);
  if (sub === 0) return 0;
  return Math.round((num(coach.rejected) / sub) * 100);
}

function fmtHours(v) {
  if (v === null || v === undefined) return '-';
  const n = Number(v);
  if (!Number.isFinite(n)) return '-';
  return n.toFixed(1);
}

function fmtDay(d) {
  if (!d) return '';
  try {
    return new Date(d).toLocaleDateString('es-MX', { day: '2-digit', month: 'short' });
  } catch { return d; }
}

onMounted(fetchStats);
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">

      <!-- Header -->
      <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">Estadisticas de Tickets de Plan</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">Salud del proceso de briefs.</p>
      </div>

      <!-- Loading -->
      <template v-if="loading">
        <div class="grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-4">
          <div v-for="n in 4" :key="n" class="animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary h-24"></div>
        </div>
        <div class="grid grid-cols-1 gap-3 sm:gap-4 md:grid-cols-3">
          <div v-for="n in 3" :key="'s'+n" class="animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary h-24"></div>
        </div>
      </template>

      <!-- Empty -->
      <div v-else-if="!stats" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center">
        <p class="text-sm text-wc-text-secondary">No se pudieron cargar las estadisticas.</p>
      </div>

      <template v-else>
        <!-- KPI cards (row 1) -->
        <div class="grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-4">
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
            <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Total</p>
            <p class="mt-1 font-data text-2xl font-bold text-wc-text">{{ num(totals.total) }}</p>
          </div>
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
            <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Pendientes</p>
            <p class="mt-1 font-data text-2xl font-bold text-yellow-500">{{ num(totals.pendiente) }}</p>
          </div>
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
            <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Completados</p>
            <p class="mt-1 font-data text-2xl font-bold text-emerald-500">{{ num(totals.completado) }}</p>
          </div>
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
            <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Rechazados</p>
            <p class="mt-1 font-data text-2xl font-bold text-red-400">{{ num(totals.rechazado) }}</p>
          </div>
        </div>

        <!-- Secondary KPIs (row 2) -->
        <div class="grid grid-cols-1 gap-3 sm:gap-4 md:grid-cols-3">
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
            <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">SLA promedio a completar</p>
            <p class="mt-1 font-data text-2xl font-bold text-wc-text">{{ fmtHours(stats.avg_time_submit_to_complete_hours) }} <span class="text-sm text-wc-text-tertiary">hrs</span></p>
          </div>
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
            <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Tiempo prom. a revision</p>
            <p class="mt-1 font-data text-2xl font-bold text-wc-text">{{ fmtHours(stats.avg_time_to_review_hours) }} <span class="text-sm text-wc-text-tertiary">hrs</span></p>
          </div>
          <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
            <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Vencidos</p>
            <p class="mt-1 font-data text-2xl font-bold" :class="num(stats.overdue_count) > 0 ? 'text-red-400' : 'text-wc-text'">{{ num(stats.overdue_count) }}</p>
          </div>
        </div>

        <!-- Distribution per plan type (row 3) -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
          <div class="flex items-center justify-between mb-4">
            <h2 class="font-display text-lg tracking-wide text-wc-text">Distribucion por tipo de plan</h2>
          </div>
          <div v-if="Object.keys(perPlanType).length === 0" class="text-sm text-wc-text-tertiary">Sin datos.</div>
          <div v-else class="space-y-3">
            <div v-for="(val, key) in perPlanType" :key="key" class="flex items-center gap-3">
              <span class="w-24 shrink-0 text-right text-xs font-medium text-wc-text-secondary">{{ PLAN_TYPE_LABEL[key] || key }}</span>
              <div class="relative h-6 flex-1 overflow-hidden rounded bg-wc-bg-secondary">
                <div
                  class="absolute inset-y-0 left-0 rounded bg-wc-accent/70 transition-all duration-1000 ease-out"
                  :style="{ width: (num(val) / maxPlanType * 100) + '%' }"
                ></div>
              </div>
              <span class="w-10 shrink-0 text-right font-data text-sm font-bold text-wc-text">{{ num(val) }}</span>
            </div>
          </div>
        </div>

        <!-- Per coach table (row 4) -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary overflow-hidden">
          <div class="p-5 pb-3">
            <h2 class="font-display text-lg tracking-wide text-wc-text">Por coach</h2>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="border-y border-wc-border bg-wc-bg-secondary text-left text-xs uppercase tracking-wider text-wc-text-tertiary">
                <tr>
                  <th class="px-4 py-3 font-semibold">Coach</th>
                  <th class="px-4 py-3 font-semibold text-right">Enviados</th>
                  <th class="px-4 py-3 font-semibold text-right">Completados</th>
                  <th class="px-4 py-3 font-semibold text-right">Rechazados</th>
                  <th class="px-4 py-3 font-semibold text-right">% Rechazo</th>
                  <th class="px-4 py-3 font-semibold text-right">SLA (hrs)</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-wc-border">
                <tr v-if="perCoach.length === 0">
                  <td colspan="6" class="px-4 py-6 text-center text-sm text-wc-text-tertiary">Sin datos por coach.</td>
                </tr>
                <tr v-for="c in perCoach" :key="c.coach_id || c.coach_name" class="hover:bg-wc-bg-secondary/40 transition">
                  <td class="px-4 py-3 text-sm font-medium text-wc-text">{{ c.coach_name || '-' }}</td>
                  <td class="px-4 py-3 text-right font-data text-sm text-wc-text">{{ num(c.submitted) }}</td>
                  <td class="px-4 py-3 text-right font-data text-sm text-emerald-500">{{ num(c.completed) }}</td>
                  <td class="px-4 py-3 text-right font-data text-sm text-red-400">{{ num(c.rejected) }}</td>
                  <td class="px-4 py-3 text-right font-data text-sm" :class="rejectionRate(c) > 20 ? 'text-red-400' : 'text-wc-text-secondary'">{{ rejectionRate(c) }}%</td>
                  <td class="px-4 py-3 text-right font-data text-sm text-wc-text-secondary">{{ fmtHours(c.avg_time_to_complete_hours) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Trend 30d chart (row 5) — simple SVG bars -->
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
          <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
            <h2 class="font-display text-lg tracking-wide text-wc-text">Tendencia ultimos 30 dias</h2>
            <div class="flex items-center gap-4 text-xs">
              <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-blue-500"></span><span class="text-wc-text-secondary">Enviados</span></span>
              <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-emerald-500"></span><span class="text-wc-text-secondary">Completados</span></span>
              <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-red-400"></span><span class="text-wc-text-secondary">Rechazados</span></span>
            </div>
          </div>

          <div v-if="trend30d.length === 0" class="text-sm text-wc-text-tertiary">Sin datos de tendencia.</div>
          <div v-else class="overflow-x-auto">
            <div class="flex items-end gap-1 h-48 min-w-full" :style="{ minWidth: Math.max(trend30d.length * 28, 400) + 'px' }">
              <div
                v-for="(d, idx) in trend30d"
                :key="idx"
                class="group relative flex-1 flex flex-col items-center gap-0.5"
                :title="`${fmtDay(d.date)} · Env: ${num(d.submitted)} · Comp: ${num(d.completed)} · Rech: ${num(d.rejected)}`"
              >
                <div class="flex items-end gap-0.5 h-44 w-full">
                  <div
                    class="flex-1 bg-blue-500 rounded-t-sm transition-all group-hover:opacity-80"
                    :style="{ height: (num(d.submitted) / maxTrend * 100) + '%' }"
                  ></div>
                  <div
                    class="flex-1 bg-emerald-500 rounded-t-sm transition-all group-hover:opacity-80"
                    :style="{ height: (num(d.completed) / maxTrend * 100) + '%' }"
                  ></div>
                  <div
                    class="flex-1 bg-red-400 rounded-t-sm transition-all group-hover:opacity-80"
                    :style="{ height: (num(d.rejected) / maxTrend * 100) + '%' }"
                  ></div>
                </div>
                <span
                  v-if="idx % Math.ceil(trend30d.length / 10) === 0"
                  class="text-[9px] text-wc-text-tertiary whitespace-nowrap"
                >{{ fmtDay(d.date) }}</span>
              </div>
            </div>
          </div>
        </div>

      </template>

    </div>
  </AdminLayout>
</template>
