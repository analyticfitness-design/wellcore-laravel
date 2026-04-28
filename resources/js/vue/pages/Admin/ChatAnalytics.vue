<script setup>
import { computed, onMounted } from 'vue';
import AdminLayout         from '../../layouts/AdminLayout.vue';
import AdminGreeting       from '../../components/admin/dashboard/AdminGreeting.vue';
import AdminChatKPIs       from '../../components/admin/chat-analytics/AdminChatKPIs.vue';
import AdminChatVolumeChart from '../../components/admin/chat-analytics/AdminChatVolumeChart.vue';
import AdminChatResponseTimeChart from '../../components/admin/chat-analytics/AdminChatResponseTimeChart.vue';
import AdminChatHeatmap    from '../../components/admin/chat-analytics/AdminChatHeatmap.vue';
import AdminChatTopCoaches from '../../components/admin/chat-analytics/AdminChatTopCoaches.vue';
import { useAdminChatAnalyticsStore } from '../../stores/adminChatAnalytics';

const store = useAdminChatAnalyticsStore();

const PERIODS = [
    { key: 'today',   label: 'Hoy' },
    { key: 'week',    label: 'Semana' },
    { key: 'month',   label: 'Mes' },
    { key: 'quarter', label: 'Trimestre' },
    { key: 'year',    label: 'Año' },
];

const initialPaint = computed(() => store.loading && !store.data);

const refreshHint = computed(() => {
    const s = store.secondsSinceRefresh;
    if (s === null) return '';
    if (s < 10)  return 'Actualizado ahora';
    if (s < 60)  return `Actualizado hace ${s}s`;
    if (s < 3600) return `Actualizado hace ${Math.floor(s / 60)} min`;
    return 'Actualizado hace 1h+';
});

onMounted(() => {
    store.fetchAnalytics();
});
</script>

<template>
  <AdminLayout>
    <AdminGreeting :greeting="'Chat Analytics'" :critical-alerts="0" />

    <!-- Sub-header eyebrow + period selector -->
    <div class="page-meta">
      <span class="page-eyebrow">COMUNICACIÓN COACH-CLIENTE</span>
      <div class="meta-actions">
        <span v-if="refreshHint" class="poll-hint">{{ refreshHint }}</span>
        <div class="period-selector" role="group" aria-label="Selector de período">
          <button
            v-for="p in PERIODS"
            :key="p.key"
            class="period-btn"
            :class="{ 'period-btn--active': store.period === p.key }"
            type="button"
            @click="store.setPeriod(p.key)"
          >{{ p.label }}</button>
        </div>
      </div>
    </div>

    <!-- Loading skeleton -->
    <div v-if="initialPaint" class="page-loading page-block">
      <div class="page-loading-bar"></div>
      <div class="page-loading-grid">
        <div v-for="i in 4" :key="i" class="page-loading-card"></div>
      </div>
      <div class="page-loading-tall"></div>
      <div class="page-loading-tall"></div>
    </div>

    <!-- Error state -->
    <div v-else-if="store.error" class="error-card page-block">
      <span class="error-eyebrow">ERROR</span>
      <p class="error-msg">{{ store.error }}</p>
      <button class="btn-primary" type="button" @click="store.fetchAnalytics()">Reintentar</button>
    </div>

    <!-- Empty state editorial -->
    <div v-else-if="store.data && !store.hasData" class="empty-card page-block">
      <div class="empty-num">—</div>
      <p class="empty-msg">"Sin datos suficientes para analytics. Los métricas se generan a partir de 100+ mensajes en el período."</p>
      <div class="empty-period-btns">
        <button
          v-for="p in PERIODS.filter(p => p.key !== store.period)"
          :key="p.key"
          class="empty-cta"
          type="button"
          @click="store.setPeriod(p.key)"
        >ELEGIR {{ p.label.toUpperCase() }} →</button>
      </div>
    </div>

    <!-- Contenido principal -->
    <template v-else-if="store.data">

      <!-- KPIs hero -->
      <AdminChatKPIs
        class="page-block"
        :kpis="store.kpis"
        :loading="store.loading"
      />

      <!-- Charts fila: Volume + Response Time -->
      <div class="charts-row page-block">
        <AdminChatVolumeChart
          :data="store.volumeChart"
          :period="store.period"
        />
        <AdminChatResponseTimeChart
          :buckets="store.responseTimeBuckets"
          :stats="store.responseTimeStats"
          :max-count="store.maxBucketCount"
        />
      </div>

      <!-- Heatmap ancho completo -->
      <AdminChatHeatmap
        class="page-block"
        :data="store.heatmap"
        :max-count="store.maxHeatmapCount"
      />

      <!-- Top coaches ancho completo (en una fila abajo) -->
      <AdminChatTopCoaches
        class="page-block"
        :coaches="store.topCoaches"
      />

    </template>
  </AdminLayout>
</template>

<style scoped>
.page-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    flex-wrap: wrap;
    padding: 6px 0 14px;
}
.page-eyebrow {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}
.meta-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.poll-hint {
    font-family: var(--font-mono, monospace);
    font-size: 9px; letter-spacing: 0.18em; text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}

/* Period selector */
.period-selector {
    display: flex;
    gap: 4px;
    flex-wrap: wrap;
}
.period-btn {
    height: 28px;
    padding: 0 10px;
    border-radius: 6px;
    background: transparent;
    color: var(--color-wc-text-secondary);
    border: 1px solid var(--color-wc-border);
    font-family: var(--font-mono, monospace);
    font-size: 9px; letter-spacing: 0.18em; text-transform: uppercase;
    cursor: pointer;
    transition: background 0.15s var(--ease-out, ease), color 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease);
}
.period-btn:hover:not(.period-btn--active) {
    background: rgba(255,255,255,0.04);
    border-color: var(--color-wc-border-2);
}
.period-btn--active {
    background: var(--color-wc-red-soft, rgba(220,38,38,0.1));
    color: var(--color-wc-red-text, #F87171);
    border-color: rgba(220,38,38,0.4);
}

.page-block { margin-bottom: 12px; }
@media (min-width: 1024px) { .page-block { margin-bottom: 20px; } }

/* Charts row: 2 columnas desktop */
.charts-row {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
}
@media (min-width: 1024px) {
    .charts-row {
        grid-template-columns: 3fr 2fr;
        gap: 16px;
    }
}

/* Loading skeleton */
.page-loading { display: flex; flex-direction: column; gap: 12px; }
.page-loading-bar {
    height: 36px;
    background: var(--color-wc-bg-tertiary);
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    animation: page-pulse 1.5s ease-in-out infinite;
}
.page-loading-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
}
@media (min-width: 768px)  { .page-loading-grid { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 1024px) { .page-loading-grid { grid-template-columns: repeat(4, 1fr); } }
.page-loading-card {
    height: 124px;
    background: var(--color-wc-bg-tertiary);
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    animation: page-pulse 1.5s ease-in-out infinite;
}
.page-loading-tall {
    height: 200px;
    background: var(--color-wc-bg-tertiary);
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    animation: page-pulse 1.5s ease-in-out infinite;
}
@keyframes page-pulse {
    0%, 100% { opacity: 0.6; }
    50%      { opacity: 0.9; }
}

/* Error state */
.error-card {
    border-radius: 14px;
    border: 1px solid rgba(220,38,38,0.22);
    background: rgba(220,38,38,0.07);
    padding: 22px; text-align: center;
    display: flex; flex-direction: column; align-items: center; gap: 8px;
}
.error-eyebrow {
    font-family: var(--font-mono, monospace);
    font-size: 9px; letter-spacing: 0.22em; text-transform: uppercase;
    color: var(--color-wc-red-text, #F87171);
}
.error-msg {
    font-family: var(--font-sans);
    font-size: 13px; color: var(--color-wc-text); margin: 0;
}
.btn-primary {
    background: var(--color-wc-accent, #DC2626);
    color: #fff;
    border: 1px solid var(--color-wc-accent, #DC2626);
    border-radius: 10px;
    padding: 10px 16px;
    font-family: var(--font-sans);
    font-size: 13px; font-weight: 600;
    cursor: pointer;
    transition: background 0.15s var(--ease-out, ease);
}
.btn-primary:hover { background: #B91C1C; }

/* Empty state */
.empty-card {
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17,17,17,0.7);
    padding: 32px 18px 24px; text-align: center;
    display: flex; flex-direction: column; align-items: center; gap: 0;
}
.empty-num {
    font-family: var(--font-display);
    font-size: 56px; color: var(--color-wc-bg-tertiary);
    letter-spacing: 0.1em; line-height: 1;
    margin-bottom: 12px; user-select: none;
}
.empty-msg {
    font-family: var(--font-editorial, 'Fraunces', Georgia, serif);
    font-style: italic; font-size: 13px;
    color: var(--color-wc-text-tertiary);
    line-height: 1.55; margin: 0 0 16px;
    max-width: 480px; text-wrap: balance;
}
.empty-period-btns { display: flex; gap: 8px; flex-wrap: wrap; justify-content: center; }
.empty-cta {
    display: inline-flex; align-items: center; gap: 5px;
    font-family: var(--font-mono, monospace);
    font-size: 9px; letter-spacing: 0.22em; text-transform: uppercase;
    color: var(--color-wc-text-secondary);
    background: transparent; border: none;
    border-bottom: 1px solid var(--color-wc-border);
    padding-bottom: 4px; cursor: pointer;
    transition: color 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease);
}
.empty-cta:hover {
    color: var(--color-wc-text);
    border-bottom-color: var(--color-wc-accent, #DC2626);
}

@media (prefers-reduced-motion: reduce) {
    .page-loading-bar,
    .page-loading-card,
    .page-loading-tall { animation: none !important; }
}
</style>
