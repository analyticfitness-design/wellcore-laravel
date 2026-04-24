<script setup>
import { RouterLink } from 'vue-router';
import { useHaptics } from '../../composables/useHaptics';

defineProps({
    data: { type: Object, required: true },
});

const haptics = useHaptics();
</script>

<template>
  <div v-if="data.coachName" class="coach-card flex items-center gap-4 rounded-xl border bg-wc-bg-tertiary p-4">
    <div class="coach-avatar flex h-11 w-11 shrink-0 items-center justify-center rounded-full">
      <span class="font-display text-sm tracking-wide text-wc-accent">{{ data.coachInitials || 'WC' }}</span>
    </div>
    <div class="min-w-0 flex-1">
      <p class="text-xs font-semibold tracking-widest uppercase text-wc-text-secondary">Tu coach</p>
      <p class="truncate text-base font-semibold text-wc-text">{{ data.coachName }}</p>
    </div>
    <RouterLink
      to="/client/chat"
      @click="haptics.light()"
      class="inline-flex items-center gap-1.5 rounded-full bg-wc-accent px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-wc-accent-hover shadow-lg shadow-wc-accent/20"
    >
      <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
      </svg>
      Enviar mensaje
    </RouterLink>
  </div>
</template>
