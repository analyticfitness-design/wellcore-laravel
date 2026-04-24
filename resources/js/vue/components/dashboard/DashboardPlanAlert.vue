<script setup>
import { RouterLink } from 'vue-router';

defineProps({
    data: { type: Object, required: true },
});
</script>

<template>
  <div v-if="!data.hasActivePlan" class="flex items-start gap-4 rounded-xl border border-wc-accent/30 bg-wc-accent/5 p-4">
    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-wc-accent/10">
      <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
      </svg>
    </div>
    <div class="min-w-0 flex-1">
      <p class="text-sm font-semibold text-wc-text">No tienes un plan activo</p>
      <p class="mt-0.5 text-sm text-wc-text-secondary">Contacta a tu coach para que te asigne un plan de entrenamiento.</p>
    </div>
    <RouterLink
      to="/client/chat"
      class="shrink-0 inline-flex items-center gap-1.5 rounded-lg bg-wc-accent px-3 py-1.5 text-sm font-medium text-white transition-colors hover:bg-wc-accent-hover"
    >
      Contactar coach
    </RouterLink>
  </div>
  <div v-else class="wc-glass flex items-center gap-3 rounded-xl border border-wc-border bg-wc-bg-tertiary px-4 py-3">
    <div class="relative z-10 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-emerald-500/10">
      <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
      </svg>
    </div>
    <span class="relative z-10 min-w-0 flex-1 text-sm text-wc-text-secondary">
      Plan
      <span v-if="data.planPhase" class="font-semibold capitalize text-wc-text">{{ data.planPhase }}</span>
      activo &mdash; Dia <span class="font-semibold text-wc-text">{{ data.planDaysActive }}</span>
    </span>
    <span class="live-dot shrink-0"></span>
  </div>
</template>
