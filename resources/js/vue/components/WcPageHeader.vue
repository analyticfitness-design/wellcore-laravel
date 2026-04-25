<script setup>
defineProps({
  contextLabel: { type: String, default: '' },
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
});
</script>

<template>
  <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div class="min-w-0">
      <div v-if="contextLabel" class="font-mono text-xs text-wc-text-tertiary uppercase tracking-widest mb-1">{{ contextLabel }}</div>
      <h1 class="font-display text-3xl sm:text-4xl uppercase tracking-wide text-wc-text leading-none">{{ title }}</h1>
      <p v-if="subtitle" class="mt-2 text-sm text-wc-text-secondary">{{ subtitle }}</p>
    </div>
    <div class="shrink-0">
      <slot name="actions" />
    </div>
  </div>
</template>
