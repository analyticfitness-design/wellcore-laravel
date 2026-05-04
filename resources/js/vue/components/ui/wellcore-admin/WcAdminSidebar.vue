<script setup>
import { ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const props = defineProps({
  open: { type: Boolean, default: false },
  userName: { type: String, default: '' },
  userRole: { type: String, default: 'SUPERADMIN' },
});
const emit = defineEmits(['close', 'logout']);

const route = useRoute();
const router = useRouter();
const collapsed = ref(false);

const userInitial = computed(() => (props.userName || 'A').charAt(0).toUpperCase());

const SECTIONS = [
  { label: 'General', items: [
    { name: 'Dashboard',  route: '/admin',         icon: 'dashboard' },
    { name: 'Live Feed',  route: '/admin/feed',    icon: 'live', badge: '12' },
    { name: 'Clientes',   route: '/admin/clients', icon: 'users' },
    { name: 'Formularios',route: '/admin/formularios', icon: 'forms' },
  ]},
  { label: 'Financiero', items: [
    { name: 'Pagos',         route: '/admin/payments',       icon: 'card' },
    { name: 'Inscripciones', route: '/admin/inscriptions',   icon: 'user-plus' },
    { name: 'Invitaciones',  route: '/admin/invitations',    icon: 'invite' },
    { name: 'Comprobantes',  route: '/admin/payment-proofs', icon: 'file' },
  ]},
  { label: 'Equipo', items: [
    { name: 'Coaches', route: '/admin/coaches', icon: 'users' },
  ]},
  { label: 'Marketing', items: [
    { name: 'Cola de Drops', route: '/admin/marketing/queue', icon: 'star', pulse: true },
  ]},
  { label: 'Planes', items: [
    { name: 'Planes',        route: '/admin/plans',         icon: 'plan', badge: '75' },
    { name: 'Generador IA',  route: '/admin/ai-generator',  icon: 'ai' },
  ]},
  { label: 'RISE', items: [
    { name: 'RISE', route: '/admin/rise', icon: 'star' },
  ]},
  { label: 'Comunicación', items: [
    { name: 'Chat Analytics',     route: '/admin/chat-analytics',  icon: 'chat' },
    { name: 'Tickets',            route: '/admin/tickets',         icon: 'tickets' },
    { name: 'Tickets de Planes',  route: '/admin/plan-tickets',    icon: 'tickets' },
    { name: 'Solicitudes Coaches',route: '/admin/client-requests', icon: 'requests' },
    { name: 'Stats de Tickets',   route: '/admin/plan-tickets/stats', icon: 'bars' },
  ]},
  { label: 'Growth', items: [
    { name: 'Campañas',  route: '/admin/campaigns', icon: 'campaign' },
    { name: 'Referidos', route: '/admin/referrals', icon: 'invite' },
  ]},
  { label: 'Sistema', items: [
    { name: 'Herramientas',  route: '/admin/tools',     icon: 'tools' },
    { name: 'Audit Log',     route: '/admin/audit-log', icon: 'audit' },
    { name: 'Configuración', route: '/admin/settings',  icon: 'gear' },
  ]},
];

function isActive(target) {
  if (target === '/admin') return route.path === '/admin';
  return route.path === target || route.path.startsWith(target + '/');
}

function go(target) {
  router.push(target);
  emit('close');
}
</script>

<template>
  <aside :class="['sidebar', { open, collapsed }]">
    <div class="side-brand">
      <div class="brand-mark">
        <svg viewBox="0 0 16 16" fill="none">
          <path d="M2 4 L4.5 12 L8 6 L11.5 12 L14 4" stroke="#fff" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
      <div class="meta">
        <div class="brand-name">WCORE<b>FIT</b></div>
        <div class="role-chip"><span class="dot"></span>{{ userRole }}</div>
      </div>
      <button class="collapse-btn" @click="collapsed = !collapsed" aria-label="Collapse sidebar">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="15 18 9 12 15 6"></polyline>
        </svg>
      </button>
    </div>

    <div class="side-scroll">
      <template v-for="sec in SECTIONS" :key="sec.label">
        <div class="side-cat"><span>{{ sec.label }}</span></div>
        <div
          v-for="item in sec.items"
          :key="item.route"
          :class="['side-item', { active: isActive(item.route) }]"
          @click="go(item.route)"
        >
          <span class="ic">
            <!-- Placeholder genérico — Fase 7 polish refinará iconos por item.icon -->
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="3" width="7" height="9" rx="1.5"/>
              <rect x="14" y="3" width="7" height="5" rx="1.5"/>
              <rect x="14" y="12" width="7" height="9" rx="1.5"/>
              <rect x="3" y="16" width="7" height="5" rx="1.5"/>
            </svg>
          </span>
          <span class="lbl">{{ item.name }}</span>
          <span v-if="item.badge" class="badge">{{ item.badge }}</span>
          <span v-if="item.pulse" class="pulse-dot"></span>
        </div>
      </template>
    </div>

    <div class="side-foot">
      <div class="avatar"><div class="inner">{{ userInitial }}</div></div>
      <div class="who">
        <div class="nm">{{ userName || 'Daniel Esparza' }}</div>
        <div class="rl">CEO · Owner</div>
      </div>
      <button class="gear" aria-label="Logout" @click="emit('logout')">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
          <polyline points="16 17 21 12 16 7"/>
          <line x1="21" y1="12" x2="9" y2="12"/>
        </svg>
      </button>
    </div>
  </aside>
</template>
