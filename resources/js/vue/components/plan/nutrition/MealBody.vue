<template>
  <div class="flex flex-col gap-3.5 px-4 py-4">

    <!-- 1. Swapped recipe details -->
    <template v-if="swapped && swappedRecipe">
      <div class="rounded-xl border border-wc-accent/30 bg-wc-accent/5 p-3.5">
        <div class="mb-2.5 flex items-center gap-2">
          <span v-if="swappedRecipe.emoji" class="text-lg leading-none">{{ swappedRecipe.emoji }}</span>
          <span class="font-display text-[11px] tracking-[0.2em] text-wc-accent">RECETA DE REEMPLAZO</span>
        </div>
        <p v-if="swappedRecipe.name" class="mb-3 font-display text-sm tracking-wide text-wc-text">
          {{ swappedRecipe.name.toUpperCase() }}
        </p>
        <p v-if="swappedRecipe.description" class="mb-3 text-xs leading-relaxed text-wc-text-tertiary">
          {{ swappedRecipe.description }}
        </p>

        <p class="mb-1.5 font-display text-[10px] tracking-[0.18em] text-wc-text-secondary">INGREDIENTES</p>
        <ul>
          <MealItem
            v-for="(ing, ii) in swappedRecipe.ingredients || []"
            :key="ii"
            :food="ing"
            :icon="getFoodIcon(ing)"
            :show-icon="true"
          />
        </ul>

        <template v-if="swappedRecipe.steps && swappedRecipe.steps.length">
          <p class="mt-3.5 mb-1.5 font-display text-[10px] tracking-[0.18em] text-wc-text-secondary">PREPARACION</p>
          <ol class="space-y-1.5">
            <li
              v-for="(step, si) in swappedRecipe.steps"
              :key="si"
              class="flex gap-2.5 text-sm leading-relaxed text-wc-text-secondary"
            >
              <span class="font-data text-xs text-wc-accent shrink-0">{{ si + 1 }}.</span>
              <span>{{ step }}</span>
            </li>
          </ol>
        </template>

        <div
          v-if="swappedRecipe.coachTip"
          class="mt-3 rounded-lg border border-wc-accent/20 bg-wc-bg-tertiary/50 px-3 py-2"
        >
          <p class="text-xs italic leading-relaxed text-wc-text-tertiary">{{ swappedRecipe.coachTip }}</p>
        </div>
      </div>
    </template>

    <!-- 2. Multi-option tabs -->
    <template v-else-if="hasMultiOption">
      <div class="flex gap-1.5">
        <button
          v-for="(_optAlimentos, optKey) in availableOptions"
          :key="optKey"
          type="button"
          @click="emit('set-option', optKey)"
          class="rounded-full px-3 py-1 text-xs font-semibold transition min-h-[36px]"
          :class="(activeOption || 'a') === optKey
            ? 'bg-wc-accent text-white'
            : 'bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-text border border-wc-border'"
        >Opcion {{ optKey.toUpperCase() }}</button>
      </div>
      <ul>
        <MealItem
          v-for="(alimento, ai) in (availableOptions[activeOption || 'a'] || [])"
          :key="ai"
          :food="alimento"
        />
      </ul>
    </template>

    <!-- 3. Standard alimentos list -->
    <ul v-else-if="standardFoods.length > 0">
      <MealItem
        v-for="(alimento, ai) in standardFoods"
        :key="ai"
        :food="alimento"
      />
    </ul>

    <!-- "POR QUÉ" inline (HTML target m-meal-note) -->
    <div
      v-if="meal.notas"
      class="rounded-lg border border-wc-border bg-wc-bg-tertiary px-3.5 py-3"
    >
      <p class="mb-1.5 font-data text-[9px] uppercase tracking-[0.14em] text-wc-text-tertiary">
        Por qué
      </p>
      <p class="text-xs leading-relaxed text-wc-text-secondary">{{ meal.notas }}</p>
    </div>

    <!-- Actions row: Marcar / Cambiar -->
    <div class="flex flex-wrap items-center gap-1.5 pt-0.5">
      <button
        type="button"
        @click="emit('mark-meal')"
        class="inline-flex flex-1 min-h-[40px] items-center justify-center gap-1.5 rounded-full border border-wc-border bg-wc-bg-tertiary/40 px-4 py-2.5 font-display text-[11px] uppercase tracking-[0.1em] text-wc-text-secondary transition hover:border-emerald-400/40 hover:text-emerald-400 active:scale-[0.98]"
      >
        <Check :size="13" :stroke-width="2.5" />
        Marcar
      </button>
      <button
        type="button"
        @click="emit('open-swap')"
        class="inline-flex flex-1 min-h-[40px] items-center justify-center gap-1.5 rounded-full border border-wc-border bg-wc-bg-tertiary/40 px-4 py-2.5 font-display text-[11px] uppercase tracking-[0.1em] text-wc-text-secondary transition hover:border-wc-accent/40 hover:text-wc-accent active:scale-[0.98]"
      >
        <Replace :size="13" :stroke-width="2.5" />
        Cambiar
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Check, Replace } from 'lucide-vue-next';
import MealItem from './MealItem.vue';
import { useFoodIcon } from '@/composables/useFoodIcon';

const props = defineProps({
  meal: {
    type: Object,
    required: true,
  },
  swapped: {
    type: Boolean,
    default: false,
  },
  swappedRecipe: {
    type: Object,
    default: null,
  },
  activeOption: {
    type: String,
    default: 'a',
  },
});

const emit = defineEmits(['set-option', 'mark-meal', 'open-swap']);

const { foodIcon: getFoodIcon } = useFoodIcon();

const availableOptions = computed(() => {
  const opciones = props.meal.opciones;
  if (!opciones || typeof opciones !== 'object') return {};
  return Object.fromEntries(
    Object.entries(opciones).filter(([, v]) => Array.isArray(v) && v.length > 0)
  );
});

const hasMultiOption = computed(() => Object.keys(availableOptions.value).length > 0);

const standardFoods = computed(() => {
  const list = props.meal.alimentos || props.meal.foods || [];
  return Array.isArray(list) ? list : [];
});
</script>
