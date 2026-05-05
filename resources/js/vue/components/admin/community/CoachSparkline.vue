<script setup>
import { computed } from 'vue';

const props = defineProps({
    series: { type: Array, default: () => [] },
    color: { type: String, default: '#DC2626' },
    width: { type: Number, default: 80 },
    height: { type: Number, default: 24 },
});

const path = computed(() => {
    if (!props.series.length) return '';
    const max = Math.max(1, ...props.series);
    const step = props.width / Math.max(1, props.series.length - 1);
    return props.series.map((v, i) => {
        const x = i * step;
        const y = props.height - (v / max) * props.height;
        return (i === 0 ? 'M' : 'L') + x.toFixed(1) + ',' + y.toFixed(1);
    }).join(' ');
});
</script>

<template>
  <svg :width="width" :height="height" :viewBox="`0 0 ${width} ${height}`" class="overflow-visible">
    <path :d="path" :stroke="color" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round" />
  </svg>
</template>
