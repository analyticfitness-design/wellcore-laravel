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
   AdminGreeting — hero saludo + tagline Raleway italic.
   v2: Oswald eyebrow + title, Raleway quote, tokens v2.
   Mobile: stack vertical compact. Desktop: greeting bar inline con CTAs.
   ============================================================================ */

/* ── Mobile ──────────────────────────────────────────────────────────────── */
.greeting-mobile {
    padding: 22px 16px 28px;
    position: relative; overflow: hidden;
    border-bottom: 1px solid var(--c-border);
}
.greeting-eyebrow {
    font-family: var(--font-display);
    font-size: 12px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase;
    color: var(--c-accent);
    margin: 0 0 12px;
}
.greeting-title {
    font-family: var(--font-display);
    font-size: var(--t-3xl, 56px); font-weight: 700;
    letter-spacing: var(--ls-display, -0.02em); line-height: var(--lh-display, 0.95);
    color: var(--c-text);
    margin: 0 0 12px;
    text-transform: uppercase; text-wrap: balance;
}
.greeting-editorial {
    font-family: var(--font-editorial, var(--font-sans));
    font-style: italic; font-weight: 300;
    font-size: 15px; line-height: var(--lh-body, 1.65);
    color: var(--c-text-2);
    margin: 0 0 16px;
}
.greeting-badge-row {
    display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
}
.greeting-badge-critical {
    display: inline-flex; align-items: center; gap: 7px;
    background: var(--c-accent-dim);
    border: 1px solid rgba(220, 38, 38, 0.35);
    border-radius: var(--r-sm, 12px);
    padding: 6px 12px;
    font-family: var(--font-display);
    font-size: 10px; font-weight: 600; letter-spacing: 1.4px;
    color: #F87171;
    text-transform: uppercase;
}
.greeting-badge-dot {
    width: 6px; height: 6px; border-radius: 50%;
    background: #F87171;
    animation: greeting-pulse 1.8s ease-in-out infinite;
}
@keyframes greeting-pulse { 0%, 100% { opacity: 1 } 50% { opacity: 0.5 } }
.greeting-badge-time {
    font-family: var(--font-display);
    font-size: 10px; font-weight: 600; letter-spacing: 1.2px; text-transform: uppercase;
    color: var(--c-text-3);
    font-variant-numeric: tabular-nums; font-feature-settings: "tnum";
}

/* ── Desktop ──────────────────────────────────────────────────────────────── */
.greeting-desktop {
    align-items: flex-start; justify-content: space-between;
    padding: 28px 0 28px;
    gap: 24px;
    border-bottom: 1px solid var(--c-border);
}
.greeting-desktop-left { flex: 1; min-width: 0; }
.greeting-title-desktop {
    font-family: var(--font-display);
    font-size: var(--t-2xl, 49px); font-weight: 700;
    letter-spacing: var(--ls-display, -0.02em); line-height: var(--lh-display, 0.95);
    color: var(--c-text);
    margin: 0 0 8px;
    text-transform: uppercase;
}
.greeting-editorial-desktop {
    font-family: var(--font-editorial, var(--font-sans));
    font-style: italic; font-weight: 300;
    font-size: 15px; line-height: var(--lh-body, 1.65);
    color: var(--c-text-2);
    margin: 0;
}
.greeting-desktop-right {
    display: flex; align-items: center; gap: 12px; flex-shrink: 0;
}
.greeting-cta {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 12px 18px; border-radius: var(--r-sm, 12px);
    min-height: var(--tap-comfort, 48px);
    font-family: var(--font-display);
    font-size: 11px; font-weight: 600; letter-spacing: 1.4px;
    text-transform: uppercase;
    transition: background 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease);
    text-decoration: none;
}
.greeting-cta--primary {
    background: var(--c-accent);
    color: #fff;
    border: 1px solid var(--c-accent);
}
.greeting-cta--primary:hover { background: #B91C1C; }
.greeting-cta--secondary {
    background: var(--c-surface-2);
    color: var(--c-text);
    border: 1px solid var(--c-border);
}
.greeting-cta--secondary:hover { border-color: var(--c-border-bright); }
.greeting-cta-badge {
    background: rgba(255, 255, 255, 0.18);
    border-radius: var(--r-pill, 999px); padding: 1px 7px;
    font-family: var(--font-display);
    font-size: 10px; font-weight: 700;
}
.greeting-cta-badge--blue {
    background: rgba(59, 130, 246, 0.2);
    color: #60A5FA;
}
.greeting-meta {
    text-align: right;
    margin-left: 8px;
}
.greeting-time {
    font-family: var(--font-display);
    font-size: 28px; letter-spacing: var(--ls-mono, 0.04em);
    color: var(--c-text-3);
    line-height: 1; margin-bottom: 4px;
    font-variant-numeric: tabular-nums; font-feature-settings: "tnum";
}
.greeting-date {
    font-family: var(--font-display);
    font-size: 10px; font-weight: 600; letter-spacing: 1.2px; text-transform: uppercase;
    color: var(--c-text-3);
}
</style>
