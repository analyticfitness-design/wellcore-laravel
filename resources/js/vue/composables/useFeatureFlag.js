import { ref, computed } from 'vue';
import { useFeatureFlags } from '../stores/featureFlags';

/**
 * Verifica si una feature flag está activa para el usuario actual.
 *
 * Orden de prioridad:
 * 1. localStorage override manual (wc_force_{flag} = '1' o '0') — para QA
 * 2. Store useFeatureFlags (localStorage wc_flags_v1, soporta ?ff= URL param)
 * 3. auth.features del server (cuando esté disponible en auth store)
 *
 * QA bypass rápido:
 *   localStorage.setItem('wc_force_workout_player_v2', '1'); location.reload();
 *   o simplemente: ?ff=workout_player_v2
 */
export function useFeatureFlag(flag) {
    const localOverride = localStorage.getItem(`wc_force_${flag}`);
    if (localOverride === '1') return ref(true);
    if (localOverride === '0') return ref(false);

    const ffStore = useFeatureFlags();
    return computed(() => ffStore.isEnabled(flag));
}
