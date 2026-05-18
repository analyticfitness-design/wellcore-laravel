<template>
  <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary overflow-hidden">
    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 px-3 py-2 sm:px-4 sm:py-2.5">
      <span class="font-display text-sm sm:text-base uppercase tracking-[0.2em] text-wc-text">
        {{ planName }}
      </span>

      <span v-if="hasWeek" class="text-wc-text-tertiary" aria-hidden="true">·</span>

      <span v-if="hasWeek" class="text-xs sm:text-sm text-wc-text-secondary">
        {{ t('client_plan.nutrition_strip_week') }}
        <span class="font-data text-wc-text">{{ weekDisplay }}</span>
        <span class="text-wc-text-tertiary">/</span>
        <span class="font-data text-wc-text">{{ totalDisplay }}</span>
      </span>

      <span v-if="dayLabel" class="text-wc-text-tertiary" aria-hidden="true">·</span>

      <span v-if="dayLabel" class="text-xs sm:text-sm text-wc-text-secondary">
        {{ dayLabel }}
      </span>
    </div>

    <div class="h-[3px] w-full bg-wc-bg-secondary" role="progressbar" :aria-valuenow="progress" aria-valuemin="0" aria-valuemax="100">
      <div
        class="h-full bg-wc-accent transition-all duration-500 ease-out"
        :style="{ width: progress + '%' }"
      ></div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
  planName: { type: String, required: true },
  currentWeek: { type: Number, default: null },
  totalWeeks: { type: Number, default: null },
  dayLabel: { type: String, default: '' },
});

const hasWeek = computed(
  () => Number.isFinite(props.currentWeek) && Number.isFinite(props.totalWeeks) && props.totalWeeks > 0
);

const pad2 = (n) => String(n).padStart(2, '0');
const weekDisplay = computed(() => (hasWeek.value ? pad2(props.currentWeek) : '--'));
const totalDisplay = computed(() => (hasWeek.value ? pad2(props.totalWeeks) : '--'));

const progress = computed(() => {
  if (!hasWeek.value) return 0;
  const pct = (props.currentWeek / props.totalWeeks) * 100;
  return Math.min(100, Math.max(0, pct));
});
</script>
