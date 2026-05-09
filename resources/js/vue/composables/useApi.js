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

// Guard against re-entrant 401/403 triggers during impersonation restore.
// Without this, concurrent in-flight requests that all 401 can each call
// _restoreCoachSession() simultaneously, causing double-redirect loops.
let _restoringSession = false;

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
        timeout: 20000,
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

    // Handle 401/403 responses globally with auto-restore for impersonation
    _instance.interceptors.response.use(
        (response) => response,
        (error) => {
            if (error?.response?.status === 403 && error.response?.data?.contract_required) {
                import('./useContractGate').then(({ useContractGate }) => {
                    useContractGate().refresh();
                });
            }

            const status = error?.response?.status;

            // ─── Auto-restore coach session si token impersonado expiró ───
            // Caso típico: coach impersona cliente, deja PWA por horas, vuelve
            // y el token cliente (8h) expiró. Sin este fix queda atrapado en
            // "Acceso solo para clientes" (403) o redirect a /login (401),
            // perdiendo todo el contexto coach.
            if ((status === 401 || status === 403) && _shouldRestoreCoachSession() && !_restoringSession) {
                _restoringSession = true;
                _restoreCoachSession();
                return Promise.reject(error);
            }

            if (error.response && error.response.status === 401) {
                authStore.clearAuth();
                window.location.href = '/login';
                // Freeze: el redirect está en curso. Retornar una promesa que nunca
                // se resuelve ni rechaza evita que el componente muestre el estado de
                // error mientras el navegador procesa la navegación (1-2s en móvil PWA).
                return new Promise(() => {});
            }

            // 500+ server errors
            if (error.response && error.response.status >= 500) {
                // TODO: wire to useToastStore() once a global toast bus is available
                console.error('[API] Server error', error.response.status, error.config?.url);
            }

            // Network errors (no response, not an intentional cancellation or timeout)
            if (!error.response && error.code !== 'ERR_CANCELED' && error.code !== 'ECONNABORTED') {
                // TODO: wire to useToastStore() for "Sin conexion — verifica tu red"
                console.error('[API] Network error', error.code, error.config?.url);
            }

            // Timeout
            if (error.code === 'ECONNABORTED') {
                // TODO: wire to useToastStore() for "La solicitud tardó demasiado — intenta de nuevo"
                console.error('[API] Timeout', error.config?.url);
            }

            return Promise.reject(error);
        }
    );

    return _instance;
}

/**
 * Verifica si hay una sesión coach respaldada que podemos restaurar.
 * Solo aplica cuando estamos en modo impersonación (wc_impersonating_by_coach).
 */
function _shouldRestoreCoachSession() {
    if (typeof localStorage === 'undefined') return false;
    const isImpersonating = localStorage.getItem('wc_impersonating_by_coach') === '1';
    const backupToken = localStorage.getItem('wc_token_backup');
    return isImpersonating && !!backupToken;
}

/**
 * Restaura la sesión coach desde wc_*_backup keys.
 * Limpia las keys de impersonación y hace hard redirect a /coach.
 * El backend recibirá un POST /api/v/coach/impersonate/end (best-effort)
 * para cerrar el log y borrar el token expirado.
 */
function _restoreCoachSession() {
    if (typeof localStorage === 'undefined') return;
    if (typeof window === 'undefined') return;

    // Best-effort cleanup en backend (no bloquear si falla)
    try {
        const expiredToken = localStorage.getItem('wc_impersonating_token_key');
        const coachToken = localStorage.getItem('wc_token_backup');
        if (expiredToken && coachToken) {
            // Fire-and-forget: no await, no esperar respuesta
            fetch('/api/v/coach/impersonate/end', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${coachToken}`,
                },
                body: JSON.stringify({ token: expiredToken }),
                keepalive: true,
            }).catch(() => { /* noop */ });
        }
    } catch { /* noop */ }

    // Restaurar coach session desde backups
    const backup = {
        token:    localStorage.getItem('wc_token_backup'),
        userType: localStorage.getItem('wc_user_type_backup'),
        userId:   localStorage.getItem('wc_user_id_backup'),
        userName: localStorage.getItem('wc_user_name_backup'),
        portal:   localStorage.getItem('wc_user_portal_backup'),
    };

    if (backup.token)    localStorage.setItem('wc_token', backup.token);
    if (backup.userType) localStorage.setItem('wc_user_type', backup.userType);
    if (backup.userId)   localStorage.setItem('wc_user_id', backup.userId);
    localStorage.setItem('wc_user_name', backup.userName || '');
    if (backup.portal)   localStorage.setItem('wc_user_portal', backup.portal);

    // Cleanup impersonation keys
    [
        'wc_token_backup', 'wc_user_type_backup', 'wc_user_id_backup',
        'wc_user_name_backup', 'wc_user_portal_backup',
        'wc_impersonating_by_coach', 'wc_impersonating_token_key',
        'wc_impersonation_client_id',
    ].forEach((k) => localStorage.removeItem(k));

    // Hard redirect a /coach para resetear Pinia con el token coach restaurado
    window.location.href = '/coach';

    // Reset the guard after 3s in case the redirect is somehow blocked
    setTimeout(() => { _restoringSession = false; }, 3000);
}
