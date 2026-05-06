// useMealSwap — composable para reemplazo de comidas (Nutrition Tab v2 · Fase 1 Batch 3).
// Extraído de PlanViewer.vue (líneas 141-274). Preserva scoring/endpoints/voz LATAM neutra.
//
// API:
//   const { swapping, swappedRecipe, swapHistory, searchQuery, searchCandidates,
//           toast, openPanel, closePanel, search, applySwap, undoSwap,
//           isMealSwapped, getSwappedRecipe, scoreCompatibility }
//     = useMealSwap({ findTodayMeal, onSwapApplied, onSwapUndone });
//
//   findTodayMeal(meal) -> { name, calories, protein, carbs, fat, swapped, swap_id, recipe_id, recipe_name } | null
//   onSwapApplied / onSwapUndone -> async hooks (recargar macros-today del padre)

import { ref, computed, onBeforeUnmount } from 'vue';
import { useApi } from './useApi';
import { RECIPES } from '../data/recipes';

const COMPAT_ORDER = { ideal: 0, aceptable: 1, fuera: 2 };

export function useMealSwap(options = {}) {
  const {
    findTodayMeal = () => null,
    onSwapApplied = async () => {},
    onSwapUndone = async () => {},
  } = options;

  const api = useApi();

  // ─── State ────────────────────────────────────────────────────────────
  const swapping = ref(false);              // request en vuelo
  const swapIndex = ref(null);              // idx de meal-card con panel abierto
  const swapContext = ref(null);            // meal en proceso de swap (today shape)
  const searchQuery = ref('');
  const swapHistory = ref([]);              // [{ mealIdx, recipeId }]
  const toast = ref(null);                  // { type, msg } | null

  let toastTimer = null;
  let abortCtrl = null;

  // ─── Score (relativo, matching nutrSwapCompatibility original) ────────
  function scoreCompatibility(recipe, meal) {
    if (!meal || !meal.calories) return 'fuera';
    const diff = Math.abs(recipe.macros.cal - meal.calories) / meal.calories;
    if (diff <= 0.15) return 'ideal';
    if (diff <= 0.30) return 'aceptable';
    return 'fuera';
  }

  // ─── Candidates (filtrados + scored + ordenados) ──────────────────────
  const searchCandidates = computed(() => {
    if (!swapContext.value) return [];
    const q = searchQuery.value.trim().toLowerCase();
    return RECIPES
      .filter(r => !q || r.name.toLowerCase().includes(q) || r.description.toLowerCase().includes(q))
      .map(r => ({ recipe: r, score: scoreCompatibility(r, swapContext.value) }))
      .sort((a, b) => COMPAT_ORDER[a.score] - COMPAT_ORDER[b.score]);
  });

  const swappedRecipe = computed(() => {
    if (!swapContext.value || !swapContext.value.swapped) return null;
    return resolveRecipe(swapContext.value);
  });

  // ─── Toast helper (auto-dismiss 3s, latino neutro) ────────────────────
  function showToast(type, msg) {
    if (toastTimer) clearTimeout(toastTimer);
    toast.value = { type, msg };
    toastTimer = setTimeout(() => { toast.value = null; }, 3000);
  }

  // ─── Panel control ────────────────────────────────────────────────────
  function openPanel(mealIdx, meal) {
    if (swapIndex.value === mealIdx) { closePanel(); return; }
    const todayMeal = findTodayMeal(meal);
    swapContext.value = todayMeal || {
      name: (meal.nombre || meal.name || '').toUpperCase(),
      calories: meal.kcal || meal.calorias || meal.calories || 0,
      protein: meal.macros?.proteina || meal.macros?.proteina_g || 0,
      carbs: meal.macros?.carbohidratos || meal.macros?.carbohidratos_g || 0,
      fat: meal.macros?.grasas || meal.macros?.grasas_g || 0,
      swapped: false,
      swap_id: null,
    };
    searchQuery.value = '';
    swapIndex.value = mealIdx;
  }

  function closePanel() {
    if (abortCtrl) { abortCtrl.abort(); abortCtrl = null; }
    swapIndex.value = null;
    swapContext.value = null;
    searchQuery.value = '';
  }

  function search(q) {
    searchQuery.value = q || '';
  }

  // ─── Apply swap (POST) ────────────────────────────────────────────────
  async function applySwap(recipe, meal) {
    if (!swapContext.value || swapping.value) return false;
    swapping.value = true;
    abortCtrl = new AbortController();
    const m = swapContext.value;
    try {
      await api.post('/api/v/client/nutrition/swap', {
        recipe_id: recipe.id,
        recipe_name: recipe.name,
        original_meal_name: m.name,
        recipe_macros: { calories: recipe.macros.cal, protein: recipe.macros.protein, carbs: recipe.macros.carbs, fat: recipe.macros.fat },
        original_macros: { calories: m.calories, protein: m.protein, carbs: m.carbs, fat: m.fat },
      }, { signal: abortCtrl.signal });
      swapHistory.value.push({ mealIdx: swapIndex.value, recipeId: recipe.id });
      await onSwapApplied(recipe, meal);
      closePanel();
      showToast('success', `Comida reemplazada por ${recipe.name}`);
      return true;
    } catch (e) {
      if (e?.name !== 'CanceledError' && e?.code !== 'ERR_CANCELED') {
        showToast('error', 'No pudimos aplicar el cambio');
      }
      return false;
    } finally {
      swapping.value = false;
      abortCtrl = null;
    }
  }

  // ─── Undo swap (DELETE) ───────────────────────────────────────────────
  async function undoSwap(meal) {
    const todayMeal = findTodayMeal(meal);
    if (!todayMeal?.swap_id) return false;
    try {
      await api.delete(`/api/v/client/nutrition/swap/${todayMeal.swap_id}`);
      await onSwapUndone(meal);
      showToast('success', 'Reemplazo deshecho');
      return true;
    } catch {
      showToast('error', 'No pudimos deshacer el cambio');
      return false;
    }
  }

  // ─── Read helpers ─────────────────────────────────────────────────────
  function isMealSwapped(meal) {
    return findTodayMeal(meal)?.swapped === true;
  }

  function getSwappedRecipe(meal) {
    const t = findTodayMeal(meal);
    if (!t || !t.swapped) return null;
    return resolveRecipe(t);
  }

  function resolveRecipe(t) {
    if (t.recipe_id) {
      const byId = RECIPES.find(r => r.id === t.recipe_id);
      if (byId) return byId;
    }
    if (t.recipe_name) {
      const target = String(t.recipe_name).toLowerCase().trim();
      const byName = RECIPES.find(r => r.name.toLowerCase().trim() === target);
      if (byName) return byName;
    }
    return null;
  }

  // ─── Cleanup ──────────────────────────────────────────────────────────
  onBeforeUnmount(() => {
    if (toastTimer) clearTimeout(toastTimer);
    if (abortCtrl) abortCtrl.abort();
  });

  return {
    swapping, swappedRecipe, swapHistory, searchQuery, searchCandidates,
    swapIndex, swapContext, toast,
    openPanel, closePanel, search, applySwap, undoSwap,
    isMealSwapped, getSwappedRecipe, scoreCompatibility,
  };
}
