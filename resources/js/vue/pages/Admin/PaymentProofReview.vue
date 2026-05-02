<script setup>
import { onMounted, onBeforeUnmount, computed } from 'vue';
import AdminLayout from '../../layouts/AdminLayout.vue';
import AdminGreeting from '../../components/admin/dashboard/AdminGreeting.vue';
import AdminProofKPIs from '../../components/admin/payment-proofs/AdminProofKPIs.vue';
import AdminProofsQueue from '../../components/admin/payment-proofs/AdminProofsQueue.vue';
import AdminProofReviewDrawer from '../../components/admin/payment-proofs/AdminProofReviewDrawer.vue';
import { useAdminProofsStore } from '../../stores/adminProofs';

const store = useAdminProofsStore();

const greeting = computed(() => {
    const total = store.kpis.pending;
    if (total === 0) return 'COMPROBANTES — COLA LIMPIA';
    if (total === 1) return '1 COMPROBANTE PENDIENTE';
    return `${total} COMPROBANTES PENDIENTES`;
});

onMounted(() => {
    store.fetchProofs();
    store.startPolling(30000);
});

onBeforeUnmount(() => {
    store.stopPolling();
});
</script>

<template>
  <AdminLayout>
    <div class="proofs-page">
      <AdminGreeting
        :greeting="greeting"
        :critical-alerts="store.kpis.pending > 5 ? store.kpis.pending : 0"
        :pending-tickets="0"
        :review-tickets="0"
      />

      <p class="proofs-eyebrow">
        REVISIÓN MANUAL DE PAGOS · POLLING 30s
        <span v-if="store.lastRefresh" class="proofs-refresh">
          · Última actualización hace {{ store.secondsSinceRefresh ?? 0 }}s
        </span>
      </p>

      <AdminProofKPIs :kpis="store.kpis" />

      <AdminProofsQueue />
    </div>

    <AdminProofReviewDrawer />
  </AdminLayout>
</template>

<style scoped>
.proofs-page {
    display: flex;
    flex-direction: column;
    gap: 16px;
}
@media (min-width: 1024px) {
    .proofs-page { gap: 22px; }
}

.proofs-eyebrow {
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.8px;
    text-transform: uppercase;
    color: var(--c-text-3);
    margin: -8px 0 0;
}
.proofs-refresh { color: var(--c-text-3); opacity: 0.7; }

@media (min-width: 1024px) {
    .proofs-eyebrow { margin-top: -10px; }
}
</style>
