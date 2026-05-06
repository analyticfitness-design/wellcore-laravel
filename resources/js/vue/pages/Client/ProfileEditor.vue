<script setup>
/**
 * ProfileEditor.vue — Dispatcher entre Profile v2 y legacy.
 *
 * **Default: legacy** (opt-in conservador hasta que v2 demuestre estabilidad).
 *
 * Lógica de resolución (en orden):
 *   1. localStorage `wc_force_profile_v2` = '1' → v2
 *   2. localStorage `wc_force_profile_v2` = '0' → legacy (rollback manual)
 *   3. Feature flag store `profile_v2` (URL `?ff=profile_v2`) → v2
 *   4. Server flag `/api/v/client/account-status` → features.profile_v2 → v2
 *      (mientras se resuelve el fetch se muestra legacy, default seguro)
 *   5. Default → legacy
 *
 * Async chunks: ambos via defineAsyncComponent para evitar costo en el bundle
 * de quien no use el opt-in.
 *
 * NO monta ClientLayout: cada componente lo monta internamente (mismo patrón
 * que WorkoutPlayer.vue).
 */
import { computed, defineAsyncComponent, onMounted, ref } from 'vue';
import { useApi } from '../../composables/useApi';
import { useFeatureFlags } from '../../stores/featureFlags';

const ffStore = useFeatureFlags();
const api = useApi();

function readLocalOverride() {
    try {
        const v = localStorage.getItem('wc_force_profile_v2');
        if (v === '1') return true;
        if (v === '0') return false;
    } catch { /* ignore */ }
    return null;
}

const serverFlagOn = ref(false);

const useV2 = computed(() => {
    // Override manual del usuario gana siempre
    const override = readLocalOverride();
    if (override !== null) return override;
    // URL ?ff=profile_v2 / store
    if (ffStore.isEnabled('profile_v2')) return true;
    // Server flag
    if (serverFlagOn.value) return true;
    // Default: legacy (opt-in conservador)
    return false;
});

onMounted(async () => {
    // Si ya tenemos override o store flag, evitar request innecesario
    if (readLocalOverride() !== null) return;
    if (ffStore.isEnabled('profile_v2')) return;
    try {
        const res = await api.get('/api/v/client/account-status');
        const enabled = res?.data?.features?.profile_v2 === true;
        if (enabled) serverFlagOn.value = true;
    } catch {
        // Silent: mantener legacy si el endpoint falla
    }
});

const Legacy = defineAsyncComponent(() => import('./ProfileEditor.legacy.vue'));
const V2     = defineAsyncComponent(() => import('./ProfileV2.vue'));
</script>

<template>
  <component :is="useV2 ? V2 : Legacy" />
</template>
