<template>
  <div
    class="overflow-hidden rounded-xl border bg-wc-bg-secondary transition-colors"
    :class="swapped ? 'border-wc-accent/40' : 'border-wc-border'"
  >
    <SwapBanner
      v-if="swapped && swappedRecipe"
      :replacement-name="swappedRecipe.name"
      :restoring="restoring"
      @restore="$emit('undo-swap')"
    />

    <MealHeader
      :meal="meal"
      :meal-idx="mealIdx"
      :expanded="expanded"
      :swap-panel-open="swapPanelOpen"
      :swapped="swapped"
      @toggle="$emit('toggle')"
      @open-swap="$emit('open-swap')"
    />

    <Transition name="accordion">
      <SwapPanel
        v-if="swapPanelOpen && swapContext"
        :meal-name="swapContext.name"
        :meal-macros="{
          cal: swapContext.calories,
          protein: swapContext.protein,
          carbs: swapContext.carbs,
          fat: swapContext.fat,
        }"
        :search-query="swapSearchQuery"
        :candidates="swapCandidates"
        :applying="applying"
        @update:search-query="$emit('update:swap-search-query', $event)"
        @apply="(r) => $emit('apply-swap', r)"
        @close="$emit('close-swap')"
      />
    </Transition>

    <Transition name="accordion">
      <div v-show="expanded && !swapPanelOpen" class="border-t border-wc-border">
        <MealBody
          :meal="meal"
          :swapped="swapped"
          :swapped-recipe="swappedRecipe"
          :active-option="activeOption"
          @set-option="(key) => $emit('update:active-option', key)"
        />
      </div>
    </Transition>
  </div>
</template>

<script setup>
import MealHeader from './MealHeader.vue';
import MealBody from './MealBody.vue';
import SwapBanner from './SwapBanner.vue';
import SwapPanel from './SwapPanel.vue';

defineProps({
  meal: { type: Object, required: true },
  mealIdx: { type: Number, required: true },
  expanded: { type: Boolean, default: false },
  swapPanelOpen: { type: Boolean, default: false },
  swapped: { type: Boolean, default: false },
  swappedRecipe: { type: Object, default: null },
  swapContext: { type: Object, default: null },
  swapSearchQuery: { type: String, default: '' },
  swapCandidates: { type: Array, default: () => [] },
  applying: { type: Boolean, default: false },
  restoring: { type: Boolean, default: false },
  activeOption: { type: String, default: 'a' },
});

defineEmits([
  'toggle',
  'open-swap',
  'close-swap',
  'apply-swap',
  'undo-swap',
  'update:swap-search-query',
  'update:active-option',
]);
</script>

<style scoped>
.accordion-enter-active,
.accordion-leave-active {
  transition: max-height 0.3s ease, opacity 0.2s ease;
  overflow: hidden;
  max-height: 600px;
}
.accordion-enter-from,
.accordion-leave-to {
  max-height: 0;
  opacity: 0;
}
</style>
