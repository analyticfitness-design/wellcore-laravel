<script setup>
import WcAdminToolRow from '../../ui/wellcore-admin/WcAdminToolRow.vue';

const TOOLS = [
  { cat: 'Operación', items: [
    { name: 'Live Feed',     route: '/admin/feed',           icon: 'live',      iconVariant: 'green', meta: '12' },
    { name: 'Inscripciones', route: '/admin/inscriptions',   icon: 'user-plus', iconVariant: 'blue',  meta: '3 pend.' },
    { name: 'Invitaciones',  route: '/admin/invitations',    icon: 'invite',    iconVariant: '',      meta: '' },
    { name: 'Comprobantes',  route: '/admin/payment-proofs', icon: 'file',      iconVariant: '',      meta: '' },
  ]},
  { cat: 'Marketing & Equipo', items: [
    { name: 'Cola de Drops',   route: '/admin/marketing/queue', icon: 'star',     iconVariant: 'red',    meta: 'Activa', pulse: true },
    { name: 'Generador IA',    route: '/admin/ai-generator',    icon: 'ai',       iconVariant: 'purple', meta: 'Beta' },
    { name: 'RISE',            route: '/admin/rise',            icon: 'star',     iconVariant: 'purple', meta: '' },
    { name: 'Chat Analytics',  route: '/admin/chat-analytics',  icon: 'chat',     iconVariant: '',       meta: '' },
  ]},
  { cat: 'Sistema', items: [
    { name: 'Tickets de Planes',   route: '/admin/plan-tickets',       icon: 'tickets',  iconVariant: 'gold',  meta: '' },
    { name: 'Solicitudes Coaches', route: '/admin/client-requests',    icon: 'requests', iconVariant: '',      meta: '' },
    { name: 'Stats de Tickets',    route: '/admin/plan-tickets/stats', icon: 'bars',     iconVariant: '',      meta: '' },
    { name: 'Campañas',            route: '/admin/campaigns',          icon: 'campaign', iconVariant: 'amber', meta: '' },
  ]},
];
</script>

<template>
  <!-- MOBILE: section + lista vertical con cats inline -->
  <section class="section section-mobile">
    <div class="section-h">
      <div class="ttl">Herramientas</div>
      <div class="lnk">12 atajos →</div>
    </div>
    <div class="tools-card">
      <template v-for="group in TOOLS" :key="group.cat">
        <div class="tools-cat">{{ group.cat }}</div>
        <WcAdminToolRow
          v-for="t in group.items"
          :key="t.name"
          :name="t.name"
          :to="t.route"
          :icon-variant="t.iconVariant"
          :meta="t.meta"
          :pulse="!!t.pulse"
        />
      </template>
    </div>
  </section>

  <!-- DESKTOP: 3-col grid -->
  <div class="tools-card section-desktop">
    <div class="tools-head">
      <div class="ttl">Herramientas</div>
      <div class="meta">12 atajos · agrupados por dominio</div>
    </div>
    <div class="tools-grid">
      <div v-for="group in TOOLS" :key="group.cat" class="tools-col">
        <div class="tools-cat">{{ group.cat }}</div>
        <WcAdminToolRow
          v-for="t in group.items"
          :key="t.name"
          :name="t.name"
          :to="t.route"
          :icon-variant="t.iconVariant"
          :meta="t.meta"
          :pulse="!!t.pulse"
        />
      </div>
    </div>
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
