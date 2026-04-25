<script setup>
/**
 * Slider WellCore con valor grande en Oswald y labels semánticos
 * en los extremos. Usa <input type="range"> nativo (mejor para
 * accesibilidad y soporte móvil) y `defineModel` para v-model.
 *
 * Props:
 *  - label:        String — texto principal junto al valor
 *  - min, max, step: Number
 *  - leftLabel, rightLabel: String — leyendas en extremos
 *  - suffix:       String — sufijo opcional para el valor (ej: '%', '/10')
 *  - id:           String — para asociar <label for>
 */
const value = defineModel({ type: Number, required: true });

defineProps({
  label: { type: String, required: true },
  min: { type: Number, default: 1 },
  max: { type: Number, default: 10 },
  step: { type: Number, default: 1 },
  leftLabel: { type: String, default: '' },
  rightLabel: { type: String, default: '' },
  suffix: { type: String, default: '' },
  id: { type: String, default: () => `wc-slider-${Math.random().toString(36).slice(2, 8)}` },
});
</script>

<template>
  <div class="space-y-2">
    <div class="flex items-baseline justify-between">
      <label :for="id" class="text-sm font-semibold text-wc-text">{{ label }}</label>
      <span class="font-display text-2xl font-bold leading-none text-wc-accent tabular-nums">
        {{ value }}<span v-if="suffix" class="text-sm font-semibold">{{ suffix }}</span>
      </span>
    </div>

    <input
      :id="id"
      v-model.number="value"
      type="range"
      :min="min"
      :max="max"
      :step="step"
      :aria-valuemin="min"
      :aria-valuemax="max"
      :aria-valuenow="value"
      class="wc-slider w-full"
    />

    <div v-if="leftLabel || rightLabel" class="flex justify-between text-[11px] text-wc-text-tertiary">
      <span>{{ leftLabel }}</span>
      <span>{{ rightLabel }}</span>
    </div>
  </div>
</template>

<style scoped>
.wc-slider {
  -webkit-appearance: none;
  appearance: none;
  height: 6px;
  background: var(--color-wc-bg-secondary, #111113);
  border: 1px solid var(--color-wc-border, rgba(255,255,255,.07));
  border-radius: 9999px;
  outline: none;
  cursor: pointer;
  transition: background-color 0.2s;
}

.wc-slider:focus-visible {
  box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.25);
}

/* WebKit thumb */
.wc-slider::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: var(--color-wc-accent, #DC2626);
  border: 2px solid #fff;
  cursor: pointer;
  box-shadow: 0 2px 8px rgba(220, 38, 38, 0.45);
  transition: transform 0.15s ease;
}
.wc-slider::-webkit-slider-thumb:hover { transform: scale(1.12); }
.wc-slider:active::-webkit-slider-thumb { transform: scale(1.18); }

/* Firefox thumb */
.wc-slider::-moz-range-thumb {
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: var(--color-wc-accent, #DC2626);
  border: 2px solid #fff;
  cursor: pointer;
  box-shadow: 0 2px 8px rgba(220, 38, 38, 0.45);
}

@media (prefers-reduced-motion: reduce) {
  .wc-slider::-webkit-slider-thumb,
  .wc-slider::-webkit-slider-thumb:hover,
  .wc-slider:active::-webkit-slider-thumb {
    transition: none;
    transform: none;
  }
}
</style>
