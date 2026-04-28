<script setup>
import { RouterLink } from 'vue-router';
import { useAdminModules } from '../../../composables/useAdminModules';

const { modules } = useAdminModules();

// 12 modulos curados que NO viven naturalmente en el sidebar/bottom-nav.
// Acceso rapido editorial a herramientas operacionales.
const QUICK_MODULES_IDS = [
  'feed', 'inscripciones', 'invitaciones', 'comprobantes',
  'queue', 'ai-generator', 'rise', 'chat-analytics',
  'plan-tickets', 'client-requests', 'plan-tickets-stats', 'campanas',
];
const tools = QUICK_MODULES_IDS
  .map(id => modules.find(m => m.id === id))
  .filter(Boolean);

// SVG path por keyword — subset reducido del set del AdminSidebar
const ICON_PATHS = {
  feed:        'M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z',
  'user-plus':'M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z',
  mail:        'M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75',
  check:       'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
  megaphone:  'M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783m3.102-9.249a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 0 8.835-2.535',
  sparkles:   'M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z',
  lightning:  'M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z',
  chart:       'M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941',
  ticket:      'M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z',
  'ticket-2': 'M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z',
  inbox:       'M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859',
  stats:       'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z',
  target:      'M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58',
};

function iconPath(key) {
  return ICON_PATHS[key] || ICON_PATHS.target;
}
</script>

<template>
  <section class="tools-grid-card">
    <header class="tools-grid-header">
      <h2 class="tools-grid-title">HERRAMIENTAS</h2>
      <span class="tools-grid-meta">{{ tools.length }} accesos</span>
    </header>

    <div class="tools-grid">
      <RouterLink
        v-for="tool in tools"
        :key="tool.id"
        :to="tool.to"
        class="tool-card"
        :class="`tool-card--${tool.color || 'neutral'}`"
      >
        <span class="tool-icon" aria-hidden="true">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
            <path stroke-linecap="round" stroke-linejoin="round" :d="iconPath(tool.icon)" />
          </svg>
        </span>
        <span class="tool-name">{{ tool.name }}</span>
        <span v-if="tool.badge" class="tool-badge">{{ tool.badge }}</span>
      </RouterLink>
    </div>
  </section>
</template>

<style scoped>
/* ============================================================================
   AdminToolsGrid — accesos rapidos a 12 modulos editoriales del admin.
   Mobile: grid 2-col. Desktop: grid 4-col.
   ============================================================================ */

.tools-grid-card {
    border-radius: 14px;
    border: 1px solid var(--color-wc-border);
    background: rgba(17, 17, 17, 0.7);
    padding: 18px;
}
.tools-grid-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
}
.tools-grid-title {
    font-family: var(--font-display);
    font-size: 13px;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--color-wc-text);
    margin: 0;
}
.tools-grid-meta {
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}

.tools-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}
@media (min-width: 768px) {
    .tools-grid { grid-template-columns: repeat(3, 1fr); }
}
@media (min-width: 1024px) {
    .tools-grid { grid-template-columns: repeat(4, 1fr); }
}

.tool-card {
    border-radius: 12px;
    border: 1px solid var(--color-wc-border);
    background: rgba(24, 24, 24, 0.6);
    padding: 14px 12px;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
    text-decoration: none;
    color: var(--color-wc-text-secondary);
    position: relative;
    transition: transform 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease), color 0.15s var(--ease-out, ease);
}
.tool-card:hover {
    border-color: var(--color-wc-border-2, rgba(255, 255, 255, 0.12));
    color: var(--color-wc-text);
    transform: translateY(-1px);
}
.tool-card:active { transform: scale(0.98); }

.tool-icon {
    width: 32px;
    height: 32px;
    border-radius: 9px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.tool-card--red    .tool-icon { background: var(--color-wc-red-soft, rgba(220, 38, 38, 0.1)); color: var(--color-wc-red-text, #F87171); }
.tool-card--amber  .tool-icon { background: var(--color-wc-amber-soft, rgba(245, 158, 11, 0.1)); color: var(--color-wc-amber-text, #FCD34D); }
.tool-card--green  .tool-icon { background: var(--color-wc-green-soft, rgba(16, 185, 129, 0.1)); color: var(--color-wc-green-text, #34D399); }
.tool-card--blue   .tool-icon { background: var(--color-wc-blue-soft, rgba(59, 130, 246, 0.1)); color: var(--color-wc-blue-text, #60A5FA); }
.tool-card--neutral .tool-icon { background: rgba(255, 255, 255, 0.05); color: var(--color-wc-text-tertiary); }

.tool-name {
    font-family: var(--font-sans);
    font-size: 12px;
    font-weight: 600;
    line-height: 1.3;
    color: var(--color-wc-text);
    flex: 1;
}

.tool-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    font-family: var(--font-mono, monospace);
    font-size: 7px;
    letter-spacing: 0.15em;
    color: var(--color-wc-gold, #C8A769);
    text-transform: uppercase;
    background: rgba(212, 160, 76, 0.1);
    border: 1px solid rgba(212, 160, 76, 0.25);
    border-radius: 3px;
    padding: 2px 5px;
}
</style>
