<template>
  <div
    v-if="note?.trim()"
    class="flex items-start gap-2 rounded-lg border border-l-2 border-wc-border bg-wc-bg-tertiary px-3.5 py-3"
    :class="toneBorderClass"
  >
    <component :is="toneIcon" :size="14" :class="toneIconClass" class="mt-0.5 shrink-0" />
    <p class="text-sm leading-relaxed text-wc-text-tertiary">{{ note }}</p>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Info, Lightbulb, AlertTriangle } from 'lucide-vue-next';

const props = defineProps({
  note: { type: String, required: true },
  tone: {
    type: String,
    default: 'neutral',
    validator: (v) => ['neutral', 'tip', 'warning'].includes(v),
  },
});

const toneIcon = computed(() => {
  if (props.tone === 'tip') return Lightbulb;
  if (props.tone === 'warning') return AlertTriangle;
  return Info;
});

const toneIconClass = computed(() => {
  if (props.tone === 'tip' || props.tone === 'warning') return 'text-amber-400';
  return 'text-wc-text-tertiary';
});

const toneBorderClass = computed(() => {
  if (props.tone === 'tip' || props.tone === 'warning') return 'border-l-amber-400';
  return 'border-l-wc-border';
});
</script>
