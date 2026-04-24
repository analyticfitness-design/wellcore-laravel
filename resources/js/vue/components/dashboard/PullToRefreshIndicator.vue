<script setup>
import { computed } from 'vue';

const props = defineProps({
    distance: { type: Number, default: 0 },
    refreshing: { type: Boolean, default: false },
    threshold: { type: Number, default: 70 },
});

// Escala el spinner con el pull distance
const progress = computed(() => Math.min(props.distance / props.threshold, 1));
const rotation = computed(() => progress.value * 270);
const opacity = computed(() => Math.min(progress.value * 1.2, 1));
const ready = computed(() => props.distance >= props.threshold);
</script>

<template>
  <div
    v-show="distance > 0 || refreshing"
    class="pointer-events-none fixed left-0 right-0 z-30 flex justify-center lg:hidden"
    :style="{
      top: `calc(${distance * 0.5}px + env(safe-area-inset-top))`,
      transition: refreshing ? 'top .2s ease-out' : 'none',
      opacity,
    }"
    aria-hidden="true"
  >
    <div
      :class="[
        'flex h-10 w-10 items-center justify-center rounded-full border shadow-lg transition-colors',
        ready || refreshing
          ? 'border-wc-accent bg-wc-accent/10 text-wc-accent'
          : 'border-wc-border bg-wc-bg-secondary text-wc-text-secondary'
      ]"
    >
      <!-- Spinner: rota con el pull, da spin infinito al refrescar -->
      <svg
        :class="['h-5 w-5', refreshing ? 'animate-spin' : '']"
        :style="{
          transform: refreshing ? '' : `rotate(${rotation}deg)`,
          transition: refreshing ? 'none' : 'transform .05s linear',
        }"
        fill="none"
        viewBox="0 0 24 24"
        stroke-width="2.5"
        stroke="currentColor"
      >
        <path
          v-if="!refreshing"
          stroke-linecap="round"
          stroke-linejoin="round"
          d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"
        />
        <circle
          v-else
          cx="12" cy="12" r="9"
          stroke-dasharray="14 5"
          stroke-linecap="round"
        />
      </svg>
    </div>
  </div>
</template>
