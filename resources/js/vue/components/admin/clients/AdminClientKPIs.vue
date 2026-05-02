<script setup>
import { computed } from 'vue';
import { formatCOP } from '../../../composables/useFormat';

const props = defineProps({
    client: { type: Object, default: null },
});

// Datos derivados del payload del backend (stats + metrics + planDetails)
const kpis = computed(() => {
    const c = props.client || {};
    const stats = c.stats || {};
    const metrics = c.metrics || {};
    const planDetails = c.planDetails || {};

    return [
        {
            key: 'checkins',
            label: 'CHECK-INS',
            value: Number(stats.checkins_count || 0),
            sub: 'TOTALES',
            tone: 'accent',
        },
        {
            key: 'workouts',
            label: 'ENTRENAMIENTOS',
            value: Number(metrics.totalWorkouts || 0),
            sub: 'COMPLETADOS',
            tone: 'info',
        },
        {
            key: 'adherence',
            label: 'ADHERENCIA',
            value: `${Number(metrics.adherence || 0)}%`,
            sub: 'WORKOUT RATE',
            tone: 'success',
            ringPercent: Math.min(100, Math.max(0, Number(metrics.adherence || 0))),
        },
        {
            key: 'plan',
            label: 'SEMANA',
            value: planDetails.currentWeek != null ? String(planDetails.currentWeek) : '—',
            sub: planDetails.name ? planDetails.name.toUpperCase() : 'SIN PLAN',
            tone: 'editorial',
        },
    ];
});
</script>

<template>
  <div class="kpis-grid">
    <article
      v-for="kpi in kpis"
      :key="kpi.key"
      class="kpi-card"
      :class="`kpi-card--${kpi.tone}`"
    >
      <span class="kpi-label">{{ kpi.label }}</span>
      <div class="kpi-value-row">
        <span class="kpi-value">{{ kpi.value }}</span>
        <svg
          v-if="kpi.ringPercent != null"
          class="kpi-ring"
          width="40"
          height="40"
          viewBox="0 0 36 36"
          aria-hidden="true"
        >
          <circle class="ring-track" cx="18" cy="18" r="15" fill="none" stroke-width="3" />
          <circle
            class="ring-progress"
            cx="18"
            cy="18"
            r="15"
            fill="none"
            stroke-width="3"
            stroke-linecap="round"
            :stroke-dasharray="`${kpi.ringPercent * 0.94} 94`"
          />
        </svg>
      </div>
      <span class="kpi-sub">{{ kpi.sub }}</span>
    </article>
  </div>
</template>

<style scoped>
.kpis-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}
@media (min-width: 1024px) {
    .kpis-grid { grid-template-columns: repeat(4, 1fr); gap: 14px; }
}

.kpi-card {
    border-radius: var(--r-md, 16px);
    border: 1px solid var(--c-border);
    background: var(--c-surface);
    padding: 14px 16px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
}
.kpi-card--accent     { border-color: rgba(220,38,38,0.22); }
.kpi-card--info       { border-color: rgba(96,165,250,0.18); }
.kpi-card--success    { border-color: rgba(22,163,74,0.22); }
.kpi-card--editorial  { border-color: rgba(212,168,14,0.22); }

.kpi-label {
    font-family: var(--font-display);
    font-size: 10px; font-weight: 600;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    color: var(--c-text-3);
}
.kpi-value-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}
.kpi-value {
    font-family: var(--font-display);
    font-variant-numeric: tabular-nums; font-feature-settings: "tnum";
    font-size: clamp(28px, 4vw, 38px);
    font-weight: 700;
    line-height: 1;
    letter-spacing: var(--ls-display, -0.02em);
    color: var(--c-text);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.kpi-card--editorial .kpi-value {
    color: var(--c-amber, #D4A80E);
    font-size: clamp(32px, 4.4vw, 42px);
}

.kpi-sub {
    font-family: var(--font-display);
    font-size: 10px; font-weight: 600;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    color: var(--c-text-3);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* ── Mini ring SVG ─────────────────────────────────────────────────── */
.kpi-ring { flex-shrink: 0; }
.ring-track { stroke: rgba(255,255,255,0.06); }
.ring-progress {
    stroke: #34D399;
    transform: rotate(-90deg);
    transform-origin: 50% 50%;
    transition: stroke-dasharray 1.2s var(--ease-out, ease);
}
@media (prefers-reduced-motion: reduce) {
    .ring-progress { transition: none; }
}
</style>
