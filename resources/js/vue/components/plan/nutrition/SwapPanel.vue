<template>
  <div class="border-t border-wc-border bg-wc-bg-secondary/30">
    <div class="px-5 py-4">
      <!-- Header -->
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
          <p class="font-display text-[10px] tracking-[0.2em] text-wc-accent/80">ALTERNATIVAS</p>
          <p class="mt-1 truncate text-xs text-wc-text-secondary">
            Para reemplazar: <span class="text-wc-text">{{ mealName }}</span>
          </p>
          <p class="mt-1.5 font-data text-[10px] tabular-nums tracking-wider text-wc-text-tertiary">
            Macros actuales:
            <span class="text-wc-text-secondary">{{ mealMacros.cal }}</span> KCAL
            <span class="mx-1 text-wc-text-tertiary/60">·</span>{{ mealMacros.protein }}P
            <span class="mx-1 text-wc-text-tertiary/60">·</span>{{ mealMacros.carbs }}C
            <span class="mx-1 text-wc-text-tertiary/60">·</span>{{ mealMacros.fat }}G
          </p>
        </div>
        <button
          type="button"
          @click="$emit('close')"
          class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-wc-text-tertiary transition-colors hover:text-wc-text-secondary hover:bg-wc-bg-secondary/50 min-h-[44px] min-w-[44px]"
          aria-label="Cerrar panel de alternativas"
        >
          <X :size="14" :stroke-width="2" />
        </button>
      </div>

      <!-- Search -->
      <div class="relative mt-4">
        <Search :size="13" :stroke-width="2" class="absolute left-3 top-1/2 -translate-y-1/2 text-wc-text-tertiary" />
        <input
          :value="searchQuery"
          @input="$emit('update:searchQuery', $event.target.value)"
          type="text"
          placeholder="Buscar receta"
          class="w-full rounded-xl border border-wc-border bg-wc-bg py-2 pl-9 pr-3 text-sm text-wc-text placeholder:text-wc-text-tertiary transition-colors focus:border-wc-accent/40 focus:outline-none min-h-[44px]"
          aria-label="Buscar receta alternativa"
        />
      </div>

      <!-- Candidates list -->
      <div class="mt-3 max-h-80 space-y-1 overflow-y-auto pr-1">
        <SwapRow
          v-for="({ recipe, score }) in candidates"
          :key="recipe.id"
          :recipe="recipe"
          :score="score"
          :applying="applying"
          @apply="$emit('apply', recipe)"
        />
        <div v-if="candidates.length === 0" class="rounded-xl border border-wc-border bg-wc-bg-secondary/30 p-6 text-center">
          <p class="font-display text-[10px] tracking-[0.2em] text-wc-text-tertiary">SIN RESULTADOS</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { X, Search } from 'lucide-vue-next';
import SwapRow from './SwapRow.vue';

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
