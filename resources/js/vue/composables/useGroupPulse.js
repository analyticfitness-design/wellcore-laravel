import { ref, computed } from 'vue';
import { useApi } from './useApi';

/**
 * useGroupPulse — composable para el feature "Latido del Grupo".
 *
 * Comparte cache in-memory del summary (TTL 25s, ligeramente menor que el
 * cache backend de 30s para evitar stale prolongado) entre todos los
 * componentes que invocan useGroupPulse(): así DashboardGroupPulse y
 * cualquier otro consumer no duplican el request.
 *
 * El feed NO se cachea aquí — la paginación vive en el consumidor
 * (GroupPulseFeed.vue) y un TTL global generaría UX confusa al cambiar
 * de filtro/página.
 *
 * Patrón: singleton module-scope para state compartido (igual que
 * useContractGate), loading/error per-instance para que la paginación
 * de un componente no toggle el spinner de otro.
 */

// Singleton in-memory cache for summary — shared across all components
// using this composable, así DashboardGroupPulse y otros widgets no
// pegan a la red dos veces en el mismo page load.
const summaryCache = ref(null);
const summaryLoadedAt = ref(0);
const SUMMARY_TTL_MS = 25_000; // ligeramente < backend 30s para evitar stale-fest

// Deduplication: concurrent fetchSummary() calls share the same in-flight Promise.
// Mismo patrón que useContractGate.refresh().
let summaryPromise = null;

export function useGroupPulse() {
    const api = useApi();
    const loading = ref(false);
    const error = ref(null);

    const isFresh = computed(() =>
        summaryCache.value !== null
        && Date.now() - summaryLoadedAt.value < SUMMARY_TTL_MS
    );

    /**
     * Fetch the dashboard widget summary.
     * @param {{ force?: boolean }} options
     * @returns {Promise<object|null>} summary payload, or null si 204 (cliente sin coach).
     */
    async function fetchSummary({ force = false } = {}) {
        if (!force && isFresh.value) return summaryCache.value;
        if (summaryPromise) return summaryPromise;

        loading.value = true;
        error.value = null;

        summaryPromise = (async () => {
            try {
                const response = await api.get('/api/v/client/group-pulse', {
                    params: { scope: 'summary' },
                });
                // 204 No Content → cliente sin coach asignado.
                // Cacheamos null + timestamp para evitar polling agresivo en clientes huérfanos.
                if (response.status === 204) {
                    summaryCache.value = null;
                    summaryLoadedAt.value = Date.now();
                    return null;
                }
                summaryCache.value = response.data;
                summaryLoadedAt.value = Date.now();
                return response.data;
            } catch (err) {
                error.value = err.response?.data?.message || 'No se pudo cargar el latido del grupo.';
                return null;
            } finally {
                loading.value = false;
                summaryPromise = null;
            }
        })();

        return summaryPromise;
    }

    /**
     * Fetch a paginated feed page. No se cachea in-memory porque la
     * paginación vive en el consumidor (GroupPulseFeed.vue) y un TTL
     * crearía UX stale al cambiar de filtro o página.
     * @param {{ time?: 'today'|'week'|'all', type?: 'all'|'pr'|'workout', page?: number, perPage?: number }} options
     * @returns {Promise<{events: Array, pagination: {current_page:number, last_page:number, total:number}}|null>}
     */
    async function fetchFeed({ time = 'today', type = 'all', page = 1, perPage = 10 } = {}) {
        loading.value = true;
        error.value = null;
        try {
            const response = await api.get('/api/v/client/group-pulse', {
                params: { scope: 'feed', time, type, page, per_page: perPage },
            });
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'No se pudo cargar el feed del grupo.';
            return null;
        } finally {
            loading.value = false;
        }
    }

    return {
        summary: summaryCache,  // ref directo — consumers leen summary.value reactivamente
        loading,
        error,
        isFresh,
        fetchSummary,
        fetchFeed,
    };
}

/**
 * Reset del cache singleton — usar al hacer logout o cambio de impersonación
 * para evitar que un usuario vea el summary del anterior. Por ahora no se
 * exporta a auth store; los Tasks 10-16 decidirán si se cablea.
 */
export function resetGroupPulse() {
    summaryCache.value = null;
    summaryLoadedAt.value = 0;
    summaryPromise = null;
}
