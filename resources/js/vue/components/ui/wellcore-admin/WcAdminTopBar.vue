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
    <div class="brand-mark">
      <svg viewBox="0 0 16 16" fill="none">
        <path d="M2 4 L4.5 12 L8 6 L11.5 12 L14 4" stroke="#fff" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </div>
    <div>
      <div class="brand-name">WCORE<b>FIT</b></div>
    </div>
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
</style>
