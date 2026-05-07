<script setup>
const props = defineProps({
  value: { type: Number, default: null },
  unit: { type: String, default: '' },
  invertColors: { type: Boolean, default: false },
});

const colorClass = computed(() => {
  if (props.value === null || props.value === undefined) return 'delta--neu';
  const positive = props.value > 0;
  if (props.invertColors) return positive ? 'delta--up' : 'delta--down';
  return positive ? 'delta--down' : 'delta--up';
});

const label = computed(() => {
  if (props.value === null || props.value === undefined) return '—';
  const sign = props.value > 0 ? '+' : '';
  return `${sign}${Number(props.value).toFixed(1)}${props.unit}`;
});

import { computed } from 'vue';
</script>

<template>
  <span :class="['delta', colorClass]">
    <svg v-if="value !== null && value > 0" width="10" height="10" viewBox="0 0 10 10" fill="currentColor" aria-hidden="true">
      <path d="M5 2L9 8H1L5 2Z"/>
    </svg>
    <svg v-else-if="value !== null && value < 0" width="10" height="10" viewBox="0 0 10 10" fill="currentColor" aria-hidden="true">
      <path d="M5 8L1 2H9L5 8Z"/>
    </svg>
    {{ label }}
  </span>
</template>

<style scoped>
.delta {
  display: inline-flex;
  align-items: center;
  gap: 3px;
  font-family: var(--font-mono);
  font-size: 11px;
  font-weight: 600;
  font-variant-numeric: tabular-nums;
  padding: 2px 8px;
  border-radius: 999px;
}
.delta--down {
  color: #86EFAC;
  background: rgba(16,185,129,.10);
  border: 1px solid rgba(16,185,129,.20);
}
.delta--up {
  color: #FCA5A5;
  background: rgba(220,38,38,.10);
  border: 1px solid rgba(220,38,38,.24);
}
.delta--neu {
  color: rgba(250,250,250,.64);
  background: #1E1E22;
  border: 1px solid rgba(255,255,255,.08);
}
</style>
