<script setup>
import WcSectionHeader from '../ui/wellcore/WcSectionHeader.vue';

defineProps({
    data: { type: Object, required: true },
    weekMarkers: { type: Array, default: () => [] },
});
</script>

<template>
  <div v-if="data.hasActivePlan" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
    <WcSectionHeader title="Tu progreso">
      <template #actions>
        <span class="text-sm wc-tnum" style="color: var(--color-wc-text-secondary);">
          Semana {{ Math.min(data.weeksActive || 0, data.totalWeeks || 12) }} de {{ data.totalWeeks || 12 }}
        </span>
      </template>
    </WcSectionHeader>

    <div class="relative">
      <div class="h-2.5 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
        <div
          class="prog-line-fg transition-all duration-700 ease-out"
          :style="{ width: (data.progressPercent || 0) + '%' }"
        ></div>
      </div>
      <div
        class="absolute top-1/2 -translate-x-1/2 -translate-y-1/2 transition-all duration-700"
        :style="{ left: (data.progressPercent || 0) + '%' }"
      >
        <div class="prog-indicator h-5 w-5 rounded-full border-[3px] border-wc-accent bg-wc-bg-tertiary"></div>
      </div>
    </div>

    <!-- Week markers -->
    <div class="mt-3 flex items-center justify-between">
      <div class="text-left">
        <p class="wc-caption">Inicio</p>
        <p class="text-sm text-wc-text-secondary">{{ data.startDate || '--' }}</p>
      </div>
      <!-- Desktop week dots -->
      <div class="hidden items-center gap-0 flex-1 mx-4 sm:flex">
        <div v-for="marker in weekMarkers" :key="marker.week" class="flex flex-1 flex-col items-center">
          <div :class="marker.isActive ? 'prog-mk-on' : 'prog-mk-off'"></div>
          <span v-if="marker.showLabel" class="mt-1 text-xs text-wc-text-tertiary">{{ marker.week }}</span>
        </div>
      </div>
      <div class="text-right">
        <p class="wc-caption">
          {{ (data.weeksActive || 0) >= (data.totalWeeks || 12) ? 'Continuo' : 'Semana 12' }}
        </p>
        <p class="text-sm font-semibold wc-tnum text-wc-accent">{{ data.progressPercent || 0 }}%</p>
      </div>
    </div>
  </div>
</template>
