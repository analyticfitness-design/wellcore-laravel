<script setup>
import { computed, ref, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
  userName: { type: String, default: '' },
  userRole: { type: String, default: 'Superadmin' },
  notifBadge: { type: [String, Number], default: 8 },
});
const emit = defineEmits(['toggleSidebar', 'openSearch']);

const userInitial = computed(() => (props.userName || 'D').charAt(0).toUpperCase());

const clock = ref('00:00');
let clockInterval = null;
function tick() {
  const d = new Date();
  clock.value = d.toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit', hour12: false });
}
onMounted(() => { tick(); clockInterval = setInterval(tick, 1000 * 30); });
onBeforeUnmount(() => clearInterval(clockInterval));

const dateLabel = computed(() => {
  const d = new Date();
  const days = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
  const months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
  return `${days[d.getDay()]} ${d.getDate()} ${months[d.getMonth()]}`;
});
</script>

<template>
  <!-- MOBILE TOPBAR (visible solo < 1024px via CSS) -->
  <header class="topbar topbar-mobile">
    <button class="hamburger" aria-label="Abrir menú lateral" @click="emit('toggleSidebar')">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="3" y1="6" x2="21" y2="6"></line>
        <line x1="3" y1="12" x2="21" y2="12"></line>
        <line x1="3" y1="18" x2="21" y2="18"></line>
      </svg>
    </button>
    <div class="brand-mark brand-mark-img">
      <picture>
        <source srcset="/images/wellcore-logo-128.webp" type="image/webp" />
        <img src="/images/wellcore-logo-128.png" alt="WellCore" width="32" height="32" loading="eager" />
      </picture>
    </div>
    <div class="brand-name">WELLCORE</div>
    <div class="role-chip"><span class="dot"></span>{{ userRole }}</div>
    <div class="tb-actions">
      <button class="cmdk-pill" aria-label="Buscar" @click="emit('openSearch')">
        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="7"></circle>
          <path d="M21 21l-4.3-4.3"></path>
        </svg>
        <kbd>⌘K</kbd>
      </button>
      <button class="bell" aria-label="Notificaciones">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
          <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path>
          <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"></path>
        </svg>
        <span v-if="notifBadge" class="bell-badge">{{ notifBadge }}</span>
      </button>
      <div class="avatar"><div class="inner">{{ userInitial }}</div></div>
    </div>
  </header>

  <!-- DESKTOP TOPBAR (visible solo >= 1024px via CSS) -->
  <header class="topbar topbar-desktop">
    <div class="tb-eye">
      <span class="live"><span class="d"></span>Command Center</span>
      <span class="sep">·</span>
      <span>{{ dateLabel }}</span>
    </div>
    <div class="tb-actions">
      <button class="cmdk-pill" @click="emit('openSearch')">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="7"></circle>
          <path d="M21 21l-4.3-4.3"></path>
        </svg>
        <span class="ph">Buscar acciones, clientes, herramientas…</span>
        <kbd>⌘K</kbd>
      </button>
      <button class="bell" aria-label="Notificaciones">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
          <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path>
          <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"></path>
        </svg>
        <span v-if="notifBadge" class="bell-badge">{{ notifBadge }}</span>
      </button>
      <div class="tb-clock"><span class="mono tnum">{{ clock }}</span></div>
    </div>
  </header>
</template>

<style scoped>
.topbar-mobile { display: flex; }
.topbar-desktop { display: none; }
@media (min-width: 1024px){
  .topbar-mobile { display: none; }
  .topbar-desktop { display: flex; }
}

/* Hamburger button — visible solo mobile */
.hamburger{
  width: 36px; height: 36px;
  border-radius: 10px;
  display: grid; place-items: center;
  background: rgba(255,255,255,.04);
  box-shadow: inset 0 0 0 1px rgba(255,255,255,.06);
  color: var(--wc-text-2);
  cursor: pointer;
  transition: all 180ms cubic-bezier(0.4, 0, 0.2, 1);
  flex-shrink: 0;
  border: 0;
}
.hamburger:hover{
  color: var(--wc-text);
  box-shadow: inset 0 0 0 1px rgba(255,255,255,.16);
}

/* Brand-mark con imagen (override del SVG-only del CSS general) */
.brand-mark.brand-mark-img{
  background: transparent !important;
  border: 0 !important;
  box-shadow: none !important;
  padding: 0;
}
.brand-mark.brand-mark-img img{
  width: 100%;
  height: 100%;
  object-fit: contain;
  display: block;
}
</style>
