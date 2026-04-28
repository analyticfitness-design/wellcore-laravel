<script setup>
import { onMounted, onBeforeUnmount } from 'vue';
import { storeToRefs } from 'pinia';
import AdminLayout from '../../layouts/AdminLayout.vue';
import AdminTicketsStatsKPIs from '../../components/admin/plan-tickets-stats/AdminTicketsStatsKPIs.vue';
import AdminTicketsThroughputChart from '../../components/admin/plan-tickets-stats/AdminTicketsThroughputChart.vue';
import AdminTicketsCoachRanking from '../../components/admin/plan-tickets-stats/AdminTicketsCoachRanking.vue';
import AdminTicketsRejectReasons from '../../components/admin/plan-tickets-stats/AdminTicketsRejectReasons.vue';
import AdminTicketsResolutionTime from '../../components/admin/plan-tickets-stats/AdminTicketsResolutionTime.vue';
import { useAdminTicketsStatsStore } from '../../stores/adminTicketsStats.js';

const store = useAdminTicketsStatsStore();
const { loading, error, period, kpis, throughput, coachRanking, rejectionReasons, resolutionBuckets, isEmpty, hasData } = storeToRefs(store);

const PERIODS = [
  { value: 'week',    label: 'SEMANA' },
  { value: 'month',   label: 'MES' },
  { value: 'quarter', label: 'TRIMESTRE' },
  { value: 'year',    label: 'AÑO' },
];

function setPeriod(p) {
  if (p !== period.value) store.setPeriod(p);
}

onMounted(() => store.fetch());
</script>

<template>
  <AdminLayout>
    <div class="stats-page">

      <!-- Page header -->
      <header class="page-header">
        <div class="header-left">
          <h1 class="page-title">STATS DE TICKETS</h1>
          <p class="page-tagline">"El dato sin accion es solo decoracion."</p>
        </div>
        <div class="period-selector" role="group" aria-label="Seleccionar periodo">
          <button
            v-for="p in PERIODS"
            :key="p.value"
            class="period-pill"
            :class="{ 'period-pill--active': period === p.value }"
            @click="setPeriod(p.value)"
            :aria-pressed="period === p.value"
          >
            {{ p.label }}
          </button>
        </div>
      </header>

      <!-- Loading skeleton -->
      <template v-if="loading && !hasData">
        <div class="skeleton-grid-4">
          <div v-for="n in 4" :key="n" class="skeleton-card"></div>
        </div>
        <div class="skeleton-card skeleton-card--tall"></div>
        <div class="skeleton-grid-2">
          <div class="skeleton-card skeleton-card--tall"></div>
          <div class="skeleton-card skeleton-card--tall"></div>
        </div>
        <div class="skeleton-card skeleton-card--tall"></div>
      </template>

      <!-- Error -->
      <div v-else-if="error" class="error-state">
        <span class="error-num">ERR</span>
        <p class="error-msg">{{ error }}</p>
        <button class="error-retry" @click="store.fetch()">REINTENTAR →</button>
      </div>

      <!-- Global empty state (no activity at all in period) -->
      <div v-else-if="hasData && isEmpty" class="global-empty">
        <div class="global-empty-num">—</div>
        <p class="global-empty-msg">
          "Sin datos del periodo seleccionado. Los stats se generan automaticamente cuando hay actividad en el pipeline."
        </p>
        <button
          class="global-empty-cta"
          @click="setPeriod('year')"
          :disabled="period === 'year'"
        >VER OTRO PERIODO →</button>
      </div>

      <!-- Content -->
      <template v-else-if="hasData">
        <!-- KPIs -->
        <AdminTicketsStatsKPIs :kpis="kpis" />

        <!-- Throughput chart -->
        <AdminTicketsThroughputChart :throughput="throughput" :period="period" />

        <!-- Coach ranking + Rejection reasons (2-col desktop) -->
        <div class="two-col">
          <AdminTicketsCoachRanking :coaches="coachRanking" />
          <AdminTicketsRejectReasons :reasons="rejectionReasons" />
        </div>

        <!-- Resolution time histogram -->
        <AdminTicketsResolutionTime :resolution-buckets="resolutionBuckets" />
      </template>

    </div>
  </AdminLayout>
</template>

<style scoped>
.stats-page {
    display: flex;
    flex-direction: column;
    gap: 12px;
    position: relative;
    z-index: 1;
}
@media (min-width: 1024px) {
    .stats-page { gap: 16px; }
}

/* Header */
.page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 4px;
}
.header-left {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.page-title {
    font-family: var(--font-display);
    font-size: clamp(28px, 5vw, 44px);
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--color-wc-text);
    margin: 0;
    line-height: 1;
}
.page-tagline {
    font-family: var(--font-editorial, 'Fraunces', Georgia, serif);
    font-style: italic;
    font-size: 12px;
    color: var(--color-wc-gold, #C8A769);
    margin: 0;
    line-height: 1.55;
}

/* Period selector */
.period-selector {
    display: flex;
    align-items: center;
    gap: 4px;
    flex-wrap: wrap;
}
.period-pill {
    height: 28px;
    padding: 0 10px;
    border-radius: 6px;
    border: 1px solid var(--color-wc-border);
    background: transparent;
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
    cursor: pointer;
    transition: background 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease), color 0.15s var(--ease-out, ease);
}
.period-pill:hover {
    color: var(--color-wc-text-secondary);
    border-color: var(--color-wc-border-2);
}
.period-pill--active {
    background: var(--color-wc-red-soft, rgba(220,38,38,0.1));
    border-color: var(--color-wc-accent, #DC2626);
    color: var(--color-wc-text);
}

/* Two-column grid */
.two-col {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
}
@media (min-width: 1024px) {
    .two-col { grid-template-columns: 1fr 1fr; gap: 16px; }
}

/* Loading skeleton */
.skeleton-grid-4 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}
@media (min-width: 768px) {
    .skeleton-grid-4 { grid-template-columns: repeat(4, 1fr); gap: 12px; }
}
.skeleton-grid-2 {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
}
@media (min-width: 1024px) {
    .skeleton-grid-2 { grid-template-columns: 1fr 1fr; }
}
.skeleton-card {
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: var(--color-wc-bg-tertiary);
    height: 80px;
    animation: page-pulse 1.5s ease-in-out infinite;
}
.skeleton-card--tall { height: 160px; }

@keyframes page-pulse {
    0%, 100% { opacity: 0.6; }
    50%       { opacity: 0.9; }
}

/* Error state */
.error-state {
    padding: 32px;
    text-align: center;
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17,17,17,0.7);
}
.error-num {
    display: block;
    font-family: var(--font-display);
    font-size: 40px;
    color: var(--color-wc-red-text, #F87171);
    letter-spacing: 0.1em;
    margin-bottom: 8px;
}
.error-msg {
    font-size: 13px;
    color: var(--color-wc-text-secondary);
    margin: 0 0 16px;
}
.error-retry {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--color-wc-text-secondary);
    background: transparent;
    border: none;
    border-bottom: 1px solid var(--color-wc-border);
    padding-bottom: 4px;
    cursor: pointer;
    transition: color 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease);
}
.error-retry:hover {
    color: var(--color-wc-text);
    border-bottom-color: var(--color-wc-accent, #DC2626);
}

/* Global empty state */
.global-empty {
    padding: 48px 24px;
    text-align: center;
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17,17,17,0.7);
}
.global-empty-num {
    font-family: var(--font-display);
    font-size: 72px;
    color: var(--color-wc-bg-tertiary, #181818);
    letter-spacing: 0.1em;
    line-height: 1;
    margin-bottom: 16px;
    user-select: none;
}
.global-empty-msg {
    font-family: var(--font-editorial, 'Fraunces', Georgia, serif);
    font-style: italic;
    font-size: 14px;
    color: var(--color-wc-text-tertiary);
    line-height: 1.6;
    margin: 0 0 20px;
    max-width: 480px;
    margin-left: auto;
    margin-right: auto;
    text-wrap: balance;
}
.global-empty-cta {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--color-wc-text-secondary);
    background: transparent;
    border: none;
    border-bottom: 1px solid var(--color-wc-border);
    padding-bottom: 4px;
    cursor: pointer;
    transition: color 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease);
}
.global-empty-cta:hover:not(:disabled) {
    color: var(--color-wc-text);
    border-bottom-color: var(--color-wc-accent, #DC2626);
}
.global-empty-cta:disabled { opacity: 0.4; cursor: not-allowed; }

@media (prefers-reduced-motion: reduce) {
    .skeleton-card { animation: none !important; }
}
</style>
