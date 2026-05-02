<script setup>
import { computed } from 'vue';

const props = defineProps({
  reasons: { type: Array, default: () => [] },
});

const COLORS = ['#DC2626', '#F59E0B', '#3B82F6', '#A78BFA', '#10B981'];

const donut = computed(() => {
  const data = props.reasons || [];
  const total = data.reduce((s, r) => s + (r.count || 0), 0);
  if (total === 0 || data.length < 2) return null;

  let cursor = 0;
  const stops = [];
  const items = data.map((r, i) => {
    const color = COLORS[i % COLORS.length];
    const start = cursor;
    const end   = cursor + (r.pct || 0);
    stops.push(`${color} ${start}% ${end}%`);
    cursor = end;
    return { ...r, color };
  });

  return {
    conic: `conic-gradient(${stops.join(', ')})`,
    items,
    total,
  };
});

const isEmpty = computed(() => !donut.value);
</script>

<template>
  <section class="reasons-card">
    <header class="reasons-head">
      <h2 class="reasons-title">RAZONES DE RECHAZO</h2>
      <span v-if="donut" class="reasons-sub">{{ donut.total }} rechazados</span>
    </header>

    <!-- Empty state -->
    <div v-if="isEmpty" class="reasons-empty">
      <div class="reasons-empty-num">—</div>
      <p class="reasons-empty-msg">
        "Sin rechazos con razon registrada en el periodo. Cada rechazo con codigo aporta al analisis."
      </p>
    </div>

    <!-- Donut + legend -->
    <div v-else class="reasons-content">
      <div class="donut-wrap">
        <div class="donut" :style="{ background: donut.conic }"></div>
        <div class="donut-hole"></div>
      </div>

      <ul class="reasons-legend">
        <li
          v-for="item in donut.items"
          :key="item.code"
          class="legend-row"
        >
          <span class="legend-dot" :style="{ background: item.color }"></span>
          <span class="legend-name">{{ item.label }}</span>
          <span class="legend-count">{{ item.count }}</span>
          <span class="legend-pct">{{ item.pct }}%</span>
        </li>
      </ul>
    </div>
  </section>
</template>

<style scoped>
.reasons-card {
    border-radius: var(--r-md, 16px);
    border: 1px solid var(--c-border);
    background: rgba(17, 17, 17, 0.7);
    padding: 18px;
}
.reasons-head {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    margin-bottom: 14px;
    gap: 8px;
}
.reasons-title {
    font-family: var(--font-display);
    font-size: 13px;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    color: var(--c-text);
    margin: 0;
}
.reasons-sub {
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    color: var(--c-text-3);
}

/* Empty */
.reasons-empty {
    padding: 18px 8px 14px;
    text-align: center;
}
.reasons-empty-num {
    font-family: var(--font-display);
    font-size: 56px;
    color: var(--c-surface-2);
    letter-spacing: 0.8px;
    line-height: 1;
    margin-bottom: 12px;
    user-select: none;
}
.reasons-empty-msg {
    font-family: var(--font-editorial, var(--font-sans));
    font-style: italic;
    font-size: 12px;
    color: var(--c-text-3);
    line-height: 1.55;
    margin: 0;
    text-wrap: balance;
}

/* Donut */
.reasons-content {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    flex-wrap: wrap;
}
.donut-wrap {
    position: relative;
    flex-shrink: 0;
    width: 80px;
    height: 80px;
}
.donut {
    width: 80px;
    height: 80px;
    border-radius: 50%;
}
.donut-hole {
    position: absolute;
    inset: 20px;
    border-radius: 50%;
    background: #111111;
}

/* Legend */
.reasons-legend {
    flex: 1;
    min-width: 0;
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.legend-row {
    display: flex;
    align-items: center;
    gap: 7px;
}
.legend-dot {
    width: 8px;
    height: 8px;
    border-radius: 2px;
    flex-shrink: 0;
}
.legend-name {
    flex: 1;
    font-size: 11px;
    color: var(--c-text-2);
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}
.legend-count {
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 700;
    color: var(--c-text);
    font-variant-numeric: tabular-nums;
    flex-shrink: 0;
}
.legend-pct {
    font-family: var(--font-display);
    font-size: 9px;
    color: var(--c-text-3);
    width: 34px;
    text-align: right;
    flex-shrink: 0;
}
</style>
