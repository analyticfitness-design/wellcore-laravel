<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { useHaptics } from '../../composables/useHaptics';

/**
 * InstallPrompt — banner "Instalar WellCore" (PWA install prompt).
 *
 * Reglas:
 *   - Solo aparece si el browser dispara `beforeinstallprompt` (Chrome/Edge).
 *   - Solo después de 2+ visitas al dashboard (counter en localStorage).
 *   - Dismiss → cooldown 30 días (no volver a mostrar).
 *   - Install success → no volver a mostrar nunca.
 *
 * iOS no soporta beforeinstallprompt. Safari usa "Add to Home Screen" nativo.
 * Para iOS podríamos mostrar un tooltip diferente, pero por ahora no.
 */

const STORAGE_VISITS = 'wc_pwa_visits';
const STORAGE_DISMISSED = 'wc_pwa_dismissed_until';
const STORAGE_INSTALLED = 'wc_pwa_installed';

const haptics = useHaptics();
const deferredPrompt = ref(null);
const visible = ref(false);

function shouldShow() {
    // No mostrar si ya está instalado
    if (localStorage.getItem(STORAGE_INSTALLED) === 'true') return false;

    // No mostrar si está dismissed y aún en cooldown
    const dismissedUntil = Number(localStorage.getItem(STORAGE_DISMISSED) || 0);
    if (dismissedUntil > Date.now()) return false;

    // Incrementar visitas
    const visits = Number(localStorage.getItem(STORAGE_VISITS) || 0) + 1;
    localStorage.setItem(STORAGE_VISITS, String(visits));

    // Solo mostrar a partir de la 2da visita (primera es discovery)
    return visits >= 2;
}

function onBeforeInstallPrompt(event) {
    event.preventDefault();
    deferredPrompt.value = event;
    if (shouldShow()) {
        visible.value = true;
    }
}

function onAppInstalled() {
    localStorage.setItem(STORAGE_INSTALLED, 'true');
    visible.value = false;
    deferredPrompt.value = null;
}

async function handleInstall() {
    if (!deferredPrompt.value) return;
    haptics.light();
    deferredPrompt.value.prompt();
    const { outcome } = await deferredPrompt.value.userChoice;
    if (outcome === 'accepted') {
        localStorage.setItem(STORAGE_INSTALLED, 'true');
    }
    deferredPrompt.value = null;
    visible.value = false;
}

function handleDismiss() {
    haptics.light();
    const cooldown = 30 * 24 * 60 * 60 * 1000; // 30 días
    localStorage.setItem(STORAGE_DISMISSED, String(Date.now() + cooldown));
    visible.value = false;
}

onMounted(() => {
    window.addEventListener('beforeinstallprompt', onBeforeInstallPrompt);
    window.addEventListener('appinstalled', onAppInstalled);
});

onBeforeUnmount(() => {
    window.removeEventListener('beforeinstallprompt', onBeforeInstallPrompt);
    window.removeEventListener('appinstalled', onAppInstalled);
});
</script>

<template>
  <Transition
    enter-active-class="transition-all duration-300 ease-out"
    enter-from-class="opacity-0 translate-y-4"
    enter-to-class="opacity-100 translate-y-0"
    leave-active-class="transition-all duration-200 ease-in"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0 translate-y-4"
  >
    <div
      v-if="visible"
      class="fixed z-40 flex items-center gap-3 rounded-2xl border border-wc-border bg-wc-bg-secondary p-4 shadow-2xl lg:bottom-6 lg:left-6 lg:max-w-sm"
      :style="{
        left: 'calc(1rem + env(safe-area-inset-left))',
        right: 'calc(1rem + env(safe-area-inset-right))',
        bottom: 'calc(5.5rem + env(safe-area-inset-bottom))'
      }"
      role="dialog"
      aria-label="Instalar WellCore"
    >
      <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-wc-accent/15">
        <svg class="h-6 w-6 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
        </svg>
      </div>
      <div class="min-w-0 flex-1">
        <p class="text-sm font-semibold text-wc-text">Instalar WellCore</p>
        <p class="text-xs text-wc-text-secondary">Acceso rápido desde tu pantalla de inicio</p>
      </div>
      <div class="flex shrink-0 items-center gap-2">
        <button
          type="button"
          @click="handleDismiss"
          class="rounded-lg px-3 py-1.5 text-xs font-medium text-wc-text-secondary hover:text-wc-text"
          aria-label="Descartar"
        >
          Después
        </button>
        <button
          type="button"
          @click="handleInstall"
          class="rounded-lg bg-wc-accent px-3 py-1.5 text-xs font-semibold text-white shadow-md hover:bg-wc-accent-hover active:scale-95 transition-transform"
        >
          Instalar
        </button>
      </div>
    </div>
  </Transition>
</template>
