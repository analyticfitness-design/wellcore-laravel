<script setup>
import { computed } from 'vue';
import { RouterLink } from 'vue-router';
import { useViewportAnimate } from '../../../composables/dashboard/useViewportAnimate';

const props = defineProps({
  // Production data del API: plan_tickets_pendientes, plan_tickets_overdue, etc.
  production: { type: Object, default: () => ({}) },
  // Financial data: mrr_actual_cop, mrr_delta_pct, pagos_pendientes_cop, etc.
  financial: { type: Object, default: () => ({}) },
  // Operational data: clientes_activos, coaches_activos, tasa_retencion_mes_pct
  operational: { type: Object, default: () => ({}) },
});

// 4 metricas hero: tickets pendientes (rojo), MRR mes (rojo/verde), clientes activos (verde), retencion (azul)
const metrics = computed(() => [
  {
    id: 'tickets',
    label: 'TICKETS PENDIENTES',
    value: Number(props.production?.plan_tickets_pendientes ?? 0),
    unit: '',
    delta: props.production?.plan_tickets_overdue
      ? `+${props.production.plan_tickets_overdue} overdue`
      : null,
    deltaDirection: 'down',
    sub: `${props.production?.checkins_sin_responder_global ?? 0} check-ins`,
    variant: (props.production?.plan_tickets_pendientes ?? 0) > 5 ? 'urgent' : 'warn',
    ringPct: Math.min(100, (Number(props.production?.plan_tickets_pendientes ?? 0) * 12)),
    to: '/admin/plan-tickets?status=pendiente',
  },
  {
    id: 'mrr',
    label: 'MRR ACTUAL',
    value: formatShortCOP(props.financial?.mrr_actual_cop),
    unit: '',
    delta: typeof props.financial?.mrr_delta_pct === 'number'
      ? `${props.financial.mrr_delta_pct >= 0 ? '+' : ''}${Number(props.financial.mrr_delta_pct).toFixed(1)}%`
      : null,
    deltaDirection: (props.financial?.mrr_delta_pct ?? 0) >= 0 ? 'up' : 'down',
    sub: `vs $${formatShortNumber(props.financial?.mrr_mes_anterior_cop ?? 0)}`,
    variant: (props.financial?.mrr_delta_pct ?? 0) < -10 ? 'urgent' : ((props.financial?.mrr_delta_pct ?? 0) >= 0 ? 'healthy' : 'warn'),
    ringPct: Math.min(100, Math.max(0, 50 + (Number(props.financial?.mrr_delta_pct ?? 0)) * 0.5)),
    to: '/admin/payments',
  },
  {
    id: 'clientes',
    label: 'CLIENTES ACTIVOS',
    value: Number(props.operational?.clientes_activos ?? 0),
    unit: '',
    delta: typeof props.operational?.clientes_nuevos_mes === 'number' && props.operational.clientes_nuevos_mes > 0
      ? `+${props.operational.clientes_nuevos_mes} mes`
      : null,
    deltaDirection: 'up',
    sub: `${props.operational?.coaches_activos ?? 0} coaches`,
    variant: 'healthy',
    ringPct: Math.min(100, Number(props.operational?.clientes_activos ?? 0) * 2),
    to: '/admin/clients',
  },
  {
    id: 'retencion',
    label: 'RETENCION MES',
    value: Number(props.operational?.tasa_retencion_mes_pct ?? 0).toFixed(1),
    unit: '%',
    delta: null,
    sub: 'objetivo 85%',
    variant: (props.operational?.tasa_retencion_mes_pct ?? 0) >= 80 ? 'info' : 'warn',
    ringPct: Math.min(100, Number(props.operational?.tasa_retencion_mes_pct ?? 0)),
    to: '/admin/clients',
  },
]);

// Constantes del SVG ring (radius 22, stroke-width 4 — circumference ~138)
const RING_RADIUS = 22;
const RING_STROKE = 4;
const RING_CIRCUMFERENCE = 2 * Math.PI * RING_RADIUS;

const { targetRef, visible } = useViewportAnimate({ threshold: 0.2 });

// Calcular stroke-dashoffset para cada metric — full perimeter cuando no visible (no hay ring),
// progresivo cuando visible.
function strokeOffset(pct) {
  if (!visible.value) return RING_CIRCUMFERENCE;
  return RING_CIRCUMFERENCE - (RING_CIRCUMFERENCE * Math.max(0, Math.min(100, pct)) / 100);
}

function ringColorClass(variant) {
  switch (variant) {
    case 'urgent': return 'ring-fill--red';
    case 'warn': return 'ring-fill--amber';
    case 'healthy': return 'ring-fill--green';
    case 'info': return 'ring-fill--blue';
    default: return 'ring-fill--blue';
  }
}

function formatShortCOP(n) {
  const num = Number(n || 0);
  if (num === 0) return '$0';
  if (num >= 1_000_000) return `$${(num / 1_000_000).toFixed(1)}M`;
  if (num >= 1_000) return `$${(num / 1_000).toFixed(0)}k`;
  return `$${num}`;
}

function formatShortNumber(n) {
  const num = Number(n || 0);
  if (num >= 1_000_000) return `${(num / 1_000_000).toFixed(1)}M`;
  if (num >= 1_000) return `${(num / 1_000).toFixed(0)}k`;
  return num.toString();
}

defineExpose({ visible });
</script>

<template>
  <div ref="targetRef" class="hero-metrics">
    <RouterLink
      v-for="m in metrics"
      :key="m.id"
      :to="m.to"
      class="metric-card"
      :class="`metric-card--${m.variant}`"
    >
      <!-- Mini ring SVG arriba a la derecha -->
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
      <span class="metric-value" :class="`metric-value--${m.variant}`">
        <span>{{ m.value }}</span>
        <span v-if="m.unit" class="metric-value-unit">{{ m.unit }}</span>
      </span>
      <span v-if="m.delta" class="metric-delta" :class="`metric-delta--${m.deltaDirection || 'neutral'}`">{{ m.delta }}</span>
      <span v-if="m.sub" class="metric-sub">{{ m.sub }}</span>
    </RouterLink>
  </div>
</template>

<style scoped>
/* ============================================================================
   AdminHeroMetrics — 4 KPI cards con mini-rings SVG.
   Mobile: scroll-snap horizontal. Desktop: grid 4 columnas.
   ============================================================================ */

.hero-metrics {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;

    /* Mobile: scroll-snap horizontal */
    grid-auto-flow: column;
    grid-auto-columns: 158px;
    overflow-x: auto;
    overflow-y: visible;
    scroll-snap-type: x mandatory;
    scrollbar-width: none;
    padding-bottom: 4px;
}
.hero-metrics::-webkit-scrollbar { display: none; }

@media (min-width: 1024px) {
    /* Desktop: grid 4-col, sin scroll horizontal */
    .hero-metrics {
        grid-auto-flow: row;
        grid-auto-columns: auto;
        grid-template-columns: repeat(4, 1fr);
        overflow: visible;
        gap: 12px;
        padding-bottom: 0;
    }
}

.metric-card {
    border-radius: 14px;
    padding: 16px 14px 14px;
    display: flex;
    flex-direction: column;
    gap: 0;
    position: relative;
    overflow: hidden;
    transition: transform 0.2s var(--ease-out, ease), box-shadow 0.2s var(--ease-out, ease);
    cursor: pointer;
    text-decoration: none;
    color: inherit;
    scroll-snap-align: start;
    min-height: 110px;
}
.metric-card:hover { transform: translateY(-2px); box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4); }
.metric-card:active { transform: scale(0.98); }

.metric-card--urgent  { background: rgba(220, 38, 38, 0.07); border: 1px solid rgba(220, 38, 38, 0.22); }
.metric-card--warn    { background: rgba(245, 158, 11, 0.07); border: 1px solid rgba(245, 158, 11, 0.2); }
.metric-card--healthy { background: rgba(16, 185, 129, 0.07); border: 1px solid rgba(16, 185, 129, 0.2); }
.metric-card--info    { background: rgba(59, 130, 246, 0.07); border: 1px solid rgba(59, 130, 246, 0.2); }

/* ── Mini ring ───────────────────────────────────────────────────────────── */
.metric-ring-wrap {
    position: absolute;
    top: 12px;
    right: 12px;
}
.metric-ring { transform: rotate(-90deg); }
.ring-track { fill: none; stroke: rgba(255, 255, 255, 0.06); }
.ring-fill {
    fill: none;
    stroke-linecap: round;
    /* Animacion clave del rediseño: 1.2s easeOut */
    transition: stroke-dashoffset 1.2s var(--ease-out, ease);
}
.ring-fill--red    { stroke: var(--color-wc-red-text, #F87171); }
.ring-fill--amber  { stroke: var(--color-wc-amber-text, #FCD34D); }
.ring-fill--green  { stroke: var(--color-wc-green-text, #34D399); }
.ring-fill--blue   { stroke: var(--color-wc-blue-text, #60A5FA); }

/* ── Texto ───────────────────────────────────────────────────────────────── */
.metric-label {
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
    margin-bottom: 8px;
    padding-right: 64px; /* dejar lugar al ring */
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

.metric-delta {
    font-family: var(--font-data, 'Barlow', sans-serif);
    font-size: 11px;
    font-weight: 600;
    margin-bottom: 2px;
}
.metric-delta--down    { color: var(--color-wc-red-text, #F87171); }
.metric-delta--up      { color: var(--color-wc-green-text, #34D399); }
.metric-delta--neutral { color: var(--color-wc-amber-text, #FCD34D); }

.metric-sub {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    color: var(--color-wc-text-tertiary);
    margin-top: 2px;
}

@media (min-width: 1024px) {
    .metric-value { font-size: 36px; }
    .metric-card { min-height: 120px; padding: 18px 16px 14px; }
}
</style>
