<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    data:     { type: Array, default: () => [] },
    maxCount: { type: Number, default: 1 },
});

const DAYS = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
const HOURS = Array.from({ length: 24 }, (_, i) => i);

const cellMap = computed(() => {
    const m = {};
    for (const r of props.data) {
        m[`${r.day}-${r.hour}`] = r.count;
    }
    return m;
});

const hasData = computed(() => props.data.length > 0);

function cellCount(day, hour) {
    return cellMap.value[`${day}-${hour}`] ?? 0;
}
function cellOpacity(day, hour) {
    const c = cellCount(day, hour);
    if (!c) return 0;
    return Math.max(0.05, Math.min(0.7, c / Math.max(1, props.maxCount)));
}

const hoveredCell = ref(null);

function onCellEnter(day, hour) {
    hoveredCell.value = { day, hour };
}
function onCellLeave() {
    hoveredCell.value = null;
}
function isHovered(day, hour) {
    return hoveredCell.value?.day === day && hoveredCell.value?.hour === hour;
}
function tooltipLabel(day, hour) {
    const n = cellCount(day, hour);
    return `${DAYS[day - 1]} ${String(hour).padStart(2, '0')}:00 — ${n} mensaje${n !== 1 ? 's' : ''}`;
}
</script>

<template>
  <div class="heatmap-card">
    <header class="hm-head">
      <h2 class="hm-title">ACTIVIDAD DÍA × HORA</h2>
      <span class="hm-eyebrow">HORA LOCAL BOGOTÁ</span>
    </header>

    <div v-if="!hasData" class="hm-empty">
      <div class="empty-num">—</div>
      <p class="empty-msg">"Sin actividad registrada en el período."</p>
    </div>

    <template v-else>
      <div class="hm-grid-wrap">
        <!-- Hour labels top -->
        <div class="hm-hour-labels" aria-hidden="true">
          <span class="hm-day-spacer"></span>
          <span
            v-for="h in HOURS" :key="`hl-${h}`"
            class="hm-hour-label"
          >{{ h % 4 === 0 ? String(h).padStart(2,'0') : '' }}</span>
        </div>

        <!-- Grid rows -->
        <div
          v-for="d in 7" :key="`day-${d}`"
          class="hm-row"
        >
          <span class="hm-day-label" aria-label="Día de la semana">{{ DAYS[d - 1] }}</span>
          <div
            v-for="h in HOURS" :key="`cell-${d}-${h}`"
            class="hm-cell"
            :class="{ 'hm-cell--hovered': isHovered(d, h) }"
            :style="{ '--cell-opacity': cellOpacity(d, h) }"
            :title="tooltipLabel(d, h)"
            :aria-label="tooltipLabel(d, h)"
            @mouseenter="onCellEnter(d, h)"
            @mouseleave="onCellLeave"
          ></div>
        </div>
      </div>

      <!-- Tooltip visible -->
      <div v-if="hoveredCell" class="hm-tooltip" role="status" aria-live="polite">
        {{ tooltipLabel(hoveredCell.day, hoveredCell.hour) }}
      </div>

      <!-- Legend scale -->
      <div class="hm-legend" aria-label="Escala de intensidad">
        <span class="legend-label">menos</span>
        <div class="legend-scale">
          <div v-for="i in 7" :key="`ls-${i}`" class="legend-cell" :style="{ '--cell-opacity': (i / 7) * 0.7 }"></div>
        </div>
        <span class="legend-label">más</span>
      </div>
    </template>
  </div>
</template>

<style scoped>
.heatmap-card {
    border-radius: var(--r-md, 16px);
    border: 1px solid var(--c-border);
    background: rgba(17,17,17,0.7);
    padding: 18px;
    overflow: hidden;
}
.hm-head { margin-bottom: 14px; }
.hm-title {
    font-family: var(--font-display);
    font-size: 9px; letter-spacing: 1.8px; text-transform: uppercase;
    color: var(--c-text); margin: 0 0 2px;
}
.hm-eyebrow {
    font-family: var(--font-display);
    font-size: 7px; letter-spacing: 1.6px; text-transform: uppercase;
    color: var(--c-text-3);
}
.hm-empty { padding: 24px 8px; text-align: center; }
.empty-num {
    font-family: var(--font-display); font-size: 56px;
    color: var(--c-surface-2); letter-spacing: 0.1em;
    line-height: 1; margin-bottom: 12px; user-select: none;
}
.empty-msg {
    font-family: var(--font-editorial, var(--font-sans));
    font-style: italic; font-size: 12px;
    color: var(--c-text-3); margin: 0;
}
.hm-grid-wrap {
    width: 100%;
    overflow-x: auto;
    scrollbar-width: thin;
    scrollbar-color: rgba(255,255,255,0.1) transparent;
}
.hm-hour-labels {
    display: grid;
    grid-template-columns: 28px repeat(24, minmax(12px, 1fr));
    gap: 2px;
    margin-bottom: 2px;
    min-width: 360px;
}
.hm-day-spacer { width: 28px; }
.hm-hour-label {
    font-family: var(--font-display);
    font-size: 7px; letter-spacing: 0.05em;
    color: var(--c-text-3);
    text-align: center;
    min-height: 10px;
}
.hm-row {
    display: grid;
    grid-template-columns: 28px repeat(24, minmax(12px, 1fr));
    gap: 2px;
    margin-bottom: 2px;
    min-width: 360px;
}
.hm-day-label {
    font-family: var(--font-display);
    font-size: 8px; letter-spacing: 0.1em;
    color: var(--c-text-3);
    display: flex; align-items: center;
    padding-right: 4px;
}
.hm-cell {
    aspect-ratio: 1;
    border-radius: 2px;
    background: rgba(220, 38, 38, var(--cell-opacity, 0));
    border: 1px solid rgba(255,255,255,0.03);
    cursor: default;
    transition: transform 0.1s var(--ease-out, ease), border-color 0.1s var(--ease-out, ease);
}
.hm-cell--hovered {
    transform: scale(1.25);
    border-color: rgba(255,255,255,0.18);
    z-index: 1;
    position: relative;
}
.hm-tooltip {
    margin-top: 10px;
    font-family: var(--font-display);
    font-size: 9px; letter-spacing: 1.0px;
    color: var(--c-text-2);
    min-height: 14px;
}
.hm-legend {
    display: flex; align-items: center; gap: 6px;
    margin-top: 10px;
}
.legend-label {
    font-family: var(--font-display);
    font-size: 8px; letter-spacing: 0.1em; text-transform: uppercase;
    color: var(--c-text-3);
}
.legend-scale { display: flex; gap: 2px; }
.legend-cell {
    width: 12px; height: 12px;
    border-radius: 2px;
    background: rgba(220, 38, 38, var(--cell-opacity, 0));
    border: 1px solid rgba(255,255,255,0.03);
}
@media (prefers-reduced-motion: reduce) {
    .hm-cell { transition: none !important; }
}
</style>
