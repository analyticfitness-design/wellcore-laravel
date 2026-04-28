<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { useApi } from '../../composables/useApi';
import AdminLayout from '../../layouts/AdminLayout.vue';
import AdminGreeting from '../../components/admin/dashboard/AdminGreeting.vue';
import AdminAlertsRow from '../../components/admin/dashboard/AdminAlertsRow.vue';
import AdminHeroMetrics from '../../components/admin/dashboard/AdminHeroMetrics.vue';
import AdminToolsGrid from '../../components/admin/dashboard/AdminToolsGrid.vue';

const api = useApi();
const loading = ref(true);
const error = ref(null);
const data = ref(null);

let refreshInterval = null;

async function fetchDashboard() {
  loading.value = !data.value;
  error.value = null;
  try {
    const { data: r } = await api.get('/api/v/admin/dashboard');
    data.value = r;
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
</script>

<template>
  <AdminLayout>

    <!-- Loading skeleton — reduced motion friendly -->
    <div v-if="loading" class="dashboard-loading">
      <div class="dashboard-loading-bar"></div>
      <div class="dashboard-loading-grid">
        <div v-for="i in 4" :key="i" class="dashboard-loading-card"></div>
      </div>
    </div>

    <!-- Error state -->
    <div v-else-if="error" class="dashboard-error">
      <p class="dashboard-error-msg">{{ error }}</p>
      <button class="dashboard-error-btn" @click="fetchDashboard">Reintentar</button>
    </div>

    <!-- Live data -->
    <div v-else-if="data" class="dashboard-stack">

      <!-- Greeting + alerts (Fase A) -->
      <AdminGreeting
        :greeting="data.greeting || 'Panel de Administracion'"
        :critical-alerts="data.alerts?.length || 0"
        :pending-tickets="data.production?.plan_tickets_pendientes || 0"
        :review-tickets="data.production?.plan_tickets_en_revision || 0"
      />

      <AdminAlertsRow :alerts="data.alerts || []" />

      <!-- Hero metrics row — 4 cards con mini-rings SVG (Fase B) -->
      <AdminHeroMetrics
        :production="data.production || {}"
        :financial="data.financial || {}"
        :operational="data.operational || {}"
      />

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
