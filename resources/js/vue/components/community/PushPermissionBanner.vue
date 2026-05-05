<script setup>
import { computed, ref, onMounted } from 'vue';
import { usePushSubscription } from '../../composables/usePushSubscription';
import { useToast } from '../../composables/useToast';

const { permission, request } = usePushSubscription();
const toast = useToast();
const dismissed = ref(false);

const STORAGE_KEY = 'coach_push_dismissed_at';
const DISMISS_DAYS = 7;

const visible = computed(() => {
    if (dismissed.value) return false;
    if (permission.value !== 'default') return false;
    const at = localStorage.getItem(STORAGE_KEY);
    if (at) {
        const ms = Date.now() - parseInt(at, 10);
        if (ms < DISMISS_DAYS * 24 * 60 * 60 * 1000) return false;
    }
    return true;
});

async function activate() {
    try {
        const result = await request();
        if (result === 'granted') {
            toast.success('Notificaciones activadas. Te avisaremos cuando tu equipo rompa PRs.');
            dismissed.value = true;
        } else if (result === 'denied') {
            toast.warn('Notificaciones bloqueadas. Puedes habilitarlas desde la configuración del navegador.');
            dismissed.value = true;
            localStorage.setItem(STORAGE_KEY, String(Date.now()));
        }
    } catch (err) {
        toast.error(err.message || 'No pudimos activar notificaciones.');
    }
}

function dismiss() {
    dismissed.value = true;
    localStorage.setItem(STORAGE_KEY, String(Date.now()));
}

onMounted(() => {
    if (typeof Notification !== 'undefined') {
        permission.value = Notification.permission;
    }
});
</script>

<template>
  <Transition
    enter-active-class="transition-all duration-300 ease-out"
    enter-from-class="opacity-0 -translate-y-2"
    leave-active-class="transition-all duration-200 ease-in"
    leave-to-class="opacity-0 -translate-y-2"
  >
    <div v-if="visible" class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 px-4 py-3 flex items-start gap-3">
      <div class="shrink-0 h-9 w-9 rounded-lg bg-wc-accent/15 flex items-center justify-center">
        <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
        </svg>
      </div>
      <div class="flex-1 min-w-0">
        <p class="text-sm font-semibold text-wc-text">Activa notificaciones</p>
        <p class="text-xs text-wc-text-tertiary mt-0.5">
          Para no perder cuando tu equipo rompa PRs o necesite atención inmediata.
        </p>
      </div>
      <div class="flex items-center gap-2 shrink-0">
        <button @click="dismiss" class="text-xs text-wc-text-tertiary hover:text-wc-text px-2 py-1">
          Más tarde
        </button>
        <button @click="activate" class="text-xs font-semibold text-white bg-wc-accent hover:bg-wc-accent/90 rounded-full px-3 py-1.5">
          Activar ahora
        </button>
      </div>
    </div>
  </Transition>
</template>
