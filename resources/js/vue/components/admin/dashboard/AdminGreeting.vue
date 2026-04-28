<script setup>
import { computed, ref, onMounted, onBeforeUnmount } from 'vue';
import { RouterLink } from 'vue-router';

const props = defineProps({
  // Texto del saludo: "Buenas noches, Daniel Esparza - CEO"
  greeting: { type: String, required: true },
  // Cantidad de alerts criticas pendientes (badge mobile)
  criticalAlerts: { type: Number, default: 0 },
  // Tickets pendientes (CTA principal desktop)
  pendingTickets: { type: Number, default: 0 },
  // Tickets en revision (CTA secundaria)
  reviewTickets: { type: Number, default: 0 },
});

// Tagline editorial Fraunces italic — rotativa, una por dia.
// 7 frases curadas con voz de Daniel (filosofica, anti-guru, directa).
const editorialPool = [
  'La disciplina es la unica medida que importa.',
  'No estamos midiendo el ego. Estamos midiendo el progreso.',
  'Cada metrica es una conversacion con el cliente.',
  'El sistema funciona cuando el coach se compromete con el cliente.',
  'No hay clientes dificiles. Hay sistemas mal disenados.',
  'La consistencia es mas valiosa que el talento.',
  'El dato sin accion es solo decoracion.',
];
const editorialPick = computed(() => {
  const dayIdx = new Date().getDay();
  return editorialPool[dayIdx % editorialPool.length];
});

// Reloj formato hora desktop
const now = ref(new Date());
let clockInterval = null;
onMounted(() => {
  clockInterval = setInterval(() => { now.value = new Date(); }, 60000);
});
onBeforeUnmount(() => {
  if (clockInterval) clearInterval(clockInterval);
});
const clockTime = computed(() => {
  const h = now.value.getHours().toString().padStart(2, '0');
  const m = now.value.getMinutes().toString().padStart(2, '0');
  return `${h}:${m}`;
});
const clockDate = computed(() => {
  return now.value.toLocaleDateString('es-CO', { weekday: 'long', day: 'numeric', month: 'short' });
});
</script>

<template>
  <!-- Mobile hero — saludo + tagline Fraunces + badge critico + tickets CTA -->
  <section class="greeting-mobile lg:hidden">
    <p class="greeting-eyebrow">PANEL EJECUTIVO</p>
    <h1 class="greeting-title">{{ greeting }}</h1>
    <p class="greeting-editorial">"{{ editorialPick }}"</p>

    <div class="greeting-badge-row">
      <span v-if="criticalAlerts > 0" class="greeting-badge-critical">
        <span class="greeting-badge-dot"></span>
        {{ criticalAlerts }} {{ criticalAlerts === 1 ? 'ALERTA CRITICA' : 'ALERTAS CRITICAS' }}
      </span>
      <span class="greeting-badge-time">{{ clockDate }} · {{ clockTime }}</span>
    </div>
  </section>

  <!-- Desktop greeting bar — saludo gigante + reloj + CTAs en la derecha -->
  <section class="greeting-desktop hidden lg:flex">
    <div class="greeting-desktop-left">
      <h1 class="greeting-title-desktop">{{ greeting }}</h1>
      <p class="greeting-editorial-desktop">"{{ editorialPick }}"</p>
    </div>
    <div class="greeting-desktop-right">
      <RouterLink
        to="/admin/plan-tickets?status=pendiente"
        class="greeting-cta greeting-cta--primary"
      >
        <span>Tickets pendientes</span>
        <span v-if="pendingTickets > 0" class="greeting-cta-badge">{{ pendingTickets }}</span>
      </RouterLink>
      <RouterLink
        to="/admin/plan-tickets?status=en_revision"
        class="greeting-cta greeting-cta--secondary"
      >
        <span>En revision</span>
        <span v-if="reviewTickets > 0" class="greeting-cta-badge greeting-cta-badge--blue">{{ reviewTickets }}</span>
      </RouterLink>
      <div class="greeting-meta">
        <div class="greeting-time">{{ clockTime }}</div>
        <div class="greeting-date">{{ clockDate }}</div>
      </div>
    </div>
  </section>
</template>

<style scoped>
/* ============================================================================
   AdminGreeting — hero saludo con tagline Fraunces italic.
   Mobile: stack vertical compact. Desktop: greeting bar inline con CTAs.
   ============================================================================ */

/* ── Mobile ──────────────────────────────────────────────────────────────── */
.greeting-mobile {
    padding: 22px 16px 20px;
    position: relative; overflow: hidden;
}
.greeting-eyebrow {
    font-family: var(--font-mono, monospace);
    font-size: 9px; letter-spacing: 0.22em; text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
    margin: 0 0 8px;
}
.greeting-title {
    font-family: var(--font-display);
    font-size: 32px; letter-spacing: 0.04em; line-height: 0.98;
    color: var(--color-wc-text);
    margin: 0 0 10px;
    text-wrap: balance;
}
.greeting-editorial {
    font-family: var(--font-editorial, 'Fraunces', Georgia, serif);
    font-style: italic;
    font-size: 12px; line-height: 1.5;
    color: var(--color-wc-gold, #D4A04C);
    margin: 0 0 14px;
}
.greeting-badge-row {
    display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
}
.greeting-badge-critical {
    display: inline-flex; align-items: center; gap: 7px;
    background: rgba(220, 38, 38, 0.14);
    border: 1px solid rgba(220, 38, 38, 0.35);
    border-radius: 8px;
    padding: 6px 11px;
    font-family: var(--font-display);
    font-size: 12px; letter-spacing: 0.14em;
    color: var(--color-wc-red-text, #F87171);
    text-transform: uppercase;
}
.greeting-badge-dot {
    width: 6px; height: 6px; border-radius: 50%;
    background: var(--color-wc-red-text, #F87171);
    animation: greeting-pulse 1.8s ease-in-out infinite;
}
@keyframes greeting-pulse { 0%, 100% { opacity: 1 } 50% { opacity: 0.5 } }
.greeting-badge-time {
    font-family: var(--font-mono, monospace);
    font-size: 9px; letter-spacing: 0.15em; text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}

/* ── Desktop ──────────────────────────────────────────────────────────────── */
.greeting-desktop {
    align-items: flex-start; justify-content: space-between;
    padding: 28px 0 20px;
    gap: 24px;
}
.greeting-desktop-left { flex: 1; min-width: 0; }
.greeting-title-desktop {
    font-family: var(--font-display);
    font-size: 38px; letter-spacing: 0.04em; line-height: 1;
    color: var(--color-wc-text);
    margin: 0 0 6px;
}
.greeting-editorial-desktop {
    font-family: var(--font-editorial, 'Fraunces', Georgia, serif);
    font-style: italic;
    font-size: 13px; line-height: 1.5;
    color: var(--color-wc-gold, #D4A04C);
    margin: 0;
}
.greeting-desktop-right {
    display: flex; align-items: center; gap: 12px; flex-shrink: 0;
}
.greeting-cta {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 16px; border-radius: 10px;
    font-family: var(--font-sans);
    font-size: 13px; font-weight: 600;
    transition: background 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease);
    text-decoration: none;
}
.greeting-cta--primary {
    background: var(--color-wc-accent, #DC2626);
    color: #fff;
    border: 1px solid var(--color-wc-accent, #DC2626);
}
.greeting-cta--primary:hover { background: #B91C1C; }
.greeting-cta--secondary {
    background: rgba(17, 17, 17, 0.7);
    color: var(--color-wc-text);
    border: 1px solid var(--color-wc-border);
}
.greeting-cta--secondary:hover { border-color: var(--color-wc-border-2, rgba(255, 255, 255, 0.12)); }
.greeting-cta-badge {
    background: rgba(255, 255, 255, 0.18);
    border-radius: 999px; padding: 1px 7px;
    font-family: var(--font-data, 'Barlow', sans-serif);
    font-size: 11px; font-weight: 700;
}
.greeting-cta-badge--blue {
    background: rgba(59, 130, 246, 0.2);
    color: var(--color-wc-blue-text, #60A5FA);
}
.greeting-meta {
    text-align: right;
    margin-left: 8px;
}
.greeting-time {
    font-family: var(--font-display);
    font-size: 26px; letter-spacing: 0.08em;
    color: var(--color-wc-text-tertiary);
    line-height: 1; margin-bottom: 3px;
}
.greeting-date {
    font-family: var(--font-mono, monospace);
    font-size: 9px; letter-spacing: 0.18em; text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}
</style>
