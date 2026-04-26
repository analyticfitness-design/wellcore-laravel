<script setup>
import { onMounted, ref, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useContractGate } from '../../composables/useContractGate';
import { useAuthStore } from '../../stores/auth';

const router    = useRouter();
const authStore = useAuthStore();
const gate      = useContractGate();

const accepted           = ref(false);
const showDeclineConfirm = ref(false);

const acceptDisabled = computed(() =>
    !gate.scrollCompleted.value || !accepted.value || gate.submitting.value
);

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
    await gate.refresh();
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
            <!-- Header — compacto en mobile -->
            <header class="border-b border-wc-border bg-wc-bg-secondary px-4 py-2 sm:px-6 sm:py-3">
                <h2 id="contract-gate-title" class="font-display text-base uppercase tracking-wider text-wc-text sm:text-lg">
                    Acuerdo de Alianza · WellCore
                </h2>
                <p class="mt-0.5 text-[10px] text-wc-text/60 sm:mt-1 sm:text-xs">
                    v{{ gate.version.value || '1.0' }} · Lee el acuerdo y confirma al terminar
                </p>
            </header>

            <!-- Iframe — solo visualización, sin sandbox -->
            <div class="flex-1 overflow-hidden bg-black p-1.5 sm:p-4">
                <iframe
                    :srcdoc="gate.html.value"
                    class="h-full w-full rounded-md border border-wc-border bg-wc-bg"
                    title="Contrato del coach"
                ></iframe>
            </div>

            <!-- Footer — compacto en mobile -->
            <footer class="border-t border-wc-border bg-wc-bg-secondary px-4 py-2 sm:px-6 sm:py-4">
                <div class="mx-auto max-w-3xl space-y-2 sm:space-y-3">

                    <!-- Botón de confirmación en el padre — sin depender del iframe -->
                    <div v-if="!gate.scrollCompleted.value" class="flex justify-center">
                        <button
                            type="button"
                            @click="gate.markScrollComplete()"
                            class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2 text-xs font-medium text-wc-text transition-colors hover:border-wc-accent hover:text-wc-accent active:scale-95 sm:w-auto sm:px-5 sm:py-2.5 sm:text-sm"
                        >
                            He leído el acuerdo completo →
                        </button>
                    </div>

                    <label class="flex items-center gap-2.5 text-xs text-wc-text sm:text-sm">
                        <input
                            type="checkbox"
                            v-model="accepted"
                            :disabled="!gate.scrollCompleted.value || gate.submitting.value"
                            class="h-4 w-4 shrink-0 rounded border-wc-border bg-wc-bg-tertiary accent-wc-accent disabled:opacity-40"
                        />
                        <span :class="gate.scrollCompleted.value ? 'text-wc-text' : 'text-wc-text/50'">
                            He leído y acepto el Acuerdo de Alianza Comercial v{{ gate.version.value || '1.0' }}.
                        </span>
                    </label>

                    <p v-if="gate.error.value" class="text-xs text-red-500">{{ gate.error.value }}</p>

                    <div class="flex flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-between sm:gap-3">
                        <button
                            type="button"
                            @click="openDeclineConfirm"
                            :disabled="gate.submitting.value"
                            class="text-center text-xs text-wc-text/40 underline hover:text-wc-text disabled:opacity-50"
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
