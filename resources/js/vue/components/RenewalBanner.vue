<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { usePlanLock } from '../composables/usePlanLock';

const router = useRouter();
const { isInGrace, daysUntilExpiry, expiresAt, clientId } = usePlanLock();

// Dismiss state — persisted to localStorage for 24h per client+expires_at so the
// banner re-appears automatically the next day even if the user dismissed it
// earlier. Including clientId prevents one user's dismiss from leaking to another
// on a shared device. Keying by expires_at guarantees a fresh banner after renewal.
const DISMISS_KEY_PREFIX = 'wc_renewal_banner_dismissed_until:';
const now = ref(Date.now());
const dismissedAt = ref(null);

function storageKey() {
  const uid = clientId.value ?? 'guest';
  return DISMISS_KEY_PREFIX + uid + ':' + (expiresAt.value || 'no-expiry');
}

function readDismissedUntil() {
  try {
    const v = localStorage.getItem(storageKey());
    return v ? Number(v) : null;
  } catch {
    return null;
  }
}

onMounted(() => {
  dismissedAt.value = readDismissedUntil();
});

const isDismissed = computed(() => {
  if (dismissedAt.value == null) return false;
  return now.value < dismissedAt.value;
});

const shouldShow = computed(() => isInGrace.value && !isDismissed.value);

const message = computed(() => {
  const d = daysUntilExpiry.value;
  if (d == null) return 'Tu plan esta por expirar';
  if (d <= 0) return 'Tu plan expira hoy';
  if (d === 1) return 'Tu plan expira en 1 dia';
  return `Tu plan expira en ${d} dias`;
});

function dismiss() {
  const until = Date.now() + 24 * 60 * 60 * 1000; // 24h
  try {
    localStorage.setItem(storageKey(), String(until));
  } catch {
    // Storage unavailable — still hide for this session.
  }
  dismissedAt.value = until;
}

function goToRenew() {
  router.push('/renovar');
}
</script>

<template>
  <Transition name="slide-down">
    <div
      v-if="shouldShow"
      role="status"
      aria-live="polite"
      class="relative z-[80] border-b border-amber-500/40 bg-gradient-to-r from-amber-500/95 to-orange-500/95 text-black shadow-[0_2px_8px_-2px_rgba(0,0,0,0.25)] backdrop-blur"
    >
      <div class="mx-auto flex w-full max-w-screen-2xl items-center gap-3 px-4 py-2 text-sm font-medium sm:px-6">
        <!-- Warning icon -->
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
             class="h-4 w-4 shrink-0" aria-hidden="true">
          <path d="M12 9v4" />
          <path d="M10.3 3.86a2 2 0 0 1 3.4 0l8.56 14.5A2 2 0 0 1 20.56 21H3.44a2 2 0 0 1-1.7-2.64Z" />
          <path d="M12 17h.01" />
        </svg>

        <!-- Message + inline CTA -->
        <div class="flex flex-1 flex-wrap items-center gap-x-2 gap-y-1">
          <span class="font-semibold">{{ message }}</span>
          <span class="opacity-60" aria-hidden="true">&middot;</span>
          <button
            type="button"
            @click="goToRenew"
            class="inline-flex items-center gap-1 rounded-md bg-black/15 px-2.5 py-0.5 text-xs font-semibold transition-colors hover:bg-black/25 focus:outline-none focus:ring-2 focus:ring-black/30"
          >
            Renovar ahora con 10% OFF
            <span aria-hidden="true">&rarr;</span>
          </button>
        </div>

        <!-- Renew button (desktop, separate CTA so the mobile inline link stays compact) -->
        <button
          type="button"
          @click="goToRenew"
          class="hidden shrink-0 rounded-lg bg-black px-3 py-1 text-xs font-semibold text-white transition-colors hover:bg-black/80 sm:inline-flex"
        >
          Renovar
        </button>

        <!-- Dismiss -->
        <button
          type="button"
          @click="dismiss"
          class="shrink-0 rounded-md p-1 text-black/70 transition-colors hover:bg-black/10 hover:text-black focus:outline-none focus:ring-2 focus:ring-black/30"
          aria-label="Cerrar aviso"
          title="Cerrar por 24 horas"
        >
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
               class="h-4 w-4" aria-hidden="true">
            <path d="M18 6 6 18" />
            <path d="m6 6 12 12" />
          </svg>
        </button>
      </div>
    </div>
  </Transition>
</template>

<style scoped>
.slide-down-enter-active,
.slide-down-leave-active {
  transition: transform 0.25s ease, opacity 0.25s ease;
}
.slide-down-enter-from,
.slide-down-leave-to {
  transform: translateY(-100%);
  opacity: 0;
}
</style>
