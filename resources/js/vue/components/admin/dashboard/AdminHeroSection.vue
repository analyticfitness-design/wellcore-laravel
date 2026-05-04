<script setup>
import { computed, ref, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
  greeting: { type: String, default: 'Buenas noches,' },
  userName: { type: String, required: true },
  role: { type: String, default: 'CEO' },
  quote: { type: String, default: '"El dato sin acción es solo decoración."' },
});

// Reloj live mobile (eyebrow del hero)
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

const firstName = computed(() => (props.userName || '').split(' ')[0] || '');
const lastName = computed(() => (props.userName || '').split(' ').slice(1).join(' ') || '');

// Greeting limpio: si "Buenas noches, Daniel — CEO" llega del backend,
// mostrar solo el saludo (los nombres ya van en hero-name).
const cleanGreeting = computed(() => {
  if (!props.greeting) return 'Buenas noches,';
  return props.greeting.split(',')[0] + ',';
});
</script>

<template>
  <section class="hero">
    <!-- Mobile: eyebrow stack + greet + name + quote -->
    <div class="hero-mobile">
      <div class="hero-eyebrow">
        <span class="live"><span class="d"></span>Panel ejecutivo</span>
        <span class="sep">·</span>
        <span>{{ dateLabel }}</span>
        <span class="sep">·</span>
        <span class="tnum mono">{{ clock }}</span>
      </div>
      <div class="hero-greet">{{ cleanGreeting }}</div>
      <h1 class="hero-name">{{ firstName }} <span v-if="lastName" class="accent">{{ lastName }}</span><template v-if="role"> — {{ role }}</template></h1>
      <p class="hero-quote">{{ quote }}</p>
    </div>

    <!-- Desktop: 2-col flex-end con eyebrow inline + alerts slot a la derecha -->
    <div class="hero-desktop">
      <div class="hero-left">
        <div class="eyebrow">Panel Ejecutivo · {{ dateLabel }}</div>
        <div class="hero-greet">{{ cleanGreeting }}</div>
        <h1 class="hero-name">{{ firstName }} <span v-if="lastName" class="accent">{{ lastName }}</span><template v-if="role"> — {{ role }}</template></h1>
        <p class="hero-quote">{{ quote }}</p>
      </div>
      <div class="hero-right">
        <slot name="alerts" />
      </div>
    </div>
  </section>
</template>

<style scoped>
.hero-mobile { display: block; }
.hero-desktop { display: none; }
@media (min-width: 1024px){
  .hero-mobile { display: none; }
  .hero-desktop { display: flex; align-items: flex-end; justify-content: space-between; gap: 24px; width: 100%; }
}
</style>
