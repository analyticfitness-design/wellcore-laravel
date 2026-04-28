<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { RouterLink, useRoute } from 'vue-router';

const props = defineProps({
  // Drawer "Mas" items: array de { name, to, routeName, icon? }
  drawerItems: { type: Array, default: () => [] },
});

const route = useRoute();
const drawerOpen = ref(false);

// Las 5 tabs del bottom nav son fixed (segun rediseno aprobado).
// El item "Mas" abre un drawer con todos los demas modulos admin.
const fixedTabs = [
  { name: 'Dashboard', to: '/admin', routeName: 'admin-dashboard', icon: 'home' },
  { name: 'Clientes', to: '/admin/clients', routeName: 'admin-clients', icon: 'users' },
  { name: 'Pagos', to: '/admin/payments', routeName: 'admin-payments', icon: 'card' },
  { name: 'Feed', to: '/admin/feed', routeName: 'admin-feed', icon: 'feed' },
];

function isActive(routeName) {
  return route.name === routeName;
}

function openDrawer() { drawerOpen.value = true; }
function closeDrawer() { drawerOpen.value = false; }

// Cerrar drawer al cambiar de ruta
const watcher = (newRoute) => { drawerOpen.value = false; };

// Bloquear scroll del body cuando el drawer esta abierto
const lockBodyScroll = (lock) => {
  document.body.style.overflow = lock ? 'hidden' : '';
};

onMounted(() => {
  // Sincronizar scroll lock con drawerOpen
  const stop = () => lockBodyScroll(drawerOpen.value);
  // Watcher manual para evitar dep de @vue/runtime-core en el ejemplo
});
onBeforeUnmount(() => {
  lockBodyScroll(false);
});

import { watch } from 'vue';
watch(drawerOpen, (val) => lockBodyScroll(val));
watch(() => route.fullPath, () => { drawerOpen.value = false; });
</script>

<template>
  <!-- Bottom nav sticky — solo mobile (lg:hidden) -->
  <nav class="bottom-nav lg:hidden" aria-label="Navegacion principal">
    <RouterLink
      v-for="tab in fixedTabs"
      :key="tab.routeName"
      :to="tab.to"
      class="bottom-nav-tab"
      :class="{ 'bottom-nav-tab--active': isActive(tab.routeName) }"
    >
      <svg v-if="tab.icon === 'home'" class="bottom-nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
      </svg>
      <svg v-else-if="tab.icon === 'users'" class="bottom-nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
      </svg>
      <svg v-else-if="tab.icon === 'card'" class="bottom-nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
      </svg>
      <svg v-else-if="tab.icon === 'feed'" class="bottom-nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
      </svg>
      <span class="bottom-nav-label">{{ tab.name }}</span>
    </RouterLink>

    <!-- Tab "Mas" — abre drawer -->
    <button
      class="bottom-nav-tab bottom-nav-tab--more"
      :class="{ 'bottom-nav-tab--active': drawerOpen }"
      @click="openDrawer"
      aria-haspopup="dialog"
      :aria-expanded="drawerOpen"
    >
      <svg class="bottom-nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM18.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
      </svg>
      <span class="bottom-nav-label">Mas</span>
    </button>
  </nav>

  <!-- Drawer "Mas" — overlay + lista vertical de modulos restantes -->
  <Teleport to="body">
    <Transition name="drawer-fade">
      <div v-if="drawerOpen" class="drawer-overlay lg:hidden" @click="closeDrawer" aria-hidden="true"></div>
    </Transition>
    <Transition name="drawer-slide">
      <aside v-if="drawerOpen" class="drawer-panel lg:hidden" role="dialog" aria-label="Mas modulos">
        <header class="drawer-header">
          <span class="drawer-title">MAS MODULOS</span>
          <button class="drawer-close" @click="closeDrawer" aria-label="Cerrar">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </header>
        <nav class="drawer-list">
          <RouterLink
            v-for="item in drawerItems"
            :key="item.routeName || item.to"
            :to="item.to"
            class="drawer-item"
            :class="{ 'drawer-item--active': isActive(item.routeName) }"
          >
            <span class="drawer-item-name">{{ item.name }}</span>
            <span v-if="item.badge" class="drawer-item-badge">{{ item.badge }}</span>
          </RouterLink>
        </nav>
      </aside>
    </Transition>
  </Teleport>
</template>

<style scoped>
/* ============================================================================
   AdminBottomNav — sticky bottom 5 tabs + drawer "Mas".
   Mobile only. Drawer usa Teleport al body para escapar contenedores con overflow.
   ============================================================================ */

.bottom-nav {
    position: fixed; left: 0; right: 0; bottom: 0; z-index: 30;
    display: flex; align-items: stretch; justify-content: space-around;
    background: rgba(10, 10, 10, 0.95);
    backdrop-filter: blur(20px) saturate(150%);
    -webkit-backdrop-filter: blur(20px) saturate(150%);
    border-top: 1px solid var(--color-wc-border);
    padding: 6px 4px calc(6px + env(safe-area-inset-bottom)) 4px;
}
/* Ocultar en desktop (>= 1024px breakpoint Tailwind lg) — el media query gana
   sobre la utility .lg\:hidden por especificidad scoped. */
@media (min-width: 1024px) {
    .bottom-nav { display: none; }
}
.bottom-nav-tab {
    flex: 1; max-width: 80px;
    display: flex; flex-direction: column; align-items: center; gap: 3px;
    padding: 6px 4px;
    color: var(--color-wc-text-tertiary);
    background: transparent; border: 0;
    text-decoration: none; cursor: pointer;
    transition: color 0.15s var(--ease-out, ease);
}
.bottom-nav-tab:active { transform: scale(0.95); }
.bottom-nav-tab--active { color: var(--color-wc-accent, #DC2626); }
.bottom-nav-icon { width: 22px; height: 22px; flex-shrink: 0; }
.bottom-nav-label {
    font-family: var(--font-mono, monospace);
    font-size: 9px; letter-spacing: 0.1em;
    font-weight: 600;
}

/* ── Drawer "Mas" ─────────────────────────────────────────────────────────── */
.drawer-overlay {
    position: fixed; inset: 0; z-index: 60;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}
.drawer-panel {
    position: fixed; right: 0; top: 0; bottom: 0; z-index: 70;
    width: min(85vw, 340px);
    background: var(--color-wc-bg-secondary, #111);
    border-left: 1px solid var(--color-wc-border);
    display: flex; flex-direction: column;
    overflow: hidden;
}
.drawer-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 18px 18px 14px;
    border-bottom: 1px solid var(--color-wc-border);
}
.drawer-title {
    font-family: var(--font-display);
    font-size: 14px; letter-spacing: 0.22em;
    color: var(--color-wc-text);
}
.drawer-close {
    width: 32px; height: 32px; border-radius: 8px;
    display: inline-flex; align-items: center; justify-content: center;
    color: var(--color-wc-text-tertiary);
    background: transparent; border: 1px solid var(--color-wc-border);
    cursor: pointer;
}
.drawer-list { flex: 1; overflow-y: auto; padding: 8px 0; }
.drawer-item {
    display: flex; align-items: center; justify-content: space-between;
    padding: 12px 18px;
    color: var(--color-wc-text-secondary);
    font-family: var(--font-sans);
    font-size: 14px; font-weight: 500;
    text-decoration: none;
    transition: background 0.12s var(--ease-out, ease), color 0.12s var(--ease-out, ease);
    border-left: 2px solid transparent;
}
.drawer-item:active { background: rgba(255, 255, 255, 0.04); }
.drawer-item--active {
    color: var(--color-wc-text);
    background: rgba(220, 38, 38, 0.08);
    border-left-color: var(--color-wc-accent, #DC2626);
}
.drawer-item-badge {
    font-family: var(--font-mono, monospace);
    font-size: 8px; letter-spacing: 0.18em;
    background: rgba(212, 160, 76, 0.14);
    color: var(--color-wc-gold, #D4A04C);
    padding: 2px 7px; border-radius: 4px;
    text-transform: uppercase;
}

/* ── Animaciones drawer ───────────────────────────────────────────────────── */
.drawer-fade-enter-active, .drawer-fade-leave-active {
    transition: opacity 0.22s var(--ease-out, ease);
}
.drawer-fade-enter-from, .drawer-fade-leave-to { opacity: 0; }
.drawer-slide-enter-active, .drawer-slide-leave-active {
    transition: transform 0.28s var(--ease-out, ease);
}
.drawer-slide-enter-from, .drawer-slide-leave-to { transform: translateX(100%); }
</style>
