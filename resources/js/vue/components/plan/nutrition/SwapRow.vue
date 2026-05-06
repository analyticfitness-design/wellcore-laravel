<template>
  <button
    type="button"
    :disabled="applying"
    :aria-label="`Reemplazar comida con ${recipe.name}`"
    class="grid w-full grid-cols-[auto_1fr_auto] items-center gap-x-3 gap-y-1.5 rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2.5 text-left transition hover:border-wc-accent/30 hover:bg-wc-bg-secondary/60 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-40"
    :class="{ 'opacity-50': score === 'fuera' }"
    @click="emit('apply')"
  >
    <!-- Row 1 col 1: badge score -->
    <span
      class="justify-self-start rounded-full border px-2 py-0.5 font-display text-[9px] font-semibold uppercase tracking-[0.14em]"
      :class="badgeClass"
    >
      {{ scoreLabel }}
    </span>

    <!-- Row 1 col 2: spacer (name fills row 2 full-width) -->
    <span></span>

    <!-- Row 1 col 3: kcal big -->
    <span class="justify-self-end text-right leading-none">
      <span class="font-display text-base font-medium tabular-nums text-wc-text">{{ recipe.macros.cal }}</span>
      <span class="ml-1 font-data text-[9px] uppercase tracking-[0.1em] text-wc-text-tertiary">kcal</span>
    </span>

    <!-- Row 2 (col-span 3): name -->
    <span class="col-span-3 font-display text-xs font-medium uppercase tracking-wide text-wc-text leading-tight">
      {{ recipe.name }}
    </span>

    <!-- Row 3 (col-span 3): macros -->
    <span class="col-span-3 flex items-center gap-2.5 font-data text-[10px] tabular-nums">
      <span class="text-red-400">P {{ recipe.macros.protein }}</span>
      <span class="text-blue-400">C {{ recipe.macros.carbs }}</span>
      <span class="text-amber-400">G {{ recipe.macros.fat }}</span>
    </span>
  </button>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  recipe: {
    type: Object,
    required: true,
  },
  score: {
    type: String,
    required: true,
    validator: (value) => ['ideal', 'aceptable', 'fuera'].includes(value),
  },
  applying: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['apply']);

const SCORE_MAP = {
  ideal: {
    badgeClass: 'border-emerald-500/30 bg-emerald-500/15 text-emerald-400',
    label: 'Ideal',
  },
  aceptable: {
    badgeClass: 'border-amber-500/30 bg-amber-500/15 text-amber-400',
    label: 'Aceptable',
  },
  fuera: {
    badgeClass: 'border-wc-border bg-wc-bg-tertiary text-wc-text-tertiary',
    label: 'Fuera',
  },
};

const badgeClass = computed(() => SCORE_MAP[props.score].badgeClass);
const scoreLabel = computed(() => SCORE_MAP[props.score].label);
</script>
