<script setup>
defineProps({
    n: { type: [Number, String], required: true },
    label: { type: String, required: true },
    accent: { type: Boolean, default: false },
});
</script>

<template>
  <div :class="[
    'rounded-xl border p-6',
    accent ? 'border-wc-accent/40 bg-wc-accent/5' : 'border-wc-border bg-wc-bg-secondary'
  ]">
    <p class="font-mono text-[10px] uppercase tracking-[0.15em] text-wc-text-tertiary">{{ label }}</p>
    <p :class="['mt-2 font-display text-4xl tracking-tight', accent ? 'text-wc-accent' : 'text-wc-text']">{{ n }}</p>
  </div>
</template>
