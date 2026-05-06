import { ref, computed } from 'vue';
import { useApi } from './useApi';

/**
 * useProfileCompletion — score 0-100 + tier ámbar/azul/verde + missing[].
 *
 * Tiers:
 *   - score < 50  → 'low'  (ámbar #F59E0B)
 *   - 50-79       → 'mid'  (azul  #3B82F6)
 *   - 80+         → 'high' (verde #10B981)
 *
 * NUNCA rojo. La accent del sistema (#DC2626) queda reservada para CTAs y errores,
 * no para "perfil pobre".
 *
 * Uso típico (cuando se necesita refrescar completion sin re-fetch del form completo):
 *   const completion = useProfileCompletion();
 *   await completion.refresh();
 *   completion.tier.value === 'mid'
 */

export function useProfileCompletion(initial = { score: 0, missing: [] }) {
    const api = useApi();
    const state = ref({
        score: typeof initial.score === 'number' ? initial.score : 0,
        missing: Array.isArray(initial.missing) ? [...initial.missing] : [],
    });

    const tier = computed(() => {
        const s = state.value.score ?? 0;
        if (s >= 80) return 'high';
        if (s >= 50) return 'mid';
        return 'low';
    });

    // Hex literal for SVG strokes (clases Tailwind text-* no aplican a stroke="").
    const colorVar = computed(() => {
        switch (tier.value) {
            case 'high': return '#10B981'; // emerald-500 / wc success
            case 'mid':  return '#3B82F6'; // blue-500 / wc info
            default:     return '#F59E0B'; // amber-500 / wc warning
        }
    });

    // Contextual encouragement message (latino neutro, tuteo).
    const message = computed(() => {
        const s = state.value.score ?? 0;
        if (s >= 80) return 'Perfil completo — destacas en la comunidad.';
        if (s >= 50) return 'Vas bien. Suma los pendientes para tener tu perfil al máximo.';
        if (s >= 20) return 'Suma los datos pendientes para que tu coach te conozca mejor.';
        return 'Empieza tu perfil para que tu coach pueda personalizar tu plan.';
    });

    function set(next) {
        if (!next || typeof next !== 'object') return;
        state.value = {
            score: typeof next.score === 'number' ? next.score : (state.value.score ?? 0),
            missing: Array.isArray(next.missing) ? [...next.missing] : (state.value.missing ?? []),
        };
    }

    async function refresh() {
        try {
            const res = await api.get('/api/v/client/profile');
            const c = res.data?.completion;
            if (c) set(c);
            return c;
        } catch {
            // silent fail — completion no es bloqueante
            return null;
        }
    }

    return {
        state,
        tier,
        colorVar,
        message,
        set,
        refresh,
    };
}
