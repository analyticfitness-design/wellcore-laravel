import { ref, computed, onScopeDispose } from 'vue';
import { useApi } from './useApi';

/**
 * useNutritionPlan — Singleton composable que carga el plan completo del cliente
 * desde GET /api/v/client/plan y expone derivados focalizados en nutrición.
 *
 * Comparte estado entre componentes (singleton module-level) — no re-fetchea si
 * el plan ya está cargado salvo que se llame fetchPlan(true).
 *
 * Uso:
 *   const { plan, nutritionPlan, loading, error, fetchPlan,
 *           hasPlan, planType, canAccessNutricion } = useNutritionPlan();
 *   await fetchPlan();
 */

// ── Singleton state ──────────────────────────────────────────────────────────
const plan = ref(null);            // payload completo del endpoint
const loading = ref(false);
const error = ref(null);

// Non-reactive handles
let api = null;
let inflight = null;               // dedupe llamadas concurrentes
let abortCtl = null;

// planType permitidos para acceder a la pestaña de Nutrición.
// Replicado de PlanViewer.vue (línea 382) — incluye nutricion_solo y excluye trial/entreno_solo/basico.
const NUTRICION_PLAN_TYPES = ['esencial', 'metodo', 'elite', 'presencial', 'rise', 'nutricion_solo'];

function getApi() {
  if (!api) api = useApi();
  return api;
}

async function fetchPlan(force = false) {
  // Cache: si ya hay plan y no es forzado, no re-fetchear
  if (!force && plan.value) return plan.value;

  // Dedupe concurrent calls
  if (inflight) return inflight;

  loading.value = true;
  error.value = null;

  // Cancelar request previo si existe
  if (abortCtl) {
    try { abortCtl.abort(); } catch (_) { /* noop */ }
  }
  abortCtl = new AbortController();

  inflight = (async () => {
    try {
      const response = await getApi().get('/api/v/client/plan', {
        signal: abortCtl.signal,
      });
      plan.value = response.data;
      return plan.value;
    } catch (err) {
      // Ignorar abortos silenciosamente
      if (err?.name === 'CanceledError' || err?.code === 'ERR_CANCELED' || err?.name === 'AbortError') {
        return null;
      }
      error.value = 'No pudimos cargar tu plan, intenta de nuevo.';
      plan.value = null;
      throw err;
    } finally {
      loading.value = false;
      inflight = null;
    }
  })();

  return inflight;
}

function cancel() {
  if (abortCtl) {
    try { abortCtl.abort(); } catch (_) { /* noop */ }
    abortCtl = null;
  }
  inflight = null;
  loading.value = false;
}

// ── Computed derivados ───────────────────────────────────────────────────────
const nutritionPlan = computed(() => plan.value?.nutrition_plan || null);

const hasPlan = computed(() => !!plan.value && !!nutritionPlan.value);

const planType = computed(() => {
  const t = plan.value?.plan_type || plan.value?.client?.plan || 'basico';
  return String(t).toLowerCase();
});

const canAccessNutricion = computed(() => NUTRICION_PLAN_TYPES.includes(planType.value));

export function useNutritionPlan() {
  // Cancelar fetch en curso si el scope que llamó este composable se desmonta
  // y aún no terminó (otros consumers pueden seguir usando el state cacheado).
  onScopeDispose(() => {
    // No abortamos por defecto — el state es singleton y otros componentes
    // pueden estar esperando. Solo exponemos cancel() para uso explícito.
  });

  return {
    plan,
    nutritionPlan,
    loading,
    error,
    fetchPlan,
    cancel,
    hasPlan,
    planType,
    canAccessNutricion,
  };
}
