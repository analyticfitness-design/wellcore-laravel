<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

// Nuevos primitives target Claude Design (Fase 1)
import WcAdminTopBar from '../components/ui/wellcore-admin/WcAdminTopBar.vue';
import WcAdminSidebar from '../components/ui/wellcore-admin/WcAdminSidebar.vue';
import WcAdminBottomNav from '../components/ui/wellcore-admin/WcAdminBottomNav.vue';
import WcAdminCommandPalette from '../components/ui/wellcore-admin/WcAdminCommandPalette.vue';

// Componentes legacy — usados mientras la vista no esté migrada al nuevo shell.
import AdminTopBarLegacy from '../components/admin/dashboard/AdminTopBar.vue';
import AdminSidebarLegacy from '../components/admin/dashboard/AdminSidebar.vue';
import AdminBottomNavLegacy from '../components/admin/dashboard/AdminBottomNav.vue';

const authStore = useAuthStore();
const route = useRoute();
const router = useRouter();

const sidebarOpen = ref(false);
const loggingOut = ref(false);
const cmdPalette = ref(null);

const userName = computed(() => localStorage.getItem('wc_user_name') || 'Admin');
const userRoleUpper = computed(() => (localStorage.getItem('wc_user_type') || 'admin').toUpperCase());
const userInitial = computed(() => (userName.value || 'A').charAt(0).toUpperCase());

// Admin es dark-first. Forzamos dark al entrar.
onMounted(() => {
  document.documentElement.classList.add('dark');
  try { localStorage.setItem('darkMode', 'true'); } catch (_) {}
});

async function handleLogout() {
  if (loggingOut.value) return;
  loggingOut.value = true;
  try {
    await authStore.logout();
  } finally {
    loggingOut.value = false;
    router.push('/login');
  }
}

const unwatch = router.afterEach(() => { sidebarOpen.value = false; });
onUnmounted(() => { if (unwatch) unwatch(); });

// ── Strangler Fig: detección de vista migrada al nuevo shell ────────────
// Map de rutas migradas → modifier del wrapper. Cada entry: pathPrefix → tab.
// Las vistas no listadas siguen renderizando el shell legacy intacto.
//
// Estrategia Fase 3+: las vistas operacionales reciben el shell visual nuevo
// (sidebar/topbar/bottomnav target), pero su contenido legacy interno sigue
// usando admin-atmosphere CSS via `.admin-shell` que aplicamos AL MISMO TIEMPO.
const MIGRATED_ROUTES = [
  // Fase 2 — Dashboard home (full target — sin .admin-shell legacy fallback)
  { match: (p) => p === '/admin',                   tab: 'dashboard',      cosmetic: false },
  // Fase 3 — Operaciones financieras (cosmetic shell, contenido legacy)
  { match: (p) => p.startsWith('/admin/clients'),         tab: 'clients',        cosmetic: true },
  { match: (p) => p.startsWith('/admin/payments'),        tab: 'payments',       cosmetic: true },
  { match: (p) => p.startsWith('/admin/inscriptions'),    tab: 'inscriptions',   cosmetic: true },
  { match: (p) => p.startsWith('/admin/payment-proofs'),  tab: 'payment-proofs', cosmetic: true },
  { match: (p) => p.startsWith('/admin/invitations'),     tab: 'invitations',    cosmetic: true },
  // Fase 4 — Equipo (cosmetic shell)
  { match: (p) => p.startsWith('/admin/coaches'),         tab: 'coaches',         cosmetic: true },
  { match: (p) => p.startsWith('/admin/plan-tickets/stats'), tab: 'stats-tickets', cosmetic: true },
  { match: (p) => p.startsWith('/admin/plan-tickets'),    tab: 'plan-tickets',    cosmetic: true },
  { match: (p) => p.startsWith('/admin/tickets'),         tab: 'tickets',         cosmetic: true },
  { match: (p) => p.startsWith('/admin/client-requests'), tab: 'client-requests', cosmetic: true },
];

const matchedRoute = computed(() => MIGRATED_ROUTES.find(r => r.match(route.path)) || null);
const isMigrated = computed(() => route.meta?.adminMigrated === true || !!matchedRoute.value);
const adminTab = computed(() => route.meta?.adminTab || matchedRoute.value?.tab || 'dashboard');
const isCosmetic = computed(() => !!matchedRoute.value?.cosmetic);

function openSearch() { cmdPalette.value?.open(); }

// Shortcuts Cmd+K palette
const SHORTCUTS = [
  { id: 'a1', label: 'Contactar inscripciones pendientes', route: '/admin/inscriptions',    meta: '3 nuevas', section: 'Sugerencias' },
  { id: 'a2', label: 'Abrir Live Feed',                    route: '/admin/feed',            meta: '⌘ L',      section: 'Sugerencias' },
  { id: 'a3', label: 'Cola de Drops',                      route: '/admin/marketing/queue', meta: '⌘ R',      section: 'Sugerencias' },
  { id: 'n1', label: 'Dashboard',     route: '/admin',                  meta: 'G D', section: 'Navegación' },
  { id: 'n2', label: 'Clientes',      route: '/admin/clients',          meta: 'G C', section: 'Navegación' },
  { id: 'n3', label: 'Pagos',         route: '/admin/payments',         meta: 'G P', section: 'Navegación' },
  { id: 'n4', label: 'Comprobantes',  route: '/admin/payment-proofs',   meta: 'G B', section: 'Navegación' },
  { id: 'n5', label: 'Coaches',       route: '/admin/coaches',          meta: 'G K', section: 'Navegación' },
  { id: 'n6', label: 'Tickets',       route: '/admin/plan-tickets',     meta: 'G T', section: 'Navegación' },
  { id: 'n7', label: 'RISE',          route: '/admin/rise',             meta: 'G I', section: 'Navegación' },
  { id: 'n8', label: 'Audit Log',     route: '/admin/audit-log',        meta: 'G A', section: 'Navegación' },
  { id: 'n9', label: 'Configuración', route: '/admin/settings',         meta: 'G S', section: 'Navegación' },
];
</script>

<template>
  <!-- ═══════ NUEVO SHELL (vistas migradas) ═══════ -->
  <!-- Cuando isCosmetic, agregamos también `.admin-shell` para que los selectores
       legacy de admin-atmosphere.css apliquen al contenido interno de la vista. -->
  <div
    v-if="isMigrated"
    :class="['wc-admin-shell', `wc-admin-shell--${adminTab}`, { 'admin-shell': isCosmetic }]"
  >
    <!-- Mobile sidebar overlay -->
    <Transition
      enter-active-class="transition-opacity ease-linear duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity ease-linear duration-300"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="sidebarOpen"
        class="admin-sidebar-overlay"
        @click="sidebarOpen = false"
        aria-hidden="true"
      ></div>
    </Transition>

    <WcAdminSidebar
      :open="sidebarOpen"
      :user-name="userName"
      :user-role="userRoleUpper"
      @close="sidebarOpen = false"
      @logout="handleLogout"
    />

    <main class="main">
      <WcAdminTopBar
        :user-name="userName"
        :user-role="userRoleUpper"
        @toggle-sidebar="sidebarOpen = !sidebarOpen"
        @open-search="openSearch"
      />

      <div class="canvas">
        <slot />
      </div>
    </main>

    <WcAdminBottomNav />

    <WcAdminCommandPalette ref="cmdPalette" :shortcuts="SHORTCUTS" />
  </div>

  <!-- ═══════ LEGACY SHELL (vistas no migradas — Fase 3+ las migra) ═══════ -->
  <div v-else class="admin-shell min-h-screen bg-wc-bg text-wc-text">
    <Transition
      enter-active-class="transition-opacity ease-linear duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity ease-linear duration-300"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="sidebarOpen"
        class="admin-sidebar-overlay lg:hidden"
        @click="sidebarOpen = false"
        aria-hidden="true"
      ></div>
    </Transition>

    <AdminTopBarLegacy
      :avatar-initial="userInitial"
      :user-name="userName"
      notifications-endpoint="/api/v/admin/notifications"
      @toggle-sidebar="sidebarOpen = !sidebarOpen"
    />

    <AdminSidebarLegacy
      :open="sidebarOpen"
      :user-name="userName"
      user-role="SUPERADMIN"
      @close="sidebarOpen = false"
      @logout="handleLogout"
    />

    <main class="admin-main">
      <slot />
    </main>

    <AdminBottomNavLegacy />
  </div>
</template>

<style scoped>
.admin-sidebar-overlay {
  position: fixed;
  inset: 0;
  z-index: 25;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(2px);
  -webkit-backdrop-filter: blur(2px);
}

@media (min-width: 1024px) {
  .admin-sidebar-overlay { display: none; }
}

/* Padding del shell legacy — preservado del estado anterior */
.admin-main {
  padding: 20px 16px 90px;
  min-height: calc(100vh - 52px);
}

@media (min-width: 1024px) {
  .admin-main {
    padding: 24px 28px 40px;
    margin-left: var(--admin-sidebar-w, 240px);
    min-height: calc(100vh - var(--admin-topbar-h, 64px));
  }
}
</style>
