<script setup>
import { useAdminClientDetailStore } from '../../../stores/adminClientDetail';

const store = useAdminClientDetailStore();

const TABS = [
    { id: 'resumen',      label: 'RESUMEN' },
    { id: 'plan',         label: 'PLAN' },
    { id: 'checkins',     label: 'CHECK-INS' },
    { id: 'pagos',        label: 'PAGOS' },
    { id: 'comunicacion', label: 'COMUNICACION' },
    { id: 'notas',        label: 'NOTAS' },
];
</script>

<template>
  <nav class="client-tabs" role="tablist" aria-label="Secciones del cliente">
    <button
      v-for="tab in TABS"
      :key="tab.id"
      type="button"
      role="tab"
      class="tab"
      :class="{ 'tab--active': store.activeTab === tab.id }"
      :aria-selected="store.activeTab === tab.id"
      :aria-controls="`panel-${tab.id}`"
      :id="`tab-${tab.id}`"
      @click="store.setTab(tab.id)"
    >
      {{ tab.label }}
    </button>
  </nav>
</template>

<style scoped>
.client-tabs {
    display: flex;
    gap: 4px;
    overflow-x: auto;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
    scroll-snap-type: x proximity;
    padding-bottom: 4px;
    border-bottom: 1px solid var(--color-wc-border);
}
.client-tabs::-webkit-scrollbar { display: none; }
.client-tabs { scrollbar-width: none; }

.tab {
    flex-shrink: 0;
    scroll-snap-align: start;
    height: 36px;
    padding: 0 14px;
    border: none;
    background: transparent;
    color: var(--color-wc-text-tertiary);
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    margin-bottom: -1px;
    transition: color 0.15s var(--ease-out, ease), border-color 0.15s var(--ease-out, ease);
}
.tab:hover { color: var(--color-wc-text-secondary); }
.tab:focus-visible {
    outline: 1px solid var(--color-wc-accent, #DC2626);
    outline-offset: 2px;
    border-radius: 4px;
}
.tab--active {
    color: var(--color-wc-text);
    border-bottom-color: var(--color-wc-accent, #DC2626);
}
</style>
