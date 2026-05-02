<script setup>
import { computed } from 'vue';
import { useViewportAnimate } from '../../../composables/dashboard/useViewportAnimate';

const props = defineProps({
  totalReferidos: { type: Number, default: 0 },
  qualified:      { type: Number, default: 0 },
  paid:           { type: Number, default: 0 },
  roi:            { type: String, default: '0x' },
  loading:        { type: Boolean, default: false },
});

const { targetRef, visible } = useViewportAnimate({ threshold: 0.2 });

const metrics = computed(() => [
  {
    id: 'total',
    label: 'TOTAL REFERIDOS',
    value: props.totalReferidos,
    sub: 'en el período',
    variant: 'neutral',
    ringPct: Math.min(100, props.totalReferidos * 10),
  },
  {
    id: 'qualified',
    label: 'QUALIFIED',
    value: props.qualified,
    sub: 'listos para payout',
    variant: props.qualified > 0 ? 'warn' : 'neutral',
    ringPct: props.totalReferidos > 0 ? Math.round((props.qualified / props.totalReferidos) * 100) : 0,
  },
  {
    id: 'paid',
    label: 'PAGADOS',
    value: props.paid,
    sub: 'recompensas entregadas',
    variant: 'healthy',
    ringPct: props.totalReferidos > 0 ? Math.round((props.paid / props.totalReferidos) * 100) : 0,
  },
  {
    id: 'roi',
    label: 'ROI PROGRAMA',
    value: props.roi,
    sub: 'revenue / costo recompensas',
    variant: 'info',
    ringPct: 72,
  },
]);

const ringColor = (variant) => {
  const map = {
    urgent:  '#DC2626',
    warn:    '#FCD34D',
    healthy: '#34D399',
    info:    '#60A5FA',
    neutral: 'rgba(250,250,250,0.2)',
  };
  return map[variant] ?? map.neutral;
};
</script>

<template>
  <div ref="targetRef" class="hero-metrics" role="list" aria-label="KPIs programa referidos">
    <article
      v-for="m in metrics"
      :key="m.id"
      class="metric-card"
      role="listitem"
    >
      <!-- Mini ring SVG -->
      <div class="metric-ring" aria-hidden="true">
        <svg viewBox="0 0 56 56" width="56" height="56" fill="none">
          <circle cx="28" cy="28" r="22" stroke="rgba(255,255,255,0.06)" stroke-width="4"/>
          <circle
            cx="28" cy="28" r="22"
            :stroke="ringColor(m.variant)"
            stroke-width="4"
            stroke-linecap="round"
            :stroke-dasharray="138.23"
            :stroke-dashoffset="visible ? 138.23 * (1 - m.ringPct / 100) : 138.23"
            transform="rotate(-90 28 28)"
            style="transition: stroke-dashoffset 1.2s cubic-bezier(.22,1,.36,1);"
          />
        </svg>
        <div class="metric-ring-inner">
          <span class="metric-value" :style="{ color: ringColor(m.variant) }">{{ m.value }}</span>
        </div>
      </div>

      <div class="metric-info">
        <p class="metric-label">{{ m.label }}</p>
        <p class="metric-sub">{{ m.sub }}</p>
      </div>
    </article>

    <!-- Loading skeleton -->
    <template v-if="loading && !totalReferidos">
      <div v-for="i in 4" :key="`sk-${i}`" class="metric-card metric-skeleton" aria-hidden="true" />
    </template>
  </div>
</template>

<style scoped>
.hero-metrics {
    display: flex;
    gap: 10px;
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    padding-bottom: 4px;
}
.hero-metrics::-webkit-scrollbar { display: none; }

.metric-card {
    flex: 0 0 auto;
    width: clamp(148px, 22vw, 190px);
    scroll-snap-align: start;
    border-radius: var(--r-md, 16px);
    border: 1px solid var(--c-border);
    background: rgba(17, 17, 17, 0.7);
    padding: 16px 14px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.metric-ring {
    position: relative;
    width: 56px;
    height: 56px;
    flex-shrink: 0;
}
.metric-ring-inner {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}
.metric-value {
    font-family: var(--font-display);
    font-size: 16px;
    letter-spacing: 0.04em;
    line-height: 1;
}

.metric-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.metric-label {
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    color: var(--c-text-3);
    margin: 0;
}
.metric-sub {
    font-family: var(--font-sans);
    font-size: 11px;
    color: var(--c-text-2);
    margin: 0;
}

.metric-skeleton {
    animation: page-pulse 1.5s ease-in-out infinite;
    background: var(--c-surface-2);
    min-height: 100px;
}
@keyframes page-pulse {
    0%, 100% { opacity: 0.6; }
    50%       { opacity: 0.9; }
}

@media (prefers-reduced-motion: reduce) {
    circle { transition: none !important; }
    .metric-skeleton { animation: none !important; }
}
</style>
