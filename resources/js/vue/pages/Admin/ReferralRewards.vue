<script setup>
import { onMounted, onBeforeUnmount, ref } from 'vue';
import AdminLayout         from '../../layouts/AdminLayout.vue';
import AdminGreeting       from '../../components/admin/dashboard/AdminGreeting.vue';
import AdminReferralKPIs   from '../../components/admin/referrals/AdminReferralKPIs.vue';
import AdminReferralsTable from '../../components/admin/referrals/AdminReferralsTable.vue';
import AdminReferralsTopList   from '../../components/admin/referrals/AdminReferralsTopList.vue';
import AdminReferralPayoutModal from '../../components/admin/referrals/AdminReferralPayoutModal.vue';
import { useAdminReferralsStore } from '../../stores/adminReferrals';

const store        = useAdminReferralsStore();
const payoutTarget = ref(null);

onMounted(() => {
  store.fetchReferrals();
  store.startPolling(60_000);
});

onBeforeUnmount(() => store.stopPolling());

const onMarkPaid = (referral) => { payoutTarget.value = referral; };

const onConfirmPayout = async ({ method, reference }) => {
  const ok = await store.markPaid(payoutTarget.value.id, method, reference);
  if (ok) payoutTarget.value = null;
};

const onExpire = async (id) => { await store.expire(id); };
</script>

<template>
  <AdminLayout>
    <div class="referrals-page">

      <!-- Header -->
      <AdminGreeting
        :greeting="'Referidos'"
        :critical-alerts="store.qualified"
        :pending-tickets="0"
        :review-tickets="0"
      />

      <!-- KPIs hero -->
      <AdminReferralKPIs
        :total-referidos="store.totalReferidos"
        :qualified="store.qualified"
        :paid="store.paid"
        :roi="store.roi"
        :loading="store.loading"
      />

      <!-- Error -->
      <div v-if="store.error" class="page-error" role="alert">
        {{ store.error }}
      </div>

      <!-- Secondary grid: tabla + top referidores -->
      <div class="page-secondary">

        <!-- Main: tabla con filtros -->
        <AdminReferralsTable
          :referrals="store.referrals"
          :loading="store.loading"
          :total-pages="store.totalPages"
          :page="store.filters.page"
          :filters="store.filters"
          @filter="(key, val) => store.setFilter(key, val)"
          @page="(p) => store.setPage(p)"
          @mark-paid="onMarkPaid"
          @expire="onExpire"
        />

        <!-- Sidebar: top referidores -->
        <AdminReferralsTopList :referidores="store.topReferidores" />

      </div>

    </div>

    <!-- Payout modal (Teleport to body en el componente) -->
    <AdminReferralPayoutModal
      :referral="payoutTarget"
      :loading="store.payoutLoading"
      @confirm="onConfirmPayout"
      @cancel="payoutTarget = null"
    />

  </AdminLayout>
</template>

<style scoped>
.referrals-page {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.page-secondary {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
}
@media (min-width: 1024px) {
    .page-secondary {
        grid-template-columns: 2fr 1fr;
        gap: 16px;
    }
}

.page-error {
    padding: 12px 16px;
    border-radius: 10px;
    border: 1px solid var(--color-wc-red-soft);
    background: var(--color-wc-red-soft);
    font-family: var(--font-sans);
    font-size: 12px;
    color: var(--color-wc-red-text);
}
</style>
