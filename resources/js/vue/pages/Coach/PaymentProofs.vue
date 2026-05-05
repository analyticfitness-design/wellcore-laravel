<script setup lang="ts">
import { ref } from 'vue';
import CoachLayout from '../../layouts/CoachLayout.vue';
import PaymentProofUploader from '../../components/coach/PaymentProofUploader.vue';
import PaymentProofList from '../../components/coach/PaymentProofList.vue';

const proofListRef = ref<InstanceType<typeof PaymentProofList> | null>(null);

function refreshProofList() {
    proofListRef.value?.reload();
}
</script>

<template>
    <CoachLayout>
        <div class="mx-auto max-w-2xl space-y-6 px-4 py-6 sm:px-6 anim-entry anim-entry-2">
            <div>
                <h1 class="font-display text-2xl uppercase tracking-wider text-wc-text">Comprobantes</h1>
                <p class="mt-1 text-sm text-wc-text/60">Sube y revisa los comprobantes de pago de tus clientes.</p>
            </div>

            <PaymentProofUploader @submitted="refreshProofList" />
            <PaymentProofList ref="proofListRef" />
        </div>
    </CoachLayout>
</template>
