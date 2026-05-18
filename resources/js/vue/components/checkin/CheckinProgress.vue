<script setup>
/**
 * Progress indicator para el wizard del check-in (4 pasos).
 * Cada paso muestra un círculo con número o ✓, una línea conectora,
 * y un label corto. Permite hacer click en pasos previos para navegar.
 *
 * Props:
 *  - steps: Array<{ key: string, label: string }>
 *  - current: Number (1-based)
 *
 * Emits:
 *  - 'go' (stepNumber)  → click en un step alcanzado
 */
const props = defineProps({
  steps: { type: Array, required: true },
  current: { type: Number, required: true },
});

const emit = defineEmits(['go']);

function handleClick(idx) {
  const target = idx + 1;
  // Solo permitimos navegar a pasos ya completados (anteriores al actual).
  if (target < props.current) emit('go', target);
}
</script>

<template>
  <ol class="flex items-start gap-1.5 sm:gap-2">
    <li
      v-for="(step, idx) in steps"
      :key="step.key"
      class="relative flex flex-1 flex-col items-center gap-1.5"
    >
      <!-- Connector line (todos menos el último) -->
      <span
        v-if="idx < steps.length - 1"
        :class="[
          'absolute left-[calc(50%+18px)] top-3.5 h-0.5 w-[calc(100%-32px)] sm:top-4 sm:h-[2px]',
          idx + 1 < current ? 'bg-wc-accent' : 'bg-wc-border'
        ]"
        aria-hidden="true"
      ></span>

      <!-- Circle -->
      <button
        type="button"
        @click="handleClick(idx)"
        :disabled="idx + 1 >= current"
        :aria-current="idx + 1 === current ? 'step' : undefined"
        :class="[
          'relative z-10 flex h-7 w-7 items-center justify-center rounded-full border-2 font-display text-xs font-semibold transition-all sm:h-8 sm:w-8 sm:text-sm',
          idx + 1 < current
            ? 'border-wc-accent bg-wc-accent text-white cursor-pointer hover:scale-105'
            : idx + 1 === current
              ? 'border-wc-accent bg-wc-bg text-wc-accent ring-2 ring-wc-accent/20'
              : 'border-wc-border bg-wc-bg-secondary text-wc-text-tertiary cursor-not-allowed'
        ]"
      >
        <svg
          v-if="idx + 1 < current"
          class="h-3.5 w-3.5 sm:h-4 sm:w-4"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
          stroke-width="3"
          aria-hidden="true"
        >
          <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
        </svg>
        <span v-else>{{ idx + 1 }}</span>
      </button>

      <!-- Label -->
      <span
        :class="[
          'text-center text-[10px] leading-tight tracking-wide sm:text-xs',
          idx + 1 === current
            ? 'font-semibold text-wc-accent'
            : idx + 1 < current
              ? 'text-wc-text-secondary'
              : 'text-wc-text-tertiary'
        ]"
      >{{ step.label }}</span>
    </li>
  </ol>
</template>
