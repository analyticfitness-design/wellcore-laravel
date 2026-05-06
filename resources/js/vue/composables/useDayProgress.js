import { ref, computed, onScopeDispose, unref } from 'vue';

/**
 * useDayProgress — estado temporal del día nutricional del cliente.
 *
 * Recibe meals (ref/computed/array) con shape { hora?: 'HH:MM', time?: 'HH:MM', nombre?: string, name?: string }.
 * Asume timezone local del browser. Tuteo latino neutro.
 *
 * @param {import('vue').MaybeRefOrGetter<Array>} mealsInput
 */
export function useDayProgress(mealsInput) {
  const now = ref(new Date());

  const intervalId = setInterval(() => {
    now.value = new Date();
  }, 60000);

  onScopeDispose(() => clearInterval(intervalId));

  const ACTIVE_WINDOW_MS = 90 * 60 * 1000; // 90 min

  function resolveMeals() {
    const raw = typeof mealsInput === 'function' ? mealsInput() : unref(mealsInput);
    return Array.isArray(raw) ? raw : [];
  }

  function getHourString(meal) {
    if (!meal) return null;
    const h = meal.hora ?? meal.time ?? null;
    if (typeof h !== 'string') return null;
    const trimmed = h.trim();
    if (!trimmed) return null;
    return /^\d{1,2}:\d{2}$/.test(trimmed) ? trimmed : null;
  }

  function parseMealDate(meal, base) {
    const str = getHourString(meal);
    if (!str) return null;
    const [hh, mm] = str.split(':').map(Number);
    if (Number.isNaN(hh) || Number.isNaN(mm) || hh > 23 || mm > 59) return null;
    const d = new Date(base);
    d.setHours(hh, mm, 0, 0);
    return d;
  }

  function getMealName(meal) {
    return meal?.nombre ?? meal?.name ?? 'Comida';
  }

  const currentMealIndex = computed(() => {
    const meals = resolveMeals();
    if (!meals.length) return -1;
    const current = now.value;

    let lastPastIdx = -1;
    let nextPendingIdx = -1;

    for (let i = 0; i < meals.length; i++) {
      const t = parseMealDate(meals[i], current);
      if (!t) continue;
      if (t <= current) {
        lastPastIdx = i;
      } else if (nextPendingIdx === -1) {
        nextPendingIdx = i;
      }
    }

    if (lastPastIdx !== -1) {
      const lastTime = parseMealDate(meals[lastPastIdx], current);
      if (current - lastTime <= ACTIVE_WINDOW_MS) return lastPastIdx;
    }
    if (nextPendingIdx !== -1) return nextPendingIdx;
    return -1;
  });

  const nextMealLabel = computed(() => {
    const meals = resolveMeals();
    const idx = currentMealIndex.value;
    if (idx < 0 || idx >= meals.length - 1) return null;
    const next = meals[idx + 1];
    const nextTime = parseMealDate(next, now.value);
    if (!nextTime) return null;
    const diffMs = nextTime - now.value;
    if (diffMs <= 0) return null;
    const diffMin = Math.round(diffMs / 60000);
    const name = getMealName(next);
    if (diffMin < 60) return `${name} en ${diffMin}min`;
    const hours = Math.round(diffMin / 60);
    return `${name} en ${hours}h`;
  });

  function isMealActive(idx) {
    return idx === currentMealIndex.value;
  }

  function isMealDone(idx, mealsList) {
    const meals = mealsList ?? resolveMeals();
    const meal = meals?.[idx];
    if (!meal) return false;
    const t = parseMealDate(meal, now.value);
    if (!t) return false;
    return now.value > t && !isMealActive(idx);
  }

  function mealState(idx, mealsList) {
    if (isMealActive(idx)) return 'current';
    if (isMealDone(idx, mealsList)) return 'done';
    return 'pending';
  }

  return {
    now,
    currentMealIndex,
    nextMealLabel,
    isMealActive,
    isMealDone,
    mealState,
  };
}
