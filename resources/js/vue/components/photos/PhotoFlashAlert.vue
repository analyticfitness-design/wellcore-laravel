<script setup>
/**
 * PhotoFlashAlert — subtle amber tooltip rendered in-context (e.g. inside a
 * dropzone or at the top of a section) to nudge the user about a non-blocking
 * issue ("La luz parece baja, intenta acercarte a una ventana").
 *
 * Props:
 *   message: string  required
 *   icon:    'info' | 'alert' | 'lightbulb'  (default 'alert')
 *
 * Use role="status" because it's advisory, not blocking.
 */
defineProps({
  message: { type: String, required: true },
  icon: { type: String, default: 'alert' },
});
</script>

<template>
  <div
    role="status"
    class="flex items-center gap-1.5 rounded-lg border border-amber-400/35 bg-amber-500/10 px-2.5 py-1.5 text-xs text-amber-300"
  >
    <svg
      v-if="icon === 'lightbulb'"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      stroke-width="2"
      class="h-3 w-3 shrink-0"
      aria-hidden="true"
    >
      <path d="M9 18h6M10 22h4M12 2a7 7 0 0 0-4 12.7V17h8v-2.3A7 7 0 0 0 12 2z" />
    </svg>
    <svg
      v-else-if="icon === 'info'"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      stroke-width="2"
      class="h-3 w-3 shrink-0"
      aria-hidden="true"
    >
      <circle cx="12" cy="12" r="10" />
      <path d="M12 16v-4M12 8h.01" />
    </svg>
    <svg
      v-else
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      stroke-width="2"
      class="h-3 w-3 shrink-0"
      aria-hidden="true"
    >
      <path d="M12 9v4M12 17h.01M10.3 3.86l-8.18 14a2 2 0 0 0 1.71 3h16.34a2 2 0 0 0 1.71-3l-8.18-14a2 2 0 0 0-3.4 0z" />
    </svg>
    <span class="leading-tight">{{ message }}</span>
  </div>
</template>
