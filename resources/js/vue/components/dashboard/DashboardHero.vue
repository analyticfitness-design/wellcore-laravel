<script setup>
import { RouterLink } from 'vue-router';

defineProps({
    data: { type: Object, required: true },
    greeting: { type: String, required: true },
    motivationalQuote: { type: String, default: '' },
});
</script>

<template>
  <!-- M64 — Hero Premium: orbe rojo + grain + glass -->
  <div class="wc-hero-premium relative overflow-hidden rounded-2xl">
    <div class="wc-orb-tr" aria-hidden="true"></div>
    <!-- Orbe rojo superior -->
    <div class="pointer-events-none absolute -top-10 left-1/2 h-52 w-80 -translate-x-1/2 rounded-full opacity-100" style="background:radial-gradient(ellipse,rgba(220,38,38,0.28) 0%,transparent 70%);filter:blur(32px);z-index:0;"></div>
    <!-- Contenido glass -->
    <div class="relative z-10 flex flex-col gap-4 rounded-2xl border border-white/10 bg-white/[0.06] p-5 backdrop-blur-xl sm:flex-row sm:items-center sm:justify-between" style="backdrop-filter:blur(24px) saturate(1.8);">
    <div>
      <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">
        {{ greeting }}, {{ data.clientName }}
      </h1>
      <div v-if="data.planLabel" class="mt-2 flex items-center gap-2">
        <span class="inline-flex rounded-full bg-wc-accent/10 px-3 py-1 text-xs font-semibold text-wc-accent">
          Plan {{ data.planLabel }}
        </span>
      </div>
      <!-- Plan-specific motivational phrase -->
      <div class="mt-3 flex items-start gap-2">
        <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-wc-accent/60" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path d="M4.583 17.321C3.553 16.227 3 15 3 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621.537-.278 1.24-.375 1.929-.311 1.804.167 3.226 1.648 3.226 3.489a3.5 3.5 0 0 1-3.5 3.5c-1.073 0-2.099-.49-2.748-1.179Zm10 0C13.553 16.227 13 15 13 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621.537-.278 1.24-.375 1.929-.311 1.804.167 3.226 1.648 3.226 3.489a3.5 3.5 0 0 1-3.5 3.5c-1.073 0-2.099-.49-2.748-1.179Z" />
        </svg>
        <p class="text-base italic leading-snug text-wc-text-secondary">{{ motivationalQuote }}</p>
      </div>
    </div>

    <!-- Quick actions (desktop) -->
    <div class="hidden items-center gap-2 sm:flex">
      <RouterLink
        to="/client/plan"
        class="btn-ripple inline-flex items-center gap-2 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-wc-accent-hover"
      >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        Registrar entrenamiento
      </RouterLink>
      <RouterLink
        to="/client/checkin"
        class="inline-flex items-center gap-2 rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2 text-sm font-medium text-wc-text transition-colors hover:bg-wc-bg-secondary"
      >
        Hacer check-in
      </RouterLink>
    </div>
    </div><!-- /glass content -->
  </div><!-- /wc-hero-premium -->
</template>
