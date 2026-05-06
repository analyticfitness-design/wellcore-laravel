<template>
  <div
    class="grid grid-cols-[auto_1fr_auto_auto] items-center gap-3 rounded-xl px-3 py-2 transition-colors hover:bg-wc-bg-secondary/50"
    :class="{ 'opacity-40 hover:opacity-70': score === 'fuera' }"
  >
    <span class="text-2xl leading-none">{{ recipe.emoji }}</span>

    <div class="min-w-0">
      <p class="truncate font-display text-sm tracking-wide text-wc-text">{{ recipe.name }}</p>
      <p class="font-data text-[10px] tabular-nums tracking-wider text-wc-text-tertiary">
        {{ recipe.macros.cal }} KCAL <span class="mx-1 text-wc-text-tertiary/60">·</span>
        {{ recipe.macros.protein }}P <span class="mx-1 text-wc-text-tertiary/60">·</span>
        {{ recipe.macros.carbs }}C <span class="mx-1 text-wc-text-tertiary/60">·</span>
        {{ recipe.macros.fat }}G
      </p>
    </div>

    <div class="flex items-center gap-1.5">
      <span class="h-1 w-1 rounded-full" :class="dotClass"></span>
      <span class="font-display text-[9px] tracking-[0.2em]" :class="labelClass">{{ scoreLabel }}</span>
    </div>

    <button
      type="button"
      :disabled="applying"
      :aria-label="`Reemplazar comida con ${recipe.name}`"
      class="min-h-[32px] rounded-full border border-wc-border px-3 py-1 font-display text-[10px] tracking-[0.15em] text-wc-text-secondary transition hover:border-wc-accent/40 hover:text-wc-accent disabled:opacity-40 disabled:hover:border-wc-border disabled:hover:text-wc-text-secondary"
      @click="emit('apply')"
    >
      REEMPLAZAR
    </button>
  </div>
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
    dotClass: 'bg-emerald-400',
    labelClass: 'text-emerald-400/70',
    label: 'IDEAL',
  },
  aceptable: {
    dotClass: 'bg-amber-400',
    labelClass: 'text-amber-400/70',
    label: 'ACEPTABLE',
  },
  fuera: {
    dotClass: 'bg-wc-text-tertiary/40',
    labelClass: 'text-wc-text-tertiary',
    label: 'FUERA',
  },
};

const dotClass = computed(() => SCORE_MAP[props.score].dotClass);
const labelClass = computed(() => SCORE_MAP[props.score].labelClass);
const scoreLabel = computed(() => SCORE_MAP[props.score].label);
</script>
