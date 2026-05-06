<template>
  <div class="flex flex-col gap-3 px-3 py-3.5 sm:gap-3.5 sm:px-4 sm:py-4">

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
          <p class="mt-3.5 mb-1.5 font-display text-[10px] tracking-[0.18em] text-wc-text-secondary">PREPARACIÓN</p>
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
          class="rounded-full px-4 py-2 text-xs font-semibold transition min-h-[44px] min-w-[44px]"
          :class="(activeOption || 'a') === optKey
            ? 'bg-wc-accent text-white'
            : 'bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-text border border-wc-border'"
        >Opción {{ optKey.toUpperCase() }}</button>
      </div>
      <ul>
        <MealItem
          v-for="(alimento, ai) in (availableOptions[activeOption || 'a'] || [])"
          :key="ai"
          :food="alimento"
          :icon="getFoodIcon(alimento)"
        />
      </ul>
    </template>

    <!-- 3. Standard alimentos list -->
    <ul v-else-if="standardFoods.length > 0">
      <MealItem
        v-for="(alimento, ai) in standardFoods"
        :key="ai"
        :food="alimento"
        :icon="getFoodIcon(alimento)"
      />
    </ul>

    <!-- "POR QUÉ" inline con accent border-left wc-accent -->
    <div
      v-if="meal.notas"
      class="relative rounded-lg border border-wc-border bg-wc-bg-tertiary px-3.5 py-3"
    >
      <span aria-hidden="true" class="absolute left-0 top-3 bottom-3 w-[2px] rounded-full bg-wc-accent/60"></span>
      <p class="mb-1.5 flex items-center gap-1.5 font-data text-[9px] uppercase tracking-[0.14em] text-wc-accent">
        <span class="h-1 w-1 rounded-full bg-wc-accent"></span>
        Por qué
      </p>
      <p class="text-xs leading-relaxed text-wc-text-secondary">{{ meal.notas }}</p>
    </div>

    <!-- Actions row: Marcar (toggle persistido localStorage) + Cambiar (swap).
         Mobile: flex-1 (cada uno ocupa mitad ancho). Desktop sm+: flex-initial
         con min-w-[160px] max-w-[220px] (tamano natural, no estira a 600px+). -->
    <div class="flex flex-wrap items-center gap-2 pt-0.5 sm:gap-3">
      <button
        type="button"
        @click="emit('toggle-mark')"
        :aria-pressed="checked"
        :class="[
          'inline-flex flex-1 min-h-[44px] items-center justify-center gap-1.5 rounded-full border px-4 py-2.5 font-display text-[11px] uppercase tracking-[0.1em] transition active:scale-[0.98] sm:flex-initial sm:min-w-[160px] sm:max-w-[220px] sm:px-6',
          checked
            ? 'border-emerald-500/50 bg-emerald-500/[0.14] text-emerald-400 hover:bg-emerald-500/[0.20]'
            : 'border-emerald-500/30 bg-emerald-500/[0.06] text-emerald-400 hover:border-emerald-500/50 hover:bg-emerald-500/[0.12]'
        ]"
      >
        <Check :size="14" :stroke-width="2.5" />
        {{ checked ? 'Marcada' : 'Marcar' }}
      </button>
      <button
        type="button"
        @click="emit('open-swap')"
        class="inline-flex flex-1 min-h-[44px] items-center justify-center gap-1.5 rounded-full border border-wc-accent/30 bg-wc-accent/[0.06] px-4 py-2.5 font-display text-[11px] uppercase tracking-[0.1em] text-wc-accent transition hover:border-wc-accent/50 hover:bg-wc-accent/[0.12] active:scale-[0.98] sm:flex-initial sm:min-w-[160px] sm:max-w-[220px] sm:px-6"
      >
        <Replace :size="14" :stroke-width="2.5" />
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
  checked: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['set-option', 'open-swap', 'toggle-mark']);

const { foodIcon: getFoodIcon } = useFoodIcon();

// Backend devuelve OPCIONES de DOS shapes posibles (replica getNutrMealOpciones legacy):
//   1. meal.opcion_a / meal.opcion_b / meal.opcion_c (top-level — caso REAL del backend prod)
//   2. meal.opciones.opcion_a / etc (anidado — caso legacy alternativo)
// Detectamos ambos. El backend real (verificado con cliente id=93) usa shape #1.
const availableOptions = computed(() => {
  const meal = props.meal;
  if (!meal) return {};
  const src = (meal.opciones && typeof meal.opciones === 'object') ? meal.opciones : meal;
  const a = src.opcion_a || src.option_a;
  const b = src.opcion_b || src.option_b;
  const c = src.opcion_c || src.option_c;
  const result = {};
  if (Array.isArray(a) && a.length > 0) result.a = a;
  if (Array.isArray(b) && b.length > 0) result.b = b;
  if (Array.isArray(c) && c.length > 0) result.c = c;
  return result;
});

const hasMultiOption = computed(() => Object.keys(availableOptions.value).length > 0);

const standardFoods = computed(() => {
  const list = props.meal.alimentos || props.meal.foods || [];
  return Array.isArray(list) ? list : [];
});
</script>
