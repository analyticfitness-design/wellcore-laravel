import { ref, computed } from 'vue';
import { useApi } from './useApi';

/**
 * useMedals — Composable SINGLETON para el sistema de medallas del cliente.
 *
 * Estado a nivel de modulo: todos los consumidores comparten las mismas refs,
 * asi cualquier accion (workout finish, check-in enviado, etc) puede llamar
 * `fetchMedals()` y el modal de celebracion montado en ClientLayout.vue se
 * entera inmediatamente — sin prop drilling ni store global.
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
 */

// ─── Module-level singleton state ───────────────────────────────────────────
const medals = ref([]);
const stats = ref(null);
const loading = ref(false);
const error = ref(null);

// Medallas desbloqueadas conocidas (para detectar nuevas entre fetches)
const knownUnlockedIds = ref(new Set());
const newMedal = ref(null);
const isFirstLoad = ref(true);

// Level-up detection
const previousLevel = ref(null);
const levelUp = ref(null); // { from, to, totalXP, xpGained }

// Computed views (compartidas)
const unlocked = computed(() => medals.value.filter((m) => m.achieved));
const locked = computed(() => medals.value.filter((m) => !m.achieved));

const byTier = computed(() => {
    const buckets = { bronce: [], plata: [], oro: [], platino: [], legendario: [] };
    for (const m of medals.value) {
        if (buckets[m.tier]) buckets[m.tier].push(m);
    }
    return buckets;
});

const unlockedCount = computed(() => unlocked.value.length);
const totalCount = computed(() => medals.value.length);

// ── Orden: logradas > en progreso > bloqueadas ──────────────────────────────
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

// ── Fetch (compartido, puede llamarse desde cualquier vista) ───────────────
let inflight = null; // dedup de fetches concurrentes
async function fetchMedals() {
    if (inflight) return inflight;
    const api = useApi();
    loading.value = true;
    error.value = null;
    inflight = (async () => {
        try {
            const res = await api.get('/api/v/client/medals');
            const incoming = Array.isArray(res.data?.medals) ? res.data.medals : [];

            // Nueva medalla desbloqueada
            if (!isFirstLoad.value) {
                const newlyUnlocked = incoming.find(
                    (m) => m.achieved && !knownUnlockedIds.value.has(m.id),
                );
                if (newlyUnlocked) newMedal.value = newlyUnlocked;
            }

            knownUnlockedIds.value = new Set(
                incoming.filter((m) => m.achieved).map((m) => m.id),
            );

            medals.value = sortMedals(incoming);
            const incomingStats = res.data?.stats ?? null;

            // Level-up
            if (!isFirstLoad.value && incomingStats && previousLevel.value !== null) {
                const from = previousLevel.value;
                const to = Number(incomingStats.level ?? 0);
                if (to > from) {
                    const prevTotalXP = Number(stats.value?.totalXP ?? 0);
                    levelUp.value = {
                        from,
                        to,
                        totalXP: Number(incomingStats.totalXP ?? 0),
                        xpGained: Math.max(0, Number(incomingStats.totalXP ?? 0) - prevTotalXP),
                    };
                }
            }
            previousLevel.value = Number(incomingStats?.level ?? previousLevel.value ?? 0);

            stats.value = incomingStats;
            isFirstLoad.value = false;
        } catch (e) {
            error.value = e.response?.data?.message || 'Error al cargar medallas';
        } finally {
            loading.value = false;
            inflight = null;
        }
    })();
    return inflight;
}

function clearNewMedal() {
    newMedal.value = null;
}

function clearLevelUp() {
    levelUp.value = null;
}

/**
 * Todos los consumidores reciben referencias al mismo estado de modulo.
 */
export function useMedals() {
    return {
        // state
        medals,
        stats,
        loading,
        error,
        newMedal,
        levelUp,
        // computed
        unlocked,
        locked,
        byTier,
        unlockedCount,
        totalCount,
        // actions
        fetchMedals,
        clearNewMedal,
        clearLevelUp,
    };
}
