<script setup>
import MetricsV2 from './MetricsV2.vue';
import MetricsTrackerLegacy from './MetricsTracker.legacy.vue';
import { useFeatureFlag } from '../../composables/useFeatureFlag';

const useV2 = useFeatureFlag('metrics_v2');
</script>

<template>
  <MetricsV2 v-if="useV2" />
  <MetricsTrackerLegacy v-else />
</template>
