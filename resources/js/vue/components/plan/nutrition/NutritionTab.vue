<template>
  <div class="space-y-5">
    <!-- 1. PlanStrip — header compacto plan + semana + dia -->
    <PlanStrip
      :plan-name="planLabel"
      :current-week="currentWeek"
      :total-weeks="totalWeeks"
      :day-label="dayLabel"
    />

    <!-- 2. NutritionDayHero — kcal grande + objetivo + macros -->
    <NutritionDayHero
      v-if="hasMacros"
      :nutrition-plan="nutritionPlan"
    />

    <!-- 3. AICameraCTA — analizar comida con foto -->
    <AICameraCTA @open="$emit('open-ai-estimator')" />

    <!-- 4. CoachNote — notas del coach -->
    <CoachNote
      v-if="nutritionPlan.notas_coach"
      :note="nutritionPlan.notas_coach"
      :coach-name="coachInfo?.name || 'Tu coach'"
      :coach-role="coachInfo?.role || 'Coach de nutricion'"
      :coach-avatar="coachInfo?.avatar"
      :timestamp="coachInfo?.lastNoteAt"
      @acknowledge="$emit('note-acknowledged')"
    />

    <!-- 5. Tips del coach -->
    <div
      v-if="tipsList.length > 0"
      class="rounded-xl border border-emerald-500/20 bg-emerald-500/[0.04] p-5"
    >
      <p class="text-xs font-semibold tracking-widest uppercase text-emerald-400 mb-3">
        Consejos de tu coach
      </p>
      <ul class="space-y-2.5">
        <li
          v-for="(tip, tIdx) in tipsList"
          :key="tIdx"
          class="flex items-start gap-2.5"
        >
          <Check :size="16" :stroke-width="2.5" class="mt-0.5 shrink-0 text-emerald-400" />
          <span class="text-sm leading-relaxed text-wc-text-secondary">{{ tip }}</span>
        </li>
      </ul>
    </div>

    <!-- 6. DayTimeline — cronograma con nodos por comida -->
    <DayTimeline
      v-if="meals.length > 0"
      :meals="meals"
      :current-meal-index="dayProgress.currentMealIndex.value"
      :swapped-meal-indices="swappedIndices"
    />

    <!-- 7. Plan de comidas — formato completo -->
    <div v-if="hasMeals" class="space-y-3">
      <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1 px-0.5 pb-2 border-b border-wc-border">
        <h3 class="font-display text-sm font-medium uppercase tracking-wider text-wc-text sm:text-base">
          Plan del día
        </h3>
        <p class="font-data text-[10px] text-wc-text-tertiary tabular-nums sm:text-[11px]">
          <strong class="text-wc-text">{{ doneCount }}</strong>
          de
          <strong class="text-wc-text">{{ meals.length }}</strong>
          <template v-if="dayProgress.nextMealLabel.value">
            <span class="mx-1 text-wc-text-tertiary/60">·</span>
            próxima {{ dayProgress.nextMealLabel.value }}
          </template>
        </p>
      </div>

      <!-- Toast notification swap (global fixed) -->
      <Transition name="fade">
        <div
          v-if="swap.toast.value"
          class="fixed bottom-24 left-1/2 z-50 max-w-[calc(100vw-2rem)] -translate-x-1/2 rounded-xl border px-5 py-3 text-center text-sm font-semibold shadow-lg backdrop-blur-sm"
          :class="swap.toast.value.type === 'success'
            ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-400'
            : 'border-red-500/30 bg-red-500/10 text-red-400'"
        >
          {{ swap.toast.value.msg }}
        </div>
      </Transition>

      <MealCard
        v-for="(meal, mIdx) in meals"
        :key="mIdx"
        :meal="meal"
        :meal-idx="mIdx"
        :is-current="dayProgress.currentMealIndex.value === mIdx"
        :expanded="!!openMeals[mIdx]"
        :swap-panel-open="swap.swapIndex.value === mIdx"
        :swapped="swap.isMealSwapped(meal)"
        :swapped-recipe="swap.getSwappedRecipe(meal)"
        :original-name="meal.nombre || meal.name || ''"
        :swap-context="swap.swapIndex.value === mIdx ? swap.swapContext.value : null"
        :swap-search-query="swap.searchQuery.value"
        :swap-candidates="swap.swapIndex.value === mIdx ? swap.searchCandidates.value : []"
        :applying="swap.swapping.value"
        :restoring="swap.undoing.value"
        :active-option="activeOptions[mIdx] || 'a'"
        :checked="checkins.isMealChecked(mIdx)"
        @toggle="toggleMeal(mIdx)"
        @open-swap="swap.openPanel(mIdx, meal)"
        @close-swap="swap.closePanel()"
        @apply-swap="(r) => onApplySwap(r, meal, mIdx)"
        @undo-swap="onUndoSwap(meal, mIdx)"
        @toggle-mark="checkins.toggleMeal(mIdx)"
        @update:swap-search-query="(q) => swap.search(q)"
        @update:active-option="(k) => activeOptions[mIdx] = k"
      />
    </div>

    <!-- Empty state -->
    <div
      v-else
      class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-6 text-center"
    >
      <p class="text-sm text-wc-text-secondary">
        Tu coach esta preparando tu plan de nutricion.
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, reactive } from 'vue';
import { Check } from 'lucide-vue-next';
import PlanStrip from './PlanStrip.vue';
import NutritionDayHero from './NutritionDayHero.vue';
import AICameraCTA from './AICameraCTA.vue';
import CoachNote from './CoachNote.vue';
import DayTimeline from './DayTimeline.vue';
import MealCard from './MealCard.vue';
import { useMealSwap } from '@/composables/useMealSwap';
import { useDayProgress } from '@/composables/useDayProgress';
import { useMealCheckins } from '@/composables/useMealCheckins';

const props = defineProps({
  nutritionPlan: { type: Object, required: true },
  clientPlanType: { type: String, default: 'esencial' },
  coachInfo: { type: Object, default: null },
  currentWeek: { type: Number, default: 1 },
  totalWeeks: { type: Number, default: 12 },
  // macrosToday: estado reactivo de /api/v/client/nutrition/macros-today que vive
  // en el padre PlanViewer. Sin esto, swap.isMealSwapped/getSwappedRecipe siempre
  // retornan false y el banner REEMPLAZADO POR no se ve hasta refresh manual.
  macrosToday: { type: Object, default: null },
});

const emit = defineEmits(['swap-applied', 'open-ai-estimator', 'note-acknowledged']);

// ─── Derived data ──────────────────────────────────────────────────────
const meals = computed(() =>
  props.nutritionPlan?.comidas || props.nutritionPlan?.comidas_sugeridas || []
);
const hasMeals = computed(() => meals.value.length > 0);
const tipsList = computed(() => props.nutritionPlan?.tips || []);

const totalKcal = computed(() =>
  props.nutritionPlan?.objetivo_cal
    || props.nutritionPlan?.objetivo_calorico
    || props.nutritionPlan?.calorias_diarias
    || props.nutritionPlan?.calorias
    || 0
);

const macroP = computed(() =>
  props.nutritionPlan?.macros?.proteina
    || props.nutritionPlan?.macros?.proteina_g
    || props.nutritionPlan?.proteina
    || 0
);
const macroC = computed(() =>
  props.nutritionPlan?.macros?.carbohidratos
    || props.nutritionPlan?.macros?.carbohidratos_g
    || props.nutritionPlan?.carbohidratos
    || 0
);
const macroF = computed(() =>
  props.nutritionPlan?.macros?.grasas
    || props.nutritionPlan?.macros?.grasas_g
    || props.nutritionPlan?.grasas
    || 0
);

const hasMacros = computed(() =>
  totalKcal.value > 0 && (macroP.value > 0 || macroC.value > 0 || macroF.value > 0)
);

// ─── PlanStrip labels ──────────────────────────────────────────────────
const planLabel = computed(() => `PLAN ${(props.clientPlanType || 'esencial').toUpperCase()}`);
const currentWeek = computed(() => props.currentWeek);
const totalWeeks = computed(() => props.totalWeeks);

const dayLabel = computed(() => {
  try {
    const fmt = new Intl.DateTimeFormat('es-CO', {
      weekday: 'short', day: '2-digit', month: 'short',
    });
    return fmt.format(new Date());
  } catch {
    return '';
  }
});

// ─── Local meal UI state ───────────────────────────────────────────────
const openMeals = reactive({});
const activeOptions = reactive({});

function toggleMeal(idx) {
  openMeals[idx] = !openMeals[idx];
}

// ─── Swap composable ───────────────────────────────────────────────────
function cleanName(name) {
  return (name || '').toLowerCase().replace(/[^a-z0-9]+/g, ' ').trim();
}

// findTodayMeal replica findNutrTodayMeal del PlanViewer legacy (linea 182).
// Lee props.macrosToday reactivamente: cuando el padre recarga macros-today
// tras un swap, este componente re-renderea y la fn se re-llama con el
// nuevo state.
function findTodayMeal(meal) {
  const today = props.macrosToday;
  if (!today?.meals) return null;
  const target = cleanName((meal?.nombre || meal?.name || '').toUpperCase());
  if (!target) return null;
  return today.meals.find((m) => {
    const mn = cleanName(m.name);
    return mn === target || mn.includes(target) || target.includes(mn);
  }) || null;
}

const swap = useMealSwap({
  findTodayMeal,
  // El padre escucha swap-applied y llama loadNutrMacrosToday(). Cuando
  // macrosToday cambia (prop reactiva), findTodayMeal devuelve el nuevo
  // estado y el banner SWAPPED aparece automaticamente.
  onSwapApplied: async () => { emit('swap-applied', { phase: 'apply' }); },
  onSwapUndone: async () => { emit('swap-applied', { phase: 'undo' }); },
});

const swappedIndices = computed(() =>
  meals.value
    .map((m, idx) => (swap.isMealSwapped(m) ? idx : null))
    .filter((i) => i !== null)
);

async function onApplySwap(recipe, meal, mIdx) {
  const ok = await swap.applySwap(recipe, meal);
  if (ok) emit('swap-applied', { mealIdx: mIdx, recipe });
}

async function onUndoSwap(meal, mIdx) {
  const ok = await swap.undoSwap(meal);
  if (ok) emit('swap-applied', { mealIdx: mIdx, recipe: null });
}

// ─── Day progress ──────────────────────────────────────────────────────
const dayProgress = useDayProgress(meals);

// ─── Marcar comidas (localStorage por dia) ─────────────────────────────
// Cuando LA-02 cree POST /api/v/client/nutrition/meals/:idx/check, se
// migra a backend manteniendo la API del composable.
const checkins = useMealCheckins();

// Counter "X de Y" basado en comidas REALMENTE marcadas por el cliente.
const doneCount = computed(() => checkins.checkedCount.value);

</script>
