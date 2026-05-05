<script setup>
defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
  kind: {
    type: String,
    default: 'activity',
    validator: v => ['activity', 'tickets', 'messages', 'success', 'search'].includes(v),
  },
});
</script>

<template>
  <div class="empty-state">
    <svg v-if="kind === 'activity'" class="illustration" viewBox="0 0 48 48" fill="none" aria-hidden="true">
      <circle cx="24" cy="24" r="18" stroke="currentColor" stroke-width="1.5" opacity="0.5" />
      <path d="M16 24 L20 20 L24 24 L28 18 L32 22" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
      <circle cx="24" cy="24" r="2" fill="currentColor" opacity="0.4" />
      <path d="M18 32 L30 32" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" opacity="0.4" />
    </svg>
    <svg v-else-if="kind === 'tickets'" class="illustration" viewBox="0 0 48 48" fill="none" aria-hidden="true">
      <rect x="8" y="12" width="32" height="24" rx="5" stroke="currentColor" stroke-width="1.5" opacity="0.5" />
      <path d="M8 20 L40 20" stroke="currentColor" stroke-width="1.5" opacity="0.4" />
      <circle cx="24" cy="30" r="3" stroke="currentColor" stroke-width="1.5" opacity="0.4" />
      <path d="M20 14 L28 14" stroke="currentColor" stroke-width="2" stroke-linecap="round" opacity="0.5" />
    </svg>
    <svg v-else-if="kind === 'messages'" class="illustration" viewBox="0 0 48 48" fill="none" aria-hidden="true">
      <path d="M10 14 L34 14 A4 4 0 0 1 38 18 V30 A4 4 0 0 1 34 34 H22 L14 40 V34 H10 A4 4 0 0 1 6 30 V18 A4 4 0 0 1 10 14 Z" stroke="currentColor" stroke-width="1.5" opacity="0.5" />
      <circle cx="16" cy="24" r="1.5" fill="currentColor" opacity="0.4" />
      <circle cx="22" cy="24" r="1.5" fill="currentColor" opacity="0.4" />
      <circle cx="28" cy="24" r="1.5" fill="currentColor" opacity="0.4" />
    </svg>
    <svg v-else-if="kind === 'success'" class="illustration" viewBox="0 0 48 48" fill="none" aria-hidden="true">
      <circle cx="24" cy="24" r="18" stroke="currentColor" stroke-width="1.5" opacity="0.6" />
      <path d="M16 24 L22 30 L34 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
    <svg v-else-if="kind === 'search'" class="illustration" viewBox="0 0 48 48" fill="none" aria-hidden="true">
      <circle cx="22" cy="22" r="11" stroke="currentColor" stroke-width="1.5" opacity="0.6" />
      <path d="M30 30 L40 40" stroke="currentColor" stroke-width="2" stroke-linecap="round" opacity="0.6" />
      <path d="M18 22 L26 22 M22 18 L22 26" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" opacity="0.4" />
    </svg>

    <p class="empty-state-title">{{ title }}</p>
    <p v-if="subtitle" class="empty-state-sub">{{ subtitle }}</p>
    <slot />
  </div>
</template>
