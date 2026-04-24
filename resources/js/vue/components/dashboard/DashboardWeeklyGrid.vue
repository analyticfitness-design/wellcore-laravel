<script setup>
defineProps({
    weekDays: { type: Array, default: () => [] },
});
</script>

<template>
  <div v-if="weekDays && weekDays.length > 0" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
    <h2 class="text-lg font-semibold text-wc-text">Semana de entrenamiento</h2>
    <p class="mt-1 text-sm text-wc-text-secondary">Semana {{ new Date().toLocaleDateString('es', { year: 'numeric' }).split('/').pop() }}</p>

    <div class="mt-5 flex items-center justify-between gap-2 sm:justify-start sm:gap-4">
      <div v-for="(day, idx) in weekDays" :key="idx" class="flex flex-col items-center gap-2">
        <span :class="['text-sm font-medium text-wc-text-secondary', day.isToday ? '!text-wc-accent !font-semibold' : '']">
          {{ day.label }}
        </span>
        <!-- Completed day -->
        <div v-if="day.completed" class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-500/15 sm:h-12 sm:w-12">
          <svg class="h-5 w-5 text-emerald-500 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
          </svg>
        </div>
        <!-- Pending day -->
        <div v-else :class="['flex h-10 w-10 items-center justify-center rounded-full border-2 border-wc-border sm:h-12 sm:w-12', day.isToday ? '!border-wc-accent/40' : '']">
          <div v-if="day.isToday" class="h-2 w-2 rounded-full bg-wc-accent"></div>
        </div>
      </div>
    </div>

    <!-- Legend -->
    <div class="mt-5 flex items-center gap-4 text-sm text-wc-text-secondary">
      <div class="flex items-center gap-1.5">
        <div class="h-2.5 w-2.5 rounded-full bg-emerald-500/40"></div>
        Completado
      </div>
      <div class="flex items-center gap-1.5">
        <div class="h-2.5 w-2.5 rounded-full border border-wc-border"></div>
        Pendiente
      </div>
      <div class="flex items-center gap-1.5">
        <div class="h-2.5 w-2.5 rounded-full bg-wc-accent"></div>
        Hoy
      </div>
    </div>
  </div>
</template>
