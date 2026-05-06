<template>
  <div class="bg-wc-bg-tertiary border border-wc-border rounded-xl p-4">
    <div :class="containerClass">
      <div
        v-for="macro in macros"
        :key="macro.key"
        class="flex items-start gap-3"
      >
        <span
          class="h-2 w-2 rounded-full mt-2 shrink-0"
          :class="macro.dotClass"
          aria-hidden="true"
        ></span>
        <div class="flex-1 min-w-0">
          <p
            class="text-xs uppercase tracking-widest"
            :class="macro.labelClass"
          >
            {{ macro.label }}
          </p>
          <div class="mt-1 flex items-baseline gap-2 flex-wrap">
            <span class="font-data text-2xl font-bold text-wc-text tabular-nums">
              {{ macro.grams }}g
            </span>
            <span class="font-data text-sm font-semibold text-wc-text-secondary tabular-nums">
              {{ macro.percent }}%
            </span>
          </div>
          <p class="text-sm text-wc-text-tertiary tabular-nums">
            {{ macro.kcal }} kcal
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  proteinG: { type: Number, required: true },
  carbsG: { type: Number, required: true },
  fatG: { type: Number, required: true },
  totalKcal: { type: Number, required: true },
  layout: {
    type: String,
    default: 'grid',
    validator: (v) => ['grid', 'stack'].includes(v),
  },
});

function pct(grams, coef) {
  if (!props.totalKcal || props.totalKcal === 0) return 0;
  return Math.round((grams * coef) / props.totalKcal * 100);
}

const macros = computed(() => [
  {
    key: 'protein',
    label: 'Proteína',
    grams: props.proteinG,
    kcal: props.proteinG * 4,
    percent: pct(props.proteinG, 4),
    dotClass: 'bg-red-400',
    labelClass: 'text-red-400',
  },
  {
    key: 'carbs',
    label: 'Carbos',
    grams: props.carbsG,
    kcal: props.carbsG * 4,
    percent: pct(props.carbsG, 4),
    dotClass: 'bg-blue-400',
    labelClass: 'text-blue-400',
  },
  {
    key: 'fat',
    label: 'Grasas',
    grams: props.fatG,
    kcal: props.fatG * 9,
    percent: pct(props.fatG, 9),
    dotClass: 'bg-amber-400',
    labelClass: 'text-amber-400',
  },
]);

const containerClass = computed(() =>
  props.layout === 'grid'
    ? 'grid grid-cols-1 sm:grid-cols-3 gap-4'
    : 'flex flex-col gap-4'
);
</script>
