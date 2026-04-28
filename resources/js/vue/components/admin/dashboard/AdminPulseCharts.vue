<script setup>
import { computed } from 'vue';

const props = defineProps({
  // [{ month: '2026-04', total: 199800 }, ...]
  revenueData: { type: Array, default: () => [] },
  // [{ name: 'Entrenamiento', count: 21 }, ...]
  planDistribution: { type: Array, default: () => [] },
  // { activo, inactivo, pendiente, suspendido, total }
  clientBreakdown: { type: Object, default: () => ({}) },
});

// ── MRR Chart: bars verticales ────────────────────────────────────────────
const mrrChart = computed(() => {
  const data = props.revenueData || [];
  if (!data.length) return { bars: [], maxTotal: 0 };
  const maxTotal = Math.max(...data.map(d => Number(d.total || 0)), 1);
  const bars = data.map((d, i) => ({
    label: d.month?.slice(5) || `M${i + 1}`,  // YYYY-MM → MM
    total: Number(d.total || 0),
    pct: (Number(d.total || 0) / maxTotal) * 100,
    valueShort: formatShortCOP(d.total),
  }));
  return { bars, maxTotal };
});

// ── Donut: plan distribution con conic-gradient ──────────────────────────
const planColors = ['#DC2626', '#F59E0B', '#3B82F6', '#10B981', '#A78BFA', '#EC4899'];
const donut = computed(() => {
  const data = props.planDistribution || [];
  const total = data.reduce((sum, d) => sum + Number(d.count || 0), 0);
  if (total === 0 || data.length === 0) return { conic: 'transparent', items: [], total: 0 };

  let cursor = 0;
  const stops = [];
  const items = data.map((d, i) => {
    const pct = (Number(d.count || 0) / total) * 100;
    const color = planColors[i % planColors.length];
    const start = cursor;
    const end = cursor + pct;
    stops.push(`${color} ${start}% ${end}%`);
    cursor = end;
    return { name: d.name, count: Number(d.count || 0), pct, color };
  });
  return {
    conic: `conic-gradient(${stops.join(', ')})`,
    items,
    total,
  };
});

// ── Client breakdown stacked bar ──────────────────────────────────────────
const breakdown = computed(() => {
  const b = props.clientBreakdown || {};
  const total = Number(b.total || 0);
  if (total === 0) return { segments: [], total: 0, ratioPct: 0, activo: 0, inactivo: 0, pendiente: 0, suspendido: 0 };

  const activo = Number(b.activo || 0);
  const inactivo = Number(b.inactivo || 0);
  const pendiente = Number(b.pendiente || 0);
  const suspendido = Number(b.suspendido || 0);

  return {
    total, activo, inactivo, pendiente, suspendido,
    ratioPct: total > 0 ? Math.round((activo / total) * 100) : 0,
    segments: [
      { key: 'activo',     label: 'ACTIVO',     count: activo,     pct: (activo / total) * 100,     color: 'var(--color-wc-green-text, #34D399)' },
      { key: 'inactivo',   label: 'INACTIVO',   count: inactivo,   pct: (inactivo / total) * 100,   color: 'var(--color-wc-text-tertiary, #737373)' },
      { key: 'pendiente',  label: 'PENDIENTE',  count: pendiente,  pct: (pendiente / total) * 100,  color: 'var(--color-wc-amber-text, #FCD34D)' },
      { key: 'suspendido', label: 'SUSPENDIDO', count: suspendido, pct: (suspendido / total) * 100, color: 'var(--color-wc-red-text, #F87171)' },
    ].filter(s => s.count > 0),
  };
});

function formatShortCOP(n) {
  const num = Number(n || 0);
  if (num === 0) return '$0';
  if (num >= 1_000_000) return `$${(num / 1_000_000).toFixed(1)}M`;
  if (num >= 1_000) return `$${(num / 1_000).toFixed(0)}k`;
  return `$${num}`;
}
</script>

<template>
  <section class="pulse-charts">
    <!-- MRR bars -->
    <article class="chart-card chart-card--mrr">
      <header class="chart-header">
        <h2 class="chart-title">MRR HISTORICO</h2>
        <span class="chart-period">ULTIMOS {{ mrrChart.bars.length }} MESES</span>
      </header>
      <div v-if="mrrChart.bars.length" class="mrr-chart">
        <div
          v-for="bar in mrrChart.bars"
          :key="bar.label"
          class="mrr-bar-wrap"
        >
          <span class="mrr-bar-value">{{ bar.valueShort }}</span>
          <div
            class="mrr-bar"
            :style="{ height: `${Math.max(bar.pct, 4)}%`, background: bar.total > 0 ? 'linear-gradient(180deg, var(--color-wc-accent, #DC2626), rgba(220,38,38,0.4))' : 'rgba(255,255,255,0.05)' }"
          ></div>
          <span class="mrr-bar-label">{{ bar.label }}</span>
        </div>
      </div>
      <p v-else class="chart-empty">Sin datos historicos.</p>
    </article>

    <!-- Donut plan distribution -->
    <article class="chart-card chart-card--donut">
      <header class="chart-header">
        <h2 class="chart-title">DISTRIBUCION PLANES</h2>
        <span class="chart-period">{{ donut.total }} ASIGNACIONES</span>
      </header>
      <div v-if="donut.items.length" class="donut-row">
        <div class="donut-svg-wrap" :style="{ background: donut.conic }">
          <div class="donut-hole">
            <div class="donut-hole-num">{{ donut.total }}</div>
            <div class="donut-hole-label">TOTAL</div>
          </div>
        </div>
        <ul class="donut-legend">
          <li
            v-for="item in donut.items"
            :key="item.name"
            class="donut-item"
          >
            <span class="donut-dot" :style="{ background: item.color }"></span>
            <span class="donut-item-name">{{ item.name }}</span>
            <span class="donut-item-count">{{ item.count }}</span>
          </li>
        </ul>
      </div>
      <p v-else class="chart-empty">Sin asignaciones.</p>
    </article>

    <!-- Client breakdown stacked bar -->
    <article class="chart-card chart-card--breakdown">
      <header class="breakdown-header">
        <div>
          <span class="breakdown-total">{{ breakdown.total }}</span>
          <span class="breakdown-total-sub">CLIENTES</span>
        </div>
        <div class="breakdown-ratio">
          <div class="breakdown-ratio-num">{{ breakdown.ratioPct }}%</div>
          <div class="breakdown-ratio-label">ACTIVOS</div>
        </div>
      </header>
      <div v-if="breakdown.segments.length" class="stacked-bar">
        <span
          v-for="seg in breakdown.segments"
          :key="seg.key"
          class="stacked-seg"
          :style="{ width: `${seg.pct}%`, background: seg.color }"
          :title="`${seg.label}: ${seg.count}`"
        ></span>
      </div>
      <ul v-if="breakdown.segments.length" class="breakdown-legend">
        <li
          v-for="seg in breakdown.segments"
          :key="seg.key"
          class="breakdown-item"
        >
          <span class="breakdown-item-dot" :style="{ background: seg.color }"></span>
          <span>{{ seg.label }} {{ seg.count }}</span>
        </li>
      </ul>
      <p v-else class="chart-empty">Sin clientes registrados.</p>
    </article>
  </section>
</template>

<style scoped>
/* ============================================================================
   AdminPulseCharts — MRR bars + donut planes + client breakdown stacked bar.
   Mobile: stack vertical. Desktop: grid 2-col superior + breakdown abajo full-width.
   ============================================================================ */

.pulse-charts {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
}
@media (min-width: 1024px) {
    .pulse-charts {
        grid-template-columns: 1fr 1fr;
        grid-template-areas:
            "mrr donut"
            "breakdown breakdown";
    }
    .chart-card--mrr { grid-area: mrr; }
    .chart-card--donut { grid-area: donut; }
    .chart-card--breakdown { grid-area: breakdown; }
}

.chart-card {
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17, 17, 17, 0.7);
    padding: 18px;
}
.chart-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
}
.chart-title {
    font-family: var(--font-display);
    font-size: 13px;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--color-wc-text);
    margin: 0;
}
.chart-period {
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}
.chart-empty {
    font-family: var(--font-editorial, 'Fraunces', Georgia, serif);
    font-style: italic;
    font-size: 12px;
    color: var(--color-wc-text-tertiary);
    text-align: center;
    padding: 24px 0;
    margin: 0;
}

/* ── MRR bars ─────────────────────────────────────────────────────────── */
.mrr-chart {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    height: 100px;
    padding-top: 16px;
}
.mrr-bar-wrap {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    height: 100%;
    justify-content: flex-end;
    min-width: 0;
}
.mrr-bar-value {
    font-family: var(--font-data, 'Barlow', sans-serif);
    font-size: 10px;
    font-weight: 700;
    color: var(--color-wc-text);
    text-align: center;
    margin-bottom: 2px;
}
.mrr-bar {
    width: 100%;
    border-radius: 4px 4px 0 0;
    transition: height 0.8s var(--ease-out, ease);
    min-height: 4px;
}
.mrr-bar-label {
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    letter-spacing: 0.1em;
    color: var(--color-wc-text-tertiary);
    text-align: center;
}

/* ── Donut con conic-gradient ──────────────────────────────────────────── */
.donut-row {
    display: flex;
    gap: 16px;
    align-items: center;
}
.donut-svg-wrap {
    flex-shrink: 0;
    width: 88px;
    height: 88px;
    border-radius: 50%;
    position: relative;
}
.donut-hole {
    position: absolute;
    inset: 14px;
    border-radius: 50%;
    background: var(--color-wc-bg-secondary, #111);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}
.donut-hole-num {
    font-family: var(--font-display);
    font-size: 22px;
    color: var(--color-wc-text);
    letter-spacing: 0.02em;
    line-height: 1;
}
.donut-hole-label {
    font-family: var(--font-mono, monospace);
    font-size: 7px;
    color: var(--color-wc-text-tertiary);
    letter-spacing: 0.18em;
    text-transform: uppercase;
}
.donut-legend {
    flex: 1;
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-width: 0;
}
.donut-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: var(--color-wc-text-secondary);
}
.donut-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}
.donut-item-name {
    flex: 1;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}
.donut-item-count {
    font-family: var(--font-data, 'Barlow', sans-serif);
    font-weight: 700;
    font-size: 13px;
    color: var(--color-wc-text);
}

/* ── Breakdown stacked bar ─────────────────────────────────────────────── */
.breakdown-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    margin-bottom: 10px;
}
.breakdown-total {
    font-family: var(--font-display);
    font-size: 34px;
    letter-spacing: 0.03em;
    line-height: 1;
    color: var(--color-wc-text);
}
.breakdown-total-sub {
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    letter-spacing: 0.18em;
    color: var(--color-wc-text-tertiary);
    text-transform: uppercase;
    margin-left: 6px;
}
.breakdown-ratio {
    text-align: right;
}
.breakdown-ratio-num {
    font-family: var(--font-display);
    font-size: 18px;
    color: var(--color-wc-green-text, #34D399);
    letter-spacing: 0.03em;
    line-height: 1;
}
.breakdown-ratio-label {
    font-family: var(--font-mono, monospace);
    font-size: 7px;
    color: var(--color-wc-text-tertiary);
    text-transform: uppercase;
    letter-spacing: 0.18em;
}
.stacked-bar {
    height: 8px;
    border-radius: 99px;
    overflow: hidden;
    display: flex;
    gap: 2px;
    margin-bottom: 8px;
    background: rgba(255, 255, 255, 0.03);
}
.stacked-seg {
    border-radius: 99px;
    transition: width 0.8s var(--ease-out, ease);
    min-width: 4px;
}
.breakdown-legend {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    gap: 14px;
    flex-wrap: wrap;
}
.breakdown-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.1em;
    color: var(--color-wc-text-tertiary);
    text-transform: uppercase;
}
.breakdown-item-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
}
</style>
