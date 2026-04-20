import { ref, computed } from 'vue';
import { useApi } from './useApi';

/**
 * useMedals — Composable para el sistema de medallas del cliente.
 *
 * Backend: GET /api/v/client/medals
 * Response shape:
 * {
 *   stats: { displayName, avatarInitial, streak, totalWorkouts, totalXP, level,
 *            xpCurrentLevel, xpNextLevel, achievedMedals, totalMedals },
 *   medals: [{ id, slug, name, description, requirement, targetValue, xp,
 *              category, tier, iconLabel, stripeColors, achieved, achievedAt,
 *              progress?: { current, target } }]
 * }
 *
 * Expone un `newMedal` ref que se llena cuando una medalla nueva se desbloquea
 * entre fetches. La página de Logros observa este ref para disparar la
 * celebración fullscreen.
 */
export function useMedals() {
    const api = useApi();

    const medals = ref([]);
    const stats = ref(null);
    const loading = ref(false);
    const error = ref(null);

    // IDs de medallas logradas que ya conocíamos en el último fetch.
    // Sirve para detectar "nuevas" desbloqueadas entre polls / refetches.
    const knownUnlockedIds = ref(new Set());
    const newMedal = ref(null); // la última medalla recién desbloqueada
    const isFirstLoad = ref(true);

    // ── Ordenamiento: logradas > en progreso (>0%) > bloqueadas ──────────────
    function sortMedals(list) {
        return [...list].sort((a, b) => {
            const scoreA = medalSortScore(a);
            const scoreB = medalSortScore(b);
            if (scoreA !== scoreB) return scoreA - scoreB;
            return (a.name || '').localeCompare(b.name || '');
        });
    }

    function medalSortScore(m) {
        if (m.achieved) return 0;
        const p = m.progress;
        if (p && p.target > 0 && p.current > 0) return 1;
        return 2;
    }

    // ── Fetch principal ──────────────────────────────────────────────────────
    async function fetchMedals() {
        loading.value = true;
        error.value = null;
        try {
            const res = await api.get('/api/v/client/medals');
            const incoming = Array.isArray(res.data?.medals) ? res.data.medals : [];

            // Detectar medallas recién desbloqueadas (solo después del primer load)
            if (!isFirstLoad.value) {
                const newlyUnlocked = incoming.find(
                    (m) => m.achieved && !knownUnlockedIds.value.has(m.id),
                );
                if (newlyUnlocked) {
                    newMedal.value = newlyUnlocked;
                }
            }

            // Actualizar el set con todas las desbloqueadas actuales
            knownUnlockedIds.value = new Set(
                incoming.filter((m) => m.achieved).map((m) => m.id),
            );

            medals.value = sortMedals(incoming);
            stats.value = res.data?.stats ?? null;
            isFirstLoad.value = false;
        } catch (e) {
            error.value = e.response?.data?.message || 'Error al cargar medallas';
            medals.value = [];
            stats.value = null;
        } finally {
            loading.value = false;
        }
    }

    function clearNewMedal() {
        newMedal.value = null;
    }

    // ── Computed views ───────────────────────────────────────────────────────
    const unlocked = computed(() => medals.value.filter((m) => m.achieved));
    const locked = computed(() => medals.value.filter((m) => !m.achieved));

    const byTier = computed(() => {
        const buckets = {
            bronce: [],
            plata: [],
            oro: [],
            platino: [],
            legendario: [],
        };
        for (const m of medals.value) {
            if (buckets[m.tier]) buckets[m.tier].push(m);
        }
        return buckets;
    });

    const unlockedCount = computed(() => unlocked.value.length);
    const totalCount = computed(() => medals.value.length);

    return {
        // state
        medals,
        stats,
        loading,
        error,
        newMedal,

        // computed
        unlocked,
        locked,
        byTier,
        unlockedCount,
        totalCount,

        // actions
        fetchMedals,
        clearNewMedal,
    };
}
