import { ref, computed } from 'vue';
import { useFeatureFlags } from '../stores/featureFlags';

/**
 * Verifica si una feature flag está activa para el usuario actual.
 *
 * Orden de prioridad:
 * 1. localStorage override manual (wc_force_{flag} = '1' o '0') — para QA
 * 2. Store useFeatureFlags (localStorage wc_flags_v1, soporta ?ff= URL param)
 * 3. Server sync: GET /api/v/client/account-status devuelve features.{flag}
 *    según WC_<FLAG>_PCT/USERS env vars en EasyPanel — popula el store en
 *    background y reactiva el computed cuando llega la respuesta.
 *
 * QA bypass rápido:
 *   localStorage.setItem('wc_force_workout_player_v2', '1'); location.reload();
 *   o simplemente: ?ff=workout_player_v2
 */

let serverSyncStarted = false;

async function syncFeaturesFromServer(ffStore) {
    if (serverSyncStarted) return;
    serverSyncStarted = true;

    const token = localStorage.getItem('wc_token');
    if (!token) return;

    try {
        const res = await fetch('/api/v/client/account-status', {
            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${token}` },
            credentials: 'same-origin',
            cache: 'no-store',
        });
        if (!res.ok) return;
        const data = await res.json().catch(() => null);
        if (!data?.features) return;
        Object.entries(data.features).forEach(([k, v]) => {
            if (typeof v === 'boolean') ffStore.set(k, v);
        });
    } catch {
        // sin red o 401: flag queda como esté en localStorage / default false
    }
}

export function useFeatureFlag(flag) {
    const localOverride = localStorage.getItem(`wc_force_${flag}`);
    if (localOverride === '1') return ref(true);
    if (localOverride === '0') return ref(false);

    // Server-injected flags via window.__WC_FEATURES son la fuente de verdad para
    // rollouts pct (eligibility ya resuelta server-side con session uid).
    const serverFlag = (typeof window !== 'undefined' && window.__WC_FEATURES)
        ? window.__WC_FEATURES[flag]
        : undefined;
    if (serverFlag === true) return ref(true);
    if (serverFlag === false) return ref(false);

    const ffStore = useFeatureFlags();
    syncFeaturesFromServer(ffStore);

    return computed(() => ffStore.isEnabled(flag));
}
