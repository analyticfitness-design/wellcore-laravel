<script setup>
defineProps({
  kpis: { type: Object, required: true },
  loading: { type: Boolean, default: false },
});

function fmtHours(v) {
  if (v === null || v === undefined) return '—';
  const n = Number(v);
  if (!Number.isFinite(n) || n === 0) return '—';
  if (n < 1) return `${Math.round(n * 60)}min`;
  if (n >= 24) return `${(n / 24).toFixed(1)}d`;
  return `${n.toFixed(1)}h`;
}
</script>

<template>
  <div class="kpis-grid">
    <article class="kpi-card">
      <span class="kpi-label">CREADOS</span>
      <span class="kpi-value">{{ kpis.created ?? 0 }}</span>
      <span class="kpi-sub">en el periodo</span>
    </article>

    <article class="kpi-card kpi-card--green">
      <span class="kpi-label">APROBADOS</span>
      <span class="kpi-value kpi-value--green">{{ kpis.approved ?? 0 }}</span>
      <span class="kpi-sub">completados</span>
    </article>

    <article class="kpi-card kpi-card--red">
      <span class="kpi-label">RECHAZADOS</span>
      <span class="kpi-value kpi-value--red">{{ kpis.rejected ?? 0 }}</span>
      <span class="kpi-sub">
        <template v-if="(kpis.created ?? 0) > 0">
          {{ Math.round(((kpis.rejected ?? 0) / (kpis.created ?? 1)) * 100) }}% tasa
        </template>
        <template v-else>sin datos</template>
      </span>
    </article>

    <article class="kpi-card kpi-card--amber">
      <span class="kpi-label">TIEMPO PROM.</span>
      <span class="kpi-value kpi-value--amber">{{ fmtHours(kpis.avg_time_hours) }}</span>
      <span class="kpi-sub">creacion a aprobacion</span>
    </article>
  </div>
</template>

<style scoped>
.kpis-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}
@media (min-width: 768px) {
    .kpis-grid { grid-template-columns: repeat(4, 1fr); gap: 12px; }
}

.kpi-card {
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17, 17, 17, 0.7);
    padding: 16px 14px 14px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    position: relative;
    overflow: hidden;
    transition: border-color 0.15s var(--ease-out, ease);
}
.kpi-card::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 14px;
    opacity: 0;
    transition: opacity 0.15s var(--ease-out, ease);
}
.kpi-card--green::before  { background: radial-gradient(ellipse 60% 50% at 50% 0%, rgba(16,185,129,0.06), transparent); }
.kpi-card--red::before    { background: radial-gradient(ellipse 60% 50% at 50% 0%, rgba(220,38,38,0.06), transparent); }
.kpi-card--amber::before  { background: radial-gradient(ellipse 60% 50% at 50% 0%, rgba(245,158,11,0.06), transparent); }
.kpi-card::before { opacity: 1; }

.kpi-label {
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}
.kpi-value {
    font-family: var(--font-display);
    font-size: clamp(28px, 4vw, 40px);
    letter-spacing: 0.04em;
    color: var(--color-wc-text);
    line-height: 1;
    margin: 2px 0;
    font-variant-numeric: tabular-nums;
}
.kpi-value--green  { color: var(--color-wc-green-text, #34D399); }
.kpi-value--red    { color: var(--color-wc-red-text, #F87171); }
.kpi-value--amber  { color: var(--color-wc-amber-text, #FCD34D); }

.kpi-sub {
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    letter-spacing: 0.12em;
    color: var(--color-wc-text-tertiary);
    text-transform: uppercase;
}
</style>
