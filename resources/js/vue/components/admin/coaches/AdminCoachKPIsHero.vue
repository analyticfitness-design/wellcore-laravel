<script setup>
import { computed } from 'vue';
import { useViewportAnimate } from '../../../composables/dashboard/useViewportAnimate';

const props = defineProps({
    kpis: { type: Object, required: true },
});

const RING_RADIUS = 22;
const RING_STROKE = 4;
const RING_CIRCUMFERENCE = 2 * Math.PI * RING_RADIUS;

const { targetRef, visible } = useViewportAnimate({ threshold: 0.2 });

function strokeOffset(pct) {
    if (!visible.value) return RING_CIRCUMFERENCE;
    const clamped = Math.max(0, Math.min(100, pct));
    return RING_CIRCUMFERENCE - (RING_CIRCUMFERENCE * clamped / 100);
}

const metrics = computed(() => {
    const k = props.kpis || {};
    const total = Number(k.totalCoaches || 0);
    const coverage = Number(k.profileCoverage || 0);
    const avg = Number(k.avgClientsPerCoach || 0);
    const tickets = Number(k.ticketsThisMonth || 0);
    const top = k.topPerformer;

    return [
        {
            id: 'team',
            label: 'COACHES ACTIVOS',
            value: total,
            unit: '',
            sub: `${k.totalClients || 0} clientes asignados`,
            variant: total > 0 ? 'healthy' : 'warn',
            ringPct: total > 0 ? 100 : 0,
        },
        {
            id: 'avg',
            label: 'CARGA PROMEDIO',
            value: avg.toFixed(1),
            unit: '',
            sub: 'clientes por coach',
            variant: avg > 0 ? 'info' : 'warn',
            ringPct: Math.min(100, avg * 8),
        },
        {
            id: 'coverage',
            label: 'PERFIL PUBLICO',
            value: coverage,
            unit: '%',
            sub: `${k.withProfile || 0} con perfil completo`,
            variant: coverage >= 80 ? 'healthy' : (coverage >= 50 ? 'warn' : 'urgent'),
            ringPct: coverage,
        },
        {
            id: 'top',
            label: 'TOP DEL MES',
            value: top || '—',
            unit: '',
            sub: tickets > 0
                ? `${tickets} tickets cerrados`
                : 'sin tickets cerrados',
            variant: top ? 'healthy' : 'info',
            ringPct: top ? Math.min(100, tickets * 5) : 0,
            isText: true,
        },
    ];
});

function ringColorClass(variant) {
    switch (variant) {
        case 'urgent': return 'ring-fill--red';
        case 'warn': return 'ring-fill--amber';
        case 'healthy': return 'ring-fill--green';
        case 'info': return 'ring-fill--blue';
        default: return 'ring-fill--blue';
    }
}
</script>

<template>
  <div ref="targetRef" class="coach-kpis">
    <div
      v-for="m in metrics"
      :key="m.id"
      class="metric-card"
      :class="`metric-card--${m.variant}`"
    >
      <div class="metric-ring-wrap">
        <svg :width="56" :height="56" class="metric-ring" aria-hidden="true">
          <circle
            class="ring-track"
            :cx="28"
            :cy="28"
            :r="RING_RADIUS"
            :stroke-width="RING_STROKE"
          />
          <circle
            class="ring-fill"
            :class="ringColorClass(m.variant)"
            :cx="28"
            :cy="28"
            :r="RING_RADIUS"
            :stroke-width="RING_STROKE"
            :stroke-dasharray="RING_CIRCUMFERENCE"
            :stroke-dashoffset="strokeOffset(m.ringPct)"
          />
        </svg>
      </div>

      <span class="metric-label">{{ m.label }}</span>
      <span
        class="metric-value"
        :class="[
          `metric-value--${m.variant}`,
          m.isText ? 'metric-value--text' : ''
        ]"
        :title="String(m.value)"
      >
        <span>{{ m.value }}</span>
        <span v-if="m.unit" class="metric-value-unit">{{ m.unit }}</span>
      </span>
      <span v-if="m.sub" class="metric-sub">{{ m.sub }}</span>
    </div>
  </div>
</template>

<style scoped>
.coach-kpis {
    display: flex;
    gap: 10px;
    overflow-x: auto;
    overflow-y: visible;
    scroll-snap-type: x mandatory;
    scrollbar-width: none;
    padding-bottom: 4px;
}
.coach-kpis::-webkit-scrollbar { display: none; }

@media (min-width: 1024px) {
    .coach-kpis {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        overflow: visible;
        gap: 12px;
        padding-bottom: 0;
    }
}

.metric-card {
    flex: 0 0 220px;
    border-radius: 14px;
    padding: 16px 14px 14px;
    display: flex;
    flex-direction: column;
    gap: 0;
    position: relative;
    overflow: hidden;
    scroll-snap-align: start;
    min-height: 124px;
    min-width: 0;
}
@media (min-width: 1024px) {
    .metric-card { flex: none; min-height: 120px; padding: 18px 16px 14px; }
}
.metric-card > * { min-width: 0; max-width: 100%; }

.metric-card--urgent  { background: rgba(220, 38, 38, 0.07); border: 1px solid rgba(220, 38, 38, 0.22); }
.metric-card--warn    { background: rgba(245, 158, 11, 0.07); border: 1px solid rgba(245, 158, 11, 0.2); }
.metric-card--healthy { background: rgba(16, 185, 129, 0.07); border: 1px solid rgba(16, 185, 129, 0.2); }
.metric-card--info    { background: rgba(59, 130, 246, 0.07); border: 1px solid rgba(59, 130, 246, 0.2); }

.metric-ring-wrap { position: absolute; top: 12px; right: 12px; }
.metric-ring { transform: rotate(-90deg); }
.ring-track { fill: none; stroke: rgba(255, 255, 255, 0.06); }
.ring-fill {
    fill: none;
    stroke-linecap: round;
    transition: stroke-dashoffset 1.2s var(--ease-out, ease);
}
.ring-fill--red    { stroke: var(--color-wc-red-text, #F87171); }
.ring-fill--amber  { stroke: var(--color-wc-amber-text, #FCD34D); }
.ring-fill--green  { stroke: var(--color-wc-green-text, #34D399); }
.ring-fill--blue   { stroke: var(--color-wc-blue-text, #60A5FA); }

.metric-label {
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
    margin-bottom: 8px;
    padding-right: 60px;
    line-height: 1.4;
    min-height: 12px;
}
.metric-value {
    font-family: var(--font-display);
    font-size: 32px;
    letter-spacing: 0.03em;
    line-height: 1;
    margin-bottom: 4px;
    display: inline-flex;
    align-items: baseline;
    gap: 4px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.metric-value--text {
    font-size: 18px;
    letter-spacing: 0.06em;
    text-transform: uppercase;
}
.metric-value--urgent  { color: var(--color-wc-red-text, #F87171); }
.metric-value--warn    { color: var(--color-wc-amber-text, #FCD34D); }
.metric-value--healthy { color: var(--color-wc-green-text, #34D399); }
.metric-value--info    { color: var(--color-wc-text); }
.metric-value-unit {
    font-size: 16px;
    opacity: 0.7;
    font-family: var(--font-display);
}
.metric-sub {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    color: var(--color-wc-text-tertiary);
    margin-top: 2px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
@media (min-width: 1024px) {
    .metric-value { font-size: 36px; }
    .metric-value--text { font-size: 20px; }
}

@media (prefers-reduced-motion: reduce) {
    .ring-fill { transition: none !important; }
}
</style>
