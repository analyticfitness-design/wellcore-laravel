<script setup>
/**
 * WorkoutPlayer.vue — Dispatcher entre legacy y v2 según feature flag.
 *
 * Lógica:
 *   1. Si localStorage tiene wc_force_workout_player_v2 = '1' → v2 (QA bypass)
 *   2. Si localStorage tiene wc_force_workout_player_v2 = '0' → legacy (rollback manual)
 *   3. Si store featureFlags activa workout_player_v2 (vía URL ?ff=workout_player_v2 o seteo manual) → v2
 *   4. Si /api/v/client/account-status devuelve features.workout_player_v2=true → v2
 *   5. En cualquier otro caso → legacy
 *
 * Mantiene WorkoutPlayer.legacy.vue como backup independiente. Cuando v2 esté
 * en producción al 100% y estable, la Fase 6 reemplaza este dispatcher por
 * un import directo de WorkoutPlayerV2.
 */
import { ref, computed, onMounted, defineAsyncComponent, h } from 'vue';
import { useApi } from '../../composables/useApi';
import { useFeatureFlags } from '../../stores/featureFlags';

const api = useApi();
const ffStore = useFeatureFlags();

const serverFlag = ref(false);
const flagLoaded = ref(false);

const localOverride = computed(() => {
    const v = localStorage.getItem('wc_force_workout_player_v2');
    if (v === '1') return true;
    if (v === '0') return false;
    return null;
});

const useV2 = computed(() => {
    if (localOverride.value !== null) return localOverride.value;
    if (ffStore.isEnabled('workout_player_v2')) return true;
    return serverFlag.value;
});

const LegacyPlayer = defineAsyncComponent(() => import('./WorkoutPlayer.legacy.vue'));
const V2Player     = defineAsyncComponent(() => import('./WorkoutPlayerV2.vue'));

onMounted(async () => {
    // Si los overrides locales ya respondieron, no hace falta consultar al server.
    if (localOverride.value !== null || ffStore.isEnabled('workout_player_v2')) {
        flagLoaded.value = true;
        return;
    }
    try {
        const response = await api.get('/api/v/client/account-status');
        serverFlag.value = !!response.data?.features?.workout_player_v2;
    } catch {
        serverFlag.value = false;
    } finally {
        flagLoaded.value = true;
    }
});
</script>

<template>
  <component :is="useV2 ? V2Player : LegacyPlayer" v-if="flagLoaded" />
  <!-- Loading skeleton mientras se resuelve la flag -->
  <div v-else class="space-y-4 animate-pulse p-4">
    <div class="h-16 rounded-xl bg-wc-bg-tertiary"></div>
    <div class="h-24 rounded-xl bg-wc-bg-tertiary"></div>
    <div class="h-48 rounded-xl bg-wc-bg-tertiary"></div>
  </div>
</template>
