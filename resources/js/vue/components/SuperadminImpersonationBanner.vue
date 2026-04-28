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

async function handleEnd() {
    if (ending.value) return;
    ending.value = true;
    try {
        const redirect = await authStore.endImpersonation();
        refresh();
        window.location.href = redirect || '/admin/coaches';
    } catch (e) {
        ending.value = false;
        alert('No se pudo cerrar la impersonificación. Recargando…');
        window.location.href = '/admin/coaches';
    }
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
        class="fixed top-0 left-0 right-0 z-[100] flex items-center justify-center gap-3 px-4 py-2 text-xs sm:text-sm font-medium text-white shadow-lg transition-colors"
        :class="isExpiringSoon ? 'bg-amber-500 animate-pulse' : 'bg-wc-accent'"
    >
        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
        </svg>

        <span>
            {{ bannerText }}
            <span v-if="remainingFormatted" class="ml-2 font-mono opacity-80">· {{ remainingFormatted }}</span>
        </span>

        <button
            @click="handleEnd"
            :disabled="ending"
            class="ml-2 inline-flex items-center gap-1 rounded-md bg-black/25 px-3 py-1 text-xs font-semibold hover:bg-black/40 transition-colors disabled:opacity-60"
        >
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            {{ ending ? 'Volviendo…' : backLabel }}
        </button>
    </div>
</template>
