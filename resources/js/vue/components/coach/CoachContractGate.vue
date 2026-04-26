<script setup>
import { onMounted, onBeforeUnmount, ref, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useContractGate } from '../../composables/useContractGate';
import { useAuthStore } from '../../stores/auth';

const router    = useRouter();
const authStore = useAuthStore();
const gate      = useContractGate();

const accepted                = ref(false);
const showDeclineConfirm      = ref(false);
const messageListenerAttached = ref(false);
const iframeEl                = ref(null);

const acceptDisabled = computed(() =>
    !gate.scrollCompleted.value || !accepted.value || gate.submitting.value
);

function handleMessage(e) {
    // srcdoc iframes have an opaque origin (e.origin === 'null' string), so
    // checking e.origin === window.location.origin would always fail for them.
    // Validating e.source against the known contentWindow reference is
    // sufficient — no external window or extension can forge this reference.
    if (
        e?.data?.type === 'wc-contract-end' &&
        iframeEl.value &&
        e.source === iframeEl.value.contentWindow
    ) {
        gate.markScrollComplete();
    }
}

async function handleAccept() {
    const ok = await gate.accept();
    if (ok) {
        window.location.reload();
    }
}

function openDeclineConfirm() {
    showDeclineConfirm.value = true;
}

async function handleDecline() {
    const ok = await gate.decline();
    if (ok) {
        try { await authStore.logout(); } catch (_) {}
        router.push({ path: '/login', query: { reason: 'contract_declined' } });
    }
}

onMounted(async () => {
    if (!messageListenerAttached.value) {
        window.addEventListener('message', handleMessage);
        messageListenerAttached.value = true;
    }
    await gate.refresh();
});

onBeforeUnmount(() => {
    if (messageListenerAttached.value) {
        window.removeEventListener('message', handleMessage);
        messageListenerAttached.value = false;
    }
});

watch(() => gate.requires.value, (val) => {
    if (!val) {
        accepted.value = false;
        showDeclineConfirm.value = false;
    }
});
</script>

<template>
    <Teleport to="body">
        <div
            v-if="gate.requires.value"
            class="fixed inset-0 z-[200] flex flex-col bg-wc-bg/95 backdrop-blur-sm"
            role="dialog"
            aria-modal="true"
            aria-labelledby="contract-gate-title"
        >
            <!-- Header -->
            <header class="border-b border-wc-border bg-wc-bg-secondary px-4 py-3 sm:px-6">
                <h2 id="contract-gate-title" class="font-display text-lg uppercase tracking-wider text-wc-text">
                    Acuerdo de Alianza Comercial · WellCore Fitness
                </h2>
                <p class="mt-1 text-xs text-wc-text/60">
                    Versión {{ gate.version.value || '1.0' }} · Lee hasta el final para activar la aceptación
                </p>
            </header>

            <!-- Iframe with contract HTML -->
            <div class="flex-1 overflow-hidden bg-black p-2 sm:p-4">
                <iframe
                    ref="iframeEl"
                    sandbox="allow-scripts"
                    :srcdoc="gate.html.value"
                    class="h-full w-full rounded-md border border-wc-border bg-wc-bg"
                    title="Contrato del coach"
                ></iframe>
            </div>

            <!-- Footer with controls -->
            <footer class="border-t border-wc-border bg-wc-bg-secondary px-4 py-4 sm:px-6">
                <div class="mx-auto max-w-3xl space-y-3">
                    <p
                        v-if="!gate.scrollCompleted.value"
                        class="text-xs text-wc-text/60"
                    >
                        Lee el documento hasta el final para activar la aceptación.
                    </p>

                    <label class="flex items-start gap-3 text-sm text-wc-text">
                        <input
                            type="checkbox"
                            v-model="accepted"
                            :disabled="!gate.scrollCompleted.value || gate.submitting.value"
                            class="mt-0.5 h-4 w-4 rounded border-wc-border bg-wc-bg-tertiary accent-wc-accent disabled:opacity-40"
                        />
                        <span :class="gate.scrollCompleted.value ? 'text-wc-text' : 'text-wc-text/50'">
                            He leído y acepto el Acuerdo de Alianza Comercial v{{ gate.version.value || '1.0' }}.
                        </span>
                    </label>

                    <p v-if="gate.error.value" class="text-xs text-red-500">{{ gate.error.value }}</p>

                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <button
                            type="button"
                            @click="openDeclineConfirm"
                            :disabled="gate.submitting.value"
                            class="text-xs text-wc-text/50 underline hover:text-wc-text disabled:opacity-50"
                        >
                            Rechazar y dar de baja mi cuenta
                        </button>

                        <button
                            type="button"
                            @click="handleAccept"
                            :disabled="acceptDisabled"
                            class="rounded-lg bg-wc-accent px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {{ gate.submitting.value ? 'Enviando...' : 'Aceptar y continuar' }}
                        </button>
                    </div>
                </div>
            </footer>

            <!-- Decline confirmation dialog -->
            <div
                v-if="showDeclineConfirm"
                class="fixed inset-0 z-[210] flex items-center justify-center bg-black/70 p-4"
            >
                <div class="w-full max-w-md rounded-xl border border-wc-border bg-wc-bg-secondary p-5 shadow-2xl">
                    <h3 class="font-display text-base uppercase text-wc-text">¿Rechazar el acuerdo?</h3>
                    <p class="mt-2 text-sm text-wc-text/70">
                        Esta acción es <strong class="text-wc-text">definitiva</strong>. Tu cuenta quedará inactiva y no podrás recuperarla sin contactar al administrador.
                    </p>
                    <div class="mt-4 flex justify-end gap-3">
                        <button
                            type="button"
                            @click="showDeclineConfirm = false"
                            :disabled="gate.submitting.value"
                            class="rounded-lg border border-wc-border px-4 py-2 text-sm text-wc-text hover:bg-wc-bg-tertiary"
                        >
                            Volver al acuerdo
                        </button>
                        <button
                            type="button"
                            @click="handleDecline"
                            :disabled="gate.submitting.value"
                            class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-50"
                        >
                            Sí, rechazar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
