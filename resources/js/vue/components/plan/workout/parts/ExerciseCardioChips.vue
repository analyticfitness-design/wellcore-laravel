<template>
  <div class="cardio-wrap" data-testid="cardio-chips">
    <span class="cardio-tag">Cardio LISS</span>
    <div v-if="hasAnyMetric" class="chips">
      <span v-if="min != null && min !== ''" class="metric cardio-min">
        <span class="k">Min</span>
        <span class="v">{{ min }}</span>
      </span>
      <span v-if="velocidad != null && velocidad !== ''" class="metric cardio-vel">
        <span class="k">Km/h</span>
        <span class="v">{{ velocidad }}</span>
      </span>
      <span v-if="inclinacion != null && inclinacion !== ''" class="metric cardio-incl">
        <span class="k">Incl.</span>
        <span class="v">{{ inclinacion }}{{ inclSuffix }}</span>
      </span>
    </div>
  </div>
</template>

<script setup>
// ExerciseCardioChips — chips sky para min/km-h/inclinación.
// CSS lines 710-721 del HTML V2.1.
import { computed } from 'vue';

const props = defineProps({
  min: { type: [Number, String], default: null },
  velocidad: { type: [Number, String], default: null },
  inclinacion: { type: [Number, String], default: null },
});

const hasAnyMetric = computed(() => {
  return [props.min, props.velocidad, props.inclinacion].some(
    (v) => v != null && v !== ''
  );
});

const inclSuffix = computed(() => {
  // Si ya viene "8%" no añadimos otro %.
  const raw = String(props.inclinacion ?? '');
  return raw.includes('%') ? '' : '%';
});
</script>

<style scoped>
.cardio-wrap {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.cardio-tag {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 2px 8px;
  border-radius: 999px;
  background: rgba(56, 189, 248, 0.12);
  font-family: var(--font-mono, 'JetBrains Mono', ui-monospace, monospace);
  font-size: 10px;
  letter-spacing: 0.10em;
  text-transform: uppercase;
  color: #38BDF8;
  font-weight: 600;
  align-self: flex-start;
}
.chips {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  align-items: center;
}
.metric {
  display: inline-flex;
  align-items: baseline;
  gap: 4px;
  font-family: var(--font-mono, 'JetBrains Mono', ui-monospace, monospace);
  font-size: 12px;
  padding: 3px 9px;
  border-radius: 6px;
  background: var(--wc-bg-tertiary);
  border: 1px solid var(--wc-border);
}
.metric .k {
  font-size: 9px;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: var(--wc-text-tertiary);
  font-weight: 600;
}
.metric .v {
  color: var(--wc-text);
  font-weight: 600;
  font-variant-numeric: tabular-nums;
}
.metric.cardio-min { background: rgba(56, 189, 248, 0.12); color: #38BDF8; border-color: rgba(56, 189, 248, 0.24); }
.metric.cardio-vel { background: rgba(99, 102, 241, 0.12); color: #818CF8; border-color: rgba(99, 102, 241, 0.24); }
.metric.cardio-incl { background: rgba(167, 139, 250, 0.12); color: #C4B5FD; border-color: rgba(167, 139, 250, 0.24); }
.metric.cardio-min .v { color: #38BDF8; }
.metric.cardio-vel .v { color: #818CF8; }
.metric.cardio-incl .v { color: #C4B5FD; }
</style>
