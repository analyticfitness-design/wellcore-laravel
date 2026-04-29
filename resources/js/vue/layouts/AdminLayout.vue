<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import AdminTopBar from '../components/admin/dashboard/AdminTopBar.vue';
import AdminSidebar from '../components/admin/dashboard/AdminSidebar.vue';
import AdminBottomNav from '../components/admin/dashboard/AdminBottomNav.vue';

const authStore = useAuthStore();
const router = useRouter();

const sidebarOpen = ref(false);
const loggingOut = ref(false);

const userName = computed(() => localStorage.getItem('wc_user_name') || 'Admin');
const userInitial = computed(() => (userName.value || 'A').charAt(0).toUpperCase());

// Admin esta diseñado dark-first (cards rojas/gold tuneadas sobre #0a0a0a;
// custom classes como .admin-shell, .admin-sidebar tienen fondos hardcoded).
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

// Cerrar sidebar mobile al cambiar de ruta
const unwatch = router.afterEach(() => { sidebarOpen.value = false; });
onUnmounted(() => { if (unwatch) unwatch(); });
</script>

<template>
  <!-- .admin-shell activa atmosfera v2 (radial orb + grain noise + dark tokens) — ver admin-atmosphere.css -->
  <div class="admin-shell min-h-screen bg-wc-bg text-wc-text">

    <!-- Mobile sidebar overlay (oscurece el contenido cuando el drawer esta abierto) -->
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

    <!-- Top bar — sticky 64px desktop / 52px mobile, brand + actions.
         Sin toggle de tema: el portal admin es dark-only por diseño. -->
    <AdminTopBar
      :avatar-initial="userInitial"
      :user-name="userName"
      notifications-endpoint="/api/v/admin/notifications"
      @toggle-sidebar="sidebarOpen = !sidebarOpen"
    />

    <!-- Sidebar — fixed 240px desktop / drawer mobile con 9 grupos colapsables -->
    <AdminSidebar
      :open="sidebarOpen"
      :user-name="userName"
      user-role="SUPERADMIN"
      @close="sidebarOpen = false"
      @logout="handleLogout"
    />

    <!-- Page content (offset por sidebar en lg+) -->
    <main class="admin-main">
      <slot />
    </main>

    <!-- Mobile Bottom Navigation — 5 tabs + drawer "Mas" con todos los modulos -->
    <AdminBottomNav />

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
