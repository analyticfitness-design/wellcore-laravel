<template>
  <div
    class="relative overflow-hidden rounded-xl border bg-wc-bg-secondary transition-colors"
    :class="[
      swapped ? 'border-wc-accent/40' : (isCurrent ? 'border-wc-border-strong' : 'border-wc-border'),
      isCurrent ? 'shadow-[0_0_0_1px_rgba(220,38,38,0.15)]' : ''
    ]"
  >
    <!-- Border-left accent color por tipo de meal (sutil pero da vida) -->
    <div
      aria-hidden="true"
      class="pointer-events-none absolute left-0 top-0 bottom-0 w-[3px]"
      :class="leftAccentClass"
    ></div>
    <SwapBanner
      v-if="swapped && swappedRecipe"
      :original-name="originalName"
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
      :is-current="isCurrent"
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
      <div v-show="expanded && !swapPanelOpen">
        <MealBody
          :meal="meal"
          :swapped="swapped"
          :swapped-recipe="swappedRecipe"
          :active-option="activeOption"
          @set-option="(key) => $emit('update:active-option', key)"
          @mark-meal="$emit('mark-meal')"
          @open-swap="$emit('open-swap')"
        />
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import MealHeader from './MealHeader.vue';
import MealBody from './MealBody.vue';
import SwapBanner from './SwapBanner.vue';
import SwapPanel from './SwapPanel.vue';

const props = defineProps({
  meal: { type: Object, required: true },
  mealIdx: { type: Number, required: true },
  isCurrent: { type: Boolean, default: false },
  expanded: { type: Boolean, default: false },
  swapPanelOpen: { type: Boolean, default: false },
  swapped: { type: Boolean, default: false },
  swappedRecipe: { type: Object, default: null },
  originalName: { type: String, default: '' },
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
  'mark-meal',
  'update:swap-search-query',
  'update:active-option',
]);

// Color accent del border-left segun tipo de meal — matchea el badge del index.
const leftAccentClass = computed(() => {
  if (props.swapped) return 'bg-wc-accent';
  const n = (props.meal?.nombre || props.meal?.name || '').toLowerCase();
  if (n.includes('desayuno')) return 'bg-amber-500/60';
  if (n.includes('pre-entreno') || n.includes('pre entreno')) return 'bg-emerald-500/60';
  if (n.includes('almuerzo') || n.includes('post-entreno') || n.includes('post entreno')) return 'bg-blue-500/60';
  if (n.includes('cena')) return 'bg-indigo-500/60';
  if (n.includes('snack') || n.includes('merienda')) return 'bg-pink-500/60';
  return 'bg-wc-accent/40';
});
</script>

<style scoped>
.accordion-enter-active,
.accordion-leave-active {
  transition: max-height 0.3s ease, opacity 0.2s ease;
  overflow: hidden;
  max-height: 800px;
}
.accordion-enter-from,
.accordion-leave-to {
  max-height: 0;
  opacity: 0;
}
</style>
