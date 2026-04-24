<script setup>
defineProps({
    data: { type: Object, required: true },
    weeklySummaryMessage: { type: Object, default: () => ({ label: '', desc: '', colorClass: '' }) },
});
</script>

<template>
  <div v-if="data.hasLastWeekData" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
    <div class="mb-4 flex items-center gap-2">
      <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-sky-500/10">
        <svg class="h-4 w-4 text-sky-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
        </svg>
      </div>
      <h2 class="text-lg font-semibold text-wc-text">Resumen semanal</h2>
    </div>

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
      <div class="rounded-xl bg-wc-bg-secondary px-4 py-3 text-center">
        <p class="font-data text-2xl font-bold text-wc-text">{{ data.lastWeekWorkouts || 0 }}</p>
        <p class="mt-0.5 text-sm text-wc-text-tertiary">Entrenamientos</p>
      </div>
      <div class="rounded-xl bg-wc-bg-secondary px-4 py-3 text-center">
        <p class="font-data text-2xl font-bold text-wc-text">{{ data.lastWeekCheckins || 0 }}</p>
        <p class="mt-0.5 text-sm text-wc-text-tertiary">Check-ins</p>
      </div>
      <div class="col-span-2 rounded-xl bg-wc-bg-secondary px-4 py-3 text-center sm:col-span-1">
        <p class="font-data text-2xl font-bold text-wc-text">{{ data.lastWeekWeight || '--' }}</p>
        <p class="mt-0.5 text-sm text-wc-text-tertiary">{{ data.lastWeekWeight ? 'kg actuales' : 'Sin registro' }}</p>
      </div>
    </div>

    <!-- Motivational text based on performance -->
    <div class="mt-4 rounded-xl border border-wc-accent/10 bg-wc-accent/5 px-4 py-2.5">
      <p class="text-sm text-wc-text-tertiary">
        <span :class="['font-semibold', weeklySummaryMessage.colorClass]">{{ weeklySummaryMessage.label }}</span>
        &mdash; {{ weeklySummaryMessage.desc }}
      </p>
    </div>
  </div>
  <!-- Weekly summary empty state (first week) -->
  <div v-else class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
    <div class="mb-3 flex items-center gap-2">
      <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-sky-500/10">
        <svg class="h-4 w-4 text-sky-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
        </svg>
      </div>
      <h2 class="text-lg font-semibold text-wc-text">Resumen semanal</h2>
    </div>
    <div class="flex items-center gap-3 rounded-xl border border-wc-accent/10 bg-wc-accent/5 px-4 py-3">
      <svg class="h-5 w-5 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
      </svg>
      <p class="text-sm text-wc-text-secondary">
        <span class="font-semibold text-wc-text">Tu primera semana</span> &mdash; Completa tu primer entrenamiento y check-in para ver tu resumen aqui.
      </p>
    </div>
  </div>
</template>
