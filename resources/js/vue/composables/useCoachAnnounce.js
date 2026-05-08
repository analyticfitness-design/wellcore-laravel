import { ref } from 'vue';
import { useApi } from './useApi';

/**
 * useCoachAnnounce — modal "Mensaje al equipo" state.
 * Singleton module-scope: el modal se controla globalmente, abierto una sola vez.
 * Recipient count cacheado por segment combo.
 */

const recipientCountCache = new Map();
const COUNT_TTL_MS = 30_000;

const isOpen = ref(false);
const mode = ref('post'); // 'post' | 'push'
const message = ref('');
const pinHours = ref(0); // 0 = no pin
const segment = ref({ status: ['activo'], plan: null });
const recipientCount = ref(null);
const sending = ref(false);
const image = ref(null);

export function useCoachAnnounce() {
    const api = useApi();

    function open() {
        isOpen.value = true;
    }

    function close() {
        isOpen.value = false;
        message.value = '';
        pinHours.value = 0;
        image.value = null;
        sending.value = false;
        segment.value = { status: ['activo'], plan: null };
        recipientCount.value = null;
    }

    async function previewCount() {
        const params = {
            status: segment.value.status,
            plan: segment.value.plan ?? undefined,
        };
        const key = JSON.stringify(params);
        if (recipientCountCache.has(key)) {
            const c = recipientCountCache.get(key);
            if (Date.now() - c.timestamp < COUNT_TTL_MS) {
                recipientCount.value = c.count;
                return c.count;
            }
        }
        try {
            const res = await api.get('/api/v/coach/clients/count', { params });
            const count = res.data?.count ?? 0;
            recipientCountCache.set(key, { count, timestamp: Date.now() });
            recipientCount.value = count;
            return count;
        } catch (err) {
            // eslint-disable-next-line no-console
            console.error('[useCoachAnnounce] previewCount failed', err);
            return 0;
        }
    }

    async function send() {
        sending.value = true;
        try {
            const fd = new FormData();
            fd.append('type', mode.value);
            fd.append('message', message.value);
            if (mode.value === 'post' && pinHours.value > 0) {
                fd.append('pin_hours', String(pinHours.value));
            }
            if (mode.value === 'post' && image.value) {
                fd.append('image', image.value);
            }
            if (mode.value === 'push' && segment.value.plan) {
                fd.append('plan_filter', JSON.stringify({ plan: segment.value.plan }));
            }
            const res = await api.post('/api/v/coach/community/announce', fd);
            close();
            return res.data;
        } finally {
            sending.value = false;
        }
    }

    return {
        isOpen, mode, message, pinHours, segment, recipientCount, sending, image,
        open, close, previewCount, send,
    };
}

export function resetCoachAnnounce() {
    recipientCountCache.clear();
    isOpen.value = false;
    message.value = '';
    pinHours.value = 0;
    image.value = null;
    sending.value = false;
    segment.value = { status: ['activo'], plan: null };
    recipientCount.value = null;
}
