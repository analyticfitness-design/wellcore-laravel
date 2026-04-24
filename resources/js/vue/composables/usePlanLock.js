import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useApi } from './useApi';

/**
 * usePlanLock — Global singleton composable that polls the plan-status endpoint
 * and exposes reactive flags to lock UI when a client's plan has expired
 * or is inside the grace window.
 *
 * Endpoint: GET /api/v/client/plan-status
 * Response shape:
 *   { has_plan: bool, is_locked: bool, is_in_grace: bool,
 *     days_until_expiry: number|null, expires_at: string|null, plan_type: string|null }
 *
 * Usage:
 *   import { usePlanLock } from '../../composables/usePlanLock';
 *   const { isLocked, isInGrace, daysUntilExpiry, expiresAt, planType } = usePlanLock();
 *
 * Poll interval: 5 minutes. Cleanly stops polling when the last consumer unmounts.
 */

// ── Module-level singleton state ─────────────────────────────────────────────
// All consumers share the same reactive state and the same polling interval,
// preventing duplicate network calls when multiple components mount simultaneously.

const status = ref(null);        // Full API response or null if never fetched
const loaded = ref(false);       // true after first (successful or failed) fetch
const fetchError = ref(null);    // last error (non-fatal — UI ignores it)

// Non-reactive handles — module-scoped so refs don't proxy timers/api.
let pollTimer = null;
let consumers = 0;
let inflight = null;             // dedupe concurrent fetchStatus() calls
let api = null;                  // lazily created (Pinia must be initialized first)

const POLL_INTERVAL_MS = 5 * 60 * 1000; // 5 minutes

function getApi() {
  if (!api) api = useApi();
  return api;
}

export async function fetchPlanStatus() {
  if (inflight) return inflight;
  inflight = (async () => {
    try {
      const response = await getApi().get('/api/v/client/plan-status');
      status.value = response.data ?? null;
      fetchError.value = null;
    } catch (err) {
      // Silent fail — absence of status should never break the UI.
      // If the endpoint 404s or the user lost session, treat as "no plan info".
      fetchError.value = err;
      // Leave `status` as-is (last known good) on transient errors so we don't
      // flicker the LockOverlay closed on a single network hiccup.
      if (!status.value) {
        status.value = { has_plan: false, is_locked: false, is_in_grace: false };
      }
    } finally {
      loaded.value = true;
      inflight = null;
    }
  })();
  return inflight;
}

function startPolling() {
  if (pollTimer) return;
  pollTimer = setInterval(() => {
    fetchPlanStatus().catch(() => {});
  }, POLL_INTERVAL_MS);
}

function stopPolling() {
  if (pollTimer) {
    clearInterval(pollTimer);
    pollTimer = null;
  }
}

export function usePlanLock() {
  // Ref-count consumers so we only keep one shared poller alive.
  onMounted(() => {
    consumers++;
    // Kick off the first fetch the moment anyone asks for the status.
    if (!loaded.value && !inflight) {
      fetchPlanStatus().catch(() => {});
    }
    startPolling();
  });

  onBeforeUnmount(() => {
    consumers = Math.max(0, consumers - 1);
    if (consumers === 0) stopPolling();
  });

  const hasPlan = computed(() => !!status.value?.has_plan);
  const isLocked = computed(() => !!status.value?.is_locked);
  const isInGrace = computed(() => !!status.value?.is_in_grace);
  const daysUntilExpiry = computed(() => {
    const n = status.value?.days_until_expiry;
    return typeof n === 'number' ? n : null;
  });
  const expiresAt = computed(() => status.value?.expires_at ?? null);
  const planType = computed(() => status.value?.plan_type ?? null);

  return {
    // raw + meta
    status,
    loaded,
    // computed flags
    hasPlan,
    isLocked,
    isInGrace,
    daysUntilExpiry,
    expiresAt,
    planType,
    // actions
    fetchStatus: fetchPlanStatus,
    refresh: fetchPlanStatus,
  };
}
