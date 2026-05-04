<script setup>
import { computed } from 'vue';

const props = defineProps({
  /**
   * Array de { color, value }.
   * Ej: [{ color:'#DC2626', value: 21 }, { color:'#10B981', value: 20 }, ...]
   */
  segments: { type: Array, default: () => [] },
  total: { type: Number, default: 0 },
  centerLabel: { type: String, default: 'Total' },
});

const circumference = 2 * Math.PI * 40; // 251.33

const computedTotal = computed(() => {
  if (props.total) return props.total;
  return props.segments.reduce((s, x) => s + x.value, 0);
});

const arcs = computed(() => {
  let offset = 0;
  return props.segments.map(seg => {
    const len = computedTotal.value
      ? (seg.value / computedTotal.value) * circumference
      : 0;
    const a = {
      color: seg.color,
      dasharray: `${len.toFixed(2)} ${circumference.toFixed(2)}`,
      dashoffset: -offset,
    };
    offset += len;
    return a;
  });
});
</script>

<template>
  <div class="donut-wrap">
    <svg viewBox="0 0 100 100">
      <circle cx="50" cy="50" r="40" stroke="rgba(255,255,255,.04)" stroke-width="10" fill="none"></circle>
      <circle
        v-for="(arc, i) in arcs"
        :key="i"
        cx="50" cy="50" r="40"
        :stroke="arc.color"
        stroke-width="10"
        fill="none"
        :stroke-dasharray="arc.dasharray"
        :stroke-dashoffset="arc.dashoffset"
      ></circle>
    </svg>
    <div class="donut-center">
      <div class="n tnum">{{ computedTotal }}</div>
      <div class="l">{{ centerLabel }}</div>
    </div>
  </div>
</template>
