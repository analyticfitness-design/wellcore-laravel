<script setup>
import { computed, ref, onMounted, onBeforeUnmount } from 'vue';
import NotificationBell from '../../NotificationBell.vue';

const props = defineProps({
  // Brand label visible solo en desktop (sidebar area)
  brandLabel: { type: String, default: 'WCORE' },
  brandAccent: { type: String, default: 'FIT' },
  // Eyebrow uppercase mostrado al lado del brand en desktop
  eyebrow: { type: String, default: 'COMMAND CENTER' },
  // Rol mostrado en pill rojo
  role: { type: String, default: 'SUPERADMIN' },
  // Iniciales del avatar (1-2 chars)
  avatarInitial: { type: String, default: 'D' },
  // Nombre completo, mostrado en mobile drawer / desktop avatar tooltip
  userName: { type: String, default: 'Admin' },
  // Endpoint API para NotificationBell
  notificationsEndpoint: { type: String, default: '/api/v/admin/notifications' },
});

const emit = defineEmits(['toggle-sidebar']);

// Reloj real time mostrado en desktop (mono Bebas) — formato HH:MM
const now = ref(new Date());
let clockInterval = null;
onMounted(() => {
  clockInterval = setInterval(() => { now.value = new Date(); }, 1000);
});
onBeforeUnmount(() => {
  if (clockInterval) clearInterval(clockInterval);
});
const clockHHMM = computed(() => {
  const h = now.value.getHours().toString().padStart(2, '0');
  const m = now.value.getMinutes().toString().padStart(2, '0');
  return `${h}:${m}`;
});
</script>

<template>
  <!-- Desktop topbar — sticky 64px, brand + eyebrow + actions a la derecha -->
  <header class="topbar-desktop hidden lg:flex">
    <div class="topbar-brand-area">
      <span class="topbar-brand">{{ brandLabel }}<span class="topbar-brand-accent">{{ brandAccent }}</span></span>
    </div>
    <div class="topbar-center">
      <span class="topbar-eyebrow">{{ eyebrow }} · {{ clockHHMM }}</span>
    </div>
    <div class="topbar-right">
      <span class="topbar-live-chip">
        <span class="topbar-live-dot"></span>
        EN VIVO
      </span>
      <span class="topbar-role-badge">{{ role }}</span>
      <NotificationBell :endpoint="notificationsEndpoint" />
      <div class="topbar-avatar" :title="userName">{{ avatarInitial }}</div>
    </div>
  </header>

  <!-- Mobile topbar — sticky 52px, hamburger + brand + actions compactas -->
  <header class="topbar-mobile flex lg:hidden">
    <button
      class="topbar-mobile-hamburger"
      @click="$emit('toggle-sidebar')"
      aria-label="Abrir menu"
    >
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
      </svg>
    </button>

    <span class="topbar-mobile-brand">
      {{ brandLabel }}<span class="topbar-brand-accent">{{ brandAccent }}</span>
      <span class="topbar-mobile-role">{{ role }}</span>
    </span>

    <div class="topbar-mobile-right">
      <NotificationBell :endpoint="notificationsEndpoint" />
      <span class="topbar-live-dot topbar-live-dot--mobile" aria-hidden="true"></span>
      <div class="topbar-avatar topbar-avatar--mobile" :title="userName">{{ avatarInitial }}</div>
    </div>
  </header>
</template>

<style scoped>
/* ============================================================================
   AdminTopBar — desktop 64px / mobile 56px sticky
   v2: Oswald brand + surface-2 role pill + 48px touch targets
   ============================================================================ */

/* ── Desktop ──────────────────────────────────────────────────────────────── */
.topbar-desktop {
    position: sticky; top: 0; z-index: 40;
    height: var(--admin-topbar-h, 64px);
    align-items: center;
    padding: 0 24px 0 0;
    background: rgba(8, 8, 8, 0.92);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    border-bottom: 1px solid var(--c-border);
    gap: 0;
}
.topbar-brand-area {
    width: var(--admin-sidebar-w, 240px);
    flex-shrink: 0;
    padding: 0 20px;
    display: flex; align-items: center; gap: 10px;
    border-right: 1px solid var(--c-border);
    height: 100%;
}
.topbar-brand {
    font-family: var(--font-display);
    font-size: 24px; font-weight: 700; letter-spacing: 5px;
    color: var(--c-text);
}
.topbar-brand-accent { color: var(--c-accent); }
.topbar-center { flex: 1; padding: 0 24px; }
.topbar-eyebrow {
    font-family: var(--font-display);
    font-size: 11px; letter-spacing: 1.6px; text-transform: uppercase;
    color: var(--c-text-3);
}
.topbar-right { display: flex; align-items: center; gap: 12px; }

.topbar-live-chip {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(22, 163, 74, 0.1);
    border: 1px solid rgba(22, 163, 74, 0.22);
    border-radius: var(--r-pill); padding: 4px 10px;
    font-family: var(--font-display);
    font-size: 9px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase;
    color: var(--c-success);
}
.topbar-live-dot {
    width: 6px; height: 6px; border-radius: 50%;
    background: var(--c-success);
    animation: topbar-pulse 2s ease-in-out infinite;
}
@keyframes topbar-pulse { 0%, 100% { opacity: 1 } 50% { opacity: 0.4 } }

/* Pill neutral: no rojo, para no distraer del acento funcional */
.topbar-role-badge {
    font-family: var(--font-display);
    font-size: 9px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase;
    color: var(--c-text-3);
    background: var(--c-surface-2);
    border: 1px solid var(--c-border);
    border-radius: var(--r-pill); padding: 4px 8px;
}
.topbar-btn {
    width: var(--tap-comfort, 48px); height: var(--tap-comfort, 48px);
    min-width: var(--tap-comfort, 48px);
    border-radius: var(--r-md);
    display: inline-flex; align-items: center; justify-content: center;
    color: var(--c-text-2);
    background: var(--c-surface);
    border: 1px solid var(--c-border);
    transition: background 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease);
    cursor: pointer;
}
.topbar-btn:hover { background: var(--c-surface-2); border-color: var(--c-border-bright); }
.topbar-avatar {
    width: var(--tap-comfort, 48px); height: var(--tap-comfort, 48px);
    min-width: var(--tap-comfort, 48px);
    border-radius: 50%;
    background: linear-gradient(135deg, var(--c-accent-dim), rgba(220,38,38,0.04));
    border: 1.5px solid var(--c-accent-border);
    display: inline-flex; align-items: center; justify-content: center;
    font-family: var(--font-display);
    font-size: 18px; font-weight: 700; letter-spacing: 0.04em;
    color: var(--c-accent);
    cursor: pointer;
}

/* ── Mobile ──────────────────────────────────────────────────────────────── */
.topbar-mobile {
    position: sticky; top: 0; z-index: 40;
    height: 56px;
    align-items: center; justify-content: space-between;
    padding: 0 16px;
    background: rgba(8, 8, 8, 0.92);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    border-bottom: 1px solid var(--c-border);
    box-shadow: var(--shadow-header);
}
.topbar-mobile-hamburger {
    width: var(--tap-comfort, 48px); height: var(--tap-comfort, 48px);
    min-width: var(--tap-comfort, 48px);
    border-radius: var(--r-md);
    display: inline-flex; align-items: center; justify-content: center;
    background: var(--c-surface); border: 1px solid var(--c-border);
    color: var(--c-text-2);
    cursor: pointer;
}
.topbar-mobile-brand {
    font-family: var(--font-display);
    font-size: 18px; font-weight: 700; letter-spacing: 5px;
    color: var(--c-text); line-height: 1;
    display: inline-flex; align-items: center; gap: 8px;
}
/* Pill SUPERADMIN en mobile: neutral surface-2, Oswald 9px */
.topbar-mobile-role {
    font-family: var(--font-display);
    font-size: 9px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase;
    color: var(--c-text-3);
    background: var(--c-surface-2);
    border-radius: var(--r-pill);
    padding: 4px 8px;
    flex-shrink: 0;
}
.topbar-mobile-right { display: inline-flex; align-items: center; gap: 6px; }
.topbar-live-dot--mobile {
    width: 7px; height: 7px;
    box-shadow: 0 0 8px var(--c-success);
}
.topbar-avatar--mobile {
    width: var(--tap-comfort, 48px); height: var(--tap-comfort, 48px);
    font-size: 16px;
}
</style>
