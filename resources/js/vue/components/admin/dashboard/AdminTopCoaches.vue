<script setup>
import { useRouter } from 'vue-router';
import WcAdminEmpty from '../../ui/wellcore-admin/WcAdminEmpty.vue';

defineProps({
  coaches: { type: Array, default: () => [] },
});

const router = useRouter();
</script>

<template>
  <!-- MOBILE -->
  <section class="section section-mobile">
    <div class="section-h">
      <div class="ttl">Top coaches · este mes</div>
      <div class="lnk" @click="router.push('/admin/coaches')">Ver coaches →</div>
    </div>
    <WcAdminEmpty
      v-if="!coaches.length"
      title="Sin tickets completados este mes."
      hint="El ranking se llena cuando los coaches cierran tickets esta semana."
      cta-text="Ver coaches →"
      @cta="router.push('/admin/coaches')"
    />
  </section>

  <!-- DESKTOP -->
  <div class="coaches-card wc-admin-card-coaches section-desktop">
    <div class="feed-head-main">
      <div class="ttl">Top coaches · este mes</div>
      <div class="lnk" @click="router.push('/admin/coaches')">Ver →</div>
    </div>
    <WcAdminEmpty
      v-if="!coaches.length"
      title="Sin tickets completados<br>este mes."
      hint="El ranking se llena cuando los coaches cierran tickets esta semana."
      cta-text="Ver coaches →"
      @cta="router.push('/admin/coaches')"
    />
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
