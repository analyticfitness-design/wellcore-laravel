import { ref } from 'vue';
import { useApi } from './useApi';

/**
 * useBroadcast — composable para Admin Broadcast Center.
 * State singleton para que el composer mantenga state entre tab switches.
 */

const previewCache = new Map();
const PREVIEW_TTL_MS = 30_000;

const audience = ref('clients'); // 'clients' | 'coaches' | 'all_communities'
const segment = ref({ plan: null, status: ['activo'], coach_id: null, inactive_days: null });
const subject = ref('');
const body = ref('');
const pushEnabled = ref(false);
const recipientCount = ref(null);
const sending = ref(false);
const history = ref([]);

export function useBroadcast() {
    const api = useApi();

    async function previewCount() {
        const params = { audience: audience.value, segment: segment.value };
        const key = JSON.stringify(params);
        if (previewCache.has(key)) {
            const c = previewCache.get(key);
            if (Date.now() - c.timestamp < PREVIEW_TTL_MS) {
                recipientCount.value = c.count;
                return c.count;
            }
        }
        try {
            const res = await api.post('/api/v/admin/broadcast/preview', params);
            const count = res.data?.count ?? 0;
            previewCache.set(key, { count, timestamp: Date.now() });
            recipientCount.value = count;
            return count;
        } catch (err) {
            // eslint-disable-next-line no-console
            console.error('[useBroadcast] preview failed', err);
            return 0;
        }
    }

    async function send() {
        sending.value = true;
        try {
            const payload = {
                audience: audience.value,
                segment: segment.value,
                subject: subject.value,
                body: body.value,
                push_enabled: pushEnabled.value,
            };
            const res = await api.post('/api/v/admin/broadcast/send', payload);
            subject.value = '';
            body.value = '';
            pushEnabled.value = false;
            return res.data;
        } finally {
            sending.value = false;
        }
    }

    async function fetchHistory({ page = 1, senderType = null } = {}) {
        const params = { page };
        if (senderType) params.sender_type = senderType;
        try {
            const res = await api.get('/api/v/admin/broadcast/history', { params });
            history.value = res.data?.data || res.data?.history || [];
            return res.data;
        } catch (err) {
            // eslint-disable-next-line no-console
            console.error('[useBroadcast] history failed', err);
            return null;
        }
    }

    return {
        audience, segment, subject, body, pushEnabled, recipientCount, sending, history,
        previewCount, send, fetchHistory,
    };
}

export function resetBroadcast() {
    previewCache.clear();
    audience.value = 'clients';
    segment.value = { plan: null, status: ['activo'], coach_id: null, inactive_days: null };
    subject.value = '';
    body.value = '';
    pushEnabled.value = false;
    recipientCount.value = null;
    history.value = [];
}
