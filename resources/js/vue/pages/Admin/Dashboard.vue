<script setup>
import { onMounted, onBeforeUnmount, computed } from 'vue';
import { storeToRefs } from 'pinia';
import { useAdminDashboardStore } from '../../stores/adminDashboard';
import AdminLayout from '../../layouts/AdminLayout.vue';

import AdminHeroSection   from '../../components/admin/dashboard/AdminHeroSection.vue';
import AdminAlertChips    from '../../components/admin/dashboard/AdminAlertChips.vue';
import AdminMrrCard       from '../../components/admin/dashboard/AdminMrrCard.vue';
import AdminMrrChart      from '../../components/admin/dashboard/AdminMrrChart.vue';
import AdminStatGrid      from '../../components/admin/dashboard/AdminStatGrid.vue';
import AdminKpiRow        from '../../components/admin/dashboard/AdminKpiRow.vue';
import AdminDistCard      from '../../components/admin/dashboard/AdminDistCard.vue';
import AdminActivosCard   from '../../components/admin/dashboard/AdminActivosCard.vue';
import AdminActivityFeed  from '../../components/admin/dashboard/AdminActivityFeed.vue';
import AdminTopCoaches    from '../../components/admin/dashboard/AdminTopCoaches.vue';
import AdminToolsCard     from '../../components/admin/dashboard/AdminToolsCard.vue';

const store = useAdminDashboardStore();
const {
  data, loading, error,
  greeting, criticalAlerts, pendingTickets, reviewTickets,
  production, financial, operational,
  alerts, revenueChartData, planDistribution, clientBreakdown,
  recentPayments, recentInscriptions, topCoaches,
} = storeToRefs(store);

// ── Derivados del target Claude Design ──────────────────────────────────
// El backend retorna `production/financial/operational` con shape variable;
// estos computed normalizan al contrato esperado por los componentes target.
// Cuando el endpoint exponga el shape exacto, los computed se vuelven thin
// pass-throughs (TODO Fase 7 polish).

const mrrData = computed(() => {
  const fin = financial.value || {};
  const current = Number(fin.mrr_current ?? fin.current ?? 0);
  const previous = Number(fin.mrr_previous ?? fin.previous ?? 0);
  const delta = current - previous;
  const deltaPercent = previous > 0
    ? Math.round((delta / previous) * 1000) / 10
    : (current > 0 ? 100 : 0);
  const fmt = (n) => '$' + Number(n).toLocaleString('es-CO', { maximumFractionDigits: 0 });
  return {
    current,
    previous,
    deltaPercent,
    formattedCurrent: fin.mrr_formatted || fmt(current),
    formattedDelta: fin.mrr_delta_formatted || fmt(delta),
  };
});

// Sparkline: últimos 3-12 puntos del histórico de ingresos. revenueChartData
// puede venir como [{ month, value }, ...] o [number, ...].
const mrrSparkData = computed(() => {
  const arr = revenueChartData.value || [];
  if (!arr.length) return [];
  if (typeof arr[0] === 'number') return arr;
  return arr.map(x => Number(x.value ?? x.amount ?? x.mrr ?? 0));
});

// Alias del breakdown de clientes (el store usa singular, target plural).
const clientsBreakdown = computed(() => {
  const b = clientBreakdown.value || {};
  return {
    total: Number(b.total ?? b.totalClients ?? 0),
    active: Number(b.active ?? b.activos ?? 0),
    inactive: Number(b.inactive ?? b.inactivos ?? 0),
  };
});

const retention = computed(() => {
  const ops = operational.value || {};
  const prod = production.value || {};
  return {
    percent: Number(ops.retention_percent ?? prod.retention_percent ?? ops.retencion ?? 0),
    target: 85,
    delta: ops.retention_delta || prod.retention_delta || '',
  };
});

// Plan distribution con colores del target (override si el backend ya manda colores).
const PLAN_COLORS = {
  entrenamiento:   { color: '#DC2626', glow: 'rgba(220,38,38,.5)' },
  nutricion:       { color: '#10B981', glow: 'rgba(16,185,129,.5)' },
  'nutrición':     { color: '#10B981', glow: 'rgba(16,185,129,.5)' },
  suplementacion:  { color: '#3B82F6', glow: 'rgba(59,130,246,.5)' },
  'suplementación':{ color: '#3B82F6', glow: 'rgba(59,130,246,.5)' },
  habitos:         { color: '#F59E0B', glow: 'rgba(245,158,11,.5)' },
  'hábitos':       { color: '#F59E0B', glow: 'rgba(245,158,11,.5)' },
  'sin clasificar':{ color: '#71717A' },
  ciclo:           { color: '#A78BFA' },
};
const planDistTarget = computed(() => {
  const arr = planDistribution.value || [];
  if (!arr.length) {
    // Fallback con shape del target para que el donut renderice estructura aún sin data
    return [
      { name: 'Entrenamiento',  value: 0, color: '#DC2626', glow: 'rgba(220,38,38,.5)' },
      { name: 'Nutrición',      value: 0, color: '#10B981', glow: 'rgba(16,185,129,.5)' },
      { name: 'Suplementación', value: 0, color: '#3B82F6', glow: 'rgba(59,130,246,.5)' },
      { name: 'Hábitos',        value: 0, color: '#F59E0B', glow: 'rgba(245,158,11,.5)' },
      { name: 'Sin clasificar', value: 0, color: '#71717A' },
      { name: 'Ciclo',          value: 0, color: '#A78BFA' },
    ];
  }
  return arr.map(d => {
    const key = String(d.name || d.label || '').toLowerCase();
    const palette = PLAN_COLORS[key] || { color: d.color || '#71717A' };
    return {
      name: d.name || d.label || 'Sin clasificar',
      value: Number(d.value ?? d.count ?? 0),
      color: d.color || palette.color,
      glow: palette.glow,
    };
  });
});

const adminName = computed(() => data.value?.adminName || data.value?.userName || 'Daniel Esparza');
const adminRole = computed(() => data.value?.adminRole || 'CEO');

const showSkeleton = computed(() => loading.value && !data.value);

onMounted(() => {
  store.fetchDashboard();
  store.startPolling(30000);
});

onBeforeUnmount(() => {
  store.stopPolling();
});
</script>

<template>
  <AdminLayout>

    <!-- Loading skeleton -->
    <div v-if="showSkeleton" class="dash-skeleton">
      <div class="sk-bar"></div>
      <div class="sk-grid">
        <div v-for="i in 4" :key="i" class="sk-card"></div>
      </div>
    </div>

    <!-- Error state -->
    <div v-else-if="error && !data" class="dash-error">
      <p>{{ error }}</p>
      <button @click="store.fetchDashboard()">Reintentar</button>
    </div>

    <!-- Live data: estructura literal del target Claude Design -->
    <template v-else-if="data">

      <!-- §1 HERO ejecutivo (mobile + desktop con alerts inline en desktop) -->
      <AdminHeroSection
        :greeting="greeting"
        :user-name="adminName"
        :role="adminRole"
      >
        <template #alerts>
          <AdminAlertChips
            :alerts="alerts"
            :pending-tickets="pendingTickets"
            :review-tickets="reviewTickets"
          />
        </template>
      </AdminHeroSection>

      <!-- §2 ALERT CHIPS (mobile only — desktop van dentro del slot del hero) -->
      <AdminAlertChips
        :alerts="alerts"
        :pending-tickets="pendingTickets"
        :review-tickets="reviewTickets"
        class="alerts-mobile-only"
      />

      <!-- §3 KPI ROW (desktop only) — 4 KPIs con sparklines mini -->
      <AdminKpiRow
        :pending-tickets="pendingTickets"
        :review-tickets="reviewTickets"
        :mrr="mrrData"
        :mrr-spark="mrrSparkData"
        :clients-breakdown="clientsBreakdown"
        :retention="retention"
        class="kpi-desktop-only"
      />

      <!-- §4 MRR (mobile: card simple) -->
      <AdminMrrCard
        :mrr="mrrData"
        :spark="mrrSparkData"
        class="mrr-mobile-only"
      />

      <!-- §5 STAT GRID (mobile only) — 2 stat-card (Activos + Retención) -->
      <AdminStatGrid
        :clients-breakdown="clientsBreakdown"
        :retention="retention"
        class="stat-grid-mobile-only"
      />

      <!-- §6 ROW 8/4 (desktop) — MRR chart + Distribución -->
      <div class="row-8-4 row-desktop-only">
        <AdminMrrChart :mrr="mrrData" :spark="mrrSparkData" />
        <AdminDistCard :distribution="planDistTarget" />
      </div>

      <!-- §6b DISTRIBUCIÓN (mobile only — desktop ya está en row-8-4) -->
      <section class="section dist-mobile-only">
        <div class="section-h">
          <div class="ttl">Distribución de planes</div>
          <div class="lnk">Ver detalle →</div>
        </div>
        <AdminDistCard :distribution="planDistTarget" />
      </section>

      <!-- §7 ACTIVOS card (full progress) — ambos viewports -->
      <AdminActivosCard :clients-breakdown="clientsBreakdown" />

      <!-- §8 FEED + COACHES (8/4 desktop, stack mobile) -->
      <div class="row-8-4">
        <AdminActivityFeed
          :payments="recentPayments"
          :inscriptions="recentInscriptions"
        />
        <AdminTopCoaches :coaches="topCoaches" />
      </div>

      <!-- §9 TOOLS card -->
      <AdminToolsCard />

    </template>
  </AdminLayout>
</template>

<style scoped>
/* Visibilidad mobile vs desktop según target */
.alerts-mobile-only,
.mrr-mobile-only,
.stat-grid-mobile-only,
.dist-mobile-only { display: block; }

.kpi-desktop-only,
.row-desktop-only { display: none; }

@media (min-width: 1024px){
  .alerts-mobile-only,
  .mrr-mobile-only,
  .stat-grid-mobile-only,
  .dist-mobile-only { display: none; }

  .kpi-desktop-only { display: grid; }

  .row-desktop-only { display: grid; }
  .row-8-4 {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 16px;
    margin-bottom: 24px;
  }
}

/* Skeleton + error mantenidos minimal — no afectan render normal */
.dash-skeleton { display: flex; flex-direction: column; gap: 18px; padding: 20px 16px; }
.sk-bar {
  height: 48px; width: min(100%, 380px);
  border-radius: 12px;
  background: rgba(255,255,255,.04);
  animation: dash-pulse 1.5s ease-in-out infinite;
}
.sk-grid { display: grid; grid-template-columns: 1fr; gap: 12px; }
@media (min-width: 1024px){ .sk-grid { grid-template-columns: repeat(4, 1fr); } }
.sk-card {
  height: 120px; border-radius: 16px;
  background: rgba(255,255,255,.03);
  animation: dash-pulse 1.5s ease-in-out infinite;
}
@keyframes dash-pulse {
  0%, 100% { opacity: 0.6; }
  50% { opacity: 0.9; }
}

.dash-error {
  border-radius: 16px;
  border: 1px solid rgba(220, 38, 38, 0.2);
  background: rgba(220, 38, 38, 0.05);
  padding: 24px; text-align: center; margin: 20px 16px;
}
.dash-error p { font-size: 14px; color: var(--wc-text); margin: 0 0 16px; }
.dash-error button {
  background: var(--wc-accent); color: #fff; border: 0;
  border-radius: 12px; padding: 8px 16px;
  font-size: 13px; font-weight: 600; cursor: pointer;
}
.dash-error button:hover { background: #B91C1C; }
</style>
