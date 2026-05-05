import { ref, computed } from 'vue';
import { useApi } from './useApi';

/**
 * useModerationQueue — composable para Admin Moderation Queue.
 * Singleton ref para que el badge de tab muestre count reactivo.
 */

const queueData = ref(null);
const queueLoadedAt = ref(0);
const QUEUE_TTL_MS = 30_000;

export function useModerationQueue() {
    const api = useApi();
    const loading = ref(false);
    const error = ref(null);

    const pendingCount = computed(() => {
        if (!queueData.value) return 0;
        const list = queueData.value.data || [];
        return list.length;
    });

    async function fetchQueue({ force = false } = {}) {
        if (!force && queueData.value && Date.now() - queueLoadedAt.value < QUEUE_TTL_MS) {
            return queueData.value;
        }
        loading.value = true;
        error.value = null;
        try {
            const res = await api.get('/api/v/admin/community/moderation/queue');
            queueData.value = res.data;
            queueLoadedAt.value = Date.now();
            return res.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'No se pudo cargar moderation queue.';
            return null;
        } finally {
            loading.value = false;
        }
    }

    async function dismissReport(reportId) {
        const res = await api.post(`/api/v/admin/community/moderation/${reportId}/dismiss`);
        await fetchQueue({ force: true });
        return res.data;
    }

    async function actionReport(reportId, action, reason = null) {
        const res = await api.post(`/api/v/admin/community/moderation/${reportId}/action`, {
            action, reason,
        });
        await fetchQueue({ force: true });
        return res.data;
    }

    return { queue: queueData, pendingCount, loading, error, fetchQueue, dismissReport, actionReport };
}

export function resetModerationQueue() {
    queueData.value = null;
    queueLoadedAt.value = 0;
}
