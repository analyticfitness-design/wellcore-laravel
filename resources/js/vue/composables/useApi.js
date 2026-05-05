import axios from 'axios';
import { useAuthStore } from '../stores/auth';

/**
 * Creates a pre-configured axios instance that automatically attaches
 * the Bearer token from the auth store to every request.
 *
 * Usage:
 *   import { useApi } from '../composables/useApi';
 *   const api = useApi();
 *   const { data } = await api.get('/api/v/client/dashboard');
 */
/**
 * Preview mode: when admin renders a form in /admin/forms-preview/* iframe,
 * we return a mock client that resolves all requests with empty data.
 * This prevents 401 redirects and lets forms render their empty/loading states.
 */
function isPreviewMode() {
    return typeof window !== 'undefined'
        && window.location.pathname.startsWith('/admin/forms-preview');
}

// Per-endpoint defaults so forms render their "happy path" UI instead of degenerate states.
// weightChange: null (not undefined) keeps MetricsTracker's v-if guard working correctly.
// is_checkin_available: true prevents the "no disponible" banner from showing in preview.
const PREVIEW_DEFAULTS = {
    '/api/v/client/checkin': {
        is_checkin_available: true,
        already_submitted: false,
        recent_checkins: [],
        show_tutorial: false,
    },
    '/api/v/client/metrics': {
        currentWeight: null,
        weightChange: null,
        history: [],
        chartData: [],
        weightTrend: [],
        weeklyCheckins: [],
        latestComposition: null,
        trainingVolume: [],
        showTutorial: false,
    },
};

function createPreviewMockClient() {
    const resolve = (url) => Promise.resolve({
        data: PREVIEW_DEFAULTS[url] ?? {},
        status: 200,
        headers: {},
        config: {},
    });
    return {
        get: (url) => resolve(url),
        post: (url) => resolve(url),
        put: (url) => resolve(url),
        patch: (url) => resolve(url),
        delete: (url) => resolve(url),
        request: () => resolve(''),
        interceptors: { request: { use: () => 0 }, response: { use: () => 0 } },
        defaults: { headers: { common: {} } },
    };
}

// Singleton instance: avoids creating dozens of orphan axios clients with
// duplicate interceptors when 50+ components call useApi() (especially with
// 30s polling). The auth store reference is captured once but its `token`
// field is read inside the interceptor on every request, so impersonation
// token swaps remain reactive.
let _instance = null;

export function useApi() {
    const authStore = useAuthStore();

    if (isPreviewMode()) {
        return createPreviewMockClient();
    }

    if (_instance) {
        return _instance;
    }

    _instance = axios.create({
        baseURL: '',
        headers: {
            'Accept': 'application/json',
        },
    });

    // Attach token on every request and handle Content-Type
    _instance.interceptors.request.use((config) => {
        if (authStore.token) {
            config.headers.Authorization = `Bearer ${authStore.token}`;
        }
        // Let axios set the Content-Type automatically for FormData (multipart/form-data with boundary).
        // Only set JSON content-type for non-FormData requests.
        if (!(config.data instanceof FormData)) {
            config.headers['Content-Type'] = 'application/json';
        }
        return config;
    });

    // Handle 401 responses globally — clear auth and redirect to login
    _instance.interceptors.response.use(
        (response) => response,
        (error) => {
            if (error?.response?.status === 403 && error.response?.data?.contract_required) {
                import('./useContractGate').then(({ useContractGate }) => {
                    useContractGate().refresh();
                });
            }
            if (error.response && error.response.status === 401) {
                authStore.clearAuth();
                window.location.href = '/login';
            }
            return Promise.reject(error);
        }
    );

    return _instance;
}
