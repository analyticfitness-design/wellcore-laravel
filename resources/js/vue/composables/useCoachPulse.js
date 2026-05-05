import { ref, computed } from 'vue';
import { useApi } from './useApi';

/**
 * useCoachPulse — composable para "Latido del Equipo" del coach.
 *
 * Replica gold standard useGroupPulse: singleton module-scope + TTL 25s + Promise dedup.
 * El backend cache es 60s, frontend 25s para evitar staleness prolongado.
 */

const summaryCache = ref(null);
const summaryLoadedAt = ref(0);
const SUMMARY_TTL_MS = 25_000;
let summaryPromise = null;

export function useCoachPulse() {
    const api = useApi();
    const loading = ref(false);
    const error = ref(null);

    const isFresh = computed(() =>
        summaryCache.value !== null
        && Date.now() - summaryLoadedAt.value < SUMMARY_TTL_MS
    );

    async function fetchSummary({ force = false } = {}) {
        if (!force && isFresh.value) return summaryCache.value;
        if (summaryPromise) return summaryPromise;

        loading.value = true;
        error.value = null;

        summaryPromise = (async () => {
            try {
                const res = await api.get('/api/v/coach/community/pulse');
                summaryCache.value = res.data;
                summaryLoadedAt.value = Date.now();
                return res.data;
            } catch (err) {
                error.value = err.response?.data?.message || 'No se pudo cargar el latido del equipo.';
                if (err.response?.status >= 500 || !err.response) {
                    // eslint-disable-next-line no-console
                    console.error('[useCoachPulse] fetchSummary failed', err);
                }
                return null;
            } finally {
                loading.value = false;
                summaryPromise = null;
            }
        })();

        return summaryPromise;
    }

    return { summary: summaryCache, loading, error, isFresh, fetchSummary };
}

export function resetCoachPulse() {
    summaryCache.value = null;
    summaryLoadedAt.value = 0;
    summaryPromise = null;
}
