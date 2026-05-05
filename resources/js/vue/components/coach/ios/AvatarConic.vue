<script setup>
import { computed } from 'vue';

const props = defineProps({
  initial: { type: String, default: '?' },
  size: {
    type: String,
    default: 'md',
    validator: v => ['sm', 'md', 'lg'].includes(v),
  },
  tone: {
    type: String,
    default: 'accent',
    validator: v => ['accent', 'gold', 'purple'].includes(v),
  },
  imageUrl: { type: String, default: '' },
});

const sizeClass = computed(() => ({
  sm: 'w-8 h-8',
  md: 'w-10 h-10',
  lg: 'w-14 h-14',
}[props.size]));

const ringClass = computed(() => `ring-conic-${props.tone}`);

const innerBg = computed(() => {
  if (props.tone === 'accent') return 'var(--color-wc-accent)';
  return 'var(--color-wc-bg-4, #222226)';
});

const innerColor = computed(() => {
  if (props.tone === 'accent') return '#fff';
  if (props.tone === 'gold') return '#C8A769';
  if (props.tone === 'purple') return '#A78BFA';
  return '#fff';
});

const fontSize = computed(() => ({
  sm: '12px',
  md: '14px',
  lg: '18px',
}[props.size]));
</script>

<template>
  <span :class="[ringClass, sizeClass, 'relative inline-block flex-shrink-0']">
    <span
      class="absolute inset-[2px] rounded-full flex items-center justify-center font-display font-bold z-[1] overflow-hidden"
      :style="{
        background: imageUrl ? 'none' : innerBg,
        color: innerColor,
        fontSize,
        backgroundImage: imageUrl ? `url(${imageUrl})` : 'none',
        backgroundSize: 'cover',
        backgroundPosition: 'center',
      }"
    >
      <template v-if="!imageUrl">{{ initial }}</template>
    </span>
  </span>
</template>
