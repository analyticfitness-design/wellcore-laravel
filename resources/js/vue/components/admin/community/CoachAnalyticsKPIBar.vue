<script setup>
defineProps({
    totals: { type: Object, default: () => ({}) },
    pending: { type: Number, default: 0 },
});
</script>

<template>
  <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
    <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-4">
      <p class="text-xs uppercase tracking-widest text-wc-text-tertiary">Comunidades activas</p>
      <p class="font-display text-3xl text-wc-text mt-1">{{ totals.active_communities ?? 0 }}</p>
    </div>
    <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-4">
      <p class="text-xs uppercase tracking-widest text-wc-text-tertiary">Posts 30d</p>
      <p class="font-display text-3xl text-wc-text mt-1">{{ totals.posts_30d ?? 0 }}</p>
    </div>
    <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-4">
      <p class="text-xs uppercase tracking-widest text-wc-text-tertiary">Engagements 30d</p>
      <p class="font-display text-3xl text-wc-text mt-1">{{ totals.engagements_30d ?? 0 }}</p>
    </div>
    <div :class="pending > 0 ? 'border-rose-500/30 bg-rose-500/5' : 'border-wc-border bg-wc-bg-secondary'" class="rounded-xl border">
      <div class="p-4">
        <p :class="pending > 0 ? 'text-rose-500' : 'text-wc-text-tertiary'" class="text-xs uppercase tracking-widest">Reportes pendientes</p>
        <p :class="pending > 0 ? 'text-rose-500' : 'text-wc-text'" class="font-display text-3xl mt-1">{{ pending }}</p>
      </div>
    </div>
  </div>
</template>
