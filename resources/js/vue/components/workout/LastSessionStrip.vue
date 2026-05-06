<script setup>
/**
 * LastSessionStrip.vue — Strip de última sesión registrada
 *
 * Muestra: "Última: 50kg × 9 · ↑+2.5kg · hace 4 días"
 * Si no hay registro previo: "Primera vez con este ejercicio".
 */
import { computed } from 'vue';

const props = defineProps({
  weight:      { type: [Number, String, null], default: null },
  reps:        { type: [Number, String, null], default: null },
  daysAgo:     { type: Number, default: 0 },
  weightDelta: { type: Number, default: 0 },
  weightUnit:  { type: String, default: 'kg' },
});

const hasRecord = computed(() => props.weight !== null && props.weight !== '' && props.weight !== undefined);

const deltaSign = computed(() => {
  if (!props.weightDelta || props.weightDelta === 0) return 'zero';
  return props.weightDelta > 0 ? 'up' : 'down';
});

const deltaText = computed(() => {
  const v = Math.abs(props.weightDelta);
  const sign = props.weightDelta > 0 ? '+' : '-';
  return `${sign}${v.toFixed(1).replace(/\.0$/, '')} ${props.weightUnit}`;
});

const daysAgoText = computed(() => {
  if (!props.daysAgo || props.daysAgo === 0) return 'hoy';
  if (props.daysAgo === 1) return 'ayer';
  if (props.daysAgo < 7)   return `hace ${props.daysAgo} días`;
  if (props.daysAgo < 14)  return 'hace 1 semana';
  if (props.daysAgo < 30)  return `hace ${Math.floor(props.daysAgo / 7)} semanas`;
  if (props.daysAgo < 60)  return 'hace 1 mes';
  return `hace ${Math.floor(props.daysAgo / 30)} meses`;
});

const weightFormatted = computed(() => {
  const w = parseFloat(props.weight) || 0;
  return Number.isInteger(w) ? String(w) : w.toFixed(1).replace(/\.0$/, '');
});
</script>

<template>
  <div v-if="!hasRecord" class="last-strip last-strip--empty">
    <span class="l">Sin registro</span>
    <span class="empty-msg">Primera vez con este ejercicio · Empezamos a registrar</span>
  </div>
  <div v-else class="last-strip">
    <span class="l">Última</span>
    <div class="r">
      <span class="w">{{ weightFormatted }}</span>
      <span class="u">{{ weightUnit }}</span>
      <span class="x">×</span>
      <span class="w">{{ reps }}</span>
    </div>
    <span class="delta" :class="`delta--${deltaSign}`" v-if="deltaSign !== 'zero'">
      <svg v-if="deltaSign === 'up'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="18 15 12 9 6 15"/>
      </svg>
      <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="6 9 12 15 18 9"/>
      </svg>
      {{ deltaText }}
    </span>
    <span class="when">{{ daysAgoText }}</span>
  </div>
</template>

<style scoped>
.last-strip {
  padding: 10px 14px;
  display: flex;
  align-items: center;
  gap: 12px;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid var(--color-wc-border);
  border-radius: 12px;
  flex-wrap: wrap;
}
.last-strip--empty { gap: 10px; }

.l {
  font-family: var(--font-display);
  font-size: 11px;
  font-weight: 500;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: var(--color-wc-text-tertiary);
  flex-shrink: 0;
}

.empty-msg {
  font-size: 13px;
  color: var(--color-wc-text-secondary);
}

.r {
  display: inline-flex;
  align-items: baseline;
  gap: 4px;
  font-family: var(--font-display);
  font-weight: 500;
  font-variant-numeric: tabular-nums;
}
.r .w {
  font-size: 18px;
  color: var(--color-wc-text);
  letter-spacing: 0.02em;
}
.r .u {
  font-family: var(--font-mono);
  font-size: 11px;
  color: var(--color-wc-text-tertiary);
  text-transform: uppercase;
  letter-spacing: 0.08em;
}
.r .x {
  color: var(--color-wc-text-tertiary);
  font-size: 14px;
  margin: 0 2px;
}

.delta {
  display: inline-flex;
  align-items: center;
  gap: 3px;
  padding: 3px 8px;
  font-family: var(--font-mono);
  font-size: 11px;
  font-weight: 500;
  border-radius: 999px;
}
.delta svg { width: 10px; height: 10px; }
.delta--up   { background: rgba(16,185,129,0.12); color: #10B981; }
.delta--down { background: rgba(245,158,11,0.12); color: #F59E0B; }

.when {
  margin-left: auto;
  font-size: 11px;
  font-weight: 500;
  letter-spacing: 0.04em;
  color: var(--color-wc-text-tertiary);
}
</style>
