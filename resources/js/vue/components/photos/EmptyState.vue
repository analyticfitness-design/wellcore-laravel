<script setup>
/**
 * EmptyState — first-run empty hero shown when the user has 0 sessions.
 * 3 ghost slots evoke the upload grid + editorial copy + 2 CTAs.
 *
 * Emits:
 *   start   user clicked "Tomar primera sesión"
 *   guide   user clicked "Ver guía"
 */
defineEmits(['start', 'guide']);
</script>

<template>
  <section
    class="rounded-2xl border border-wc-border bg-wc-bg-tertiary p-8 text-center sm:p-10"
    aria-label="Sin fotos aún"
  >
    <!-- Ghost slots -->
    <div class="mx-auto mb-6 flex justify-center gap-2.5">
      <div
        v-for="(label, i) in ['Frente', 'Perfil', 'Espalda']"
        :key="label"
        class="flex aspect-[3/4] w-16 flex-col items-center justify-center gap-1 rounded-lg border border-dashed border-wc-border bg-[repeating-linear-gradient(45deg,_rgba(255,255,255,0.02)_0_6px,_rgba(255,255,255,0.04)_6px_12px)]"
        :style="{ animationDelay: `${i * 0.08}s` }"
      >
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-3.5 w-3.5 text-wc-text-tertiary opacity-50" aria-hidden="true">
          <rect x="3" y="6" width="18" height="14" rx="2" />
          <circle cx="12" cy="13" r="3.5" />
        </svg>
        <small class="font-mono text-[7px] uppercase tracking-widest text-wc-text-tertiary">
          {{ label }}
        </small>
      </div>
    </div>

    <h3 class="font-display text-2xl font-medium uppercase leading-tight tracking-wide text-wc-text sm:text-3xl">
      Tu primer día
      <em class="block not-italic font-normal text-wc-text-tertiary">queda guardado para siempre.</em>
    </h3>
    <p class="mx-auto mt-3 max-w-md text-sm text-wc-text-secondary">
      Sube tu primera sesión hoy. En 6 semanas vas a mirar atrás y ver lo que el peso no muestra.
    </p>

    <div class="mx-auto mt-6 flex max-w-xs flex-col gap-2">
      <button
        type="button"
        class="inline-flex min-h-[48px] items-center justify-center gap-2 rounded-xl bg-wc-accent px-4 text-sm font-semibold text-white transition-colors hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-2 focus:ring-offset-wc-bg"
        @click="$emit('start')"
      >
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3.5 w-3.5" aria-hidden="true">
          <rect x="3" y="6" width="18" height="14" rx="2" />
          <circle cx="12" cy="13" r="3.5" />
        </svg>
        Tomar primera sesión
      </button>
      <button
        type="button"
        class="inline-flex min-h-[48px] items-center justify-center gap-2 rounded-xl border border-wc-border bg-wc-bg-secondary px-4 text-sm font-semibold text-wc-text transition-colors hover:border-wc-accent/50"
        @click="$emit('guide')"
      >
        Ver guía
      </button>
    </div>
  </section>
</template>
