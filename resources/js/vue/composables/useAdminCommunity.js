import { ref } from 'vue';
import { useApi } from './useApi';

/**
 * useAdminCommunity — singleton composable para Pulse Cross-Coach + Coach Analytics drill-down.
 * Pattern espejo de useCoachPulse: cache module-scope + TTL 60s pulse / 120s analytics + Promise dedup.
 */

const pulseCache = new Map(); // period → { data, timestamp }
const PULSE_TTL_MS = 60_000;
const coachAnalyticsCache = new Map(); // coachId → { data, timestamp }
const COACH_ANALYTICS_TTL_MS = 120_000;
const promises = new Map();

export function useAdminCommunity() {
    const api = useApi();
    const loading = ref(false);
    const error = ref(null);

    async function fetchPulseCrossCoach({ period = 'week', force = false } = {}) {
        const key = `pulse:${period}`;
        if (!force && pulseCache.has(period)) {
            const cached = pulseCache.get(period);
            if (Date.now() - cached.timestamp < PULSE_TTL_MS) return cached.data;
        }
        if (promises.has(key)) return promises.get(key);

        loading.value = true;
        error.value = null;
        const promise = (async () => {
            try {
                const res = await api.get('/api/v/admin/community/pulse-cross-coach', {
                    params: { period },
                });
                pulseCache.set(period, { data: res.data, timestamp: Date.now() });
                return res.data;
            } catch (err) {
                error.value = err.response?.data?.message || 'No se pudo cargar pulse cross-coach.';
                if (err.response?.status >= 500 || !err.response) {
                    // eslint-disable-next-line no-console
                    console.error('[useAdminCommunity] pulse failed', err);
                }
                return null;
            } finally {
                loading.value = false;
                promises.delete(key);
            }
        })();
        promises.set(key, promise);
        return promise;
    }

    async function fetchCoachAnalytics(coachId, { force = false } = {}) {
        const key = `coach:${coachId}`;
        if (!force && coachAnalyticsCache.has(coachId)) {
            const c = coachAnalyticsCache.get(coachId);
            if (Date.now() - c.timestamp < COACH_ANALYTICS_TTL_MS) return c.data;
        }
        if (promises.has(key)) return promises.get(key);

        loading.value = true;
        error.value = null;
        const promise = (async () => {
            try {
                const res = await api.get(`/api/v/admin/community/coaches/${coachId}/analytics`);
                coachAnalyticsCache.set(coachId, { data: res.data, timestamp: Date.now() });
                return res.data;
            } catch (err) {
                error.value = err.response?.data?.message || 'No se pudo cargar coach analytics.';
                return null;
            } finally {
                loading.value = false;
                promises.delete(key);
            }
        })();
        promises.set(key, promise);
        return promise;
    }

    async function fetchCommunityFeed({ coachId = null, type = null, page = 1 } = {}) {
        const params = { page };
        if (coachId) params.coach_id = coachId;
        if (type) params.type = type;
        try {
            const res = await api.get('/api/v/admin/feed', { params });
            return res.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'No se pudo cargar el feed.';
            return null;
        }
    }

    return { loading, error, fetchPulseCrossCoach, fetchCoachAnalytics, fetchCommunityFeed };
}

export function resetAdminCommunity() {
    pulseCache.clear();
    coachAnalyticsCache.clear();
    promises.clear();
}
