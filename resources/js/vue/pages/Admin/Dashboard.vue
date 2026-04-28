<script setup>
import { onMounted, onBeforeUnmount, computed } from 'vue';
import { storeToRefs } from 'pinia';
import { useAdminDashboardStore } from '../../stores/adminDashboard';
import AdminLayout from '../../layouts/AdminLayout.vue';
import AdminGreeting from '../../components/admin/dashboard/AdminGreeting.vue';
import AdminAlertsRow from '../../components/admin/dashboard/AdminAlertsRow.vue';
import AdminHeroMetrics from '../../components/admin/dashboard/AdminHeroMetrics.vue';
import AdminPulseCharts from '../../components/admin/dashboard/AdminPulseCharts.vue';
import AdminActivityFeed from '../../components/admin/dashboard/AdminActivityFeed.vue';
import AdminTopCoaches from '../../components/admin/dashboard/AdminTopCoaches.vue';
import AdminToolsGrid from '../../components/admin/dashboard/AdminToolsGrid.vue';

const store = useAdminDashboardStore();
const {
    data, loading, error,
    greeting, criticalAlerts, pendingTickets, reviewTickets,
    production, financial, operational,
    alerts, revenueChartData, planDistribution, clientBreakdown,
    recentPayments, recentInscriptions, topCoaches,
} = storeToRefs(store);

// Mostrar skeleton solo si no hay data previa (evita parpadeo en polling 30s)
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

    <!-- Loading skeleton — solo en first paint sin data -->
    <div v-if="showSkeleton" class="dashboard-loading">
      <div class="dashboard-loading-bar"></div>
      <div class="dashboard-loading-grid">
        <div v-for="i in 4" :key="i" class="dashboard-loading-card"></div>
      </div>
    </div>

    <!-- Error state — solo si no hay data previa -->
    <div v-else-if="error && !data" class="dashboard-error">
      <p class="dashboard-error-msg">{{ error }}</p>
      <button class="dashboard-error-btn" @click="store.fetchDashboard()">Reintentar</button>
    </div>

    <!-- Live data -->
    <div v-else-if="data" class="dashboard-stack">

      <!-- Greeting + alerts (Fase A) -->
      <AdminGreeting
        :greeting="greeting"
        :critical-alerts="criticalAlerts"
        :pending-tickets="pendingTickets"
        :review-tickets="reviewTickets"
      />

      <AdminAlertsRow :alerts="alerts" />

      <!-- Hero metrics row — 4 cards con mini-rings SVG (Fase B) -->
      <AdminHeroMetrics
        :production="production"
        :financial="financial"
        :operational="operational"
      />

      <!-- Charts row — MRR bars + donut planes + client breakdown (Fase C) -->
      <AdminPulseCharts
        :revenue-data="revenueChartData"
        :plan-distribution="planDistribution"
        :client-breakdown="clientBreakdown"
      />

      <!-- Activity feed + top coaches en grid 2-col en desktop, stack mobile -->
      <div class="dashboard-secondary">
        <AdminActivityFeed
          :payments="recentPayments"
          :inscriptions="recentInscriptions"
        />
        <AdminTopCoaches :coaches="topCoaches" />
      </div>

      <!-- Tools grid — accesos rapidos a 12 modulos editoriales (Fase B) -->
      <AdminToolsGrid />

    </div>
  </AdminLayout>
</template>

<style scoped>
.dashboard-stack {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

@media (min-width: 1024px) {
    .dashboard-stack { gap: 22px; }
}

/* Activity feed + top coaches: mobile stack, desktop 2/3 + 1/3 */
.dashboard-secondary {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
}
@media (min-width: 1024px) {
    .dashboard-secondary {
        grid-template-columns: 2fr 1fr;
        gap: 16px;
    }
}

/* ── Loading skeleton ──────────────────────────────────────────────────── */
.dashboard-loading {
    display: flex;
    flex-direction: column;
    gap: 18px;
}
.dashboard-loading-bar {
    height: 48px;
    width: min(100%, 380px);
    border-radius: 8px;
    background: var(--color-wc-bg-tertiary, #181818);
    animation: dashboard-pulse 1.5s ease-in-out infinite;
}
.dashboard-loading-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
}
@media (min-width: 1024px) {
    .dashboard-loading-grid { grid-template-columns: repeat(4, 1fr); }
}
.dashboard-loading-card {
    height: 120px;
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: var(--color-wc-bg-tertiary, #181818);
    animation: dashboard-pulse 1.5s ease-in-out infinite;
}
@keyframes dashboard-pulse {
    0%, 100% { opacity: 0.6; }
    50% { opacity: 0.9; }
}

/* ── Error state ───────────────────────────────────────────────────────── */
.dashboard-error {
    border-radius: 14px;
    border: 1px solid rgba(220, 38, 38, 0.2);
    background: var(--color-wc-red-soft, rgba(220, 38, 38, 0.05));
    padding: 24px;
    text-align: center;
}
.dashboard-error-msg {
    font-size: 14px;
    font-weight: 500;
    color: var(--color-wc-text);
    margin: 0 0 16px;
}
.dashboard-error-btn {
    background: var(--color-wc-accent, #DC2626);
    color: #fff;
    border: 0;
    border-radius: 8px;
    padding: 8px 16px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s var(--ease-out, ease);
}
.dashboard-error-btn:hover { background: #B91C1C; }
</style>
