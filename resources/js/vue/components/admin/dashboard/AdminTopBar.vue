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

const emit = defineEmits(['toggle-sidebar', 'toggle-dark']);

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
      <button
        class="topbar-btn"
        @click="$emit('toggle-dark')"
        aria-label="Cambiar modo oscuro"
        title="Cambiar modo"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
        </svg>
      </button>
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
   AdminTopBar — desktop 64px / mobile 52px sticky
   Tokens consumidos: --color-wc-bg, --color-wc-border, --color-wc-red,
   --color-wc-red-text, --color-wc-green, --color-wc-text, --color-wc-text-tertiary,
   --font-display, --font-mono, --ease-out, --admin-sidebar-w, --admin-topbar-h
   ============================================================================ */

/* ── Desktop ──────────────────────────────────────────────────────────────── */
.topbar-desktop {
    position: sticky; top: 0; z-index: 40;
    height: var(--admin-topbar-h, 64px);
    align-items: center;
    padding: 0 24px 0 0;
    background: rgba(10, 10, 10, 0.88);
    backdrop-filter: blur(20px) saturate(160%);
    -webkit-backdrop-filter: blur(20px) saturate(160%);
    border-bottom: 1px solid var(--color-wc-border);
    gap: 0;
}
.topbar-brand-area {
    width: var(--admin-sidebar-w, 240px);
    flex-shrink: 0;
    padding: 0 20px;
    display: flex; align-items: center; gap: 10px;
    border-right: 1px solid var(--color-wc-border);
    height: 100%;
}
.topbar-brand {
    font-family: var(--font-display);
    font-size: 24px; letter-spacing: 0.1em;
    color: var(--color-wc-text);
}
.topbar-brand-accent { color: var(--color-wc-accent, #DC2626); }
.topbar-center { flex: 1; padding: 0 24px; }
.topbar-eyebrow {
    font-family: var(--font-mono, 'JetBrains Mono', monospace);
    font-size: 11px; letter-spacing: 0.18em; text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}
.topbar-right { display: flex; align-items: center; gap: 12px; }

.topbar-live-chip {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(16, 185, 129, 0.08);
    border: 1px solid rgba(16, 185, 129, 0.2);
    border-radius: 20px; padding: 4px 10px;
    font-family: var(--font-mono, monospace);
    font-size: 9px; letter-spacing: 0.18em; text-transform: uppercase;
    color: var(--color-wc-green-text, #34D399);
}
.topbar-live-dot {
    width: 6px; height: 6px; border-radius: 50%;
    background: var(--color-wc-success, #10B981);
    animation: topbar-pulse 2s ease-in-out infinite;
}
@keyframes topbar-pulse { 0%, 100% { opacity: 1 } 50% { opacity: 0.4 } }

.topbar-role-badge {
    font-family: var(--font-mono, monospace);
    font-size: 8px; letter-spacing: 0.22em; text-transform: uppercase;
    color: var(--color-wc-red-text, #F87171);
    background: var(--color-wc-red-soft, rgba(220, 38, 38, 0.1));
    border: 1px solid rgba(220, 38, 38, 0.25);
    border-radius: 4px; padding: 4px 9px;
}
.topbar-btn {
    width: 36px; height: 36px; border-radius: 8px;
    display: inline-flex; align-items: center; justify-content: center;
    color: var(--color-wc-text-tertiary);
    background: transparent;
    border: 1px solid var(--color-wc-border);
    transition: border-color 0.15s var(--ease-out, ease), color 0.15s var(--ease-out, ease);
    cursor: pointer;
}
.topbar-btn:hover {
    border-color: var(--color-wc-border-2, rgba(255, 255, 255, 0.12));
    color: var(--color-wc-text);
}
.topbar-avatar {
    width: 36px; height: 36px; border-radius: 50%;
    background: var(--color-wc-bg-tertiary, #181818);
    border: 1.5px solid rgba(220, 38, 38, 0.4);
    display: inline-flex; align-items: center; justify-content: center;
    font-family: var(--font-display);
    font-size: 13px; letter-spacing: 0.04em;
    color: var(--color-wc-text);
    cursor: pointer;
}

/* ── Mobile ──────────────────────────────────────────────────────────────── */
.topbar-mobile {
    position: sticky; top: 0; z-index: 40;
    height: 52px;
    align-items: center; justify-content: space-between;
    padding: 0 16px;
    background: rgba(10, 10, 10, 0.85);
    backdrop-filter: blur(20px) saturate(150%);
    -webkit-backdrop-filter: blur(20px) saturate(150%);
    border-bottom: 1px solid var(--color-wc-border);
}
.topbar-mobile-hamburger {
    width: 36px; height: 36px; border-radius: 8px;
    display: inline-flex; align-items: center; justify-content: center;
    background: transparent; border: 1px solid var(--color-wc-border);
    color: var(--color-wc-text-tertiary);
    cursor: pointer;
}
.topbar-mobile-brand {
    font-family: var(--font-display);
    font-size: 18px; letter-spacing: 0.1em;
    color: var(--color-wc-text); line-height: 1;
    display: inline-flex; align-items: baseline; gap: 6px;
}
.topbar-mobile-role {
    font-family: var(--font-mono, monospace);
    font-size: 7px; letter-spacing: 0.2em;
    color: var(--color-wc-text-tertiary);
    margin-left: 4px;
}
.topbar-mobile-right { display: inline-flex; align-items: center; gap: 8px; }
.topbar-live-dot--mobile {
    width: 7px; height: 7px;
    box-shadow: 0 0 8px var(--color-wc-success, #10B981);
}
.topbar-avatar--mobile { width: 32px; height: 32px; font-size: 11px; }
</style>
