<script setup>
/**
 * Picker de días entrenados (0-7). 8 botones tipo pill horizontales.
 * Tap único — cero teclado en mobile.
 *
 * Props:
 *  - max: Number (default 7) — total de días posibles
 */
const value = defineModel({ type: Number, required: true });

const props = defineProps({
  max: { type: Number, default: 7 },
});

function selectDay(d) {
  value.value = d;
}
</script>

<template>
  <div
    class="grid grid-cols-8 gap-1.5 sm:gap-2"
    role="radiogroup"
    aria-label="Días entrenados esta semana"
  >
    <button
      v-for="d in max + 1"
      :key="d - 1"
      type="button"
      role="radio"
      :aria-checked="value === d - 1"
      @click="selectDay(d - 1)"
      :class="[
        'flex aspect-square flex-col items-center justify-center rounded-lg border font-display text-sm font-bold leading-none transition-all sm:text-base',
        value === d - 1
          ? 'border-wc-accent bg-wc-accent text-white shadow-[0_2px_8px_rgba(220,38,38,0.35)]'
          : 'border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:border-wc-accent/40 hover:text-wc-accent'
      ]"
    >
      <span>{{ d - 1 }}</span>
    </button>
  </div>
</template>
