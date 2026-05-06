<template>
  <button
    v-if="interactive"
    type="button"
    @click="$emit('click')"
    :class="containerClasses"
  >
    <span :class="timeClasses">{{ time }}</span>
    <span :class="dotClasses"></span>
    <span :class="labelClasses">{{ label }}</span>
  </button>
  <div v-else :class="containerClasses">
    <span :class="timeClasses">{{ time }}</span>
    <span :class="dotClasses"></span>
    <span :class="labelClasses">{{ label }}</span>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  time: { type: String, required: true },
  label: { type: String, required: true },
  state: {
    type: String,
    required: true,
    validator: (v) => ['pending', 'current', 'done', 'swapped'].includes(v),
  },
  interactive: { type: Boolean, default: false },
  compact: { type: Boolean, default: false },
});

defineEmits(['click']);

const containerClasses = computed(() => [
  'flex flex-col items-center gap-1 min-w-[60px]',
  props.interactive
    ? 'min-h-[44px] px-2 py-1 rounded-lg transition-colors hover:bg-wc-bg-tertiary/40 focus:outline-none focus-visible:ring-2 focus-visible:ring-wc-accent/60'
    : '',
]);

const timeClasses = 'font-data text-[10px] tracking-wider text-wc-text-tertiary tabular-nums';

const dotSize = computed(() => (props.compact ? 'h-2.5 w-2.5' : 'h-3 w-3'));

const dotClasses = computed(() => {
  const base = `${dotSize.value} rounded-full shrink-0`;
  switch (props.state) {
    case 'done':
      return `${base} bg-emerald-400 ring-4 ring-emerald-400/30`;
    case 'current':
      return `${base} bg-wc-accent ring-4 ring-wc-accent/30 motion-safe:animate-pulse`;
    case 'swapped':
      return `${base} bg-wc-accent ring-4 ring-wc-accent/30`;
    case 'pending':
    default:
      return `${base} bg-transparent border-2 border-wc-border`;
  }
});

const labelClasses = computed(() => {
  const base = 'font-display text-[10px] tracking-[0.18em] uppercase';
  switch (props.state) {
    case 'done':
      return `${base} text-wc-text-tertiary`;
    case 'current':
      return `${base} text-wc-accent font-semibold`;
    case 'swapped':
      return `${base} text-wc-accent`;
    case 'pending':
    default:
      return `${base} text-wc-text-tertiary`;
  }
});
</script>
