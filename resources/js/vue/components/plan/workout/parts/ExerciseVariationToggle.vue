<template>
  <div class="variation-wrap" v-if="hasVariation">
    <span v-if="isUsingVariant" class="variation-active-mark" data-testid="variation-active-mark">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
      </svg>
      Usando variación{{ variantName ? ` · ${variantName}` : '' }}
    </span>
    <button
      type="button"
      class="variation-toggle"
      :class="{ 'is-loading': isToggling }"
      :disabled="isToggling"
      :aria-pressed="isUsingVariant"
      data-testid="variation-toggle-btn"
      @click="onClick"
    >
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/>
      </svg>
      {{ buttonLabel }}
    </button>
  </div>
</template>

<script setup>
// ExerciseVariationToggle — chip pill que alterna entre original y variación.
// CSS lines 684-708 del HTML V2.1.
// El backend persiste el flag en plan_exercise_variations sin tocar gif_url original.
import { computed } from 'vue';

const props = defineProps({
  hasVariation: { type: Boolean, default: false },
  isUsingVariant: { type: Boolean, default: false },
  variantName: { type: String, default: '' },
  isToggling: { type: Boolean, default: false },
});

const emit = defineEmits(['toggle']);

const buttonLabel = computed(() => {
  if (props.isToggling) return 'Cambiando...';
  return props.isUsingVariant ? 'Volver al original' : 'Usar variación';
});

function onClick() {
  if (props.isToggling) return;
  emit('toggle', !props.isUsingVariant);
}
</script>

<style scoped>
.variation-wrap {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 4px;
}
.variation-active-mark {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  margin-top: 4px;
  font-family: var(--font-mono, 'JetBrains Mono', ui-monospace, monospace);
  font-size: 9.5px;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: rgba(239, 68, 68, 0.85);
}
.variation-active-mark svg {
  width: 9px;
  height: 9px;
}
.variation-toggle {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 4px 10px;
  margin-top: 6px;
  border-radius: 999px;
  border: 1px solid var(--wc-border);
  background: rgba(255, 255, 255, 0.04);
  font-family: var(--font-mono, 'JetBrains Mono', ui-monospace, monospace);
  font-size: 10px;
  letter-spacing: 0.10em;
  text-transform: uppercase;
  color: var(--wc-text-secondary);
  cursor: pointer;
  transition: all 0.15s ease;
}
.variation-toggle:hover:not(.is-loading) {
  color: #EF4444;
  border-color: rgba(220, 38, 38, 0.40);
}
.variation-toggle:active:not(.is-loading) {
  transform: translateY(1px);
}
.variation-toggle.is-loading {
  opacity: 0.6;
  cursor: progress;
}
.variation-toggle svg {
  width: 10px;
  height: 10px;
}
:global(html.light) .variation-toggle {
  background: rgba(0, 0, 0, 0.04);
}
</style>
