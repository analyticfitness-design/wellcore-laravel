<script setup>
const props = defineProps({
    data: { type: Object, required: true },
    calendarDays: { type: Array, default: () => [] },
});

function getCalendarColor(count) {
    if (count >= 5) return 'bg-wc-accent';
    if (count >= 4) return 'bg-wc-accent/80';
    if (count >= 3) return 'bg-wc-accent/60';
    if (count === 2) return 'bg-wc-accent/40';
    if (count === 1) return 'bg-wc-accent/20';
    return 'bg-wc-bg-secondary';
}

function getCalendarCount(dateStr) {
    if (!props.data?.streakCalendar) return 0;
    return props.data.streakCalendar[dateStr] || 0;
}
</script>

<template>
  <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
    <div class="mb-3 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-orange-500/10">
          <svg class="h-4 w-4 text-orange-500" viewBox="0 0 24 24" fill="currentColor">
            <path fill-rule="evenodd" d="M12.963 2.286a.75.75 0 0 0-1.071-.136 9.742 9.742 0 0 0-3.539 6.176A7.547 7.547 0 0 1 6.648 6.61a.75.75 0 0 0-1.152.082A9 9 0 1 0 15.68 4.534a7.46 7.46 0 0 1-2.717-2.248ZM15.75 14.25a3.75 3.75 0 1 1-7.313-1.172c.628.465 1.35.81 2.133 1a5.99 5.99 0 0 1 1.925-3.546 3.75 3.75 0 0 1 3.255 3.718Z" clip-rule="evenodd" />
          </svg>
        </div>
        <h3 class="text-lg font-semibold text-wc-text">Racha de entrenamiento</h3>
        <span v-if="(data.calendarStreak || 0) > 0" class="inline-flex items-center gap-1 rounded-full bg-orange-500/10 px-2 py-0.5 text-xs font-bold text-orange-500">
          {{ data.calendarStreak }} dia{{ data.calendarStreak !== 1 ? 's' : '' }} seguido{{ data.calendarStreak !== 1 ? 's' : '' }}
        </span>
      </div>
      <span class="hidden text-sm text-wc-text-tertiary sm:inline">Ultimos 90 dias</span>
    </div>

    <!-- Calendar grid -->
    <div class="flex gap-0.5 overflow-x-auto pb-1">
      <!-- Day labels -->
      <div class="flex flex-col gap-0.5 pr-1 shrink-0">
        <span class="h-2.5 w-4 text-xs leading-tight text-wc-text-tertiary sm:h-3">L</span>
        <span class="h-2.5 w-4 text-xs leading-tight text-wc-text-tertiary sm:h-3">M</span>
        <span class="h-2.5 w-4 text-xs leading-tight text-wc-text-tertiary sm:h-3">X</span>
        <span class="h-2.5 w-4 text-xs leading-tight text-wc-text-tertiary sm:h-3">J</span>
        <span class="h-2.5 w-4 text-xs leading-tight text-wc-text-tertiary sm:h-3">V</span>
        <span class="h-2.5 w-4 text-xs leading-tight text-wc-text-tertiary sm:h-3">S</span>
        <span class="h-2.5 w-4 text-xs leading-tight text-wc-text-tertiary sm:h-3">D</span>
      </div>

      <!-- Grid -->
      <div class="grid grid-flow-col grid-rows-7 gap-0.5 flex-1">
        <div
          v-for="day in calendarDays"
          :key="day.date"
          :class="[
            'h-2.5 w-2.5 rounded-[2px] sm:h-3 sm:w-3 sm:rounded-sm transition-all duration-150 hover:scale-125 hover:z-10 relative',
            day.isFuture || day.isBeforeRange
              ? 'bg-wc-bg-secondary/30'
              : getCalendarColor(getCalendarCount(day.date)),
            day.isToday ? 'ring-1 ring-wc-text/30' : ''
          ]"
          :style="day.isFuture ? 'opacity: 0.2' : ''"
          :title="day.displayDate + (getCalendarCount(day.date) ? ' - ' + getCalendarCount(day.date) + ' sesion(es)' : '')"
        ></div>
      </div>
    </div>

    <!-- Legend -->
    <div class="mt-2 flex items-center justify-between">
      <span class="text-xs text-wc-text-tertiary sm:hidden">Ultimos 90 dias</span>
      <div class="ml-auto flex items-center gap-1 text-sm text-wc-text-tertiary">
        <span>Menos</span>
        <div class="h-2 w-2 rounded-[2px] bg-wc-bg-secondary sm:h-2.5 sm:w-2.5 sm:rounded-sm"></div>
        <div class="h-2 w-2 rounded-[2px] bg-wc-accent/40 sm:h-2.5 sm:w-2.5 sm:rounded-sm"></div>
        <div class="h-2 w-2 rounded-[2px] bg-wc-accent/70 sm:h-2.5 sm:w-2.5 sm:rounded-sm"></div>
        <div class="h-2 w-2 rounded-[2px] bg-wc-accent sm:h-2.5 sm:w-2.5 sm:rounded-sm"></div>
        <span>Mas</span>
      </div>
    </div>
  </div>
</template>
