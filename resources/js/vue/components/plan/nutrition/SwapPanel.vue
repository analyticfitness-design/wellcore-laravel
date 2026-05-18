<template>
  <div class="border-t border-wc-border bg-wc-bg/40">
    <div class="px-4 py-4">
      <!-- Header simplificado -->
      <div class="mb-3 flex items-center justify-between gap-3">
        <p class="font-display text-xs font-medium uppercase tracking-[0.1em] text-wc-text">
          {{ t('client_plan.swap_panel_title') }}
        </p>
        <button
          type="button"
          @click="$emit('close')"
          class="inline-flex min-h-[44px] items-center gap-1 px-2 -mr-2 font-data text-[11px] text-wc-text-tertiary transition hover:text-wc-text"
          :aria-label="t('client_plan.swap_panel_close_aria')"
        >
          {{ t('client_plan.swap_panel_cancel') }}
          <X :size="12" :stroke-width="2.5" />
        </button>
      </div>

      <!-- Search + target macros -->
      <div class="mb-3 flex items-center gap-2 rounded-full border border-wc-border bg-wc-bg-tertiary px-3 py-2">
        <Search :size="14" :stroke-width="2" class="shrink-0 text-wc-text-tertiary" />
        <input
          :value="searchQuery"
          @input="$emit('update:searchQuery', $event.target.value)"
          type="text"
          :placeholder="t('client_plan.swap_search_placeholder')"
          class="min-w-0 flex-1 bg-transparent text-sm text-wc-text placeholder:text-wc-text-tertiary focus:outline-none"
          :aria-label="t('client_plan.swap_search_aria')"
        />
        <span
          v-if="mealMacros && (mealMacros.protein || mealMacros.carbs || mealMacros.fat)"
          class="hidden shrink-0 font-data text-[10px] tabular-nums text-wc-text-tertiary sm:flex items-center gap-1.5"
        >
          <span class="text-red-400">P {{ mealMacros.protein }}</span>
          <span class="text-wc-text-tertiary/50">·</span>
          <span class="text-blue-400">C {{ mealMacros.carbs }}</span>
          <span class="text-wc-text-tertiary/50">·</span>
          <span class="text-amber-400">G {{ mealMacros.fat }}</span>
        </span>
      </div>

      <!-- Mobile target macros (separate row) -->
      <p
        v-if="mealMacros && (mealMacros.protein || mealMacros.carbs || mealMacros.fat)"
        class="mb-2 flex items-center gap-2 px-1 font-data text-[10px] tabular-nums text-wc-text-tertiary sm:hidden"
      >
        <span>{{ t('client_plan.swap_target_label') }}</span>
        <span class="text-red-400">P {{ mealMacros.protein }}</span>
        <span class="text-wc-text-tertiary/50">·</span>
        <span class="text-blue-400">C {{ mealMacros.carbs }}</span>
        <span class="text-wc-text-tertiary/50">·</span>
        <span class="text-amber-400">G {{ mealMacros.fat }}</span>
      </p>

      <!-- Candidates list -->
      <div class="max-h-80 space-y-1.5 overflow-y-auto pr-1">
        <SwapRow
          v-for="({ recipe, score }) in candidates"
          :key="recipe.id"
          :recipe="recipe"
          :score="score"
          :applying="applying"
          @apply="$emit('apply', recipe)"
        />
        <div v-if="candidates.length === 0" class="rounded-xl border border-wc-border bg-wc-bg-secondary/30 p-6 text-center">
          <p class="font-display text-[10px] tracking-[0.2em] text-wc-text-tertiary">{{ t('client_plan.swap_no_results') }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { X, Search } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import SwapRow from './SwapRow.vue';

const { t } = useI18n();

defineProps({
  mealName: {
    type: String,
    required: true,
  },
  mealMacros: {
    type: Object,
    required: true,
  },
  searchQuery: {
    type: String,
    default: '',
  },
  candidates: {
    type: Array,
    required: true,
  },
  applying: {
    type: Boolean,
    default: false,
  },
});

defineEmits(['update:searchQuery', 'apply', 'close']);
</script>
