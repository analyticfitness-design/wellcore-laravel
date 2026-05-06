<script setup>
/**
 * LastSessionStrip.vue — Strip de última sesión registrada (fiel al HTML target).
 *
 * Layout:
 *   [Última sesión · hace 4 días]    [52,5 kg × 9]  [↑ +2,5 kg]
 *
 * Si no hay registro previo: "Primera vez con este ejercicio · Empezamos a registrar".
 */
import { computed } from 'vue';

const props = defineProps({
    weight:      { type: [Number, String, null], default: null },
    reps:        { type: [Number, String, null], default: null },
    daysAgo:     { type: Number, default: 0 },
    weightDelta: { type: Number, default: 0 },
    weightUnit:  { type: String, default: 'kg' },
});

const hasRecord = computed(() =>
    props.weight !== null && props.weight !== '' && props.weight !== undefined
);

const deltaSign = computed(() => {
    if (!props.weightDelta || props.weightDelta === 0) return 'zero';
    return props.weightDelta > 0 ? 'up' : 'down';
});

const deltaText = computed(() => {
    const v = Math.abs(props.weightDelta);
    const sign = props.weightDelta > 0 ? '+' : '−';
    const formatted = Number.isInteger(v) ? String(v) : v.toFixed(1).replace('.', ',');
    return `${sign}${formatted} ${props.weightUnit}`;
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

const labelText = computed(() => `Última sesión · ${daysAgoText.value}`);

const weightFormatted = computed(() => {
    const w = parseFloat(props.weight) || 0;
    if (Number.isInteger(w)) return String(w);
    return w.toFixed(1).replace('.', ',');
});
</script>

<template>
  <div v-if="!hasRecord" class="last-strip last-strip--empty">
    <span class="l">Sin registro</span>
    <span class="empty-msg">Primera vez con este ejercicio</span>
  </div>
  <div v-else class="last-strip">
    <span class="l">{{ labelText }}</span>
    <span class="r">
      <span class="w">{{ weightFormatted }}</span>
      <span class="u">{{ weightUnit }} × {{ reps }}</span>
      <span v-if="deltaSign !== 'zero'" class="delta" :class="`delta--${deltaSign}`">
        <svg v-if="deltaSign === 'up'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
          <path d="M7 14l5-5 5 5"/>
        </svg>
        <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
          <path d="M7 10l5 5 5-5"/>
        </svg>
        {{ deltaText }}
      </span>
    </span>
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
  margin-left: auto;
  display: inline-flex;
  align-items: baseline;
  gap: 6px;
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

.delta {
  display: inline-flex;
  align-items: center;
  gap: 3px;
  padding: 3px 8px;
  font-family: var(--font-mono);
  font-size: 11px;
  font-weight: 500;
  border-radius: 999px;
  margin-left: 4px;
}
.delta svg { width: 10px; height: 10px; }
.delta--up   { background: rgba(16,185,129,0.12); color: #10B981; }
.delta--down { background: rgba(245,158,11,0.12); color: #F59E0B; }

/* Mobile: stack si no entra */
@media (max-width: 380px) {
  .last-strip { padding: 8px 12px; gap: 8px; }
  .l { font-size: 10px; letter-spacing: 0.14em; }
  .r .w { font-size: 16px; }
}
</style>
