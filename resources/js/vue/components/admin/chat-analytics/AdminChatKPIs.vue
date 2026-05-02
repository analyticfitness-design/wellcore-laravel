<script setup>
import { computed } from 'vue';
import { useViewportAnimate } from '../../../composables/dashboard/useViewportAnimate';

const props = defineProps({
    kpis: { type: Object, default: null },
    loading: { type: Boolean, default: false },
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

function formatResponseTime(minutes) {
    if (!minutes) return '—';
    if (minutes < 60) return `${minutes} min`;
    return `${(minutes / 60).toFixed(1)} h`;
}

function formatPeakHour(peakHour) {
    if (!peakHour) return '—';
    const h = peakHour.hour ?? 0;
    return `${String(h).padStart(2, '0')}:00`;
}

function ringColorClass(variant) {
    switch (variant) {
        case 'urgent': return 'ring-fill--red';
        case 'warn':   return 'ring-fill--amber';
        case 'green':  return 'ring-fill--green';
        case 'info':   return 'ring-fill--blue';
        default:       return 'ring-fill--blue';
    }
}

const metrics = computed(() => {
    const k = props.kpis || {};
    const vol = Number(k.msg_volume ?? 0);
    const avgR = Number(k.avg_response_minutes ?? 0);
    const peak = k.peak_hour;

    return [
        {
            id: 'volume',
            label: 'VOLUMEN MENSAJES',
            value: vol.toLocaleString('es-CO'),
            sub: `${Number(k.coach_to_client ?? 0).toLocaleString('es-CO')} coach → ${Number(k.client_to_coach ?? 0).toLocaleString('es-CO')} cliente`,
            variant: vol > 0 ? 'info' : 'warn',
            ringPct: Math.min(100, (vol / 500) * 100),
        },
        {
            id: 'response',
            label: 'TIEMPO RESPUESTA',
            value: formatResponseTime(avgR),
            sub: 'promedio del período',
            variant: avgR === 0 ? 'warn' : avgR < 30 ? 'green' : avgR < 120 ? 'info' : 'urgent',
            ringPct: avgR === 0 ? 0 : Math.max(10, 100 - Math.min(100, (avgR / 120) * 100)),
            isText: true,
        },
        {
            id: 'satisfaction',
            label: 'SATISFACCIÓN',
            value: k.satisfaction_score !== null ? String(k.satisfaction_score) : '—',
            sub: k.satisfaction_score !== null ? 'sobre 5.0' : null,
            tooltip: k.satisfaction_score === null ? 'Encuesta de satisfacción aún no implementada' : null,
            variant: 'info',
            ringPct: k.satisfaction_score !== null ? (k.satisfaction_score / 5) * 100 : 0,
            isText: true,
        },
        {
            id: 'peak',
            label: 'HORA PICO',
            value: formatPeakHour(peak),
            sub: peak ? `${Number(peak.count ?? 0).toLocaleString('es-CO')} mensajes` : 'sin datos',
            variant: peak ? 'green' : 'warn',
            ringPct: peak ? Math.min(100, (Number(peak.count ?? 0) / 50) * 100) : 0,
            isText: true,
        },
    ];
});
</script>

<template>
  <div ref="targetRef" class="chat-kpis" role="region" aria-label="KPIs de chat analytics">
    <div
      v-for="m in metrics"
      :key="m.id"
      class="metric-card"
      :class="`metric-card--${m.variant}`"
      :title="m.tooltip || undefined"
    >
      <div class="metric-ring-wrap" aria-hidden="true">
        <svg :width="56" :height="56" class="metric-ring">
          <circle class="ring-track" :cx="28" :cy="28" :r="RING_RADIUS" :stroke-width="RING_STROKE" />
          <circle
            class="ring-fill"
            :class="ringColorClass(m.variant)"
            :cx="28" :cy="28" :r="RING_RADIUS" :stroke-width="RING_STROKE"
            :stroke-dasharray="RING_CIRCUMFERENCE"
            :stroke-dashoffset="strokeOffset(m.ringPct)"
          />
        </svg>
      </div>
      <span class="metric-label">{{ m.label }}</span>
      <span
        class="metric-value"
        :class="[`metric-value--${m.variant}`, m.isText ? 'metric-value--text' : '']"
      >{{ m.value }}</span>
      <span v-if="m.sub" class="metric-sub">{{ m.sub }}</span>
      <span v-if="m.tooltip" class="metric-tooltip-hint" aria-label="Nota">*</span>
    </div>
  </div>
</template>

<style scoped>
.chat-kpis {
    display: flex;
    gap: 10px;
    overflow-x: auto;
    overflow-y: visible;
    scroll-snap-type: x mandatory;
    scrollbar-width: none;
    padding-bottom: 4px;
}
.chat-kpis::-webkit-scrollbar { display: none; }

@media (min-width: 1024px) {
    .chat-kpis {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        overflow: visible;
        gap: 12px;
        padding-bottom: 0;
    }
}

.metric-card {
    flex: 0 0 220px;
    border-radius: var(--r-md, 16px);
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

.metric-card--urgent { background: rgba(220,38,38,0.07); border: 1px solid rgba(220,38,38,0.22); }
.metric-card--warn   { background: rgba(245,158,11,0.07); border: 1px solid rgba(245,158,11,0.2); }
.metric-card--green  { background: rgba(16,185,129,0.07); border: 1px solid rgba(16,185,129,0.2); }
.metric-card--info   { background: rgba(59,130,246,0.07); border: 1px solid rgba(59,130,246,0.2); }

.metric-ring-wrap { position: absolute; top: 12px; right: 12px; }
.metric-ring { transform: rotate(-90deg); }
.ring-track { fill: none; stroke: rgba(255,255,255,0.06); }
.ring-fill { fill: none; stroke-linecap: round; transition: stroke-dashoffset 1.2s var(--ease-out, ease); }
.ring-fill--red   { stroke: #F87171; }
.ring-fill--amber { stroke: #FCD34D; }
.ring-fill--green { stroke: #34D399; }
.ring-fill--blue  { stroke: #60A5FA; }

.metric-label {
    font-family: var(--font-display);
    font-size: 8px; letter-spacing: 1.6px; text-transform: uppercase;
    color: var(--c-text-3);
    margin-bottom: 8px; padding-right: 60px; line-height: 1.4; min-height: 12px;
}
.metric-value {
    font-family: var(--font-display);
    font-size: 32px; letter-spacing: 0.03em; line-height: 1;
    margin-bottom: 4px;
    display: inline-flex; align-items: baseline; gap: 4px;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.metric-value--text { font-size: 22px; letter-spacing: 0.05em; text-transform: uppercase; }
.metric-value--urgent { color: #F87171; }
.metric-value--warn   { color: #FCD34D; }
.metric-value--green  { color: #34D399; }
.metric-value--info   { color: var(--c-text); }
.metric-sub {
    font-family: var(--font-display);
    font-size: 9px; color: var(--c-text-3);
    margin-top: 2px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.metric-tooltip-hint {
    font-family: var(--font-display);
    font-size: 8px; color: #FCD34D;
    margin-top: 2px;
}
@media (min-width: 1024px) {
    .metric-value { font-size: 36px; }
    .metric-value--text { font-size: 24px; }
}
@media (prefers-reduced-motion: reduce) {
    .ring-fill { transition: none !important; }
}
</style>
