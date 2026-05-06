<template>
  <div class="space-y-3 p-4">

    <!-- Mobile macro chips (hidden on sm+) -->
    <div v-if="proteinG > 0 || carbsG > 0 || fatG > 0" class="flex flex-wrap gap-1.5 sm:hidden">
      <span
        v-if="proteinG > 0"
        class="rounded-full px-2.5 py-1 text-xs font-semibold"
        style="background:rgba(220,38,38,0.12); color:#F87171;"
      >P {{ proteinG }}g</span>
      <span
        v-if="carbsG > 0"
        class="rounded-full px-2.5 py-1 text-xs font-semibold"
        style="background:rgba(59,130,246,0.12); color:#60A5FA;"
      >C {{ carbsG }}g</span>
      <span
        v-if="fatG > 0"
        class="rounded-full px-2.5 py-1 text-xs font-semibold"
        style="background:rgba(245,158,11,0.12); color:#FBBF24;"
      >G {{ fatG }}g</span>
    </div>

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
        <ul class="space-y-1.5">
          <MealItem
            v-for="(ing, ii) in swappedRecipe.ingredients || []"
            :key="ii"
            :food="ing"
            :icon="getFoodIcon(ing)"
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
          <p class="text-xs italic leading-relaxed text-wc-text-tertiary">💡 {{ swappedRecipe.coachTip }}</p>
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
      <ul class="space-y-1.5">
        <MealItem
          v-for="(alimento, ai) in (availableOptions[activeOption || 'a'] || [])"
          :key="ai"
          :food="alimento"
          :icon="getFoodIcon(alimento)"
        />
      </ul>
    </template>

    <!-- 3. Standard alimentos list -->
    <ul v-else-if="standardFoods.length > 0" class="space-y-1.5">
      <MealItem
        v-for="(alimento, ai) in standardFoods"
        :key="ai"
        :food="alimento"
        :icon="getFoodIcon(alimento)"
      />
    </ul>

    <!-- Notas del coach -->
    <MealNote v-if="meal.notas" :note="meal.notas" tone="tip" />
  </div>
</template>

<script setup>
import { computed } from 'vue';
import MealItem from './MealItem.vue';
import MealNote from './MealNote.vue';
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

const emit = defineEmits(['set-option']);

const { foodIcon: getFoodIcon } = useFoodIcon();

const proteinG = computed(() => props.meal.macros?.proteina ?? props.meal.macros?.proteina_g ?? 0);
const carbsG = computed(() => props.meal.macros?.carbohidratos ?? props.meal.macros?.carbohidratos_g ?? 0);
const fatG = computed(() => props.meal.macros?.grasas ?? props.meal.macros?.grasas_g ?? 0);

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
