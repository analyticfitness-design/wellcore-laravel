<script setup>
import { computed } from 'vue';

const props = defineProps({
  /**
   * Array de números 0..N. Componente normaliza a viewBox 320x56 (mrr) o 100x28 (mini).
   */
  data: { type: Array, default: () => [] },
  variant: { type: String, default: 'mrr' }, // mrr (320x56) | mini (100x28)
  color: { type: String, default: 'red' }, // red | green | blue | amber
});

const colors = {
  red: { line: 'url(#sparkLineRed)', fill: 'url(#sparkFillRed)', dot: '#EF4444' },
  green: { line: '#10B981', fill: 'url(#sparkFillGreen)', dot: '#34D399' },
  blue: { line: '#3B82F6', fill: 'transparent', dot: '#60A5FA' },
  amber: { line: '#F59E0B', fill: 'transparent', dot: '#F59E0B' },
};

const viewBox = computed(() => props.variant === 'mrr' ? '0 0 320 56' : '0 0 100 28');
const w = computed(() => props.variant === 'mrr' ? 320 : 100);
const h = computed(() => props.variant === 'mrr' ? 56 : 28);

const path = computed(() => {
  if (!props.data?.length) return '';
  const min = Math.min(...props.data);
  const max = Math.max(...props.data);
  const range = max - min || 1;
  const stepX = w.value / (props.data.length - 1 || 1);
  const points = props.data.map((v, i) => {
    const x = i * stepX;
    const y = h.value - 6 - ((v - min) / range) * (h.value - 12);
    return `${x},${y.toFixed(2)}`;
  });
  return 'M' + points.join(' L');
});

const fillPath = computed(() => {
  if (!path.value) return '';
  return path.value + ` L${w.value},${h.value} L0,${h.value} Z`;
});

const lastX = computed(() => w.value);
const lastY = computed(() => {
  if (!props.data?.length) return h.value / 2;
  const min = Math.min(...props.data);
  const max = Math.max(...props.data);
  const range = max - min || 1;
  const v = props.data[props.data.length - 1];
  return h.value - 6 - ((v - min) / range) * (h.value - 12);
});

const c = computed(() => colors[props.color] || colors.red);
</script>

<template>
  <div :class="['spark-wrap', `spark-${variant}`]">
    <svg :viewBox="viewBox" preserveAspectRatio="none">
      <defs>
        <linearGradient id="sparkFillRed" x1="0" x2="0" y1="0" y2="1">
          <stop offset="0%" stop-color="#DC2626" stop-opacity="0.32"></stop>
          <stop offset="100%" stop-color="#DC2626" stop-opacity="0"></stop>
        </linearGradient>
        <linearGradient id="sparkLineRed" x1="0" x2="1" y1="0" y2="0">
          <stop offset="0%" stop-color="#7F1D1D"></stop>
          <stop offset="60%" stop-color="#DC2626"></stop>
          <stop offset="100%" stop-color="#EF4444"></stop>
        </linearGradient>
        <linearGradient id="sparkFillGreen" x1="0" x2="0" y1="0" y2="1">
          <stop offset="0%" stop-color="#10B981" stop-opacity=".4"></stop>
          <stop offset="100%" stop-color="#10B981" stop-opacity="0"></stop>
        </linearGradient>
      </defs>
      <path v-if="fillPath && c.fill !== 'transparent'" :d="fillPath" :fill="c.fill"></path>
      <path :d="path" fill="none" :stroke="c.line" :stroke-width="variant === 'mrr' ? 2 : 1.5" stroke-linecap="round"></path>
      <circle :cx="lastX - (variant === 'mrr' ? 2 : 0)" :cy="lastY" :r="variant === 'mrr' ? 3.5 : 2" fill="#fff"></circle>
      <circle v-if="variant === 'mrr'" :cx="lastX - 2" :cy="lastY" r="6" fill="none" :stroke="c.dot" stroke-width="1" opacity=".6"></circle>
    </svg>
  </div>
</template>
