<script setup>
import { computed, ref, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
  throughput: { type: Array, default: () => [] },
  period: { type: String, default: 'month' },
});

const SVG_W = 600;
const SVG_H = 100;
const PAD_TOP = 6;
const PAD_BOTTOM = 22;
const PAD_LEFT = 6;
const PAD_RIGHT = 6;
const CHART_H = SVG_H - PAD_TOP - PAD_BOTTOM;
const CHART_W = SVG_W - PAD_LEFT - PAD_RIGHT;

const chart = computed(() => {
  const data = props.throughput || [];
  if (!data.length) return null;

  const maxVal = Math.max(
    1,
    ...data.map(d => Math.max(d.created || 0, d.approved || 0, d.rejected || 0))
  );

  function toPoints(key) {
    return data.map((d, i) => {
      const x = PAD_LEFT + (data.length > 1 ? (i / (data.length - 1)) * CHART_W : CHART_W / 2);
      const v = d[key] || 0;
      const y = PAD_TOP + CHART_H - (v / maxVal) * CHART_H;
      return `${x.toFixed(1)},${y.toFixed(1)}`;
    }).join(' ');
  }

  // X-axis labels: show at most 7 evenly spaced labels
  const labelStep = Math.max(1, Math.ceil(data.length / 7));
  const xLabels = data
    .map((d, i) => ({ i, d }))
    .filter(({ i }) => i % labelStep === 0 || i === data.length - 1)
    .map(({ i, d }) => ({
      x: PAD_LEFT + (data.length > 1 ? (i / (data.length - 1)) * CHART_W : CHART_W / 2),
      label: fmtLabel(d.date),
    }));

  return {
    created: toPoints('created'),
    approved: toPoints('approved'),
    rejected: toPoints('rejected'),
    xLabels,
    maxVal,
  };
});

function fmtLabel(date) {
  if (!date) return '';
  try {
    if (date.length === 7) {
      const [y, m] = date.split('-');
      return new Date(Number(y), Number(m) - 1).toLocaleDateString('es-CO', { month: 'short' });
    }
    const d = new Date(date + 'T00:00:00');
    return d.toLocaleDateString('es-CO', { day: '2-digit', month: 'short' });
  } catch { return date; }
}
</script>

<template>
  <article class="throughput-card">
    <header class="chart-head">
      <h2 class="chart-title">VOLUMEN DE TICKETS</h2>
      <div class="chart-legend">
        <span class="legend-item">
          <span class="legend-dot legend-dot--gray"></span>CREADOS
        </span>
        <span class="legend-item">
          <span class="legend-dot legend-dot--green"></span>APROBADOS
        </span>
        <span class="legend-item">
          <span class="legend-dot legend-dot--red"></span>RECHAZADOS
        </span>
      </div>
    </header>

    <div v-if="!chart" class="chart-empty">
      <span class="chart-empty-num">—</span>
      <p class="chart-empty-msg">"Sin actividad en el periodo seleccionado."</p>
    </div>

    <div v-else class="chart-wrap">
      <svg
        :viewBox="`0 0 ${SVG_W} ${SVG_H}`"
        preserveAspectRatio="none"
        class="chart-svg"
        aria-hidden="true"
      >
        <!-- Grid lines -->
        <line
          v-for="n in 4"
          :key="n"
          :x1="PAD_LEFT" :x2="SVG_W - PAD_RIGHT"
          :y1="PAD_TOP + (CHART_H / 4) * (n - 1)"
          :y2="PAD_TOP + (CHART_H / 4) * (n - 1)"
          class="grid-line"
        />

        <!-- Lines -->
        <polyline :points="chart.created"  class="line line--gray"  fill="none" />
        <polyline :points="chart.approved" class="line line--green" fill="none" />
        <polyline :points="chart.rejected" class="line line--red"   fill="none" />

        <!-- X-axis labels -->
        <text
          v-for="lbl in chart.xLabels"
          :key="lbl.label"
          :x="lbl.x"
          :y="SVG_H - 4"
          class="axis-label"
          text-anchor="middle"
        >{{ lbl.label }}</text>
      </svg>
    </div>
  </article>
</template>

<style scoped>
.throughput-card {
    border-radius: var(--r-md, 16px);
    border: 1px solid var(--c-border);
    background: rgba(17, 17, 17, 0.7);
    padding: 18px;
}
.chart-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 14px;
}
.chart-title {
    font-family: var(--font-display);
    font-size: 13px;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    color: var(--c-text);
    margin: 0;
}
.chart-legend {
    display: flex;
    align-items: center;
    gap: 14px;
}
.legend-item {
    display: flex;
    align-items: center;
    gap: 5px;
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    color: var(--c-text-3);
}
.legend-dot {
    width: 8px;
    height: 8px;
    border-radius: 2px;
    flex-shrink: 0;
}
.legend-dot--gray  { background: rgba(250,250,250,0.3); }
.legend-dot--green { background: #34D399; }
.legend-dot--red   { background: #F87171; }

.chart-wrap {
    width: 100%;
    overflow-x: auto;
}
.chart-svg {
    width: 100%;
    min-width: 260px;
    height: 100px;
    display: block;
}

.grid-line {
    stroke: rgba(255,255,255,0.04);
    stroke-width: 1;
}
.line {
    stroke-width: 1.5;
    stroke-linecap: round;
    stroke-linejoin: round;
    vector-effect: non-scaling-stroke;
}
.line--gray  { stroke: rgba(250,250,250,0.28); }
.line--green { stroke: #34D399; }
.line--red   { stroke: #F87171; }

.axis-label {
    font-family: var(--font-display);
    font-size: 8px;
    fill: var(--c-text-3);
    letter-spacing: 0.08em;
}

.chart-empty {
    padding: 24px 8px 18px;
    text-align: center;
}
.chart-empty-num {
    display: block;
    font-family: var(--font-display);
    font-size: 56px;
    color: var(--c-surface-2);
    letter-spacing: 0.8px;
    line-height: 1;
    margin-bottom: 12px;
    user-select: none;
}
.chart-empty-msg {
    font-family: var(--font-editorial, var(--font-sans));
    font-style: italic;
    font-size: 12px;
    color: var(--c-text-3);
    line-height: 1.55;
    margin: 0;
}

@media (prefers-reduced-motion: reduce) {
    .line { transition: none !important; }
}
</style>
