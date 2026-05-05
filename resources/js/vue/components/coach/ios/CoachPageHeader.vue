<script setup>
defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
  badge: { type: String, default: '' },
  badgeVariant: {
    type: String,
    default: 'accent',
    validator: v => ['accent', 'muted'].includes(v),
  },
});
</script>

<template>
  <header class="coach-page-header pb-5 mb-5 border-b anim-entry anim-entry-1" style="border-color: var(--b1);">
    <div class="flex items-end justify-between gap-3 flex-wrap">
      <div class="min-w-0">
        <h1 class="font-display text-2xl lg:text-3xl font-bold uppercase tracking-wide text-wc-text truncate">
          {{ title }}
        </h1>
        <p v-if="subtitle" class="text-sm text-[var(--color-wc-text-2)] mt-1 line-clamp-2">
          {{ subtitle }}
        </p>
      </div>
      <div class="flex items-center gap-2 shrink-0">
        <span
          v-if="badge"
          :class="[
            'inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider border',
            badgeVariant === 'accent'
              ? 'bg-wc-accent/15 text-wc-accent border-wc-accent/25'
              : 'border-[var(--b1)]',
          ]"
          :style="badgeVariant === 'muted' ? 'background: var(--s2); color: var(--color-wc-text-2);' : ''"
        >
          {{ badge }}
        </span>
        <slot name="actions" />
      </div>
    </div>
  </header>
</template>
