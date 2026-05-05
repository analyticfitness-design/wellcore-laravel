<script setup>
defineProps({
  contextLabel: { type: String, default: '' },
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
  badge: { type: String, default: '' },
});
</script>

<template>
  <header class="wc-page-header anim-entry anim-entry-1 mb-5 pb-5 border-b border-[var(--b1,rgba(255,255,255,0.08))] flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div class="min-w-0">
      <div v-if="contextLabel" class="font-mono text-xs text-wc-text-tertiary uppercase tracking-widest mb-1">{{ contextLabel }}</div>
      <h1 class="font-display text-2xl sm:text-3xl lg:text-4xl uppercase tracking-wide text-wc-text leading-none">{{ title }}</h1>
      <p v-if="subtitle" class="mt-2 text-sm text-wc-text-secondary">{{ subtitle }}</p>
    </div>
    <div class="flex items-center gap-2 shrink-0">
      <span
        v-if="badge"
        class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider bg-wc-accent/15 text-wc-accent border border-wc-accent/25"
      >
        {{ badge }}
      </span>
      <slot name="actions" />
    </div>
  </header>
</template>
