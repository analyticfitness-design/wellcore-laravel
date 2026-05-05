<script setup>
import { ref, computed, onMounted } from 'vue';
import { useReducedMotion } from '../../../composables/useReducedMotion';

const props = defineProps({
  label: { type: String, required: true },
  value: { type: Number, required: true },
  accent: { type: Boolean, default: false },
  muted: { type: Boolean, default: false },
  sparkline: { type: Array, default: () => [] },
  sparklineColor: { type: String, default: '#DC2626' },
  animateOnMount: { type: Boolean, default: true },
});

const emit = defineEmits(['click']);

const reducedMotion = useReducedMotion();
const displayValue = ref(0);

onMounted(() => {
  if (reducedMotion.value || !props.animateOnMount) {
    displayValue.value = props.value;
    return;
  }
  const target = props.value;
  if (target === 0) { displayValue.value = 0; return; }
  const duration = 800;
  const start = performance.now();
  const step = (now) => {
    const p = Math.min((now - start) / duration, 1);
    const eased = 1 - Math.pow(1 - p, 3);
    displayValue.value = Math.round(eased * target);
    if (p < 1) requestAnimationFrame(step);
    else displayValue.value = target;
  };
  requestAnimationFrame(step);
});

const sparkPoints = computed(() => {
  if (props.sparkline.length < 2) return '';
  const max = Math.max(...props.sparkline, 1);
  const w = 60, h = 24;
  return props.sparkline.map((v, i) => {
    const x = (i / (props.sparkline.length - 1)) * w;
    const y = h - (v / max) * (h - 4) - 2;
    return `${x.toFixed(1)},${y.toFixed(1)}`;
  }).join(' ');
});

const numColor = computed(() =>
  props.accent ? 'var(--color-wc-accent)' :
  props.muted ? 'var(--color-wc-text-3)' :
  'var(--color-wc-text)'
);
</script>

<template>
  <button
    class="kpi-card relative overflow-hidden rounded-[14px] border border-[var(--b1)] p-4 lg:p-5 cursor-pointer transition active:scale-[0.96] hover:bg-[var(--s2)] hover:lg:-translate-y-px text-left w-full"
    style="background: var(--color-wc-bg-secondary, #111113); box-shadow: var(--shadow-card-ios); transition-duration: var(--t-tap); transition-timing-function: var(--ease-spring-ios);"
    :aria-label="`${label}: ${value}`"
    @click="emit('click')"
  >
    <span
      class="kpi-num kpi-num-anim font-display tnum-ios block text-[34px] lg:text-[36px] font-bold leading-none tracking-tight"
      :style="{ color: numColor }"
    >
      {{ displayValue }}
    </span>
    <span class="block mt-1.5 text-[10px] font-bold tracking-[0.1em] uppercase text-[var(--color-wc-text-3)]">
      {{ label }}
    </span>

    <svg
      v-if="sparkline.length >= 2"
      class="absolute bottom-2 right-2 lg:bottom-3 lg:right-3 opacity-[0.4] pointer-events-none"
      width="60" height="24" viewBox="0 0 60 24" fill="none" aria-hidden="true"
    >
      <polyline
        :points="sparkPoints"
        :stroke="sparklineColor"
        stroke-width="1.5"
        stroke-linecap="round"
        stroke-linejoin="round"
        fill="none"
      />
    </svg>

    <svg
      v-else
      class="absolute bottom-2 right-2 lg:bottom-3 lg:right-3 opacity-[0.3] pointer-events-none"
      width="60" height="24" viewBox="0 0 60 24" fill="none" aria-hidden="true"
    >
      <line x1="2" y1="12" x2="58" y2="12" stroke="rgba(255,255,255,0.15)" stroke-width="1.5" stroke-linecap="round" stroke-dasharray="2 3" />
    </svg>
  </button>
</template>
