<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue';
import { useI18n } from 'vue-i18n';
import { Chart, registerables } from 'chart.js';
import { useMetricsChart } from '../../composables/metrics/useMetricsChart';
import EmptyStateChart from './EmptyStateChart.vue';

Chart.register(...registerables);

const { t } = useI18n();

const props = defineProps({
  entries: { type: Array, default: () => [] },
  period: { type: String, default: '90d' },
});

const emit = defineEmits(['cta-click', 'period-change']);

const { setGlobalDefaults, weightChartConfig } = useMetricsChart();
const canvasRef = ref(null);
let chartInstance = null;

const PERIODS = [
  { label: '30d', value: '30d' },
  { label: '90d', value: '90d' },
  { label: '6m', value: '6m' },
  { label: '1a', value: '1y' },
];

function createChart() {
  if (!canvasRef.value || !props.entries.length) return;
  destroyChart();
  setGlobalDefaults(Chart);
  const config = weightChartConfig(props.entries, props.period);
  chartInstance = new Chart(canvasRef.value, config);
}

function destroyChart() {
  if (chartInstance) {
    chartInstance.destroy();
    chartInstance = null;
  }
}

onMounted(createChart);
onBeforeUnmount(destroyChart);

watch(() => props.entries, createChart, { deep: true });
watch(() => props.period, createChart);
</script>

<template>
  <section class="weight-chart-card">
    <div class="weight-chart-head">
      <div>
        <h2 class="weight-chart-title">{{ t('client_progress.metrics_chart_title') }}</h2>
        <p class="weight-chart-sub">{{ t('client_progress.metrics_chart_sub', { period }) }}</p>
      </div>
      <!-- Period tabs -->
      <div class="period-tabs" role="tablist" :aria-label="t('client_progress.metrics_chart_period_aria')">
        <button
          v-for="p in PERIODS"
          :key="p.value"
          class="period-tab"
          :class="{ 'period-tab--active': period === p.value }"
          role="tab"
          :aria-selected="period === p.value"
          @click="emit('period-change', p.value)"
        >
          {{ p.label }}
        </button>
      </div>
    </div>

    <!-- Legend -->
    <div v-if="entries.length" class="chart-legend">
      <span class="legend-item">
        <span class="legend-dot legend-dot--line"></span> {{ t('client_progress.metrics_chart_legend_weight') }}
      </span>
    </div>

    <!-- Chart or Empty -->
    <div class="weight-chart-canvas-wrap">
      <canvas v-show="entries.length" ref="canvasRef"></canvas>
      <EmptyStateChart
        v-if="!entries.length"
        :title="t('client_progress.metrics_chart_empty_title')"
        :message="t('client_progress.metrics_chart_empty_msg')"
        :cta-text="t('client_progress.metrics_chart_empty_cta')"
        height="100%"
        @cta-click="emit('cta-click')"
      />
    </div>
  </section>
</template>

<style scoped>
.weight-chart-card {
  border-radius: 16px;
  border: 1px solid var(--color-wc-border);
  background: var(--color-wc-bg-tertiary);
  padding: 24px;
  margin-bottom: 24px;
}
.weight-chart-head {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 16px;
  margin-bottom: 8px;
  flex-wrap: wrap;
}
.weight-chart-title {
  font-size: 15px;
  font-weight: 600;
  color: var(--color-wc-text);
  letter-spacing: .01em;
  margin: 0;
}
.weight-chart-sub {
  margin-top: 4px;
  font-size: 12px;
  color: var(--color-wc-text-tertiary);
}
.period-tabs {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 4px;
  background: var(--color-wc-bg);
  border: 1px solid var(--color-wc-border);
  border-radius: 999px;
}
.period-tab {
  font-family: var(--font-mono);
  font-size: 11px;
  font-weight: 500;
  letter-spacing: .04em;
  padding: 6px 12px;
  border-radius: 999px;
  color: var(--color-wc-text-tertiary);
  background: none;
  border: none;
  cursor: pointer;
  transition: background .12s, color .12s;
  min-height: 32px;
}
.period-tab--active {
  background: var(--color-wc-bg-tertiary);
  color: var(--color-wc-text);
  box-shadow: 0 1px 0 rgba(255,255,255,.14) inset;
}
.chart-legend {
  display: flex;
  align-items: center;
  gap: 16px;
  margin: 14px 0 10px;
  flex-wrap: wrap;
}
.legend-item {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-family: var(--font-mono);
  font-size: 11px;
  color: var(--color-wc-text-secondary);
  letter-spacing: .04em;
}
.legend-dot {
  width: 10px;
  height: 2px;
  border-radius: 1px;
}
.legend-dot--line { background: var(--color-wc-text); }
.weight-chart-canvas-wrap {
  width: 100%;
  height: 280px;
  position: relative;
}
canvas { width: 100% !important; height: 100% !important; }
</style>
