import { ref, computed } from 'vue';
import { useApi } from './useApi';

export function useFoodTracking() {
    const api = useApi();

    const loading = ref(true);
    const uploadingIndex = ref(null);
    const error = ref(null);

    const hasNutritionPlan = ref(false);
    const meals = ref([]);
    const xpToday = ref(0);
    const bonusEarnedToday = ref(false);
    const streakDays = ref(0);
    const weekHistory = ref([]);

    async function fetchToday() {
        loading.value = true;
        error.value = null;
        try {
            const { data } = await api.get('/api/v/client/food-photos');
            hasNutritionPlan.value = data.has_nutrition_plan;
            meals.value = data.meals || [];
            xpToday.value = data.xp_today ?? 0;
            bonusEarnedToday.value = data.bonus_earned_today ?? false;
            streakDays.value = data.streak_days ?? 0;
        } catch (err) {
            error.value = err.response?.data?.message || 'Error al cargar tu alimentación';
        } finally {
            loading.value = false;
        }
    }

    async function fetchHistory() {
        try {
            const { data } = await api.get('/api/v/client/food-photos/history');
            weekHistory.value = data.week_history || [];
        } catch {
            // non-critical
        }
    }

    async function uploadPhoto(file, mealName, mealIndex) {
        uploadingIndex.value = mealIndex;
        const fd = new FormData();
        fd.append('photo', file);
        fd.append('meal_name', mealName);
        fd.append('meal_index', String(mealIndex));
        try {
            await api.post('/api/v/client/food-photos', fd, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
            await fetchToday();
        } finally {
            uploadingIndex.value = null;
        }
    }

    async function deletePhoto(photoId) {
        await api.delete(`/api/v/client/food-photos/${photoId}`);
        await fetchToday();
    }

    const completedToday = computed(() =>
        meals.value.filter((m) => m.photo).length
    );
    const totalToday = computed(() => meals.value.length);
    const completionPct = computed(() => {
        if (totalToday.value === 0) return 0;
        return Math.round((completedToday.value / totalToday.value) * 100);
    });

    return {
        loading, uploadingIndex, error,
        hasNutritionPlan, meals, xpToday, bonusEarnedToday, streakDays, weekHistory,
        completedToday, totalToday, completionPct,
        fetchToday, fetchHistory, uploadPhoto, deletePhoto,
    };
}
