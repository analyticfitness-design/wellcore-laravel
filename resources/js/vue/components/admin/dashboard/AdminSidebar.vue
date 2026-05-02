<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { RouterLink, useRoute } from 'vue-router';
import { useAdminModules } from '../../../composables/useAdminModules';

const props = defineProps({
  // Si se pasa true, el sidebar se muestra en mobile (drawer overlay)
  open: { type: Boolean, default: false },
  // CEO/Superadmin name mostrado en el footer del sidebar
  userName: { type: String, default: 'Admin' },
  userRole: { type: String, default: 'SUPERADMIN' },
});

const emit = defineEmits(['close', 'logout']);

const route = useRoute();
const { groupedModules } = useAdminModules();

// Persistencia de grupos colapsados: localStorage['admin_sidebar_groups_v2']
// Shape: { general: false, financiero: true, ... } donde true = colapsado.
const STORAGE_KEY = 'admin_sidebar_groups_v2';
const collapsedGroups = ref({});

onMounted(() => {
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    if (raw) collapsedGroups.value = JSON.parse(raw);
  } catch (e) {
    // localStorage puede no estar disponible (modo incognito en algunos browsers)
    collapsedGroups.value = {};
  }
});

watch(collapsedGroups, (val) => {
  try { localStorage.setItem(STORAGE_KEY, JSON.stringify(val)); } catch {}
}, { deep: true });

function toggleGroup(groupId) {
  collapsedGroups.value = { ...collapsedGroups.value, [groupId]: !collapsedGroups.value[groupId] };
}

function isCollapsed(groupId) {
  return !!collapsedGroups.value[groupId];
}

function isActive(routeName) {
  return route.name === routeName;
}

// Resolver iconos como componentes inline. SVGs minimal stroke-1.5 24x24.
// Mapeo keyword → path stroke. Igual al que usaba AdminLayout legacy + nuevos.
const ICON_PATHS = {
  home:      'M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z',
  feed:      'M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z',
  users:     'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z',
  form:      'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z',
  card:      'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z',
  'user-plus': 'M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z',
  mail:      'M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75',
  check:     'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
  headset:   'M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5',
  megaphone: 'M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 0 8.835-2.535m0 0A23.74 23.74 0 0 0 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 0 1 0 3.46',
  clipboard: 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z',
  sparkles:  'M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 0 0-2.455 2.456Z',
  lightning: 'M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z',
  chart:     'M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941',
  ticket:    'M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z',
  'ticket-2':'M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z',
  inbox:     'M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H6.911a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661Z',
  stats:     'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z',
  target:    'M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z',
  share:     'M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z',
  wrench:    'M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.049.58.025 1.193-.14 1.743',
  shield:    'M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.063 2.522-.184 3.762a4.49 4.49 0 0 0-1.756-.94 4.484 4.484 0 0 0-3.06-3.06 4.49 4.49 0 0 0-.94-1.756L21 12Zm-9 9c-1.268 0-2.522-.063-3.762-.184a4.49 4.49 0 0 0 .94-1.756 4.484 4.484 0 0 0 3.06-3.06 4.49 4.49 0 0 0 1.756-.94L12 21Z',
  settings:  'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
};

function iconPath(key) {
  return ICON_PATHS[key] || ICON_PATHS.settings;
}
</script>

<template>
  <aside
    class="admin-sidebar"
    :class="{ 'admin-sidebar--open': open }"
    aria-label="Navegacion principal del admin"
  >
    <nav class="admin-sidebar-nav">
      <div
        v-for="group in groupedModules"
        :key="group.id"
        class="nav-group"
        :class="{ 'nav-group--collapsed': isCollapsed(group.id) }"
      >
        <button
          class="nav-group-header"
          @click="toggleGroup(group.id)"
          :aria-expanded="!isCollapsed(group.id)"
          :aria-controls="`group-items-${group.id}`"
        >
          <span class="nav-group-label">{{ group.label }}</span>
          <span class="nav-group-chevron" aria-hidden="true">
            <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" />
            </svg>
          </span>
        </button>

        <ul
          v-show="!isCollapsed(group.id)"
          :id="`group-items-${group.id}`"
          class="nav-group-items"
        >
          <li v-for="item in group.items" :key="item.id">
            <RouterLink
              :to="item.to"
              class="nav-item"
              :class="{ 'nav-item--active': isActive(item.routeName) }"
              @click="emit('close')"
            >
              <span class="nav-item-icon" aria-hidden="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                  <path stroke-linecap="round" stroke-linejoin="round" :d="iconPath(item.icon)" />
                </svg>
              </span>
              <span class="nav-item-label">{{ item.name }}</span>
              <span v-if="item.badge" class="nav-item-badge">{{ item.badge }}</span>
            </RouterLink>
          </li>
        </ul>
      </div>
    </nav>

    <footer class="admin-sidebar-footer">
      <div class="admin-sidebar-footer-role">{{ userRole }}</div>
      <div class="admin-sidebar-footer-name">{{ userName }}</div>
      <button
        class="admin-sidebar-logout"
        @click="emit('logout')"
        aria-label="Cerrar sesion"
      >
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
        </svg>
        <span>Cerrar sesion</span>
      </button>
    </footer>
  </aside>
</template>

<style scoped>
/* ============================================================================
   AdminSidebar — desktop sticky 240px / mobile drawer overlay.
   v2: Oswald section labels + Raleway items + 48px touch targets
   ============================================================================ */

.admin-sidebar {
    position: fixed;
    left: 0;
    top: var(--admin-topbar-h, 64px);
    bottom: 0;
    width: var(--admin-sidebar-w, 240px);
    z-index: 30;
    background: var(--c-surface);
    border-right: 1px solid var(--c-border);
    overflow-y: auto;
    overflow-x: hidden;
    scrollbar-width: thin;
    scrollbar-color: rgba(255, 255, 255, 0.1) transparent;
    display: flex;
    flex-direction: column;
    transform: translateX(-100%);
    transition: transform 0.32s cubic-bezier(0.22, 1, 0.36, 1);
}
.admin-sidebar--open { transform: translateX(0); }

@media (max-width: 1023px) {
    .admin-sidebar {
        bottom: calc(70px + env(safe-area-inset-bottom, 0px));
        -webkit-overflow-scrolling: touch;
        overscroll-behavior: contain;
    }
}
@media (min-width: 1024px) {
    .admin-sidebar { transform: translateX(0); bottom: 0; }
    .admin-sidebar--open { transform: translateX(0); }
}

.admin-sidebar::-webkit-scrollbar { width: 3px; }
.admin-sidebar::-webkit-scrollbar-track { background: transparent; }
.admin-sidebar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 2px;
}

.admin-sidebar-nav {
    flex: 1;
    padding: 16px 0 24px;
    display: flex;
    flex-direction: column;
}

.nav-group { margin-bottom: 4px; }
.nav-group-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    padding: 10px 16px 6px;
    cursor: pointer;
    user-select: none;
    background: transparent;
    border: 0;
}
/* Section labels: Oswald 10px weight 600 ls 2.5px uppercase */
.nav-group-label {
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 2.5px;
    text-transform: uppercase;
    color: var(--c-text-3);
}
.nav-group-chevron {
    color: var(--c-text-3);
    display: inline-flex;
    align-items: center;
    transition: transform 0.2s var(--ease-out, ease);
}
.nav-group--collapsed .nav-group-chevron { transform: rotate(-90deg); }

.nav-group-items {
    list-style: none;
    margin: 0;
    padding: 0 8px 4px;
}
/* Items: Raleway 15px mobile weight 500. Min-height 48px (touch target). */
.nav-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 0 12px;
    min-height: var(--tap-comfort, 48px);
    color: var(--c-text-2);
    font-family: var(--font-sans);
    font-size: 15px;
    font-weight: 500;
    text-decoration: none;
    border-radius: var(--r-md);
    position: relative;
    transition: color 0.15s var(--ease-out, ease), background 0.15s var(--ease-out, ease);
}
@media (min-width: 1024px) {
    .nav-item { font-size: 14px; }
}
.nav-item:hover {
    color: var(--c-text);
    background: var(--c-surface-2);
}
.nav-item--active {
    color: var(--c-text);
    background: var(--c-accent-dim);
    border-left: 2px solid var(--c-accent);
    padding-left: 10px;
}
.nav-item-icon {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    opacity: 0.7;
}
.nav-item--active .nav-item-icon { opacity: 1; color: var(--c-accent); }
.nav-item-label {
    flex: 1;
    min-width: 0;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}
.nav-item-badge {
    margin-left: auto;
    font-family: var(--font-display);
    font-size: 8px;
    font-weight: 600;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    color: var(--c-amber);
    background: var(--c-amber-dim);
    border: 1px solid rgba(212, 168, 14, 0.25);
    border-radius: var(--r-pill);
    padding: 2px 7px;
    flex-shrink: 0;
}

/* ── Footer ─────────────────────────────────────────────────────────────── */
.admin-sidebar-footer {
    padding: 14px 16px;
    border-top: 1px solid var(--c-border);
}
.admin-sidebar-footer-role {
    font-family: var(--font-display);
    font-size: 9px;
    font-weight: 600;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: var(--c-text-3);
    margin-bottom: 4px;
}
.admin-sidebar-footer-name {
    font-family: var(--font-sans);
    font-size: 14px;
    font-weight: 600;
    color: var(--c-text-2);
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    margin-bottom: 10px;
}
.admin-sidebar-logout {
    width: 100%;
    min-height: 44px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 0 12px;
    border-radius: var(--r-md);
    background: transparent;
    border: 1px solid var(--c-border);
    color: var(--c-text-3);
    font-family: var(--font-display);
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    cursor: pointer;
    transition: border-color 0.15s var(--ease-out, ease), color 0.15s var(--ease-out, ease);
}
.admin-sidebar-logout:hover {
    border-color: var(--c-accent-border);
    color: var(--c-accent);
}
</style>
