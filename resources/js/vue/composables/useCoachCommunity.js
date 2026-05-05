import { ref } from 'vue';
import { useApi } from './useApi';

/**
 * useCoachCommunity — singleton composable para feed + threads + achievements + pulsos
 * del coach. Pattern espejo de useGroupPulse: cache module-scope + TTL 25s + Promise dedup
 * + reset hook para impersonation.
 */

const feedCache = new Map(); // key = `${filter}:${page}:${perPage}` → { data, timestamp }
const FEED_TTL_MS = 25_000;
const feedPromises = new Map();

export function useCoachCommunity() {
    const api = useApi();
    const loading = ref(false);
    const error = ref(null);

    async function fetchFeed({ filter = 'all', page = 1, perPage = 20, force = false } = {}) {
        const key = `${filter}:${page}:${perPage}`;
        if (!force && feedCache.has(key)) {
            const cached = feedCache.get(key);
            if (Date.now() - cached.timestamp < FEED_TTL_MS) return cached.data;
        }
        if (feedPromises.has(key)) return feedPromises.get(key);

        loading.value = true;
        error.value = null;
        const promise = (async () => {
            try {
                const res = await api.get('/api/v/coach/community/posts', {
                    params: { filter, page, per_page: perPage },
                });
                feedCache.set(key, { data: res.data, timestamp: Date.now() });
                return res.data;
            } catch (err) {
                error.value = err.response?.data?.message || 'No se pudo cargar el feed.';
                if (err.response?.status >= 500 || !err.response) {
                    // eslint-disable-next-line no-console
                    console.error('[useCoachCommunity] fetchFeed failed', err);
                }
                return null;
            } finally {
                loading.value = false;
                feedPromises.delete(key);
            }
        })();
        feedPromises.set(key, promise);
        return promise;
    }

    async function fetchThreads({ sinceDays = 7, page = 1, perPage = 20 } = {}) {
        loading.value = true;
        error.value = null;
        try {
            const res = await api.get('/api/v/coach/community/threads', {
                params: { since_days: sinceDays, page, per_page: perPage },
            });
            return res.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'No se pudo cargar conversaciones.';
            if (err.response?.status >= 500 || !err.response) {
                // eslint-disable-next-line no-console
                console.error('[useCoachCommunity] fetchThreads failed', err);
            }
            return null;
        } finally {
            loading.value = false;
        }
    }

    async function fetchAchievements({ period = 'week', page = 1, perPage = 20 } = {}) {
        loading.value = true;
        error.value = null;
        try {
            const res = await api.get('/api/v/coach/community/achievements', {
                params: { period, page, per_page: perPage },
            });
            return res.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'No se pudo cargar logros.';
            return null;
        } finally {
            loading.value = false;
        }
    }

    async function fetchPulsos() {
        loading.value = true;
        error.value = null;
        try {
            const res = await api.get('/api/v/coach/community/pulsos');
            return res.data?.data ?? [];
        } catch (err) {
            error.value = err.response?.data?.message || 'No se pudo cargar pulsos.';
            return [];
        } finally {
            loading.value = false;
        }
    }

    return { loading, error, fetchFeed, fetchThreads, fetchAchievements, fetchPulsos };
}

/**
 * Reset cache singleton — usar al hacer logout o cambio de impersonación.
 */
export function resetCoachCommunity() {
    feedCache.clear();
    feedPromises.clear();
}
