<script setup>
/**
 * WorkoutPlayer.vue — Dispatcher entre v2 (default) y legacy.
 *
 * **2026-05-06**: Activación 100% para todos los clientes. v2 es el default.
 *
 * Lógica de resolución (en orden):
 *   1. localStorage `wc_force_workout_player_v2` = '1' → v2 (force)
 *   2. localStorage `wc_force_workout_player_v2` = '0' → legacy (rollback manual user)
 *   3. featureFlags store con `workout_player_legacy` = true → legacy (URL ?ff=)
 *   4. Default → V2 (instantáneo, sin fetch)
 *
 * Performance: 0 latencia adicional. V2 carga directamente. Si el usuario quiere
 * legacy: localStorage.setItem('wc_force_workout_player_v2','0'); reload.
 *
 * Mantiene WorkoutPlayer.legacy.vue intacto como backup. Cuando el v2 demuestre
 * estabilidad por 7 días, la Fase 6 reemplaza este dispatcher por un import
 * directo de WorkoutPlayerV2 y elimina el legacy.
 */
import { computed, defineAsyncComponent } from 'vue';
import { useFeatureFlags } from '../../stores/featureFlags';

const ffStore = useFeatureFlags();

function readLocalOverride() {
    try {
        const v = localStorage.getItem('wc_force_workout_player_v2');
        if (v === '1') return true;
        if (v === '0') return false;
    } catch { /* ignore */ }
    return null;
}

const useV2 = computed(() => {
    // Override manual del usuario gana siempre.
    const override = readLocalOverride();
    if (override !== null) return override;
    // Opt-out global a legacy via URL ?ff=workout_player_legacy
    if (ffStore.isEnabled('workout_player_legacy')) return false;
    // Default: v2 (rollout 100%)
    return true;
});

const LegacyPlayer = defineAsyncComponent(() => import('./WorkoutPlayer.legacy.vue'));
const V2Player     = defineAsyncComponent(() => import('./WorkoutPlayerV2.vue'));
</script>

<template>
  <component :is="useV2 ? V2Player : LegacyPlayer" />
</template>
