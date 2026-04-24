<script setup>
import { RouterLink } from 'vue-router';

defineProps({
    weightChartData: { type: Array, default: () => [] },
});

function getWeightBarHeight(weight, min, range) {
    if (range === 0) return 50;
    return ((weight - min) / range) * 70 + 30;
}
</script>

<template>
  <div v-if="weightChartData && weightChartData.length > 0" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
    <div class="mb-4 flex items-center justify-between">
      <h3 class="text-lg font-semibold text-wc-text">Tendencia de peso</h3>
      <span class="text-sm text-wc-text-tertiary">Ultimos 90 dias</span>
    </div>

    <div class="flex items-end justify-center gap-1 sm:gap-2 overflow-x-auto" style="height: 140px;">
      <div
        v-for="(entry, idx) in weightChartData"
        :key="idx"
        class="group relative flex w-8 sm:w-10 shrink-0 flex-col items-center justify-end"
        style="height: 100%;"
      >
        <!-- Tooltip -->
        <div class="pointer-events-none absolute -top-8 z-10 hidden rounded bg-wc-bg-secondary px-2 py-1 text-xs font-medium text-wc-text shadow-lg group-hover:block">
          {{ Number(entry.weight).toFixed(1) }} kg
        </div>
        <!-- Bar -->
        <div
          class="w-full rounded-t bg-wc-accent/80 transition-all group-hover:bg-wc-accent"
          :style="{
            height: getWeightBarHeight(
              entry.weight,
              Math.min(...weightChartData.map(e => e.weight)),
              Math.max(...weightChartData.map(e => e.weight)) - Math.min(...weightChartData.map(e => e.weight)) || 1
            ) + '%'
          }"
        ></div>
        <!-- Label -->
        <span class="mt-1 w-full truncate text-center text-xs text-wc-text-tertiary">
          {{ entry.date ? String(entry.date).slice(0, 5) : '' }}
        </span>
      </div>
    </div>

    <div class="mt-2 flex justify-between text-sm text-wc-text-tertiary">
      <span>Min: {{ Math.min(...weightChartData.map(e => e.weight)).toFixed(1) }} kg</span>
      <span>Max: {{ Math.max(...weightChartData.map(e => e.weight)).toFixed(1) }} kg</span>
    </div>
  </div>
  <!-- Weight chart empty state -->
  <div v-else class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
    <div class="flex flex-col items-center justify-center h-48 text-center">
      <svg class="h-8 w-8 text-wc-text-tertiary/40" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0 0 12 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 0 1-2.031.352 5.988 5.988 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971Zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 0 1-2.031.352 5.989 5.989 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971Z" />
      </svg>
      <p class="mt-2 text-sm text-wc-text-tertiary">Sin datos de peso aun</p>
      <RouterLink to="/client/metrics" class="mt-2 text-sm text-wc-accent hover:underline">Registrar peso</RouterLink>
    </div>
  </div>
</template>
