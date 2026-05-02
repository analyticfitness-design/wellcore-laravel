<script setup>
const props = defineProps({
  tool:         { type: Object,  required: true },
  isSuperadmin: { type: Boolean, default: false },
});

const emit = defineEmits(['run']);

// SVG paths — inline subset, no external icon lib
const ICON_PATHS = {
  database:           'M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125',
  chip:               'M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-15 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21m-9-1.5h10.5a2.25 2.25 0 0 0 2.25-2.25V6.75a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 6.75v10.5a2.25 2.25 0 0 0 2.25 2.25Zm.75-12h9v9h-9v-9Z',
  lightning:          'M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z',
  'circle-stack':     'M6.429 9.75 2.25 12l4.179 2.25m0-4.5 5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0 4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0-5.571 3-5.571-3',
  'information-circle':'M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z',
  key:                'M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 0 1 21.75 8.25Z',
  'shield-check':     'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z',
  users:              'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z',
  'document-text':    'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z',
  'archive-box':      'M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z',
  envelope:           'M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75',
  'paper-airplane':   'M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5',
};

function iconPath(key) {
  return ICON_PATHS[key] || ICON_PATHS.lightning;
}

// Derived: is this card locked for the current user?
const locked = props.tool.destructive && !props.isSuperadmin;
</script>

<template>
  <article class="tool-card" :class="{ 'tool-card--destructive': tool.destructive, 'tool-card--locked': locked }">
    <!-- Icon -->
    <div class="tool-card-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" :d="iconPath(tool.icon)" />
      </svg>
    </div>

    <!-- Divider -->
    <div class="tool-card-divider" />

    <!-- Content -->
    <h3 class="tool-card-title">{{ tool.title }}</h3>
    <p class="tool-card-desc">{{ tool.description }}</p>

    <!-- Spacer -->
    <div style="flex: 1;" />

    <!-- Destructive badge -->
    <div v-if="tool.destructive" class="tool-card-badge">
      <svg class="tool-card-badge-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
      </svg>
      DESTRUCTIVO
    </div>

    <!-- CTA -->
    <button
      class="tool-card-btn"
      :class="locked ? 'tool-card-btn--disabled' : 'tool-card-btn--active'"
      :disabled="locked"
      :title="locked ? 'Solo Superadmin puede ejecutar esta herramienta' : undefined"
      @click="!locked && emit('run', tool)"
      :aria-label="`Ejecutar ${tool.title}`"
    >
      {{ locked ? 'SOLO SUPERADMIN' : 'EJECUTAR →' }}
    </button>
  </article>
</template>

<style scoped>
.tool-card {
  display: flex;
  flex-direction: column;
  gap: 8px;
  border-radius: var(--r-md, 16px);
  border: 1px solid var(--c-border);
  background: rgba(17, 17, 17, 0.7);
  padding: 18px;
  transition: border-color 0.15s var(--ease-out), background 0.15s var(--ease-out);
}
.tool-card:hover:not(.tool-card--locked) {
  border-color: rgba(255,255,255,0.12);
  background: rgba(24, 24, 24, 0.9);
}

.tool-card-icon {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background: rgba(220,38,38,0.08);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #F87171;
  flex-shrink: 0;
}
.tool-card-icon svg { width: 18px; height: 18px; }
.tool-card--locked .tool-card-icon {
  background: rgba(255,255,255,0.04);
  color: var(--c-text-3);
}

.tool-card-divider {
  height: 1px;
  background: var(--c-border);
  margin: 2px 0;
}

.tool-card-title {
  font-family: var(--font-display);
  font-size: 16px;
  letter-spacing: 0.06em;
  color: var(--c-text);
  text-transform: uppercase;
  line-height: 1.2;
}
.tool-card--locked .tool-card-title { color: var(--c-text-2); }

.tool-card-desc {
  font-family: var(--font-sans);
  font-size: 12px;
  color: var(--c-text-2);
  line-height: 1.55;
}

.tool-card-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-family: var(--font-display);
  font-size: 8px;
  letter-spacing: 1.6px;
  text-transform: uppercase;
  color: #F87171;
  background: var(--c-accent-dim);
  border-radius: var(--r-pill, 999px);
  padding: 2px 7px;
  width: fit-content;
}
.tool-card-badge-icon { width: 11px; height: 11px; }

.tool-card-btn {
  margin-top: 4px;
  width: 100%;
  min-height: var(--tap-comfort, 48px);
  border-radius: var(--r-sm, 12px);
  padding: 9px 12px;
  font-family: var(--font-display);
  font-size: 10px;
  letter-spacing: 1.6px;
  text-transform: uppercase;
  cursor: pointer;
  border: 1px solid;
  transition: background 0.15s var(--ease-out), color 0.15s var(--ease-out), border-color 0.15s var(--ease-out);
}
.tool-card-btn--active {
  background: rgba(220,38,38,0.08);
  border-color: rgba(220,38,38,0.25);
  color: #F87171;
}
.tool-card-btn--active:hover {
  background: rgba(220,38,38,0.15);
  border-color: rgba(220,38,38,0.4);
}
.tool-card-btn--disabled {
  background: rgba(255,255,255,0.02);
  border-color: var(--c-border);
  color: var(--c-text-3);
  cursor: not-allowed;
  opacity: 0.6;
}

@media (prefers-reduced-motion: reduce) {
  .tool-card, .tool-card-btn { transition: none !important; }
}
</style>
