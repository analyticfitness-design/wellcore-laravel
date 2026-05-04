<script setup>
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();

const ITEMS = [
  { lbl: 'Dashboard', route: '/admin', icon: 'dashboard' },
  { lbl: 'Clientes', route: '/admin/clients', icon: 'users' },
  { lbl: 'Pagos', route: '/admin/payments', icon: 'card' },
  { lbl: 'Feed', route: '/admin/feed', icon: 'live' },
  { lbl: 'Más', route: '/admin/tools', icon: 'more' },
];

function isActive(target) {
  if (target === '/admin') return route.path === '/admin';
  return route.path === target || route.path.startsWith(target + '/');
}
</script>

<template>
  <nav class="bottomnav">
    <div
      v-for="item in ITEMS"
      :key="item.route"
      :class="['bn-item', { active: isActive(item.route) }]"
      @click="router.push(item.route)"
    >
      <div class="ic">
        <!-- Dashboard (grid 2x2) -->
        <svg v-if="item.icon === 'dashboard'" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="3" width="7" height="9" rx="1.5"/>
          <rect x="14" y="3" width="7" height="5" rx="1.5"/>
          <rect x="14" y="12" width="7" height="9" rx="1.5"/>
          <rect x="3" y="16" width="7" height="5" rx="1.5"/>
        </svg>
        <!-- Users -->
        <svg v-else-if="item.icon === 'users'" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
          <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
          <circle cx="9" cy="7" r="4"/>
          <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
          <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
        </svg>
        <!-- Card -->
        <svg v-else-if="item.icon === 'card'" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
          <rect x="2" y="6" width="20" height="13" rx="2"/>
          <line x1="2" y1="11" x2="22" y2="11"/>
        </svg>
        <!-- Live (pulse) -->
        <svg v-else-if="item.icon === 'live'" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="3"/>
          <path d="M5 12a7 7 0 0 1 14 0"/>
          <path d="M2 12a10 10 0 0 1 20 0"/>
        </svg>
        <!-- More -->
        <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="5" cy="12" r="1.5"/>
          <circle cx="12" cy="12" r="1.5"/>
          <circle cx="19" cy="12" r="1.5"/>
        </svg>
      </div>
      <div class="lbl">{{ item.lbl }}</div>
      <span class="cap"></span>
    </div>
  </nav>
</template>
