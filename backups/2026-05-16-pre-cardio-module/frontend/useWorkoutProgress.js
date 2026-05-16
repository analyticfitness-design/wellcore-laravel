import { computed } from 'vue';

/**
 * useWorkoutProgress(exercises, setData)
 *
 * Computed agregados de progreso de la sesión actual.
 * `exercises` y `setData` deben ser refs/computed reactivos.
 *
 * Returns: progressPct, completedSetsCount, totalSetsCount,
 *          completedExercisesCount, totalRepsAll, maxWeightAll,
 *          totalVolumeKg, currentExerciseIndex, exerciseStates
 */
export function useWorkoutProgress(exercises, setData) {
    function getSets(idx) {
        const raw = setData.value?.[idx];
        if (!raw) return [];
        return Array.isArray(raw) ? raw : Object.values(raw);
    }

    function exSeries(ex) {
        return parseInt(ex?.series || ex?.sets || 3) || 3;
    }

    const completedSetsCount = computed(() => {
        let count = 0;
        const data = setData.value || {};
        for (const key in data) {
            const sets = data[key];
            const arr = Array.isArray(sets) ? sets : Object.values(sets);
            arr.forEach((s) => {
                if (s.completed) count++;
            });
        }
        return count;
    });

    const totalSetsCount = computed(() => {
        return (exercises.value || []).reduce((sum, ex) => sum + exSeries(ex), 0);
    });

    const progressPct = computed(() => {
        if (totalSetsCount.value === 0) return 0;
        return Math.round((completedSetsCount.value / totalSetsCount.value) * 100);
    });

    const completedExercisesCount = computed(() => {
        let count = 0;
        (exercises.value || []).forEach((ex, idx) => {
            const total = exSeries(ex);
            const done = getSets(idx).filter((s) => s.completed).length;
            if (done >= total) count++;
        });
        return count;
    });

    const totalRepsAll = computed(() => {
        let total = 0;
        const data = setData.value || {};
        for (const key in data) {
            const sets = data[key];
            const arr = Array.isArray(sets) ? sets : Object.values(sets);
            arr.forEach((s) => {
                if (s.completed) total += parseInt(s.reps) || 0;
            });
        }
        return total;
    });

    const maxWeightAll = computed(() => {
        let max = 0;
        const data = setData.value || {};
        for (const key in data) {
            const sets = data[key];
            const arr = Array.isArray(sets) ? sets : Object.values(sets);
            arr.forEach((s) => {
                if (s.completed) {
                    const w = parseFloat(s.weight) || 0;
                    if (w > max) max = w;
                }
            });
        }
        return max;
    });

    const totalVolumeKg = computed(() => {
        let vol = 0;
        const data = setData.value || {};
        for (const key in data) {
            const sets = data[key];
            const arr = Array.isArray(sets) ? sets : Object.values(sets);
            arr.forEach((s) => {
                if (s.completed) {
                    const w = parseFloat(s.weight) || 0;
                    const r = parseInt(s.reps) || 0;
                    vol += w * r;
                }
            });
        }
        return Math.round(vol);
    });

    /**
     * El "current exercise" es el primer ejercicio NO completado.
     * Si todos están completados, devuelve el último.
     */
    const currentExerciseIndex = computed(() => {
        const list = exercises.value || [];
        for (let i = 0; i < list.length; i++) {
            const total = exSeries(list[i]);
            const done = getSets(i).filter((s) => s.completed).length;
            if (done < total) return i;
        }
        return Math.max(0, list.length - 1);
    });

    /**
     * Array paralelo a `exercises` con el estado visual de cada uno:
     * 'done' | 'active' | 'pending'
     */
    const exerciseStates = computed(() => {
        const list = exercises.value || [];
        const cur = currentExerciseIndex.value;
        return list.map((ex, idx) => {
            const total = exSeries(ex);
            const done = getSets(idx).filter((s) => s.completed).length;
            if (done >= total) return 'done';
            if (idx === cur) return 'active';
            return 'pending';
        });
    });

    return {
        progressPct,
        completedSetsCount,
        totalSetsCount,
        completedExercisesCount,
        totalRepsAll,
        maxWeightAll,
        totalVolumeKg,
        currentExerciseIndex,
        exerciseStates,
    };
}
