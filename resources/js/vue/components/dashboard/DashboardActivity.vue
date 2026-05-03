<script setup>
import WcSectionHeader from '../ui/wellcore/WcSectionHeader.vue';

defineProps({
    activities: { type: Array, default: () => [] },
});
</script>

<template>
  <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
    <WcSectionHeader title="Actividad reciente" />

    <ul v-if="activities && activities.length > 0" class="mt-4 space-y-3">
      <li v-for="(activity, idx) in activities" :key="idx" class="flex items-start gap-3">
        <!-- Color dot -->
        <span :class="['act-dot', `act-dot-${activity.type || 'default'}`]"></span>
        <!-- Text -->
        <div class="min-w-0 flex-1">
          <p class="text-sm font-medium text-wc-text">{{ activity.description }}</p>
          <p class="text-xs text-wc-text-secondary">{{ activity.timeAgo }}</p>
        </div>
      </li>
    </ul>

    <!-- Empty activity state -->
    <div v-else class="mt-6 flex flex-col items-center py-4 text-center">
      <svg class="h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
      </svg>
      <p class="mt-2 text-sm text-wc-text-tertiary">Sin actividad reciente</p>
    </div>
  </div>
</template>
