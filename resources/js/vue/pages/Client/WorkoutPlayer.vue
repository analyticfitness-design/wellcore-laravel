<script setup>
/**
 * WorkoutPlayer.vue — Dispatcher entre legacy y v2 según feature flag.
 *
 * Lógica de resolución (en orden):
 *   1. localStorage `wc_force_workout_player_v2` = '1' → v2 (QA bypass)
 *   2. localStorage `wc_force_workout_player_v2` = '0' → legacy (rollback manual)
 *   3. featureFlags store con workout_player_v2 = true → v2 (URL ?ff= o seteo)
 *   4. sessionStorage cached `wc_ff_workout_v2` → respeta valor cacheado
 *   5. /api/v/client/account-status → features.workout_player_v2 (fetch + cache)
 *   6. Fallback → legacy
 *
 * Performance: una sola petición a account-status por sesión (cache en
 * sessionStorage), y solo si los overrides locales no respondieron.
 *
 * Mantiene WorkoutPlayer.legacy.vue intacto como backup. Cuando v2 esté
 * 100% en producción y estable, la Fase 6 reemplaza este dispatcher por
 * un import directo de WorkoutPlayerV2.
 */
import { ref, computed, onMounted, defineAsyncComponent } from 'vue';
import { useApi } from '../../composables/useApi';
import { useFeatureFlags } from '../../stores/featureFlags';

const api = useApi();
const ffStore = useFeatureFlags();

const SESSION_CACHE_KEY = 'wc_ff_workout_v2';

const serverFlag = ref(false);
const flagLoaded = ref(false);

function readLocalOverride() {
    const v = localStorage.getItem('wc_force_workout_player_v2');
    if (v === '1') return true;
    if (v === '0') return false;
    return null;
}

function readSessionCache() {
    try {
        const raw = sessionStorage.getItem(SESSION_CACHE_KEY);
        if (raw === '1') return true;
        if (raw === '0') return false;
    } catch { /* ignore */ }
    return null;
}

function writeSessionCache(value) {
    try {
        sessionStorage.setItem(SESSION_CACHE_KEY, value ? '1' : '0');
    } catch { /* ignore */ }
}

const useV2 = computed(() => {
    const override = readLocalOverride();
    if (override !== null) return override;
    if (ffStore.isEnabled('workout_player_v2')) return true;
    return serverFlag.value;
});

const LegacyPlayer = defineAsyncComponent(() => import('./WorkoutPlayer.legacy.vue'));
const V2Player     = defineAsyncComponent(() => import('./WorkoutPlayerV2.vue'));

onMounted(async () => {
    // Override local manual o store store siempre gana.
    if (readLocalOverride() !== null || ffStore.isEnabled('workout_player_v2')) {
        flagLoaded.value = true;
        return;
    }
    // Cache de sesión: evita el fetch en navegaciones subsiguientes.
    const cached = readSessionCache();
    if (cached !== null) {
        serverFlag.value = cached;
        flagLoaded.value = true;
        return;
    }
    // Solo en primer load por sesión: 1 fetch a account-status.
    try {
        const response = await api.get('/api/v/client/account-status');
        const enabled = !!response.data?.features?.workout_player_v2;
        serverFlag.value = enabled;
        writeSessionCache(enabled);
    } catch {
        serverFlag.value = false;
        writeSessionCache(false);
    } finally {
        flagLoaded.value = true;
    }
});
</script>

<template>
  <component :is="useV2 ? V2Player : LegacyPlayer" v-if="flagLoaded" />
  <!-- Loading skeleton mientras se resuelve la flag (typically <100ms) -->
  <div v-else class="space-y-4 animate-pulse p-4">
    <div class="h-16 rounded-xl bg-wc-bg-tertiary"></div>
    <div class="h-24 rounded-xl bg-wc-bg-tertiary"></div>
    <div class="h-48 rounded-xl bg-wc-bg-tertiary"></div>
  </div>
</template>
