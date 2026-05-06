import { ref, computed } from 'vue';
import { useApi } from './useApi';

/**
 * usePlanViewer — composable singleton para el tab Entrenamiento V2.
 *
 * Responsabilidades:
 *  - Estado local de variation toggles (optimistic UI con rollback en error)
 *  - Action `toggleVariation(exerciseId, useVariant)` -> POST /api/v/client/plan/exercise/{id}/toggle-variation
 *  - Cache TTL 60s en memoria de los toggles aplicados (evita doble POST consecutivos)
 *  - Reset hook que se invoca desde auth.js setAuth/clearAuth para evitar leak entre impersonations
 *
 * No fetch del plan completo — eso lo hace PlanViewer.vue padre y se pasa via props.
 *
 * Uso:
 *   const { toggleVariation, isToggling, variationStateFor, resetPlanViewerState } = usePlanViewer();
 */

// Module-level singleton state — compartido entre todos los consumidores
const togglesInFlight = ref({});                 // { [exerciseId]: true } mientras POST in-flight
const togglesError = ref({});                    // { [exerciseId]: message } último error
const localVariantState = ref({});               // { [exerciseId]: bool } override optimistic (pre-server confirmation)
const lastToggleAt = ref({});                    // { [exerciseId]: timestamp_ms } para TTL/dedup

const TTL_MS = 60 * 1000;
let api = null;

function getApi() {
    if (!api) api = useApi();
    return api;
}

async function toggleVariation(exerciseId, useVariant) {
    if (!exerciseId) return false;

    // Dedup: si la última acción para este ejercicio fue hace <1s, ignora
    const last = lastToggleAt.value[exerciseId] ?? 0;
    if (Date.now() - last < 600) return false;
    lastToggleAt.value = { ...lastToggleAt.value, [exerciseId]: Date.now() };

    // Optimistic
    const previous = localVariantState.value[exerciseId];
    localVariantState.value = { ...localVariantState.value, [exerciseId]: !!useVariant };
    togglesInFlight.value = { ...togglesInFlight.value, [exerciseId]: true };
    togglesError.value = { ...togglesError.value, [exerciseId]: null };

    try {
        const response = await getApi().post(
            `/api/v/client/plan/exercise/${exerciseId}/toggle-variation`,
            { use_variant: !!useVariant }
        );
        const serverState = response?.data?.using_variant;
        if (typeof serverState === 'boolean') {
            localVariantState.value = { ...localVariantState.value, [exerciseId]: serverState };
        }
        return true;
    } catch (err) {
        // Rollback
        if (previous === undefined) {
            const next = { ...localVariantState.value };
            delete next[exerciseId];
            localVariantState.value = next;
        } else {
            localVariantState.value = { ...localVariantState.value, [exerciseId]: previous };
        }
        const msg = err?.response?.data?.message || err?.message || 'No se pudo cambiar la variación';
        togglesError.value = { ...togglesError.value, [exerciseId]: msg };
        return false;
    } finally {
        const next = { ...togglesInFlight.value };
        delete next[exerciseId];
        togglesInFlight.value = next;
    }
}

function variationStateFor(exerciseId) {
    return computed(() => localVariantState.value[exerciseId]);
}

function isToggling(exerciseId) {
    return computed(() => !!togglesInFlight.value[exerciseId]);
}

function resetPlanViewerState() {
    togglesInFlight.value = {};
    togglesError.value = {};
    localVariantState.value = {};
    lastToggleAt.value = {};
}

export function usePlanViewer() {
    return {
        toggleVariation,
        variationStateFor,
        isToggling,
        togglesError,
        resetPlanViewerState,
    };
}

// Export aislado para auth.js setAuth/clearAuth reset hooks
export { resetPlanViewerState };
