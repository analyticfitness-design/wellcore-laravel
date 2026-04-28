<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useImpersonation } from '../composables/useImpersonation';

const authStore = useAuthStore();
const { chain, topOfChain, rootUserName, refresh } = useImpersonation();

const ending = ref(false);
const expiresAt = ref(null);
const remainingSeconds = ref(null);

let countdownInterval = null;

function refreshExpiry() {
    const top = topOfChain.value;
    if (!top) {
        expiresAt.value = null;
        remainingSeconds.value = null;
        return;
    }
    if (!expiresAt.value && top.expires_at) {
        expiresAt.value = new Date(top.expires_at);
    }
    if (expiresAt.value) {
        remainingSeconds.value = Math.max(0, Math.floor((expiresAt.value - Date.now()) / 1000));
    }
}

const remainingFormatted = computed(() => {
    if (remainingSeconds.value === null) return '';
    const m = Math.floor(remainingSeconds.value / 60).toString().padStart(2, '0');
    const s = (remainingSeconds.value % 60).toString().padStart(2, '0');
    return `${m}:${s}`;
});

const isExpiringSoon = computed(() =>
    remainingSeconds.value !== null && remainingSeconds.value <= 300
);

const bannerText = computed(() => {
    const top = topOfChain.value;
    if (!top) return '';
    if (top.target_type === 'admin') {
        return `Viendo como ${top.target_name} (coach)`;
    }
    if (top.via_actor_name) {
        return `Viendo como ${top.target_name} (cliente) vía ${top.via_actor_name}`;
    }
    return `Viendo como ${top.target_name} (cliente)`;
});

const backLabel = computed(() =>
    rootUserName.value ? `Volver al panel admin (${rootUserName.value})` : 'Volver al panel admin'
);
// Compact label for mobile: just "Volver" + optional name
const backLabelMobile = computed(() => 'Volver al admin');

async function handleEnd() {
    if (ending.value) return;
    ending.value = true;
    try {
        const redirect = await authStore.endImpersonation();
        refresh();
        window.location.href = redirect || '/admin/coaches';
    } catch (e) {
        // Last-resort: even if everything fails, clear local state and go to login.
        ['wc_token', 'wc_user_type', 'wc_user_id', 'wc_user_name', 'wc_user_portal',
         'wc_root_token', 'wc_root_user_id', 'wc_root_user_name',
         'wc_impersonation_chain', 'wc_admin_token', 'wc_impersonating',
         'wc_impersonating_by_coach']
            .forEach(k => localStorage.removeItem(k));
        window.location.href = '/login';
    }
}

// Emergency escape: long-press the banner (1.5s) clears all impersonation state
// and sends the user to /login. Useful if for any reason the normal "Volver"
// flow leaves them stuck in a half-impersonated state.
let pressTimer = null;
function startPress() {
    pressTimer = setTimeout(() => {
        if (!confirm('¿Salida de emergencia? Se cerrará la sesión y tendrás que loguearte de nuevo.')) return;
        ['wc_token', 'wc_user_type', 'wc_user_id', 'wc_user_name', 'wc_user_portal',
         'wc_root_token', 'wc_root_user_id', 'wc_root_user_name',
         'wc_impersonation_chain', 'wc_admin_token', 'wc_impersonating',
         'wc_impersonating_by_coach']
            .forEach(k => localStorage.removeItem(k));
        window.location.href = '/login';
    }, 1500);
}
function cancelPress() {
    if (pressTimer) { clearTimeout(pressTimer); pressTimer = null; }
}

onMounted(() => {
    refresh();
    refreshExpiry();
    countdownInterval = setInterval(refreshExpiry, 1000);
    window.addEventListener('storage', refresh);
});

onUnmounted(() => {
    if (countdownInterval) clearInterval(countdownInterval);
    window.removeEventListener('storage', refresh);
});
</script>

<template>
    <div
        v-if="chain.length > 0 && rootUserName"
        class="sticky top-0 left-0 right-0 z-[100] flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-white shadow-lg transition-colors sm:justify-center sm:gap-3 sm:px-4 sm:py-2 sm:text-sm"
        :class="isExpiringSoon ? 'bg-amber-500 animate-pulse' : 'bg-wc-accent'"
        :title="'Mantén presionado 1.5s para salida de emergencia'"
        @mousedown="startPress"
        @mouseup="cancelPress"
        @mouseleave="cancelPress"
        @touchstart="startPress"
        @touchend="cancelPress"
        @touchcancel="cancelPress"
    >
        <svg class="hidden h-4 w-4 shrink-0 sm:block" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
        </svg>

        <span class="min-w-0 flex-1 truncate sm:flex-initial">
            {{ bannerText }}
            <span v-if="remainingFormatted" class="ml-1 font-mono opacity-80">· {{ remainingFormatted }}</span>
        </span>

        <button
            @click="handleEnd"
            :disabled="ending"
            class="ml-2 inline-flex shrink-0 items-center gap-1 rounded-md bg-black/25 px-2 py-1 text-[11px] font-semibold hover:bg-black/40 transition-colors disabled:opacity-60 sm:px-3 sm:text-xs"
        >
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            <span class="hidden sm:inline">{{ ending ? 'Volviendo…' : backLabel }}</span>
            <span class="sm:hidden">{{ ending ? 'Volviendo…' : backLabelMobile }}</span>
        </button>
    </div>
</template>
