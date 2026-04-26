import { ref, computed } from 'vue';
import { useApi } from './useApi';

const requires        = ref(false);
const version         = ref('');
const html            = ref('');
const scrollCompleted = ref(false);
const submitting      = ref(false);
const error           = ref(null);

// Deduplication: all concurrent refresh() calls share the same in-flight Promise.
let refreshPromise = null;

export function useContractGate() {
    const api = useApi();

    async function refresh() {
        if (refreshPromise) return refreshPromise;

        refreshPromise = (async () => {
            error.value = null;
            try {
                const { data } = await api.get('/api/v/coach/contract/status');
                requires.value = !!data.requires_acceptance;
                version.value  = data.version || '';
                html.value     = data.html || '';
                if (!requires.value) {
                    scrollCompleted.value = false;
                }
            } catch (e) {
                // 401 is normal pre-login; ignore. Anything else surfaces.
                if (e?.response?.status !== 401) {
                    error.value = e?.response?.data?.error || 'No fue posible verificar el contrato.';
                    requires.value = false; // fail-open: backend middleware es el gate real
                }
            } finally {
                refreshPromise = null;
            }
        })();

        return refreshPromise;
    }

    async function accept() {
        if (!scrollCompleted.value) return false;
        submitting.value = true;
        error.value = null;
        try {
            await api.post('/api/v/coach/contract/accept', {
                version: version.value,
                scroll_completed: true,
            });
            requires.value = false;
            html.value = '';
            scrollCompleted.value = false;
            return true;
        } catch (e) {
            error.value = e?.response?.data?.error || 'No fue posible registrar la aceptación.';
            return false;
        } finally {
            submitting.value = false;
        }
    }

    async function decline() {
        submitting.value = true;
        error.value = null;
        try {
            await api.post('/api/v/coach/contract/decline');
            return true;
        } catch (e) {
            error.value = e?.response?.data?.error || 'No fue posible registrar el rechazo.';
            return false;
        } finally {
            submitting.value = false;
        }
    }

    function markScrollComplete() {
        scrollCompleted.value = true;
    }

    return {
        requires,
        version,
        html,
        scrollCompleted: computed(() => scrollCompleted.value),
        submitting,
        error,
        refresh,
        accept,
        decline,
        markScrollComplete,
    };
}

export function resetContractGate() {
    requires.value        = false;
    version.value         = '';
    html.value            = '';
    scrollCompleted.value = false;
    submitting.value      = false;
    error.value           = null;
    refreshPromise        = null;  // discard any in-flight fetch on logout
}
