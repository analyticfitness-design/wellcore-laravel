<script setup>
import { computed } from 'vue';
import WcAdminFeedGroup from '../../ui/wellcore-admin/WcAdminFeedGroup.vue';
import WcAdminFeedRow from '../../ui/wellcore-admin/WcAdminFeedRow.vue';

const props = defineProps({
  // [{ buyerName, plan, amount, method, timeAgo, ... }, ...]
  payments: { type: Array, default: () => [] },
  // [{ nombre, email, plan, status, timeAgo, id, ... }, ...]
  inscriptions: { type: Array, default: () => [] },
  limit: { type: Number, default: 5 },
});

const paymentsList = computed(() => props.payments.slice(0, props.limit));
const inscriptionsList = computed(() => props.inscriptions.slice(0, 3));
</script>

<template>
  <!-- MOBILE: section + 2 feed-groups stacked -->
  <section class="section section-mobile">
    <div class="section-h">
      <div class="ttl">Actividad reciente</div>
      <div class="lnk">Ver todo →</div>
    </div>
    <WcAdminFeedGroup
      title="Pagos"
      :count="payments.length"
      count-variant="green"
      :when="paymentsList[0]?.timeAgo || ''"
    >
      <WcAdminFeedRow
        v-for="(p, idx) in paymentsList"
        :key="`p${idx}`"
        variant="pago"
        :name="p.buyerName || p.clientName || p.nombre || 'Cliente'"
        :plan="p.plan || p.planName || ''"
        :when="p.timeAgo || ''"
        :amount="p.amountFormatted || (typeof p.amount === 'number' ? `$${p.amount.toLocaleString('es-CO', { maximumFractionDigits: 0 })}` : (p.amount ? `$${p.amount}` : ''))"
      />
    </WcAdminFeedGroup>
    <WcAdminFeedGroup
      v-if="inscriptionsList.length"
      title="Inscripciones"
      :count="inscriptions.length"
      count-variant="blue"
      :when="inscriptionsList[0]?.timeAgo || 'Hace 1 mes'"
    >
      <WcAdminFeedRow
        v-for="(i, idx) in inscriptionsList"
        :key="`i${idx}`"
        variant="insc"
        :name="i.nombre || i.clientName || 'Inscripción'"
        :plan="i.plan || i.planName || ''"
        :when="i.timeAgo || ''"
        pending="Pending"
        cta-text="Contactar →"
        @cta="$router.push(`/admin/inscriptions${i.id ? '?id=' + i.id : ''}`)"
      >
        <template #icon>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
            <line x1="19" y1="8" x2="19" y2="14"></line>
            <line x1="22" y1="11" x2="16" y2="11"></line>
          </svg>
        </template>
      </WcAdminFeedRow>
    </WcAdminFeedGroup>
  </section>

  <!-- DESKTOP: feed-card unificado -->
  <div class="feed-card wc-admin-card-feed section-desktop">
    <div class="feed-head-main">
      <div class="ttl">Actividad reciente</div>
      <div class="lnk">Ver todo →</div>
    </div>
    <WcAdminFeedGroup
      title="Pagos"
      :count="payments.length"
      count-variant="green"
      when="Últimas 24h"
    >
      <WcAdminFeedRow
        v-for="(p, idx) in paymentsList"
        :key="`pd${idx}`"
        variant="pago"
        :name="p.buyerName || p.clientName || p.nombre || 'Cliente'"
        :plan="p.plan || p.planName || ''"
        :when="p.timeAgo || ''"
        :amount="p.amountFormatted || (typeof p.amount === 'number' ? `$${p.amount.toLocaleString('es-CO', { maximumFractionDigits: 0 })}` : (p.amount ? `$${p.amount}` : ''))"
        meta="Pago recibido"
      />
    </WcAdminFeedGroup>
    <WcAdminFeedGroup
      v-if="inscriptionsList.length"
      title="Inscripciones"
      :count="inscriptions.length"
      count-variant="blue"
      when="Hace 1 mes · sin contactar"
    >
      <WcAdminFeedRow
        v-for="(i, idx) in inscriptionsList"
        :key="`id${idx}`"
        variant="insc"
        :name="i.nombre || i.clientName || 'Inscripción'"
        :plan="i.plan || i.planName || ''"
        :when="i.timeAgo || ''"
        meta="Nueva inscripción"
        pending="Pending Contact"
        cta-text="Contactar →"
        @cta="$router.push(`/admin/inscriptions${i.id ? '?id=' + i.id : ''}`)"
      />
    </WcAdminFeedGroup>
  </div>
</template>

<style scoped>
.section-mobile { display: block; }
.section-desktop { display: none; }
@media (min-width: 1024px){
  .section-mobile { display: none; }
  .section-desktop { display: block; }
}
</style>
