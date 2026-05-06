import { ref, computed, watch } from 'vue';

/**
 * useMealCheckins — tracking client-side de comidas "marcadas" hechas.
 *
 * Persiste en localStorage bajo key `wc_meals_checked_v1_YYYY-MM-DD`.
 * El estado se resetea automaticamente cada dia (key incluye fecha).
 *
 * Por que client-side: no hay endpoint backend
 * `POST /api/v/client/nutrition/meals/:idx/check` todavia. Cuando LA-02
 * lo cree, este composable se migra a cache backend (manteniendo la API).
 *
 * Uso:
 *   const { checkedSet, checkedCount, isMealChecked, toggleMeal, markMeal,
 *           unmarkMeal, resetToday } = useMealCheckins();
 *
 *   isMealChecked(2)        // false
 *   toggleMeal(2)            // marca idx 2
 *   isMealChecked(2)        // true
 *   checkedCount.value      // 1
 */

const STORAGE_PREFIX = 'wc_meals_checked_v1_';

function todayKey() {
    const d = new Date();
    const yyyy = d.getFullYear();
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    const dd = String(d.getDate()).padStart(2, '0');
    return `${STORAGE_PREFIX}${yyyy}-${mm}-${dd}`;
}

function loadFromStorage() {
    try {
        const raw = localStorage.getItem(todayKey());
        if (!raw) return new Set();
        const arr = JSON.parse(raw);
        return Array.isArray(arr) ? new Set(arr.filter(Number.isInteger)) : new Set();
    } catch {
        return new Set();
    }
}

function saveToStorage(set) {
    try {
        localStorage.setItem(todayKey(), JSON.stringify([...set]));
    } catch {
        // Quota / private mode — silently ignore
    }
}

export function useMealCheckins() {
    const checkedSet = ref(loadFromStorage());

    const checkedCount = computed(() => checkedSet.value.size);

    function isMealChecked(mealIdx) {
        return checkedSet.value.has(mealIdx);
    }

    function markMeal(mealIdx) {
        if (!Number.isInteger(mealIdx)) return;
        const next = new Set(checkedSet.value);
        next.add(mealIdx);
        checkedSet.value = next;
    }

    function unmarkMeal(mealIdx) {
        if (!Number.isInteger(mealIdx)) return;
        const next = new Set(checkedSet.value);
        next.delete(mealIdx);
        checkedSet.value = next;
    }

    function toggleMeal(mealIdx) {
        if (isMealChecked(mealIdx)) unmarkMeal(mealIdx);
        else markMeal(mealIdx);
    }

    function resetToday() {
        checkedSet.value = new Set();
    }

    // Persist en cada cambio (deep watch innecesario — siempre re-asignamos)
    watch(checkedSet, (s) => saveToStorage(s));

    return {
        checkedSet,
        checkedCount,
        isMealChecked,
        markMeal,
        unmarkMeal,
        toggleMeal,
        resetToday,
    };
}
