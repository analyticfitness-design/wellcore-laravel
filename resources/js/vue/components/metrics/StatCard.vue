<script setup>
import DeltaBadge from './DeltaBadge.vue';

defineProps({
  variant: {
    type: String,
    default: 'default',
    // hero | delta | goal | counter | empty
  },
  label: { type: String, required: true },
  value: { type: [String, Number], default: null },
  unit: { type: String, default: '' },
  sub: { type: String, default: '' },
  delta: { type: Number, default: null },
  deltaUnit: { type: String, default: '' },
  goalCurrent: { type: Number, default: null },
  goalTarget: { type: Number, default: null },
  pill: { type: String, default: '' },
  empty: { type: Boolean, default: false },
});
</script>

<template>
  <div class="stat-card" :class="[`stat-card--${variant}`, { 'stat-card--empty': empty || value === null || value === '--' }]">
    <!-- Header row -->
    <div class="stat-card__hd">
      <span class="stat-card__lbl">{{ label }}</span>
      <span v-if="pill" class="stat-card__pill">{{ pill }}</span>
    </div>

    <!-- Value -->
    <div class="stat-card__val">
      <span class="stat-card__num">{{ value ?? '—' }}</span>
      <span v-if="unit && value !== null && value !== '--'" class="stat-card__unit">{{ unit }}</span>
    </div>

    <!-- Goal progress bar (variant=goal) -->
    <template v-if="variant === 'goal' && goalCurrent !== null && goalTarget !== null">
      <div class="stat-card__goal-track">
        <div
          class="stat-card__goal-fill"
          :style="{ width: Math.min(100, (goalCurrent / goalTarget) * 100) + '%' }"
        ></div>
      </div>
      <div class="stat-card__goal-meta">
        <span>Actual <b>{{ goalCurrent }}</b> kg</span>
        <span>Objetivo <b>{{ goalTarget }}</b> kg</span>
      </div>
    </template>

    <!-- Sub row -->
    <div v-if="sub || delta !== null" class="stat-card__sub">
      <span v-if="sub">{{ sub }}</span>
      <DeltaBadge v-if="delta !== null" :value="delta" :unit="deltaUnit" />
    </div>
  </div>
</template>

<style scoped>
.stat-card {
  position: relative;
  border-radius: 16px;
  border: 1px solid var(--color-wc-border);
  background: var(--color-wc-bg-tertiary);
  padding: 20px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  min-height: 148px;
}
.stat-card--hero {
  background: linear-gradient(180deg, var(--color-wc-bg-tertiary) 0%, var(--color-wc-bg-secondary) 100%);
  border-color: rgba(255,255,255,.14);
}
.dark .stat-card {
  background: var(--color-wc-bg-tertiary);
}
.dark .stat-card--hero {
  background: linear-gradient(180deg, #18181B 0%, #111113 100%);
  border-color: rgba(255,255,255,.14);
}
.stat-card__hd {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  margin-bottom: auto;
}
.stat-card__lbl {
  font-family: var(--font-display);
  font-size: 11px;
  font-weight: 500;
  letter-spacing: .18em;
  text-transform: uppercase;
  color: var(--color-wc-text-tertiary);
}
.stat-card--hero .stat-card__lbl { color: var(--color-wc-text-secondary); }
.stat-card__pill {
  font-family: var(--font-mono);
  font-size: 10px;
  letter-spacing: .06em;
  color: var(--color-wc-text-tertiary);
  padding: 3px 8px;
  border-radius: 999px;
  border: 1px solid var(--color-wc-border);
}
.stat-card__val {
  display: flex;
  align-items: baseline;
  gap: 6px;
  margin-top: 14px;
}
.stat-card__num {
  font-family: var(--font-display);
  font-size: 44px;
  font-weight: 600;
  line-height: 1;
  letter-spacing: -.01em;
  color: var(--color-wc-text);
  font-variant-numeric: tabular-nums;
}
.stat-card--hero .stat-card__num {
  font-size: 64px;
  font-weight: 700;
  letter-spacing: -.02em;
}
.stat-card--counter .stat-card__num { font-size: 36px; }
.stat-card--empty .stat-card__num {
  color: var(--color-wc-text-tertiary);
  font-weight: 500;
}
.stat-card__unit {
  font-family: var(--font-mono);
  font-size: 13px;
  color: var(--color-wc-text-tertiary);
  font-weight: 500;
}
.stat-card__sub {
  margin-top: 10px;
  font-size: 12px;
  color: var(--color-wc-text-tertiary);
  display: flex;
  align-items: center;
  gap: 8px;
}
/* Goal bar */
.stat-card__goal-track {
  margin-top: 12px;
  height: 6px;
  background: var(--color-wc-bg);
  border-radius: 999px;
  overflow: hidden;
  border: 1px solid var(--color-wc-border);
}
.stat-card__goal-fill {
  height: 100%;
  background: linear-gradient(90deg, var(--color-wc-accent-dark, #7F1D1D), var(--color-wc-accent));
  border-radius: 999px;
  transition: width .4s ease;
}
.stat-card__goal-meta {
  display: flex;
  justify-content: space-between;
  font-family: var(--font-mono);
  font-size: 10.5px;
  color: var(--color-wc-text-tertiary);
  margin-top: 6px;
  letter-spacing: .04em;
}
.stat-card__goal-meta b {
  color: var(--color-wc-text-secondary);
  font-weight: 600;
}
</style>
