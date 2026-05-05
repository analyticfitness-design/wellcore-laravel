<script setup>
import { ref } from 'vue';

const props = defineProps({
  title: { type: String, required: true },
  initialOpen: { type: Boolean, default: false },
  maxHeightOpen: { type: Number, default: 480 },
});

const emit = defineEmits(['toggle']);

const open = ref(props.initialOpen);

function toggle() {
  open.value = !open.value;
  emit('toggle', open.value);
}
</script>

<template>
  <div :class="['disclosure', { open }]">
    <button
      class="disclosure-header"
      :aria-expanded="open"
      @click="toggle"
    >
      <span class="flex items-center gap-2.5">
        <slot name="leading">
          <svg class="h-3.5 w-3.5" style="stroke: var(--color-wc-accent);" fill="none" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
          </svg>
        </slot>
        <span class="text-[12px] font-bold uppercase tracking-[0.08em] text-[var(--color-wc-text-2)]">
          {{ title }}
        </span>
      </span>
      <svg
        class="disclosure-chevron h-3.5 w-3.5"
        style="stroke: var(--color-wc-text-3);"
        fill="none" viewBox="0 0 24 24" stroke-width="2"
        aria-hidden="true"
      >
        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
      </svg>
    </button>
    <div class="disclosure-body" :style="{ '--max-h': maxHeightOpen + 'px' }">
      <div class="disclosure-inner">
        <slot />
      </div>
    </div>
  </div>
</template>
