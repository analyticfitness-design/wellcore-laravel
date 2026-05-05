<script setup>
import { computed } from 'vue';

const props = defineProps({
    pinnedUntil: { type: [String, null], default: null },
    note: { type: [String, null], default: null },
});

const remaining = computed(() => {
    if (!props.pinnedUntil) return null;
    const target = new Date(props.pinnedUntil);
    const diffMs = target.getTime() - Date.now();
    if (diffMs <= 0) return null;
    const hours = Math.floor(diffMs / (1000 * 60 * 60));
    if (hours >= 24) return `${Math.floor(hours / 24)}d restantes`;
    return `${hours}h restantes`;
});
</script>

<template>
  <div class="inline-flex items-center gap-2 rounded-lg bg-wc-accent/5 border border-wc-accent/20 px-2 py-1 text-xs">
    <svg class="h-3.5 w-3.5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="M5 5v14m7-14l-3 3v3h6V8l-3-3zM5 19h14" />
    </svg>
    <span class="font-semibold text-wc-accent">Fijado</span>
    <span v-if="remaining" class="text-wc-text-tertiary">· {{ remaining }}</span>
    <span v-if="note" :title="note" class="text-wc-text-tertiary truncate max-w-[140px]">· {{ note }}</span>
  </div>
</template>
