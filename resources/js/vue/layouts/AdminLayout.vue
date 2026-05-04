<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import WcAdminTopBar from '../components/ui/wellcore-admin/WcAdminTopBar.vue';
import WcAdminSidebar from '../components/ui/wellcore-admin/WcAdminSidebar.vue';
import WcAdminBottomNav from '../components/ui/wellcore-admin/WcAdminBottomNav.vue';
import WcAdminCommandPalette from '../components/ui/wellcore-admin/WcAdminCommandPalette.vue';

const authStore = useAuthStore();
const route = useRoute();
const router = useRouter();

const sidebarOpen = ref(false);
const loggingOut = ref(false);
const cmdPalette = ref(null);

const userName = computed(() => localStorage.getItem('wc_user_name') || 'Admin');
const userRole = computed(() => (localStorage.getItem('wc_user_type') || 'admin').toUpperCase());

// Admin esta diseñado dark-first (cards rojas/gold tuneadas sobre #0a0a0a;
// custom classes como .wc-admin-shell, .sidebar tienen fondos hardcoded).
// Forzamos dark al entrar al portal y persistimos para que el toggle del
// PublicLayout no se quede en light cuando volves a /admin desde /metodo, etc.
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

// Tab modifier — cada vista admin establece route.meta.adminTab para que
// .wc-admin-shell--{tab} cargue el CSS scopeado correspondiente.
const adminTab = computed(() => route.meta?.adminTab || 'dashboard');

function openSearch() { cmdPalette.value?.open(); }

// Shortcuts del Command Palette
const SHORTCUTS = [
  { id: 'a1', label: 'Contactar inscripciones pendientes', route: '/admin/inscriptions', meta: '3 nuevas', section: 'Sugerencias' },
  { id: 'a2', label: 'Abrir Live Feed',                    route: '/admin/feed',         meta: '⌘ L',     section: 'Sugerencias' },
  { id: 'a3', label: 'Cola de Drops',                      route: '/admin/marketing/queue', meta: '⌘ R',  section: 'Sugerencias' },
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
  <!-- Wrapper .wc-admin-shell + modifier por tab. CSS scopeado en
       wc-admin-shell.css (universal) + wc-admin-shell-tabs/{tab}.css (específico). -->
  <div :class="['wc-admin-shell', `wc-admin-shell--${adminTab}`]">

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

    <!-- Sidebar — fixed 240px desktop / drawer mobile con 9 grupos -->
    <WcAdminSidebar
      :open="sidebarOpen"
      :user-name="userName"
      :user-role="userRole"
      @close="sidebarOpen = false"
      @logout="handleLogout"
    />

    <!-- Main column (topbar + canvas) -->
    <main class="main">
      <WcAdminTopBar
        :user-name="userName"
        :user-role="userRole"
        @toggle-sidebar="sidebarOpen = !sidebarOpen"
        @open-search="openSearch"
      />

      <div class="canvas">
        <slot />
      </div>
    </main>

    <!-- Bottom nav mobile (5 tabs) -->
    <WcAdminBottomNav />

    <!-- Cmd+K palette montado a nivel layout (mobile + desktop) -->
    <WcAdminCommandPalette ref="cmdPalette" :shortcuts="SHORTCUTS" />
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
</style>
